<?php
/**
 * Terms Carousel Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Terms_Carousel_Shortcode' ) ) {

	class VCEX_Terms_Carousel_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_terms_carousel';

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

				add_filter( 'vc_autocomplete_' . $this->shortcode . '_exclude_terms_callback', 'vcex_suggest_terms' );
				add_filter( 'vc_autocomplete_' . $this->shortcode . '_exclude_terms_render', 'vcex_render_terms' );

				add_filter( 'vc_autocomplete_' . $this->shortcode . '_taxonomy_callback', 'vcex_suggest_taxonomies' );
				add_filter( 'vc_autocomplete_' . $this->shortcode . '_taxonomy_render', 'vcex_render_taxonomies' );

				add_filter( 'vc_autocomplete_' . $this->shortcode . '_child_of_callback', 'vcex_suggest_terms' );
				add_filter( 'vc_autocomplete_' . $this->shortcode . '_child_of_render', 'vcex_render_terms' );

			}

			if ( 'vc_edit_form' === $vc_action ) {

				add_filter( 'vc_edit_form_fields_attributes_' . $this->shortcode, array( $this, 'edit_form_fields' ) );

			}

		}

		/**
		 * Edit form fields.
		 */
		public function edit_form_fields( $atts ) {

			if ( ! empty( $atts['title_typo'] ) ) {
				$atts = vcex_migrate_font_container_param( 'title_typo', 'title', $atts );
				unset( $atts['title_typo'] );
			}

			if ( ! empty( $atts['description_typo'] ) ) {
				$atts = vcex_migrate_font_container_param( 'description_typo', 'description', $atts );
				unset( $atts['description_typo'] );
			}

			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			$settings = array(
				'name' => esc_html__( 'Categories/Terms Carousel', 'total-theme-core' ),
				'description' => esc_html__( 'Carousel of taxonomy terms', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-terms-carousel vcex-icon ticon ticon-th-large',
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
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Taxonomy', 'total-theme-core' ),
						'param_name' => 'taxonomy',
						'admin_label' => true,
						'std' => 'category',
						'settings' => array(
							'multiple' => false,
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
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Display Terms Assigned to Current Post Only?', 'total-theme-core' ),
						'param_name' => 'get_post_terms',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Hide Empty Terms?', 'total-theme-core' ),
						'param_name' => 'hide_empty',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Parent Terms Only', 'total-theme-core' ),
						'param_name' => 'parent_terms',
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Child Of', 'total-theme-core' ),
						'param_name' => 'child_of',
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => true,
							'display_inline' => true,
							'delay' => 0,
							'auto_focus' => true,
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Exclude terms', 'total-theme-core' ),
						'param_name' => 'exclude_terms',
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => true,
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
							esc_html__( 'ASC', 'total-theme-core' ) => 'ASC',
							esc_html__( 'DESC', 'total-theme-core' ) => 'DESC',
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order By', 'total-theme-core' ),
						'param_name' => 'orderby',
						'value' => array(
							esc_html__( 'Name', 'total-theme-core' ) => 'name',
							esc_html__( 'Slug', 'total-theme-core' ) => 'slug',
							esc_html__( 'Term Group', 'total-theme-core' ) => 'term_group',
							esc_html__( 'Term ID', 'total-theme-core' ) => 'term_id',
							'ID' => 'id',
							esc_html__( 'Description', 'total-theme-core' ) => 'description',
						),
						'group' => esc_html__( 'Query', 'total-theme-core' ),
					),
					// Image
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'img',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'full',
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
						'description' => esc_html__( 'Enter a width in pixels.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'vcex_overlay',
						'heading' => esc_html__( 'Image Overlay', 'total-theme-core' ),
						'param_name' => 'overlay_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title_overlay', 'value' => 'false' ),
						'exclude_choices' => array(
							'thumb-swap',
							'thumb-swap-title',
							'category-tag',
							'category-tag-two',
							'title-category-hover',
							'title-category-visible',
							'title-date-hover',
							'title-date-visible',
							'categories-title-bottom-visible'
						),
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
						'type' => 'textfield',
						'heading' => esc_html__( 'Overlay Button Text', 'total-theme-core' ),
						'param_name' => 'overlay_button_text',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'dependency' => array( 'element' => 'overlay_style', 'value' => 'hover-button' ),
					),
					array(
						'type' => 'vcex_image_hovers',
						'heading' => esc_html__( 'Image Hover', 'total-theme-core' ),
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
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'title',
						'std' => 'true',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Overlay Title', 'total-theme-core' ),
						'param_name' => 'title_overlay',
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Term Count', 'total-theme-core' ),
						'param_name' => 'term_count',
						'std' => 'false',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Term Count on New Line', 'total-theme-core' ),
						'param_name' => 'term_count_block',
						'std' => 'true',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'term_count', 'value' => 'true' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Vertical Align', 'total-theme-core' ),
						'param_name' => 'title_overlay_align_items',
						'dependency' => array( 'element' => 'title_overlay', 'value' => 'true' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							esc_html__( 'Top', 'total-theme-core' ) => 'start',
							esc_html__( 'Center', 'total-theme-core' ) => 'center',
							esc_html__( 'Bottom', 'total-theme-core' ) => 'end',
						),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Overlay Background', 'total-theme-core' ),
						'param_name' => 'title_overlay_bg',
						'dependency' => array( 'element' => 'title_overlay', 'value' => 'true' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Overlay Background Opacity', 'total-theme-core' ),
						'param_name' => 'title_overlay_opacity',
						'dependency' => array( 'element' => 'title_overlay', 'value' => 'true' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'value' => vcex_opacity_choices(),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'title_font_family',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'title_font_weight',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'HTML Tag', 'total-theme-core' ),
						'param_name' => 'title_tag',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
						'choices' => 'html_tag',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'title_text_align',
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
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'title_line_height',
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
						'type'  => 'textfield',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'title_bottom_margin',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'true' ),
					),
					// Description
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'description',
						'group' => esc_html__( 'Description', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title_overlay', 'value' => 'false' ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'description_font_family',
						'group' => esc_html__( 'Description', 'total-theme-core' ),
						'dependency' => array( 'element' => 'description', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'description_text_align',
						'group' => esc_html__( 'Description', 'total-theme-core' ),
						'dependency' => array( 'element' => 'description', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'description_font_size',
						'group' => esc_html__( 'Description', 'total-theme-core' ),
						'dependency' => array( 'element' => 'description', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'description_line_height',
						'group' => esc_html__( 'Description', 'total-theme-core' ),
						'dependency' => array( 'element' => 'description', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'description_color',
						'group' => esc_html__( 'Description', 'total-theme-core' ),
						'dependency' => array( 'element' => 'description', 'value' => 'true' ),
					),
					// Readmore Button
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'button',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title_overlay', 'value' => 'false' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Alignment', 'total-theme-core' ),
						'param_name' => 'button_align',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Text', 'total-theme-core' ),
						'param_name' => 'button_text',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'button_style',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_style_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'button_size',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'button_border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total-theme-core' ),
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'button_padding',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'button_margin',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'button_background',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'button_hover_background',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'button_hover_color',
						'group' => esc_html__( 'Button', 'total-theme-core' ),
						'dependency' => array( 'element' => 'button', 'value' => 'true' ),
					),
					// Design Options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'Entry CSS', 'total-theme-core' ),
						'param_name' => 'entry_css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					// Deprecated
					array( 'param_name' => 'title_typo', 'type' => 'hidden' ),
					array( 'param_name' => 'description_typo', 'type' => 'hidden' ),
				),
			);

			$settings[ 'params' ] = array_merge( $settings[ 'params' ], vcex_vc_map_carousel_settings() );

			return $settings;

		}

	}
}
new VCEX_Terms_Carousel_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_terms_carousel' ) ) {
	class WPBakeryShortCode_vcex_terms_carousel extends WPBakeryShortCode {}
}
