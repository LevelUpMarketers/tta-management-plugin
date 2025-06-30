<?php
/**
 * Skillbar Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Skillbar_Shortcode' ) ) {

	class VCEX_Skillbar_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_skillbar';

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
		 * Shortcode scripts.
		 */
		public function enqueue_scripts() {
			wp_enqueue_script(
				'appear',
				vcex_asset_url( 'js/lib/jquery.appear.min.js' ),
				array( 'jquery' ),
				'1.0',
				true
			);
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
				'name' => esc_html__( 'Percentage Bar', 'total-theme-core' ),
				'description' => esc_html__( 'Animated percentage bar', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-skill-bar vcex-icon ticon ticon-percent',
				'params' => array(
					// General
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text', 'total-theme-core' ),
						'param_name' => 'title',
						'admin_label' => true,
						'value' => 'Web Design',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Percentage Source', 'total-theme-core' ),
						'param_name' => 'source',
						'value' => array(
							esc_html__( 'Custom Text', 'total-theme-core' ) => 'custom',
							esc_html__( 'Custom Field', 'total-theme-core' ) => 'custom_field',
							esc_html__( 'Callback Function', 'total-theme-core' ) => 'callback_function',
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field ID', 'total-theme-core' ),
						'param_name' => 'custom_field',
						'dependency' => array( 'element' => 'source', 'value' => 'custom_field' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Callback Function', 'total-theme-core' ),
						'param_name' => 'callback_function',
						'dependency' => array( 'element' => 'source', 'value' => 'callback_function' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Percentage', 'total-theme-core' ),
						'param_name' => 'percentage',
						'value' => 70,
						'dependency' => array( 'element' => 'source', 'value' => 'custom' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							esc_html__( 'Title Above', 'total-theme-core' ) => 'alt-1',
						),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Animate', 'total-theme-core' ),
						'param_name' => 'animate_percent',
						'dependency' => array( 'element' => 'show_percent', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Display Percentage', 'total-theme-core' ),
						'param_name' => 'show_percent',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Label Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Label Color', 'total-theme-core' ),
						'param_name' => 'label_color',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Percentage Font Size', 'total-theme-core' ),
						'param_name' => 'percentage_font_size',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Percentage Color', 'total-theme-core' ),
						'param_name' => 'percentage_color',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Container Background', 'total-theme-core' ),
						'param_name' => 'background',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Container Inset Shadow', 'total-theme-core' ),
						'param_name' => 'box_shadow',
						'dependency' => array( 'element' => 'style', 'is_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Container Height', 'total-theme-core' ),
						'param_name' => 'container_height',
						'description' => '(px/em)',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Container Left Padding', 'total-theme-core' ),
						'param_name' => 'container_padding_left',
						'dependency' => array( 'element' => 'style', 'is_empty' => true ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
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
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					// Icon
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Display Icon', 'total-theme-core' ),
						'param_name' => 'show_icon',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Icon library', 'total-theme-core' ),
						'param_name' => 'icon_type',
						'value' => array(
							esc_html__( 'Theme Icons', 'total-theme-core' ) => '',
							esc_html__( 'Font Awesome', 'total-theme-core' ) => 'fontawesome',
							esc_html__( 'Open Iconic', 'total-theme-core' ) => 'openiconic',
							esc_html__( 'Typicons', 'total-theme-core' ) => 'typicons',
							esc_html__( 'Entypo', 'total-theme-core' ) => 'entypo',
							esc_html__( 'Linecons', 'total-theme-core' ) => 'linecons',
							esc_html__( 'Pixel', 'total-theme-core' ) => 'pixelicons',
						),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'show_icon', 'value' => 'true' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_fontawesome',
						'settings' => array( 'emptyIcon' => true, 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_openiconic',
						'settings' => array(
							'emptyIcon' => true,
							'iconsPerPage' => 100,
							'type' => 'openiconic',
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_typicons',
						'settings' => array(
							'emptyIcon' => true,
							'iconsPerPage' => 100,
							'type' => 'typicons',
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_entypo',
						'settings' => array(
							'emptyIcon' => false,
							'type' => 'entypo',
							'iconsPerPage' => 300,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_linecons',
						'settings' => array(
							'emptyIcon' => true,
							'iconsPerPage' => 100,
							'type' => 'linecons',
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_pixelicons',
						'settings' => array(
							'emptyIcon' => true,
							'iconsPerPage' => 100,
							'type' => 'pixelicons',
							'source' => vcex_pixel_icons(),
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Side Margin', 'total-theme-core' ),
						'param_name' => 'icon_margin',
						'value' => vcex_margin_choices(),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'show_icon', 'value' => 'true' ),
					),
				)
			);
		}

	}
}
new VCEX_Skillbar_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_skillbar' ) ) {
	class WPBakeryShortCode_vcex_skillbar extends WPBakeryShortCode {}
}
