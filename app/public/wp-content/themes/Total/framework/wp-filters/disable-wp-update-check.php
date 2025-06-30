<?php
/**
 * Prevent WP update checks
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_disable_wporg_theme_update_check( $parsed_args, $url ) {

	if ( false === strpos( $url, 'api.wordpress.org/themes/update-check' ) ) {
		return $parsed_args;
	}

	if ( isset( $parsed_args['body']['themes'] ) ) {

		$themes = json_decode( $parsed_args['body']['themes'] );

		if ( $parent = get_option( 'template' ) ) {
			unset( $themes->themes->$parent );
		} elseif ( $child = get_option( 'stylesheet' ) ) {
			unset( $themes->themes->$child );
		}

		$parsed_args['body']['themes'] = wp_json_encode( $themes );

	}

	return $parsed_args;

}
add_filter( 'http_request_args', 'wpex_disable_wporg_theme_update_check', 5, 2 );