<?php
/**
 * Meta tag functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*

	# Generator
	# Viewport
	# X-UA-Compatible
	# Theme Color (not actually enabled)

/*-------------------------------------------------------------------------------*/
/* [ Generator ]
/*-------------------------------------------------------------------------------*/

/**
 * Return theme generator.
 */
function wpex_theme_meta_generator() {
	if ( ! get_theme_mod( 'theme_meta_generator_enable', true ) ) {
		return;
	}
	echo '<meta name="generator" content="Total WordPress Theme v' . WPEX_THEME_VERSION . '">' . "\n";
}
add_action( 'wp_head', 'wpex_theme_meta_generator', 1 );

/*-------------------------------------------------------------------------------*/
/* [ Viewport ]
/*-------------------------------------------------------------------------------*/

/**
 * Return correct viewport tag
 */
function wpex_get_meta_viewport() {

	$viewport = '';

	// Responsive viewport viewport
	if ( wpex_is_layout_responsive() ) {
		$viewport = '<meta name="viewport" content="width=device-width, initial-scale=1">';
	}

	// Non responsive meta viewport
	else {
		$width = get_theme_mod( 'main_container_width', '980' );
		if ( $width && false == strpos( $width, '%' ) ) {
			$width = $width ? intval( $width ) : '980';
			if ( 'boxed' == wpex_site_layout() ) {
				$outer_margin  = intval( get_theme_mod( 'boxed_padding', 30 ) );
				$inner_padding = 30;
				$width = $width + ( $inner_padding * 2 ) + ( $outer_margin * 2 ); // Add inner + outer padding
			}
			$viewport = '<meta name="viewport" content="width=' . absint( apply_filters( 'wpex_viewport_width', $width ) ) . '">';
		} else {
			$viewport = '<meta name="viewport" content="width=device-width, initial-scale=1">';
		}
	}

	// Apply filters to the meta viewport for child theme tweaking
	$viewport = apply_filters( 'wpex_meta_viewport', $viewport );

	// Return viewport
	if ( $viewport ) {
		return $viewport;
	}

}

/**
 * Echo viewport tag.
 */
function wpex_meta_viewport() {
	echo wpex_get_meta_viewport() . "\n";
}

/**
 * Hook viewport to wp_head.
 */
add_action( 'wp_head', 'wpex_meta_viewport', 1 );

/*-------------------------------------------------------------------------------*/
/* [ X-UA-Compatible ]
/*-------------------------------------------------------------------------------*/

/**
 * Adds x-ua compatible meta tag
 *
 * @since 4.0
 */
function wpex_x_ua_compatible_meta_tag() {
	echo '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
	echo "\r\n";
}

/**
 * Filters the HTTP headers before they’re sent to the browser.
 *
 * @since 4.0
 */
function wpex_x_ua_compatible_headers( $headers ) {
	$headers['X-UA-Compatible'] = 'IE=edge';
	return $headers;
}

if ( apply_filters( 'wpex_x_ua_compatible_headers', true ) ) {
	add_action( 'wp_head', 'wpex_x_ua_compatible_meta_tag', 1 );
	add_filter( 'wp_headers', 'wpex_x_ua_compatible_headers' );
}

/*-------------------------------------------------------------------------------*/
/* [ Theme Color ]
/*-------------------------------------------------------------------------------*/
function wpex_theme_color_meta_tag() {

	// Get theme color from customizer field
	$theme_color = get_theme_mod( 'meta_theme_color' );

	// If no color defined in the customizer grab the theme accent color
	if ( ! $theme_color ) {
		$theme_color = ( $theme_color = wpex_get_custom_accent_color() ) ? $theme_color : '#2c87f0';
	}

	// Apply_filters for child theming
	$theme_color = apply_filters( 'wpex_theme_color_meta_hex', $theme_color );

	// Echo the meta tag
	if ( $theme_color ) {
		echo '<meta name="theme-color" content="' . esc_attr( $theme_color ) . '">' . "\n";
	}

}
