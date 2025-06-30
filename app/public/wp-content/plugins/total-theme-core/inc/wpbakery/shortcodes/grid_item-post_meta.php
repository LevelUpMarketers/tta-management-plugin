<?php
/**
 * Visual Composer Post Meta
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Grid_Item_Post_Meta' ) ) {

	class VCEX_Grid_Item_Post_Meta {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_gitem_post_meta';

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
		  return '{{ vcex_post_meta:' . http_build_query( (array) $atts ) . ' }}';
		}

		/**
		 * VC functions.
		 */
		public function vc_after_mapping() {
			add_filter( 'vc_grid_item_shortcodes', array( $this, 'map' ) );
			add_filter( 'vc_gitem_template_attribute_vcex_post_meta', array( $this, 'template_attribute' ), 10, 2 );
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

			$atts = vc_map_get_attributes( 'vcex_gitem_post_meta', $atts );

			extract( $atts );

			// Get post id
			$post_id = ! empty( $post_id ) ? $post_id : $post->ID;

			// Return if no post ID
			if ( ! $post_id ) {
				return;
			}

			// Classes
			$classes = 'meta vcex-gitem-post-meta vcex-clr';
			if ( $color ) {
				$classes .= ' wpex-child-inherit-color';
			}
			if ( $css ) {
				$classes .= ' '. vc_shortcode_custom_css_class( $css );
			}

			// Inline CSS
			$inline_style = vcex_inline_style( array(
				'font_size' => $font_size,
				'color'     => $color,
			) );

			// Generate output
			$output .= '<ul class="'. esc_attr( $classes ) .'"'. $inline_style .'>';

				// Date
				if ( 'true' == $date ) {

					$output .= '<li class="meta-date"><span class="ticon ticon-clock-o"></span><time class="updated" datetime="'. esc_attr( get_the_date( 'Y-m-d' ) ) .'"'. wpex_get_schema_markup( 'publish_date' ) .'>'. get_the_date() .'</time></li>';

				}

				// Author
				if ( 'true' == $author ) {

					$output .= '<li class="meta-author"><span class="ticon ticon-user"></span><span class="vcard author"'. wpex_get_schema_markup( 'author_name' ).'><span class="fn">'. get_the_author_posts_link().'</span></span></li>';

				}

				// Comment
				if ( 'true' == $comments ) {

					$comment_number = get_comments_number();
					if ( $comment_number == 0 ) {
						$comment_output = esc_html__( '0 Comments', 'total-theme-core' );
					} elseif ( $comment_number > 1 ) {
						$comment_output = $comment_number .' '. esc_html__( 'Comments', 'total-theme-core' );
					} else {
						$comment_output = esc_html__( '1 Comment',  'total-theme-core' );
					}
					$output .= '<li class="meta-comments comment-scroll"><span class="ticon ticon-comment-o"></span>'. $comment_output .'</li>';

				}

			$output .= '</ul>';

			// Return output
			return $output;

		}

		/**
		 * Create shortcode.
		 */
		public function map( $shortcodes ) {
			$shortcodes['vcex_gitem_post_meta'] = array(
				'name'        => esc_html__( 'Post Meta', 'total-theme-core' ),
				'base'        => 'vcex_gitem_post_meta',
				'icon'        => 'vcex-gitem-post-meta vcex-icon fa fa-list-alt',
				'category'    => vcex_shortcodes_branding(),
				'description' => esc_html__( 'Author, date, comments...', 'total-theme-core' ),
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
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'color',
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Show Date?', 'total-theme-core' ),
						'param_name' => 'date',
						'std' => 'true',
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Show Author?', 'total-theme-core' ),
						'param_name' => 'author',
						'std' => 'true',
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Show Comments Count?', 'total-theme-core' ),
						'param_name' => 'comments',
						'std' => 'true',
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
new VCEX_Grid_Item_Post_Meta;