<?php
/**
 * Adds custom CSS to alter all main theme border colors.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 * @todo go through all classes and remove any no longer needed
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class BorderColors {

	/**
	 * Our single BorderColors instance.
	 *
	 * @var BorderColors
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
	 * Create or retrieve the instance of BorderColors.
	 *
	 * @return BorderColors
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new BorderColors;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		if ( is_customize_preview() ) {
			add_action( 'wp_head', array( $this, 'customizer_css' ), 99 );
			add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
		} else {
			add_filter( 'wpex_head_css', array( $this, 'live_css' ), 1 );
		}

	}

	/**
	 * Array of elements.
	 */
	public function elements() {
		return apply_filters( 'wpex_border_color_elements', array(

			// Utility classes
			'.wpex-border-main',
			'.wpex-bordered',
			'.wpex-bordered-list li',
			'.wpex-bordered-list li:first-child',
			'.wpex-divider',

			// General
			'.theme-heading.border-side span.text:after',
			'.theme-heading.border-w-color',
			'#comments .comment-body',
			'.theme-heading.border-bottom',

			// Pagination
			'ul .page-numbers a,
			 a.page-numbers,
			 span.page-numbers',

			// Widgets
			'.modern-menu-widget',
			'.modern-menu-widget li',
			'.modern-menu-widget li ul',

			'#sidebar .widget_nav_menu a',
			'#sidebar .widget_nav_menu ul > li:first-child > a',
			'.widget_nav_menu_accordion .widget_nav_menu a',
			'.widget_nav_menu_accordion .widget_nav_menu ul > li:first-child > a',

			// Modules
			'.vcex-blog-entry-details',
			'.theme-button.minimal-border',
			'.vcex-login-form',
			'.vcex-recent-news-entry',

		) );
	}

	/**
	 * Generates the CSS output.
	 */
	public function generate() {

		// Get array to loop through
		$elements = $this->elements();

		// Return if array is empty
		if ( empty( $elements ) ) {
			return;
		}

		// Get border color
		$color = get_theme_mod( 'main_border_color', '#eee' );

		// Check for theme mod and make sure it's not the same as the theme's default color
		if ( $color && ! in_array( $color, array( '#eee', '#eeeeee' ), true ) ) {

			// Define css var
			$css = '';

			// Borders
			$elements = implode( ',', $elements );
			$css .= $elements . '{border-color:' . $color . ';}';

			// Return CSS
			if ( $css ) {
				return '/*BORDER COLOR*/' . $css;
			}

		}

	}

	/**
	 * Live site output.
	 */
	public function live_css( $output ) {
		if ( $css = $this->generate() ) {
			$output .= $css;
		}
		return $output;
	}

	/**
	 * Customizer Output.
	 */
	public function customizer_css() {
		echo '<style id="wpex-borders-css">' . $this->generate() . '</style>';
	}

	/**
	 * Customizer Live JS.
	 */
	public function customize_preview_init() {

		$elements = $this->elements();

		if ( empty( $elements ) ) {
			return;
		}

		wp_enqueue_script( 'wpex-customizer-border-colors',
			wpex_asset_url( 'js/dynamic/customizer/wpex-border-colors.min.js' ),
			array( 'customize-preview' ),
			WPEX_THEME_VERSION,
			true
		);

		wp_localize_script(
			'wpex-customizer-border-colors',
			'wpexBorderColorElements',
			$elements
		);

	}

}
BorderColors::instance();