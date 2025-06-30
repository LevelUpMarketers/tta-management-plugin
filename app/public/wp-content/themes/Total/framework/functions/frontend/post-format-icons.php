<?php
/**
 * Post format icons.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns correct post format icon classes.
 *
 * @since 3.6.0
 */
function wpex_get_post_format_icon( $format = '' ) {

	// Get post format
	$format = $format ? $format : get_post_format();

	// Video
	switch ( $format ) {
		case 'video':
			$icon = 'ticon ticon-video-camera';
			break;
		case 'audio':
			$icon = 'ticon ticon-music';
			break;
		case 'gallery':
			$icon = 'ticon ticon-file-photo-o';
			break;
		case 'quote':
			$icon = 'ticon ticon-quote-left';
			break;
		default:
			$icon = 'ticon ticon-file-text-o';
			break;
	}

	// Apply filters for child theme editing and return
	return apply_filters( 'wpex_post_format_icon', $icon );
}

/**
 * Output post format icon class.
 *
 * @since 1.4.0
 */
function wpex_post_format_icon( $format ) {
	echo wpex_get_post_format_icon( $format );
}