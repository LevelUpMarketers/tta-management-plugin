<?php
/**
 * Icon Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Icon_Shortcode' ) ) {

	class VCEX_Icon_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_icon';

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
				add_filter( 'vc_edit_form_fields_attributes_vcex_icon', array( $this, 'edit_form_fields' ) );
			}

		}

		/**
		 * Parse shortcode attributes and set correct values.
		 */
		public function edit_form_fields( $atts ) {

			// Convert textfield link to vc_link
			if ( ! empty( $atts['link_url'] ) && false === strpos( $atts['link_url'], 'url:' ) ) {
				$url              = 'url:'. rawurlencode( $atts['link_url'] ) .'|';
				$link_title       = isset( $atts['link_title'] ) ? 'title:' . rawurlencode( $atts['link_title'] ) .'|' : '|';
				$link_target      = ( isset( $atts['link_target'] ) && 'blank' == $atts['link_target'] ) ? 'target:_blank' : '';
				$atts['link_url'] = $url . $link_title . $link_target;
			}

			// Update link target
			if ( isset( $atts['link_target'] ) && 'local' == $atts['link_target'] ) {
				$atts['link_local_scroll'] = 'true';
			}

			// Parse icon
			$atts = vcex_parse_icon_param( $atts );

			// Return $atts
			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Font Icon', 'total-theme-core' ),
				'description' => esc_html__( 'Font Icon from various libraries', 'total-theme-core' ),
				'base' => 'vcex_icon',
				'icon' => 'vcex-font-icon vcex-icon ticon ticon-bolt',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
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
							esc_html__( 'Mono Social', 'total-theme-core' ) => 'monosocial',
							esc_html__( 'Pixel (legacy)', 'total-theme-core' ) => 'pixelicons',
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
						'admin_label' => true,
						'value' => 'fa fa-info-circle',
						'settings' => array( 'emptyIcon' => true, 'type' => 'fontawesome', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_openiconic',
						'std' => '',
						'settings' => array( 'emptyIcon' => true, 'type' => 'openiconic', 'iconsPerPage' => 100, ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_typicons',
						'std' => '',
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100, ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_entypo',
						'std' => '',
						'settings' => array( 'emptyIcon' => true, 'type' => 'entypo', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_linecons',
						'std' => '',
						'settings' => array( 'emptyIcon' => true, 'type' => 'linecons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_material',
						'std' => '',
						'settings' => array( 'emptyIcon' => true, 'type' => 'material', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'material' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_pixelicons',
						'std' => '',
						'settings' => array( 'emptyIcon' => true, 'type' => 'pixelicons', 'source' => vcex_pixel_icons(), 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_monosocial',
						'settings' => array( 'emptyIcon' => true, 'type' => 'monosocial', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'monosocial' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Font Alternative Classes', 'total-theme-core' ),
						'param_name' => 'icon_alternative_classes',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Icon Alignment', 'total-theme-core' ),
						'param_name' => 'align',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'float', 'is_empty' => true ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Float', 'total-theme-core' ),
						'param_name' => 'float',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'None', 'total-theme-core' ) => '',
							esc_html__( 'Left', 'total-theme-core' ) => 'left',
							esc_html__( 'Center', 'total-theme-core' ) => 'center',
							esc_html__( 'Right', 'total-theme-core') => 'right',
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Size', 'total-theme-core' ),
						'param_name' => 'size',
						'std' => 'normal',
						'value' => array(
							esc_html__( 'Inherit', 'total-theme-core' ) => 'inherit',
							esc_html__( 'Tiny', 'total-theme-core' ) => 'tiny',
							esc_html__( 'Small', 'total-theme-core') => 'small',
							esc_html__( 'Normal', 'total-theme-core' ) => 'normal',
							esc_html__( 'Large', 'total-theme-core' ) => 'large',
							esc_html__( 'Extra Large', 'total-theme-core' ) => 'xlarge',
						),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Size', 'total-theme-core' ),
						'param_name' => 'custom_size',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					// General
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
						'param_name' => 'unique_id',
						'description' => sprintf( esc_html__( 'Enter element ID (Note: make sure it is unique and valid according to %sw3c specification%s).', 'total-theme-core' ), '<a href="https://www.w3schools.com/tags/att_global_id.asp" target="_blank" rel="noopener noreferrer">', '</a>' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
					),
					// Design
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'width',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Height', 'total-theme-core' ),
						'param_name' => 'height',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Use Accent for Color', 'total-theme-core' ),
						'param_name' => 'color_accent',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'color_hover',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Use Accent for Background', 'total-theme-core' ),
						'param_name' => 'background_accent',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'background',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'background_hover',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border', 'total-theme-core' ),
						'param_name' => 'border',
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					// Link
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'Link', 'total-theme-core' ),
						'param_name' => 'link_url',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Local Scroll', 'total-theme-core' ),
						'param_name' => 'link_local_scroll',
						'std' => 'false',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Icon_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_icon' ) ) {
	class WPBakeryShortCode_vcex_icon extends WPBakeryShortCode {}
}
