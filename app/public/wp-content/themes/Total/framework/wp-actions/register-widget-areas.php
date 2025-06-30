<?php
/**
 * Functions that run on widgets init
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register sidebar widget areas
 *
 * @since 4.0
 */
function wpex_register_sidebar_widget_areas() {

	// Define sidebars array
	$sidebars = array(
		'sidebar' => esc_html__( 'Main Sidebar', 'total' ),
	);

	// Pages Sidebar
	if ( get_theme_mod( 'pages_custom_sidebar', true ) ) {
		$sidebars['pages_sidebar'] = esc_html__( 'Pages Sidebar', 'total' );
	}

	// Blog Sidebar
	if ( get_theme_mod( 'blog_custom_sidebar', false ) ) {
		$sidebars['blog_sidebar'] = esc_html__( 'Blog Sidebar', 'total' );
	}

	// Search Results Sidebar
	if ( get_theme_mod( 'search_custom_sidebar', true ) ) {
		$sidebars['search_sidebar'] = esc_html__( 'Search Results Sidebar', 'total' );
	}

	// WooCommerce
	if ( WPEX_WOOCOMMERCE_ACTIVE && get_theme_mod( 'woo_custom_sidebar', true ) ) {
		$sidebars['woo_sidebar'] = esc_html__( 'WooCommerce Sidebar', 'total' );
	}

	// Apply filters - makes it easier to register new sidebars
	$sidebars = apply_filters( 'wpex_register_sidebars_array', $sidebars );

	// If there are no sidebars then return
	if ( ! $sidebars ) {
		return;
	}

	// Sidebar tags
	$tag_escaped = ( $tag = get_theme_mod( 'sidebar_headings' ) ) ? tag_escape( $tag ) : 'div';

	// Loop through sidebars and register them
	foreach ( $sidebars as $k => $v ) {

		$args = array(
			'id'            => sanitize_key( $k ),
			'before_widget' => '<div id="%1$s" class="sidebar-box widget %2$s wpex-mb-30 wpex-clr">',
			'after_widget'  => '</div>',
			'before_title'  => '<' . $tag_escaped . ' class="widget-title wpex-heading wpex-text-md wpex-mb-20">',
			'after_title'   => '</' . $tag_escaped . '>',
		);

		if ( is_array( $v ) ) {
			$args = wp_parse_args( $v, $args );
		} else {
			$args['name'] = esc_html( $v );
		}

		register_sidebar( $args );

	}

}
add_action( 'widgets_init', 'wpex_register_sidebar_widget_areas' );

/**
 * Register footer widget areas
 *
 * @since 4.0
 */
function wpex_register_footer_widget_areas() {

	if ( wpex_has_custom_footer() ) {
		$has_footer_widgets = get_theme_mod( 'footer_builder_footer_widgets', false );
	} else {
		$has_footer_widgets = get_theme_mod( 'footer_widgets', true );
	}

	// Check if footer widgets are enabled
	// @todo rename filter to "wpex_maybe_register_footer_widget_areas" ?
	$has_footer_widgets = apply_filters( 'wpex_register_footer_sidebars', $has_footer_widgets );

	// Return if disabled
	if ( ! $has_footer_widgets ) {
		return;
	}

	// Footer tag
	$tag_escaped = ( $tag = get_theme_mod( 'footer_headings' ) ) ? tag_escape( $tag ) : 'div';

	// Footer widget columns
	$footer_columns = get_theme_mod( 'footer_widgets_columns', '4' );

	// Footer 1
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Column 1', 'total' ),
		'id'            => 'footer_one',
		'before_widget' => '<div id="%1$s" class="footer-widget widget wpex-pb-40 wpex-clr %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<' . $tag_escaped . ' class="widget-title wpex-heading wpex-text-md wpex-mb-20">',
		'after_title'   => '</' . $tag_escaped . '>',
	) );

	// Footer 2
	if ( $footer_columns > '1' ) {

		register_sidebar( array(
			'name'          => esc_html__( 'Footer Column 2', 'total' ),
			'id'            => 'footer_two',
			'before_widget' => '<div id="%1$s" class="footer-widget widget wpex-pb-40 wpex-clr %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<' . $tag_escaped . ' class="widget-title wpex-heading wpex-text-md wpex-mb-20">',
			'after_title'   => '</' . $tag_escaped . '>'
		) );

	}

	// Footer 3
	if ( $footer_columns > '2' ) {

		register_sidebar( array(
			'name'          => esc_html__( 'Footer Column 3', 'total' ),
			'id'            => 'footer_three',
			'before_widget' => '<div id="%1$s" class="footer-widget widget wpex-pb-40 wpex-clr %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<' . $tag_escaped . ' class="widget-title wpex-heading wpex-text-md wpex-mb-20">',
			'after_title'   => '</' . $tag_escaped . '>',
		) );

	}

	// Footer 4
	if ( $footer_columns > '3' ) {

		register_sidebar( array(
			'name'          => esc_html__( 'Footer Column 4', 'total' ),
			'id'            => 'footer_four',
			'before_widget' => '<div id="%1$s" class="footer-widget widget wpex-pb-40 wpex-clr %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<' . $tag_escaped . ' class="widget-title wpex-heading wpex-text-md wpex-mb-20">',
			'after_title'   => '</' . $tag_escaped . '>',
		) );

	}

	// Footer 5
	if ( $footer_columns > '4' ) {

		register_sidebar( array(
			'name'          => esc_html__( 'Footer Column 5', 'total' ),
			'id'            => 'footer_five',
			'before_widget' => '<div id="%1$s" class="footer-widget widget wpex-pb-40 wpex-clr %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<' . $tag_escaped . ' class="widget-title wpex-heading wpex-text-md wpex-mb-20">',
			'after_title'   => '</' . $tag_escaped . '>',
		) );

	}

}
add_action( 'widgets_init', 'wpex_register_footer_widget_areas', 40 );