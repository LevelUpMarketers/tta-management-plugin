<?php
/**
 * Image Before/After Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Image_Before_After_Shortcode' ) ) {

	class VCEX_Image_Before_After_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_image_ba';

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
			include( vcex_get_shortcode_template( 'vcex_image_before_after' ) );
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
		 * Register scripts.
		 */
		public function enqueue_scripts() {
			wp_enqueue_script(
				'jquery-move',
				vcex_asset_url( 'js/lib/jquery.event.move.min.js' ),
				array( 'jquery' ),
				'2.0',
				true
			);
			wp_enqueue_script(
				'twentytwenty',
				vcex_asset_url( 'js/lib/jquery.twentytwenty.min.js' ),
				array( 'jquery', 'jquery-move' ),
				'1.0',
				true
			);
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Image Before/After', 'total-theme-core' ),
				'description' => esc_html__( 'Visual difference between two images', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-image-ba vcex-icon ticon ticon-picture-o',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// Images
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Before', 'total-theme-core' ),
						'param_name' => 'before_img',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
					),
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'After', 'total-theme-core' ),
						'param_name' => 'after_img',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Module Width', 'total-theme-core' ),
						'param_name' => 'width',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
						'description' => esc_html__( 'Enter a width to constrain this module.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Module Align', 'total-theme-core' ),
						'param_name' => 'align',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
						'dependency' => array( 'element' => 'width', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_crop_locations',
						'heading' => esc_html__( 'Image Crop Location', 'total-theme-core' ),
						'param_name' => 'img_crop',
						'std' => 'center-center',
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
						'group' => esc_html__( 'Images', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Images', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
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
						'type' => 'vcex_number',
						'heading' => esc_html__( 'Default Offset Percentage', 'total-theme-core' ),
						'std' => '0.5',
						'param_name' => 'default_offset_pct',
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.1,
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Orientation', 'total-theme-core' ),
						'param_name' => 'orientation',
						'std' => 'horizontal',
						'choices' => array(
							'horizontal' => esc_html__( 'Horizontal', 'total-theme-core' ),
							'vertical' => esc_html__( 'Vertical', 'total-theme-core' ),
						),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Overlay', 'total-theme-core' ),
						'std' => 'true',
						'param_name' => 'overlay',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Before Label', 'total-theme-core' ),
						'param_name' => 'before_label',
						'dependency' => array( 'element' => 'overlay', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'After Label', 'total-theme-core' ),
						'param_name' => 'after_label',
						'dependency' => array( 'element' => 'overlay', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
						'param_name' => 'el_class',
					),
					vcex_vc_map_add_css_animation(),
					// Design Options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design options', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Image_Before_After_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_image_ba' ) ) {
	class WPBakeryShortCode_vcex_image_ba extends WPBakeryShortCode {}
}
