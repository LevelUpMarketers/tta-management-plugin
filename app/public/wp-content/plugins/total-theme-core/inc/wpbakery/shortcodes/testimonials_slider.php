<?php
/**
 * Testimonials Slider Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Testimonials_Slider_Shortcode' ) ) {

	class VCEX_Testimonials_Slider_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_testimonials_slider';

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
					'vc_autocomplete_vcex_testimonials_slider_include_categories_callback',
					'vcex_suggest_testimonials_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_testimonials_slider_exclude_categories_callback',
					'vcex_suggest_testimonials_categories'
				);

				// Render autocomplete suggestions
				add_filter(
					'vc_autocomplete_vcex_testimonials_slider_include_categories_render',
					'vcex_render_testimonials_categories'
				);
				add_filter(
					'vc_autocomplete_vcex_testimonials_slider_exclude_categories_render',
					'vcex_render_testimonials_categories'
				);

			}

			if ( 'vc_edit_form' === $vc_action ) {

				add_filter( 'vc_edit_form_fields_attributes_vcex_testimonials_slider', array( $this, 'edit_form_fields' ) );

			}

		}

		/**
		 * Parse old shortcode attributes.
		 */
		public function edit_form_fields( $atts ) {
			if ( ! empty( $atts['animation'] ) && 'fade' == $atts['animation'] ) {
				$atts['animation'] = 'fade_slides';
			}
			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Testimonials Slider', 'total-theme-core' ),
				'description' => esc_html__( 'Recent testimonials slider', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-testimonials-slider vcex-icon ticon ticon-comments-o',
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
						'type' => 'dropdown',
						'heading' => esc_html__( 'Skin', 'total-theme-core' ),
						'param_name' => 'skin',
						'value' => array(
							esc_html__( 'Dark Text', 'total-theme-core' ) => 'dark',
							esc_html__( 'Light Text', 'total-theme-core' ) => 'light',
						),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Rating', 'total-theme-core' ),
						'param_name' => 'rating',
					),
					// Slider Settings
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Animation', 'total-theme-core' ),
						'param_name' => 'animation',
						'std' => 'fade_slides',
						'value' => array(
							esc_html__( 'Fade', 'total-theme-core' ) => 'fade_slides',
							esc_html__( 'Slide', 'total-theme-core' ) => 'slide',
						),
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Animation Speed', 'total-theme-core' ),
						'param_name' => 'animation_speed',
						'std' => 600,
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Auto Play', 'total-theme-core' ),
						'param_name' => 'slideshow',
						'description' => esc_html__( 'Enable automatic slideshow? Disabled in front-end composer to prevent page "jumping".', 'total-theme-core' ),
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Auto Play Delay', 'total-theme-core' ),
						'param_name' => 'slideshow_speed',
						'std' => 5000,
						'description' => esc_html__( 'Enter a value in milliseconds.', 'total-theme-core' ),
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
						'dependency' => array( 'element' => 'slideshow', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Auto Height', 'total-theme-core' ),
						'param_name' => 'auto_height',
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
						'description' => esc_html__( 'If disabled the slider height will be based on the tallest slide on page load. It is generally recommended to keep this enabled.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Auto Height Animation Speed', 'total-theme-core' ),
						'std' => '500',
						'param_name' => 'height_animation',
						'description' => esc_html__( 'You can enter "0.0" to disable the animation completely.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'auto_height', 'value' => 'true' ),
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Loop', 'total-theme-core' ),
						'param_name' => 'loop',
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Dot Navigation', 'total-theme-core' ),
						'param_name' => 'control_nav',
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Arrows', 'total-theme-core' ),
						'param_name' => 'direction_nav',
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'no',
						'heading' => esc_html__( 'Thumbnails', 'total-theme-core' ),
						'param_name' => 'control_thumbs',
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
						'vcex' => array( 'off' => 'no', 'on' => 'true' ),
					),
					array(
						'type' => 'vcex_image_crop_locations',
						'heading' => esc_html__( 'Image Crop Location', 'total-theme-core' ),
						'param_name' => 'control_thumbs_crop',
						'std' => 'center-center',
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
						'dependency' => array( 'element' => 'control_thumbs', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'control_thumbs_width',
						'std' => 50,
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
						'dependency' => array( 'element' => 'control_thumbs', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'control_thumbs_height',
						'std' => 50,
						'group' => esc_html__( 'Slider', 'total-theme-core' ),
						'dependency' => array( 'element' => 'control_thumbs', 'value' => 'true' ),
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
						'admin_label' => true,
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
						'heading' => esc_html__( 'Posts Count', 'total-theme-core' ),
						'param_name' => 'count',
						'value' => 3,
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Offset', 'total-theme-core' ),
						'param_name' => 'offset',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
						'description' => esc_html__( 'Number of post to displace or pass over. Warning: Setting the offset parameter overrides/ignores the paged parameter and breaks pagination. The offset parameter is ignored when posts per page is set to -1.', 'total-theme-core' ),
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
							'groups' => false,
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
							'groups' => false,
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
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order By', 'total-theme-core' ),
						'param_name' => 'orderby',
						'value' => vcex_orderby_array(),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
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
						'type' => 'vcex_ofswitch',
						'std' => 'yes',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'display_author_avatar',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'vcex' => array( 'on' => 'yes', 'off' => 'no', ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'img_bottom_margin',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'display_author_avatar', 'value' => 'yes' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'img_border_radius',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'display_author_avatar', 'value' => 'yes' ),
					),
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'display_author_avatar', 'value' => 'yes' ),
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
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					// Content
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'text_color',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'font_weight',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'no',
						'heading' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'param_name' => 'excerpt',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'vcex' => array( 'off' => 'no', 'on' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Excerpt Length', 'total-theme-core' ),
						'param_name' => 'excerpt_length',
						'value' => 20,
						'description' => esc_html__( 'Enter a custom excerpt length. Will trim the excerpt by this number of words. Enter "-1" to display the_content instead of the auto excerpt.', 'total-theme-core' ),
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Read More', 'total-theme-core' ),
						'param_name' => 'read_more',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Read More Text', 'total-theme-core' ),
						'param_name' => 'read_more_text',
						'group' => esc_html__( 'Content', 'total-theme-core' ),
						'value' => esc_html__( 'read more', 'total-theme-core' ),
						'dependency' => array( 'element' => 'read_more', 'value' => 'true' ),
					),
					// Meta
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'meta_color',
						'group' => esc_html__( 'Meta', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'meta_font_size',
						'group' => esc_html__( 'Meta', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'meta_font_weight',
						'group' => esc_html__( 'Meta', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'yes',
						'heading' => esc_html__( 'Author', 'total-theme-core' ),
						'param_name' => 'display_author_name',
						'group' => esc_html__( 'Meta', 'total-theme-core' ),
						'vcex' => array( 'on' => 'yes', 'off' => 'no' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'no',
						'heading' => esc_html__( 'Company', 'total-theme-core' ),
						'param_name' => 'display_author_company',
						'group' => esc_html__( 'Meta', 'total-theme-core' ),
						'vcex' => array( 'on' => 'yes', 'off' => 'no' ),
					),
					// CSS
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
new VCEX_Testimonials_Slider_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_testimonials_slider' ) ) {
	class WPBakeryShortCode_vcex_testimonials_slider extends WPBakeryShortCode {}
}
