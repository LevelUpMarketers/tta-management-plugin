<?php
/**
 * Feature Box Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Feature_Box_Shortcode' ) ) {

	class VCEX_Feature_Box_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_feature_box';

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
				'name' => esc_html__( 'Feature Box', 'total-theme-core' ),
				'description' => esc_html__( 'A feature content box', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-feature-box vcex-icon ticon ticon-trophy',
				'params' => array(
					// Content
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'textarea_html',
						'holder' => 'div',
						'heading' => esc_html__( 'Content', 'total-theme-core' ),
						'param_name' => 'content',
						'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
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
					// Content
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Content Placement', 'total-theme-core' ),
						'param_name' => 'style',
						'std' => 'left-content-right-image',
						'choices' => array(
							'left-content-right-image' => esc_html__( 'Left', 'total-theme-core' ),
							'left-image-right-content' => esc_html__( 'Right', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Vertical Align Center', 'total-theme-core' ),
						'param_name' => 'content_vertical_align',
						'dependency' => array( 'element' => 'equal_heights', 'value' => 'false' ),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Alignment', 'total-theme-core' ),
						'std' => '',
						'param_name' => 'text_align',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'content_font_size',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'dependency' => array( 'element' => 'content', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'content_font_weight',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'dependency' => array( 'element' => 'content', 'not_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'content_background',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'dependency' => array( 'element' => 'content', 'not_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'content_color',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'dependency' => array( 'element' => 'content', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'content_padding',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'dependency' => array( 'element' => 'content', 'not_empty' => true ),
					),
					// Heading
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Heading', 'total-theme-core' ),
						'param_name' => 'heading',
						'value' => 'Sample Heading',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Tag', 'total-theme-core' ),
						'param_name' => 'heading_type',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'std' => '',
						'choices' => 'html_tag',
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'Link', 'total-theme-core' ),
						'param_name' => 'heading_url',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'heading_font_family',
						'std' => '',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'heading_color',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'heading_size',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'heading_margin',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'heading_weight',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'heading_transform',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'heading_letter_spacing',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'dependency' => array( 'element' => 'heading', 'not_empty' => true ),
					),
					// Image
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Image', 'total-theme-core' ),
						'param_name' => 'image',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Equal Heights?', 'total-theme-core' ),
						'param_name' => 'equal_heights',
						'description' => esc_html__( 'Keeps the image column the same height as your content.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'Image URL', 'total-theme-core' ),
						'param_name' => 'image_url',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Lightbox Type', 'total-theme-core' ),
						'param_name' => 'image_lightbox',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'None', 'total-theme-core' ) => '',
							esc_html__( 'Auto Detect (Image, Video or Inline)', 'total-theme-core' ) => 'auto-detect',
							esc_html__( 'Self', 'total-theme-core' ) => 'image',
							esc_html__( 'URL', 'total-theme-core' ) => 'url',
							esc_html__( 'Video', 'total-theme-core' ) => 'video_embed',
							esc_html__( 'Inline Content', 'total-theme-core' ) => 'inline',
							esc_html__( 'HTML5', 'total-theme-core' ) => 'html5',
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Lightbox Dimensions', 'total-theme-core' ),
						'param_name' => 'lightbox_dimensions',
						'description' => esc_html__( 'Enter a custom width and height for your lightbox pop-up window. Use format widthxheight. Example: 1920x1080.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'image_lightbox', 'value' => array( 'video_embed', 'url', 'html5', 'iframe' ) ),
					),
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_crop_locations',
						'heading' => esc_html__( 'Image Crop Location', 'total-theme-core' ),
						'param_name' => 'img_crop',
						'std' => 'center-center',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'description' => esc_html__( 'Enter a width in pixels.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'img_border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'equal_heights', 'value' => 'false' ),
					),
					array(
						'type' => 'vcex_image_hovers',
						'heading' => esc_html__( 'CSS3 Image Hover', 'total-theme-core' ),
						'param_name' => 'img_hover_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_filters',
						'heading' => esc_html__( 'Image Filter', 'total-theme-core' ),
						'param_name' => 'img_filter',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					// Video
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Video link', 'total-theme-core' ),
						'param_name' => 'video',
						'description' => esc_html__('Enter a URL that is compatible with WP\'s built-in oEmbed feature or a self-hosted video URL.', 'total-theme-core' ),
						'group' => esc_html__( 'Video', 'total-theme-core' ),
					),
					// Widths
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Content Width', 'total-theme-core' ),
						'param_name' => 'content_width',
						'value' => '50%',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Width', 'total-theme-core' ),
						'param_name' => 'media_width',
						'value' => '50%',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Tablet Widths', 'total-theme-core' ),
						'param_name' => 'tablet_widths',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Inherit', 'total-theme-core' ),
							'fullwidth' => esc_html__( 'Full-Width', 'total-theme-core' ),
						),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Phone Widths', 'total-theme-core' ),
						'param_name' => 'phone_widths',
						'group' => esc_html__( 'Widths', 'total-theme-core' ),
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Inherit', 'total-theme-core' ),
							'fullwidth' => esc_html__( 'Full-Width', 'total-theme-core' ),
						),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'background',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'padding',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border', 'total-theme-core' ),
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'param_name' => 'border',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
				)
			);
		}

	}

}
new VCEX_Feature_Box_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_feature_box' ) ) {
	class WPBakeryShortCode_vcex_feature_box extends WPBakeryShortCode {}
}
