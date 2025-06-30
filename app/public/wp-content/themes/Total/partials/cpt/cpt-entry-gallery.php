<?php
/**
 * CTP entry gallery
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

$type = get_post_type();

if ( apply_filters( 'wpex_' . $type . '_entry_media_lightbox', true ) && wpex_gallery_is_lightbox_enabled() ) {
	$lightbox_enabled = true;
} else {
	$lightbox_enabled = false;
}

$args = array(
	'lightbox'       => $lightbox_enabled,
	'lightbox_title' => apply_filters( 'wpex_cpt_gallery_lightbox_title', false ),
	'slider_data'    => array(
		'filter_tag' => 'wpex_' . $type . '_entry_gallery',
	),
	'thumbnail_args' => array(
		'size'          => $type . '_archive',
		'apply_filters' => 'wpex_' . $type . '_entry_thumbnail_args',
	),
);

wpex_post_media_gallery_slider( get_the_ID(), $args );
