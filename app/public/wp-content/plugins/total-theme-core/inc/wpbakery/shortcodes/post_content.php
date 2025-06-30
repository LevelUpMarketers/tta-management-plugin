<?php
/**
 * Post Content Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Post_Content_Shortcode' ) ) {

	class VCEX_Post_Content_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_post_content';

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
				add_filter( 'vc_edit_form_fields_attributes_' . $this->shortcode, array( $this, 'edit_form_fields' ) );
			}

		}

		/**
		 * Update fields on edit.
		 */
		public function edit_form_fields( $atts ) {

			if ( empty( $atts['blocks'] ) ) {

				$blocks = array();

				$settings_to_check = array(
					'post_series',
					'the_content',
					'social_share',
					'author_bio',
					'related',
					'comments',
				);

				foreach( $settings_to_check as $setting ) {

					if ( 'the_content' == $setting ) {
						$blocks[] = $setting;
					} elseif ( isset( $atts[$setting] ) && 'true' == $atts[$setting] ) {
						$blocks[] = $setting;
					}

				}

				if ( $blocks ) {
					$atts['blocks'] = implode( ',', $blocks );
				}

			}

			return $atts;
		}

		/**
		 * Map shortcode to VC
		 *
		 * @since 4.3
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Post Content', 'total-theme-core' ),
				'description' => esc_html__( 'Display your post content.', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-post-content vcex-icon ticon ticon-pencil',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					array(
						'type' => 'vcex_notice',
						'param_name' => 'main_notice',
						'text' => esc_html__( 'The Post Content module should be used only when creating a custom template via templatera that will override the default output of a post/page.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Enable Sidebar', 'total-theme-core' ),
						'param_name' => 'sidebar',
						'std' => 'false',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Sidebar Position', 'total-theme-core' ),
						'param_name' => 'sidebar_position',
						'value' => array(
							esc_html__( 'Right', 'total-theme-core' ) => 'right',
							esc_html__( 'Left', 'total-theme-core' ) => 'left',
						),
						'dependency' => array( 'element' => 'sidebar', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_sorter',
						'heading' => esc_html__( 'Blocks', 'total-theme-core' ),
						'param_name' => 'blocks',
						'std' => 'the_content',
						'admin_label' => true,
						'choices' => apply_filters( 'vcex_post_content_blocks', array(
							'the_content'    => esc_html__( 'The Content', 'total-theme-core' ),
							'featured_media' => esc_html__( 'Featured Media', 'total-theme-core' ),
							'title'          => esc_html__( 'Title', 'total-theme-core' ),
							'meta'           => esc_html__( 'Meta', 'total-theme-core' ),
							'series'         => esc_html__( 'Series', 'total-theme-core' ),
							'social_share'   => esc_html__( 'Social Share', 'total-theme-core' ),
							'author_bio'     => esc_html__( 'Author Bio', 'total-theme-core' ),
							'related'        => esc_html__( 'Related Posts', 'total-theme-core' ),
							'comments'       => esc_html__( 'Comments', 'total-theme-core' ),
						) ),
						'description' => esc_html__( 'By default only "The Content" block is enabled but you can click on the toggle icon to enable any block. Drag and drop the items for custom sorting. The purpose for allowing you to enable other blocks is so you can have them next to the sidebar when enabled in this module. You can also add custom blocks via your child theme or code snippets plugin using the "vcex_post_content_blocks" filter.', 'total-theme-core' ),
					),
					// Typography
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'font_family',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
						'description' => esc_html__( 'Applies to the content block only.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'target' => 'font-size',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
						'description' => esc_html__( 'Applies to the content block only.', 'total-theme-core' ),
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
new VCEX_Post_Content_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_post_content' ) ) {
	class WPBakeryShortCode_vcex_post_content extends WPBakeryShortCode {}
}
