<?php
/**
 * Shortcode Shortcode ;)
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Shortcode' ) ) {

	class VCEX_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_shortcode';

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
				'name' => esc_html__( 'Shortcode', 'total-theme-core' ),
				'description' => esc_html__( 'Insert custom shortcodes', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-shortcode vcex-icon ticon ticon-cog',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Shortcode', 'total-theme-core' ),
						'param_name' => 'content',
						'admin_label' => true,
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
				),
			);
		}

	}
}
new VCEX_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_shortcode' ) ) {
	class WPBakeryShortCode_vcex_shortcode extends WPBakeryShortCode {}
}
