<?php
/**
 * WPBakery Tabs Configuration
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 *
 * @todo rename to WPBakery_Old_Tabs_Config and add namespace
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_VC_Tabs_Config' ) ) {

	class VCEX_VC_Tabs_Config {

		/**
		 * Main constructor
		 *
		 * @since 2.0.0
		 */
		public function __construct() {
			add_filter( 'wpex_vc_add_params', array( 'VCEX_VC_Tabs_Config', 'add_params' ) );
			add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, array( 'VCEX_VC_Tabs_Config', 'shortcode_classes' ), 99, 3 );
		}

		/**
		 * Add custom params
		 *
		 * @since 4.0
		 */
		public static function add_params( $params ) {

			$styles = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Style', 'total' ),
				'param_name' => 'style',
				'value' => array(
					__( 'Default', 'total' ) => 'default',
					__( 'Alternative #1', 'total' ) => 'alternative-one',
					__( 'Alternative #2', 'total' ) => 'alternative-two',
				),
				'weight' => 9999,
			);

			$params['vc_tabs'] = array(
				$styles
			);

			$params['vc_tour'] = array(
				$styles
			);

			return $params;
		}

		/**
		 * Add custom params
		 *
		 *
		 * @since 4.0
		 */
		public static function shortcode_classes( $class_string, $tag, $atts ) {

			if ( ( 'vc_tabs' == $tag || 'vc_tour' == $tag ) && ! empty( $atts['style'] ) ) {
				$class_string .= ' tab-style-' . sanitize_html_class( $atts['style'] );
			}

			return $class_string;

		}

	}

}
new VCEX_VC_Tabs_Config();