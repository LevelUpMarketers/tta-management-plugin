<?php
/**
 * Footer Helper Functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if footer is enabled
 *
 * @since 4.0
 */
function wpex_has_footer() {

	// Return true by default
	$bool = true;

	// Disabled on landing page
	if ( is_page_template( 'templates/landing-page.php' ) ) {
		$bool = false;
	}

	// Get current post id
	$post_id = wpex_get_current_post_id();

	// Check page settings
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_disable_footer', true ) ) {
		if ( 'on' == $meta ) {
			$bool = false;
		} elseif ( 'enable' == $meta ) {
			$bool = true;
		}
	}

	// Apply filters and return bool
	return apply_filters( 'wpex_display_footer', $bool );

}

/**
 * Check if footer has widgets
 *
 * @since 4.0
 */
function wpex_footer_has_widgets() {
	if ( wpex_has_custom_footer() || ! empty( $_GET[ 'wpex_inline_footer_template_editor' ] ) ) {
		$bool = get_theme_mod( 'footer_builder_footer_widgets', false ); //@todo make the option same value as Customizer?
	} else {
		$bool = get_theme_mod( 'footer_widgets', true );
	}
	$post_id = wpex_get_current_post_id();
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_disable_footer_widgets', true ) ) {
		if ( 'on' == $meta ) {
			$bool = false;
		} elseif ( 'enable' == $meta ) {
			$bool = true;
		}
	}
	return apply_filters( 'wpex_display_footer_widgets', $bool );
}

/**
 * Returns footer class
 *
 * @since 4.9.8
 * @todo update to return full class="" html
 */
function wpex_footer_class() {

	$class = array(
		'site-footer',
		'wpex-bg-gray-A900',
		'wpex-text-gray-600',
	);

	if ( get_theme_mod( 'footer_bg_img' ) && $bg_style = get_theme_mod( 'footer_bg_img_style' ) ) {
		$class[] = 'bg-' . sanitize_html_class( $bg_style );
	}

	$class = apply_filters( 'wpex_footer_class', $class );

	return implode( ' ', $class );

}

/**
 * Returns footer widgets class
 *
 * @since 4.9.8
 */
function wpex_footer_widgets_class() {
	$columns = get_theme_mod( 'footer_widgets_columns', '4' );
	$gap     = get_theme_mod( 'footer_widgets_gap', '30' );

	$class = array(
		'wpex-row',
		'wpex-clr',
	);

	if ( '1' === $columns ) {
		$class[] = 'single-col-footer'; // @todo remove legacy class?
	}

	if ( $gap ) {
		$class[] = 'gap-' . sanitize_html_class( $gap );
	}

	$class = apply_filters( 'wpex_footer_widgets_class', $class ); // added in 4.9.8

	return apply_filters( 'wpex_footer_widget_row_classes', implode( ' ', $class ) ); // @todo deprecate filter

}

/**
 * Check if footer reveal is enabled
 *
 * @since 4.0
 */
function wpex_has_footer_reveal( $post_id = '' ) {

	// Disable here always
	if ( ! wpex_has_footer()
		|| 'boxed' == wpex_site_layout()
		|| 'six' == wpex_header_style()
		|| wpex_vc_is_inline()
	) {
		return false;
	}

	// Check customizer setting
	$bool = get_theme_mod( 'footer_reveal', false );

	// Get current post id if not set
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Check page settings
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_footer_reveal', true ) ) {
		if ( 'on' == $meta ) {
			$bool = true;
		} elseif ( 'off' == $meta ) {
			$bool = false;
		}
	}

	// Apply filters and return
	return (bool) apply_filters( 'wpex_has_footer_reveal', $bool );
}
