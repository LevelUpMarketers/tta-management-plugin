<?php
/**
 * Portfolio single gallery
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( apply_filters( 'wpex_single_portfolio_media_lightbox', true ) && wpex_gallery_is_lightbox_enabled() ) {
	$lightbox_enabled = true;
} else {
	$lightbox_enabled = false;
}

$args = array(
	'before'         => '<div id="portfolio-single-gallery" class="portfolio-post-slider wpex-clr">',
	'after'          => '</div>',
	'lightbox'       => $lightbox_enabled,
	'lightbox_title' => apply_filters( 'wpex_portfolio_gallery_lightbox_title', false ),
	'slider_data'    => array(
		'filter_tag' => 'wpex_portfolio_single_gallery',
	),
	'thumbnail_args' => array(
		'size'          => 'portfolio_post',
		'class'         => 'portfolio-single-media-img',
		'apply_filters' => 'wpex_get_portfolio_post_thumbnail_args',
	),
);

echo wpex_get_post_media_gallery_slider( get_the_ID(), $args );