=== Media Vault ===
Contributors: Max GJ Panas
Donate Link:
Tags: media, security, protection, attachments, downloads, download links, powerful, shortcode, flexible, simple, uploads
Requires at least: 3.2.0
Tested up to: 3.6.2
Stable tag: 0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Protect attachment files from direct access using powerful and flexible restrictions. Offer safe download links for any file in your uploads folder.

== Description ==

When you upload a file in WordPress, that file gets saved into your WordPress uploads folder on your server and an attachment post type is created for you so that you can use it in your website. By default, the attachment's files saved on your server are not protected in any way, and anyone can type in their address and directly access or download them.

Now let's say you create a Private Post which you only want to share with certain people. WordPress let's you do this with the Password Protect feature. However if that post contains sensitive attachment files such as images, video, pdfs, etc. those attachments' files are not protected in any way and anyone that has or can obtain the addresses to these files can easily just directly access them and view or download them to their heart's content.

This is where Media Vault steps in.

= Protected Attachment Files =

Media Vault cordons off a section of your WordPress uploads folder and secures it, protecting all files within by passing requests for them through a powerful and flexible set of permission checks.

After activating the plugin, to protect attachment files with Media Vault you can either use the Media Uploader Admin page to upload new protected attachments, or, using bulk actions in your Media Library page, you can change file protection on multiple pre-existing attachments at once.

By default the only permission check that the plugin does on media files is that the user requesting them be logged in. You can change this default behavior from the 'Media Settings' page in the 'Settings' menu of the WordPress Admin.

= Safe Download Links =

Creating a cross-browser compatible download link for a file is a harder task than might be expected. Media Vault handles this for you, and it does so while preserving all the file security features discussed earlier like blocking downloads to people who should not have access to the file.

The download links are available through a simple shortcode that you can use in your post/page editor screen:

	[mv_dl_links ids="1,2,3"]

where 'ids' are the comma separated list of attachment ids you would like to make available for download in the list.


*Note:* Plugin comes with styles ready for WordPress [mp6](http://wordpress.org/plugins/mp6)!

== Installation ==

= Install Through your Blog's Admin =
*This sometimes does not to work on `localhost`, so if you're running your site off your own computer it's simpler to use the second method.*

1. Go to the 'Plugins' menu in WordPress and select 'Add New'.
1. Type 'Media Vault' in the Search Box and press the 'Search' button.
1. When you find 'Media Vault', click 'Install Now' and after reading it, click 'ok' on the little alert that pops up.
1. When the plugin finishes installing, simply click 'Activate Now'.
1. You can now go to the 'Media Settings' page under the 'Settings' menu to set the default permissions required for protected file access.

= Downloading from WordPress.org =

1. Clicking the big 'Download' button on the right on this page (wordpress.org/plugins/mediavault) will download the plugin's `zip` folder (`mediavault.zip`).
1. Upload this `zip` folder to your server; to the `/wp-content/plugins/` directory of the site you wish to install the plugin on.
1. Extract the contents of the `zip`. Once it is done you can delete the `mediavault.zip` file if you wish.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. You can now go to the 'Media Settings' page under the 'Settings' menu to set the default permissions required for protected file access.

== Frequently Asked Questions ==

= How do I toggle File Protection on an existing Attachment =

The plugin comes with two bulk actions that can be performed in the Media Library page in the WordPress Admin. On the Media Library page select the attachment or attachments you would like to manipulate by ticking the box next to their title. Then from the 'bulk options' dropdown select either the 'Add to Protected' or 'Remove from Protected' option and click the 'Apply' button next to the dropdown.

You can verify that the action took effect by looking at the Media Vault column in the Media Library list table. It will display when an attachment's files are protected as well as the permissions set on the particular attachment.

== Screenshots ==

1. The WordPress Media Upload page with Media Vault file protection activated.
2. An example of the access denied prompt produced by a custom file access restriction implemented very simply using Media Vault. (more on this custom implementation when the next version of the plugin is ready)
3. The WordPress Media Upload page with Media Vault file protection activated (in mp6)

== Changelog ==



== Upgrade Notice ==

= 0.6 =
This is the original release version.