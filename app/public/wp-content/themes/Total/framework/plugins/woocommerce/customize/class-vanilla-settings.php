<?php
/**
 * Vanilla WooCommerce Customizer Settings
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 */

namespace TotalTheme\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class Vanilla_Settings {

	/**
	 * Our single Vanilla_Settings instance.
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
	 * Create or retrieve the instance of Vanilla_Settings.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Vanilla_Settings;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'customize_register' , array( $this , 'customizer_settings' ) );
	}

	/**
	 * Customizer Settings.
	 */
	public function customizer_settings( $wp_customize ) {

		$choices_layouts = wpex_get_post_layouts();

		// Add Theme Section to WooCommerce tab
		$wp_customize->add_section(
			'wpex_woocommerce_vanilla',
			array(
				'title' => __( 'Theme Settings', 'total' ),
				'theme_supports' => array( 'woocommerce' ),
				'panel' => 'woocommerce',
			)
		);

		// Shop Layout
		$wp_customize->add_setting( 'woo_shop_layout' , array(
			'default'           => 'full-width',
			'transport'         => 'refresh',
			'sanitize_callback' => 'wpex_sanitize_customizer_select',
		) );

		$wp_customize->add_control( 'woo_shop_layout', array(
			'label'    => esc_html__( 'Shop Layout', 'total' ),
			'section'  => 'wpex_woocommerce_vanilla',
			'settings' => 'woo_shop_layout',
			'type'     => 'select',
			'choices'  => $choices_layouts,
		) );

		// Shop Layout
		$wp_customize->add_setting( 'woo_product_layout' , array(
			'default'           => 'full-width',
			'transport'         => 'refresh',
			'sanitize_callback' => 'wpex_sanitize_customizer_select',
		) );

		$wp_customize->add_control( 'woo_product_layout', array(
			'label'    => esc_html__( 'Single Product Layout', 'total' ),
			'section'  => 'wpex_woocommerce_vanilla',
			'settings' => 'woo_product_layout',
			'type'     => 'select',
			'choices'  => $choices_layouts,
		) );

		// Next Previous
		$wp_customize->add_setting( 'woo_next_prev' , array(
			'default'           => true,
			'transport'         => 'refresh',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control( 'woo_next_prev', array(
			'label'    => esc_html__( 'Display Next & Previous Links?', 'total' ),
			'section'  => 'wpex_woocommerce_vanilla',
			'settings' => 'woo_next_prev',
			'type'     => 'checkbox',
		) );

	}

}
Vanilla_Settings::instance();