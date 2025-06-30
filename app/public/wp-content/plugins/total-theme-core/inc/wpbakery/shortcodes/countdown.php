<?php
/**
 * Countdown Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Countdown_Shortcode' ) ) {

	class VCEX_Countdown_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_countdown';

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
		 * Enqueue scripts.
		 */
		public function enqueue_scripts( $atts ) {

			wp_enqueue_script(
				'countdown',
				vcex_asset_url( 'js/lib/countdown.min.js' ),
				array( 'jquery' ),
				'2.1.0',
				true
			);

			if ( vcex_vc_is_inline() || $atts['timezone'] ) {

				wp_enqueue_script(
					'moment-with-locales',
					vcex_asset_url( 'js/lib/moment-with-locales.min.js' ),
					array( 'jquery' ),
					'2.10.0',
					true
				);

				wp_enqueue_script(
					'moment-timezone-with-data',
					vcex_asset_url( 'js/lib/moment-timezone-with-data.min.js' ),
					array( 'jquery' ),
					'2.10.0',
					true
				);

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
			vc_add_shortcode_param( 'vcex_timezones', array( $this, 'timezones' ) );
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Countdown', 'total' ),
				'description' => esc_html__( 'Animated countdown clock', 'total' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-countdown vcex-icon ticon ticon-clock-o',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin',
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_timezones',
						'heading' => esc_html__( 'Time Zone', 'total' ),
						'param_name' => 'timezone',
						'description' => esc_html__( 'If a time zone is not selected the time zone will be based on the visitors computer time.', 'total' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'End Month', 'total' ),
						'param_name' => 'end_month',
						'value' => array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'End Day', 'total' ),
						'param_name' => 'end_day',
						'value' => array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'End Year', 'total' ),
						'param_name' => 'end_year',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'End Time', 'total' ),
						'param_name' => 'end_time',
						'description' => esc_html__( 'Enter your custom end time in military format. Example if your event starts at 1:30pm enter 13:30', 'total' ),
					),
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Extra class name', 'total' ),
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total' ),
						'param_name' => 'el_class',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					// Typography
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total' ),
						'param_name' => 'font_family',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'target' => 'font-size',
						'heading' => esc_html__( 'Font Size', 'total' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total' ),
						'param_name' => 'line_height',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total' ),
						'param_name' => 'letter_spacing',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Italic', 'total' ),
						'param_name' => 'italic',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total' ),
						'param_name' => 'font_weight',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total' ),
						'param_name' => 'text_align',
						'group' => esc_html__( 'Typography', 'total' ),
					),
					// Translations
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Days', 'total' ),
						'param_name' => 'days',
						'group' =>  esc_html__( 'Strings', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Hours', 'total' ),
						'param_name' => 'hours',
						'group' =>  esc_html__( 'Strings', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Minutes', 'total' ),
						'param_name' => 'minutes',
						'group' =>  esc_html__( 'Strings', 'total' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Seconds', 'total' ),
						'param_name' => 'seconds',
						'group' =>  esc_html__( 'Strings', 'total' ),
					),
				)
			);
		}

		/**
		 * Return array of timezones.
		 */
		public function timezones( $settings, $value ) {

			$output = '<select name="'
					. $settings['param_name']
					. '" class="wpb_vc_param_value wpb-input wpb-select vcex-chosen '
					. $settings['param_name']
					. ' ' . $settings['type'] .'">';

			$output .= '<option value="" '. selected( $value, '', false ) .'>' . esc_html__( 'Default', 'total' ) . '</option>';

			if ( function_exists( 'timezone_identifiers_list' ) ) {

				$zones = timezone_identifiers_list();

				foreach ( $zones as $zone ) {

					$output .= '<option value="'. esc_attr( $zone )  .'" '. selected( $value, $zone, false ) .'>'. esc_attr( $zone ) .'</option>';

				}

			}

			$output .= '</select>';

			return $output;

		}

	}
}
new VCEX_Countdown_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_countdown' ) ) {
	class WPBakeryShortCode_vcex_countdown extends WPBakeryShortCode {}
}