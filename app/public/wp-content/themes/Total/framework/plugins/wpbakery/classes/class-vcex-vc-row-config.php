<?php
/**
 * WPBakery Row Configuration
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0.6
 *
 * @todo rename to WPBakery_Row_Config and add namespace
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_VC_Row_Config' ) ) {

	class VCEX_VC_Row_Config {

		/**
		 * Main constructor.
		 *
		 * @since 2.0.0
		 */
		public function __construct() {

			// Modify some default params when editing to move things around
			add_filter( 'wpex_vc_modify_params', array( 'VCEX_VC_Row_Config', 'modify_params' ) );

			// Add and remove params
			add_filter( 'wpex_vc_add_params', array( 'VCEX_VC_Row_Config', 'add_params' ) );

			// Edit fields when opening editor window
			add_filter( 'vc_edit_form_fields_attributes_vc_row', array( 'VCEX_VC_Row_Config', 'edit_form_fields' ) );

			// Add custom classes
			add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, array( 'VCEX_VC_Row_Config', 'shortcode_classes' ), 10, 3 );

			// Parse attributes
			add_filter( 'shortcode_atts_vc_row', 'vcex_parse_row_atts', 99 );

			// Add custom attributes to row
			add_filter( 'wpex_vc_row_wrap_atts', array( 'VCEX_VC_Row_Config', 'wrap_attributes' ), 10, 2 );

			// Top Hook
			add_filter( 'wpex_hook_vc_row_top', array( 'VCEX_VC_Row_Config', 'center_row_open' ), 10, 2 );

			// Bottom Hook
			add_filter( 'wpex_hook_vc_row_bottom', array( 'VCEX_VC_Row_Config', 'center_row_close' ), 10, 2 );
			add_filter( 'wpex_hook_vc_row_bottom', array( 'VCEX_VC_Row_Config', 'vc_row_added_elements' ), 50, 2 );
			add_filter( 'wpex_hook_vc_row_bottom', 'vcex_insert_top_shape_divider', 100, 2 );
			add_filter( 'wpex_hook_vc_row_bottom', 'vcex_insert_bottom_shape_divider', 100, 2 );

			// Custom output
			add_filter( 'vc_shortcode_output', array( 'VCEX_VC_Row_Config', 'custom_output' ), 10, 3 );

		}

		/**
		 * Used to update default parms.
		 *
		 * @since 3.0.0
		 */
		public static function modify_params( $params ) {

			$params['vc_row'] = array(

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

				'content_placement' => array(
					'weight' => 99,
				),

				// Change gap description
				'gap' => array(
					'heading' => esc_html__( 'Outer Column Gap', 'total' ),
					'description' =>  esc_html__( 'Alters the outer column gap to be used when adding backgrounds to your inner columns. To increase the default space between the columns without backgrounds use the "Inner Column Gap" setting instead.', 'total' ),
					'weight' => 40,
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
					'weight' => -1,
				),

				/* Change items to use vcex_ofswitch
				// @todo
				'full_height' => array(
					'type' => 'vcex_ofswitch',
					'vcex' => array( 'off' => '', 'on' => 'yes', ),
					'std' => '',
				),*/

			);

			if ( vcex_supports_advanced_parallax() ) {

				// Alter Parallax dropdown
				$params['vc_row']['parallax'] = array(
					'group' => esc_html__( 'Parallax', 'total' ),
					'value' => array(
						__( 'Advanced Parallax', 'total' ) => 'vcex_parallax',
					),
				);

				// Alter Parallax image location
				$params['vc_row']['parallax_image'] = array(
					'group' => esc_html__( 'Parallax', 'total' ),
				);

				// Alter Parallax speed location
				$params['vc_row']['parallax_speed_bg'] = array(
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
		 * Adds new params for the VC Rows.
		 *
		 * @since 2.0.0
		 */
		public static function add_params( $params ) {

			$add_params = array();

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

			$add_params[] = array(
				'type' => 'vcex_visibility',
				'heading' => esc_html__( 'Visibility', 'total' ),
				'param_name' => 'visibility',
				'weight' => 99,
				//'admin_label' => true,
			);

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Local Scroll ID', 'total' ),
				'param_name' => 'local_scroll_id',
				'description' => esc_html__( 'Unique identifier for local scrolling links.', 'total' ),
				'weight' => 99,
			);

			/*
			@todo maybe add?
			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Text Align', 'total' ),
				'param_name' => 'text_align',
				'value' => array(
					esc_html__( 'Default', 'total' ) => '',
					esc_html__( 'Left', 'total' ) => 'left',
					esc_html__( 'Center', 'total' ) => 'center',
					esc_html__( 'Right', 'total' ) => 'right',
				),
			);*/

			$add_params[] = array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Minimum Height', 'total' ),
				'description' => esc_html__( 'Adds a minimum height to the row so you can have a row without any content but still display it at a certain height. Such as a background with a video or image background but without any content.', 'total' ),
				'param_name' => 'min_height',
			);

			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Equal Column Heights', 'total' ),
				'param_name' => 'match_column_height',
				'value' => array(
					__( 'No', 'total' ) => '',
					__( 'Yes', 'total' ) => 'yes',
				),
			);

			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Typography Style', 'total' ),
				'param_name' => 'typography_style',
				'value' => array_flip( wpex_typography_styles() ),
			);

			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Max Width', 'total' ),
				'param_name' => 'max_width',
				'value' => array(
					__( 'None', 'total' ) => '',
					'10%' => '10',
					'20%' => '20',
					'30%' => '30',
					'40%' => '40',
					'50%' => '50',
					'60%' => '60',
					'70%' => '70',
					'80%' => '80',
				),
				'description' => esc_html__( 'The max width is done by setting a percentage margin on the left and right of your row. You can visit the Design Options tab to enter custom percentage margins yourself if you prefer', 'total' ),
				'dependency' => array( 'element' => 'full_width', 'is_empty' => true ),
			);

			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Inner Column Gap', 'total' ),
				'param_name' => 'column_spacing',
				'value' => array(
					__( 'Default', 'total' ) => '',
					'0px' => '0px',
					'1px' => '1',
					'5px' => '5',
					'10px' => '10',
					'20px' => '20',
					'30px' => '30',
					'40px' => '40',
					'50px' => '50',
					'60px' => '60',
				),
				'description' => esc_html__( 'Alter the inner column spacing.', 'total' ),
				'weight' => 40,
			);

			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Remove Bottom Column Margin', 'total' ),
				'param_name' => 'remove_bottom_col_margin',
				'std' => 'false',
				'description' => esc_html__( 'Enable to remove the default bottom margin on all the columns inside this row.', 'total' ),
			);

			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Float Columns Right', 'total' ),
				'param_name' => 'columns_right',
				'vcex' => array( 'off' => 'no', 'on' => 'yes', ),
				'std' => 'no',
				'description' => esc_html__( 'Most useful when you want to alternate content such as an image to the right and content to the left but display the image at the top on mobile.', 'total' ),
			);

			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Full-Width Columns On Tablets', 'total' ),
				'param_name' => 'tablet_fullwidth_cols',
				'vcex' => array(
					'off' => 'no',
					'on' => 'yes',
				),
				'std' => 'no',
				'description' => esc_html__( 'Enable to make all columns inside this row full-width for tablets', 'total' ) . ' (min-width: 768px) and (max-width: 959px)',
			);

			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Offset Overlay Header', 'total' ),
				'param_name' => 'offset_overlay_header',
				'vcex' => array( 'off' => 'no', 'on' => 'yes', ),
				'std' => 'no',
				'description' => esc_html__( 'Check this box to add an offset spacing before this row equal to the height of your header to prevent issues with the header Overlay when enabled.', 'total' ),
			);

			// Parallax
			if ( vcex_supports_advanced_parallax() ) {

				$add_params[] = array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Enable parallax for mobile devices', 'total' ),
					'param_name' => 'parallax_mobile',
					'vcex' => array( 'off' => 'no', 'on' => 'yes', ),
					'std' => 'no',
					'description' => esc_html__( 'Parallax effects would most probably cause slowdowns when your site is viewed in mobile devices. By default it is disabled.', 'total' ),
					'group' => esc_html__( 'Parallax', 'total' ),
					'dependency' => array(
						'element' => 'parallax',
						'value' => 'vcex_parallax',
					),
				);

				$add_params[] = array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Parallax Style', 'total' ),
					'param_name' => 'parallax_style',
					'group' => esc_html__( 'Parallax', 'total' ),
					'value' => array(
						__( 'Cover', 'total' ) => '',
						__( 'Fixed and Repeat', 'total' ) => 'fixed-repeat',
						__( 'Fixed and No-Repeat', 'total' ) => 'fixed-no-repeat',
					),
					'dependency' => array(
						'element' => 'parallax',
						'value' => 'vcex_parallax',
					),
				);

				$add_params[] = array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Parallax Direction', 'total' ),
					'param_name' => 'parallax_direction',
					'value' => array(
						__( 'Up', 'total' ) => '',
						__( 'Down', 'total' ) => 'down',
						__( 'Left', 'total' ) => 'left',
						__( 'Right', 'total' ) => 'right',
					),
					'group' => esc_html__( 'Parallax', 'total' ),
					'dependency' => array(
						'element' => 'parallax',
						'value' => 'vcex_parallax',
					),
				);

				$add_params[] = array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Parallax Speed', 'total' ),
					'param_name' => 'parallax_speed',
					'description' => esc_html__( 'The movement speed, value should be between 0.1 and 1.0. A lower number means slower scrolling speed. Be mindful of the background size and the dimensions of your background image when setting this value. Faster scrolling means that the image will move faster, make sure that your background image has enough width or height for the offset.', 'total' ),
					'group' => esc_html__( 'Parallax', 'total' ),
					'dependency' => array(
						'element' => 'parallax',
						'value' => 'vcex_parallax',
					),
				);

			}

			// Video
			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Video Background', 'total' ),
				'param_name' => 'video_bg',
				'value' => array(
					__( 'None', 'total' ) => '',
					__( 'Youtube', 'total' ) => 'youtube',
					__( 'Self Hosted', 'total' ) => 'self_hosted',
				),
				'group' => esc_html__( 'Video', 'total' ),
				'description' => esc_html__( 'Video backgrounds do not display on mobile because mobile devices do not allow the auto playing of videos it is recommended to apply a standard background image or color as a fallback for mobile devices.', 'total' ),
			);
			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Center Video?', 'total' ),
				'param_name' => 'video_bg_center',
				'std' => 'false',
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

			/*** Overlay ***/
			$add_params[] = array(
				'type' => 'dropdown',
				'heading' => esc_html__( 'Background Overlay', 'total' ),
				'param_name' => 'wpex_bg_overlay',
				'group' => esc_html__( 'Overlay', 'total' ),
				'value' => array(
					__( 'None', 'total' ) => '',
					__( 'Color', 'total' ) => 'color',
					__( 'Dark', 'total' ) => 'dark',
					__( 'Dotted', 'total' ) => 'dotted',
					__( 'Diagonal Lines', 'total' ) => 'dashed',
					__( 'Custom', 'total' ) => 'custom',
				),
			);

			$add_params[] = array(
				'type' => 'colorpicker',
				'heading' => esc_html__( 'Background Overlay Color', 'total' ),
				'param_name' => 'wpex_bg_overlay_color',
				'group' => esc_html__( 'Overlay', 'total' ),
				'dependency' => array( 'element' => 'wpex_bg_overlay', 'value' => array( 'color', 'dark', 'dotted', 'dashed', 'custom' ) ),
			);

			$add_params[] = array(
				'type' => 'attach_image',
				'heading' => esc_html__( 'Custom Overlay Pattern', 'total' ),
				'param_name' => 'wpex_bg_overlay_image',
				'group' => esc_html__( 'Overlay', 'total' ),
				'dependency' => array( 'element' => 'wpex_bg_overlay', 'value' => array( 'custom' ) ),
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

			/*** Design Options ***/
			$add_params[] = array(
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Use Featured Image as Background?', 'total' ),
				'param_name' => 'wpex_post_thumbnail_bg', //@todo rename to something else since it can also get category images.
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
					__( 'None', 'total' ) => '',
					__( 'Fixed', 'total' ) => 'fixed',
					__( 'Fixed top', 'total' ) => 'fixed-top',
					__( 'Fixed bottom', 'total' ) => 'fixed-bottom',
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
				'type' => 'vcex_ofswitch',
				'heading' => esc_html__( 'Center Row Content (deprecated)', 'total' ),
				'param_name' => 'center_row',
				'vcex' => array( 'off' => 'no', 'on' => 'yes', ),
				'std' => 'no',
				'dependency' => array( 'element' => 'full_width', 'is_empty' => true ),
				'description' => esc_html__( 'Use this option is used to center the inner content horizontally in your row when using the "Full Screen" layout for your post/page. This was added prior to the stretch row setting it is now best to use the no-sidebar layout and the stretch row function to achive full-screen sections. If enable certain settings such as the "Content position" may not work correctly.', 'total' ),
			);

			// Hidden fields = Deprecated params, these should be removed on save
			$deprecated_params = array(
				'id',
				'style',
				'bg_style',
				'no_margins',
				'video_bg_overlay',
				'match_column_height', // converted to equal_height in 4.0
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
					'padding_right',
				) );
			}

			foreach ( $deprecated_params as $param ) {

				$add_params[] = array(
					'type' => 'hidden',
					'param_name' => $param,
				);

			}

			// Apply filters for child theming
			// @deprecated in Total 4.0 since it's not really needed
			// $add_params = apply_filters( 'wpex_vc_row_custom_params', $add_params );

			// Add params to default rows
			$params['vc_row'] = $add_params;

			// Add params to inner rows
			$params['vc_row_inner'] = array(

				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Remove Bottom Column Margin', 'total' ),
					'param_name' => 'remove_bottom_col_margin',
					'std' => 'false',
					'description' => esc_html__( 'Enable to remove the default bottom margin on all the columns inside this row.', 'total' ),
				),

				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Float Columns Right', 'total' ),
					'param_name' => 'columns_right',
					'vcex' => array( 'off' => 'no', 'on' => 'yes', ),
					'std' => 'no',
					'description' => esc_html__( 'Most useful when you want to alternate content such as an image to the right and content to the left but display the image at the top on mobile.', 'total' ),
				)

			);

			// Return all custom params
			return $params;

		}

		/**
		 * Tweaks row attributes on edit.
		 *
		 * @since 2.0.2
		 */
		public static function edit_form_fields( $atts ) {

			// Parse ID
			if ( empty( $atts['el_id'] ) && ! empty( $atts['id'] ) ) {
				$atts['el_id'] = $atts['id'];
				unset( $atts['id'] );
			}

			// Convert match_column_height to equal_height
			if ( ! empty( $atts['match_column_height'] ) ) {
				$atts['equal_height'] = 'yes';
				unset( $atts['match_column_height'] );
			}

			// Parse $style into $typography_style
			if ( empty( $atts['typography_style'] ) && ! empty( $atts['style'] ) ) {
				if ( in_array( $atts['style'], array_flip( wpex_typography_styles() ) ) ) {
					$atts['typography_style'] = $atts['style'];
					unset( $atts['style'] );
				}
			}

			// Parse parallax
			if ( vcex_supports_advanced_parallax() ) {
				if ( ! empty( $atts['parallax'] ) ) {
					if ( in_array( $atts['parallax'], array( 'simple', 'advanced', 'true' ) ) ) {
						$atts['parallax'] = 'vcex_parallax';
					}
				} elseif ( empty( $atts['parallax'] ) && ! empty( $atts['bg_style'] ) ) {
					if ( 'parallax' == $atts['bg_style'] || 'parallax-advanced' == $atts['bg_style'] ) {
						$atts['parallax'] = 'vcex_parallax';
						unset( $atts['bg_style'] );
					}
				}
			}

			// Parse video background
			if ( ! empty( $atts['video_bg'] ) && 'yes' == $atts['video_bg'] ) {
				$atts['video_bg'] = 'self_hosted';
			}

			// Convert 'no-margins' to '0px' column_spacing
			if ( empty( $atts['column_spacing'] ) && ! empty( $atts['no_margins'] ) && 'true' == $atts['no_margins'] ) {
				$atts['column_spacing'] = '0px';
				unset( $atts['no_margins'] );
			}

			// Convert video overlay to just overlay
			if ( ! empty( $atts['video_bg_overlay'] ) && 'none' != $atts['video_bg_overlay'] ) {
				$atts['wpex_bg_overlay'] = $atts['video_bg_overlay'];
				unset( $atts['video_bg_overlay'] );
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
		 * Tweak shortcode classes
		 *
		 * @since 4.0
		 */
		public static function shortcode_classes( $class_string, $tag, $atts ) {

			if ( 'vc_row' != $tag && 'vc_row_inner' != $tag ) {
				return $class_string;
			}

			$add_classes = array();

			// Tweak some classes
			if ( strpos( $class_string, 'vc_row-has-fill' ) !== false ) {
				$class_string = str_replace( 'vc_row-has-fill', '', $class_string );
				$add_classes['wpex-vc_row-has-fill'] = 'wpex-vc_row-has-fill';
			}

			/* Text align (@todo maybe add?)
			if ( ! empty( $atts['text_align'] )
				&& in_array( $atts['text_align'], array( 'left', 'center', 'right' ) ) ) {
				$add_classes[] = 'text' . sanitize_html_class( $atts['text_align'] );
			}*/

			// Visibility
			if ( ! empty( $atts['visibility'] ) ) {
				$add_classes[] = $atts['visibility'];
			}

			// Typography
			if ( ! empty( $atts['typography_style'] ) ) {
				$add_classes[] = $atts['typography_style'];
			}

			// Full width
			if ( ! empty( $atts['full_width'] ) ) {
				$add_classes[] = 'wpex-vc-row-stretched';
			}
			if ( ! empty( $atts['full_width_boxed_layout'] ) ) {
				$add_classes[] = 'wpex-vc-row-boxed-layout-stretched';
				if ( isset( $atts['full_width_style'] ) && 'stretch_row_content_no_spaces' == $atts['full_width_style'] ) {
					$add_classes[] = 'vc_row-no-padding';
				}
			}

			// Max width
			if ( empty( $atts['full_width'] ) && ! empty( $atts['max_width'] ) ) {
				$add_classes[] = 'vc-has-max-width vc-max-width-'. $atts['max_width'];
			}

			// Centered row
			if ( isset( $atts['center_row'] ) && 'yes' == $atts['center_row'] ) {
				$add_classes[] = 'wpex-vc-row-centered';
			}

			// Column spacing
			if ( ! empty( $atts['column_spacing'] ) ) {
				$add_classes[] = 'wpex-vc-has-custom-column-spacing';
				$add_classes[] = 'wpex-vc-column-spacing-'. $atts['column_spacing'];
			}

			// Remove column bottom margin
			if ( isset( $atts['remove_bottom_col_margin'] ) && 'true' == $atts['remove_bottom_col_margin'] ) {
				$add_classes[] = 'no-bottom-margins';
			}

			// Tablet
			if ( isset( $atts['tablet_fullwidth_cols'] ) && 'yes' == $atts['tablet_fullwidth_cols'] ) {
				$add_classes[] = 'tablet-fullwidth-columns';
			}

			// Right hand columns
			if ( isset( $atts['columns_right'] ) && 'yes' == $atts['columns_right'] ) {
				$add_classes[] = 'wpex-cols-right';
			}

			// Parallax
			if ( ! empty( $atts['vcex_parallax'] ) ) {
				$add_classes[] = 'wpex-parallax-bg-wrap';
				$add_classes['wpex-vc_row-has-fill'] = 'wpex-vc_row-has-fill';
			}

			// Video bg
			if ( ! empty( $atts['wpex_self_hosted_video_bg'] ) ) {
				$add_classes[] = 'wpex-has-video-bg';
				$add_classes['wpex-vc_row-has-fill'] = 'wpex-vc_row-has-fill';
			}

			// Overlay BG
			if ( ! empty( $atts['wpex_bg_overlay'] ) && 'none' != $atts['wpex_bg_overlay'] ) {
				$add_classes[] = 'wpex-has-overlay';
			}

			// Add shape divider classes
			if ( ! empty( $atts['wpex_shape_divider_top'] ) ) {
				$add_classes[] = 'wpex-has-shape-divider-top';
			}
			if ( ! empty( $atts['wpex_shape_divider_bottom'] ) ) {
				$add_classes[] = 'wpex-has-shape-divider-bottom';
			}

			// BG class
			// @deprecated fallback
			if ( ! empty( $atts['bg_style_class'] ) ) {
				$add_classes[] = $atts['bg_style_class'];
			}

			// Fixed background
			if ( ! empty( $atts['wpex_fixed_bg'] ) ) {
				$add_classes[] = 'bg-' . sanitize_html_class( $atts['wpex_fixed_bg'] );
			}

			// Header overlay offset
			if ( isset( $atts['offset_overlay_header'] ) && 'yes' == $atts['offset_overlay_header'] ) {
				$add_classes[] = 'add-overlay-header-offset';
			}

			/*
			// Deprecated in 4.0
			if ( ! empty( $atts['full_width'] ) ) {
				$add_classes[] = 'wpex-vc-row-'. $atts['full_width'];
			}*/

			// Remove negative margins
			if ( empty( $atts['full_width'] ) && isset( $add_classes['wpex-vc_row-has-fill'] ) ) {
				$add_classes[] = 'wpex-vc-reset-negative-margin';
			}

			// Add custom classes
			if ( $add_classes ) {
				$add_classes = implode( ' ', array_filter( $add_classes, 'trim' ) );
				$class_string .= ' '. $add_classes;
			}

			// Return class string
			return $class_string;

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
			if ( isset( $atts['wpex_post_thumbnail_bg'] ) && 'true' === $atts['wpex_post_thumbnail_bg'] ) {

				$thumbnail_id = '';

				$post_id = wpex_get_dynamic_post_id();

				if ( $post_id ) {
					$thumbnail_id = get_post_thumbnail_id( $post_id );
				} else {
					$thumbnail_id = wpex_get_term_thumbnail_id();
				}

				if ( $thumbnail_id && 0 !== $thumbnail_id ) {

					$inline_style .= 'background-image:url(' . esc_url( wp_get_attachment_image_url( $thumbnail_id, 'full' ) ) . ')';

					if ( apply_filters( 'wpex_vc_row_post_thumbnail_bg_has_important', true ) ) {
						$inline_style .= '!important';
					}

					$inline_style .= ';';

				}

			}

			// Min Height
			if ( ! empty( $atts['min_height'] ) ) {

				// Sanitize min-height value
				$min_height = $atts['min_height'];
				if ( ! preg_match('/[A-z]/', $min_height ) && strpos( $min_height, '%' ) === false ) {
					$min_height = intval( $min_height ) . 'px';
				}

				// Add min-height inline style
				if ( $min_height ) {
					$inline_style .= 'min-height:' . $min_height . ';';
				}

			}

			// Background position
			if ( ! empty( $atts['wpex_bg_position'] ) ) {
				$inline_style .= 'background-position:' . $atts['wpex_bg_position'] . ' !important;';
			}

			// Background size
			if ( ! empty( $atts['wpex_bg_size'] ) ) {
				$inline_style .= 'background-size:' . $atts['wpex_bg_size'] . ' !important;';
			}

			// Inline css styles
			// Fallback For OLD Total Params
			if ( empty( $atts['css'] ) && function_exists( 'vcex_parse_deprecated_row_css' ) ) {
				$inline_style .= vcex_parse_deprecated_row_css( $atts, 'inline_css' );
			}

			// Add inline style to wrapper attributes
			if ( $inline_style ) {
				$wrapper_attributes[] = 'style="' . esc_attr( $inline_style ) . '"';
			}

			// Return attributes
			return $wrapper_attributes;

		}

		/**
		 * Open center row
		 *
		 * Priority: 10
		 *
		 * @since 4.0
		 * @todo deprecate
		 */
		public static function center_row_open( $content, $atts ) {

			if ( ! empty( $atts['center_row'] ) ) {
				$content .= '<div class="center-row container"><div class="center-row-inner wpex-clr">';
			}

			return $content;

		}

		/**
		 * Close center row
		 *
		 * Priority: 10
		 *
		 * @since 4.0
		 * @todo deprecate
		 */
		public static function center_row_close( $content, $atts ) {

			if ( ! empty( $atts['center_row'] ) ) {
				$content .= '</div></div>';
			}

			return $content;

		}

		/**
		 * Insert custom elements into rows as needed
		 *
		 * Priority: 1
		 *
		 * @since 4.0
		 */
		public static function vc_row_added_elements( $content, $atts ) {
			$content .= vcex_row_overlay( $atts );
			$content .= vcex_parallax_bg( $atts );
			$content .= vcex_row_video( $atts );
			return $content;

		}

		/**
		 * Custom HTML output
		 *
		 * @since 4.1
		 */
		public static function custom_output( $output, $obj, $atts ) {

			// Tweaks neeed for vc_row only
			if ( 'vc_row' != $obj->settings( 'base' ) ) {
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

	}

}
new VCEX_VC_Row_Config();