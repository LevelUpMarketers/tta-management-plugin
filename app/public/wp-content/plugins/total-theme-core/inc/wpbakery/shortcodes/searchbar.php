<?php
/**
 * Searchbar Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Searchbar_Shortcode' ) ) {

	class VCEX_Searchbar_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_searchbar';

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
		 * Map shortcode to VC
		 *
		 * @since 3.5.0
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Search Bar', 'total-theme-core' ),
				'description' => esc_html__( 'Custom search form', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-searchbar vcex-icon ticon ticon-search',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Autofocus', 'total-theme-core'),
						'param_name' => 'autofocus',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Full-Width on Mobile', 'total-theme-core'),
						'param_name' => 'fullwidth_mobile',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
						'param_name' => 'unique_id',
						'description' => sprintf( esc_html__( 'Enter element ID (Note: make sure it is unique and valid according to %sw3c specification%s).', 'total-theme-core' ), '<a href="https://www.w3schools.com/tags/att_global_id.asp" target="_blank" rel="noopener noreferrer">', '</a>' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
						'param_name' => 'classes',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					// Query
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Advanced Search', 'total-theme-core' ),
						'param_name' => 'advanced_query',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'description' => esc_html__( 'Example: ', 'total-theme-core' ) . 'post_type=portfolio&taxonomy=portfolio_category&term=advertising',
					),
					// Widths
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Wrap Width', 'total-theme-core' ),
						'param_name' => 'wrap_width',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Wrap Align', 'total-theme-core' ),
						'param_name' => 'wrap_float',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
						'dependency' => array( 'element' => 'wrap_width', 'not_empty' => true ),
						'value' => array(
							esc_html__( 'Left', 'total-theme-core' )   => '',
							esc_html__( 'Center', 'total-theme-core' ) => 'center',
							esc_html__( 'Right', 'total-theme-core' )  => 'right',
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Input Width', 'total-theme-core' ),
						'param_name' => 'input_width',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
						'description' => '70%',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Button Width', 'total-theme-core' ),
						'param_name' => 'button_width',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
						'description' => '28%',
					),

					// Input
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Placeholder', 'total-theme-core' ),
						'param_name' => 'placeholder',
						'group' => esc_html__( 'Input', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'input_color',
						'group' => esc_html__( 'Input', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'input_font_size',
						'group' => esc_html__( 'Input', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'input_letter_spacing',
						'group' => esc_html__( 'Input', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'input_text_transform',
						'group' => esc_html__( 'Input', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'input_font_weight',
						'group' => esc_html__( 'Input', 'total-theme-core' ),
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'Design', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Input', 'total-theme-core' ),
					),
					// Submit
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Button Text', 'total-theme-core' ),
						'param_name' => 'button_text',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'button_text_transform',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'button_font_weight',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'button_font_size',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'button_letter_spacing',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'button_border_radius',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'button_bg',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'button_bg_hover',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_color',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'button_color_hover',
						'group' => esc_html__( 'Submit', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Searchbar_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_searchbar' ) ) {
	class WPBakeryShortCode_vcex_searchbar extends WPBakeryShortCode {}
}
