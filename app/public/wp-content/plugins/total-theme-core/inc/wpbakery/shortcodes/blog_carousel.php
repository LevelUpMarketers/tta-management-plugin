<?php
/**
 * Blog Carousel Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Blog_Carousel_Shortcode' ) ) {

	class VCEX_Blog_Carousel_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_blog_carousel';

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
				add_filter( 'vc_autocomplete_' . $this->shortcode . '_include_categories_callback', 'vcex_suggest_categories', 10, 1 );
				add_filter( 'vc_autocomplete_' . $this->shortcode . '_exclude_categories_callback', 'vcex_suggest_categories', 10, 1 );

				// Render autocomplete suggestions
				add_filter( 'vc_autocomplete_' . $this->shortcode . '_include_categories_render', 'vcex_render_categories', 10, 1 );
				add_filter( 'vc_autocomplete_' . $this->shortcode . '_exclude_categories_render', 'vcex_render_categories', 10, 1 );

			}

			if ( 'vc_edit_form' === $vc_action ) {
				add_filter( 'vc_edit_form_fields_attributes_' . $this->shortcode, 'vcex_parse_deprecated_grid_entry_content_css' );
			}

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			$settings = array(
				'name' => esc_html__( 'Blog Carousel', 'total-theme-core' ),
				'description' => esc_html__( 'Recent blog posts carousel', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-blog-carousel vcex-icon ticon ticon-pencil',
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
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Content Style', 'total-theme-core' ),
						'param_name' => 'content_style',
						'std' => 'boxed',
						'choices' => vcex_entry_content_styles(),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Content Alignment', 'total-theme-core' ),
						'param_name' => 'content_alignment',
						'std' => '',
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
						'heading' => esc_html__( 'Post Count', 'total-theme-core' ),
						'param_name' => 'count',
						'value' => '8',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Offset', 'total-theme-core' ),
						'param_name' => 'offset',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'description' => esc_html__( 'Number of post to displace or pass over. Warning: Setting the offset parameter overrides/ignores the paged parameter and breaks pagination. The offset parameter is ignored when posts per page is set to -1.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Post With Thumbnails Only', 'total-theme-core' ),
						'param_name' => 'thumbnail_query',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Ignore Sticky Posts', 'total-theme-core' ),
						'param_name' => 'ignore_sticky_posts',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'std' => 'false',
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Include Categories', 'total-theme-core' ),
						'param_name' => 'include_categories',
						'param_holder_class' => 'vc_not-for-custom',
						'admin_label' => true,
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => true,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Exclude Categories', 'total-theme-core' ),
						'param_name' => 'exclude_categories',
						'param_holder_class' => 'vc_not-for-custom',
						'admin_label' => true,
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => true,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order', 'total-theme-core' ),
						'param_name' => 'order',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' )=> '',
							esc_html__( 'DESC', 'total-theme-core' ) => 'DESC',
							esc_html__( 'ASC', 'total-theme-core' ) => 'ASC',
						),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_orderby',
						'heading' => esc_html__( 'Order By', 'total-theme-core' ),
						'param_name' => 'orderby',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Orderby: Meta Key', 'total-theme-core' ),
						'param_name' => 'orderby_meta_key',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'orderby', 'value' => array( 'meta_value_num', 'meta_value' ) ),
					),
					// Image
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'media',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'std' => 'true',
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Image Links To', 'total-theme-core' ),
						'param_name' => 'thumbnail_link',
						'std' => 'post',
						'choices' => array(
							'post' => esc_html__( 'Post', 'total-theme-core' ) ,
							'lightbox' => esc_html__( 'Lightbox', 'total-theme-core' ) ,
							'none' => esc_html__( 'None', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'media', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Lightbox Gallery', 'total-theme-core' ),
						'param_name' => 'lightbox_gallery',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'thumbnail_link', 'value' => 'lightbox' ),
					),
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'media', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_image_crop_locations',
						'heading' => esc_html__( 'Image Crop Location', 'total-theme-core' ),
						'param_name' => 'img_crop',
						'std' => 'center-center',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom', ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom', ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom', ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Image Border Radius', 'total' ),
						'param_name' => 'img_border_radius',
						'value' => vcex_border_radius_choices(),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'media', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_overlay',
						'heading' => esc_html__( 'Image Overlay', 'total-theme-core' ),
						'param_name' => 'overlay_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'media', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Overlay Button Text', 'total-theme-core' ),
						'param_name' => 'overlay_button_text',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'overlay_style', 'value' => 'hover-button' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Overlay Excerpt Length', 'total-theme-core' ),
						'param_name' => 'overlay_excerpt_length',
						'value' => '15',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'overlay_style', 'value' => 'title-excerpt-hover' ),
					),
					array(
						'type' => 'vcex_image_hovers',
						'heading' => esc_html__( 'Image Hover', 'total-theme-core' ),
						'param_name' => 'img_hover_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'media', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_image_filters',
						'heading' => esc_html__( 'Image Filter', 'total-theme-core' ),
						'param_name' => 'img_filter',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'media', 'value' => 'true' ),
					),
					// Title
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'title',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'std' => 'true',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'content_heading_color',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'content_heading_size',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__(  'Margin', 'total-theme-core' ),
						'param_name' => 'content_heading_margin',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'content_heading_line_height',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'content_heading_weight',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'std' => '',
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'content_heading_transform',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					// Date
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'date',
						'group' => esc_html__( 'Date', 'total-theme-core' ),
						'std' => 'true',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'date_color',
						'group' => esc_html__( 'Date', 'total-theme-core' ),
						'dependency' => array( 'element' => 'date', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'date_font_size',
						'group' => esc_html__( 'Date', 'total-theme-core' ),
						'dependency' => array( 'element' => 'date', 'value' => 'true' ),
					),
					// Excerpt
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'excerpt',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'std' => 'true',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Length', 'total-theme-core' ),
						'param_name' => 'excerpt_length',
						'value' => '15',
						'description' => esc_html__( 'Enter how many words to display for the excerpt. To display the full post content enter "-1". To display the full post content up to the "more" tag enter "9999".', 'total-theme-core' ),
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'content_font_size',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Text Color', 'total-theme-core' ),
						'param_name' => 'content_color',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					// Readmore
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'read_more',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'std' => 'false',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Text', 'total-theme-core' ),
						'param_name' => 'read_more_text',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'readmore_style',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'readmore_style_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Arrow', 'total-theme-core' ),
						'param_name' => 'readmore_rarr',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'std' => 'false',
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'readmore_background',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'readmore_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'readmore_hover_background',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'readmore_hover_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'readmore_size',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'readmore_border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total-theme-core' ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'readmore_padding',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'readmore_margin',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					// Design Options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'content_css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'std' => 'default',
						'choices' => array(
							'default' => esc_html__( 'Default', 'total-theme-core' ),
							'no-margins' => esc_html__( 'No Margins', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Opacity', 'total-theme-core' ),
						'param_name' => 'content_opacity',
						'description' => esc_html__( 'Enter a value between "0" and "1".', 'total-theme-core' ),
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					// Hidden/Deprecated fields
					array( 'type' => 'hidden', 'param_name' => 'content_background' ),
					array( 'type' => 'hidden', 'param_name' => 'content_margin' ),
					array( 'type' => 'hidden', 'param_name' => 'content_padding' ),
					array( 'type' => 'hidden', 'param_name' => 'content_border' ),
				),
			);

			$settings[ 'params' ] = array_merge( $settings[ 'params' ], vcex_vc_map_carousel_settings() );

			return $settings;

		}

	}

}
new VCEX_Blog_Carousel_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_blog_carousel' ) ) {
	class WPBakeryShortCode_vcex_blog_carousel extends WPBakeryShortCode {}
}
