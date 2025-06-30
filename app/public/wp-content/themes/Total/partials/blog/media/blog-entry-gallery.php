<?php
/**
 * Blog entry gallery
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$entry_style = wpex_blog_entry_style();

if ( get_theme_mod( 'blog_entry_image_lightbox' ) || wpex_gallery_is_lightbox_enabled() ) {
	$lightbox_enabled = true;
} else {
	$lightbox_enabled = false;
}

$slider_data = wpex_get_post_slider_settings( array(
	'filter_tag' => 'wpex_blog_slider_data_atrributes',
) );

if ( 'grid-entry-style' === $entry_style ) {
	$slider_data['auto-height'] = 'false';
}

$args = array(
	'lightbox'       => $lightbox_enabled,
	'lightbox_title' => apply_filters( 'wpex_blog_gallery_lightbox_title', false ),
	'slider_data'    => $slider_data,
	'thumbnail_args' => wpex_get_blog_entry_thumbnail_args(),
	'class'          => wpex_get_entry_image_animation_classes(),
);

if ( 'large-image-entry-style' !== $entry_style ) {
	$args['thumbnails'] = false; // force disable thumbnails on specific entry styles
}

wpex_post_media_gallery_slider( get_the_ID(), $args );
