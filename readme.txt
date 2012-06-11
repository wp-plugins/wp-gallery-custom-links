=== WP Gallery Custom Links ===
Contributors: fourlightsweb
Donate link: http://www.fourlightsweb.com/wordpress-plugins/wp-gallery-custom-links/#donate
Tags: gallery links, gallery link, gallery
Requires at least: 3.3.2
Tested up to: 3.3.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Specifiy custom links for WordPress gallery images (instead of attachment or file only).

== Description ==

This plugin adds a "Gallery Link URL" field when editing post images. If the image
is included in a gallery, the "Gallery Link URL" value will be used as the link on
the image instead of the raw file or the attachment post. It's designed to work
even if customizations have been made via the post_gallery filter; instead of
replacing the entire post_gallery function, it calls the normal function
and simply replaces the link hrefs in the generated output.  Javascript is 
also in place to attempt to remove any lightbox functionality on the custom link
and allow it to function as a regular link.

== Installation ==

1. Upload the 'wp-gallery-custom-links' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= When will you have FAQs? =

As soon as someone asks me something. :)

== Screenshots ==

1. The added "Gallery Link URL" field.

== Changelog ==

= 1.1.0 =
* Added support for replacing links to all sizes of an uploaded image instead of the full version only
* Replaced lightbox removal with a more advanced javascript method

= 1.0.5 =
* Moving the $post_id code above first_call to avoid messing that up if a return does occur due to a missing post_id

= 1.0.4 =
* The "id" attribute of the gallery shortcode is now supported

= 1.0.3 =
* Added a check to return a simple space in the event $post is undefined

= 1.0.2 =
* Fixed an issue with two undefined variables

= 1.0.1 =
* Changed priority on post_gallery filter from 10 to 999 to help ensure it runs after anything else

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
* Added support for replacing links to all sizes of an uploaded image instead of the full version only
* Replaced lightbox removal with a more advanced javascript method

= 1.0.5 =
* Moving the $post_id code above first_call to avoid messing that up if a return does occur due to a missing post_id

= 1.0.4 =
* The "id" attribute of the gallery shortcode is now supported

= 1.0.3 =
* Added a check to return a simple space in the event $post is undefined

= 1.0.2 =
* Fixed an issue with two undefined variables

= 1.0.1 =
* Changed priority on post_gallery filter from 10 to 999 to help ensure it runs after anything else

= 1.0.0 =
* Initial release