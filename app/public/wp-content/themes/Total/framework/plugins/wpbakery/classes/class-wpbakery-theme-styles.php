<?php
/**
 * WPBakery Theme Styles
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

namespace TotalTheme\WPBakery;

defined( 'ABSPATH' ) || exit;

use WPBMap;

final class Theme_Styles {

	/**
	 * Our single Theme_Styles instance.
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
	 * Create or retrieve the instance of Theme_Styles.
	 *
	 * @return Theme_Styles
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Theme_Styles;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Initialize action hooks and filters.
	 *
	 * @since 5.0
	 */
	public function init_hooks() {
		add_action( 'vc_after_init', array( $this, 'register_styles' ) );
	}

	/**
	 * Register theme styles.
	 *
	 * @since 5.0
	 */
	public function register_styles() {

		if ( class_exists( 'WPBMap' ) && function_exists( 'vc_update_shortcode_param' ) ) {

			$style_name = esc_html__( 'Theme Style', 'total' );
			$style_id = 'total';

			$modules = array(
				'vc_tta_accordion',
				'vc_tta_tour',
				'vc_tta_tabs',
				'vc_toggle',
			);

			foreach( $modules as $module ) {

				// Get the module parameter value
				$param = WPBMap::getParam( $module, 'style' );

				if ( ! $param ) {
					continue;
				}

				// Add your custom style to the list of available options
				$param[ 'value' ][ $style_name ] = $style_id;

				// Set theme style as default style.
				if ( apply_filters( 'wpex_wpbakery_theme_styles_set_default', false ) ) {
					$param['std'] = $style_id;
				}

				// Pass your custom settings to WPBakery
				vc_update_shortcode_param( $module, $param );

				$hide_settings = array(
					'color',
					'shape',
					'no_fill',
					'no_fill_content_area',
				);

				if ( 'vc_toggle' == $module ) {
					$hide_settings[] = 'color';
					$hide_settings[] = 'size';
				}

				foreach ( $hide_settings as $setting ) {

					$get_param = WPBMap::getParam( $module, $setting );

					if ( ! $get_param ) {
						continue;
					}

					$get_param[ 'dependency' ] = array(
						'element'            => 'style',
						'value_not_equal_to' => $style_id
					);

					vc_update_shortcode_param( $module, $get_param );

				}

			}

		}

	}

}
Theme_Styles::instance();