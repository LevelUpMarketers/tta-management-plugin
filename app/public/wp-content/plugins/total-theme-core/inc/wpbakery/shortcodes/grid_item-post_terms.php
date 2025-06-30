<?php
/**
 * Visual Composer Grid Item Post Terms
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Post_Terms_Grid_Item_Shortcode' ) ) {

	class VCEX_Post_Terms_Grid_Item_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_gitem_post_terms';

		/**
		 * Main constructor.
		 */
		public function __construct() {

			add_shortcode( $this->shortcode, array( $this, 'add_shortcode' ) );

			if ( function_exists( 'vc_lean_map' ) ) {
				add_action( 'vc_after_mapping', array( $this, 'vc_after_mapping' ) );
			}

		}

		/**
		 * Create shortcode.
		 */
		public function add_shortcode( $atts ) {
		   return '{{ vcex_gitem_post_terms:' . http_build_query( (array) $atts ) . ' }}';
		}

		/**
		 * VC functions.
		 */
		public function vc_after_mapping() {

			add_filter( 'vc_grid_item_shortcodes', array( $this, 'map' ) );

			add_filter( 'vc_gitem_template_attribute_vcex_gitem_post_terms', array( $this, 'template_attribute' ), 10, 2 );

			$vc_action = vc_request_param( 'action' );

			if ( 'vc_get_autocomplete_suggestion' === $vc_action || 'vc_edit_form' === $vc_action ) {

				// Suggest tax
				add_filter(
					'vc_autocomplete_vcex_gitem_post_terms_taxonomy_callback',
					'vcex_suggest_taxonomies'
				);
				add_filter(
					'vc_autocomplete_vcex_gitem_post_terms_taxonomy_render',
					'vcex_render_taxonomies'
				);

				// Suggest terms
				add_filter(
					'vc_autocomplete_vcex_gitem_post_terms_exclude_terms_callback',
					'vcex_suggest_terms'
				);
				add_filter(
					'vc_autocomplete_vcex_gitem_post_terms_exclude_terms_render',
					'vcex_render_terms'
				);

			}

		}

		/**
		 * Add data to the vcex_gitem_post_excerpt shortcode.
		 */
		function template_attribute( $value, $data ) {

			extract( array_merge( array(
				'output' => '',
				'post'   => null,
				'data'   => '',
			), $data ) );

			$atts = array();

			parse_str( $data, $atts );

			$atts = vc_map_get_attributes( 'vcex_gitem_post_terms', $atts );

			ob_start();
			include( vcex_get_shortcode_template( $this->shortcode ) );
			return ob_get_clean();

		}

		/**
		 * Create shortcode.
		 */
		public function map( $shortcodes ) {
			$shortcodes['vcex_gitem_post_terms'] = array(
				'name'        => esc_html__( 'Post Terms', 'total-theme-core' ),
				'base'        => 'vcex_gitem_post_terms',
				'icon'        => 'vcex-gitem-post-terms vcex-icon fa fa-folder',
				'category'    => vcex_shortcodes_branding(),
				'description' => esc_html__( 'Display your post terms.', 'total-theme-core' ),
				'post_type'   => Vc_Grid_Item_Editor::postType(),
				'params'      => array(
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Post ID', 'total-theme-core' ),
						'param_name' => 'post_id',
						'description' => esc_html__( 'Leave empty to use current post or post in loop.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'admin_label' => true,
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'classes',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Taxonomy', 'total-theme-core' ),
						'param_name' => 'taxonomy',
						'admin_label' => true,
						'std' => '',
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
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order', 'total-theme-core' ),
						'param_name' => 'order',
						'value' => array(
							esc_html__( 'ASC', 'total-theme-core' ) => 'ASC',
							esc_html__( 'DESC', 'total-theme-core' ) => 'DESC',					),
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
					),
					// Link
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Link to Archive?', 'total-theme-core' ),
						'param_name' => 'archive_link',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Link Target', 'total-theme-core' ),
						'param_name' => 'target',
						'value' => array(
							esc_html__( 'Self', 'total-theme-core' ) => '',
							esc_html__( 'Blank', 'total-theme-core' ) => 'blank',
						),
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					// Design
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'button_style',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_color_style',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'button_align',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Size', 'total-theme-core' ),
						'param_name' => 'button_size',
						'std' => '',
						'value' => array(
							esc_html__( 'Default', 'total-theme-core' ) => '',
							esc_html__( 'Small', 'total-theme-core' ) => 'small',
							esc_html__( 'Medium', 'total-theme-core' ) => 'medium',
							esc_html__( 'Large', 'total-theme-core' ) => 'large',
						),
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'button_font_family',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'button_background',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'button_hover_background',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'button_color',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'button_hover_color',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'button_font_size',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'button_letter_spacing',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'button_text_transform',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'button_font_weight',
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'button_border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total-theme-core' ),
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'button_padding',
						'description' => esc_html__( 'Please use the following format: top right bottom left.', 'total-theme-core' ),
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'button_margin',
						'description' => esc_html__( 'Please use the following format: top right bottom left.', 'total-theme-core' ),
						'group' => esc_html__( 'Typopgraphy', 'total-theme-core' ),
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
				)
			);
			return $shortcodes;
		}

	}
}
new VCEX_Post_Terms_Grid_Item_Shortcode;