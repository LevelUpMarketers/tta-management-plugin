<?php
/**
 * Testimonials Grid Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Testimonials_Grid_Shortcode' ) ) {

	class VCEX_Testimonials_Grid_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_testimonials_grid';

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

			$vc_action = vc_request_param( 'action' );

			if ( 'vc_get_autocomplete_suggestion' === $vc_action || 'vc_edit_form' === $vc_action ) {

				// Get autocomplete suggestion
				add_filter(
					'vc_autocomplete_vcex_testimonials_grid_include_categories_callback',
					'vcex_suggest_testimonials_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_testimonials_grid_exclude_categories_callback',
					'vcex_suggest_testimonials_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_testimonials_grid_filter_active_category_callback',
					'vcex_suggest_testimonials_categories'
				);

				// Render autocomplete suggestions
				add_filter(
					'vc_autocomplete_vcex_testimonials_grid_include_categories_render',
					'vcex_render_testimonials_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_testimonials_grid_exclude_categories_render',
					'vcex_render_testimonials_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_testimonials_grid_filter_active_category_render',
					'vcex_render_testimonials_categories'
				);

			}
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Testimonials Grid', 'total-theme-core' ),
				'description' => esc_html__( 'Recent testimonials post grid', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-testimonials-grid vcex-icon ticon ticon-comments-o',
				'params' => array(
					// General
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Header', 'total-theme-core' ),
						'param_name' => 'header',
						'admin_label' => true,
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Header Style', 'total-theme-core' ),
						'param_name' => 'header_style',
						'value' => vcex_get_theme_heading_styles(),
						'description' => sprintf( esc_html__( 'Select your custom heading style. You can select your global style in %sthe Customizer%s.', 'total-theme-core' ), '<a href="' . esc_url( admin_url( '/customize.php?autofocus[section]=wpex_theme_heading' ) ) . '" target="_blank" rel="noopener noreferrer">', '</a>' ),
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
						'heading' => esc_html__( 'Grid Style', 'total-theme-core' ),
						'param_name' => 'grid_style',
						'value' => array(
							esc_html__( 'Fit Columns', 'total-theme-core' ) => 'fit_columns',
							esc_html__( 'Masonry', 'total-theme-core' ) => 'masonry',
						),
						'edit_field_class' => 'vc_col-sm-3 vc_column clear',
					),
					array(
						'type' => 'vcex_grid_columns',
						'heading' => esc_html__( 'Columns', 'total-theme-core' ),
						'param_name' => 'columns',
						'std' => '3',
						'edit_field_class' => 'vc_col-sm-3 vc_column',
					),
					array(
						'type' => 'vcex_column_gaps',
						'heading' => esc_html__( 'Gap', 'total-theme-core' ),
						'param_name' => 'columns_gap',
						'edit_field_class' => 'vc_col-sm-3 vc_column',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Responsive', 'total-theme-core' ),
						'param_name' => 'columns_responsive',
						'value' => array(
							esc_html__( 'Yes', 'total-theme-core' ) => 'true',
							esc_html__( 'No', 'total-theme-core' ) => 'false',
						),
						'edit_field_class' => 'vc_col-sm-3 vc_column',
						'dependency' => array( 'element' => 'columns', 'value' => array( '2', '3', '4', '5', '6', '7', '8', '9', '10' ) ),
					),
					array(
						'type' => 'vcex_grid_columns_responsive',
						'heading' => esc_html__( 'Responsive Settings', 'total-theme-core' ),
						'param_name' => 'columns_responsive_settings',
						'dependency' => array( 'element' => 'columns_responsive', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
						'param_name' => 'unique_id',
						'admin_label' => true,
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
					// Query
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Advanced Query', 'total-theme-core' ),
						'param_name' => 'custom_query',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'description' => esc_html__( 'Enable to build a custom query using your own parameters.', 'total-theme-core' ),
					),
					array(
						'type' => 'textarea_safe',
						'heading' => esc_html__( 'Query Parameter String or Callback Function Name', 'total-theme-core' ),
						'param_name' => 'custom_query_args',
						'description' => esc_html__( 'Build a query according to the WordPress Codex in string format. Example: posts_per_page=-1&post_type=portfolio&post_status=publish&orderby=title or enter a custom callback function name that will return an array of query arguments.', 'total-theme-core' ),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'true' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Posts Per Page', 'total-theme-core' ),
						'param_name' => 'posts_per_page',
						'value' => '-1',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Pagination', 'total-theme-core' ),
						'param_name' => 'pagination',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Load More Button', 'total-theme-core' ),
						'param_name' => 'pagination_loadmore',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Include Categories', 'total-theme-core' ),
						'param_name' => 'include_categories',
						'param_holder_class' => 'vc_not-for-custom',
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => false,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'admin_label' => true,
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Exclude Categories', 'total-theme-core' ),
						'param_name' => 'exclude_categories',
						'param_holder_class' => 'vc_not-for-custom',
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => false,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'admin_label' => true,
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order', 'total-theme-core' ),
						'param_name' => 'order',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							esc_html__( 'DESC', 'total-theme-core' ) => 'DESC',
							esc_html__( 'ASC', 'total-theme-core' ) => 'ASC',
						),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order By', 'total-theme-core' ),
						'param_name' => 'orderby',
						'value' => vcex_orderby_array(),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Orderby: Meta Key', 'total-theme-core' ),
						'param_name' => 'orderby_meta_key',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array(
							'element' => 'orderby',
							'value' => array( 'meta_value_num', 'meta_value' ),
						),
					),
					// Filter
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'filter',
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Display All Link?', 'total-theme-core' ),
						'param_name' => 'filter_all_link',
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'no',
						'heading' => esc_html__( 'Center Filter Links', 'total-theme-core' ),
						'param_name' => 'center_filter',
						'vcex' => array(
							'off' => 'no',
							'on' => 'yes',
						),
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Default Active Category', 'total-theme-core' ),
						'param_name' => 'filter_active_category',
						'param_holder_class' => 'vc_not-for-custom',
						'settings' => array(
							'multiple' => false,
							'min_length' => 1,
							'groups' => false,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
						),
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Filter "All" Text', 'total-theme-core' ),
						'param_name' => 'all_text',
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter_all_link', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Button Style', 'total-theme-core' ),
						'param_name' => 'filter_button_style',
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'std' => 'minimal-border',
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Button Color', 'total-theme-core' ),
						'param_name' => 'filter_button_color',
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Layout Mode', 'total-theme-core' ),
						'param_name' => 'masonry_layout_mode',
						'value' => array(
							esc_html__( 'Masonry', 'total-theme-core' ) => 'masonry',
							esc_html__( 'Fit Rows', 'total-theme-core' ) => 'fitRows',
						),
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Filter Speed', 'total-theme-core' ),
						'param_name' => 'filter_speed',
						'description' => esc_html__( 'Default is 0.4 seconds. Enter 0.0 to disable.', 'total-theme-core' ),
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'filter_font_size',
						'group' => esc_html__( 'Filter', 'total-theme-core' ),
						'dependency' => array( 'element' => 'filter', 'value' => 'true' ),
					),
					// Image
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'entry_media',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'img_border_radius',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'entry_media', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'entry_media', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_image_crop_locations',
						'heading' => esc_html__( 'Image Crop Location', 'total-theme-core' ),
						'param_name' => 'img_crop',
						'std' => 'center-center',
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					// Title
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'title',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Link to Post', 'total-theme-core' ),
						'param_name' => 'title_link',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'HTML Tag', 'total-theme-core' ),
						'param_name' => 'title_tag',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'std' => 'h2',
						'value' => array(
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5',
							'h6' => 'h6',
							'div' => 'div',
						),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'title_font_family',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'title_font_size',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'title_color',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'title_bottom_margin',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					// Author
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Author', 'total-theme-core' ),
						'param_name' => 'author',
						'group' => esc_html__( 'Details', 'total-theme-core' ),
					),
					// Company
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Company', 'total-theme-core' ),
						'param_name' => 'company',
						'group' => esc_html__( 'Details', 'total-theme-core' ),
					),
					// Rating
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Rating', 'total-theme-core' ),
						'param_name' => 'rating',
						'group' => esc_html__( 'Details', 'total-theme-core' ),
					),
					// Content
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'content_font_size',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'content_color',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'param_name' => 'excerpt',
						 'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Excerpt Length', 'total-theme-core' ),
						'param_name' => 'excerpt_length',
						'value' => '20',
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Read More', 'total-theme-core' ),
						'param_name' => 'read_more',
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text', 'total-theme-core' ),
						'param_name' => 'read_more_text',
						'value' => esc_html__( 'read more', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Arrow', 'total-theme-core' ),
						'param_name' => 'read_more_rarr',
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
				),
			);
		}

	}
}
new VCEX_Testimonials_Grid_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_testimonials_grid' ) ) {
	class WPBakeryShortCode_vcex_testimonials_grid extends WPBakeryShortCode {}
}
