<?php
/*
Plugin Name: AutoMedia
Plugin URI: http://rcollier.me/software/
Description: Automatically adds photos from mobile phones to media library. For use with Android AutoMedia app.
Version: 2.1
Author: Rich Collier
Author URI: http://rcollier.me
*/

// Lets simplify this and just add images one by one to the media library

// Prevent execution of this file directly
if ( ! defined( 'ABSPATH' ) )	die( 'No cheating allowed :-)' );

// Do not extend or things will get ugly ;-)
final class WP_AutoMedia {
	
	// Singleton isntance
	private static $instance = null;
	
	// Get instance
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	// Choreograph the show
	private function __construct() {
		register_activation_hook( __FILE__, array( 'WP_AutoMedia', 'plugin_activation' ) );
		register_deactivation_hook( __FILE__, array( 'WP_AutoMedia', 'plugin_deactivation' ) );
		
		if ( is_admin() )
			add_action( 'admin_init', array( 'WP_AutoMedia', 'update_media_library' ) );
	}
	
	// On activation ...
	public function plugin_activation() {
		$wp_upload_dir = wp_upload_dir();
		
		// Default options
		$defaults = array( 
			'wpam_path' => trailingslashit( $wp_upload_dir['basedir'] ) . 'automedia/', 
			'wpam_url' => trailingslashit( $wp_upload_dir['baseurl'] ) . 'automedia/', 
		);
		
		// Write the default options array to DB
		update_option( 'automedia_options', $defaults );
		
		// Get the proper path
		$wpam_path = wpam_get_option( 'wpam_path' );
		
		// Set the umask
		$old_umask = umask( 0 );
		
		// Create the directory or throw an error
		if ( ! file_exists( $wpam_path ) && ! mkdir( $wpam_path, 0777 ) )
			wpam_error( "The required directory {$wpam_path} doesn't exist, and cannot be created automatically. Please create it manually.", 101 );
		
		// Reset the umask
		umask( $old_umask );
	}
	
	// On deactivation ...
	public function plugin_deactivation() {
		// Get the path to our directory
		$wpam_path = wpam_get_option( 'wpam_path' );
		
		// Remove the directory if it exists
		if ( file_exists( $wpam_path ) && ! rmdir( $wpam_path ) )
			wpam_error( "The directory {$wpam_path} was not removed due to file permissions. Please delete this path manually.", 102 );
		
		// Remove the database options
		delete_option( 'automedia_options' );
	}
	
	// Custom scandir that removes certain files and folders from list
	public function wpam_scandir( $path ) {
		$contents = scandir( $path );
		
		$contents = array_diff( $contents, array( '.', '..', 'index.php', 'queue', 'archive' ) );
		
		return $contents;
	}
	
	// Runs on each admin PV and imports images to library
	public function update_media_library() {
		$wp_upload_dir = wp_upload_dir();
		
		// This is where we want the images to end up eventually
		$upload_path = trailingslashit( untrailingslashit( $wp_upload_dir['basedir'] ) . $wp_upload_dir['subdir'] );
		
		// This is our staging path for images
		$wpam_path = wpam_get_option( 'wpam_path' );
		
		// Array of "importable" images in our staging folder
		$queue_contents = WP_AutoMedia::wpam_scandir( $wpam_path );
		
		// If we have images ...
		if ( $item_count = count( $queue_contents ) ) {
			// Need access to WordPress image functions
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			
			// Run a loop on all our images
			foreach ( $queue_contents as $image_name ) {
				// Set up paths of original image and target image
				$old_image_path = $wpam_path . $image_name;
				$new_image_path = $upload_path . preg_replace( '/\s/', '_', $image_name );
				
				// Make the switch
				rename( $old_image_path, $new_image_path );
				
				// Delete original - in some instances php rename doesn't delete the old file
				if ( file_exists( $old_image_path ) )
					unlink( $old_image_path );
				
				// WP attachment metadata
				$attachment = array( 
					'post_mime_type' => 'image/jpeg', 
					'post_title' => preg_replace( '/\.[^.]+$/', '', $image_name ), 
					'post_content' => 'AutoMedia Image', 
					'post_status' => 'inherit', 
				);
				
				// Insert the attachment into the DB
				$attachment_id = wp_insert_attachment( $attachment, $new_image_path );
				
				// Generate attachment metadata and additional image sizes
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $new_image_path );
				
				// Update the attachment metadata to reflect the changes
				wp_update_attachment_metadata( $attachment_id, $attachment_data );
			}
			
			// Update the last item count variable for later reference
			wpam_update_option( 'last_item_count', $item_count );
			
			// Show an admin message
			add_action( 'admin_notices', 'wpam_imported_admin_message' );
		}
	}
	
}

// Let the show begin ...
WP_AutoMedia::get_instance();

// Get an option from our options array
function wpam_get_option( $option_key ) {
	$wpam_options = get_option( 'automedia_options' );
	
	if ( ! is_array( $wpam_options ) )
		return false;
	
	return $wpam_options[$option_key];
}

// Update our array with an option
function wpam_update_option( $option_key, $option_value ) {
	$wpam_options = get_option( 'automedia_options' );
	
	$wpam_options[$option_key] = $option_value;
	
	update_option( 'automedia_options', $wpam_options );	
}

// Handle errors
function wpam_error( $message, $code=100 ) {
	wpam_update_option( 'last_error_message', $message );
	wpam_update_option( 'last_error_code', $code );
	add_action( 'admin_notices', 'wpam_error_admin_message' );
}

// Display output for wpam errors
function wpam_error_admin_message() {
	echo '<div class="error"><p>';
	echo 'AutoMedia Error: ' . wpam_get_option( 'last_error_message' );
	echo '</p></div>';
}

// Display output for wpam import messages
function wpam_imported_admin_message() {
	echo '<div class="updated"><p>';
	echo 'AutoMedia imported ' . wpam_get_option( 'last_item_count' ) . ' images into the media library.';
	echo '</p></div>';
}

// omit