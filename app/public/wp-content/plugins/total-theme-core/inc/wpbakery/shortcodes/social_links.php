<?php
/**
 * Social Links Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Social_Links_Shortcode' ) ) {

	class VCEX_Social_Links_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_social_links';

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
				add_filter( 'vc_edit_form_fields_attributes_vcex_social_links', array( $this, 'edit_form_fields' ) );
			}

		}

		/**
		 * Parse attributes on edit.
		 */
		public function edit_form_fields( $atts ) {

			// Get array of social links to loop through
			$social_profiles = vcex_social_links_profiles();

			// Social links list required
			if ( empty( $social_profiles ) )  {
				return $atts;
			}

			// Loop through old options and move to new ones + delete old settings?
			if ( empty( $atts['social_links'] ) ) {
				$social_links = array();
				foreach ( $social_profiles  as $key => $val ) {
					if ( ! empty( $atts[$key] ) ) {
						$social_links[] = array(
							'site' => $key,
							'link' => $atts[$key],
						);
					}
					unset( $atts[$key] );
				}
				if ( $social_links ) {
					$atts['social_links'] = urlencode( json_encode( $social_links ) );
				}
			}

			// Return attributes
			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			// Get array of social links to loop through
			$social_links = vcex_social_links_profiles();
			// Social links list required
			if ( empty( $social_links ) )  {
				return;
			}
			// Create dropdown of social sites
			$social_link_select = array();
			foreach ( $social_links as $key => $val ) {
				$social_link_select[$val['label']] = $key;
			}
			// Return array
			return array(
				'name' => esc_html__( 'Social Links', 'total-theme-core' ),
				'description' => esc_html__( 'Display social links using icon fonts', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-social-links vcex-icon ticon ticon-user-plus',
				'params' => array(
					// Social Links
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Post Author Links', 'total-theme-core' ),
						'param_name' => 'author_links',
						'description' => esc_html__( 'Enable to display the social links for the current post author.', 'total-theme-core' ),
						'group' => esc_html__( 'Profiles', 'total-theme-core' ),
					),
					array(
						'type' => 'param_group',
						'param_name' => 'social_links',
						'group' => esc_html__( 'Profiles', 'total-theme-core' ),
						'value' => urlencode( json_encode( array( ) ) ),
						'dependency' => array( 'element' => 'author_links', 'value' => 'false' ),
						'params' => array(
							array(
								'type' => 'dropdown',
								'heading' => esc_html__( 'Site', 'total-theme-core' ),
								'param_name' => 'site',
								'admin_label' => true,
								'value' => $social_link_select,
							),
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Link', 'total-theme-core' ),
								'param_name' => 'link',
							),
						),
					),
					// General
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
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
						'param_name' => 'classes',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Link Target', 'total-theme-core'),
						'param_name' => 'link_target',
						'std' => 'self',
						'choices' => 'link_target',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Show Labels', 'total-theme-core' ),
						'param_name' => 'show_label',
					),
					// Style
					array(
						'type' => 'vcex_social_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core'),
						'param_name' => 'style',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Spacing', 'total-theme-core' ),
						'param_name' => 'spacing',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Expand Items', 'total-theme-core' ),
						'param_name' => 'expand',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'expand', 'value' => 'false' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Custom Icon Size', 'total-theme-core' ),
						'param_name' => 'size',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Vertical Padding', 'total-theme-core' ),
						'param_name' => 'padding_y',
						'value' => vcex_margin_choices(),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'height', 'is_empty' => true ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Horizontal Padding', 'total-theme-core' ),
						'param_name' => 'padding_x',
						'value' => vcex_margin_choices(),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'width', 'is_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'width',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'expand', 'value' => 'false' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Height', 'total-theme-core' ),
						'param_name' => 'height',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'line_height',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'height', 'not_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'style', 'value' => array( 'none', '' ) ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Hover Background', 'total-theme-core' ),
						'param_name' => 'hover_bg',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Hover Color', 'total-theme-core' ),
						'param_name' => 'hover_color',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'Custom CSS applied to each social link.', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'hidden',
						'param_name' => 'border_radius',
					),
				),
			);
		}

	}
}
new VCEX_Social_Links_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_social_links' ) ) {
	class WPBakeryShortCode_vcex_social_links extends WPBakeryShortCode {}
}
