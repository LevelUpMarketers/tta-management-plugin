<?php
/**
 * WPBakery Section Configuration
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0.6
 *
 * @todo rename to WPBakery_Section_Config and add namespace
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_VC_Section_Config' ) ) {

	class VCEX_VC_Section_Config {

		/**
		 * Main constructor
		 *
		 * @since 4.0
		 */
		public function __construct() {

			// Modify core params
			add_filter( 'wpex_vc_modify_params', array( 'VCEX_VC_Section_Config', 'modify_params' ) );

			// Add/remove params
			add_filter( 'wpex_vc_add_params', array( 'VCEX_VC_Section_Config', 'add_params' ) );

			// Edit fields when opening editor window
			add_filter( 'vc_edit_form_fields_attributes_vc_section', array( 'VCEX_VC_Section_Config', 'edit_form_fields' ) );

			// Parse attributes
			add_filter( 'shortcode_atts_vc_section', array( 'VCEX_VC_Section_Config', 'parse_attributes' ), 99 );

			// Add custom attributes to row
			add_filter( 'wpex_vc_section_wrap_atts', array( 'VCEX_VC_Section_Config', 'wrap_attributes' ), 10, 2 );

			// Filter classes
			add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, array( 'VCEX_VC_Section_Config', 'shortcode_classes' ), 10, 3 );

			// Alter output
			add_filter( 'vc_shortcode_output', array( 'VCEX_VC_Section_Config', 'custom_output' ), 10, 3 );

			// Hooks
			add_filter( 'wpex_hook_vc_section_bottom', array( 'VCEX_VC_Section_Config', 'hook_bottom' ), 50, 2 );
			add_filter( 'wpex_hook_vc_section_bottom', 'vcex_insert_top_shape_divider', 100, 2 );
			add_filter( 'wpex_hook_vc_section_bottom', 'vcex_insert_bottom_shape_divider', 100, 2 );

		}

		/**
		 * Modify core params
		 *
		 * @since 4.0
		 */
		public static function modify_params( $params ) {

			$params['vc_section'] = array(

				'el_id' => array(
					'weight' => 99
				),

				'el_class' => array(
					'weight' => 99,
				),

				'css_animation' => array(
					'weight' => 99,
				),

				'full_width' => array(
					'weight' => 99,
				),

				// Move video parallax setting
				'video_bg_parallax' => array(
					'group' => esc_html__( 'Video', 'total' ),
					'dependency' => array(
						'element' => 'video_bg',
						'value' => 'youtube',
					),
				),

				// Move youtube url
				'video_bg_url' => array(
					'group' => esc_html__( 'Video', 'total' ),
					'dependency' => array(
						'element' => 'video_bg',
						'value' => 'youtube',
					),
				),

				// Move video parallax speed
				'parallax_speed_video' => array(
					'group' => esc_html__( 'Video', 'total' ),
					'dependency' => array(
						'element' => 'video_bg',
						'value' => 'youtube',
					),
				),

				// Move design options
				'css' => array(
					'group' => esc_html__( 'Design Options', 'total' ),
					'weight' => -1,
				),

			);

			// Move parallax settings
			if ( vcex_supports_advanced_parallax() ) {

				$params['vc_section']['parallax'] = array(
					'group' => esc_html__( 'Parallax', 'total' ),
				);

				$params['vc_section']['parallax_image'] = array(
					'group' => esc_html__( 'Parallax', 'total' ),
				);

				$params['vc_section']['parallax_speed_bg'] = array(
					'group' => esc_html__( 'Parallax', 'total' ),
					'dependency' => array(
						'element' => 'parallax',
						'value' => array( 'content-moving', 'content-moving-fade' ),
					),
				);

			}

			return $params;

		}

		/**
		 * Adds new params for the VC Rows
		 *
		 * @since 4.0
		 */
		public static function add_params( $params ) {

			// Access
			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Access', 'total' ),
				'param_name' => 'vcex_user_access',
				'weight' => 99,
				'value' => array(
					esc_html__( 'All', 'total' ) => '',
					esc_html__( 'Logged in', 'total' ) => 'logged_in',
					esc_html__( 'Logged out', 'total' ) => 'logged_out',
					esc_html__( 'First paginated page only', 'total' ) => 'not_paged',
					esc_html__( 'Custom', 'total' ) => 'custom',
				)
			);

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Custom Access', 'total' ),
				'param_name' => 'vcex_user_access_callback',
				'description' => esc_html__( 'Enter your callback function name here.', 'total' ),
				'weight' => 99,
				'dependency' => array( 'element' => 'vcex_user_access', 'value' => 'custom' ),
			);

			// Video
			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Video Background', 'total' ),
				'param_name' => 'video_bg',
				'std' => '',
				'value' => array(
					esc_html__( 'None', 'total' ) => '',
					esc_html__( 'Youtube', 'total' ) => 'youtube',
					esc_html__( 'Self Hosted', 'total' ) => 'self_hosted',
				),
				'group' => esc_html__( 'Video', 'total' ),
				'description' => esc_html__( 'Video backgrounds do not display on mobile because mobile devices do not allow the auto playing of videos it is recommended to apply a standard background image or color as a fallback for mobile devices.', 'total' ),
			);
			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Center Video?', 'total' ),
				'param_name' => 'video_bg_center',
				'std' => 'false','std' => 'false',
				'group' => esc_html__( 'Video', 'total' ),
				'dependency' => array( 'element' => 'video_bg', 'value' => 'self_hosted' ),
			);
			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Video URL: MP4 URL', 'total' ),
				'param_name' => 'video_bg_mp4',
				'dependency' => array( 'element' => 'video_bg', 'value' => 'self_hosted' ),
				'group' => esc_html__( 'Video', 'total' ),
			);
			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Video URL: WEBM URL', 'total' ),
				'param_name' => 'video_bg_webm',
				'dependency' => array( 'element' => 'video_bg', 'value' => 'self_hosted' ),
				'group' => esc_html__( 'Video', 'total' ),
				'description' => esc_html__( '(Optional)', 'total' ),
			);
			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Video URL: OGV URL', 'total' ),
				'param_name' => 'video_bg_ogv',
				'dependency' => array( 'element' => 'video_bg', 'value' => 'self_hosted' ),
				'group' => esc_html__( 'Video', 'total' ),
				'description' => esc_html__( '(Optional)', 'total' ),
			);

			$add_params['visibility'] = array(
				'type' => 'vcex_visibility',
				'heading' => esc_html__( 'Visibility', 'total' ),
				'param_name' => 'visibility',
				'weight' => 99,
				//'admin_label' => true,
			);

			// Overlay
			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Background Overlay', 'total' ),
				'param_name' => 'wpex_bg_overlay',
				'group' => esc_html__( 'Overlay', 'total' ),
				'value' => array(
					esc_html__( 'None', 'total' ) => '',
					esc_html__( 'Color', 'total' ) => 'color',
					esc_html__( 'Dark', 'total' ) => 'dark',
					esc_html__( 'Dotted', 'total' ) => 'dotted',
					esc_html__( 'Diagonal Lines', 'total' ) => 'dashed',
					esc_html__( 'Custom', 'total' ) => 'custom',
				),
			);

			$add_params[] = array(
				'type' => 'attach_image',
				'heading' => esc_html__( 'Custom Overlay Pattern', 'total' ),
				'param_name' => 'wpex_bg_overlay_image',
				'group' => esc_html__( 'Overlay', 'total' ),
				'dependency' => array( 'element' => 'wpex_bg_overlay', 'value' => array( 'custom' ) ),
			);

			$add_params[] = array(
				'type' => 'colorpicker',
				'heading' => esc_html__( 'Background Overlay Color', 'total' ),
				'param_name' => 'wpex_bg_overlay_color',
				'group' => esc_html__( 'Overlay', 'total' ),
				'dependency' => array( 'element' => 'wpex_bg_overlay', 'value' => array( 'color', 'dark', 'dotted', 'dashed', 'custom' ) ),
			);

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Background Overlay Opacity', 'total' ),
				'param_name' => 'wpex_bg_overlay_opacity',
				'dependency' => array( 'element' => 'wpex_bg_overlay', 'value' => array( 'color', 'dark', 'dotted', 'dashed', 'custom' ) ),
				'group' => esc_html__( 'Overlay', 'total' ),
				'description' => esc_html__( 'Default', 'total' ) . ': 0.65',
			);

			/*** Shape Dividers ***/
			if ( vcex_supports_shape_dividers() ) {

				$add_params[] = array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Divider Type', 'total' ),
					'param_name' => 'wpex_shape_divider_top',
					'group' => esc_html__( 'Top Divider', 'total' ),
					'value' => array_flip( wpex_get_shape_divider_types() ),
				);

				$add_params[] = array(
					'type' => 'vcex_visibility',
					'heading' => esc_html__( 'Visibility', 'total' ),
					'group' => esc_html__( 'Top Divider', 'total' ),
					'param_name' => 'wpex_shape_divider_top_visibility',
				);

				$add_params[] = array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Invert', 'total' ),
					'param_name' => 'wpex_shape_divider_top_invert',
					'std' => 'false',
					'group' => esc_html__( 'Top Divider', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_top',
						'value' => array( 'triangle','triangle_asymmetrical', 'arrow', 'clouds', 'curve', 'waves' )
					),
				);

				$add_params[] = array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Flip', 'total' ),
					'param_name' => 'wpex_shape_divider_top_flip',
					'std' => 'false',
					'group' => esc_html__( 'Top Divider', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_top',
						'value' => array( 'tilt', 'triangle_asymmetrical', 'clouds', 'waves' )
					),
				);

				$add_params[] = array(
					'type' => 'colorpicker',
					'heading' => esc_html__( 'Divider Color', 'total' ),
					'param_name' => 'wpex_shape_divider_top_color',
					'group' => esc_html__( 'Top Divider', 'total' ),
					'description' => esc_html__( 'Your color should equal the background color of the previous or next section.', 'total' ),
					'dependency' => array( 'element' => 'wpex_shape_divider_top', 'not_empty' => true ),
				);

				$add_params[] = array(
					'type' => 'vcex_number',
					'heading' => esc_html__( 'Divider Height', 'total' ),
					'param_name' => 'wpex_shape_divider_top_height',
					'group' => esc_html__( 'Top Divider', 'total' ),
					'description' => esc_html__( 'Enter your custom height in pixels.', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_top',
						'value' => array( 'tilt', 'triangle', 'triangle_asymmetrical', 'arrow', 'clouds', 'curve', 'waves' )
					),
					'min'  => 1,
					'step' => 1,
					'max'  => 500,
				);

				$add_params[] = array(
					'type' => 'vcex_number',
					'heading' => esc_html__( 'Divider Width', 'total' ),
					'param_name' => 'wpex_shape_divider_top_width',
					'group' => esc_html__( 'Top Divider', 'total' ),
					'description' => esc_html__( 'Enter your custom percentage based width. For example to make your shape twice as big enter 200.', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_top',
						'value' => array( 'triangle', 'triangle_asymmetrical', 'arrow', 'curve', 'waves' )
					),
					'min'  => 100,
					'step' => 1,
					'max'  => 300,
				);

				$add_params[] = array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Divider Type', 'total' ),
					'param_name' => 'wpex_shape_divider_bottom',
					'group' => esc_html__( 'Bottom Divider', 'total' ),
					'value' => array_flip( wpex_get_shape_divider_types() ),
				);

				$add_params[] = array(
					'type' => 'vcex_visibility',
					'heading' => esc_html__( 'Visibility', 'total' ),
					'group' => esc_html__( 'Bottom Divider', 'total' ),
					'param_name' => 'wpex_shape_divider_bottom_visibility',
				);

				$add_params[] = array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Invert', 'total' ),
					'param_name' => 'wpex_shape_divider_bottom_invert',
					'std' => 'false',
					'group' => esc_html__( 'Bottom Divider', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_bottom',
						'value' => array( 'triangle', 'triangle_asymmetrical', 'arrow', 'clouds', 'curve', 'waves' )
					),
				);

				$add_params[] = array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Flip', 'total' ),
					'param_name' => 'wpex_shape_divider_bottom_flip',
					'std' => 'false',
					'group' => esc_html__( 'Bottom Divider', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_bottom',
						'value' => array( 'tilt', 'triangle_asymmetrical', 'clouds', 'waves' )
					),
				);

				$add_params[] = array(
					'type' => 'colorpicker',
					'heading' => esc_html__( 'Divider Color', 'total' ),
					'param_name' => 'wpex_shape_divider_bottom_color',
					'group' => esc_html__( 'Bottom Divider', 'total' ),
					'dependency' => array( 'element' => 'wpex_shape_divider_bottom', 'not_empty' => true ),
				);

				$add_params[] = array(
					'type' => 'vcex_number',
					'heading' => esc_html__( 'Divider Height', 'total' ),
					'param_name' => 'wpex_shape_divider_bottom_height',
					'group' => esc_html__( 'Bottom Divider', 'total' ),
					'description' => esc_html__( 'Enter your custom height in pixels.', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_bottom',
						'value' => array( 'tilt', 'triangle', 'triangle_asymmetrical', 'arrow', 'clouds', 'curve', 'waves' )
					),
					'min'  => 1,
					'step' => 1,
					'max'  => 500,
				);

				$add_params[] = array(
					'type' => 'vcex_number',
					'heading' => esc_html__( 'Divider Width', 'total' ),
					'param_name' => 'wpex_shape_divider_bottom_width',
					'group' => esc_html__( 'Bottom Divider', 'total' ),
					'description' => esc_html__( 'Enter your custom percentage based width. For example to make your shape twice as big enter 200.', 'total' ),
					'dependency' => array(
						'element' => 'wpex_shape_divider_bottom',
						'value' => array( 'triangle','triangle_asymmetrical', 'arrow', 'curve', 'waves' )
					),
					'min'  => 100,
					'step' => 1,
					'max'  => 300,
				);

			}

			/* Design options */
			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Use Featured Image as Background?', 'total' ),
				'param_name' => 'wpex_post_thumbnail_bg',
				'std' => 'false',
				'description' => esc_html__( 'Enable this option to use the current post featured image as the row background.', 'total' ),
				'group' => esc_html__( 'Design Options', 'total' ),
				'weight' => -2,
			);

			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Fixed Background Style', 'total' ),
				'param_name' => 'wpex_fixed_bg',
				'group' => esc_html__( 'Design Options', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
				'value' => array(
					esc_html__( 'None', 'total' ) => '',
					esc_html__( 'Fixed', 'total' ) => 'fixed',
					esc_html__( 'Fixed top', 'total' ) => 'fixed-top',
					esc_html__( 'Fixed bottom', 'total' ) => 'fixed-bottom',
				),
			);

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Background Position', 'total' ),
				'param_name' => 'wpex_bg_position',
				'group' => esc_html__( 'Design Options', 'total' ),
				'description' => esc_html__( 'Enter your custom background position. Example: "center center"', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
			);

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Background Image Size', 'total' ),
				'param_name' => 'wpex_bg_size',
				'group' => esc_html__( 'Design Options', 'total' ),
				'description' => esc_html__( 'Specify the size of the background image. Example: 100% 100% ', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
			);

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Z-Index', 'total' ),
				'param_name' => 'wpex_zindex',
				'group' => esc_html__( 'Design Options', 'total' ),
				'description' => esc_html__( 'Note: Adding z-index values on rows containing negative top/bottom margins will allow you to overlay the rows, however, this can make it hard to access the page builder tools in the frontend editor and you may need to use the backend editor to modify the overlapped rows.', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
			);

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Local Scroll ID', 'total' ),
				'param_name' => 'local_scroll_id',
				'description' => esc_html__( 'Unique identifier for local scrolling links.', 'total' ),
				'weight' => 99,
			);

			$params['vc_section'] = $add_params;

			return $params;

		}

		/**
		 * Tweaks row attributes on edit
		 *
		 * @since 4.3
		 */
		public static function edit_form_fields( $atts ) {

			// Parse video background
			if ( ! empty( $atts['video_bg'] ) && 'yes' == $atts['video_bg'] ) {
				$atts['video_bg'] = 'youtube';
			}

			// Return attributes
			return $atts;

		}

		/**
		 * Parse VC section attributes on front-end
		 *
		 * @since 4.0
		 */
		public static function parse_attributes( $atts ) {

			if ( ! empty( $atts['full_width'] ) && 'boxed' == wpex_site_layout() ) {
				$atts['full_width'] = '';
				$atts['full_width_boxed_layout'] = 'true';
			}

			if ( ! empty( $atts['video_bg'] ) && 'self_hosted' == $atts['video_bg'] ) {
				$atts['video_bg'] = false;
				$atts['wpex_self_hosted_video_bg'] = true;
			}

			return $atts;

		}

		/**
		 * Add custom attributes to the row wrapper
		 *
		 * @since 4.0
		 */
		public static function wrap_attributes( $wrapper_attributes, $atts ) {
			$inline_style = '';

			// Local scroll ID
			if ( ! empty( $atts['local_scroll_id'] ) ) {
				$wrapper_attributes[] = 'data-ls_id="#' . esc_attr( $atts['local_scroll_id'] ) . '"';
			}

			// Z-Index
			if ( ! empty( $atts['wpex_zindex'] ) ) {
				$inline_style .= 'z-index:' . esc_attr( $atts['wpex_zindex'] ) . '!important;';
			}

			// Custom background
			if ( isset( $atts['wpex_post_thumbnail_bg'] )
				&& 'true' == $atts['wpex_post_thumbnail_bg']
				&& has_post_thumbnail()
			) {
				$inline_style .= 'background-image:url(' . esc_url( get_the_post_thumbnail_url() ) . ')!important;';
			}

			// Background position
			if ( ! empty( $atts['wpex_bg_position'] ) ) {
				$inline_style .= 'background-position:'. $atts['wpex_bg_position'] .' !important;';
			}

			// Background size
			if ( ! empty( $atts['wpex_bg_size'] ) ) {
				$inline_style .= 'background-size:'. $atts['wpex_bg_size'] .' !important;';
			}

			// Add inline style to wrapper attributes
			if ( $inline_style ) {
				$wrapper_attributes[] = 'style="'. $inline_style .'"';
			}

			// Return attributes
			return $wrapper_attributes;

		}

		/**
		 * Tweak shortcode classes
		 *
		 * @since 4.0
		 */
		public static function shortcode_classes( $class_string, $tag, $atts ) {

			// Edits only for columns
			if ( 'vc_section' != $tag ) {
				return $class_string;
			}

			$add_classes = array();

			// Tweak some classes
			if ( strpos( $class_string, 'vc_section-has-fill' ) !== false ) {
				$class_string = str_replace( 'vc_section-has-fill', '', $class_string );
				$add_classes['wpex-vc_section-has-fill'] = 'wpex-vc_section-has-fill';
			}

			// Visibility
			if ( ! empty( $atts['visibility'] ) ) {
				$add_classes[] = $atts['visibility'];
			}

			// Full width
			if ( ! empty( $atts['full_width'] ) ) {
				$add_classes[] = 'wpex-vc-row-stretched';
			}

			if ( ! empty( $atts['full_width_boxed_layout'] ) ) {
				$add_classes[] = 'wpex-vc-section-boxed-layout-stretched';
			}

			// Video bg
			if ( ! empty( $atts['wpex_self_hosted_video_bg'] ) ) {
				$add_classes[] = 'wpex-has-video-bg';
				$add_classes['wpex-vc_section-has-fill'] = 'wpex-vc_section-has-fill';
			}

			// Overlay
			if ( ! empty( $atts['wpex_bg_overlay'] ) && 'none' != $atts['wpex_bg_overlay'] ) {
				$add_classes[] = 'wpex-has-overlay';
			}

			// Remove negative margins
			if ( empty( $atts['full_width'] ) && isset( $add_classes['wpex-vc_section-has-fill'] ) ) {
				$add_classes[] = 'wpex-vc-reset-negative-margin';
			}

			// Fixed background
			if ( ! empty( $atts['wpex_fixed_bg'] ) ) {
				$add_classes[] = 'bg-' . esc_attr( $atts['wpex_fixed_bg'] );
			}

			// Add shape divider classes
			if ( ! empty( $atts['wpex_shape_divider_top'] ) ) {
				$add_classes[] = 'wpex-has-shape-divider-top';
			}

			if ( ! empty( $atts['wpex_shape_divider_bottom'] ) ) {
				$add_classes[] = 'wpex-has-shape-divider-bottom';
			}

			// Add the classes
			if ( $add_classes ) {
				$add_classes = implode( ' ', array_filter( $add_classes, 'trim' ) );
				$class_string .= ' ' . $add_classes;
			}

			// Return class string
			return $class_string;

		}

		/**
		 * Custom HTML output
		 *
		 * @since 4.1
		 */
		public static function custom_output( $output, $obj, $atts ) {

			// Tweaks neeed for vc_row only
			if ( 'vc_section' != $obj->settings( 'base' ) ) {
				return $output;
			}

			// Check user settings
			if ( isset( $atts['vcex_user_access'] )
				&& ! is_admin()
				&& ! vc_is_inline()
			) {
				$callback = ( 'custom' == $atts['vcex_user_access'] && isset( $atts['vcex_user_access_callback'] ) ) ? $atts['vcex_user_access_callback'] : '';
				if ( ! wpex_user_can_access( $atts['vcex_user_access'], $callback ) ) {
					return;
				}
			}

			// Return output
			return $output;

		}

		/**
		 * Insert custom elements to the Section content at the bottom
		 *
		 * Priority: 1
		 *
		 * @since 4.0
		 */
		public static function hook_bottom( $content, $atts ) {
			$content .= vcex_row_overlay( $atts );
			//$content .= vcex_parallax_bg( $atts ); // advanced parallax not added to section yet
			$content .= vcex_row_video( $atts );
			return $content;

		}

	}

}
new VCEX_VC_Section_Config();