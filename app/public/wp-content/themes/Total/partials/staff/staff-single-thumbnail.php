<?php
/**
 * Staff single thumbnail
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

$thumb_args = array();

if ( apply_filters( 'wpex_single_staff_media_lightbox', true ) ) {

	wpex_enqueue_lightbox_scripts();

	$thumb_args['before'] = '<a href="' .  wpex_get_lightbox_image() . '" class="wpex-lightbox">';
	$thumb_args['after']  = '</a>';

}

echo wpex_get_staff_post_thumbnail( $thumb_args );
