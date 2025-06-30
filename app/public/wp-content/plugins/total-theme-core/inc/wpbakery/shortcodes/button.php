<?php
/**
 * Button Shortcodes
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Button_Shortcode' ) ) {

	class VCEX_Button_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_button';

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

			if ( 'vc_edit_form' === vc_post_param( 'action' ) ) {
				add_filter( 'vc_edit_form_fields_attributes_' . $this->shortcode, array( $this, 'edit_form_fields' ) );
			}

		}

		/**
		 * Update fields on edit.
		 */
		public function edit_form_fields( $atts ) {

			if ( ! empty( $atts['lightbox_image'] ) ) {
				$atts['image_attachment'] = $atts['lightbox_image'];
				unset( $atts['lightbox_image'] );
			}

			if ( isset( $atts['lightbox'] ) && 'true' == $atts['lightbox'] ) {
				$atts['onclick'] = 'lightbox';
			}

			$atts = vcex_parse_icon_param( $atts, 'icon_left' );
			$atts = vcex_parse_icon_param( $atts, 'icon_right' );

			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Theme Button', 'total-theme-core' ),
				'description' => esc_html__( 'Customizable button', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-total-button vcex-icon ticon ticon-external-link-square',
				'params' => array(
					// General
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Text Source', 'total-theme-core' ),
						'param_name' => 'text_source',
						'value' => array(
							esc_html__( 'Custom Text', 'total-theme-core' ) => 'custom_text',
							esc_html__( 'Custom Field', 'total-theme-core' ) => 'custom_field',
							esc_html__( 'Callback Function', 'total-theme-core' ) => 'callback_function',
						),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Text', 'total-theme-core' ),
						'param_name' => 'content',
						'admin_label' => true,
						'std' => 'Button Text',
						'dependency' => array( 'element' => 'text_source', 'value' => 'custom_text' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field ID', 'total-theme-core' ),
						'param_name' => 'text_custom_field',
						'dependency' => array( 'element' => 'text_source', 'value' => 'custom_field' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Callback Function', 'total-theme-core' ),
						'param_name' => 'text_callback_function',
						'dependency' => array( 'element' => 'text_source', 'value' => 'callback_function' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
						'admin_label' => true,
					),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
						'param_name' => 'unique_id',
						'description' => sprintf( esc_html__( 'Enter element ID (Note: make sure it is unique and valid according to %sw3c specification%s).', 'total-theme-core' ), '<a href="https://www.w3schools.com/tags/att_global_id.asp" target="_blank" rel="noopener noreferrer">', '</a>' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'classes',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					// Link
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'On click action', 'total-theme-core' ),
						'param_name' => 'onclick',
						'value' => array(
							esc_html__( 'Open custom link', 'total-theme-core' ) => 'custom_link',
							esc_html__( 'Open internal page', 'total-theme-core' ) => 'internal_link',
							esc_html__( 'Open custom field link', 'total-theme-core' ) => 'custom_field',
							esc_html__( 'Open callback function link', 'total-theme-core' ) => 'callback_function',
							esc_html__( 'Open image or image gallery', 'total-theme-core' ) => 'image',
							esc_html__( 'Open custom lightbox', 'total-theme-core' ) => 'lightbox',
						),
						'default' => 'custom_link',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'URL', 'total-theme-core' ),
						'param_name' => 'url',
						'value' => '#',
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'custom_link', 'lightbox' ) ),
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'Internal Link', 'total-theme-core' ),
						'param_name' => 'internal_link',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
						'description' => esc_html__( 'This setting is used only if you want to link to an internal page to make it easier to find and select it. Any extra settings in the popup (title, target, nofollow) are ignored.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'internal_link' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field ID', 'total-theme-core' ),
						'param_name' => 'url_custom_field',
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'custom_field' ) ),
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Callback Function', 'total-theme-core' ),
						'param_name' => 'url_callback_function',
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'callback_function' ) ),
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Title Attribute', 'total-theme-core' ),
						'param_name' => 'title',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Target', 'total-theme-core' ),
						'param_name' => 'target',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'Self', 'total-theme-core' ) ,
							'blank' => esc_html__( 'Blank', 'total-theme-core' ),
							'local' => esc_html__( 'Local', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Rel', 'total-theme-core' ),
						'param_name' => 'rel',
						'std' => '',
						'choices' => array(
							'' => esc_html__( 'None', 'total-theme-core' ),
							'nofollow' => esc_html__( 'Nofollow', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Link', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Use Download Attribute?', 'total-theme-core' ),
						'param_name' => 'download_attribute',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'custom_link', 'custom_field', 'callback_function' ) ),
					),
					array(
						'type' => 'exploded_textarea',
						'heading' => esc_html__( 'Custom Data Attributes', 'total-theme-core' ),
						'param_name' => 'data_attributes',
						'group' => esc_html__( 'Link', 'total-theme-core' ),
						'description' => esc_html__( 'Enter your custom data attributes in the format of data|value. Hit enter after each set of data attributes.', 'total-theme-core' ),
					),
					// Lightbox
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Type', 'total-theme-core' ),
						'param_name' => 'lightbox_type',
						'value' => array(
							esc_html__( 'Auto Detect (Image, Video or Inline)', 'total-theme-core' ) => '',
							esc_html__( 'Image', 'total-theme-core' ) => 'image',
							esc_html__( 'Video', 'total-theme-core' ) => 'video_embed',
							esc_html__( 'iFrame', 'total-theme-core' ) => 'iframe',
							esc_html__( 'Inline Content', 'total-theme-core' ) => 'inline',
							esc_html__( 'HTML5', 'total-theme-core' ) => 'html5',
						),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'onclick', 'value' => 'lightbox' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Lightbox Title', 'total-theme-core' ),
						'param_name' => 'lightbox_title',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'onclick', 'value' => 'lightbox' ),
					),
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Custom Image', 'total-theme-core' ),
						'param_name' => 'image_attachment',
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'image', 'lightbox' ) ),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
					),
					array(
						'type' => 'attach_images',
						'heading' => esc_html__( 'Custom Gallery', 'total-theme-core' ),
						'param_name' => 'lightbox_gallery',
						'description' => esc_html__( 'Select images to create a lightbox Gallery.', 'total-theme-core' ),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'image', 'lightbox' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Post Gallery', 'total-theme-core' ),
						'param_name' => 'lightbox_post_gallery',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'onclick', 'value' => array( 'image', 'lightbox' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom HTML5 URL', 'total-theme-core' ),
						'param_name' => 'lightbox_video_html5_webm',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox_type', 'value' => 'html5' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Lightbox Dimensions (optional)', 'total-theme-core' ),
						'param_name' => 'lightbox_dimensions',
						'description' => esc_html__( 'Enter a custom width and height for your lightbox pop-up window. Use format widthxheight. Example: 1920x1080.', 'total-theme-core' ),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox_type', 'value' => array( 'iframe', 'html5', 'video_embed', 'inline' ) ),
					),
					// Styling
					array(
						'type' => 'vcex_notice',
						'param_name' => 'styling_notice',
						'text' => esc_html__( 'You can set custom styles for your this specific button below but you can also customize the design of all your buttons via the Customizer.', 'total-theme-core' ),
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_button_styles',
						'heading' => esc_html__( 'Style', 'total-theme-core' ),
						'param_name' => 'style',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_button_colors',
						'heading' => esc_html__( 'Preset Color', 'total-theme-core' ),
						'param_name' => 'color',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Layout', 'total-theme-core' ),
						'param_name' => 'layout',
						'choices' => 'button_layout',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
						'std' => 'inline',
						'description' => esc_html__( 'Note: If you add any custom settings in the container design tab the button can no longer render inline since the added elements are added as a wrapper.', 'total-theme-core' )
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Alignment', 'total-theme-core' ),
						'param_name' => 'align',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
						'description' => esc_html__( 'Note: Any alignment besides "Default" will add a wrapper around the button to position it so it will no longer be inline.', 'total-theme-core' )
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Size', 'total-theme-core' ),
						'param_name' => 'size',
						'std' => '',
						'choices' => 'button_size',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
						'dependency' => array( 'element' => 'font_size', 'is_empty' => true )
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background', 'total-theme-core' ),
						'param_name' => 'custom_background',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Background: Hover', 'total-theme-core' ),
						'param_name' => 'custom_hover_background',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'custom_color',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color: Hover', 'total-theme-core' ),
						'param_name' => 'custom_hover_color',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Width', 'total-theme-core' ),
						'param_name' => 'width',
						'description' => esc_html__( 'Please use a pixel or percentage value.', 'total-theme-core' ),
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
						'description' => esc_html__( 'Please enter a px value.', 'total-theme-core' ),
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Padding', 'total-theme-core' ),
						'param_name' => 'font_padding',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'margin',
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border', 'total-theme-core' ),
						'param_name' => 'border',
						'description' => esc_html__( 'Please use the shorthand format: width style color. Enter 0px or "none" to disable border.', 'total-theme-core' ),
						'group' => esc_html__( 'Styling', 'total-theme-core' ),
					),
					// Typography
					array(
						'type' => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'font_family',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_responsive_sizes',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'font_size',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
						'target' => 'font-size',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Letter Spacing', 'total-theme-core' ),
						'param_name' => 'letter_spacing',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'text_transform',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'font_weight',
						'group' => esc_html__( 'Typography', 'total-theme-core' ),
					),
					//Icons
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Icon library', 'total-theme-core' ),
						'param_name' => 'icon_type',
						'description' => esc_html__( 'Select icon library.', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'Theme Icons', 'total-theme-core' ) => '',
							esc_html__( 'Font Awesome', 'total-theme-core' ) => 'fontawesome',
							esc_html__( 'Open Iconic', 'total-theme-core' ) => 'openiconic',
							esc_html__( 'Typicons', 'total-theme-core' ) => 'typicons',
							esc_html__( 'Entypo', 'total-theme-core' ) => 'entypo',
							esc_html__( 'Linecons', 'total-theme-core' ) => 'linecons',
							esc_html__( 'Material', 'total-theme-core' ) => 'material',
							esc_html__( 'Pixel', 'total-theme-core' ) => 'pixelicons',
						),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left_fontawesome',
						'settings' => array( 'emptyIcon' => true, 'type' => 'fontawesome', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left_openiconic',
						'settings' => array( 'emptyIcon' => true, 'type' => 'openiconic', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left_typicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left_entypo',
						'settings' => array( 'emptyIcon' => true, 'type' => 'entypo', 'iconsPerPage' => 300 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left_linecons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'linecons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left_material',
						'settings' => array( 'emptyIcon' => true, 'type' => 'material', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'material' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Left Icon', 'total-theme-core' ),
						'param_name' => 'icon_left_pixelicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'pixelicons', 'source' => vcex_pixel_icons() ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right',
						'settings' => array( 'emptyIcon' => true, 'type' => 'ticons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'is_empty' => true ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right_fontawesome',
						'settings' => array( 'emptyIcon' => true, 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'fontawesome' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right_openiconic',
						'settings' => array( 'emptyIcon' => true, 'type' => 'openiconic', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'openiconic' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right_typicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'typicons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'typicons' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right_entypo',
						'settings' => array( 'emptyIcon' => true, 'type' => 'entypo', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'entypo' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right_linecons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'linecons', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'linecons' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right_material',
						'settings' => array( 'emptyIcon' => true, 'type' => 'material', 'iconsPerPage' => 100 ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'material' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Right Icon', 'total-theme-core' ),
						'param_name' => 'icon_right_pixelicons',
						'settings' => array( 'emptyIcon' => true, 'type' => 'pixelicons', 'source' => vcex_pixel_icons() ),
						'dependency' => array( 'element' => 'icon_type', 'value' => 'pixelicons' ),
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Left Icon: Right Padding', 'total-theme-core' ),
						'param_name' => 'icon_left_padding',
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Left Icon: Hover Transform x', 'total-theme-core' ),
						'param_name' => 'icon_left_transform',
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
						'description' => esc_html__( 'Enter a value to move the icon horizontally on hover. You can enter a px or em value. Use negative values to go left and positive values to go right. Example: 10px would move the icon 10 pixels to the right while -10px would move the icon 10 pixels to the left.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Right Icon: Left Padding', 'total-theme-core' ),
						'param_name' => 'icon_right_padding',
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Right Icon: Hover Transform x', 'total-theme-core' ),
						'param_name' => 'icon_right_transform',
						'group' => esc_html__( 'Icons', 'total-theme-core' ),
						'description' => esc_html__( 'Enter a value to move the icon horizontally on hover. You can enter a px or em value. Use negative values to go left and positive values to go right. Example: 10px would move the icon 10 pixels to the right while -10px would move the icon 10 pixels to the left.', 'total-theme-core' ),
					),
					// Design options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css_wrap',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					// Deprecated
					array( 'type' => 'hidden', 'param_name' => 'lightbox' ),
					array( 'type' => 'hidden', 'param_name' => 'lightbox_image' ),
					array( 'type' => 'hidden', 'param_name' => 'lightbox_poster_image' ),
				)
			);
		}

	}

}
new VCEX_Button_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_button' ) ) {
	class WPBakeryShortCode_vcex_button extends WPBakeryShortCode {}
}