<?php
/**
 * WooCommerce menu cart functions.
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add WooCommerce framents.
 *
 * @since 4.0
 */
function wpex_menu_cart_icon_fragments( $fragments ) {
	$fragments['.wcmenucart']      = wpex_wcmenucart_menu_item();
	$fragments['.wpex-cart-count'] = wpex_mobile_menu_cart_count();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'wpex_menu_cart_icon_fragments' );

/**
 * Get correct style for WooCommerce menu cart style.
 *
 * @since 4.0
 */
function wpex_header_menu_cart_style() {

	// Return if disabled completely in Customizer
	if ( 'disabled' == get_theme_mod( 'woo_menu_icon_display', 'icon_count' ) ) {
		return false;
	}

	// If not disabled get style from settings
	else {

		// Get Menu Icon Style
		$style = get_theme_mod( 'woo_menu_icon_style', 'drop_down' );

		// Return click style for these pages
		if ( is_cart() || is_checkout() ) {
			$style = 'custom-link';
		}

	}

	// Apply filters for child theme mods
	$style = apply_filters( 'wpex_menu_cart_style', $style );

	// Sanitize output so it's not empty and check for deprecated 'drop-down' style
	if ( 'drop-down' == $style || ! $style ) {
		$style = 'drop_down';
	}

	// Return style
	return $style;

}

/**
 * Returns header menu cart item.
 *
 * @since 4.4
 */
function wpex_get_header_menu_cart_item( $style = '' ) {

	if ( ! $style ) {
		return;
	}

	// Get header style
	$header_style = wpex_header_style();

	// Define classes to add to li element
	$class = array(
		'woo-menu-icon',
		'menu-item',
		'wpex-menu-extra',
	);

	// Add style class
	$class[] = 'wcmenucart-toggle-' . sanitize_html_class( $style );

	// Prevent clicking on cart and checkout
	if ( 'custom-link' != $style && ( is_cart() || is_checkout() ) ) {
		$class[] = 'nav-no-click';
	}

	// Add toggle class
	else {
		$class[] = 'toggle-cart-widget';
	}

	// Add ubermenu classes
	if ( class_exists( 'UberMenu' ) && apply_filters( 'wpex_add_search_toggle_ubermenu_classes', true ) ) {
		$class[] = 'ubermenu-item-level-0 ubermenu-item';
	}

	// Add cart dropdown inside menu items for specific header styles
	$cart_dropdown = '';
	if ( 'drop_down' == $style && ! in_array( $header_style, array( 'one' ) ) ) {
		ob_start();
		get_template_part( 'partials/cart/cart-dropdown' );
		$cart_dropdown .= ob_get_clean();
	}

	// Add cart link to menu items
	return '<li class="' . esc_attr( implode( ' ', $class ) ) . '">' . wpex_wcmenucart_menu_item() . $cart_dropdown . '</li>';

}

/**
 * Add cart link to the header menu for use on mobile.
 *
 * @since 4.0
 */
function wpex_add_header_menu_cart_item( $items, $args ) {

	// Only used for the main menu
	if ( 'main_menu' != $args->theme_location ) {
		return $items;
	}

	// Get style
	$style = wpex_header_menu_cart_style();

	// Return items if no style
	if ( ! $style ) {
		return $items;
	}

	// Add cart item to menu
	$items .= wpex_get_header_menu_cart_item( $style );

	// Add mobile menu link
	if ( apply_filters( 'woo_mobile_menu_cart_link', true )
		&& wpex_has_header_mobile_menu()
		&& $cart_id = wpex_parse_obj_id( wc_get_page_id( 'cart' ), 'page' )
	) {
		$items .= '<li class="menu-item wpex-mm-menu-item"><a href="' . esc_url( get_permalink( $cart_id ) ) . '"><span class="link-inner">' . esc_html( get_theme_mod( 'woo_menu_cart_text', esc_html__( 'Cart', 'total' ) ) ) . '</span></a></li>';
	}

	// Return menu items
	return $items;

}
add_filter( 'wp_nav_menu_items', 'wpex_add_header_menu_cart_item', 10, 2 );

/**
 * Creates the WooCommerce link for the navbar
 * Must check if function exists for easier child theme edits.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wpex_wcmenucart_menu_item' ) ) {
	function wpex_wcmenucart_menu_item() {

		// Vars
		global $woocommerce;
		$icon_style   = get_theme_mod( 'woo_menu_icon_style', 'drop-down' );
		$custom_link  = get_theme_mod( 'woo_menu_icon_custom_link' );
		$count        = WC()->cart->cart_contents_count;

		// Link classes
		$a_classes = 'wcmenucart';
		$count     = $count ? $count : '0';
		$a_classes .= ' wcmenucart-items-' . intval( $count );
		if ( $count && '0' !== $count ) {
			$a_classes .= ' wpex-has-items';
		}
		if ( class_exists( 'UberMenu' ) && apply_filters( 'wpex_add_search_toggle_ubermenu_classes', true ) ) {
			$a_classes .= ' ubermenu-target ubermenu-item-layout-default ubermenu-item-layout-text_only';
		}

		// Define cart icon link URL
		$url = '';
		if ( 'custom-link' == $icon_style && $custom_link ) {
			$url = esc_url( $custom_link );
		} elseif ( $cart_id = wpex_parse_obj_id( wc_get_page_id( 'cart' ), 'page' ) ) {
			$url = get_permalink( $cart_id );
		}

		// Cart total
		$display = get_theme_mod( 'woo_menu_icon_display', 'icon_count' );
		$count_txt = absint( $count );
		if ( 'icon_total' == $display ) {
			$cart_extra = WC()->cart->get_cart_total();
			$cart_extra = str_replace( 'amount', 'amount wcmenucart-details', $cart_extra );
		} elseif ( 'icon_count' == $display ) {
			$extra_class = 'wcmenucart-details count';
			if ( $count && '0' != $count ) {
				$extra_class .= ' wpex-has-items';
			}
			if ( 'six' == wpex_header_style() ) {
				$count_txt = '(' . $count_txt . ')';
			} elseif ( get_theme_mod( 'wpex_woo_menu_icon_bubble', true ) ) {
				$extra_class .= ' t-bubble';
			}
			$cart_extra = '<span class="' . esc_attr( $extra_class ) . '">' . esc_html( $count_txt ) . '</span>';
		} else {
			$cart_extra = '';
		}

		// Cart Icon
		$icon_class = ( $icon_class = get_theme_mod( 'woo_menu_icon_class' ) ) ? $icon_class : 'shopping-cart';
		$cart_text  = get_theme_mod( 'woo_menu_cart_text', esc_html__( 'Cart', 'total' ) );
		$cart_icon  = '<span class="wcmenucart-icon ticon ticon-' . esc_attr( $icon_class ) . '"></span><span class="wcmenucart-text">' . esc_html( $cart_text ) . '</span>';
		$cart_icon = apply_filters( 'wpex_menu_cart_icon_html', $cart_icon, $icon_class );

		// Output
		$output = '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $a_classes ) . '">';

			$output .= '<span class="link-inner">';

				$output .= '<span class="wcmenucart-count">' . $cart_icon . $cart_extra . '</span>';

			$output .= '</span>';

		$output .= '</a>';

		return $output;

	}
}

/**
 * Add cart overlay html to site.
 *
 * @since 4.0
 */
function wpex_cart_overlay_html() {
	if ( 'overlay' == wpex_header_menu_cart_style() ) {
		get_template_part( 'partials/cart/cart-overlay' );
	}
}
add_action( 'wpex_outer_wrap_after', 'wpex_cart_overlay_html' );

/**
 * Add cart dropdown html.
 *
 * @since 4.0
 */
function wpex_add_cart_dropdown_html() {

	if ( 'drop_down' !== wpex_header_menu_cart_style() ) {
		return;
	}

	$get = false;

	if ( 'one' == wpex_header_style() && 'wpex_hook_header_inner' == current_filter() ) {
		$get = true;
	}

	if ( $get ) {
		get_template_part( 'partials/cart/cart-dropdown' );
	}

}
add_action( 'wpex_hook_header_inner', 'wpex_add_cart_dropdown_html', 40 );
add_action( 'wpex_hook_main_menu_bottom', 'wpex_add_cart_dropdown_html' );

/**
 * Mobile menu cart counter
 *
 * @since 4.0
 */
function wpex_mobile_menu_cart_count() {

	$count = absint( WC()->cart->cart_contents_count );

	$count = $count ? absint( $count ) : 0;

	$classes = array(
		'wpex-cart-count',
		'wpex-absolute',
		'wpex-text-center',
		'wpex-semibold',
		'wpex-rounded',
		'wpex-text-white',
	);

	if ( $count ) {
		$classes[] = 'wpex-block wpex-bg-accent';
	} else {
		$classes[] = 'wpex-hidden wpex-bg-gray-400';
	}

	return '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . esc_html( $count ) . '</span>';
}