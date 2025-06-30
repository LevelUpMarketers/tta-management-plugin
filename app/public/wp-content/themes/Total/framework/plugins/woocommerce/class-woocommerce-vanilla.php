<?php
/**
 * Vanilla WooCommerce (very basic WooCommerce support)
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 */

namespace TotalTheme\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class WooCommerce_Vanilla {

	/**
	 * Our single WooCommerce_Vanilla instance.
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
	 * Create or retrieve the instance of WooCommerce_Vanilla.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new WooCommerce_Vanilla;
			static::$instance->includes();
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Includes.
	 */
	public function includes() {
		require_once WPEX_FRAMEWORK_DIR . 'plugins/woocommerce/customize/class-vanilla-settings.php';
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Add theme support
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );

		// Remove title from main shop
		add_filter( 'woocommerce_show_page_title', '__return_false' );

		// Remove category descriptions because they are added by the theme
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );

	}

	/**
	 * Register theme support.
	 */
	public function add_theme_support() {
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-slider' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
	}

}
WooCommerce_Vanilla::instance();