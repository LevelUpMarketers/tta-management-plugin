<?php
/**
 * Visual Composer Post Excerpt
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Grid_Item_Post_Excerpt' ) ) {

	class VCEX_Grid_Item_Post_Excerpt {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_gitem_post_excerpt';

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
		   return '{{ vcex_post_excerpt:' . http_build_query( (array) $atts ) . ' }}';
		}

		/**
		 * VC functions.
		 */
		public function vc_after_mapping() {
			add_filter( 'vc_grid_item_shortcodes', array( $this, 'map' ) );
			add_filter( 'vc_gitem_template_attribute_vcex_post_excerpt', array( $this, 'template_attribute' ), 10, 2 );
		}

		/**
		 * Add data to the vcex_gitem_post_excerpt shortcode.
		 */
		function template_attribute( $value, $data ) {

			// Extract data
			extract( array_merge( array(
				'output' => '',
				'post'   => null,
				'data'   => '',
			), $data ) );

			$atts = array();
			parse_str( $data, $atts );

			$atts = vc_map_get_attributes( 'vcex_gitem_post_excerpt', $atts );

			// Get video
			$excerpt = wpex_get_excerpt( array(
				'post_id' => $post->ID,
				'length'  => isset( $atts['length'] ) ? $atts['length'] : '30',
			) );

			if ( ! $excerpt && 'vc_grid_item' == get_post_type( $post->ID ) ) {
				$excerpt = esc_html__( 'Sample text for item preview.', 'total-theme-core' );
			}

			if ( ! $excerpt ) {
				return;
			}

			$attrs = array(
				'class' => 'vcex-gitem-post-excerpt wpex-clr',
			);

			$attrs['style'] = vcex_inline_style( array(
				'color'          => isset( $atts['color'] ) ? $atts['color'] : '',
				'font_family'    => isset( $atts['font_family'] ) ? $atts['font_family'] : '',
				'font_size'      => isset( $atts['font_size'] ) ? $atts['font_size'] : '',
				'letter_spacing' => isset( $atts['letter_spacing'] ) ? $atts['letter_spacing'] : '',
				'font_weight'    => isset( $atts['font_weight'] ) ? $atts['font_weight'] : '',
				'text_align'     => isset( $atts['text_align'] ) ? $atts['text_align'] : '',
				'line_height'    => isset( $atts['line_height'] ) ? $atts['line_height'] : '',
				'width'          => isset( $atts['width'] ) ? $atts['width'] : '',
				'font_style'     => ( isset( $atts['italic'] ) && 'true' == $atts['italic'] ) ? 'italic' : '',
			), false );

			if ( ! empty( $atts['css'] ) ) {
				$attrs['class'] .= ' '. vc_shortcode_custom_css_class( $atts['css'] );
			}

			$output .= '<div '. wpex_parse_attrs( $attrs ) .'>';

				$output .= wp_kses_post( $excerpt );

			$output .= '</div>';

			return $output;

		}

		/**
		 * Create shortcode.
		 */
		public function map( $shortcodes ) {
			$shortcodes['vcex_gitem_post_excerpt'] = array(
				'name'        => esc_html__( 'Post Excerpt', 'total-theme-core' ),
				'base'        => $this->shortcode,
				'icon'        => 'vcex-gitem-post-video vcex-icon fa fa-film',
				'category'    => vcex_shortcodes_branding(),
				'description' => esc_html__( 'Post Excerpt.', 'total-theme-core' ),
				'post_type'   => Vc_Grid_Item_Editor::postType(),
				'params'      => array(
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Length', 'total-theme-core' ),
						'param_name' => 'length',
						'value' => 30,
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
					),
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'font_family',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'description' => esc_html__( 'You can enter a px or em value. Example 13px or 1em.', 'total-theme-core' ),
						'target' => 'font-size',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'line_height',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'letter_spacing',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Italic', 'total-theme-core' ),
						'param_name' => 'italic',
						'value' => array( esc_html__( 'No', 'total-theme-core' ) => 'false', esc_html__( 'Yes', 'total-theme-core' ) => 'true' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'font_weight',
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Text Align', 'total-theme-core' ),
						'param_name' => 'text_align',
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'CSS', 'total-theme-core' ),
					),
				)
			);
			return $shortcodes;
		}

	}
}
new VCEX_Grid_Item_Post_Excerpt;