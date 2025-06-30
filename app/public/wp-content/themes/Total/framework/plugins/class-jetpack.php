<?php
/**
 * JetPack Configuration Class
 *
 * @package Total WordPress Theme
 * @subpackage Jetpack
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Jetpack {

	/**
	 * Our single Jetpack instance.
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
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of Jetpack.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Jetpack;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Social share
		if ( \Jetpack::is_module_active( 'sharedaddy' ) ) {

			if ( wpex_is_request( 'frontend' ) ) {

				// Remove default filters
				add_action( 'loop_start', array( $this, 'remove_share' ) );

				// Social share should always be enabled & disabled via blocks/theme filter
				add_filter( 'sharing_show', '__return_true' );

				// Enqueue scripts if social share is enabled
				add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

				// Replace social share
				add_filter( 'wpex_custom_social_share', array( $this, 'alter_share' ) );

			}

			// Remove Customizer settings
			add_filter( 'wpex_customizer_sections', array( $this, 'remove_customizer_settings' ), 40 );

		}

		// Carousel
		if ( \Jetpack::is_module_active( 'carousel' ) || \Jetpack::is_module_active( 'tiled-gallery' ) ) {
			add_filter( 'wpex_custom_wp_gallery', '__return_false' );
		}

	}

	/**
	 * Removes jetpack default loop filters
	 *
	 * @version 3.3.5
	 */
	public function remove_share() {
		remove_filter( 'the_content', 'sharing_display', 19 );
		remove_filter( 'the_excerpt', 'sharing_display', 19 );
	}

	/**
	 * Enqueue scripts if social share is enabled
	 *
	 * @version 3.3.5
	 */
	public function load_scripts() {
		if ( wpex_has_social_share() ) {
			add_filter( 'sharing_enqueue_scripts', '__return_true' );
		}
	}

	/**
	 * Replace Total social share with sharedaddy
	 *
	 * @version 3.3.5
	 */
	public function alter_share() {
		if ( function_exists( 'sharing_display' ) ) {
			return sharing_display( '', false ); // text, echo
		}
	}

	/**
	 * Remove Customizer settings
	 *
	 * @version 3.3.5
	 */
	public function remove_customizer_settings( $array ) {
		unset( $array['wpex_social_sharing'] );
		return $array;
	}

}
Jetpack::instance();