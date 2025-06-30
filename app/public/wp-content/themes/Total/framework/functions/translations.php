<?php
/**
 * Translation functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns correct ID for any object
 * Used to fix issues with translation plugins such as WPML
 *
 * @since 3.1.1
 */
function wpex_parse_obj_id( $id = '', $type = 'page', $key = '' ) {

	// WPML Check
	if ( WPEX_WPML_ACTIVE ) {

		// If you want to set type to term and key to category for example
		$type = ( 'term' == $type && $key ) ? $key : $type;

		// Make sure to grab the correct type
		// Fixes issues when using templatera for example for the topbar, header, footer, etc.
		if ( 'page' == $type ) {
			$type = get_post_type( $id );
		}

		// Get correct ID
		$id = apply_filters( 'wpml_object_id', $id, $type, true );

	}

	// Polylang check
	elseif ( function_exists( 'pll_get_post' ) ) {
		$type = taxonomy_exists( $type ) ? 'term' : $type; // Fixes issue where type may be set to 'category' instead of term
		if ( 'page' == $type || 'post' == $type ) {
			$id = pll_get_post( $id );
		} elseif ( 'term' == $type && function_exists( 'pll_get_term' ) ) {
			$id = pll_get_term( $id );
		}
	}

	// Return ID
	return $id;

}

/**
 * Retrives a theme mod value and translates it
 * Note :   Translated strings do not have any defaults in the Customizer
 *          Because they all have localized fallbacks.
 *
 * @since 3.3.0
 */
function wpex_get_translated_theme_mod( $id, $default = '' ) {
	return wpex_translate_theme_mod( $id, get_theme_mod( $id, $default ) );
}

/**
 * Provides translation support for plugins such as WPML for theme_mods
 *
 * @since 1.6.3
 */
function wpex_translate_theme_mod( $id = '', $val = '' ) {

	// Translate theme mod val
	if ( $val && $id ) {

		// WPML translation
		if ( function_exists( 'icl_t' ) ) {
			$val = icl_t( 'Theme Settings', $id, $val );
		}

		// Polylang Translation
		elseif ( function_exists( 'pll__' ) ) {
			$val = pll__( $val );
		}

		// Return the value
		return $val;

	}

}

/**
 * Register theme mods for translations
 *
 * @since 2.1.0
 */
function wpex_register_theme_mod_strings() {

	$strings = array(
		'custom_logo'                    => false,
		'retina_logo'                    => false,
		'fixed_header_logo'              => false,
		'logo_height'                    => false,
		'error_page_title'               => '404: Page Not Found',
		'error_page_text'                => false,
		'top_bar_content'                => '[font_awesome icon="phone" margin_right="5px" color="#000"] 1-800-987-654 [font_awesome icon="envelope" margin_right="5px" margin_left="20px" color="#000"] admin@totalwptheme.com [font_awesome icon="user" margin_right="5px" margin_left="20px" color="#000"] [wp_login_url text="User Login" logout_text="Logout"]',
		'top_bar_social_alt'             => false,
		'header_aside'                   => false,
		'breadcrumbs_home_title'         => false,
		'blog_entry_readmore_text'       => 'Read More',
		'social_share_heading'           => 'Share This',
		'portfolio_related_title'        => 'Related Projects',
		'staff_related_title'            => 'Related Staff',
		'blog_related_title'             => 'Related Posts',
		'callout_text'                   => 'I am the footer call-to-action block, here you can add some relevant/important information about your company or product. I can be disabled in the Customizer.',
		'callout_link'                   => '#',
		'callout_link_txt'               => 'Get In Touch',
		'footer_copyright_text'          => 'Copyright <a href="#">Your Business LLC.</a> [current_year] - All Rights Reserved',
		'blog_single_header_custom_text' => 'Blog',
		'mobile_menu_toggle_text'        => 'Menu',
		'page_animation_loading'         => 'Loading&hellip;',
	);

	if ( WPEX_WOOCOMMERCE_ACTIVE ) {
		$strings['woo_shop_single_title']     = 'Store';
		$strings['woo_menu_icon_custom_link'] = '';
		$strings['woo_sale_flash_text']       = '';
	}

	return apply_filters( 'wpex_register_theme_mod_strings', $strings );
}