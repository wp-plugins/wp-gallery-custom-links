=== WP Gallery Custom Links ===
Contributors: fourlightsweb
Donate link: http://www.fourlightsweb.com/wordpress-plugins/wp-gallery-custom-links/#donate
Tags: gallery links, gallery link, gallery
Requires at least: 3.3.2
Tested up to: 3.5
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Specifiy custom links for WordPress gallery images (instead of attachment or file only).

== Description ==

If you've ever had a WordPress gallery of staff, product, or other images and needed
to link them to other pages but couldn't, this plugin is for you!
 
This plugin adds a "Gallery Link URL" field when editing post images. If the image
is included in a gallery, the "Gallery Link URL" value will be used as the link on
the image instead of the raw file or the attachment post.  There is also an option
to open gallery images in a new window.  If you want to use the same images in a
different gallery but not use the custom links, you can add
'ignore_gallery_link_urls="true"' to the [gallery] shortcode:

[gallery ignore_gallery_link_urls="true"]

If you want all the custom-linked images in a gallery to keep their Lightbox or
other OnClick events, you can add 'preserve_click_events="true"' to the [gallery]
shortcode:

[gallery preserve_click_events="true"]

It's designed to work even if customizations have been made via the
post_gallery filter; instead of replacing the entire post_gallery function, it
calls the normal function and simply replaces the link hrefs in the generated
output.  Javascript is also in place to attempt to remove any lightbox
functionality on the custom link and allow it to function as a regular link.


== Installation ==

1. Upload the 'wp-gallery-custom-links' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Will this plugin work with my theme's galleries? =

Possibly.  WP Gallery Custom Links plugin was designed for use with 
1) WordPress's [gallery] shortcode and 2) images
attached to the post/page.  Some themes use these features, and others
have their own proprietary way of saving gallery images and drawing out the gallery.
Provided your theme meets the criteria above, the plugin *should* work with it.

= Will this plugin work with NextGen galleries? =

No, this plugin is *not* compatible with NextGen galleries.  WP Gallery Custom Links was
designed for use with 1) WordPress's [gallery] shortcode and 2) images
attached to the post/page.  NextGen galleries uses its own [nggallery] etc. shortcodes
that function outside of the WordPress [gallery] shortcode.

== Screenshots ==

1. The additional WP Gallery Custom Link fields.

== Changelog ==

= 1.4.0 =
* By popular demand, added an option to remove or keep Lightbox and other OnClick
events ("remove" by default).
* Added support for the "preserve_click_events" gallery shortcode attribute to
set all custom-linked images in a gallery to "preserve" its OnClick events.

= 1.3.0 =
* Added support for the "ignore_gallery_link_urls" gallery shortcode attribute to 
ignore custom links on a gallery and use the normal file/attachment setting.
* Added support for IDs in the "include" gallery shortcode attribute that aren't 
directly attached to the post.

= 1.2.2 =
* Moved javascript to a separate file so jquery could be required as a dependency.

= 1.2.1 =
* Fixed a bug where javascript hover effects were not working properly on images.

= 1.2.0 =
* By popular demand, added an option to open gallery image links in a new window.

= 1.1.2 =
* Added a check to prevent javascript from showing up in feeds.

= 1.1.1 =
* Fixed an error that occurred when an images were small enough to only have one size
* Tested with WordPress 3.4

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

= 1.4.0 =
* By popular demand, added an option to remove or keep Lightbox and other OnClick
events ("remove" by default).
* Added support for the "preserve_click_events" gallery shortcode attribute to
set all custom-linked images in a gallery to "preserve" its OnClick events.

= 1.3.0 =
* Added support for the "ignore_gallery_link_urls" gallery shortcode attribute to 
ignore custom links on a gallery and use the normal file/attachment setting.
* Added support for IDs in the "include" gallery shortcode attribute that aren't 
directly attached to the post.

= 1.2.2 =
* Moved javascript to a separate file so jquery could be required as a dependency.

= 1.2.1 =
* Fixed a bug where javascript hover effects were not working properly on images.

= 1.2.0 =
* By popular demand, added an option to open gallery image links in a new window.

= 1.1.2 =
* Added a check to prevent javascript from showing up in feeds.

= 1.1.1 =
* Fixed an error that occurred when an images were small enough to only have one size
* Tested with WordPress 3.4

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