=== Plugin Name ===
Contributors: chris57100
Donate link: 
Tags: upload, media library,unused,remove,delete,uploaded
Requires at least: 3.3
Tested up to: 3.3.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will help you to manage your upload folder, identify useless files and remove them and save space.

== Description ==

The plugin will help users to detect files that have been loaded in the Media Library and that are not used anymore.
Also, automatic resized pictures will be listed.
Once files are selected, they will be moved to a new folder so in case of problem, they can be restored.
The folder can be excluded from backups to save a huge amount of space !

== Installation ==

How to install the plugin and get it working.

1. Upload plugin archive to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go in the extensions menu and follow instructions for the 3 steps

== Frequently Asked Questions ==

= Will the files be deleted =

No. They will be only moved in a subfolder called 'WUFC_moved_files' in the upload directory.

== Screenshots ==

1. First screen to select folders to analyse
2. List of files that are in subfolders
3. Files moved

== Changelog ==

= 1.2 =
* SVN issue

= 1.1 =
* Bug corrected : remove duplicate files in the results list
* Display the number of files in each folder for the step 1
* Search for links in posts, pages, posts attachment (WP Gallery), wordpress tables : wp_postmeta
* Display the link type between the picture and the post/page (link, attachment, postmeta) for step 2

= 1.0 =
* Update step 1 for a better selection method (use of jQuery Dynatree)

= 0.4 =
* Correct javascript bugs for Chrome and Firefox

= 0.3 =
* More explicit steps in the selection
* Add filters at step 2 when selecting files
* Add a dynamic div in the top right corner to display the number of files that are selected and the disk space used by them
* Next improvement : A better folder selection at step 1 (and we will have version 1.0 ;-) )

= 0.2 =
* UI updates
* Add contact email in the header of the page
* Report errors/warning when creating/moving selected files
* Add screenshots for Wordpress.org plugin's page

= 0.1 =
* Initial revision

== Upgrade Notice ==

= 0.1 =
Initial revision

== Arbitrary section ==

