<?php
/**
 * Staff single gallery
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( apply_filters( 'wpex_single_staff_media_lightbox', true ) && wpex_gallery_is_lightbox_enabled() ) {
	$lightbox_enabled = true;
} else {
	$lightbox_enabled = false;
}

echo wpex_get_post_media_gallery_slider( get_the_ID(), array(
	'before'         => '<div id="staff-single-gallery" class="staff-post-slider wpex-clr">',
	'after'          => '</div>',
	'lightbox'       => $lightbox_enabled,
	'lightbox_title' => apply_filters( 'wpex_staff_gallery_lightbox_title', false ),
	'slider_data'    => array(
		'filter_tag' => 'wpex_staff_single_gallery',
	),
	'thumbnail_args' => array(
		'size'          => 'staff_post',
		'class'         => 'staff-single-media-img',
		'apply_filters' => 'wpex_get_staff_post_thumbnail_args',
	),
) );
