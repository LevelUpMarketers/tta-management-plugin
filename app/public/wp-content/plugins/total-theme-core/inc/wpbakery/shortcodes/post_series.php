<?php
/**
 * Post Series Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Post_Series_Shortcode' ) ) {

	class VCEX_Post_Series_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_post_series';

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
				'name' => esc_html__( 'Post Series', 'total-theme-core' ),
				'description' => esc_html__( 'Display your post series.', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-post-series vcex-icon ticon ticon-pencil',
				'category' => vcex_shortcodes_branding(),
				'show_settings_on_create' => false,
				'params' => array(
					array(
						'type' => 'vcex_notice',
						'param_name' => 'main_notice',
						'text' => esc_html__( 'This module displays your post series as defined via the theme template parts so there aren\'t any individual settings.', 'total-theme-core' ),
					),
				)
			);
		}
	}
}
new VCEX_Post_Series_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_post_series' ) ) {
	class WPBakeryShortCode_vcex_post_series extends WPBakeryShortCode {}
}
