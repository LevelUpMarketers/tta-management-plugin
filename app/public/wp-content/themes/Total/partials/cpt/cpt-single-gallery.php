<?php
/**
 * CPT single gallery
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();

$post_type = get_post_type( $post_id );

$args = array(
	'before'         => '<div class="gallery-format-post-slider wpex-clr">',
	'after'          => '</div>',
	'lightbox'       => wpex_gallery_is_lightbox_enabled( $post_id ) ? true : false,
	'lightbox_title' => apply_filters( 'wpex_cpt_gallery_lightbox_title', false ),
	'thumbnails'     => apply_filters( 'wpex_' . $post_type . '_gallery_slider_has_thumbnails', true ),
	'thumbnail_args' => array(
		'size'          => $post_type . '_single',
		'apply_filters' => 'wpex_' . $post_type . '_single_thumbnail_args',
	),
);

echo wpex_get_post_media_gallery_slider( $post_id, $args );