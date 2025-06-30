<?php
/**
 * Divider Multi-Color Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Multi_Color_Divider_Shortcode' ) ) {

	class VCEX_Multi_Color_Divider_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_divider_multicolor';

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
				'name' => esc_html__( 'Divider - Multicolor', 'total-theme-core' ),
				'description' => esc_html__( 'A multicolor divider.', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-divider-multicolor vcex-icon ticon ticon-ellipsis-h',
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'width',
						'value' => '100%',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Margin Bottom', 'total-theme-core' ) . ' ' . esc_html__( '(legacy option)', 'total-theme-core' ),
						'param_name' => 'margin_bottom',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Height', 'total-theme-core' ),
						'param_name' => 'height',
						'value' => '8px',
					),
					array(
						'type' => 'param_group',
						'param_name' => 'colors',
						'value' => urlencode( json_encode( array(
							array(
								'value' => '#301961',
							),
							array(
								'value' => '#452586',
							),
							array(
								'value' => '#301961',
							),
							array(
								'value' => '#5f3aae',
							),
							array(
								'value' => '#01c1a8',
							),
							array(
								'value' => '#11e2c5',
							),
							array(
								'value' => '#6ffceb',
							),
							array(
								'value' => '#b0fbff',
							),
						) ) ),
						'params' => array(
							array(
								'type' => 'colorpicker',
								'heading' => esc_html__( 'Color', 'total-theme-core' ),
								'param_name' => 'value',
								'admin_label' => true,
							),
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
						'param_name' => 'el_class',
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
new VCEX_Multi_Color_Divider_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_divider_multicolor' ) ) {
	class WPBakeryShortCode_vcex_divider_multicolor extends WPBakeryShortCode {}
}
