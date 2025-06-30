<?php
/**
 * Form Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Form_Shortcode' ) ) {
	class VCEX_Form_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_form_shortcode';

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

			$params = array(
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
					'param_name' => 'bottom_margin',
					'value' => vcex_margin_choices(),
					'admin_label' => true,
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Form Shortcode', 'total' ),
					'param_name' => 'content',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Style', 'total' ),
					'param_name' => 'style',
					'std' => '',
					'value' => array_flip( vcex_get_form_styles() ),
					'description' => esc_html__( 'The theme will try and apply the necessary styles to your form (works best with Contact Form 7) but remember every contact form plugin has their own styles so additional tweaks may be required.', 'total' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Full-Width Inputs', 'total' ),
					'param_name' => 'full_width',
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Width', 'total' ),
					'param_name' => 'width',
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Font Size', 'total' ),
					'param_name' => 'font_size',
				),
				vcex_vc_map_add_css_animation(),
				// Design
				array(
					'type' => 'css_editor',
					'heading' => esc_html__( 'CSS box', 'total' ),
					'param_name' => 'css',
					'group' => esc_html__( 'Design options', 'total' ),
				),
			);

			$cf7 = vcex_select_cf7_form( array(
				'heading' => esc_html__( 'Contact Form 7 Select', 'total' ),
				'param_name' => 'cf7_id',
			) );

			if ( $cf7 ) {
				array_unshift( $params, $cf7 );
			}

			return array(
				'name' => esc_html__( 'Form Shortcode', 'total' ),
				'description' => esc_html__( 'Form shortcode with style', 'total' ),
				'base' => 'vcex_form_shortcode',
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-form-shortcode vcex-icon ticon ticon-wpforms',
				'params' => $params,
			);

		}

	}
}
new VCEX_Form_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_form_shortcode' ) ) {
	class WPBakeryShortCode_vcex_form_shortcode extends WPBakeryShortCode {}
}
