<?php
/**
 * WooCommerce Loop Carousel
 *
 * @package Total WordPress Theme
 * @subpackage VC Functions
 * @version 1.2
 */

if ( ! class_exists( 'VCEX_WooCommerce_Loop_Carousel' ) ) {

	class VCEX_WooCommerce_Loop_Carousel {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_woocommerce_loop_carousel';

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
					'vc_autocomplete_vcex_woocommerce_loop_carousel_include_categories_callback',
					'vcex_suggest_product_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_woocommerce_loop_carousel_exclude_categories_callback',
					'vcex_suggest_product_categories'
				);

				// Render autocomplete suggestions
				add_filter(
					'vc_autocomplete_vcex_woocommerce_loop_carousel_include_categories_render',
					'vcex_render_product_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_woocommerce_loop_carousel_exclude_categories_render',
					'vcex_render_product_categories'
				);

			}

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			$settings = array(
				'name' => esc_html__( 'Woo Products Carousel', 'total-theme-core' ),
				'description' => esc_html__( 'WooCommerce products carousel.', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-woocommerce-carousel vcex-icon ticon ticon-shopping-cart',
				'params' => array(
					// General
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
						'type' => 'dropdown',
						'heading' => esc_html__( 'Display', 'total-theme-core' ),
						'param_name' => 'query_products_by',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'All Products', 'total-theme-core' ) => '',
							esc_html__( 'Featured Products', 'total-theme-core' ) => 'featured',
							esc_html__( 'On Sale Products', 'total-theme-core' ) => 'on_sale',
						),
						'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => '',
						'vcex' => array( 'on' => true, 'off' => '' ),
						'heading' => esc_html__( 'Exclude Out of Stock Products', 'total-theme-core' ),
						'param_name' => 'exclude_products_out_of_stock',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
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
							esc_html__( 'Default', 'total-theme-core' ) => '',
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
						'post_type' => 'woo_product',
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
				),
			);

			$settings[ 'params' ] = array_merge( $settings[ 'params' ], vcex_vc_map_carousel_settings() );

			$settings[ 'params' ][] = array(
				'type' => 'hidden',
				'param_name' => 'entry_output',
			);

			return $settings;

		}

	}
}
new VCEX_WooCommerce_Loop_Carousel;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_woocommerce_loop_carousel' ) ) {
	class WPBakeryShortCode_vcex_woocommerce_loop_carousel extends WPBakeryShortCode {}
}
