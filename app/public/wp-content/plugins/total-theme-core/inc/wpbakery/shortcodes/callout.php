<?php
/**
 * Callout Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Callout_Shortcode' ) ) {

	class VCEX_Callout_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_callout';

		/**
		 * Main constructor
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, array( $this, 'output' ) );

			if ( function_exists( 'vc_lean_map' ) ) {
				add_action( 'vc_after_mapping', array( $this, 'vc_after_mapping' ) );
			}

		}

		/**
		 * Shortcode output
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
		 * Update fields on edit.
		 */
		public function edit_form_fields( $atts ) {

			$atts = vcex_parse_icon_param( $atts, 'button_icon_left' );
			$atts = vcex_parse_icon_param( $atts, 'button_icon_right' );

			return $atts;
		}

		/**
		 * Map shortcode to VC
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Callout', 'total' ),
				'description' => esc_html__( 'Call to action section with or without button', 'total' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-callout vcex-icon ticon ticon-bullhorn',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// Content
					array(
						'type' => 'textarea_html',
						'holder' => 'div',
						'class' => 'vcex-callout',
						'heading' => esc_html__( 'Content', 'total' ),
						'param_name' => 'content',
						'value' => 'Curabitur et suscipit tellus, quis dapibus nisl. Duis ultrices faucibus sapien, vel hendrerit est scelerisque vel.',
						'group' => esc_html__( 'Content', 'total' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					// General
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Style', 'total' ),
						'param_name' => 'style',
						'std' => 'boxed',
						'choices' => apply_filters( 'vcex_callout_styles', array(
							'none'     => esc_html__( 'None', 'total-theme-core' ),
							'boxed'    => esc_html__( 'Boxed', 'total-theme-core' ),
							'bordered' => esc_html__( 'Bordered', 'total-theme-core' ),
						) ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Layout', 'total' ),
						'param_name' => 'layout',
						'std' => '75-25',
						'value' => array(
							'75% | 25%'   => '75-25',
							'50% | 50%'   => '50-50',
							'100% | 100%' => '100-100',
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Breakpoint', 'total' ),
						'param_name' => 'breakpoint',
						'value' => vcex_breakpoint_choices(),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Border Radius', 'total' ),
						'param_name' => 'border_radius',
						'std' => 'semi-rounded',
						'value' => vcex_border_radius_choices(),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Shadow', 'total' ),
						'param_name' => 'shadow',
						'value' => vcex_shadow_choices(),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Border Width', 'total' ),
						'param_name' => 'border_width',
						'value' => vcex_border_width_choices(),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Border Color', 'total' ),
						'param_name' => 'border_color',
					),
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Element ID', 'total' ),
						'param_name' => 'unique_id',
						'description' => sprintf( esc_html__( 'Enter element ID (Note: make sure it is unique and valid according to %sw3c specification%s).', 'total-theme-core' ), '<a href="https://www.w3schools.com/tags/att_global_id.asp" target="_blank" rel="noopener noreferrer">', '</a>' ),
					),
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
						'param_name' => 'classes',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					// Content
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total' ),
						'param_name' => 'content_color',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total' ),
						'param_name' => 'content_font_family',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total' ),
						'param_name' => 'content_font_size',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total' ),
						'param_name' => 'content_letter_spacing',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total' ),
						'param_name' => 'content_font_weight',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					// Button
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'URL', 'total' ),
						'param_name' => 'button_url',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text', 'total' ),
						'param_name' => 'button_text',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Button Full-Width', 'total' ),
						'param_name' => 'button_full_width',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Button Align', 'total' ),
						'param_name' => 'button_align',
						'dependency' => array( 'element' => 'button_full_width', 'value' => 'false' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Button Style', 'total' ),
						'param_name' => 'button_style',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Color', 'total' ),
						'param_name' => 'button_color',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total' ),
						'param_name' => 'button_border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Link Target', 'total' ),
						'param_name' => 'button_target',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Self', 'total' ),
							'blank' => esc_html__( 'Blank', 'total' ),
							'local' => esc_html__( 'Local', 'total' ),
						),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Rel', 'total' ),
						'param_name' => 'button_rel',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'None', 'total' ),
							'nofollow' => esc_html__( 'Nofollow', 'total' ),
						),
						'group' => esc_html__( 'Button', 'total' ),
					),
					// Button styling
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total' ),
						'param_name' => 'button_custom_background',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total' ),
						'param_name' => 'button_custom_hover_background',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total' ),
						'param_name' => 'button_custom_color',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total' ),
						'param_name' => 'button_custom_hover_color',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total' ),
						'param_name' => 'button_padding',
						'group' => esc_html__( 'Button', 'total' ),
					),
					// Button Typography
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total' ),
						'param_name' => 'button_font_family',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total' ),
						'param_name' => 'button_font_size',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total' ),
						'param_name' => 'button_letter_spacing',
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total' ),
						'param_name' => 'button_font_weight',
						'group' => esc_html__( 'Button', 'total' ),
					),
					// Button Icons
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Icon library', 'total' ),
						'param_name' => 'icon_type',
						'description' => esc_html__( 'Select icon library.', 'total' ),
						'value' => array(
							esc_html__( 'Theme Icons', 'total-theme-core' ) => '',
							esc_html__( 'Font Awesome', 'total' ) => 'fontawesome',
							esc_html__( 'Open Iconic', 'total' ) => 'openiconic',
							esc_html__( 'Typicons', 'total' ) => 'typicons',
							esc_html__( 'Entypo', 'total' ) => 'entypo',
							esc_html__( 'Linecons', 'total' ) => 'linecons',
							esc_html__( 'Pixel', 'total' ) => 'pixelicons',
						),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total' ),
						'param_name' => 'button_icon_left_fontawesome',
						'settings' => array( 'emptyIcon' => true, 'iconsPerPage' => 100, 'type' => 'fontawesome' ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total' ),
						'param_name' => 'button_icon_left_openiconic',
						'settings' => array( 'emptyIcon' => true, 'type' => 'openiconic', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total' ),
						'param_name' => 'button_icon_left_typicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total' ),
						'param_name' => 'button_icon_left_entypo',
						'settings' => array( 'emptyIcon' => true, 'type' => 'entypo', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total' ),
						'param_name' => 'button_icon_left_linecons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'linecons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total' ),
						'param_name' => 'button_icon_right_fontawesome',
						'settings' => array( 'emptyIcon' => true, 'iconsPerPage' => 100, 'type' => 'fontawesome' ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total' ),
						'param_name' => 'button_icon_right_openiconic',
						'settings' => array( 'emptyIcon' => true, 'type' => 'openiconic', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total' ),
						'param_name' => 'button_icon_right_typicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total' ),
						'param_name' => 'button_icon_right_entypo',
						'settings' => array( 'emptyIcon' => true, 'type' => 'entypo', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total' ),
						'param_name' => 'button_icon_right_linecons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'linecons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Button', 'total' ),
					),
					// Design Options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design options', 'total' ),
					),
				)
			);
		}

	}
}
new VCEX_Callout_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_callout' ) ) {
	class WPBakeryShortCode_vcex_callout extends WPBakeryShortCode {}
}