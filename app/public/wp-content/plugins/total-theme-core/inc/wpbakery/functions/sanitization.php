<?php
/**
 * Sanitization functions.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize border radius.
 */
function vcex_sanitize_border_radius( $input = '' ) {
	switch ( $input ) {
		case '5px':
			$input = 'rounded-sm';
			break;
		case '10px':
			$input = 'rounded';
			break;
		case '15px':
			$input = 'rounded-md';
			break;
		case '20px':
			$input = 'rounded-lg';
			break;
		case '9999px':
		case '50%':
			$input = 'rounded-full';
			break;
	}
	return sanitize_html_class( $input );
}

/**
 * Sanitize margin class.
 */
function vcex_sanitize_margin_class( $margin = '', $prefix = '' ) {
	if ( function_exists( 'wpex_utl_margins' ) && array_key_exists( $margin, wpex_utl_margins() ) ) {
		$margin = absint( $margin );
	}
	return sanitize_html_class( $prefix . $margin );
}
