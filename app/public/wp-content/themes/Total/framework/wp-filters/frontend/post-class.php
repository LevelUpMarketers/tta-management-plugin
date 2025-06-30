<?php
/**
 * Adds custom classes to the posts class
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_post_class( $classes, $class = '', $post_id = '' ) {

	if ( ! $post_id ) {
		return $classes;
	}

	// Get post type
	$type = get_post_type( $post_id );

	// Not needed here
	if ( 'forum' == $type || 'topic' == $type ) {
		return $classes;
	}

	// Add entry class
	$classes[] = 'entry';

	// Add media class
	if ( wpex_post_has_media( $post_id ) ) {
		$classes[] = 'has-media';
	} else {
		$classes[] = 'no-media';
	}

	// Custom link class
	if ( wpex_get_post_redirect_link( $post_id ) ) {
		$classes[] = 'has-redirect';
	}

	// Sticky
	if ( is_sticky( $post_id ) ) {
		$classes[] = 'sticky';
	}

	// Return classes
	return $classes;
}
add_filter( 'post_class', 'wpex_post_class', 10, 3 );