<?php
/**
 * WooCommerce accent colors
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 *
 */

namespace TotalTheme\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class Accent_Colors {

	/**
	 * Our single Accent_Colors instance.
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
	 * Create or retrieve the instance of Accent_Colors.
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
	 *
	 * @since 5.0
	 */
	public function init_hooks() {
		add_filter( 'wpex_accent_texts', array( $this, 'accent_texts' ) );
		add_filter( 'wpex_accent_borders', array( $this, 'accent_borders' ) );
		add_filter( 'wpex_accent_backgrounds', array( $this, 'accent_backgrounds' ) );
		add_filter( 'wpex_border_color_elements', array( $this, 'border_color_elements' ) );
	}

	/**
	 * Adds border accents for WooCommerce styles.
	 *
	 * @since 4.1
	 */
	public function accent_texts( $texts ) {
		return array_merge( array(
			'.woocommerce .order-total td',
			'.price > .amount',
			'.price ins .amount',
		), $texts );
	}

	/**
	 * Adds border accents for WooCommerce styles.
	 *
	 * @since 4.1
	 */
	public function accent_borders( $borders ) {
		return array_merge( array(
			'.woocommerce div.product .woocommerce-tabs ul.tabs li.active a' => array( 'bottom' ),
		), $borders );
	}

	/**
	 * Adds backgrounds accents for WooCommerce styles.
	 *
	 * @since 4.1
	 */
	public function accent_backgrounds( $backgrounds ) {
		return array_merge( array(
			'.woocommerce-MyAccount-navigation li.is-active a',
			'.woocommerce .widget_price_filter .ui-slider .ui-slider-range',
			'.woocommerce .widget_price_filter .ui-slider .ui-slider-handle',
			'.wcmenucart-details.count.t-bubble',
			'.select2-container--default .select2-results__option--highlighted[aria-selected],.select2-container--default .select2-results__option--highlighted[data-selected]',
		), $backgrounds );
	}

	/**
	 * Adds border color elements for WooCommerce styles.
	 *
	 * @since 4.1
	 */
	public function border_color_elements( $elements ) {
		return array_merge( array(

			// Product
			'.product_meta',
			'.woocommerce div.product .woocommerce-tabs ul.tabs',

			// Account
			'#customer_login form.login, #customer_login form.register, p.myaccount_user',

			// Widgets
			'.woocommerce ul.product_list_widget li:first-child',
			'.woocommerce .widget_shopping_cart .cart_list li:first-child',
			'.woocommerce.widget_shopping_cart .cart_list li:first-child',
			'.woocommerce ul.product_list_widget li',
			'.woocommerce .widget_shopping_cart .cart_list li',
			'.woocommerce.widget_shopping_cart .cart_list li',

			// Cart dropdown
			'#current-shop-items-dropdown p.total',

			// Checkout
			'.woocommerce form.login',
			'.woocommerce form.register',
			'.woocommerce-checkout #payment',
			'#add_payment_method #payment ul.payment_methods',
			'.woocommerce-cart #payment ul.payment_methods',
			'.woocommerce-checkout #payment ul.payment_methods',

		), $elements );
	}

}
Accent_Colors::instance();