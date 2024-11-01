=== WordPress AutoMedia ===
Contributors: vedstudio, rcoll
Tags: gallery, image, photo, picture, pic, upload, uploader, auto, automatic, android, media, library, automedia
Requires at least: 3.0
Tested up to: 3.7.1
Stable tag: 3.7.1

Add photos to the Media Library automatically!

== Description ==

This is a very basic plugin, intended to add your FTP uploads directly to your 
Media Library for easy publishing. No more moving files around from phones 
and cameras in order to publish them. Simply batch upload or use the WordPress 
AutoMedia app for Android powered phones to get the photos to your server, and 
then this plugin will handle adding to your media library automatically.

Download the Android App at https://play.google.com/store/apps/details?id=com.vedstudio.wpam

Note: the app was created on an Android 2.3 device and does crash on many newer devices. I 
don't have the time to create a new version right now, but if anyone wants to step in and 
help out, please get in contact with me. Otherwise, give it a shot, it's free ...

Eventually, I'd like to see this rolled into core and the WordPress app.

Note: I'm all ears for future versions. Tell me what you would like to see 
improved/added/changed and I will do my best.

== Installation ==

1. Upload the wp-automedia directory containing 'wp-automedia.php' to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Download the Android 'WordPress AutoMedia' app from Google Play and follow the simple instructions.
4. Start taking pics! They'll automatically appear in your WordPress Media Library for publishing!
5. If you're using FTP instead of the app, upload your photos to /wp-content/uploads/automedia/ for proper indexing.

== Other Notes ==

== Changelog ==
= 2.1 =
* Adds a check to be sure that the image exists before removing it from the staging area, thereby averting a PHP warning if the file does not exist.

= 2.0 =
* General maintenance
* Cleaned up the code
* Security audit

= 1.5 =
* URL fix for new version of App

= 1.4 =
* Added menu page with helpful information

== Upgrade Notice ==
= 2.0 =
* Update the remote path on your Android app to continue using with 2.0