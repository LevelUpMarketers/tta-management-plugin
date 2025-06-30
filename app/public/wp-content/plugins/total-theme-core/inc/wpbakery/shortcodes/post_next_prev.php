<?php
/**
 * Next & Previous Posts Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Post_Next_Prev_Shortcode' ) ) {

	class VCEX_Post_Next_Prev_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_post_next_prev';

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
		 * @since 4.3
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Next/Previous Post Links', 'total-theme-core' ),
				'description' => esc_html__( 'Display next/prev post buttons', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-breadcrumbs vcex-icon ticon ticon-arrows-h',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'dropdown',
						'std' => 'chevron',
						'heading' => esc_html__( 'Arrows Style', 'total-theme-core' ),
						'param_name' => 'icon_style',
						'value' => array(
							esc_html__( 'Chevron', 'total-theme-core' ) => 'chevron',
							esc_html__( 'Chevron Circle', 'total-theme-core' ) => 'chevron-circle',
							esc_html__( 'Angle', 'total-theme-core' ) => 'angle',
							esc_html__( 'Double Angle', 'total-theme-core' ) => 'angle-double',
							esc_html__( 'Arrow', 'total-theme-core' ) => 'arrow',
							esc_html__( 'Long Arrow', 'total-theme-core' ) => 'long-arrow',
							esc_html__( 'Caret', 'total-theme-core' ) => 'caret',
							esc_html__( 'Cirle', 'total-theme-core' ) => 'arrow-circle',
							esc_html__( 'None', 'total-theme-core' ) => '',
						),
					),
					array(
						'type' => 'vcex_select_buttons',
						'std' => 'icon',
						'heading' => esc_html__( 'Link Format', 'total-theme-core' ),
						'param_name' => 'link_format',
						'choices' => array(
							'icon' => esc_html__( 'Icon Only', 'total-theme-core' ),
							'title' => esc_html__( 'Post Name', 'total-theme-core' ),
							'custom' => esc_html__( 'Custom Text', 'total-theme-core' ),
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Arrow Side Margin', 'total' ),
						'param_name' => 'icon_margin',
						'value' => vcex_margin_choices(),
						'dependency' => array( 'element' => 'link_format', 'value' => array( 'title', 'custom' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Previous Text', 'total-theme-core' ),
						'param_name' => 'previous_link_custom_text',
						'dependency' => array( 'element' => 'link_format', 'value' => 'custom' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Next Text', 'total-theme-core' ),
						'param_name' => 'next_link_custom_text',
						'dependency' => array( 'element' => 'link_format', 'value' => 'custom' )
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Expand', 'total-theme-core' ),
						'param_name' => 'expand',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
						'dependency' => array( 'element' => 'expand', 'value' => 'false' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Button Spacing', 'total' ),
						'param_name' => 'spacing',
						'value' => vcex_margin_choices(),
						'description' => esc_html__( 'Margin applied to each button. If you want a 10px spacing between your buttons select 5px.', 'total' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Previous Link', 'total-theme-core' ),
						'param_name' => 'previous_link',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Next Link', 'total-theme-core' ),
						'param_name' => 'next_link',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Reverse Order', 'total-theme-core' ),
						'param_name' => 'reverse_order',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'In Same Term?', 'total-theme-core' ),
						'param_name' => 'in_same_term',
					),
					array(
						'type' => 'textfield',
						'std' => '',
						'heading' => esc_html__( 'Same Term Taxonomy Name', 'total-theme-core' ),
						'param_name' => 'same_term_tax',
						'description' => esc_html__( 'If you want to display posts from the same term enter the taxonomy name here. Such as category, portfolio_category, staff_category..etc.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'in_same_term', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					// Design
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Button Style', 'total-theme-core' ),
						'param_name' => 'button_style',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_color',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Min-Width', 'total-theme-core' ),
						'param_name' => 'button_min_width',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
				)
			);
		}
	}
}
new VCEX_Post_Next_Prev_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_post_next_prev' ) ) {
	class WPBakeryShortCode_vcex_post_next_prev extends WPBakeryShortCode {}
}
