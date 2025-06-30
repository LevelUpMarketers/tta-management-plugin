<?php
/**
 * Adds custom classes to the body tag
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_body_class( $classes ) {

	// Save some vars
	$post_id     = wpex_get_current_post_id();
	$main_layout = wpex_site_layout( $post_id );

	// Customizer
	if ( is_customize_preview() ) {
		$classes[] = 'is_customize_preview'; // @todo remove?
	}

	// Main class
	$classes[] = 'wpex-theme';

	// Responsive
	if ( wpex_is_layout_responsive() ) {
		$classes[] = 'wpex-responsive';
	}

	// Layout Style
	$classes[] = sanitize_html_class( $main_layout ) . '-main-layout';

	// Check if the Visual Composer is being used on this page
	if ( wpex_has_post_wpbakery_content( $post_id ) ) {
		$classes[] = 'has-composer';
	} else {
		$classes[] = 'no-composer';
	}

	// Live site class
	if ( ! wpex_vc_is_inline() ) {
		$classes[] = 'wpex-live-site'; // @todo remove this unneded check.
	}

	// Add primary element bottom margin
	if ( wpex_has_primary_bottom_spacing() ) {
		$classes[] = 'wpex-has-primary-bottom-spacing';
	}

	// Boxed Layout dropshadow
	if ( 'boxed' == $main_layout && get_theme_mod( 'boxed_dropdshadow' ) ) {
		$classes[] = 'wrap-boxshadow';
	}

	// Content layout
	$classes[] = 'content-' . sanitize_html_class( wpex_content_area_layout( $post_id ) );

	// Sidebar
	if ( wpex_has_sidebar() ) {
		$classes[] = 'has-sidebar';
	}

	// Extra header classes
	if ( wpex_has_header() ) {
		if ( wpex_has_vertical_header() ) {
			$classes[] = 'wpex-has-vertical-header';
			if ( 'fixed' == get_theme_mod( 'vertical_header_style' ) ) {
				$classes[] = 'wpex-fixed-vertical-header';
			}
		}
	} else {
		$classes[] = 'wpex-site-header-disabled';
	}

	// Topbar
	if ( wpex_has_topbar() ) {
		$classes[] = 'has-topbar';
	}

	// Single Post cagegories
	if ( is_singular( 'post' ) ) {
		$cats = get_the_category( $post_id );
		foreach ( $cats as $cat ) {
			$classes[] = 'post-in-category-' . sanitize_html_class( $cat->category_nicename );
		}
	}

	// Widget Icons
	if ( get_theme_mod( 'has_widget_icons', true ) ) {
		$classes[] = 'sidebar-widget-icons';
	}

	// Overlay header style
	if ( wpex_has_overlay_header() ) {
		$classes[] = 'has-overlay-header';
	} else {
		$classes[] = 'hasnt-overlay-header';
	}

	// Footer reveal
	if ( wpex_has_footer_reveal() ) {
		$classes[] = 'footer-has-reveal';
	}

	// Fixed Footer - adds min-height to main wraper
	if ( get_theme_mod( 'fixed_footer', false ) ) {
		$classes[] = 'wpex-has-fixed-footer';
	}

	// Disabled header
	if ( wpex_has_page_header() ) {
		if ( 'background-image' == wpex_page_header_style() ) {
			$classes[] = 'page-with-background-title';
		}
	} else {
		$classes[] = 'page-header-disabled';
	}

	// Disable title margin
	// @todo deprecate this class? Maybe we shouldn't because it could be used to add CSS as needed for spacing, etc.
	if ( $post_id && wpex_validate_boolean( get_post_meta( $post_id, 'wpex_disable_header_margin', true ) ) ) {
		$classes[] = 'no-header-margin';
	}

	// Page slider
	if ( wpex_has_post_slider( $post_id ) && $slider_position = wpex_post_slider_position( $post_id ) ) {
		$classes[]       = 'page-with-slider'; // Deprecated @todo remove this class
		$classes[]       = 'has-post-slider';
		$classes[]       = 'post-slider-' . sanitize_html_class( str_replace( '_', '-', $slider_position ) );
	}

	// Font smoothing
	if ( get_theme_mod( 'enable_font_smoothing', false ) ) {
		$classes[] = 'wpex-antialiased';
	}

	// Mobile menu toggle style
	if ( wpex_has_header_mobile_menu() ) {

		// Mobile menu toggle style
		$classes[] = 'wpex-mobile-toggle-menu-' . sanitize_html_class( wpex_header_menu_mobile_toggle_style() );

		// Mobile menu style
		$classes[] = 'has-mobile-menu';

	} elseif ( wpex_has_header_menu() ) {
		$classes[] = 'hasnt-mobile-menu';
	}

	// Navbar inner span bg
	if ( get_theme_mod( 'menu_link_span_background' ) ) {
		$classes[] = 'navbar-has-inner-span-bg';
	}

	// Check if avatars are enabled
	if ( is_singular() && ! get_option( 'show_avatars' ) ) {
		$classes[] = 'comment-avatars-disabled';
	}

	// Togglebar
	if ( 'inline' == wpex_togglebar_style() ) {
		$classes[] = 'togglebar-is-inline';
	}

	// Frame border
	if ( wpex_has_site_frame_border() ) {
		$classes[] = 'has-frame-border';
	}

	// Social share position
	if ( wpex_has_social_share() && $position = wpex_social_share_position() ) {
		$classes[] = 'wpex-share-p-' . sanitize_html_class( $position );
	}

	// WooCommerce
	if ( WPEX_WOOCOMMERCE_ACTIVE ) {
		if ( get_theme_mod( 'woo_product_responsive_tabs', false ) && is_singular( 'product' ) ) {
			$classes[] = 'woo-single-responsive-tabs';
		}
		if ( get_theme_mod( 'woo_checkout_single_col', false ) ) {
			$classes[] = 'wpex-fw-checkout';
		}
	}

	// Custom focus Styles
	if ( get_theme_mod( 'disable_mobile_menu_focus_styles', true ) ) {
		$classes[] = 'wpex-disable-mm-focus-styles';
	}

	// Return classes
	return $classes;

}
add_filter( 'body_class', 'wpex_body_class' );