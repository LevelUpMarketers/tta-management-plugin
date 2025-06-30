<?php
/**
 * List Item Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.4
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_List_item_Shortcode' ) ) {

	class VCEX_List_item_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_list_item';

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
				'name' => esc_html__( 'List Item', 'total-theme-core' ),
				'description' => esc_html__( 'Font Icon list item', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-list-item vcex-icon ticon ticon-list',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Text Source', 'total-theme-core' ),
						'param_name' => 'text_source',
						'value' => array(
							esc_html__( 'Custom Text', 'total-theme-core' ) => 'custom_text',
							esc_html__( 'Custom Field', 'total-theme-core' ) => 'custom_field',
							esc_html__( 'Callback Function', 'total-theme-core' ) => 'callback_function',
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text', 'total-theme-core' ),
						'param_name' => 'content',
						'admin_label' => true,
						'value' => esc_html__( 'This is a pretty list item', 'total-theme-core' ),
						'dependency' => array( 'element' => 'text_source', 'value' => 'custom_text' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field ID', 'total-theme-core' ),
						'param_name' => 'text_custom_field',
						'dependency' => array( 'element' => 'text_source', 'value' => 'custom_field' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Callback Function', 'total-theme-core' ),
						'param_name' => 'text_callback_function',
						'dependency' => array( 'element' => 'text_source', 'value' => 'callback_function' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Vertical Align', 'total-theme-core' ),
						'param_name' => 'flex_align',
						'std' => '',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '', // browser default | start
							esc_html__( 'Top', 'total-theme-core' )     => 'start',
							esc_html__( 'Center', 'total-theme-core' )  => 'center',
							esc_html__( 'Bottom', 'total-theme-core' )  => 'end',
						),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total-theme-core' ),
						'param_name' => 'text_align',
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
					// Typography
					array(
						'type'  => 'vcex_font_family_select',
						'heading'  => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name'  => 'font_family',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'font_weight',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'font_color',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Auto Responsive Font Size', 'total-theme-core' ),
						'param_name' => 'responsive_font_size',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Minimum Font Size', 'total-theme-core' ),
						'param_name' => 'min_font_size',
						'dependency' => array( 'element' => 'responsive_font_size', 'value' => 'true' ),
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Font Style', 'total-theme-core' ),
						'param_name' => 'font_style',
						'std' => '',
						'choices' => 'font_style',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					// Icon
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
						'value' => 'fa fa-info-circle',
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
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100, ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_entypo',
						'settings' => array( 'emptyIcon' => true, 'type' => 'entypo', 'iconsPerPage' => 100, ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_linecons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'linecons', 'iconsPerPage' => 100, ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_material',
						'settings' => array( 'emptyIcon' => true, 'type' => 'material', 'iconsPerPage' => 100, ),
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
						'type' => 'dropdown',
						'heading' => esc_html__( 'Spacing', 'total-theme-core' ),
						'param_name' => 'icon_spacing',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Spacing', 'total-theme-core' ),
						'param_name' => 'margin_right',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
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
						'heading' => esc_html__( 'Size', 'total-theme-core' ),
						'param_name' => 'icon_size',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'icon_border_radius',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
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
					// Link
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'Link', 'total-theme-core' ),
						'param_name' => 'link',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
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
new VCEX_List_item_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_list_item' ) ) {
	class WPBakeryShortCode_vcex_list_item extends WPBakeryShortCode {}
}
