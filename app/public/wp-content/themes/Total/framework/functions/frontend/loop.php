<?php
/**
 * Loop functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the loop top class.
 *
 * @since 5.0
 */
function wpex_loop_top_class() {

	$classes = (array) apply_filters( 'wpex_loop_top_class', wpex_get_archive_grid_class() );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Set loop instance.
 *
 * @since 5.0
 */
function wpex_set_loop_instance( $instance = 'archive' ) {
	set_query_var( 'wpex_loop', $instance );
}

/**
 * Returns loop instance.
 *
 * @since 5.0
 */
function wpex_get_loop_instance() {
	$instance = get_query_var( 'wpex_loop' );
	if ( ! $instance ) {
		global $wpex_loop;
		if ( $wpex_loop ) {
			$instance = $wpex_loop;
		}
	}
	if ( empty( $instance ) ) {
		$instance = 'archive'; // fallback required.
	}
	return $instance;
}

/**
 * Set loop running total.
 *
 * @since 5.0
 */
function wpex_increment_loop_running_count() {
	$count = absint( get_query_var( 'wpex_loop_running_count' ) );
	$count = $count + 1;
	set_query_var( 'wpex_loop_running_count', intval( $count ) );
}

/**
 * Set loop counter.
 *
 * @since 5.0
 */
function wpex_set_loop_counter( $count = 0 ) {
	set_query_var( 'wpex_count', intval( $count ) );
}

/**
 * Returns loop counter.
 *
 * @since 5.0
 */
function wpex_get_loop_counter() {
	$count = get_query_var( 'wpex_count' );
	if ( ! $count ) {
		global $wpex_count;
		if ( $wpex_count ) {
			$count = $wpex_count;
		}
	}
	return (int) $count;
}

/**
 * Increase loop counter.
 *
 * @since 5.0
 */
function wpex_increment_loop_counter() {
	$count = intval( wpex_get_loop_counter() );
	$count = $count + 1;
	wpex_set_loop_counter( $count );
}

/**
 * Maybe reset loop counter.
 *
 * @since 5.0
 */
function wpex_maybe_reset_loop_counter( $check = '' ) {
	$check = intval( $check );
	if ( $check && $check === wpex_get_loop_counter() ) {
		wpex_set_loop_counter( 0 );
	}
}

/**
 * Clear loop query vars
 *
 * @since 5.0
 */
function wpex_reset_loop_query_vars() {
	set_query_var( 'wpex_loop', null );
	set_query_var( 'wpex_count', null );
	set_query_var( 'wpex_loop_running_count', null );
}