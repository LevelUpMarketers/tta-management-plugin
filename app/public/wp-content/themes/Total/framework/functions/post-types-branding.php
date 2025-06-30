<?php
/**
 * Post Type Functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Portfolio ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns portfolio name
 *
 * @since 3.3.0
 */
function wpex_get_portfolio_name() {
	return ( $name = wpex_get_translated_theme_mod( 'portfolio_labels' ) ) ? $name : esc_html__( 'Portfolio', 'total' );
}

/**
 * Returns portfolio singular name
 *
 * @since 3.3.0
 */
function wpex_get_portfolio_singular_name() {
	return ( $name = wpex_get_translated_theme_mod( 'portfolio_singular_name' ) ) ? $name : esc_html__( 'Portfolio Item', 'total' );
}

/**
 * Returns portfolio menu icon
 *
 * @since 3.3.0
 */
function wpex_get_portfolio_menu_icon() {
	return ( $icon = get_theme_mod( 'portfolio_admin_icon' ) ) ? $icon : 'portfolio';
}

/*-------------------------------------------------------------------------------*/
/* [ Staff ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns staff name
 *
 * @since 3.3.0
 */
function wpex_get_staff_name() {
	return ( $name = wpex_get_translated_theme_mod( 'staff_labels' ) ) ? $name : esc_html__( 'Staff', 'total' );
}

/**
 * Returns staff singular name
 *
 * @since 3.3.0
 */
function wpex_get_staff_singular_name() {
	return ( $name = wpex_get_translated_theme_mod( 'staff_singular_name' ) ) ? $name : esc_html__( 'Staff Member', 'total' );
}
/**
 * Returns staff menu icon
 *
 * @since 3.3.0
 */
function wpex_get_staff_menu_icon() {
	return ( $icon = get_theme_mod( 'staff_admin_icon' ) ) ? $icon : 'businessman';
}

/*-------------------------------------------------------------------------------*/
/* [ Testimonials ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns testimonials name
 *
 * @since 3.3.0
 */
function wpex_get_testimonials_name() {
	return ( $name = wpex_get_translated_theme_mod( 'testimonials_labels' ) ) ? $name : esc_html__( 'Testimonials', 'total' );
}

/**
 * Returns testimonials singular name
 *
 * @since 3.3.0
 */
function wpex_get_testimonials_singular_name() {
	return ( $name = wpex_get_translated_theme_mod( 'testimonials_singular_name' ) ) ? $name : esc_html__( 'Testimonial', 'total' );
}

/**
 * Returns testimonials menu icon
 *
 * @since 3.3.0
 */
function wpex_get_testimonials_menu_icon() {
	return ( $icon = get_theme_mod( 'testimonials_admin_icon' ) ) ? $icon : 'testimonial';
}