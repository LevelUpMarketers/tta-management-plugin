<?php
/**
 * Pricing Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Pricing_Shortcode' ) ) {

	class VCEX_Pricing_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_pricing';

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
				add_filter( 'vc_edit_form_fields_attributes_vcex_pricing', array( $this, 'edit_form_fields' ) );
			}

		}

		/**
		 * Alter module fields on edit.
		 */
		public function edit_form_fields( $atts ) {

			if ( ! empty( $atts['button_url'] )
				&& false === strpos( $atts['button_url'], 'url:' )
				&& '|' != $atts['button_url']
				&& '||' != $atts['button_url']
				&& '|||' != $atts['button_url']
			) {
				$url = 'url:' . rawurlencode( $atts['button_url'] ) . '|';
				$atts['button_url'] = $url;
			}

			$atts = vcex_parse_icon_param( $atts, 'button_icon_left' );
			$atts = vcex_parse_icon_param( $atts, 'button_icon_right' );

			return $atts;

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Pricing Table', 'total-theme-core' ),
				'description' => esc_html__( 'Insert a pricing column', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-pricing vcex-icon ticon ticon-usd',
				'params' => array(
					// General
					array(
						'type' => 'textarea_html',
						'heading' => esc_html__( 'Features List', 'total-theme-core' ),
						'param_name' => 'content',
						'value' => '<ul>
							<li>30GB Storage</li>
							<li>512MB Ram</li>
							<li>10 databases</li>
							<li>1,000 Emails</li>
							<li>25GB Bandwidth</li>
						</ul>',
						'description' => esc_html__( 'Enter your pricing content. You can use a UL list as shown by default but anything would really work!', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							esc_html__( 'Alt 1', 'total-theme-core' ) => 'alt-1',
							esc_html__( 'Alt 2', 'total-theme-core' ) => 'alt-2',
							esc_html__( 'Alt 3', 'total-theme-core' ) => 'alt-3',
						),
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
						'heading' => esc_html__( 'Shadow', 'total' ),
						'param_name' => 'shadow',
						'value' => vcex_shadow_choices(),
					),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Advanced Styling Options', 'total-theme-core' ),
						'param_name' => 'advanced_settings',
						'std' => 'true',
						'description' => esc_html__( 'Important: If you disable this option and save your module it will reset your styling options.', 'total-theme-core' ),
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
						'param_name' => 'el_class',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					// Plan
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Featured', 'total-theme-core'),
						'param_name' => 'featured',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'std' => 'no',
						'vcex' => array( 'on'  => 'yes', 'off' => 'no' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Plan', 'total-theme-core' ),
						'param_name' => 'plan',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'std' => esc_html__( 'Basic', 'total-theme-core' ),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'plan_weight',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'plan_font_family',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'plan_text_transform',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'plan_size',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'plan_letter_spacing',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'description' => esc_html__( 'Please enter a px value.', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'plan_background',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'plan_color',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'plan_padding',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'plan_margin',
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border', 'total-theme-core' ),
						'param_name' => 'plan_border',
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'group' => esc_html__( 'Plan', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					// Cost
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Cost', 'total-theme-core' ),
						'param_name' => 'cost',
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
						'std' => '$20',
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'cost_font_family',
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'cost_weight',
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'cost_size',
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'cost_background',
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'cost_color',
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'cost_padding',
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border', 'total-theme-core' ),
						'param_name' => 'cost_border',
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'group' => esc_html__( 'Cost', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					// Per
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Per', 'total-theme-core' ),
						'param_name' => 'per',
						'group' => esc_html__( 'Per', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Display', 'total-theme-core' ),
						'param_name' => 'per_display',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							esc_html__( 'Inline', 'total-theme-core' ) => 'inline',
							esc_html__( 'Block', 'total-theme-core' ) => 'block',
							esc_html__( 'Inline-Block', 'total-theme-core' ) => 'inline-block',
						),
						'group' => esc_html__( 'Per', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'per_font_family',
						'group' => esc_html__( 'Per', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'per_weight',
						'group' => esc_html__( 'Per', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'per_transform',
						'group' => esc_html__( 'Per', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'per_size',
						'group' => esc_html__( 'Per', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'per_color',
						'group' => esc_html__( 'Per', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					// Features
					array(
						'type' => 'vcex_notice',
						'param_name' => 'main_notice',
						'text' => esc_html__( 'Visit the "General" tab to edit the features list.', 'total-theme-core' ),
						'group' => esc_html__( 'Features', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'font_family',
						'group' => esc_html__( 'Features', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Features', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'line_height',
						'group' => esc_html__( 'Features', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'font_color',
						'group' => esc_html__( 'Features', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'features_bg',
						'group' => esc_html__( 'Features', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'features_padding',
						'group' => esc_html__( 'Features', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border', 'total-theme-core' ),
						'param_name' => 'features_border',
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'group' => esc_html__( 'Features', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					// Button
					array(
						'type' => 'textarea_raw_html',
						'heading' => esc_html__( 'Custom Button HTML', 'total-theme-core' ),
						'param_name' => 'custom_button',
						'description' => esc_html__( 'Enter your custom button HTML, such as your paypal button code.', 'total-theme-core' ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
					),
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'URL', 'total-theme-core' ),
						'param_name' => 'button_url',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Local Scroll?', 'total-theme-core' ),
						'param_name' => 'button_local_scroll',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text', 'total-theme-core' ),
						'param_name' => 'button_text',
						'value' => esc_html__( 'Text', 'total-theme-core' ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'button_style',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_style_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'button_font_family',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'button_weight',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'button_transform',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'button_size',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'button_bg_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
							'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'button_hover_bg_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
							'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
							'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'button_hover_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'button_border_radius',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'button_letter_spacing',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'button_padding',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Area Background', 'total-theme-core' ),
						'param_name' => 'button_wrap_bg',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Area Padding', 'total-theme-core' ),
						'param_name' => 'button_wrap_padding',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Area Border', 'total-theme-core' ),
						'param_name' => 'button_wrap_border',
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
					//Icons
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
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_button', 'is_empty' => true ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left_fontawesome',
						'settings' => array(
							'emptyIcon' => true,
							'iconsPerPage' => 100,
							'type' => 'fontawesome',
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left_openiconic',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'openiconic',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left_typicons',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'typicons',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left_entypo',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'entypo',
							'iconsPerPage' => 300,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left_linecons',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'linecons',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Left', 'total-theme-core' ),
						'param_name' => 'button_icon_left_pixelicons',
						'settings' => array(
							'emptyIcon' => false,
							'type' => 'pixelicons',
							'source' => vcex_pixel_icons(),
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right_fontawesome',
						'settings' => array( 'emptyIcon' => true, 'iconsPerPage' => 100, 'type' => 'fontawesome' ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome', ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right_openiconic',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'openiconic',
							'iconsPerPage' => 100,
						),
						'dependency' => array(
							'element' => 'icon_type',
							'value' => 'openiconic',
						),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right_typicons',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'typicons',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right_entypo',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'entypo',
							'iconsPerPage' => 300,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right_linecons',
						'settings' => array(
							'emptyIcon' => true,
							'type' => 'linecons',
							'iconsPerPage' => 100,
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon Right', 'total-theme-core' ),
						'param_name' => 'button_icon_right_pixelicons',
						'settings' => array(
							'emptyIcon' => false,
							'type' => 'pixelicons',
							'source' => vcex_pixel_icons(),
						),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Left Icon: Hover Transform x', 'total-theme-core' ),
						'param_name' => 'button_icon_left_transform',
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Right Icon: Hover Transform x', 'total-theme-core' ),
						'param_name' => 'button_icon_right_transform',
						'group' => esc_html__( 'Button Icons', 'total-theme-core' ),
					),
					// CSS
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
						'dependency' => array( 'element' => 'advanced_settings', 'value' => 'true' ),
					),
				)
			);
		}

	}
}
new VCEX_Pricing_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_pricing' ) ) {
	class WPBakeryShortCode_vcex_pricing extends WPBakeryShortCode {}
}
