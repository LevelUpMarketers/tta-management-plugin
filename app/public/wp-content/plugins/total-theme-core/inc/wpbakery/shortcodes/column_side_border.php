<?php
/**
 * Column Side Border Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Column_Side_Border_Shortcode' ) ) {

	class VCEX_Column_Side_Border_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_column_side_border';

		/**
		 * Main constructor.
		 */
		public function __construct() {

			if ( ! function_exists( 'vc_lean_map' ) ) {
				return; // this module is only needed for WPBakery
			}

			add_shortcode( $this->shortcode, array( $this, 'output' ) );
			add_action( 'vc_after_mapping', array( $this, 'vc_after_mapping' ) );

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
				'name'        => esc_html__( 'Column Side Border', 'total-theme-core' ),
				'description' => esc_html__( 'Responsive column side border.', 'total-theme-core' ),
				'base'        => $this->shortcode,
				'category'    => vcex_shortcodes_branding(),
				'icon'        => 'vcex-column-separator vcex-icon ticon ticon-arrows-v',
				'params'      => array(
					array(
						'type' => 'vcex_notice',
						'param_name' => 'editor_notice',
						'text' => esc_html__( 'Due to how the page builder works this module will display a placeholder in the front-end editor you will have to save and preview your live site to view the final result.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Position', 'total-theme-core' ),
						'param_name' => 'position',
						'std' => 'right',
						'choices' => array(
							'left' => esc_html__( 'Left', 'total-theme-core' ),
							'right' => esc_html__( 'Right', 'total-theme-core' ) ,
						),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background Color', 'total-theme-core' ),
						'param_name' => 'background_color',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Height', 'total-theme-core' ),
						'param_name' => 'height',
						'description' => esc_html__( 'Enter a custom px or % value. Default: 100%', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Width', 'total-theme-core' ),
						'param_name' => 'width',
						'description' => esc_html__( 'Enter a custom px value. Default: 1px', 'total-theme-core' ),
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
new VCEX_Column_Side_Border_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_column_side_border' ) ) {
	class WPBakeryShortCode_vcex_column_side_border extends WPBakeryShortCode {}
}
