<?php
/**
 * Staff Social Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Staff_Social' ) ) {

	class VCEX_Staff_Social {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'staff_social';

		/**
		 * Main constructor.
		 */
		public function __construct() {
			if ( function_exists( 'vc_lean_map' ) ) {
				add_action( 'vc_after_mapping', array( $this, 'vc_after_mapping' ) );
			}
		}

		/**
		 * VC functions.
		 */
		public function vc_after_mapping() {
			vc_lean_map( $this->shortcode, array( $this, 'map' ) );
			add_filter(
				'vc_autocomplete_staff_social_post_id_callback',
				'vcex_suggest_staff_members'
			);
			add_filter(
				'vc_autocomplete_staff_social_post_id_render',
				'vcex_render_staff_members'
			);
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Staff Social Links', 'total-theme-core' ),
				'description' => esc_html__( 'Single staff social links', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-staff-social vcex-icon ticon ticon-share-alt',
				'params' => array(
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Staff Member ID', 'total-theme-core' ),
						'param_name' => 'post_id',
						'admin_label' => true,
						'param_holder_class' => 'vc_not-for-custom',
						'description' => esc_html__( 'Select a staff member to display their social links. By default it will diplay the current staff member links.', 'total-theme-core'),
						'settings' => array(
							'multiple' => false,
							'min_length' => 1,
							'groups' => false,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
						),
					),
					array(
						'type' => 'vcex_social_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'std' => get_theme_mod( 'staff_social_default_style', 'minimal-round' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Link Target', 'total-theme-core' ),
						'param_name' => 'link_target',
						'value' => array(
							esc_html__( 'Blank', 'total-theme-core' ) => 'blank',
							esc_html__( 'Self', 'total-theme-core') => 'self',
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Icon Size', 'total-theme-core' ),
						'param_name' => 'font_size',
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Icon Margin', 'total-theme-core' ),
						'param_name' => 'icon_margin',
					),
					vcex_vc_map_add_css_animation(),
					// Design Options
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
new VCEX_Staff_Social;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_staff_social' ) ) {
	class WPBakeryShortCode_staff_social extends WPBakeryShortCode {}
}
