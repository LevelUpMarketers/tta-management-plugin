<?php
/**
 * Grid frontend functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the correct classname for any specific column grid.
 *
 * @since 1.0.0
 */
function wpex_grid_class( $col = '4' ) {
	if ( is_array( $col ) && count( $col ) > 1 ) {
		$class = 'span_1_of_' . sanitize_html_class( $col[ 'd' ] );
		$responsive_columns = $col;
		unset( $responsive_columns[ 'd'] );
		foreach ( $responsive_columns as $key => $val ) {
			if ( $val ) {
				$class .= ' span_1_of_' . sanitize_html_class( $val ) . '_' . sanitize_html_class( $key );
			}
		}
	} else {
		$class = 'span_1_of_' . sanitize_html_class( $col );
	}
	return apply_filters( 'wpex_grid_class', $class );
}

/**
 * Returns the correct gap class.
 *
 * @since 1.0.0
 */
function wpex_gap_class( $gap = '' ) {
	if ( '0px' === $gap || '0' === $gap ) {
		$gap = 'none';
	}
	return apply_filters( 'wpex_gap_class', 'gap-' . sanitize_html_class( $gap ) );
}
