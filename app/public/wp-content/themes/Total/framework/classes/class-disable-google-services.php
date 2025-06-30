<?php
/**
 * Disable Google Searvices
 *
 * @package Total WordPress Theme
 * @subpackage 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class DisableGoogleServices {

	/**
	 * Our single DisableGoogleServices instance.
	 *
	 * @var DisableGoogleServices
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
	 * Create or retrieve the instance of DisableGoogleServices.
	 *
	 * @return DisableGoogleServices
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new DisableGoogleServices;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Remove Google Fonts from theme fonts array
		add_filter( 'wpex_google_fonts_array', '__return_empty_array' );

		// Remove Google Fonts from WPBakery
		add_filter( 'vc_google_fonts_render_filter', '__return_false' );
		add_filter( 'vc_google_fonts_get_fonts_filter', '__return_false' );

		// Remove scripts
		add_action( 'wp_print_scripts', array( $this, 'remove_scripts' ), 10 );

		// Remove inline scripts
		add_action( 'wp_footer', array( $this, 'remove_inline_scripts' ), 10 );

	}

	/**
	 * Remove scripts
	 *
	 * @since 2.1.0
	 */
	public function remove_scripts() {
		wp_dequeue_script( 'webfont' );
	}

	/**
	 * Remove footer scripts
	 *
	 * @since 2.1.0
	 */
	public function remove_inline_scripts() {

		// Get global styles
		global $wp_styles;

		// Loop through and remove VC fonts
		if ( $wp_styles ) {
			foreach ( $wp_styles->registered as $handle => $data ) {
				if ( false !== strpos( $handle, 'vc_google_fonts_' ) ) {
					wp_deregister_style( $handle );
					wp_dequeue_style( $handle );
				}
			}
		}

	}

}
DisableGoogleServices::instance();