<?php
/**
 * Blog single post gallery format media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( get_theme_mod( 'blog_post_image_lightbox', false ) || wpex_gallery_is_lightbox_enabled() ) {
	$lightbox_enabled = true;
} else {
	$lightbox_enabled = false;
}

$args = array(
	'lightbox'       => $lightbox_enabled,
	'lightbox_title' => apply_filters( 'wpex_blog_gallery_lightbox_title', false ),
	'slider_data'    => wpex_get_post_slider_settings( array(
		'filter_tag' => 'wpex_blog_slider_data_atrributes',
	) ),
	'thumbnail_args' => wpex_get_blog_post_thumbnail_args(),
);

wpex_post_media_gallery_slider( get_the_ID(), $args );