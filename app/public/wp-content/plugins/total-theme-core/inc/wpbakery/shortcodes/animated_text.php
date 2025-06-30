<?php
/**
 * Animated Text Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Animated_Text_Shortcode' ) ) {

	class VCEX_Animated_Text_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_animated_text';

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
			wp_enqueue_script(
				'typed',
				vcex_asset_url( 'js/lib/typed.min.js' ),
				array( 'jquery' ),
				'2.0.6',
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
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Animated Typed Text', 'total-theme-core' ),
				'description' => esc_html__( 'Animated text', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-animated-text vcex-icon ticon ticon-text-width',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total-theme-core' ),
						'param_name' => 'text_align',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Speed', 'total-theme-core' ),
						'param_name' => 'speed',
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Back Delay', 'total-theme-core' ),
						'param_name' => 'back_delay',
						'std' => '500',
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Back Speed', 'total-theme-core' ),
						'param_name' => 'back_speed',
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Start Delay', 'total-theme-core' ),
						'param_name' => 'start_delay',
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Loop', 'total-theme-core' ),
						'param_name' => 'loop',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Cursor', 'total-theme-core' ),
						'param_name' => 'type_cursor',
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
					// Typography
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'font_family',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'heading' => esc_html__( 'Tag', 'total-theme-core' ),
						'param_name' => 'tag',
						'type' => 'vcex_select_buttons',
						'std' => 'div',
						'choices' => 'html_tag',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'param_name' => 'font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
						'target' => 'font-size',
					),
					array(
						'heading' => esc_html__( 'Font Style', 'total-theme-core' ),
						'param_name' => 'font_style',
						'type' => 'vcex_select_buttons',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Normal', 'total-theme-core' ),
							'italic' => esc_html__( 'Italic', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					// Animated Text
					array(
						'type'  => 'textfield',
						'heading' => esc_html__( 'Fixed Width', 'total-theme-core' ),
						'param_name' => 'animated_span_width',
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
						'description' => esc_html__( 'Enter a custom width to keep the animated container fixed. Useful when adding custom background or static text after the animated text.', 'total-theme-core' ),
					),
					array(
						'type'  => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total-theme-core' ),
						'param_name' => 'animated_text_align',
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
						'dependency' => array( 'element' => 'animated_span_width', 'not_empty' => true )
					),
					array(
						'type' => 'param_group',
						'param_name' => 'strings',
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
						'value' => urlencode( json_encode( array(
							array(
								'text' => esc_html__( 'Welcome', 'total-theme-core' ),
							),
							array(
								'text' => esc_html__( 'Bienvenido', 'total-theme-core' ),
							),
							array(
								'text' => esc_html__( 'Welkom', 'total-theme-core' ),
							),
							array(
								'text' => esc_html__( 'Bienvenue', 'total-theme-core' ),
							),
						) ) ),
						'params' => array(
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Text', 'total-theme-core' ),
								'param_name' => 'text',
								'admin_label' => true,
							),
						),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'animated_font_family',
						'dependency' => array( 'element' => 'static_text', 'value' => 'true' ),
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'animated_color',
						'dependency' => array( 'element' => 'static_text', 'value' => 'true' ),
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'param_name' => 'animated_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'std' => '',
						'dependency' => array( 'element' => 'static_text', 'value' => 'true' ),
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
					),
					array(
						'heading' => esc_html__( 'Font Style', 'total-theme-core' ),
						'param_name' => 'animated_font_style',
						'type' => 'vcex_select_buttons',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Normal', 'total-theme-core' ),
							'italic' => esc_html__( 'Italic', 'total-theme-core' ),
						),
						'dependency' => array( 'element' => 'static_text', 'value' => 'true' ),
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Text Decoration', 'total-theme-core' ),
						'param_name' => 'animated_text_decoration',
						'choices' => 'text_decoration',
						'dependency' => array( 'element' => 'static_text', 'value' => 'true' ),
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS', 'total-theme-core' ),
						'param_name' => 'animated_css',
						'group' => esc_html__( 'Animated Text', 'total-theme-core' ),
					),
					// Static Text
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'static_text',
						'group' => esc_html__( 'Static Text', 'total-theme-core' ),
						'std' => 'false',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Before', 'total-theme-core' ),
						'param_name' => 'static_before',
						'group' => esc_html__( 'Static Text', 'total-theme-core' ),
						'dependency' => array( 'element' => 'static_text', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'After', 'total-theme-core' ),
						'param_name' => 'static_after',
						'group' => esc_html__( 'Static Text', 'total-theme-core' ),
						'dependency' => array( 'element' => 'static_text', 'value' => 'true' ),
					),
					// Design options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
				),
			);
		}

	}

}
new VCEX_Animated_Text_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_animated_text' ) ) {
	class WPBakeryShortCode_vcex_animated_text extends WPBakeryShortCode {}
}
