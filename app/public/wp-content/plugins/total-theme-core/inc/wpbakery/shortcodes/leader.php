<?php
/**
 * Leader Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Leader_Shortcode' ) ) {

	class VCEX_Leader_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_leader';

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
			do_action( 'vcex_shortcode_before', $this->shortcode, $atts );
			include( vcex_get_shortcode_template( $this->shortcode ) );
			do_action( 'vcex_shortcode_after', $this->shortcode, $atts );
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
				'name' => esc_html__( 'Leader (Menu Items)', 'total-theme-core' ),
				'description' => esc_html__( 'CSS dot or line leader (menu item)', 'total-theme-core' ),
				'base' => 'vcex_leader',
				'icon' => 'vcex-leader vcex-icon ticon ticon-long-arrow-right',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'param_group',
						'param_name' => 'leaders',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'One', 'total-theme-core' ),
								'value' => '$10',
							),
							array(
								'label' => esc_html__( 'Two', 'total-theme-core' ),
								'value' => '$20',
							),
						) ) ),
						'params' => array(
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Label', 'total-theme-core' ),
								'param_name' => 'label',
								'admin_label' => true,
							),
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Value', 'total-theme-core' ),
								'param_name' => 'value',
								'admin_label' => true,
							),
						),
					),
					// General
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'std' => 'dots',
						'choices' => array(
							'dots' => esc_html__( 'Dots', 'total-theme-core' ),
							'dashes' => esc_html__( 'Dashes', 'total-theme-core' ),
							'minimal' => esc_html__( 'Empty Space', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Responsive', 'total-theme-core' ),
						'param_name' => 'responsive',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Space Between Items', 'total-theme-core' ),
						'param_name' => 'spacing',
						'value' => vcex_margin_choices(),
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'background',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
						'target' => 'font-size',
					),
					// Label
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'label_font_family',
						'group' => esc_html__( 'Label', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'param_name' => 'label_color',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'group' => esc_html__( 'Label', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'param_name' => 'label_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'group' => esc_html__( 'Label', 'total-theme-core' ),
					),
					array(
						'heading' => esc_html__( 'Font Style', 'total-theme-core' ),
						'param_name' => 'label_font_style',
						'type' => 'vcex_select_buttons',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Normal', 'total-theme-core' ),
							'italic' => esc_html__( 'Italic', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Label', 'total-theme-core' ),
					),
					// Color
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'value_font_family',
						'group' => esc_html__( 'Value', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'param_name' => 'value_color',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'group' => esc_html__( 'Value', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'param_name' => 'value_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'group' => esc_html__( 'Value', 'total-theme-core' ),
					),
					array(
						'heading' => esc_html__( 'Font Style', 'total-theme-core' ),
						'param_name' => 'value_font_style',
						'type' => 'vcex_select_buttons',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Normal', 'total-theme-core' ),
							'italic' => esc_html__( 'Italic', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Value', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Leader_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_leader' ) ) {
	class WPBakeryShortCode_vcex_leader extends WPBakeryShortCode {}
}
