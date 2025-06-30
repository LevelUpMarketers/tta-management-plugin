<?php
/**
 * Togglebar functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get togglebar content ID
 *
 * @since 4.0
 */
function wpex_togglebar_content_id() {
	$id = apply_filters( 'wpex_toggle_bar_content_id', get_theme_mod( 'toggle_bar_page', null ) );
	return $id ? wpex_parse_obj_id( intval( $id ) ) : null;
}

/**
 * Returns togglebar content
 *
 * @since 4.0
 */
function wpex_togglebar_content() {
	if ( $togglebar_id = wpex_togglebar_content_id() ) {
		return wpex_parse_vc_content( get_post_field( 'post_content', $togglebar_id ) );
	}
}

/**
 * Check if togglebar is enabled
 *
 * @since 4.0
 */
function wpex_has_togglebar( $post_id = '' ) {

	// Return false if toggle bar page is not defined
	if ( ! wpex_togglebar_content_id() && ! wpex_elementor_location_exists( 'togglebar' ) ) {
		return false;
	}

	// Check if enabled in Customizer
	$return = get_theme_mod( 'toggle_bar', true );

	// Get post ID
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Check meta
	if ( $post_id ) {

		// Return true if enabled via the page settings
		if ( 'enable' == get_post_meta( $post_id, 'wpex_disable_toggle_bar', true ) ) {
			$return = true;
		}

		// Return false if disabled via the page settings
		if ( 'on' == get_post_meta( $post_id, 'wpex_disable_toggle_bar', true ) ) {
			$return = false;
		}

	}

	// Apply filters and return
	return apply_filters( 'wpex_toggle_bar_active', $return ); // @todo Rename to "wpex_has_togglebar" for consistency

}

/**
 * Get correct togglebar style
 *
 * @since 4.0
 */
function wpex_togglebar_style() {
	$style = ( $style = get_theme_mod( 'toggle_bar_display' ) ) ? $style : 'overlay';
	return apply_filters( 'wpex_togglebar_style', $style );
}

/**
 * Returns correct togglebar classes
 *
 * @since 4.9.9.5
 */
function wpex_togglebar_class() {
	if ( $classes = wpex_togglebar_classes() ) {
		echo 'class="' . esc_attr( $classes ) . '"';
	}
}

/**
 * Returns togglebar state.
 *
 * @since 5.0.6
 * @return (string) hidden or visible
 */
function wpex_togglebar_state() {
	$state = apply_filters( 'wpex_togglebar_state', get_theme_mod( 'toggle_bar_default_state', 'hidden' ) );
	switch ( $state ) {
		case 'open':
			$state = 'visible';
			break;
		case 'closed':
			$state = 'hidden';
			break;
	}
	return $state;
}

/**
 * Returns togglebar visibility.
 *
 * @since 5.0.6
 */
function wpex_togglebar_visibility() {
	return apply_filters( 'wpex_togglebar_visibility', get_theme_mod( 'toggle_bar_visibility', 'always-visible' ) );
}

/**
 * Returns correct togglebar classes
 *
 * @since 1.0
 */
function wpex_togglebar_classes() {

	$classes    = array();
	$style      = wpex_togglebar_style();
	$visibility = wpex_togglebar_visibility();
	$is_builder = wpex_elementor_location_exists( 'togglebar' );
	$animation  = get_theme_mod( 'toggle_bar_animation', 'fade' );

	/*** Add theme classes ***/

	$classes[] = 'toggle-bar-' . sanitize_html_class( $style );

		// Overlay class
		if ( 'overlay' == $style && $animation ) {
			$classes[] = 'toggle-bar-' . sanitize_html_class( $animation );
		}

		// Default state
		if ( 'visible' == wpex_togglebar_state() ) {
			$classes[] = 'active-bar';
		} else {
			$classes[] = 'close-on-doc-click';
		}

		// Visibility
		if ( $visibility && 'always-visible' !== $visibility ) {
			$classes[] = sanitize_html_class( $visibility );
		}

	/*** Add utility classes ***/

		// Default
		$classes[] = 'wpex-invisible';
		$classes[] = 'wpex-opacity-0';
		$classes[] = 'wpex-bg-white';
		$classes[] = 'wpex-w-100';

		// Style specific classes
		if ( 'overlay' === $style ) {
			$classes[] = 'wpex-fixed';
			$classes[] = '-wpex-z-1';
			$classes[] = 'wpex-top-0';
			$classes[] = 'wpex-inset-x-0';
			$classes[] = 'wpex-max-h-100';
			$classes[] = 'wpex-overflow-auto';
			$classes[] = 'wpex-shadow';
			if ( ! $is_builder ) {
				$classes[] = 'wpex-py-40';
			}
		} elseif ( 'inline' === $style ) {
			$classes[] = 'wpex-hidden';
			$classes[] = 'wpex-border-b';
			$classes[] = 'wpex-border-solid';
			$classes[] = 'wpex-border-main';
			if ( ! $is_builder ) {
				$classes[] = 'wpex-py-20';
			}
		}

		// Add animation classes
		if ( 'overlay' == $style && $animation ) {
			$classes[] = 'wpex-transition-all';
			$classes[] = 'wpex-duration-300';
			if ( 'fade-slide' === $animation ) {
				$classes[] = '-wpex-translate-y-50';
			}
		}

		// Add clearfix
		$classes[] = 'wpex-clr';

	/*** Sanitize & Apply Filters ***/

		// Sanitize
		$classes = array_map( 'esc_attr', $classes );

		// Apply filters for child theming
		$classes = apply_filters_deprecated( 'wpex_toggle_bar_active', array( $classes ), '4.9', 'wpex_togglebar_classes' );
		$classes = apply_filters( 'wpex_togglebar_classes', $classes );

		// Turn classes into space seperated string
		$classes = is_array( $classes ) ? implode( ' ', $classes ) : $classes;

	/*** Return classes ***/
	return $classes;

}