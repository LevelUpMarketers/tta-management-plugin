<?php
/**
 * Post Meta Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Post_Meta_Shortcode' ) ) {

	class VCEX_Post_Meta_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_post_meta';

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
				'name' => esc_html__( 'Post Meta', 'total-theme-core' ),
				'description' => esc_html__( 'Author, date, comments...', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-post-meta vcex-icon ticon ticon-list-alt',
				'params' => array(
					// Sections
					array(
						'type' => 'param_group',
						'param_name' => 'sections',
						'group' => esc_html__( 'Blocks', 'total-theme-core' ),
						'value' => urlencode( json_encode( array(
							array(
								'type' => 'date',
								'icon' => 'ticon ticon-clock-o',
							),
							array(
								'type' => 'author',
								'icon' => 'ticon ticon-user-o',
							),
							array(
								'type' => 'comments',
								'icon' => 'ticon ticon-comment-o',
							),
							array(
								'type' => 'post_terms',
								'taxonomy' => 'category',
								'fist_only' => 'false',
								'icon' => 'ticon ticon-folder-o',
							),
						) ) ),
						'params' => array(
							array(
								'type' => 'dropdown',
								'heading' => esc_html__( 'Section', 'total-theme-core' ),
								'param_name' => 'type',
								'admin_label' => true,
								'value' => apply_filters( 'vcex_post_meta_sections', array(
									esc_html__( 'Date', 'total-theme-core' ) => 'date',
									esc_html__( 'Author', 'total-theme-core' ) => 'author',
									esc_html__( 'Comments', 'total-theme-core' ) => 'comments',
									esc_html__( 'Post Terms', 'total-theme-core' ) => 'post_terms',
									esc_html__( 'Last Updated', 'total-theme-core' ) => 'modified_date',
									esc_html__( 'Callback Function', 'total-theme-core' ) => 'callback',
								) ),
							),
							// Label
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Label', 'total-theme-core' ),
								'param_name' => 'label',
							),
							// Taxonomy Select
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Taxonony Name', 'total-theme-core' ),
								'param_name' => 'taxonomy',
								'dependency' => array( 'element' => 'type', 'value' => 'post_terms' )
							),
							// Date Format
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Date Format', 'total-theme-core' ),
								'param_name' => 'date_format',
								'dependency' => array( 'element' => 'type', 'value' => array( 'date', 'last_modified' ) ),
								'description' => sprintf( esc_html__( 'Enter your preferred date format according to the %sWordPress manual%s.', 'total-theme-core' ), '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">', '</a>' ),
							),
							// Callback Function
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Callback Function', 'total-theme-core' ),
								'param_name' => 'callback_function',
								'dependency' => array( 'element' => 'type', 'value' => 'callback' )
							),
							// Icon select
							array(
								'type' => 'dropdown',
								'heading' => esc_html__( 'Icon library', 'total-theme-core' ),
								'param_name' => 'icon_type',
								'description' => esc_html__( 'Select icon library.', 'total-theme-core' ),
								'value' => array(
									esc_html__( 'Theme Icons', 'total-theme-core' )  => '',
									esc_html__( 'Font Awesome', 'total-theme-core' ) => 'fontawesome',
									esc_html__( 'Typicons', 'total-theme-core' )     => 'typicons',
								),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'total-theme-core' ),
								'param_name' => 'icon',
								'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
								'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'total-theme-core' ),
								'param_name' => 'icon_fontawesome',
								'settings' => array( 'emptyIcon' => true, 'iconsPerPage' => 100 ),
								'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'total-theme-core' ),
								'param_name' => 'icon_typicons',
								'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100 ),
								'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
							),
						),
					),
					// General
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'choices' => array(
							'' => esc_html__( 'Default', 'total-theme-core' ),
							'vertical' => esc_html__( 'Vertical', 'total-theme-core' ),
						),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Label Colon', 'total-theme-core' ),
						'param_name' => 'label_colon',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					vcex_vc_map_add_css_animation(),
					// Typography
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					// Design options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design options', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Post_Meta_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_post_meta' ) ) {
	class WPBakeryShortCode_vcex_post_meta extends WPBakeryShortCode {}
}
