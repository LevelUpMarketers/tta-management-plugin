<?php
/**
 * Social Share Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Social_Share_Shortcode' ) ) {

	class VCEX_Social_Share_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_social_share';

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

			$social_share_items = vcex_get_social_items();

			$default_sites = array();
			$site_choices  = array();

			foreach ( $social_share_items as $k => $v ) {
				$default_sites[$k] = array(
					'site' => $k
				);
				$site_choices[$v['site']] = $k;
			}

			return array(
				'name' => esc_html__( 'Social Share', 'total-theme-core' ),
				'description' => esc_html__( 'Display post social share.', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-social-share vcex-icon ticon ticon-share-alt',
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
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'std' => 'flat',
						'choices' => array(
							'flat' => esc_html__( 'Flat', 'total-theme-core' ),
							'minimal' => esc_html__( 'Minimal', 'total-theme-core' ),
							'three-d' => esc_html__( '3D', 'total-theme-core' ),
							'rounded' => esc_html__( 'Rounded', 'total-theme-core' ),
							'custom' => esc_html__( 'Custom', 'total-theme-core' ),
						),
					),
					// Sites
					array(
						'type' => 'param_group',
						'param_name' => 'sites',
						'value' => urlencode( json_encode( $default_sites ) ),
						'params' => array(
							array(
								'type' => 'dropdown',
								'heading' => esc_html__( 'Site', 'total-theme-core' ),
								'param_name' => 'site',
								'admin_label' => true,
								'value' => $site_choices,
							),
						),
					),
				)
			);
		}
	}
}
new VCEX_Social_Share_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_social_share' ) ) {
	class WPBakeryShortCode_vcex_social_share extends WPBakeryShortCode {}
}
