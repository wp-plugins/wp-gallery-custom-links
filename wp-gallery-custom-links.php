<?php
/*
Plugin Name: WP Gallery Custom Links
Plugin URI: http://www.fourlightsweb.com/wordpress-plugins/wp-gallery-custom-links/
Description: Specifiy custom links for WordPress gallery images (instead of attachment or file only).
Version: 1.0.5
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
	} // End function init()
	
	public static function apply_filter_attachment_fields_to_edit( $form_fields, $post ) {
		$form_fields['gallery_link_url'] = array(
			'label' => __( 'Gallery Link URL' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, '_gallery_link_url', true ),
			'helps' => 'Will replace "Image File" or "Attachment Page" link for this thumbnail in the gallery.'
		);
		return $form_fields;
	} // End function apply_filter_attachment_fields_to_edit()
	
	public static function apply_filter_attachment_fields_to_save( $post, $attachment ) {
		if( isset( $attachment['gallery_link_url'] ) ) {
			update_post_meta( $post['ID'], '_gallery_link_url', $attachment['gallery_link_url'] );
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
			
			// See if we have a custom url for this attachment image
			$attachment_meta = get_post_meta( $attachment_id, '_gallery_link_url', true );
			if( $attachment_meta ) {
				$link = $attachment_meta;
			}

			if( $link != '' ) {
				// If we have a non-blank custom url, swap out the href on the image
				// in the generated gallery code with the custom url
				
				// Replace the attachment href
				$needle = get_attachment_link( $attachment_id );
				$output = self::replace_link( $needle, $link, $output );
				
				// Replace the file href
				list( $needle ) = wp_get_attachment_image_src( $attachment_id, '' );
				$output = self::replace_link( $needle, $link, $output );

				// Replace all possible file sizes - some themes etc.
				// may use sizes other than the full version
				$attachment_metadata = wp_get_attachment_metadata( $attachment_id );
				$attachment_sizes = $attachment_metadata['sizes'];
				foreach( $attachment_sizes as $attachment_size => $attachment_info ) {
					list( $needle ) = wp_get_attachment_image_src( $attachment_id, $attachment_size );
					$output = self::replace_link( $needle, $link, $output );
				}				
			} // End if we have a custom url to swap in
		} // End foreach post attachment
		
		// Javascript to override lightboxes
		$output .= "<script type=\"text/javascript\">\n";
		$output .= "/* <![CDATA[ */\n";
		$output .= "jQuery(document).ready(function () {\n";
		$output .= "	jQuery('a.no-lightbox').unbind();\n";
		$output .= "	jQuery('a.no-lightbox').off();\n";
		$output .= "	jQuery('a.no-lightbox').click(function(){window.location=this.href; return false;});";
		$output .= "});";
		$output .= "/* ]]> */\n";
		$output .= "</script>";

		return $output;
	} // End function apply_filter_post_gallery()
	
	private static function replace_link( $default_link, $custom_link, $output ) {
		// Build the regex for matching/replacing
		$needle = preg_quote( $default_link );
		$needle = str_replace( '/', '\/', $needle );
		$needle = '/href\s*=\s*["\']' . $needle . '["\']/';
		if( preg_match( $needle, $output ) > 0 ) {
			// If we found the href to swap out, perform the href replacement,
			// and add some javascript to prevent lightboxes from kicking in
			$output = preg_replace( $needle, 'href="' . $custom_link . '"', $output );
			
			// Clean up the new href for regex-ing
			$custom_link = preg_quote( $custom_link );
			$custom_link = str_replace( '/', '\/', $custom_link );
			
			// Add a class to the link so we can manipulate it with
			// javascript later
			if( preg_match( '/<a[^>]*href="' . $custom_link . '"[^>]*class\s*=\s*["\'][^"\']*["\'][^>]*>/', $output ) > 0 ) {
				// href comes before class
				$output = preg_replace( '/(<a[^>]*href="' . $custom_link . '"[^>]*class\s*=\s*["\'][^"\']*)(["\'][^>]*>)/', '$1no-lightbox$2', $output );
			} elseif( preg_match( '/<a[^>]*class\s*=\s*["\'][^"\']*["\'][^>]*href="' . $custom_link . '"[^>]*>/', $output ) > 0 ) {
				// href comes after class
				$output = preg_replace( '/(<a[^>]*class\s*=\s*["\'][^"\']*)(["\'][^>]*href="' . $custom_link . '"[^>]*>)/', '$1no-lightbox$2', $output );
			} else {
				// No previous class
				$output = preg_replace( '/(<a[^>]*href="' . $custom_link . '"[^>]*)(>)/', '$1 class="no-lightbox"$2', $output );
			} // End if we have a class on the a tag or not
		} // End if we found the attachment to replace in the output
		
		return $output;
	} // End function replace_link()
	
} // End class WPGalleryCustomLinks