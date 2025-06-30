<?php
/**
 * Custom Field Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Custom_Field' ) ) {

	class VCEX_Custom_Field {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_custom_field';

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
				'name' => esc_html__( 'Custom Field', 'total-theme-core' ),
				'description' => esc_html__( 'Display custom field meta value', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-custom-field vcex-icon ticon ticon-file-code-o',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field Name', 'total-theme-core' ),
						'param_name' => 'name',
						'admin_label' => true,
						'description' => esc_html__( 'Enter a custom field name to display it\'s value. For more advanced displays you can hook into the "vcex_custom_field_value_output" filter.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Fallback Value', 'total-theme-core' ),
						'param_name' => 'fallback',
						'description' => esc_html__( 'If the custom field value is empty you can add a fallback text to display on the site (optional).', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Before Text', 'total-theme-core' ),
						'param_name' => 'before',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
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
						),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
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
						'value' => '',
						'settings' => array( 'emptyIcon' => true, 'iconsPerPage' => 100, 'type' => 'fontawesome' ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_openiconic',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'openiconic',
							'iconsPerPage' => 100,
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
							'type' => 'typicons',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_entypo',
						'settings' => array(
							'emptyIcon' => true,
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
							'type' => 'linecons',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'total-theme-core' ),
						'param_name' => 'icon_material',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'material',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'material' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Icon Side Margin', 'total-theme-core' ),
						'param_name' => 'icon_side_margin',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'value' => vcex_margin_choices(),
					),
					// Typography
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'font_family',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'description' => esc_html__( 'You can enter a px or em value. Example 13px or 1em.', 'total-theme-core' ),
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
						'target' => 'font-size',
					),
				),
			);
		}

	}
}
new VCEX_Custom_Field;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_shortcode' ) ) {
	class WPBakeryShortCode_vcex_shortcode extends WPBakeryShortCode {}
}
