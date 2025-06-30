<?php
/**
 * Login Form Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Login_Form' ) ) {

	class VCEX_Login_Form {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_login_form';

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
				'name' => esc_html__( 'Login Form', 'total-theme-core' ),
				'description' => esc_html__( 'Adds a WordPress login form', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-login-form vcex-icon ticon ticon-unlock-alt',
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
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
						'heading' => esc_html__( 'Form Style', 'total-theme-core' ),
						'param_name' => 'form_style',
						'std' => '',
						'value' => array_flip( vcex_get_form_styles() ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Remember Me', 'total-theme-core' ),
						'param_name' => 'remember',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Lost Password', 'total-theme-core' ),
						'param_name' => 'lost_password',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Register', 'total-theme-core' ),
						'param_name' => 'register',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Register URL', 'total-theme-core' ),
						'param_name' => 'register_url',
						'dependency' => array( 'element' => 'register', 'value' => 'true' ),
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
					vcex_vc_map_add_css_animation(),
					// Labels
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Username Label', 'total-theme-core' ),
						'param_name' => 'label_username',
						'group' =>  esc_html__( 'Labels', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Password Label', 'total-theme-core' ),
						'param_name' => 'label_password',
						'group' =>  esc_html__( 'Labels', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Remember Me Label', 'total-theme-core' ),
						'param_name' => 'label_remember',
						'group' =>  esc_html__( 'Labels', 'total-theme-core' ),
						'dependency' => array( 'element' => 'remember', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Lost Password Label', 'total-theme-core' ),
						'param_name' => 'lost_password_label',
						'dependency' => array( 'element' => 'lost_password', 'value' => 'true' ),
						'group' =>  esc_html__( 'Labels', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Register Label', 'total-theme-core' ),
						'param_name' => 'register_label',
						'dependency' => array( 'element' => 'register', 'value' => 'true' ),
						'group' =>  esc_html__( 'Labels', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Button Label', 'total-theme-core' ),
						'param_name' => 'label_log_in',
						'group' =>  esc_html__( 'Labels', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Redirect', 'total-theme-core' ),
						'param_name' => 'redirect',
						'description' => esc_html__( 'Enter a URL to redirect the user after they successfully log in. Leave blank to redirect to the current page.','total-theme-core'),
					),
					// Logged In Content
					array(
						'type' => 'textarea_html',
						'heading' => esc_html__( 'Logged in Content', 'total-theme-core' ),
						'param_name' => 'content',
						'value' => esc_html__( 'You are currently logged in.', 'total-theme-core' ) .' ' . '<a href="' . esc_url( wp_logout_url( home_url() ) ) . '">' . esc_html__( 'Logout?', 'total-theme-core' ) . '</a>',
						'description' => esc_html__( 'The content to displayed for logged in users.','total-theme-core'),
					),
					// Typography
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'text_font_size',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'text_color',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
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
new VCEX_Login_Form;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_login_form' ) ) {
	class WPBakeryShortCode_vcex_login_form extends WPBakeryShortCode {}
}
