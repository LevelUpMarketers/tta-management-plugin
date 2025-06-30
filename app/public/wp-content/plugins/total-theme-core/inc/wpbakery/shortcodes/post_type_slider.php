<?php
/**
 * Post Type Slider Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Post_Type_Flexslider_Shortcode' ) ) {

	class VCEX_Post_Type_Flexslider_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_post_type_flexslider';

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
					'vc_autocomplete_vcex_post_type_flexslider_tax_query_taxonomy_callback',
					'vcex_suggest_taxonomies'
				);
				add_filter(
					'vc_autocomplete_vcex_post_type_flexslider_tax_query_terms_callback',
					'vcex_suggest_terms'
				);
				add_filter(
					'vc_autocomplete_vcex_post_type_flexslider_author_in_callback',
					'vcex_suggest_users'
				);

				// Render autocomplete suggestions
				add_filter(
					'vc_autocomplete_vcex_post_type_flexslider_tax_query_taxonomy_render',
					'vcex_render_taxonomies'
				);
				add_filter(
					'vc_autocomplete_vcex_post_type_flexslider_tax_query_terms_render',
					'vcex_render_terms'
				);
				add_filter(
					'vc_autocomplete_vcex_post_type_flexslider_author_in_render',
					'vcex_render_users'
				);

			}

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Post Types Slider', 'total-theme-core' ),
				'description' => esc_html__( 'Posts slider', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-post-type-slider vcex-icon ticon ticon-files-o',
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
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Post Link Target', 'total-theme-core' ),
						'param_name' => 'link_target',
						'std' => 'self',
						'choices' => 'link_target',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Randomize', 'total-theme-core' ),
						'param_name' => 'randomize',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Loop', 'total-theme-core' ),
						'param_name' => 'loop',
					),
					// Slider Settings
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Animation', 'total-theme-core' ),
						'param_name' => 'animation',
						'std' => 'slide',
						'choices' => 'slider_animation',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Auto Height Animation', 'total-theme-core' ),
						'std' => '500',
						'param_name' => 'height_animation',
						'description' => esc_html__( 'You can enter "0.0" to disable the animation completely.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Animation Speed', 'total-theme-core' ),
						'param_name' => 'animation_speed',
						'std' => '600',
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Auto Play', 'total-theme-core' ),
						'param_name' => 'slideshow',
						'description' => esc_html__( 'Enable automatic slideshow? Disabled in front-end composer to prevent page "jumping".', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Auto Play Delay', 'total-theme-core' ),
						'param_name' => 'slideshow_speed',
						'std' => '5000',
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'slideshow', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Dot Navigation', 'total-theme-core' ),
						'param_name' => 'control_nav',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Arrows', 'total-theme-core' ),
						'param_name' => 'direction_nav',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Arrows on Hover', 'total-theme-core' ),
						'param_name' => 'direction_nav_hover',
						'dependency' => array( 'element' => 'direction_nav', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Thumbnails', 'total-theme-core' ),
						'param_name' => 'control_thumbs',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Thumbnail Carousel', 'total-theme-core' ),
						'param_name' => 'control_thumbs_carousel',
						'dependency' => array( 'element' => 'control_thumbs', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Thumbnails Pointer', 'total-theme-core' ),
						'param_name' => 'control_thumbs_pointer',
						'dependency' => array( 'element' => 'control_thumbs_carousel', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Navigation Thumbnails Height', 'total-theme-core' ),
						'param_name' => 'control_thumbs_height',
						'std' => '70',
						'dependency' => array( 'element' => 'control_thumbs', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Navigation Thumbnails Width', 'total-theme-core' ),
						'param_name' => 'control_thumbs_width',
						'std' => '70',
						'dependency' => array( 'element' => 'control_thumbs', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
						'param_name' => 'unique_id',
						'admin_label' => true,
						'description' => sprintf( esc_html__( 'Optional element ID (Note: make sure it is unique and valid according to %sw3c specification%s).', 'total-theme-core' ), '<a href="https://www.w3schools.com/tags/att_global_id.asp" target="_blank" rel="noopener noreferrer">', '</a>' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Classes', 'total-theme-core' ),
						'param_name' => 'classes',
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
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
						'type' => 'posttypes',
						'heading' => esc_html__( 'Post types', 'total-theme-core' ),
						'param_name' => 'post_types',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'std' => 'post',
						'admin_label' => true,
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Count', 'total-theme-core' ),
						'param_name' => 'posts_per_page',
						'value' => '4',
						'description' => esc_html__( 'You can enter "-1" to display all posts.', 'total-theme-core' ),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Limit By Post ID\'s', 'total-theme-core' ),
						'param_name' => 'posts_in',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'description' => esc_html__( 'Seperate by a comma.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Limit By Author', 'total-theme-core' ),
						'param_name' => 'author_in',
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => false,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
							//'values' => vcex_get_users(),
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Query by Taxonomy', 'total-theme-core' ),
						'param_name' => 'tax_query',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Taxonomy Name', 'total-theme-core' ),
						'param_name' => 'tax_query_taxonomy',
						'dependency' => array( 'element' => 'tax_query', 'value' => 'true' ),
						'settings' => array(
							'multiple' => false,
							'min_length' => 1,
							'groups' => false,
							'unique_values' => true,
							'display_inline' => true,
							'delay' => 500,
							'auto_focus' => true,
							//'values' => vcex_get_taxonomies(),
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Terms', 'total-theme-core' ),
						'param_name' => 'tax_query_terms',
						'dependency' => array( 'element' => 'tax_query', 'value' => 'true' ),
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
						'dependency' => array( 'element' => 'orderby', 'value' => array( 'meta_value_num', 'meta_value' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Ignore Sticky Posts', 'total-theme-core' ),
						'param_name' => 'ignore_sticky_posts',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					// Image
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
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
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
						'group' => esc_html__( 'Image', 'total-theme-core' )
					),
					array(
						'type' => 'vcex_overlay',
						'heading' => esc_html__( 'Image Overlay', 'total-theme-core' ),
						'param_name' => 'overlay_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'entry_media', 'value' => 'true' ),
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
						'type' => 'vcex_image_filters',
						'heading' => esc_html__( 'Image Filter', 'total-theme-core' ),
						'param_name' => 'img_filter',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'entry_media', 'value' => 'true' ),
					),
					// Caption
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Caption', 'total-theme-core' ),
						'param_name' => 'caption',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'caption_visibility',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'caption', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Location', 'total-theme-core' ),
						'param_name' => 'caption_location',
						'std' => 'over-image',
						'choices' => array(
							'over-image' => esc_html__( 'Over Image', 'total-theme-core' ),
							'under-image' => esc_html__( 'Under Image', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'caption', 'value' => 'true' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Breakpoint', 'total' ),
						'param_name' => 'caption_breakpoint',
						'value' => vcex_breakpoint_choices(),
						'description' => esc_html__( 'Breakpoint at which the caption goes over the image instead of below it.', 'total' ),
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'caption_location', 'value' => 'over-image' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Opacity', 'total-theme-core' ),
						'param_name' => 'caption_opacity',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'caption_location', 'value' => 'over-image' ),
						'value' => vcex_opacity_choices(),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Title', 'total-theme-core' ),
						'param_name' => 'title',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'caption', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Meta', 'total-theme-core' ),
						'param_name' => 'meta',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'caption', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'param_name' => 'excerpt',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'caption', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Excerpt Length', 'total-theme-core' ),
						'param_name' => 'excerpt_length',
						'value' => '40',
						'group' => esc_html__( 'Caption', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					// Design
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
new VCEX_Post_Type_Flexslider_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_post_type_flexslider' ) ) {
	class WPBakeryShortCode_vcex_post_type_flexslider extends WPBakeryShortCode {}
}
