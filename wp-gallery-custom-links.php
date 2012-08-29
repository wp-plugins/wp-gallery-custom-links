<?php
/*
Plugin Name: WP Gallery Custom Links
Plugin URI: http://www.fourlightsweb.com/wordpress-plugins/wp-gallery-custom-links/
Description: Specifiy custom links for WordPress gallery images (instead of attachment or file only).
Version: 1.2.2
Author: Four Lights Web Development
Author URI: http://www.fourlightsweb.com
License: GPL2

Copyright 2012 Four Lights Web Development, LLC. (email : development@fourlightsweb.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'init', array( 'WPGalleryCustomLinks', 'init' ) );

class WPGalleryCustomLinks {
	// We will always be "replacing" the gallery shortcode function
	// via the post_gallery filter, although usually it will be with
	// the gallery shortcode function content itself unless there's
	// an additional theme filter or something.
	// [gallery] ->
	// 		$GLOBALS['shortcode_tags']['gallery'] ->
	//			apply_filter('post_gallery') *
	//			apply_filter('post_gallery') (first call) ->
	//				$GLOBALS['shortcode_tags']['gallery'] ->
	//					apply_filter('post_gallery') *
	//					apply_filter('post_gallery') (second call, simply returns output passed in)
	//			return "filter" $output to original $GLOBALS['shortcode_tags']['gallery'] call
	private static $first_call = true;
	
	public static function init() {	
		// Add the filter for editing the custom url field
		add_filter( 'attachment_fields_to_edit', array( 'WPGalleryCustomLinks', 'apply_filter_attachment_fields_to_edit' ), null, 2);
		
		// Add the filter for saving the custom url field
		add_filter( 'attachment_fields_to_save', array( 'WPGalleryCustomLinks', 'apply_filter_attachment_fields_to_save' ), null , 2);
		
		// Add the filter for when the post_gallery is written out
		add_filter( 'post_gallery', array( 'WPGalleryCustomLinks', 'apply_filter_post_gallery' ), 999, 2 );
		
		// Require the javascript to disable lightbox
		wp_enqueue_script(
			'wp-gallery-custom-links-js',
			plugins_url( '/wp-gallery-custom-links.js', __FILE__ ),
			array( 'jquery' ),
			'1.0',
			true
		);
	} // End function init()
	
	public static function apply_filter_attachment_fields_to_edit( $form_fields, $post ) {
		$form_fields['gallery_link_url'] = array(
			'label' => __( 'Gallery Link URL' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, '_gallery_link_url', true ),
			'helps' => 'Will replace "Image File" or "Attachment Page" link for this image in the gallery.'
		);
		$target_value = get_post_meta( $post->ID, '_gallery_link_target', true );
		$form_fields['gallery_link_target'] = array(
			'label' => __( 'Gallery Link Target' ),
			'input'	=> 'html',
			'html'	=> '
				<select name="attachments['.$post->ID.'][gallery_link_target]" id="attachments['.$post->ID.'][gallery_link_target]">
					<option value="">Same Window</option>
					<option value="_blank"'.($target_value == '_blank' ? ' selected="selected"' : '').'>New Window</option>
				</select>'
		);
		return $form_fields;
	} // End function apply_filter_attachment_fields_to_edit()
	
	public static function apply_filter_attachment_fields_to_save( $post, $attachment ) {
		if( isset( $attachment['gallery_link_url'] ) ) {
			update_post_meta( $post['ID'], '_gallery_link_url', $attachment['gallery_link_url'] );
		}
		if( isset( $attachment['gallery_link_target'] ) ) {
			update_post_meta( $post['ID'], '_gallery_link_target', $attachment['gallery_link_target'] );
		}
		return $post;
	} // End function apply_filter_attachment_fields_to_save() 
	
	public static function apply_filter_post_gallery( $output, $attr ) {
		global $post;
		
		// Get the shortcode attributes
		extract( shortcode_atts( array(), $attr ) );
		
		// Determine what our postID for attachments is - either
		// from our shortcode attr or from $post->ID. If we don't
		// have one from either of those places...something weird
		// is going on, so just bail. 
		if( isset( $attr['id'] ) ) {
			$post_id = intval( $attr['id'] );
		} else if( $post ) {
			$post_id = intval( $post->ID );
		} else {
			return ' ';
		}
		
		if( self::$first_call ) {
			// Our first run, so the gallery function thinks it's being
			// overwritten. Set the variable to prevent actual endless
			// overwriting later.
			self::$first_call = false;
		} else {
			// We're inside our dynamically called gallery function and
			// don't want to spiral into an endless loop, so return
			// whatever output we were given so the gallery function
			// will run as normal. Also reset the first call variable
			// so if there's two galleries or something it will run the
			// same next time.
			self::$first_call = true;
			return $output;
		}

		// Get the normal gallery shortcode function
		if ( isset( $GLOBALS['shortcode_tags'] ) && isset( $GLOBALS['shortcode_tags']['gallery'] ) ) {
			$gallery_shortcode_function = $GLOBALS['shortcode_tags']['gallery'];
		}
		
		// Run whatever gallery shortcode function has been set up, 
		// default, theme-specified or whatever
		$output = call_user_func( $gallery_shortcode_function, $attr );		
		
		// Get the attachments for this post
		$attachments = get_children( array( 'post_parent' => $post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
		foreach ( $attachments as $attachment_id => $attachment ) {
			$link = '';
			$target = '';
			
			// See if we have a custom url for this attachment image
			$attachment_meta = get_post_meta( $attachment_id, '_gallery_link_url', true );
			if( $attachment_meta ) {
				$link = $attachment_meta;
			}
			// See if we have a target for this attachment image
			$attachment_meta = get_post_meta( $attachment_id, '_gallery_link_target', true );
			if( $attachment_meta ) {
				$target = $attachment_meta;
			}
			
			if( $link != '' || $target != '' ) {
				// Replace the attachment href
				$needle = get_attachment_link( $attachment_id );
				$output = self::replace_link( $needle, $link, $target, $output );

				// Replace the file href
				list( $needle ) = wp_get_attachment_image_src( $attachment_id, '' );
				$output = self::replace_link( $needle, $link, $target, $output );

				// Replace all possible file sizes - some themes etc.
				// may use sizes other than the full version
				$attachment_metadata = wp_get_attachment_metadata( $attachment_id );
				if( $attachment_metadata !== false && isset( $attachment_metadata['sizes'] ) ) {
					$attachment_sizes = $attachment_metadata['sizes'];
					if( is_array( $attachment_sizes ) && count( $attachment_sizes ) > 0 ) {
						foreach( $attachment_sizes as $attachment_size => $attachment_info ) {
							list( $needle ) = wp_get_attachment_image_src( $attachment_id, $attachment_size );
							$output = self::replace_link( $needle, $link, $target, $output );
						} // End of foreach attachment size
					} // End if we have attachment sizes
				} // End if we have attachment metadata (specifically sizes)
			} // End if we have a link to swap in or a target to add
			
		} // End foreach post attachment

		return $output;
	} // End function apply_filter_post_gallery()
	
	private static function replace_link( $default_link, $custom_link, $target, $output ) {
		// Build the regex for matching/replacing
		$needle = preg_quote( $default_link );
		$needle = str_replace( '/', '\/', $needle );
		$needle = '/href\s*=\s*["\']' . $needle . '["\']/';
		if( preg_match( $needle, $output ) > 0 ) {			
			// Custom Target
			if( $target != '' ) {
				// Replace the link target
				$output = self::add_target( $default_link, $target, $output );
				
				// Add a class to the link so we can manipulate it with
				// javascript later
				$output = self::add_class( $default_link, 'set-target', $output );
			}
			
			// Custom Link
			if( $custom_link != '' ) {
				// If we found the href to swap out, perform the href replacement,
				// and add some javascript to prevent lightboxes from kicking in
				$output = preg_replace( $needle, 'href="' . $custom_link . '"', $output );
				
				// Add a class to the link so we can manipulate it with
				// javascript later
				$output = self::add_class( $custom_link, 'no-lightbox', $output );
			} // End if we have a custom link to swap in
		} // End if we found the attachment to replace in the output
		
		return $output;
	} // End function replace_link()
	
	private static function add_class( $needle, $class, $output ) {
		// Clean up our needle for regexing
		$needle = preg_quote( $needle );
		$needle = str_replace( '/', '\/', $needle );
		
		// Add a class to the link so we can manipulate it with
		// javascript later
		if( preg_match( '/<a[^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*class\s*=\s*["\'][^"\']*["\'][^>]*>/', $output ) > 0 ) {
			// href comes before class
			$output = preg_replace( '/(<a[^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*class\s*=\s*["\'][^"\']*)(["\'][^>]*>)/', '$1 '.$class.'$2', $output );
		} elseif( preg_match( '/<a[^>]*class\s*=\s*["\'][^"\']*["\'][^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*>/', $output ) > 0 ) {
			// href comes after class
			$output = preg_replace( '/(<a[^>]*class\s*=\s*["\'][^"\']*)(["\'][^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*>)/', '$1 '.$class.'$2', $output );
		} else {
			// No previous class
			$output = preg_replace( '/(<a[^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*)(>)/', '$1 class="'.$class.'"$2', $output );
		} // End if we have a class on the a tag or not
		
		return $output;
	} // End function add_class()
	
	private static function add_target( $needle, $target, $output ) {
		// Clean up our needle for regexing
		$needle = preg_quote( $needle );
		$needle = str_replace( '/', '\/', $needle );
		
		// Add a target to the link (or overwrite what's there)
		if( preg_match( '/<a[^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*target\s*=\s*["\'][^"\']*["\'][^>]*>/', $output ) > 0 ) {
			// href comes before target
			$output = preg_replace( '/(<a[^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*target\s*=\s*["\'])[^"\']*(["\'][^>]*>)/', '$1'.$target.'$2', $output );
		} elseif( preg_match( '/<a[^>]*target\s*=\s*["\'][^"\']*["\'][^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*>/', $output ) > 0 ) {
			// href comes after target
			$output = preg_replace( '/(<a[^>]*target\s*=\s*["\'])[^"\']*(["\'][^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*>)/', '$1'.$target.'$2', $output );
		} else {
			// No previous target
			$output = preg_replace( '/(<a[^>]*href\s*=\s*["\']' . $needle . '["\'][^>]*)(>)/', '$1 target="'.$target.'"$2', $output );
		} // End if we have a class on the a tag or not
		
		return $output;
	} // End function add_target()
	
} // End class WPGalleryCustomLinks