<?php
/**
 * Theme tweaks for WooCommerce images
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 *
 */

namespace TotalTheme\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class Product_Gallery {

	/**
	 * Our single Product_Gallery instance.
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
	 * Create or retrieve the instance of Product_Gallery.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Product_Gallery;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		if ( is_customize_preview() ) {
			add_action( 'wp', array( $this, 'add_theme_support' ) ); // run later to work correctly with customizer.
		} else {
			add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
		}

		// Enqueue lightbox scripts
		add_action( 'wp_footer', array( $this, 'lightbox_scripts' ) );

		// Custom product gallery flexslider options
		add_filter( 'woocommerce_single_product_carousel_options', array( $this, 'flexslider_options' ) );

		// Gallery columns
		add_filter( 'woocommerce_product_thumbnails_columns', array( $this, 'columns' ) );

		// Custom gallery CSS
		add_filter( 'wpex_head_css', array( $this, 'custom_css' ) );

	}

	/**
	 * Add theme support.
	 */
	public function add_theme_support() {

		if ( get_theme_mod( 'woo_product_gallery_slider', true ) ) {
			add_theme_support( 'wc-product-gallery-slider' );
		} else {
			remove_theme_support( 'wc-product-gallery-slider' );
		}

		if ( get_theme_mod( 'woo_product_gallery_zoom', true ) ) {
			add_theme_support( 'wc-product-gallery-zoom' );
		} else {
			remove_theme_support( 'wc-product-gallery-zoom' );
		}

		if ( 'woo' === $this->lightbox_type() ) {
			add_theme_support( 'wc-product-gallery-lightbox' );
		} else {
			remove_theme_support( 'wc-product-gallery-lightbox' );
		}

	}

	/**
	 * Enqueue theme lightbox scripts.
	 *
	 * @since 4.1
	 */
	public function lightbox_scripts() {

		if ( 'total' !== $this->lightbox_type() || ! is_product() ) {
			return;
		}

		wpex_enqueue_lightbox_scripts();

		$file = 'js/dynamic/woocommerce/wpex-lightbox-gallery.js';

		if ( get_theme_mod( 'minify_js_enable', true ) ) {
			$file = 'js/dynamic/woocommerce/wpex-lightbox-gallery.min.js';
		}

		if ( class_exists( 'WC_Additional_Variation_Images' )
			&& apply_filters( 'wpex_woo_additional_variation_images_custom_lightbox', true )
		) {
			$file = 'js/dynamic/woocommerce/wpex-lightbox-additional-variation-images.min.js'; // this plugin changes the gallery HTML :(
		}

		wp_enqueue_script(
			'wpex-wc-product-lightbox',
			wpex_asset_url( $file ),
			array( 'jquery', WPEX_THEME_JS_HANDLE ),
			WPEX_THEME_VERSION,
			true
		);

		wp_localize_script(
			'wpex-wc-product-lightbox',
			'wpex_wc_lightbox',
			array(
				'showTitle' => get_theme_mod( 'woo_product_gallery_lightbox_titles' ) ? 1 : 0,
			)
		);

	}

	/**
	 * Check what lightbox type is enabled for products.
	 *
	 * @since 5.0
	 */
	public function lightbox_type() {
		return get_theme_mod( 'woo_product_gallery_lightbox', 'total' );
	}

	/**
	 * Custom product gallery flexslider options
	 *
	 * Not used at the moment due to WooCommerce bugs
	 *
	 * @since 4.1
	 */
	public function flexslider_options( $options ) {
		$options['directionNav'] = true; // Not sure if I like it
		$speed = get_theme_mod( 'woo_product_gallery_slider_animation_speed', '600' );
		$options['animationSpeed'] = intval( $speed );
		return $options;
	}

	/**
	 * Define columns for gallery
	 *
	 * @since 4.3
	 */
	public function columns() {
		return ( $cols = absint( get_theme_mod( 'woocommerce_gallery_thumbnails_count' ) ) ) ? $cols : 5;
	}

	/**
	 * Custom CSS for gallery
	 *
	 * @since 4.1
	 */
	public function custom_css( $css ) {
		if ( is_singular( 'product' ) ) {
			$thumb_cols = self::columns();
			if ( $thumb_cols && 5 !== $thumb_cols ) {
				if ( get_theme_mod( 'woo_product_gallery_slider', true ) ) {
					$css .= '.woocommerce div.product div.images .flex-control-thumbs li:nth-child(4n+1) {clear: none;}';
					$css .= '.product-variation-thumbs a, .woocommerce div.product div.images .flex-control-thumbs li { width:' . 100 / $thumb_cols . '%;}';
					$css .= '.woocommerce div.product div.images .flex-control-thumbs li:nth-child(' . $thumb_cols . 'n+1) {clear: both;}';
				} else {
					$css .= '.woocommerce .product div.images > .woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image:nth-child(n+2) { width:' . 100 / $thumb_cols . '%;}';
				}
			}
		}
		return $css;
	}

}
Product_Gallery::instance();