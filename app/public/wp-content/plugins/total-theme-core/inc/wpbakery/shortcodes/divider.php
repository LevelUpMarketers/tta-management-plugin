<?php
/**
 * Divider Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Divider_Shortcode' ) ) {

	class VCEX_Divider_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_divider';

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
			include( vcex_get_shortcode_template( $this->shortcode ) );
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

			add_filter( 'shortcode_atts_' . $this->shortcode, array( $this, 'parse_attributes' ), 99 );

		}

		/**
		 * Edit form fields.
		 */
		public function edit_form_fields( $atts ) {

			if ( empty( $atts['margin'] ) && ( $atts['margin_top'] || $atts['margin_bottom'] ) ) {
				$atts['margin'] = vcex_combine_trbl_fields( $atts['margin_top'], '', $atts['margin_bottom'], '' );
				unset( $atts['margin_top'] );
				unset( $atts['margin_bottom'] );
			}

			$atts = vcex_parse_icon_param( $atts );

			return $atts;

		}

		/**
		 * Parse divider attributes on the front-end.
		 */
		public function parse_attributes( $atts ) {

			// Parse old margin settings
			if ( empty( $atts['margin'] ) && ( $atts['margin_top'] || $atts['margin_bottom'] ) ) {
				$atts['margin'] = vcex_combine_trbl_fields( $atts['margin_top'], '', $atts['margin_bottom'], '' );
			}

			// Return attributes
			return $atts;

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Divider', 'total-theme-core' ),
				'description' => esc_html__( 'Line Separator with optional icon', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-divider vcex-icon ticon ticon-minus',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// General
					array(
						'type' => 'vcex_select_buttons',
						'admin_label' => true,
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'std' => 'solid',
						'choices' => array(
							'solid' => esc_html__( 'Solid', 'total-theme-core' ),
							'dashed' => esc_html__( 'Dashed', 'total-theme-core' ),
							'double' => esc_html__( 'Double', 'total-theme-core' ),
							'dotted-line' => esc_html__( 'Dotted', 'total-theme-core' ),
							'dotted' => esc_html__( 'Ben-Day', 'total-theme-core' ),
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Spacing', 'total-theme-core' ),
						'param_name' => 'margin_y',
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'width',
						'description' => esc_html__( 'Enter a pixel or percentage value.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
						'dependency' => array( 'element' => 'width', 'not_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Height', 'total-theme-core' ),
						'param_name' => 'height',
						'dependency' => array(
							'element' => 'style',
							'value' => array( 'solid', 'dashed', 'double', 'dotted-line' ),
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Height', 'total-theme-core' ),
						'param_name' => 'dotted_height',
						'dependency' => array(
							'element' => 'style',
							'value' => 'dotted',
						),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
						'value' => '',
						'dependency' => array(
							'element' => 'style',
							'value' => array( 'solid', 'dashed', 'double', 'dotted-line' ),
						),
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'margin',
						'dependency' => array( 'element' => 'margin_y', 'is_empty' => true),
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
							esc_html__( 'Pixel', 'total-theme-core' ) => 'pixelicons',
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
						'param_name' => 'icon_pixelicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'pixelicons', 'source' => vcex_pixel_icons() ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Icon Color', 'total-theme-core' ),
						'param_name' => 'icon_color',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Icon Background', 'total-theme-core' ),
						'param_name' => 'icon_bg',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Side Margin', 'total-theme-core' ),
						'param_name' => 'icon_spacing',
						'value' => vcex_margin_choices(),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Size', 'total-theme-core' ),
						'param_name' => 'icon_size',
						'description' => esc_html__( 'You can use em or px values, but you must define them.', 'total-theme-core' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Height', 'total-theme-core' ),
						'param_name' => 'icon_height',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Width', 'total-theme-core' ),
						'param_name' => 'icon_width',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Border Radius', 'total-theme-core' ),
						'param_name' => 'icon_border_radius',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Icon Padding', 'total-theme-core' ),
						'param_name' => 'icon_padding',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					// Hidden Removed attributes
					array( 'type' => 'hidden', 'param_name' => 'margin_top' ),
					array( 'type' => 'hidden', 'param_name' => 'margin_bottom' ),
				)
			);
		}

	}

}
new VCEX_Divider_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_divider' ) ) {
	class WPBakeryShortCode_vcex_divider extends WPBakeryShortCode {}
}
