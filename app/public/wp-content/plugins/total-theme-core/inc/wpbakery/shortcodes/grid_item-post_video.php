<?php
/**
 * Visual Composer Post Video
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Grid_Item_Post_Video' ) ) {

	class VCEX_Grid_Item_Post_Video {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_gitem_post_video';

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
		  return '{{ vcex_post_video:' . http_build_query( (array) $atts ) . ' }}';
		}

		/**
		 * VC functions.
		 */
		public function vc_after_mapping() {
			add_filter( 'vc_grid_item_shortcodes', array( $this, 'map' ) );
			add_filter( 'vc_gitem_template_attribute_vcex_post_video', array( $this, 'template_attribute' ), 10, 2 );
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

			// Get and extract shortcode attributes
			$atts = array();
			parse_str( $data, $atts );

			$atts = vc_map_get_attributes( 'vcex_gitem_post_video', $atts );

			extract( $atts );

			// Get post id
			$post_id = ! empty( $post_id ) ? $post_id : $post->ID;

			// Get video
			if ( 'vc_grid_item' == get_post_type( $post->ID ) ) {
				$video = 'https://www.youtube.com/watch?v=2JyW4yAyTl0';
			} else {
				$video = wpex_get_post_video( $post_id );
			}

			// Parse video into HTML format
			$video = $video ? vcex_get_post_video_html( $video ) : '';

			// Display video
			if ( $video ) {

				// Custom CSS
				if ( $css ) {
					$css = ' ' . vc_shortcode_custom_css_class( $css );
				}

				// Generate output
				$output .= '<div class="vcex-gitem-post-video wpex-clr' . $css . '">';

					$output .= $video;

				$output .= '</div>';

				// Return output
				return $output;

			}

		}

		/**
		 * Create shortcode.
		 */
		public function map( $shortcodes ) {
			$shortcodes['vcex_gitem_post_video'] = array(
				'name'        => esc_html__( 'Post Video', 'total-theme-core' ),
				'base'        => 'vcex_gitem_post_video',
				'icon'        => 'vcex-gitem-post-video vcex-icon fa fa-film',
				'category'    => vcex_shortcodes_branding(),
				'description' => esc_html__( 'Featured post video.', 'total-theme-core' ),
				'post_type'   => Vc_Grid_Item_Editor::postType(),
				'params'      => array(
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Post ID', 'total-theme-core' ),
						'param_name' => 'post_id',
						'description' => esc_html__( 'Leave empty to use current post or post in loop.', 'total-theme-core' ),
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS', 'total-theme-core' ),
						'param_name' => 'css',
					),
				)
			);
			return $shortcodes;
		}

	}
}
new VCEX_Grid_Item_Post_Video;