<?php
/**
 * Icon Box Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.3
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Icon_Box_Shortcode' ) ) {

	class VCEX_Icon_Box_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_icon_box';

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
				add_filter( 'vc_edit_form_fields_attributes_' . $this->shortcode, array( $this, 'edit_fields' ), 10 );
			}

		}

		/**
		 * Edit form fields.
		 */
		public function edit_fields( $atts ) {
			$atts = vcex_parse_icon_param( $atts );
			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Icon Box', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-icon-box vcex-icon ticon ticon-star',
				'description' => esc_html__( 'Content box with icon', 'total-theme-core' ),
				'params' => array(
					// Content
					array(
						'type' => 'textarea_html',
						'holder' => 'div',
						'heading' => esc_html__( 'Content', 'total-theme-core' ),
						'param_name' => 'content',
						'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'target' => 'font-size',
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'param_name' => 'font_weight',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'font_color',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'value' => vcex_icon_box_styles(),
						'std' => 'one',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Icon Spacing', 'total-theme-core' ),
						'param_name' => 'icon_spacing',
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Vertical Align Center', 'total-theme-core' ),
						'param_name' => 'align_center',
						'dependency' => array( 'element' => 'style', 'value' => array( 'one', 'seven' ) ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Alignment', 'total-theme-core' ),
						'param_name' => 'alignment',
						'std' => '',
						'dependency' => array( 'element' => 'style', 'value' => array( 'two' ) ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Border Width', 'total' ),
						'param_name' => 'border_width',
						'value' => vcex_border_width_choices(),
						'dependency' => array( 'element' => 'style', 'value' => array( 'four' ) ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Border Color', 'total' ),
						'param_name' => 'border_color',
						'dependency' => array( 'element' => 'style', 'value' => array( 'four' ) ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'wpex_padding',
						'value' => vcex_padding_choices(),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Shadow', 'total' ),
						'param_name' => 'shadow',
						'value' => vcex_shadow_choices(),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Shadow: Hover', 'total' ),
						'param_name' => 'shadow_hover',
						'value' => vcex_shadow_choices(),
					),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
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
						'param_name' => 'classes',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
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
						'std' => 'Sample Heading',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Badge', 'total-theme-core' ),
						'param_name' => 'heading_badge',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'heading_font_family',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'heading_color',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
					),
					array(
						'heading' => esc_html__( 'HTML Tag', 'total-theme-core' ),
						'param_name' => 'heading_type',
						'type' => 'vcex_select_buttons',
						'choices' => 'html_tag',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'description' => esc_html__( 'Default', 'total-theme-core' ) . ': h2',
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
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'heading_size',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'target' => 'font-size',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'heading_letter_spacing',
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
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'heading_bottom_margin',
						'group' => esc_html__( 'Heading', 'total-theme-core' ),
						'description' => esc_html__( 'Default', 'total-theme-core' ) . ': 10px',
					),
					// Icons
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Icon library', 'total-theme-core' ),
						'param_name' => 'icon_type',
						'description' => esc_html__( 'Select icon library.', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'Theme Icons', 'total-theme-core' ) => '',
							esc_html__( 'Font Awesome', 'total-theme-core' ) => 'fontawesome',
							esc_html__( 'Open Iconic', 'total-theme-core' ) => 'openiconic',
							esc_html__( 'Typicons', 'total-theme-core' ) => 'typicons',
							esc_html__( 'Entypo', 'total-theme-core' ) => 'entypo',
							esc_html__( 'Linecons', 'total-theme-core' ) => 'linecons',
							esc_html__( 'Material', 'total-theme-core' ) => 'material',
							esc_html__( 'Pixel', 'total-theme-core' ) => 'pixelicons',
						),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon',
						'value' => 'ticon ticon-star-o',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_fontawesome',
						'value' => 'fas fa-info-circle',
						'settings' => array( 'emptyIcon' => true, 'type' => 'fontawesome', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_openiconic',
						'settings' => array( 'emptyIcon' => true, 'type' => 'openiconic', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_typicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_entypo',
						'settings' => array( 'emptyIcon' => true, 'type' => 'entypo', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_linecons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'linecons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_material',
						'settings' => array( 'emptyIcon' => true, 'type' => 'material', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'material' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_pixelicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'pixelicons', 'source' => vcex_pixel_icons() ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Alternative Classes', 'total-theme-core' ),
						'param_name' => 'icon_alternative_classes',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Size', 'total-theme-core' ),
						'param_name' => 'icon_size',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'description' => esc_html__( 'Default', 'total-theme-core' ) . ': 28px',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Use Accent for Color', 'total-theme-core' ),
						'param_name' => 'icon_color_accent',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'icon_color',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Use Accent for Background', 'total-theme-core' ),
						'param_name' => 'icon_background_accent',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'icon_background',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'icon_border_radius',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Shadow', 'total' ),
						'param_name' => 'icon_shadow',
						'value' => vcex_shadow_choices(),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'description' => esc_html__( 'For optimal display add a white background to your icon or give the icon a custom width/height.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'icon_width',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Height', 'total-theme-core' ),
						'param_name' => 'icon_height',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'icon_bottom_margin',
						'dependency' => array( 'element' => 'style', 'value' => array( 'two', 'three', 'four', 'five', 'six' ) ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'description' => esc_html__( 'Default', 'total-theme-core' ) . ': 20px',
					),
					// Image
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Icon Image Alternative', 'total-theme-core' ),
						'param_name' => 'image',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'image_bottom_margin',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'style', 'value' => array( 'two' ) ),
						'description' => esc_html__( 'Default', 'total-theme-core' ) . ': 20px',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'image_width',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'description' => esc_html__( 'If you are using the "Left Icon" style, be sure to also alter the "Container Left Padding" setting under the general tab to allow space for your custom icon size.', 'total-theme-core' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Height', 'total-theme-core' ),
						'param_name' => 'image_height',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Resize Image', 'total-theme-core' ),
						'param_name' => 'resize_image',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'description' => esc_html__( 'Enable to run the image through the resizing script, disable to simply resize via CSS.', 'total-theme-core' )
					),
					// URL
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'URL', 'total-theme-core' ),
						'param_name' => 'url',
						'group' => esc_html__( 'URL', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Target', 'total-theme-core' ),
						'param_name' => 'url_target',
						'std' => 'self',
						'choices' => array(
							'self' => esc_html__( 'Self', 'total-theme-core' ),
							'_blank' => esc_html__( 'Blank', 'total-theme-core' ),
							'local' => esc_html__( 'Local', 'total-theme-core' ),
						),
						'group' => esc_html__( 'URL', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Rel', 'total-theme-core' ),
						'param_name' => 'url_rel',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'None', 'total-theme-core' ),
							'nofollow' => esc_html__( 'Nofollow', 'total-theme-core' ),
						),
						'group' => esc_html__( 'URL', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Apply Link To Entire Icon Box?', 'total-theme-core' ),
						'param_name' => 'url_wrap',
						'std' => 'false',
						'group' => esc_html__( 'URL', 'total-theme-core' ),
						'dependency' => array( 'element' => 'style', 'value' => array( 'one', 'two', 'three', 'seven' ) ),
					),
					// Design
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'Container Design', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Container Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'width',
						'group' => esc_html__( 'Container Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'hover_background',
						'group' => esc_html__( 'Container Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'White Text On Hover', 'total-theme-core' ),
						'param_name' => 'hover_white_text',
						'group' => esc_html__( 'Container Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
						'group' => esc_html__( 'Container Design', 'total-theme-core' ),
					),
				),
			);
		}
	}
}
new VCEX_Icon_Box_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_icon_box' ) ) {
	class WPBakeryShortCode_vcex_icon_box extends WPBakeryShortCode {}
}
