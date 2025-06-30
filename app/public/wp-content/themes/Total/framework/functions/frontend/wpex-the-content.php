<?php
/**
 * Helper function similar to get_the_content but without potential plugin conflicts.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'do_blocks' ) ) {
	add_filter( 'wpex_the_content', 'do_blocks', 9 );
}

if ( function_exists( 'wptexturize' ) ) {
	add_filter( 'wpex_the_content', 'wptexturize' );
}

if ( function_exists( 'convert_chars' ) ) {
	add_filter( 'wpex_the_content', 'convert_chars' );
}

if ( function_exists( 'wpautop' ) ) {
	add_filter( 'wpex_the_content', 'wpautop' );
}

if ( function_exists( 'shortcode_unautop' ) ) {
	add_filter( 'wpex_the_content', 'shortcode_unautop' );
}

// Fixes broken shortcodes
if ( function_exists( 'wpex_clean_up_shortcodes' ) ) {
	add_filter( 'wpex_the_content', 'wpex_clean_up_shortcodes' );
}

// New WP 5.5 filter
if ( function_exists( 'wp_filter_content_tags' ) ) {
	add_filter( 'wpex_the_content', 'wp_filter_content_tags' );
} elseif ( function_exists( 'wp_make_content_images_responsive' ) ) {
	add_filter( 'wpex_the_content', 'wp_make_content_images_responsive' );
}

// Render shortcodes
if ( function_exists( 'do_shortcode' ) ) {
	add_filter( 'wpex_the_content', 'do_shortcode', 11 );
}

// Convert smilies
if ( function_exists( 'convert_smilies' ) ) {
	add_filter( 'wpex_the_content', 'convert_smilies', 20 );
}

function wpex_the_content( $raw_string = '', $context = '' ) {
	if ( ! $raw_string ) {
		return;
	}
	return apply_filters( 'wpex_the_content', wp_kses_post( $raw_string ), $context );
}
