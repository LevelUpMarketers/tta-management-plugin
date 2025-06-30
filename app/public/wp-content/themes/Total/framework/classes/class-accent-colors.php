<?php
/**
 * Adds custom CSS to the site to tweak the main accent colors
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 * @todo better naming for filters?
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Accent_Colors {

	/**
	 * Our single Accent_Colors instance.
	 *
	 * @var Accent_Colors
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
	 * Create or retrieve the instance of Accent_Colors.
	 *
	 * @return Accent_Colors
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Accent_Colors;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		if ( is_customize_preview() ) {
			add_action( 'wp_head', array( $this, 'customizer_css' ), 10 );
		} else {
			add_filter( 'wpex_head_css', array( $this, 'live_css' ), 1 );
		}

	}

	/**
	 * Color Targets.
	 */
	public static function color_targets() {

		$targets = array(

			// Utility classes
			'.wpex-text-accent',
			'.wpex-hover-text-accent:hover',
			'.wpex-accent-color', // @todo deprecate

			// Menus
			'#site-navigation .dropdown-menu > li.menu-item > a:hover',
			'#site-navigation .dropdown-menu > li.menu-item.current-menu-item > a',
			'#site-navigation .dropdown-menu > li.menu-item.current-menu-parent > a',

			// Widgets
			'.modern-menu-widget a:hover',

			// Theme button
			'.theme-button.outline',
			'.theme-button.clean',

			// Links
			'a',
			'h1 a:hover',
			'h2 a:hover',
			'h3 a:hover',
			'h4 a:hover',
			'h5 a:hover',
			'h6 a:hover',
			'.entry-title a:hover',
			'.meta a:hover',
			'.wpex-heading a:hover',

		);

		if ( current_theme_supports( 'gutenberg-editor' ) ) {
			$targets[] = '.is-style-outline .wp-block-button__link:not(.has-color):not(.has-background)';
			$targets[] = '.wp-block-button__link.is-style-outline:not(.has-color):not(.has-background)';
		}

		return (array) apply_filters( 'wpex_accent_texts', $targets );

	}

	/**
	 * Background Targets.
	 */
	public static function background_targets() {

		$targets = array(

			// Utility
			'.wpex-bg-accent',
			'.wpex-hover-bg-accent:hover',

			// Alt accent
			'.wpex-bg-accent_alt',
			'.wpex-hover-bg-accent_alt:hover',

			// Badge
			'.wpex-badge',

			// Legacy utility class
			'.wpex-accent-bg',

			// Buttons
			'input[type="submit"]',
			'.theme-button',
			'button',
			'.button',
			'.active > .theme-button',
			'.theme-button.active',

			// Outline buttons
			'.theme-button.outline:hover',
			'.active > .theme-button.outline',
			'.theme-button.outline.active',
			'.theme-button.outline:hover',

			// Everything else
			'.post-edit a',
			'.background-highlight', // legacy
			'.tagcloud a:hover',
			'.post-tags a:hover',
			'.wpex-carousel .owl-dot.active',
			'.wpex-carousel .owl-prev',
			'.wpex-carousel .owl-next',
			'body #header-two-search #header-two-search-submit',
			'#site-navigation .menu-button > a > span.link-inner',
			'.modern-menu-widget li.menu-item.current-menu-item a',
			'#sidebar .widget_nav_menu .current-menu-item > a',
			'.widget_nav_menu_accordion .widget_nav_menu li.menu-item.current-menu-item > a',
			'#site-navigation-wrap.has-menu-underline .main-navigation-ul>li>a>.link-inner::after',
			'#wp-calendar caption',
			'#wp-calendar tbody td:hover a',

		);

		if ( current_theme_supports( 'gutenberg-editor' ) ) {
			$targets[] = '.is-style-fill .wp-block-button__link:not(.has-background)';
			$targets[] = '.wp-block-button__link.is-style-fill:not(.has-background)';
			$targets[] = '.is-style-outline .wp-block-button__link:not(.has-color):not(.has-background):hover';
			$targets[] = '.wp-block-button__link.is-style-outline:not(.has-color):not(.has-background):hover';
		}

		return (array) apply_filters( 'wpex_accent_backgrounds', $targets );

	}

	/**
	 * Border Targets.
	 */
	public static function border_targets() {

		$targets = array(

			// Utility class
			'.wpex-border-accent',
			'.wpex-hover-border-accent:hover',

			// Everything else'
			'.theme-button.outline',
			'.wpex-dropdown-top-border #site-navigation .dropdown-menu li.menu-item ul.sub-menu' => array( 'top' ),
			'.theme-heading.border-w-color span.text' => array( 'bottom' ),

		);

		if ( current_theme_supports( 'gutenberg-editor' ) ) {
			$targets[] = '.is-style-outline .wp-block-button__link:not(.has-color):not(.has-background)';
			$targets[] = '.wp-block-button__link.is-style-outline:not(.has-color):not(.has-background)';
		}

		return (array) apply_filters( 'wpex_accent_borders', $targets );

	}

	/**
	 * Hover Background targets.
	 */
	public static function hover_background_targets() {

		$targets = array(
			'.wpex-bg-accent_alt',
			'.wpex-hover-bg-accent_alt:hover',

			'.post-edit a:hover',
			'.theme-button:hover',
			'input[type="submit"]:hover',
			'button:hover',
			'.button:hover',
			'.active > .theme-button',
			'.theme-button.active',
			'.wpex-carousel .owl-prev:hover',
			'.wpex-carousel .owl-next:hover',
			'#site-navigation .menu-button > a > span.link-inner:hover',
		);

		return (array) apply_filters( 'wpex_accent_hover_backgrounds', $targets );

	}

	/**
	 * Hover Text targets.
	 */
	public static function hover_text_targets() {

		$targets = array(
			'.wpex-text-accent_alt',
			'.wpex-hover-text-accent_alt:hover',
		);

		return (array) apply_filters( 'wpex_accent_hover_texts', $targets );

	}

	/**
	 * All Targets.
	 */
	public static function all_targets() {
		return array(
			// Main accent
			'texts'            => self::color_targets(),
			'backgrounds'      => self::background_targets(),
			'borders'          => self::border_targets(),
			// Alt accent
			'backgroundsHover' => self::hover_background_targets(),
			'textsHover'       => self::hover_text_targets(),
		);
	}

	/**
	 * Generates the Accent css.
	 */
	public function accent_css() {

		$accent = wpex_get_custom_accent_color();
		$accent_escaped = esc_attr( $accent );

		if ( ! $accent_escaped ) {
			return;
		}

		$css = '';

		// Texts
		$color_targets = self::color_targets();
		if ( ! empty( $color_targets ) ) {
			$color_targets_escaped = array_map( 'wp_strip_all_tags', $color_targets );
			$css .= implode( ',', $color_targets_escaped ) . '{color:' . $accent_escaped . ';}';
		}

		// Backgrounds
		$bg_targets = self::background_targets();
		if ( ! empty( $bg_targets ) ) {
			$bg_targets_escaped = array_map( 'wp_strip_all_tags', $bg_targets );
			$css .= implode( ',', $bg_targets_escaped ) . '{background-color:' . $accent_escaped . ';}';
		}

		// Borders
		$border_targets = self::border_targets();
		if ( ! empty( $border_targets ) ) {
			foreach ( $border_targets as $key => $val ) {
				if ( is_array( $val ) ) {
					$css .= $key . '{';
					foreach ( $val as $key => $val ) {
						$property_escaped = 'border-' . wp_strip_all_tags( trim( $val ) ) . '-color';
						$css .= $property_escaped . ':' . $accent_escaped . ';';
					}
					$css .= '}';
				} else {
					$css .= wp_strip_all_tags( trim( $val ) ) . '{border-color:' . $accent_escaped . ';}';
				}
			}
		}

		// Return CSS
		if ( $css ) {
			return $css;
		}

	}

	/**
	 * Generates the Accent hover css.
	 */
	public function accent_hover_css() {

		$accent = wpex_get_custom_accent_color_hover();
		$accent_escaped = esc_attr( $accent );

		if ( ! $accent_escaped ) {
			return;
		}

		$css = '';

		$hover_background_targets = self::hover_background_targets();

		if ( ! empty( $hover_background_targets ) ) {
			$hover_background_targets_escaped = array_map( 'wp_strip_all_tags', $hover_background_targets );
			$css .= implode( ',', $hover_background_targets_escaped ) . '{background-color:' . $accent_escaped . ';}';
		}

		$hover_text_targets = self::hover_text_targets();

		if ( ! empty( $hover_text_targets ) ) {
			$hover_text_targets_escaped = array_map( 'wp_strip_all_tags', $hover_text_targets );
			$css .= implode( ',', $hover_text_targets_escaped ) . '{color:' . $accent_escaped . ';}';
		}

		return $css;

	}

	/**
	 * Customizer Output.
	 */
	public function customizer_css() {
		echo '<style id="wpex-accent-css">' . $this->accent_css() . '</style>';
		echo '<style id="wpex-accent-hover-css">' . $this->accent_hover_css() . '</style>';
	}

	/**
	 * Live site output.
	 */
	public function live_css( $output ) {
		$accent_css = $this->accent_css();
		if ( $accent_css ) {
			$output .= '/*ACCENT COLOR*/' . $accent_css;
		}
		$accent_hover_css = $this->accent_hover_css();
		if ( $accent_hover_css ) {
			$output .= '/*ACCENT HOVER COLOR*/' . $accent_hover_css;
		}
		return $output;
	}

}
Accent_Colors::instance();