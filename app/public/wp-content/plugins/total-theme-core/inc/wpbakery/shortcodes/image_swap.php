<?php
/**
 * Image Swap Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Image_Swap_Shortcode' ) ) {

	class VCEX_Image_Swap_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_image_swap';

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
				'name' => esc_html__( 'Image Swap', 'total-theme-core' ),
				'description' => esc_html__( 'Double Image Hover Effect', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-image-swap vcex-icon ticon ticon-picture-o',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// Images
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Use Post Images', 'total-theme-core' ),
						'param_name' => 'dynamic_images',
						'std' => 'false',
						'description' => esc_html__( 'Enable to display the current post featured and secondary images automatically.', 'total-theme-core' ),
						'group' => esc_html__( 'Images', 'total-theme-core' ),
					),
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Primary Image', 'total-theme-core' ),
						'param_name' => 'primary_image',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
						'dependency' => array( 'element' => 'dynamic_images', 'value' => 'false' ),
					),
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Secondary Image', 'total-theme-core' ),
						'param_name' => 'secondary_image',
						'group' => esc_html__( 'Images', 'total-theme-core' ),
						'dependency' => array( 'element' => 'dynamic_images', 'value' => 'false' ),
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
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Container Width', 'total-theme-core' ),
						'param_name' => 'container_width',
						'description' => esc_html__( 'By default the images are stretched to 100% to fit the parent container. Enter a custom width (px or %) to restrict the width of your images.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
						'dependency' => array( 'element' => 'container_width', 'not_empty' => true ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Hover Swap Speed', 'total-theme-core' ),
						'param_name' => 'hover_speed',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							'75ms' => '75',
							'100ms' => '100',
							'150ms' => '150',
							'200ms' => '200',
							'300ms' => '300',
							'500ms' => '500',
							'700ms' => '700',
							'1000ms' => '1000',
						),
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
					vcex_vc_map_add_css_animation(),
					// Link
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'Link', 'total-theme-core' ),
						'param_name' => 'link',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					// Design Options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design options', 'total-theme-core' ),
					),
					// Hidden
					array(
						'type' => 'hidden',
						'param_name' => 'link_title',
					),
					array(
						'type' => 'hidden',
						'param_name' => 'link_target',
					),
				)
			);
		}

	}
}
new VCEX_Image_Swap_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_image_swap' ) ) {
	class WPBakeryShortCode_vcex_image_swap extends WPBakeryShortCode {}
}
