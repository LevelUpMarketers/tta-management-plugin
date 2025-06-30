<?php
/**
 * Register and Load frontend scripts
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.6
 *
 * @todo convert into single final class TotalTheme\Scripts
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register scripts.
 *
 * @since 5.0
 */
function wpex_register_scripts() {

	// Theme Icons
	wp_register_style(
		'ticons',
		wpex_asset_url( 'lib/ticons/css/ticons.min.css' ),
		array(),
		WPEX_THEME_VERSION
	);

	// CSS hover animations
	wp_register_style(
		'wpex-hover-animations',
		wpex_asset_url( 'lib/hover-css/hover-css.min.css' ),
		array(),
		'2.0.1'
	);

	// HoverIntent
	wp_register_script(
		'wpex-hoverintent',
		wpex_asset_url( 'js/core/hoverIntent.min.js' ),
		array( 'jquery' ),
		'1.10.1',
		true
	);

	// Supersubs
	wp_register_script(
		'wpex-supersubs',
		wpex_asset_url( 'js/core/supersubs.min.js' ),
		array( 'jquery' ),
		'0.3b',
		true
	);

	// Superfish
	wp_register_script(
		'wpex-superfish',
		wpex_asset_url( 'js/core/superfish.min.js' ),
		array( 'jquery', 'wpex-hoverintent', 'wpex-supersubs' ),
		'1.7.4',
		true
	);

	// Easing
	wp_register_script(
		'wpex-easing',
		wpex_asset_url( 'js/core/jquery.easing.min.js' ),
		array( 'jquery' ),
		'1.3.2',
		true
	);

	// Sidr
	wp_register_script(
		'wpex-sidr',
		wpex_asset_url( 'js/dynamic/sidr.min.js' ),
		array( 'jquery' ),
		'2.2.1',
		true
	);

	// SliderPro
	wp_register_style(
		'wpex-slider-pro',
		wpex_asset_url( 'lib/slider-pro/jquery.sliderPro.min.css' ),
		array(),
		'1.3'
	);

	wp_register_script(
		'wpex-slider-pro',
		wpex_asset_url( 'lib/slider-pro/jquery.sliderPro.min.js' ),
		array( 'jquery' ),
		'1.3',
		true
	);

	wp_localize_script(
		'wpex-slider-pro',
		'wpexSliderPro',
		array(
			// @todo change i18n to L10n
			'i18n' => array(
				'NEXT' => esc_html__( 'next slide', 'total' ),
				'PREV' => esc_html__( 'previous slide', 'total' ),
				'GOTO' => esc_html__( 'go to slide', 'total' ),
			),
		)
	);

	// Fancybox
	wp_register_style(
		'fancybox',
		wpex_asset_url( 'lib/fancybox/jquery.fancybox.min.css' ),
		array(),
		'3.5.7'
	);

	wp_register_script(
		'fancybox',
		wpex_asset_url( 'lib/fancybox/jquery.fancybox.min.js' ),
		array( 'jquery', WPEX_THEME_JS_HANDLE ),
		'3.5.7',
		true
	);

	wp_localize_script(
		'fancybox',
		'wpexLightboxSettings',
		wpex_get_lightbox_settings()
	);

	// Load Lightbox Globally if enabled
	if ( get_theme_mod( 'lightbox_auto', false )
		|| apply_filters( 'wpex_load_ilightbox_globally', get_theme_mod( 'lightbox_load_style_globally', false ) )
		) {
		wpex_enqueue_lightbox_scripts();
	}

	// Load More
	wp_register_script(
		'wpex-loadmore',
		wpex_asset_url( 'js/dynamic/wpex-loadmore.min.js' ),
		array( 'jquery', 'imagesloaded', WPEX_THEME_JS_HANDLE ),
		WPEX_THEME_VERSION,
		true
	);

	wp_register_script(
		'wpex-slider-pro-custom-thumbs',
		wpex_asset_url( 'lib/slider-pro/jquery.sliderProCustomThumbnails.min.js' ),
		array( 'jquery', 'wpex-slider-pro' ),
		WPEX_THEME_VERSION,
		true
	);

	// Social share
	wp_register_script(
		'wpex-social-share',
		wpex_asset_url( 'js/dynamic/wpex-social-share.min.js' ),
		array( WPEX_THEME_JS_HANDLE ),
		WPEX_THEME_VERSION,
		true
	);

	// Parallax Backgrounds
	wp_register_script(
		'wpex-scrolly2',
		wpex_asset_url( 'js/dynamic/scrolly2.min.js' ),
		array( 'jquery', WPEX_THEME_JS_HANDLE ),
		WPEX_THEME_VERSION,
		true
	);

}
add_action( 'wp_enqueue_scripts', 'wpex_register_scripts' );

/**
 * Core theme CSS.
 */
function wpex_enqueue_front_end_main_css() {

	// Declare theme handle
	$theme_handle = WPEX_THEME_STYLE_HANDLE; // !!! must go first !!!

	// Main style.css File
	wp_enqueue_style(
		$theme_handle,
		get_stylesheet_uri(),
		array(),
		WPEX_THEME_VERSION
	);

	// Check theme handle when child theme is active.
	if ( is_child_theme() ) {
		$parent_handle = apply_filters( 'wpex_parent_stylesheet_handle', 'parent-style' );
		if ( wp_style_is( $parent_handle ) ) {
			$theme_handle = $parent_handle;
		}
	}

	// Override main style.css with style-rtl.css
	wp_style_add_data( $theme_handle, 'rtl', 'replace' );

	// Mobile menu breakpoint CSS
	$mm_breakpoint = wpex_header_menu_mobile_breakpoint();
	$max_media     = false;
	$min_media     = false;

	if ( $mm_breakpoint < 9999 && wpex_is_layout_responsive() ) {
		$max_media = 'only screen and (max-width:' . $mm_breakpoint . 'px)';
		$min_media = 'only screen and (min-width:' . ( $mm_breakpoint + 1 )  . 'px)';
	}

	wp_enqueue_style(
		'wpex-mobile-menu-breakpoint-max',
		wpex_asset_url( 'css/wpex-mobile-menu-breakpoint-max.css' ),
		$theme_handle ? array( $theme_handle ) : array(),
		WPEX_THEME_VERSION,
		$max_media
	);

	wp_style_add_data( 'wpex-mobile-menu-breakpoint-max', 'rtl', 'replace' );

	if ( $min_media ) {

		wp_enqueue_style(
			'wpex-mobile-menu-breakpoint-min',
			wpex_asset_url( 'css/wpex-mobile-menu-breakpoint-min.css' ),
			$theme_handle ? array( $theme_handle ) : array(),
			WPEX_THEME_VERSION,
			$min_media
		);

		wp_style_add_data( 'wpex-mobile-menu-breakpoint-min', 'rtl', 'replace' );

	}

	// WPBakery
	if ( WPEX_VC_ACTIVE && wpex_has_vc_mods() ) {

		$deps = array( WPEX_THEME_STYLE_HANDLE );

		if ( wp_style_is( 'js_composer_front', 'registered' ) ) {
			$deps[] = 'js_composer_front';
		}

		wp_enqueue_style(
			'wpex-wpbakery',
			wpex_asset_url( 'css/wpex-wpbakery.css' ),
			$deps,
			WPEX_THEME_VERSION
		);

		wp_style_add_data( 'wpex-wpbakery', 'rtl', 'replace' );

	}

	// Load theme icons
	wp_enqueue_style( 'ticons' );

	// Total Shortcodes
	if ( get_theme_mod( 'extend_visual_composer', true ) ) {

		wp_enqueue_style(
			'vcex-shortcodes',
			wpex_asset_url( 'css/vcex-shortcodes.css' ),
			array(),
			WPEX_THEME_VERSION
		);

		wp_style_add_data( 'vcex-shortcodes', 'rtl', 'replace' );

	}

	// Customizer CSS
	if ( is_customize_preview() ) {

		wp_enqueue_style(
			'wpex-customizer-shortcuts',
			wpex_asset_url( 'css/wpex-customizer-shortcuts.css' ),
			array(),
			WPEX_THEME_VERSION
		);

	}

}
add_action( 'wp_enqueue_scripts', 'wpex_enqueue_front_end_main_css' );

/**
 * Load theme js.
 */
function wpex_enqueue_front_end_js() {

	// Comment reply
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Sidr Mobile Menu
	if ( 'sidr' == wpex_header_menu_mobile_style() && wpex_has_header_mobile_menu() ) {
		wp_enqueue_script( 'wpex-sidr' );
	}

	// Menu dropdowns
	if ( wpex_has_header_menu() ) {
		wp_enqueue_script( 'wpex-superfish' );
	}

	// Retina JS
	if ( wpex_is_retina_enabled() ) {

		wp_enqueue_script(
			'wpex-retina',
			wpex_asset_url( 'js/dynamic/retina.js' ),
			array( 'jquery' ),
			'1.3',
			true
		);

	}

	// Easing (used in total core so it's required)
	wp_enqueue_script( 'wpex-easing' );

	// Load minified theme JS
	if ( get_theme_mod( 'minify_js_enable', true ) ) {

		wp_enqueue_script(
			WPEX_THEME_JS_HANDLE,
			wpex_asset_url( 'js/total.min.js' ),
			array( 'jquery' ),
			WPEX_THEME_VERSION,
			true
		);

	}

	// Load all non-minified Theme js
	else {

		wp_enqueue_script(
			'wpex-equal-heights',
			wpex_asset_url( 'js/core/jquery.wpexEqualHeights.js' ),
			array( 'jquery' ),
			'1.0',
			true
		);

		// Core global functions
		wp_enqueue_script(
			WPEX_THEME_JS_HANDLE,
			wpex_asset_url( 'js/total.js' ),
			array( 'jquery' ),
			WPEX_THEME_VERSION,
			true
		);

	}

	// Localize core js
	if ( function_exists( 'wpex_js_localize_data' ) ) {
		wp_localize_script( WPEX_THEME_JS_HANDLE, 'wpexLocalize', wpex_js_localize_data() );
	}

}
add_action( 'wp_enqueue_scripts', 'wpex_enqueue_front_end_js' );

/**
 * Remove block library CSS if Gutenberg is disabled via WPBakery or if the Classic Editor plugin is active
 */
function wpex_remove_block_library_css() {
	if ( apply_filters( 'wpex_remove_block_library_css', true ) && ! current_theme_supports( 'gutenberg-editor' ) ) {
		wp_dequeue_style( 'wp-block-library' );
		if ( WPEX_WOOCOMMERCE_ACTIVE ) {
			wp_dequeue_style( 'wc-block-style' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'wpex_remove_block_library_css', 100 );