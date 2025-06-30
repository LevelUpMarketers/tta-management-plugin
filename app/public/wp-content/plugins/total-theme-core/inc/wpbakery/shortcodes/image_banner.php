<?php
/**
 * Image Banner Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Image_Banner_Shortcode' ) ) {

	class VCEX_Image_Banner_Shortcode {

		/*
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_image_banner';

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

			if ( 'vc_edit_form' === vc_post_param( 'action' ) ) {
				add_filter( 'vc_edit_form_fields_attributes_' . $this->shortcode, array( $this, 'edit_form_fields' ) );
			}

		}

		/**
		 * Update deprecated options.
		 */
		public function edit_form_fields( $atts ) {

			if ( ! empty( $atts['border_radius'] ) ) {
				$atts['border_radius'] = vcex_sanitize_border_radius( $atts['border_radius'] );
			}

			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Image Banner', 'total-theme-core' ),
				'description' => esc_html__( 'Image Banner with overlay text and animation', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-image-banner vcex-icon ticon ticon-picture-o',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Fixed Width', 'total-theme-core' ),
						'param_name' => 'width',
						'value' => '',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Module Align', 'total-theme-core' ),
						'param_name' => 'align',
						'dependency' => array( 'element' => 'width', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Content Align', 'total-theme-core' ),
						'param_name' => 'content_align',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total-theme-core' ),
						'param_name' => 'text_align',
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Custom Inner Padding', 'total-theme-core' ),
						'param_name' => 'padding',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
						'value' => vcex_border_radius_choices(),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text Width', 'total-theme-core' ),
						'param_name' => 'content_width',
						'description' => esc_html__( 'Enter a max width to constrain the inner text. You can enter a pixel value such as 200px or a percentage such as 50%.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
						'param_name' => 'el_class',
					),
					vcex_vc_map_add_css_animation(),
					// Image
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Background Image Source', 'total-theme-core' ),
						'param_name' => 'image_source',
						'std' => 'media_library',
						'value' => array(
							esc_html__( 'Media Library', 'total-theme-core' ) => 'media_library',
							esc_html__( 'Custom Field', 'total-theme-core' ) => 'custom_field',
							esc_html__( 'Featured Image', 'total-theme-core' ) => 'featured',
						),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Background Image', 'total-theme-core' ),
						'param_name' => 'image',
						'dependency' => array( 'element' => 'image_source', 'value' => 'media_library' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field Name', 'total-theme-core' ),
						'param_name' => 'image_custom_field',
						'dependency' => array( 'element' => 'image_source', 'value' => 'custom_field' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Background Image Position', 'total-theme-core' ),
						'param_name' => 'image_position',
						'description' => esc_html__( 'Enter your custom background position. Example: "center center"', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Use Image Tag', 'total-theme-core' ),
						'param_name' => 'use_img_tag',
						'std' => 'false',
						'description' => esc_html__( 'This will make your image display as a standard image via the html img tag instead of an absolutely positioned background image which may render better responsively in certain situations. However, this also limits the content area to the size of your image so your content may not exceed the height of your image at any given screen size.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Content Align', 'total-theme-core' ),
						'param_name' => 'flex_align',
						'std' => 'center',
						'value' => array(
							esc_html__( 'Top', 'total-theme-core' ) => 'start',
							esc_html__( 'Center', 'total-theme-core' ) => 'center',
							esc_html__( 'Bottom', 'total-theme-core' ) => 'end',
						),
						'dependency' => array( 'element' => 'use_img_tag', 'value' => 'true' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
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
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
										// Overlay
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Overlay', 'total-theme-core' ),
						'param_name' => 'overlay',
						'std' => 'true',
						'group' => esc_html__( 'Overlay', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Overlay Color', 'total-theme-core' ),
						'param_name' => 'overlay_color',
						'group' => esc_html__( 'Overlay', 'total-theme-core' ),
						'description' => esc_html__( 'If you select a custom overlay color make sure to select a custom alpha transparency so that your background image is still visible.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Overlay Opacity', 'total-theme-core' ),
						'param_name' => 'overlay_opacity',
						'group' => esc_html__( 'Overlay', 'total-theme-core' ),
					),
					// Border
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Inner Border', 'total-theme-core' ),
						'param_name' => 'inner_border',
						'std' => 'false',
						'group' => esc_html__( 'Border', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'inner_border_style',
						'group' => esc_html__( 'Border', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'Solid', 'total-theme-core' ) => 'solid',
							esc_html__( 'Dashed', 'total-theme-core' ) => 'dashed',
							esc_html__( 'Dotted', 'total-theme-core' ) => 'dotted',
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'inner_border_margin',
						'group' => esc_html__( 'Border', 'total-theme-core' ),
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'inner_border_radius',
						'group' => esc_html__( 'Border', 'total-theme-core' ),
						'value' => vcex_border_radius_choices(),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'inner_border_color',
						'group' => esc_html__( 'Border', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'inner_border_width',
						'group' => esc_html__( 'Border', 'total-theme-core' ),
						'description' => esc_html__( 'Default:', 'total-theme-core' ) . '1px',
					),
					// Heading
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Heading', 'total-theme-core' ),
						'param_name' => 'heading',
						'value' => esc_html__( 'Add Your Heading', 'total-theme-core' ),
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Bottom Padding', 'total-theme-core' ),
						'param_name' => 'heading_bottom_padding',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'heading' => esc_html__( 'Tag', 'total-theme-core' ),
						'param_name' => 'heading_tag',
						'type' => 'vcex_select_buttons',
						'choices' => 'html_tag',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'heading_color',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'heading_font_family',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'heading_font_size',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'target' => 'font-size',
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'heading_font_weight',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Italic', 'total-theme-core' ),
						'param_name' => 'heading_italic',
						'std' => 'false',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'heading_line_height',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'heading_letter_spacing',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					// Caption
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Caption', 'total-theme-core' ),
						'param_name' => 'caption',
						'value' => esc_html__( 'Add your custom caption', 'total-theme-core' ),
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Bottom Padding', 'total-theme-core' ),
						'param_name' => 'caption_bottom_padding',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'caption_color',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'caption_font_family',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'caption_font_size',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'target' => 'font-size',
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'caption_font_weight',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Italic', 'total-theme-core' ),
						'param_name' => 'caption_italic',
						'std' => 'false',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'caption_line_height',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'caption_letter_spacing',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					// Link
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'URL', 'total-theme-core' ),
						'param_name' => 'link',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Local Scroll', 'total-theme-core' ),
						'param_name' => 'link_local_scroll',
						'std' => 'false',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					// Button
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Button', 'total-theme-core' ),
						'param_name' => 'button',
						'std' => 'false',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text', 'total-theme-core' ),
						'param_name' => 'button_text',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'value' => esc_html__( 'learn more', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'button_font_family',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'button_style',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'button_font_weight',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'button_letter_spacing',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'button_font_size',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'target' => 'font-size',
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Italic', 'total-theme-core' ),
						'param_name' => 'button_italic',
						'std' => 'false',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'button_custom_background',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'button_custom_hover_background',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_custom_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'button_custom_hover_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Width', 'total-theme-core' ),
						'param_name' => 'button_width',
						'description' => esc_html__( 'Please use a pixel or percentage value.', 'total-theme-core' ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'button_border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total-theme-core' ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'button_padding',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => array( 'true' ) ),
					),
					// Hover
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Text on Hover', 'total-theme-core' ),
						'param_name' => 'show_on_hover',
						'std' => 'false',
						'group' => esc_html__( 'Hover', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Hover Text Animation', 'total-theme-core' ),
						'param_name' => 'show_on_hover_anim',
						'std' => 'fade-up',
						'choices' => array(
							'fade-up' => esc_html__( 'Fade Up', 'total-theme-core' ),
							'fade' => esc_html__( 'Fade', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Hover', 'total-theme-core' ),
						'dependency' => array( 'element' => 'show_on_hover', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Hover Image Zoom', 'total-theme-core' ),
						'param_name' => 'image_zoom',
						'std' => 'false',
						'group' => esc_html__( 'Hover', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Hover Image Zoom Speed', 'total-theme-core' ),
						'param_name' => 'image_zoom_speed',
						'std' => '0.4',
						'description' => esc_html__( 'Value in seconds', 'total-theme-core' ),
						'group' => esc_html__( 'Hover', 'total-theme-core' ),
						'dependency' => array( 'element' => 'image_zoom', 'value' => array( 'true' ) ),
					),
				)
			);
		}

	}
}
new VCEX_Image_Banner_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_image_banner' ) ) {
	class WPBakeryShortCode_vcex_image_banner extends WPBakeryShortCode {}
}
