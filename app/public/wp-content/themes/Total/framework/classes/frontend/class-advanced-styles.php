<?php
/**
 * Advanced frontend styles based on user settings.
 *
 * @package Total WordPress Theme
 * @subpackage Classes
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class AdvancedStyles {

	/**
	 * Define custom css var.
	 */
	protected $css = '';

	/**
	 * Our single AdvancedStyles instance.
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disabled instantiation.
	}

	/**
	 * Disable the cloning of this class.
	 *
	 * @return void
	 */
	final public function __clone() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Disable the wakeup of this class.
	 *
	 * @return void
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of AdvancedStyles.
	 *
	 * @return AdvancedStyles
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new AdvancedStyles;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_filter( 'wpex_head_css', array( $this, 'advanced_styles_css' ), 999 );
	}

	/**
	 * Generates the CSS output.
	 */
	public function advanced_styles_css( $output ) {

		$this->header_background();
		$this->overlay_header();
		$this->shrink_sticky_header_height();
		$this->logo_mobile_side_margin();
		$this->logo_height();
		$this->page_header_title();
		$this->mobile_menu_toggles();
		$this->vertical_header_width();
		$this->footer_background();
		$this->footer_callout_background();

		if ( ! empty( $this->css ) ) {
			$output .= '/*ADVANCED STYLING CSS*/' . $this->css;
		}

		return $output;

	}

	/**
	 * Shrink header height.
	 */
	protected function shrink_sticky_header_height() {

		// Sticky Header shrink height
		// Must add CSS in Visual Composer live editor to keep logo height consistancy
		if ( wpex_has_shrink_sticky_header() || wpex_vc_is_inline() ) {

			$shrink_header_style = wpex_sticky_header_style();

			if ( 'shrink' == $shrink_header_style || 'shrink_animated' == $shrink_header_style ) {

				$start_height = intval( get_theme_mod( 'fixed_header_shrink_start_height' ) );
				$start_height_escaped = $start_height ? $start_height : 60;

				$this->css .= '/*Shrink Fixed header*/';

				if ( wpex_vc_is_inline() ) {
					$this->css .= '#site-header #site-logo img{max-height:' . $start_height_escaped .'px !important}';
				} else {

					$this->css .= '.shrink-sticky-header #site-logo img{max-height:' . $start_height_escaped .'px !important}';

					$end_height = intval( get_theme_mod( 'fixed_header_shrink_end_height' ) );
					$end_height_escaped = $end_height ? $end_height : 50;

					$header_height_escaped = $end_height_escaped + 20;

					$this->css .= '.sticky-header-shrunk #site-header-inner{height:'. $header_height_escaped .'px;}';
					$this->css .= '.shrink-sticky-header.sticky-header-shrunk .navbar-style-five .dropdown-menu > li > a{height:'. $end_height_escaped .'px;}';
					$this->css .= '.shrink-sticky-header.sticky-header-shrunk #site-logo img{max-height:'. $end_height_escaped .'px !important;}';
				}

			}

		}

	}

	/**
	 * Header background.
	 */
	protected function header_background() {
		$header_bg = wpex_header_background_image();
		if ( $header_bg ) {
			$this->css .= '#site-header{background-image:url(' . esc_url( $header_bg ) . ');}';
		}
	}

	/**
	 * Overlay header.
	 */
	protected function overlay_header() {

		$post_id = wpex_get_current_post_id();

		if ( $post_id && wpex_has_post_meta( 'wpex_overlay_header' ) && wpex_has_overlay_header() ) {

			// Custom overlay header font size
			$overlay_header_font_size = get_post_meta( $post_id, 'wpex_overlay_header_font_size', true );

			if ( $overlay_header_font_size ) {
				$this->css .= '#site-navigation, #site-navigation .dropdown-menu a{font-size:' . intval( $overlay_header_font_size ) . 'px;}';
			}

			// Apply overlay header background color
			// Note we use background and not background-color
			$overlay_header_bg = get_post_meta( $post_id, 'wpex_overlay_header_background', true );

			if ( $overlay_header_bg ) {
				$this->css .= '#site-header.overlay-header.dyn-styles{background:' . esc_attr( $overlay_header_bg ) . '; }';
			}

		}

	}

	/**
	 * Logo mobile side margin.
	 */
	protected function logo_mobile_side_margin() {

		$margin_escaped = absint( get_theme_mod( 'logo_mobile_side_offset' ) );

		if ( ! empty( $margin_escaped ) ) {

			$mm_breakpoint = wpex_header_menu_mobile_breakpoint();

			$this->css .= '/*Mobile Menu Side Offset*/';

			if ( $mm_breakpoint < 9999 ) {
				$this->css .= '@media only screen and (max-width:' . $mm_breakpoint . 'px) {';
			}

			$this->css .= 'body.has-mobile-menu #site-logo {';

				if ( is_rtl() ) {
					$this->css .= 'margin-left:' . $margin_escaped . 'px;';
				} else {
					$this->css .= 'margin-right:' . $margin_escaped . 'px;';
				}

			$this->css .= '}';

			if ( $mm_breakpoint < 9999 ) {
				$this->css .= '}';
			}

		}

	}

	/**
	 * Custom logo height.
	 */
	protected function logo_height() {
		if ( get_theme_mod( 'apply_logo_height', false ) ) {
			$height = intval( get_theme_mod( 'logo_height' ) );
			if ( ! empty( $height ) ) {
				$this->css .= '/*Logo Height*/';
				$this->css .= '#site-logo img{max-height:' . $height . 'px;}';
			}
		}
	}

	/**
	 * Page header title.
	 */
	protected function page_header_title() {
		if ( ! wpex_has_post_meta( 'wpex_post_title_style' ) ) {
			$page_header_bg = wpex_page_header_background_image(); // already passed through wpex_get_image_url
			if ( $page_header_bg ) {
				$this->css .= '.page-header.has-bg-image{background-image:url(' . esc_url( $page_header_bg ) . ');}';
			}
		}
	}

	/**
	 * Mobile menu toggles.
	 */
	protected function mobile_menu_toggles() {

		$icon_color = get_theme_mod( 'mobile_menu_icon_color' );
		if ( $icon_color ) {
			$icon_color_escaped = sanitize_hex_color( $icon_color );
			$this->css .= '#mobile-menu .wpex-bars>span, #mobile-menu .wpex-bars>span::before, #mobile-menu .wpex-bars>span::after{background-color:' . $icon_color_escaped . ';}';
		}

		$icon_color_hover = get_theme_mod( 'mobile_menu_icon_color_hover' );
		if ( $icon_color_hover ) {
			$icon_color_hover_escaped = sanitize_hex_color( $icon_color_hover );
			$this->css .= '#mobile-menu a:hover .wpex-bars>span, #mobile-menu a:hover .wpex-bars>span::before, #mobile-menu a:hover .wpex-bars>span::after{background-color:' . $icon_color_hover_escaped . ';}';
		}
	}

	/**
	 * Vertical header width.
	 */
	protected function vertical_header_width() {

		$width = get_theme_mod( 'vertical_header_width' );

		if ( $width ) {

			$width_escaped = absint( $width );

			if ( ! empty( $width_escaped ) && wpex_has_vertical_header() ) {

				$mm_breakpoint = wpex_header_menu_mobile_breakpoint();

				if ( $mm_breakpoint >= 9999 ) {
					return;
				}

				$this->css .= '@media only screen and ( min-width: ' . ( $mm_breakpoint + 1 ) . 'px ) {';

					$this->css .= 'body.wpex-has-vertical-header #site-header {';
						$this->css .= 'width:' . $width_escaped  . 'px;';
					$this->css .= '}';

					if ( is_rtl() ) {

						$this->css .= 'body.wpex-has-vertical-header.full-width-main-layout #wrap {';
							$this->css .= 'padding-right:' . $width_escaped  . 'px;';
						$this->css .= '}';

						$this->css .= 'body.wpex-has-vertical-header.boxed-main-layout #wrap {';
							$this->css .= 'padding-right:' . $width_escaped  . 'px;';
						$this->css .= '}';

					} else {

						$this->css .= 'body.wpex-has-vertical-header.full-width-main-layout #wrap {';
							$this->css .= 'padding-left:' . $width_escaped  . 'px;';
						$this->css .= '}';

						$this->css .= 'body.wpex-has-vertical-header.boxed-main-layout #wrap {';
							$this->css .= 'padding-left:' . $width_escaped  . 'px;';
						$this->css .= '}';

					}

				$this->css .= '}';

			}

		}

	}

	/**
	 * Footer background.
	 */
	protected function footer_background() {
		$background = wpex_get_image_url( get_theme_mod( 'footer_bg_img' ) );
		if ( $background ) {
			$this->css .= '#footer{background-image:url(' . esc_url( $background ) . ');}';
		}
	}

	/**
	 * Footer callout background.
	 */
	protected function footer_callout_background() {
		$background = wpex_get_image_url( get_theme_mod( 'footer_callout_bg_img' ) );
		if ( $background ) {
			$this->css .= '#footer-callout-wrap{background-image:url(' . esc_url( $background ) . ');}';
		}
	}

}
AdvancedStyles::instance();