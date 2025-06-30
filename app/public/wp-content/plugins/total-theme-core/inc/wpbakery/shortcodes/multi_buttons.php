<?php
/**
 * Multi Buttons Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Multi_Buttons_Shortcode' ) ) {

	class VCEX_Multi_Buttons_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_multi_buttons';

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
				'name' => esc_html__( 'Multi-Buttons', 'total' ),
				'description' => esc_html__( 'Multiple Buttons side by side', 'total' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-multi-buttons vcex-icon ticon ticon-ellipsis-h',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// Buttons
					array(
						'type' => 'param_group',
						'param_name' => 'buttons',
						'group' => esc_html__( 'Buttons', 'total' ),
						'value' => urlencode( json_encode( array(
							array(
								'text' => esc_html__( 'Button 1', 'total' ),
								'link' => 'url:#',
							),
							array(
								'text' => esc_html__( 'Button 2', 'total' ),
								'link' => 'url:#',
							),
						) ) ),
						'params' => array(
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Text', 'total' ),
								'param_name' => 'text',
								'admin_label' => true,
							),
							array(
								'type' => 'vc_link',
								'heading' => esc_html__( 'Link', 'total' ),
								'param_name' => 'link',
							),
							vcex_vc_map_add_css_animation(),
							array(
								'type' => 'vcex_select_buttons',
								'std' => 'flat',
								'heading' => esc_html__( 'Style', 'total' ),
								'param_name' => 'style',
								'choices' => apply_filters( 'wpex_button_styles', array(
									'flat' => esc_html__( 'Flat', 'total' ),
									'outline' => esc_html__( 'Outline', 'total' ),
									'plain-text' => esc_html__( 'Plain Text', 'total' ),
								) ),
							),
							array(
								'type' => 'vcex_button_colors',
								'heading' => esc_html__( 'Prefixed Color', 'total' ),
								'param_name' => 'color',
								'description' => esc_html__( 'Custom color options can be added via a child theme.', 'total' ),
							),
							array(
								'type' => 'colorpicker',
								'heading' => esc_html__( 'Custom Color', 'total' ),
								'param_name' => 'custom_color',
							),
							array(
								'type' => 'colorpicker',
								'heading' => esc_html__( 'Custom Color: Hover', 'total' ),
								'param_name' => 'custom_color_hover',
							),
							array(
								'type' => 'vcex_ofswitch',
								'heading' => esc_html__( 'Local Scroll', 'total' ),
								'param_name' => 'local_scroll',
								'std' => 'false',
							),
							array(
								'type' => 'vcex_ofswitch',
								'std' => 'false',
								'heading' => esc_html__( 'Use Download Attribute?', 'total-theme-core' ),
								'param_name' => 'download_attribute',
							),
						),
					),
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total' ),
						'param_name' => 'align',
						'std' => 'center',
						'exclude_choices' => array( 'default' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Button Width', 'total' ),
						'param_name' => 'width',
						'description' => esc_html__( 'Number in pixels.', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total' ),
						'param_name' => 'line_height',
						'description' => esc_html__( 'Number in pixels.', 'total' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Button Padding', 'total' ),
						'param_name' => 'padding',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Spacing', 'total' ),
						'param_name' => 'spacing',
						'description' => esc_html__( 'Enter a custom spacing in pixels that will be added between the buttons.', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total' ),
						'param_name' => 'border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Width', 'total' ),
						'param_name' => 'border_width',
						'description' => esc_html__( 'Please enter a px value. This will control the border width when using the outline style button. Default is 3px.', 'total' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Full-Width on Small Screens', 'total' ),
						'param_name' => 'small_screen_full_width',
						'description' => esc_html__( 'If enabled the buttons will render at 100% width on devices under 480px.', 'total' ),
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total' ),
						'param_name' => 'visibility',
					),
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Extra class name', 'total' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total' ),
					),
					// Typography
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total' ),
						'param_name' => 'font_family',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Typography', 'total' ),
						'target' => 'font-size',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total' ),
						'param_name' => 'letter_spacing',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total' ),
						'param_name' => 'font_weight',
						'group' => esc_html__( 'Typography', 'total' ),
					),
				)
			);
		}

	}

}
new VCEX_Multi_Buttons_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_multi_buttons' ) ) {
	class WPBakeryShortCode_vcex_multi_buttons extends WPBakeryShortCode {}
}
