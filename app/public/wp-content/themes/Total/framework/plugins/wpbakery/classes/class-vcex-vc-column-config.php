<?php
/**
 * WPBakery Row Configuration
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 *
 * @todo rename to WPBakery_Column_Config and add namespace
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_VC_Column_Config' ) ) {

	class VCEX_VC_Column_Config {
		public $parse_old_css_settings;

		/**
		 * Main constructor.
		 *
		 * @since 2.0.0
		 */
		public function __construct() {

			// Modify some default params when editing to move things around
			add_filter( 'wpex_vc_modify_params', array( 'VCEX_VC_Column_Config', 'modify_params' ) );

			// Add new parameters
			add_filter( 'wpex_vc_add_params', array( 'VCEX_VC_Column_Config', 'add_params' ) );

			// Tweak fields on edit
			add_filter( 'vc_edit_form_fields_attributes_vc_column', array( 'VCEX_VC_Column_Config', 'edit_form_fields') );
			add_filter( 'vc_edit_form_fields_attributes_vc_column_inner', array( 'VCEX_VC_Column_Config', 'edit_form_fields') );

			// Alter shortcode classes
			add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, array( 'VCEX_VC_Column_Config', 'shortcode_classes' ), 9999, 3 );

			// Alter shortcode output
			add_filter( 'vc_shortcode_output', array( 'VCEX_VC_Column_Config', 'custom_output' ), 10, 3 );

		}

		/**
		 * Used to update default parms.
		 *
		 * @since 4.3
		 */
		public static function modify_params( $params ) {

			$params['vc_column'] = array(

				'el_id' => array(
					'weight' => 99
				),

				'el_class' => array(
					'weight' => 99,
				),

				'css_animation' => array(
					'weight' => 99,
				),

				// Move video bg checkbox
				'video_bg' => array(
					'group' => esc_html__( 'Video', 'total' ),
				),

				// Move video parallax setting
				'video_bg_parallax' => array(
					'group' => esc_html__( 'Video', 'total' ),
				),

				// Move youtube url
				'video_bg_url' => array(
					'group' => esc_html__( 'Video', 'total' ),
				),

				// Move video parallax speed
				'parallax_speed_video' => array(
					'group' => esc_html__( 'Video', 'total' ),
				),

				// Alter Parallax dropdown
				'parallax' => array(
					'group' => esc_html__( 'Parallax', 'total' ),
				),

				// Alter Parallax image location
				'parallax_image' => array(
					'group' => esc_html__( 'Parallax', 'total' ),
				),

				// Alter Parallax speed location
				'parallax_speed_bg' => array(
					'group' => esc_html__( 'Parallax', 'total' ),
					'dependency' => array(
						'element' => 'parallax',
						'value' => array( 'content-moving', 'content-moving-fade' ),
					),
				),

				// Move design options
				'width' => array(
					'weight' => -1,
				),

			);

			return $params;

		}

		/**
		 * Adds new params for the VC Rows.
		 *
		 * @since 2.0.0
		 */
		public static function add_params( $params ) {

			/*-----------------------------------------------------------------------------------*/
			/*  - Columns
			/*-----------------------------------------------------------------------------------*/

			// Array of params to add
			$column_params = array();

			$column_params[] = array(
				'type'       => 'vcex_visibility',
				'heading'    => esc_html__( 'Visibility', 'total' ),
				'param_name' => 'visibility',
				'std'        => '',
				'weight'     => 99,
			);

			$column_params[] = array(
				'type' => 'vcex_number',
				'heading' => esc_html__( 'CSS Animation Delay', 'total'),
				'param_name' => 'css_animation_delay',
				'value' => '',
				'min' => 0.1,
				'max' => 5,
				'step' => 0.1,
				'description' => esc_html__( 'Enter a value in seconds for a custom animation delay. By adding a different delay for different modules in a row you can have them load in one after the other.', 'total' )
			);

			$column_params[] = array(
				'type'       => 'dropdown',
				'heading'    => esc_html__( 'Typography Style', 'total' ),
				'param_name' => 'typography_style',
				'value'      => array_flip( wpex_typography_styles() ),
			);

			$column_params[] = array(
				'type'        => 'textfield',
				'heading'     => esc_html__( 'Minimum Height', 'total' ),
				'param_name'  => 'min_height',
				'description' => esc_html__( 'You can enter a minimum height for this row.', 'total' ),
			);

			/* Design Options */
			$column_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Fixed Background Style', 'total' ),
				'param_name' => 'wpex_fixed_bg',
				'group' => esc_html__( 'Design Options', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
				'value' => array(
					__( 'None', 'total' ) => '',
					__( 'Fixed', 'total' ) => 'fixed',
					__( 'Fixed top', 'total' ) => 'fixed-top',
					__( 'Fixed bottom', 'total' ) => 'fixed-bottom',
				),
			);

			$column_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Background Position', 'total' ),
				'param_name' => 'wpex_bg_position',
				'group' => esc_html__( 'Design Options', 'total' ),
				'description' => esc_html__( 'Enter your custom background position. Example: "center center"', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
			);

			$column_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Background Image Size', 'total' ),
				'param_name' => 'wpex_bg_size',
				'group' => esc_html__( 'Design Options', 'total' ),
				'description' => esc_html__( 'Specify the size of the background image. Example: 100% 100% ', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
			);

			$column_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Z-Index', 'total' ),
				'param_name' => 'wpex_zindex',
				'group' => esc_html__( 'Design Options', 'total' ),
				'description' => esc_html__( 'Note: Adding z-index values on rows containing negative top/bottom margins will allow you to overlay the rows, however, this can make it hard to access the page builder tools in the frontend editor and you may need to use the backend editor to modify the overlapped rows.', 'total' ),
				'weight' => -2,
				'dependency' => array( 'element' => 'parallax', 'is_empty' => true ),
			);

			// Hidden fields = Deprecated params, these should be removed on save
			$deprecated_column_params = array(
				'id',
				'style',
				'typo_style',
				'bg_style',
				'drop_shadow',
			);

			if ( function_exists( 'vcex_parse_deprecated_row_css' ) ) {
				$deprecated_column_params = array_merge( $deprecated_column_params, array(
					'bg_color',
					'bg_image',
					'border_style',
					'border_color',
					'border_width',
					'margin_top',
					'margin_bottom',
					'margin_left',
					'padding_top',
					'padding_bottom',
					'padding_left',
					'padding_right',
				) );
			}

			foreach ( $deprecated_column_params as $param ) {

				$column_params[] = array(
					'type'       => 'hidden',
					'param_name' => $param,
				);

			}

			$params['vc_column'] = $column_params;

			/*-----------------------------------------------------------------------------------*/
			/*  - Inner Columns
			/*-----------------------------------------------------------------------------------*/
			$inner_column_params = array();

			// Hidden fields = Deprecated params, these should be removed on save
			$deprecated_params = array(
				'id',
				'style',
				'bg_style',
				'typo_style',
			);

			if ( function_exists( 'vcex_parse_deprecated_row_css' ) ) {
				$deprecated_params = array_merge( $deprecated_params, array(
					'bg_color',
					'bg_image',
					'border_style',
					'border_color',
					'border_width',
					'margin_top',
					'margin_bottom',
					'margin_left',
					'padding_top',
					'padding_bottom',
					'padding_left',
					'padding_right'
				) );
			}

			foreach ( $deprecated_params as $param ) {

				$inner_column_params[] = array(
					'type'       => 'hidden',
					'param_name' => $param,
				);

			}

			$params['vc_column_inner'] = $inner_column_params;

			return $params;

		}

		/**
		 * Tweaks attributes on edit.
		 *
		 * @since 3.0.0
		 */
		public static function edit_form_fields( $atts ) {

			// Parse ID
			if ( empty( $atts['el_id'] ) && ! empty( $atts['id'] ) ) {
				$atts['el_id'] = $atts['id'];
				unset( $atts['id'] );
			}

			// Parse $atts['typo_style'] into $atts['typography_style']
			if ( empty( $atts['typography_style'] ) && ! empty( $atts['typo_style'] ) ) {
				if ( in_array( $atts['typo_style'], array_flip( wpex_typography_styles() ) ) ) {
					$atts['typography_style'] = $atts['typo_style'];
					unset( $atts['typo_style'] );
				}
			}

			// Remove old style param and add it to the classes field
			$style = isset( $atts['style'] ) ? $atts['style'] : '';
			if ( $style && ( 'bordered' == $style || 'boxed' == $style ) ) {
				if ( ! empty( $atts['el_class'] ) ) {
					$atts['el_class'] .= ' ' . $style . '-column';
				} else {
					$atts['el_class'] = $style . '-column';
				}
				unset( $atts['style'] );
			}

			// Parse css
			if ( empty( $atts['css'] ) && function_exists( 'vcex_parse_deprecated_row_css' ) ) {

				// Convert deprecated fields to css field
				$atts['css'] = vcex_parse_deprecated_row_css( $atts );

				// Unset deprecated vars
				unset( $atts['bg_image'] );
				unset( $atts['bg_color'] );

				unset( $atts['margin_top'] );
				unset( $atts['margin_bottom'] );
				unset( $atts['margin_right'] );
				unset( $atts['margin_left'] );

				unset( $atts['padding_top'] );
				unset( $atts['padding_bottom'] );
				unset( $atts['padding_right'] );
				unset( $atts['padding_left'] );

				unset( $atts['border_width'] );
				unset( $atts['border_style'] );
				unset( $atts['border_color'] );

			}

			// Return $atts
			return $atts;

		}

		/**
		 * Tweak shortcode classes.
		 *
		 * @since 4.0
		 */
		public static function shortcode_classes( $class_string, $tag, $atts ) {

			// Edits only for columns
			if ( 'vc_column' != $tag && 'vc_column_inner' != $tag ) {
				return $class_string;
			}

			// Move 'vc_column_container' to the front
			$class_string = str_replace( 'wpb_column', '', $class_string );
			$class_string = 'wpb_column ' . trim( $class_string );

			// Remove colorfill class which VC adds extra margins to
			$class_string = str_replace( 'vc_col-has-fill', 'wpex-vc_col-has-fill', $class_string );

			// Visibility
			if ( ! empty( $atts['visibility'] ) ) {
				$class_string .= ' ' . $atts['visibility'];
			}

			// Style => deprecated fallback
			if ( ! empty( $atts['style'] ) && 'default' != $atts['style'] ) {
				$class_string .= ' ' . $atts['style'] . '-column';
			}

			// Typography Style => deprecated fallback
			if ( ! empty( $atts['typo_style'] ) && empty( $atts['typography_style'] ) ) {
				$class_string .= ' ' . wpex_typography_style_class( $atts['typo_style'] );
			} elseif ( empty( $atts['typo_style'] ) && ! empty( $atts['typography_style'] ) ) {
				$class_string .= ' ' . wpex_typography_style_class( $atts['typography_style'] );
			}

			// Return class string
			return $class_string;

		}

		/**
		 * Customize the column HTML output.
		 *
		 * @since 4.0
		 */
		public static function custom_output( $output, $obj, $atts ) {

			// Only tweaks neeed for columns
			if ( 'vc_column' != $obj->settings( 'base' ) ) {
				return $output;
			}

			/* Outer Column Edits */

				$outer_style = '';

				// Z-Index
				if ( ! empty( $atts['wpex_zindex'] ) ) {
					$outer_style .= 'z-index:' . esc_attr( $atts['wpex_zindex'] ) . '!important;';
				}

				// Add animation speed to outer wrapper
				if ( ! empty( $atts['css_animation'] ) && ! empty( $atts['css_animation_delay'] ) ) {
					$outer_style .= 'animation-delay:' . floatval( $atts['css_animation_delay'] ) . 's;';

				}

				// Add outer inline style
				if ( $outer_style ) {
					$outer_style = 'style="' . esc_attr( $outer_style ) . '"';
					$output = str_replace( 'class="wpb_column', $outer_style . ' class="wpb_column', $output );
				}

			/* Inner Column Edits */

				// Fix empty space after vc_column-inner classname
				// @todo Remove when WPBakery fixes.
				$output = str_replace( 'class="vc_column-inner "', 'class="vc_column-inner"', $output );

				// Generate inline CSS
				$inner_style = '';

				// Min Height
				if ( ! empty( $atts['min_height'] ) ) {
					$min_height = $atts['min_height'];
					if ( ! preg_match('/[A-z]/', $min_height ) && strpos( $min_height, '%' ) === false ) {
						$min_height = intval( $min_height ) . 'px';
					}
					$inner_style .= 'min-height:' . $min_height . ';';
				}

				// Inline css styles => Fallback For OLD Total Params
				// @deprecated in 4.9
				if ( empty( $atts['css'] ) && function_exists( 'vcex_parse_deprecated_row_css' ) ) {
					$inner_style .= vcex_parse_deprecated_row_css( $atts, 'inline_css' );
				}

				// Background position
				if ( ! empty( $atts['wpex_bg_position'] ) ) {
					$inner_style .= 'background-position:' . $atts['wpex_bg_position'] . ' !important;';
				}

				// Background size
				if ( ! empty( $atts['wpex_bg_size'] ) ) {
					$inner_style .= 'background-size:' . $atts['wpex_bg_size'] . ' !important;';
				}

				// Add inner inline style
				if ( $inner_style ) {
					$inner_style = 'style="' . esc_attr( $inner_style ) . '"';
					$output = str_replace( 'class="vc_column-inner', $inner_style . ' class="vc_column-inner', $output );
				}

				// Add Fixed background classname
				if ( ! empty( $atts['wpex_fixed_bg'] ) ) {
					$output = str_replace( 'class="vc_column-inner', 'class="vc_column-inner bg-' . esc_attr( $atts['wpex_fixed_bg'] ), $output );
				}

			// Add output
			return $output;

		}

	}

}
new VCEX_VC_Column_Config();