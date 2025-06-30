<?php
/**
 * Teaser Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Teaser_Shortcode' ) ) {

	class VCEX_Teaser_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_teaser';

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
				'name' => esc_html__( 'Teaser Box', 'total-theme-core' ),
				'description' => esc_html__( 'A teaser content box', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-teaser vcex-icon ticon ticon-file-text-o',
				'params' => array(
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							esc_html__( 'Plain', 'total-theme-core' )   => 'one',
							esc_html__( 'Boxed Rounded', 'total-theme-core' ) => 'two',
							esc_html__( 'Boxed Square', 'total-theme-core' ) => 'three',
							esc_html__( 'Outline', 'total-theme-core' ) => 'four',
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total-theme-core' ),
						'param_name' => 'text_align',
						'std' => '',
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'padding',
						'dependency' => array( 'element' => 'style', 'value' => 'two' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background Color', 'total-theme-core' ),
						'param_name' => 'background',
						'dependency' => array( 'element' => 'style', 'value' => array( 'two', 'three' ) ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Border Color', 'total-theme-core' ),
						'param_name' => 'border_color',
						'dependency' => array( 'element' => 'style', 'value' => array( 'four' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
						'dependency' => array( 'element' => 'style', 'value' => array( 'two', 'three', 'four' ) ),
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
					// Heading
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Heading', 'total-theme-core' ),
						'param_name' => 'heading',
						'value' => 'Sample Heading',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'heading_color',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Tag', 'total-theme-core' ),
						'param_name' => 'heading_type',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'std' => 'h2',
						'choices' => 'html_tag',
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'heading_font_family',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'heading_weight',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'heading_transform',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'heading_size',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'heading_margin',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'heading_letter_spacing',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					// Content
					array(
						'type' => 'textarea_html',
						'holder' => 'div',
						'heading' => esc_html__( 'Content', 'total-theme-core' ),
						'param_name' => 'content',
						'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed faucibus feugiat convallis. Integer nec eros et risus condimentum tristique vel vitae arcu.',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'content_background',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'content_color',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'content_margin',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'content_padding',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'content_font_size',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'content_font_weight',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					// Media
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Image', 'total-theme-core' ),
						'param_name' => 'image',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Alt', 'total-theme-core' ),
						'param_name' => 'image_alt',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
						'dependency' => array( 'element' => 'image', 'not_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Video link', 'total-theme-core' ),
						'param_name' => 'video',
						'description' => esc_html__( 'Enter in a video URL that is compatible with WordPress\'s built-in oEmbed feature.', 'total-theme-core' ),
						'group' => esc_html__( 'Media', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Image Style', 'total-theme-core' ),
						'param_name' => 'img_style',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Auto', 'total-theme-core' ),
							'stretch' => esc_html__( 'Stretch', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Media', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Image Align', 'total-theme-core' ),
						'param_name' => 'img_align',
						'std' => '',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_crop_locations',
						'heading' => esc_html__( 'Image Crop Location', 'total-theme-core' ),
						'param_name' => 'img_crop',
						'std' => 'center-center',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Media', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'vcex_image_filters',
						'heading' => esc_html__( 'Image Filter', 'total-theme-core' ),
						'param_name' => 'img_filter',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_hovers',
						'heading' => esc_html__( 'CSS3 Image Hover', 'total-theme-core' ),
						'param_name' => 'img_hover_style',
						'group' => esc_html__( 'Media', 'total-theme-core' ),
					),
					// Link
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'URL', 'total-theme-core' ),
						'param_name' => 'url',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Local Scroll', 'total-theme-core' ),
						'param_name' => 'url_local_scroll',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
						'std' => 'false',
					),
					// CSS
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Teaser_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_teaser' ) ) {
	class WPBakeryShortCode_vcex_teaser extends WPBakeryShortCode {}
}
