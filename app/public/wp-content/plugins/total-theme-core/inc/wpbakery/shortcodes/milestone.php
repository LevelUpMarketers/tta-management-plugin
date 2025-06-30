<?php
/**
 * Milestone Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 *
 * @todo add alignment option.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Milestone_Shortcode' ) ) {

	class VCEX_Milestone_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_milestone';

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
		 * Enqueue scripts.
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
				'countUp',
				vcex_asset_url( 'js/lib/countUp.min.js' ),
				array( 'jquery' ),
				'1.9.3',
				true
			);
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Milestone', 'total-theme-core' ),
				'description' => esc_html__( 'Animated counter', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-milestone vcex-icon ticon ticon-medium',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// General
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Module Style', 'total-theme-core' ),
						'param_name' => 'style',
						'choices' => array(
							''         => esc_html__( 'Default', 'total-theme-core' ),
							'plain'    => esc_html__( 'Plain', 'total-theme-core' ),
							'bordered' => esc_html__( 'Bordered', 'total-theme-core' ),
							'boxed'    => esc_html__( 'Boxed', 'total-theme-core' ),
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
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total-theme-core' ),
						'param_name' => 'text_align',
					),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Animated', 'total-theme-core' ),
						'param_name' => 'animated',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Start Value', 'total-theme-core' ),
						'param_name' => 'startval',
						'value' => '0',
						'description' => esc_html__( 'Enter the number which to start counting from, if the number is greater then the value set under the number tab then the counter will count down instead of up.','total-theme-core'),
						'dependency' => array( 'element' => 'animated', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Speed', 'total-theme-core' ),
						'param_name' => 'speed',
						'value' => '2500',
						'description' => esc_html__( 'The number of milliseconds it should take to finish counting.','total-theme-core'),
						'dependency' => array( 'element' => 'animated', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
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
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					// Number
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Number', 'total-theme-core' ),
						'param_name' => 'number',
						'std' => '45',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
						'description' => esc_html__( 'Enter a PHP function name if you would like to return a dynamic number based on a custom function', 'total-theme-core' )
					),
					array(
						'type' => 'textfield',
						'std' => ',',
						'heading' => esc_html__( 'Thousand Seperator', 'total-theme-core' ),
						'param_name' => 'separator',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Decimal Places', 'total-theme-core' ),
						'param_name' => 'decimals',
						'value' => '0',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'std' => '.',
						'heading' => esc_html__( 'Decimal Seperator', 'total-theme-core' ),
						'param_name' => 'decimal_separator',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Before', 'total-theme-core' ),
						'param_name' => 'before',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'After', 'total-theme-core' ),
						'param_name' => 'after',
						'default' => '%',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'number_font_family',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'number_color',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'number_size',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'number_weight',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'number_bottom_margin',
						'group' => esc_html__( 'Number', 'total-theme-core' ),
					),
					// Icons
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Enable Icon', 'total-theme-core' ),
						'param_name' => 'enable_icon',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
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
						'dependency' => array( 'element' => 'enable_icon', 'value' => 'true' ),
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
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100, ),
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
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Font Alternative Classes', 'total-theme-core' ),
						'param_name' => 'icon_alternative_classes',
						'dependency' => array( 'element' => 'enable_icon', 'value' => 'true' ),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Icon Position', 'total-theme-core' ),
						'param_name' => 'icon_position',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'std' => 'inline', // this is required for vcex_select_buttons
						'choices' => array(
							'inline' => esc_html__( 'Inline', 'total-theme-core' ),
							'top' => esc_html__( 'Top', 'total-theme-core' ),
							'left' => esc_html__( 'Left', 'total-theme-core' ),
							'right' => esc_html__( 'Right', 'total-theme-core' ),
						),
						'dependency' => array( 'element' => 'enable_icon', 'value' => 'true' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Spacing', 'total-theme-core' ),
						'param_name' => 'icon_spacing',
						'value' => vcex_margin_choices(),
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__(  'Color', 'total-theme-core' ),
						'param_name' => 'icon_color',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'enable_icon', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Size', 'total-theme-core' ),
						'param_name' => 'icon_size',
						'group' => esc_html__( 'Icon', 'total-theme-core' ),
						'dependency' => array( 'element' => 'enable_icon', 'value' => 'true' ),
						'description' => esc_html__( 'Default:', 'total-theme-core' ) . ' 54px',
					),
					// caption
					array(
						'type' => 'textfield',
						'class' => 'vcex-animated-counter-caption',
						'heading' => esc_html__( 'Caption', 'total-theme-core' ),
						'param_name' => 'caption',
						'value' => 'Awards Won',
						'admin_label' => true,
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'caption_font_family',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__(  'Color', 'total-theme-core' ),
						'param_name' => 'caption_color',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'caption_size',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'caption_font', // @todo rename setting to font_weight
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					// Link
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'URL', 'total-theme-core' ),
						'param_name' => 'url',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Target', 'total-theme-core' ),
						'param_name' => 'url_target',
						'std' => 'self',
						'choices' => array(
							'self' => esc_html__( 'Self', 'total-theme-core' ),
							'blank' => esc_html__( 'Blank', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Link', 'total-theme-core' ),
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

						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Link Container Wrap', 'total-theme-core' ),
						'param_name' => 'url_wrap',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
						'description' => esc_html__( 'Apply the link to the entire wrapper?', 'total-theme-core' ),
					),
					// CSS
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design options', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'width',
						'group' => esc_html__( 'Design options', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
						'group' => esc_html__( 'Design options', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Milestone_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_milestone' ) ) {
	class WPBakeryShortCode_vcex_milestone extends WPBakeryShortCode {}
}
