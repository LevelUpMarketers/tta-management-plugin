<?php
/**
 * Helper functions for site breadcrumbs.
 *
 * @package Total WordPress Theme
 * @subpackage Portfolio Functions
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns correct breadcrumbs positon.
 *
 * @since 5.0
 */
function wpex_breadcrumbs_position() {

	// Get position
	$position = ( $position = get_theme_mod( 'breadcrumbs_position' ) ) ? $position : 'page_header_aside';

	// Check renamed styles
	if ( 'absolute' === $position || 'default' === $position ) {
		$position = 'page_header_aside';
	}

	// Apply filters
	$position = (string) apply_filters( 'wpex_breadcrumbs_position', $position );

	// If position is empty, let's assume it's custom
	if ( empty( $position ) ) {
		$position = 'custom';
	}

	// Return
	return $position;

}

/**
 * Check if breadcrumbs should be contained or not.
 *
 * @since 5.0
 */
function wpex_has_breadcrumbs_container() {

	$check = false;

	$position = wpex_breadcrumbs_position();

	if ( 'header_after' == $position || 'page_header_after' == $position ) {
		$check = true;
	}

	return apply_filters( 'wpex_has_breadcrumbs_container', $check );

}