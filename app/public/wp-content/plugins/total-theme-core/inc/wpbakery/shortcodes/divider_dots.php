<?php
/**
 * Divider Dots Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Divider_Dots_Shortcode' ) ) {

	class VCEX_Divider_Dots_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_divider_dots';

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

			if ( 'vc_edit_form' === vc_post_param( 'action' ) ) {
				add_filter( 'vc_edit_form_fields_attributes_' . $this->shortcode, array( $this, 'edit_form_fields' ) );
			}

			add_filter( 'shortcode_atts_' . $this->shortcode, array( $this, 'parse_attributes' ), 99 );

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Divider Dots', 'total-theme-core' ),
				'description' => esc_html__( 'Dot Separator', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-dots vcex-icon ticon ticon-ellipsis-h',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
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
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Count', 'total-theme-core' ),
						'param_name' => 'count',
						'value' => '3',
						'admin_label' => true,
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Spacing', 'total-theme-core' ),
						'param_name' => 'spacing',
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Size', 'total-theme-core' ),
						'param_name' => 'size',
						'description' => esc_html__( 'Default', 'total-theme-core' ) . ': 5px',
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'margin',
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
					// Hidden Removed attributes
					array( 'type' => 'hidden', 'param_name' => 'margin_top' ),
					array( 'type' => 'hidden', 'param_name' => 'margin_bottom' ),
				),
			);
		}

		/**
		 * Edit form fields.
		 */
		public function edit_form_fields( $atts ) {

			// Parse old margin settings
			if ( empty( $atts['margin'] ) && ( $atts['margin_top'] || $atts['margin_bottom'] ) ) {
				$atts['margin'] = vcex_combine_trbl_fields( $atts['margin_top'], '', $atts['margin_bottom'], '' );
				unset( $atts['margin_top'] );
				unset( $atts['margin_bottom'] );
			}

			// Return $atts
			return $atts;

		}

		/**
		 * Parse attributes on front-end.
		 */
		public function parse_attributes( $atts ) {

			// Parse old margin settings
			if ( empty( $atts['margin'] ) && ( $atts['margin_top'] || $atts['margin_bottom'] ) ) {
				$atts['margin'] = vcex_combine_trbl_fields( $atts['margin_top'], '', $atts['margin_bottom'], '' );
			}

			// Return attributes
			return $atts;

		}

	}
}
new VCEX_Divider_Dots_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_divider_dots' ) ) {
	class WPBakeryShortCode_vcex_divider_dots extends WPBakeryShortCode {}
}
