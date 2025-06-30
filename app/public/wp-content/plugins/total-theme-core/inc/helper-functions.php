<?php
/**
 * Helper Functions
 *
 * @package Total Theme Core
 * @subpackage inc
 * @version 1.0
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

/**
 * Check current request type.
 *
 * @since 5.0
 */
function is_request( $type ) {
	switch ( $type ) {
		case 'admin':
			return is_admin();
		case 'ajax':
			return wp_doing_ajax();
		case 'cron':
			return defined( 'DOING_CRON' );
		case 'frontend':
			return ( ! is_admin() || wp_doing_ajax() );
	}
}

/**
 * Check if a specific theme mod is enabled.
 *
 * @since 1.0
 */
function is_mod_enabled( $mod ) {
	return ( $mod && 'off' !== $mod ) ? true : false;
}

/**
 * Sanitize data through Total theme Sanitization class.
 *
 * @since 1.0
 */
function sanitize_data( $data = '', $type = '' ) {
	if ( function_exists( 'wpex_sanitize_data' ) ) {
		return wpex_sanitize_data( $data, $type );
	}
	return wp_strip_all_tags( $data );
}

/**
 * Cleans up an array, comma- or space-separated list of scalar values.
 *
 * @since 1.0
 *
 * @param array|string $list List of values.
 * @return array Sanitized array of values.
 */
function parse_list( $list ) {
    if ( ! is_array( $list ) ) {
        return preg_split( '/[\s,]+/', $list, -1, PREG_SPLIT_NO_EMPTY );
    }

    return $list;
}
