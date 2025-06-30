<?php
/**
 * Helper functions for local scrolling.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Elements that support local scroll.
 *
 * @since 5.0
 * @return bool Return bool or true
 */
function wpex_get_local_scroll_targets() {
	$targets = 'li.local-scroll a, a.local-scroll, .local-scroll-link, .local-scroll-link > a';
	$targets = apply_filters( 'wpex_local_scroll_targets', $targets );
	return wp_strip_all_tags( $targets );
}

/**
 * Check if local scroll menu highlight is enabled.
 *
 * @since 5.0
 * @return bool Return bool or true
 */
function wpex_has_local_scroll_menu_highlight() {
	$check = wp_validate_boolean( get_theme_mod( 'local_scroll_highlight', true ) );
	return (bool) apply_filters( 'wpex_has_local_scroll_menu_highlight', $check );
}

/**
 * Check if local scroll menu highlight is enabled.
 *
 * @since 5.0
 * @return bool Return bool or true
 */
function wpex_has_local_scroll_on_load() {
	$check = wp_validate_boolean( get_theme_mod( 'scroll_to_hash', true ) );
	return (bool) apply_filters( 'wpex_has_local_scroll_on_load', $check );
}

/**
 * Check if the url hash should update when clicking local scroll links.
 *
 * @since 5.0
 * @return bool Return bool
 */
function wpex_has_local_scroll_hash_update() {
	$check = wp_validate_boolean( get_theme_mod( 'local_scroll_update_hash', false ) );
	return (bool) apply_filters( 'wpex_has_local_scroll_hash_update', $check );
}

/**
 * Return local scroll on load timeout.
 *
 * @since 5.0
 * @return int Return integer or 500
 */
function wpex_get_local_scroll_on_load_timeout() {
	$timeout = ( $timeout = get_theme_mod( 'scroll_to_hash_timeout' ) ) ? $timeout : 500;
	$timeout = apply_filters( 'wpex_local_scroll_on_load_timeout', $timeout );
	return absint( $timeout );
}

/**
 * Return local scroll easing value.
 *
 * @since 5.0
 * @return string Return string or easeInOutExpo
 */
function wpex_get_local_scroll_easing() {
	$easing = 'easeInOutExpo';
	$easing = apply_filters( 'wpex_local_scroll_easing', $easing );
	if ( empty( $easing ) || 'false' === $easing ) {
		$easing = 'swing';
	}
	return wp_strip_all_tags( $easing );
}

/**
 * Return local scroll speed.
 *
 * @since 5.0
 * @return int Return integer or 1000
 */
function wpex_get_local_scroll_speed() {
	$speed = get_theme_mod( 'local_scroll_speed' );
	$speed = ( $speed || '0' === $speed ) ? absint( $speed ) : 1000;
	$speed = apply_filters( 'wpex_local_scroll_speed', $speed );
	return absint( $speed );
}
