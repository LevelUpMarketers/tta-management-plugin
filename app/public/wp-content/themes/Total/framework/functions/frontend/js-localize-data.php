<?php
/**
 * Returns array of data for the global js wpexLocalize object
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 * @todo rename to wpex_js_l10n();
 */

defined( 'ABSPATH' ) || exit;

function wpex_js_localize_data() {

	$post_id         = wpex_get_current_post_id();
	$header_style    = wpex_header_style( $post_id );
	$mm_style        = wpex_header_menu_mobile_style();
	$mm_toggle_style = wpex_header_menu_mobile_toggle_style();
	$mm_breakpoint   = wpex_header_menu_mobile_breakpoint(); // @todo check if we should be adding + 1 to this value.

	// Create array
	$array = array(
		'ajaxurl'                   => set_url_scheme( admin_url( 'admin-ajax.php' ) ),
		'isRTL'                     => is_rtl(), // @todo remove and simply check body class
		'mainLayout'                => wp_strip_all_tags( wpex_site_layout() ),
		'menuSearchStyle'           => wp_strip_all_tags( wpex_header_menu_search_style() ),
		'siteHeaderStyle'           => wp_strip_all_tags( $header_style ),
		'megaMenuJS'                => true,
		'superfishDelay'            => 600,
		'superfishSpeed'            => 'fast',
		'superfishSpeedOut'         => 'fast',
		'menuWidgetAccordion'       => true,
		'hasMobileMenu'             => wpex_has_header_mobile_menu(),
		'mobileMenuBreakpoint'      => wp_strip_all_tags( $mm_breakpoint ),
		'mobileMenuStyle'           => wp_strip_all_tags( $mm_style ),
		'mobileMenuToggleStyle'     => wp_strip_all_tags( $mm_toggle_style ),
		'mobileMenuAriaLabel'       => wp_strip_all_tags( wpex_get_aria_label( 'mobile_menu' ) ),
		'mobileMenuCloseAriaLabel'  => wp_strip_all_tags( wpex_get_aria_label( 'mobile_menu_toggle' ) ),
		'responsiveDataBreakpoints' => array(
			'tl' => '1024px',
			'tp' => '959px',
			'pl' => '767px',
			'pp' => '479px',
		),

		// These are general strings that can be used for multiple areas
		'i18n' => array(
			'openSubmenu'  => esc_html__( 'Open submenu of %s', 'total' ),
			'closeSubmenu' => esc_html__( 'Close submenu of %s', 'total' ),
		),

		// @todo add only when load more scripts are being loaded and use it's own var
		'loadMore' => array(
			'text'        => wp_strip_all_tags( wpex_get_loadmore_text() ),
			'loadingText' => wp_strip_all_tags( wpex_get_loadmore_loading_text() ),
			'failedText'  => wp_strip_all_tags( wpex_get_loadmore_failed_text() ),
		),

	);

	/**** Header params ****/
	if ( 'disabled' != $header_style ) {

		// Sticky Header
		if ( wpex_has_sticky_header() ) {

			$array['hasStickyHeader'] = true; // @todo remove as it's not needed.
			if ( $logo = wpex_sticky_header_logo_img() ) {
				$array['stickyheaderCustomLogo'] = esc_url( $logo );
				if ( $logo = wpex_sticky_header_logo_img_retina() ) {
					$array['stickyheaderCustomLogoRetina'] = esc_url( $logo );
				}
			}

			$array['stickyHeaderStyle'] = wp_strip_all_tags( wpex_sticky_header_style() );
			if ( wpex_has_custom_header() ) {
				$array['hasStickyMobileHeader'] = true;
			} else {
				$array['hasStickyMobileHeader'] = wp_validate_boolean( get_theme_mod( 'fixed_header_mobile' ) );
			}
			$array['overlayHeaderStickyTop'] = 0;
			$array['stickyHeaderBreakPoint'] = $mm_breakpoint + 1;

			// Sticky header start position
			$fixed_startp = wpex_sticky_header_start_position();
			if ( $fixed_startp ) {
				$fixed_startp = str_replace( 'px', '', $fixed_startp );
				$array['stickyHeaderStartPosition'] = wp_strip_all_tags( $fixed_startp ); // can be int or element class/id
			}

			// Make sure sticky is always enabled if responsive is disabled
			if ( ! wpex_is_layout_responsive() ) {
				$array['hasStickyMobileHeader'] = true;
			}

			// Shrink sticky header > used for local-scroll offset
			if ( wpex_has_shrink_sticky_header() ) {
				$height_escaped = intval( get_theme_mod( 'fixed_header_shrink_end_height' ) );
				$height_escaped = $height_escaped ? $height_escaped + 20 : 70;
				$array['shrinkHeaderHeight'] = $height_escaped;
			}

		}

		// Sticky Navbar
		if ( wpex_has_sticky_header_menu() ) {
			$array['hasStickyNavbarMobile']  = wp_validate_boolean( get_theme_mod( 'fixed_header_menu_mobile' ) );
			$array['stickyNavbarBreakPoint'] = 960;
		}

		// Header five
		if ( 'five' == $header_style ) {
			$array['headerFiveSplitOffset'] = 1;
		}

		// WooCart
		if ( function_exists( 'wpex_header_menu_cart_style' ) ) {
			$array['wooCartStyle'] = wp_strip_all_tags( wpex_header_menu_cart_style( 'menu_cart_style' ) );
		}

	} // End header params

	// Toggle mobile menu position
	if ( 'toggle' == $mm_style ) {
		$array['animateMobileToggle'] = true;
		if ( get_theme_mod( 'fixed_header_mobile', false ) ) {
			$mobileToggleMenuPosition = 'absolute'; // Must be absolute for sticky header
		} elseif ( 'fixedTopNav' != $mm_toggle_style && wpex_has_overlay_header() ) {
			if ( 'navbar' == $mm_toggle_style ) {
				$mobileToggleMenuPosition = 'afterself';
			} else {
				$mobileToggleMenuPosition = 'absolute';
			}
		} elseif ( 'outer_wrap_before' == get_theme_mod( 'mobile_menu_navbar_position' ) && 'navbar' == $mm_toggle_style ) {
			$mobileToggleMenuPosition = 'afterself';
		} else {
			$mobileToggleMenuPosition = 'afterheader';
		}
		$array['mobileToggleMenuPosition'] = $mobileToggleMenuPosition;
	}

	// Sidr settings
	if ( 'sidr' == $mm_style ) {
		$sidr_side = get_theme_mod( 'mobile_menu_sidr_direction' );
		$sidr_side = $sidr_side ? $sidr_side : 'right'; // Fallback is crucial
		$array['sidrSource']       = wpex_sidr_menu_source( $post_id );
		$array['sidrDisplace']     = wp_validate_boolean( get_theme_mod( 'mobile_menu_sidr_displace', false ) );
		$array['sidrSide']         = wp_strip_all_tags( $sidr_side );
		$array['sidrBodyNoScroll'] = false;
		$array['sidrSpeed']        = 300;
	}

	// Mobile menu toggles style
	if ( ( 'toggle' == $mm_style || 'sidr' == $mm_style ) && get_theme_mod( 'mobile_menu_dropdowns_arrow_toggle', false ) ) {
		$array['mobileMenuDropdownsArrowToggle'] = true;
	}

	// Sticky topBar
	if ( ! wpex_vc_is_inline() && apply_filters( 'wpex_has_sticky_topbar', get_theme_mod( 'top_bar_sticky' ) ) ) {
		$array['stickyTopBarBreakPoint'] = 960;
		$array['hasStickyTopBarMobile']  = wp_validate_boolean( get_theme_mod( 'top_bar_sticky_mobile', true ) );
	}

	// Full screen mobile menu style
	if ( 'full_screen' == $mm_style ) {
		$array['fullScreenMobileMenuStyle'] = wp_strip_all_tags( get_theme_mod( 'full_screen_mobile_menu_style', 'white' ) );
	}

	// Auto lightbox
	if ( get_theme_mod( 'lightbox_auto', false ) ) {
		$array['autoLightbox'] = apply_filters( 'wpex_auto_lightbox_targets', '.wpb_text_column a:has(img), body.no-composer .entry a:has(img)' );
	}

	// Custom selects
	if ( apply_filters( 'wpex_custom_selects_js', true ) ) {
		$array['customSelects'] = '.widget_categories form,.widget_archive select,.vcex-form-shortcode select';
		if ( WPEX_WOOCOMMERCE_ACTIVE && wpex_has_woo_mods() ) {
			$array['customSelects'] .= ',.woocommerce-ordering .orderby,#dropdown_product_cat,.single-product .variations_form .variations select';
		}
	}

	/**** Local Scroll args ****/
	$array['scrollToHash']          = wpex_has_local_scroll_on_load();
	$array['scrollToHashTimeout']   = wpex_get_local_scroll_on_load_timeout();
	$array['localScrollTargets']    = wpex_get_local_scroll_targets();
	$array['localScrollUpdateHash'] = wpex_has_local_scroll_hash_update();
	$array['localScrollHighlight']  = wpex_has_local_scroll_menu_highlight();
	$array['localScrollSpeed']      = wpex_get_local_scroll_speed();
	$array['localScrollEasing']     = wpex_get_local_scroll_easing();

	// Apply filters and return
	return apply_filters( 'wpex_localize_array', $array );

}