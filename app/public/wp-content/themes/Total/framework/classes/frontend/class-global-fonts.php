<?php
/**
 * Global_Fonts Class | Loads site-wide fonts and adds CSS font font targeting.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class Global_Fonts {

	/**
	 * Our single Global_Fonts instance.
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
	 * Create or retrieve the instance of Global_Fonts.
	 *
	 * @return Global_Fonts
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Global_Fonts;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Initialization hooks.
	 *
	 * @since 5.0
	 */
	public function init_hooks() {

		// Load any global adobe/google fonts globally
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_registered_fonts' ) );

		// Load all custom fonts globally
		add_filter( 'wpex_head_css', array( $this, 'add_custom_fonts_css' ) );
		add_filter( 'wpex_head_css', array( $this, 'assign_registered_fonts' ) );

	}

	/**
	 * Load globally registered custom fonts.
	 *
	 * @since 5.0
	 */
	public function enqueue_registered_fonts() {

		$fonts = wpex_get_registered_fonts();

		if ( ! empty( $fonts ) ) {

			foreach( $fonts as $font => $args ) {

				$type = $args['type'];

				if ( ! in_array( $type, array( 'google', 'adobe' ) ) ) {
					continue;
				}

				if ( ! empty( $args['is_global'] ) || ! empty( $args['assign_to'] ) ) {
					wpex_enqueue_font( $font, 'registered', $args );
				}

			}

		}

	}

	/**
	 * Adds Header CSS for custom uploaded fonts.
	 *
	 * @since 5.0
	 */
	public function add_custom_fonts_css( $css ) {

		$fonts = wpex_get_registered_fonts();

		if ( ! empty( $fonts ) ) {

			foreach( $fonts as $font => $args ) {

				if ( empty( $args['type'] ) || empty( $args['custom_fonts'] ) || 'custom' !== $args['type'] ) {
					continue;
				}

				$custom_font_css = wpex_render_custom_font_css( $font, $args );

				if ( $custom_font_css ) {
					$css .= '/*CUSTOM FONTS*/' . $custom_font_css;
				}

			}

		}

		return $css;

	}

	/**
	 * Assign registered fonts to their corresponding elements.
	 *
	 * @since 5.0
	 */
	public function assign_registered_fonts( $css ) {

		$fonts = wpex_get_registered_fonts();

		if ( ! empty( $fonts ) ) {

			$registered_font_css = '';

			foreach( $fonts as $font => $args ) {
				if ( ! empty( $args['assign_to'] ) ) {
					foreach( $args['assign_to'] as $el ) {
						$font_css = wpex_parse_css( wpex_sanitize_font_family( $font ), 'font-family', $el );
						if ( $font_css ) {
							$registered_font_css .= $font_css;
						}
					}
				}
			}

			if ( $registered_font_css ) {
				$css .= '/*REGISTERED FONT ASSIGNEMENT*/' . $registered_font_css;
			}

		}

		return $css;

	}

}
Global_Fonts::instance();