<?php
/**
 * Spacing Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Spacing_Shortcode' ) ) {

	class VCEX_Spacing_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_spacing';

		/**
		 * Main constructor.
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, array( $this, 'output' ) );

			if ( function_exists( 'vc_lean_map' ) ) {
				add_action( 'vc_after_mapping', array( $this, 'vc_after_mapping' ) );
			}

		}

		/**
		 * Shortcode output => Get template file and display shortcode.
		 */
		public function output( $atts, $content = null ) {
			ob_start();
			include( vcex_get_shortcode_template( $this->shortcode ) );
			return ob_get_clean();
		}

		/**
		 * VC functions.
		 */
		public function vc_after_mapping() {
			vc_lean_map( $this->shortcode, array( $this, 'map' ) );
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name'        => esc_html__( 'Spacing', 'total-theme-core' ),
				'description' => esc_html__( 'Adds spacing anywhere you need it', 'total-theme-core' ),
				'base'        => $this->shortcode,
				'category'    => vcex_shortcodes_branding(),
				'icon'        => 'vcex-spacing vcex-icon ticon ticon-sort',
				'params'      => array(
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Spacing', 'total-theme-core' ),
						'param_name' => 'size',
						'value' => '30px',
						'description' => esc_html__( 'Enter a px, em, % or vh value. If you do not specify a unit it will use px.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Classes', 'total-theme-core' ),
						'param_name' => 'class',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
				)
			);
		}

	}
}
new VCEX_Spacing_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_spacing' ) ) {
	class WPBakeryShortCode_vcex_spacing extends WPBakeryShortCode {}
}
