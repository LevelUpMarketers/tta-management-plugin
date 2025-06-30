<?php
/**
 * WooCommerce Carousel Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Woocommerce_Carousel_Shortcode' ) ) {

	class VCEX_Woocommerce_Carousel_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_woocommerce_carousel';

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

				add_filter(
					'vc_autocomplete_vcex_woocommerce_carousel_include_categories_callback',
					'vcex_suggest_product_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_woocommerce_carousel_exclude_categories_callback',
					'vcex_suggest_product_categories'
				);

				add_filter(
					'vc_autocomplete_vcex_woocommerce_carousel_include_categories_render',
					'vcex_render_product_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_woocommerce_carousel_exclude_categories_render',
					'vcex_render_product_categories'
				);

			}

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {

			$settings = array(
				'name' => esc_html__( 'Woo Products Carousel (Custom)', 'total-theme-core' ),
				'description' => esc_html__( 'Custom products carousel', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-woocommerce-carousel vcex-icon ticon ticon-shopping-cart',
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
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					// Query
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Post Count', 'total-theme-core' ),
						'param_name' => 'count',
						'value' => '8',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => '',
						'vcex' => array( 'on' => true, 'off' => '' ),
						'heading' => esc_html__( 'Featured Products Only', 'total-theme-core' ),
						'param_name' => 'featured_products_only',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Exclude Out of Stock Products', 'total-theme-core' ),
						'param_name' => 'exclude_products_out_of_stock',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'std' => '',
						'vcex' => array( 'on'  => true, 'off' => '' ),
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
					),
					array(
						'type' => 'vcex_orderby',
						'heading' => esc_html__( 'Order By', 'total-theme-core' ),
						'param_name' => 'orderby',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'post_type' => 'woo_product',
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
					// Image
					array(
						'type' => 'vcex_select_buttons',
						'std' => 'post',
						'heading' => esc_html__( 'Image Links To', 'total-theme-core' ),
						'param_name' => 'thumbnail_link',
						'choices' => array(
							'post' => esc_html__( 'Post', 'total-theme-core' ),
							'lightbox' => esc_html__( 'Lightbox', 'total-theme-core' ),
							'none' => esc_html__( 'None', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
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
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'description' => esc_html__( 'Enter a width in pixels.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array(
							'element' => 'img_size',
							'value' => 'wpex_custom',
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'dependency' => array(
							'element' => 'img_size',
							'value' => 'wpex_custom',
						),
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_overlay',
						'heading' => esc_html__( 'Image Overlay', 'total-theme-core' ),
						'param_name' => 'overlay_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
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
						'heading' => esc_html__( 'CSS3 Image Hover', 'total-theme-core' ),
						'param_name' => 'img_hover_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_filters',
						'heading' => esc_html__( 'Image Filter', 'total-theme-core' ),
						'param_name' => 'img_filter',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					// Title
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'vcex' => array( 'on'  => 'true', 'off' => 'false' ),
						'heading' => esc_html__( 'Display Title', 'total-theme-core' ),
						'param_name' => 'title',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'content_heading_color',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'content_heading_size',
						'description' => esc_html__( 'You can use em or px values, but you must define them.', 'total-theme-core' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'content_heading_margin',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'content_heading_line_height',
						'description' => esc_html__( 'Enter a numerical, pixel or percentage value.', 'total-theme-core' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'content_heading_weight',
						'description' => esc_html__( 'Note: Not all font families support every font weight.', 'total-theme-core' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'content_heading_transform',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					// Price
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'vcex' => array( 'on'  => 'true', 'off' => 'false' ),
						'heading' => esc_html__( 'Display Price', 'total-theme-core' ),
						'param_name' => 'price',
						'group' => esc_html__( 'Price', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'content_color',
						'group' => esc_html__( 'Price', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'content_font_size',
						'group' => esc_html__( 'Price', 'total-theme-core' ),
						'description' => esc_html__( 'You can use em or px values, but you must define them.', 'total-theme-core' ),
					),
					// Readmore
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'read_more',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
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
						'std' => 'false',
						'heading' => esc_html__( 'Arrow', 'total-theme-core' ),
						'param_name' => 'readmore_rarr',
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
					// Design
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core') => 'default',
							esc_html__( 'No Margins', 'total-theme-core' ) => 'no-margins',
						),
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Content Background', 'total-theme-core' ),
						'param_name' => 'content_background',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Content Alignment', 'total-theme-core' ),
						'param_name' => 'content_alignment',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Content Margin', 'total-theme-core' ),
						'param_name' => 'content_margin',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Content Padding', 'total-theme-core' ),
						'param_name' => 'content_padding',
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Content Border', 'total-theme-core' ),
						'param_name' => 'content_border',
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'group' => esc_html__( 'Design', 'total-theme-core' ),
					),
				),
			);

			$settings[ 'params' ] = array_merge( $settings[ 'params' ], vcex_vc_map_carousel_settings() );

			return $settings;

		}

	}
}
new VCEX_Woocommerce_Carousel_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_woocommerce_carousel' ) ) {
	class WPBakeryShortCode_vcex_woocommerce_carousel extends WPBakeryShortCode {}
}
