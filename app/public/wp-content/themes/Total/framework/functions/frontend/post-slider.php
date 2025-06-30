<?php
/**
 * Post Slider functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if a post has a slider defined
 *
 * @since 4.0
 */
function wpex_has_post_slider( $post_id = '' ) {
	$bool = wpex_get_post_slider_shortcode( $post_id ) ? true : false;
	return (bool) apply_filters( 'wpex_has_post_slider', $bool );
}

/**
 * Get correct post slider position
 *
 * @since 4.0
 */
function wpex_post_slider_position( $post_id = '' ) {

	// Default position is below the title
	$position = 'below_title';

	// Get post id
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Define empty meta var
	$meta = '';

	// Check meta field for position
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_post_slider_shortcode_position', true ) ) {
		$position = $meta;
	}

	// Apply filters and return
	return apply_filters( 'wpex_post_slider_position', $position, $meta );

}

/**
 * Get correct post slider shortcode
 *
 * @since 4.0
 */
function wpex_get_post_slider_shortcode( $post_id = '' ) {

	// None by default
	$slider = '';

	// Get post Id
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Check meta field for slider shortcode
	if ( $post_id ) {
		$slider = get_post_meta( $post_id, 'wpex_post_slider_shortcode', true );
		$slider = $slider ? $slider : get_post_meta( $post_id, 'wpex_page_slider_shortcode', true ); // deprecated option
	}

	// Apply filters and return
	return apply_filters( 'wpex_post_slider_shortcode', $slider );

}