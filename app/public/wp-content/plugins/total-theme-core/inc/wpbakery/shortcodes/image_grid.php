<?php
/**
 * Image Grid Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Image_Grid' ) ) {

	class VCEX_Image_Grid {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_image_grid';

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
			if ( empty( $atts['img_border_radius'] ) && isset( $atts['rounded_image'] ) ) {
				if ( 'yes' == $atts['rounded_image'] || 'true' == $atts['rounded_image'] ) {
					$atts['img_border_radius'] = 'round';
				}
				unset( $atts['rounded_image'] );
			}
			return $atts;
		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Image Grid', 'total-theme-core' ),
				'description' => esc_html__( 'Responsive image gallery', 'total-theme-core' ),
				'base' => $this->shortcode,
				'icon' => 'vcex-image-grid vcex-icon ticon ticon-picture-o',
				'category' => vcex_shortcodes_branding(),
				'params' => array(
					array(
						'type' => 'vcex_attach_images',
						'heading' => esc_html__( 'Images', 'total-theme-core' ),
						'param_name' => 'image_ids',
						'group' => esc_html__( 'Gallery', 'total-theme-core' ),
						'description' => esc_html__( 'Click the plus icon to add images to your gallery. Once images are added they can be drag and dropped for sorting.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'admin_label' => true,
						'heading' => esc_html__( 'Post Gallery', 'total-theme-core' ),
						'param_name' => 'post_gallery',
						'group' => esc_html__( 'Gallery', 'total-theme-core' ),
						'description' => esc_html__( 'Enable to display images from the current post "Image Gallery".', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field Name', 'total-theme-core' ),
						'param_name' => 'custom_field_gallery',
						'group' => esc_html__( 'Gallery', 'total-theme-core' ),
						'description' => esc_html__( 'Enter the name of an Advanced Custom Field gallery or other meta field that returns an array of attachment ID\'s or a comma separated string to pull images from.', 'total-theme-core' ),
					),
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
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Content Alignment', 'total-theme-core' ),
						'param_name' => 'content_alignment',
						'std' => '',
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' ),
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
						'param_name' => 'unique_id',
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
					vcex_vc_map_add_css_animation( array(
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' ),
						),
					) ),
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' ),
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Grid Style', 'total-theme-core' ),
						'param_name' => 'grid_style',
						'value' => array(
							esc_html__( 'Fit Rows', 'total-theme-core' )   => 'default',
							esc_html__( 'Masonry', 'total-theme-core' )    => 'masonry',
							esc_html__( 'No Margins', 'total-theme-core' ) => 'no-margins',
							esc_html__( 'Justified', 'total-theme-core' )  => 'justified',
						),
						'edit_field_class' => 'vc_col-sm-3 vc_column clear',
					),
					array(
						'type' => 'vcex_grid_columns',
						'heading' => esc_html__( 'Columns', 'total-theme-core' ),
						'param_name' => 'columns',
						'std' => '4',
						'edit_field_class' => 'vc_col-sm-3 vc_column',
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' ),
						),
					),
					array(
						'type' => 'vcex_column_gaps',
						'heading' => esc_html__( 'Gap', 'total-theme-core' ),
						'param_name' => 'columns_gap',
						'edit_field_class' => 'vc_col-sm-3 vc_column',
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' )
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Responsive', 'total-theme-core' ),
						'param_name' => 'responsive_columns',
						'value' => array(
							esc_html__( 'Yes', 'total-theme-core' ) => 'true',
							esc_html__( 'No', 'total-theme-core' ) => 'false',
						),
						'edit_field_class' => 'vc_col-sm-3 vc_column',
						'dependency' => array( 'element' => 'columns', 'value' => array( '2', '3', '4', '5', '6', '7', '8', '9', '10' ) ),
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' )
						),
					),
					array(
						'type' => 'vcex_grid_columns_responsive',
						'heading' => esc_html__( 'Responsive Settings', 'total-theme-core' ),
						'param_name' => 'columns_responsive_settings',
						'dependency' => array( 'element' => 'responsive_columns', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Target Height', 'total-theme-core' ),
						'param_name' => 'justified_row_height',
						'std' => '200',
						'edit_field_class' => 'vc_col-sm-3 vc_column',
						'dependency' => array( 'element' => 'grid_style', 'value' => array( 'justified' ) ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'justified_row_margin',
						'std' => '5',
						'edit_field_class' => 'vc_col-sm-3 vc_column',
						'dependency' => array( 'element' => 'grid_style', 'value' => array( 'justified' ) ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Last Row', 'total-theme-core' ),
						'param_name' => 'justified_last_row',
						'std' => 'justify',
						'value' => array(
							esc_html__( 'Justfiy', 'total-theme-core' ) => 'justify',
							esc_html__( 'No Justify', 'total-theme-core' ) => 'nojustify',
							esc_html__( 'Hide', 'total-theme-core' ) => 'hide',
							esc_html__( 'Center', 'total-theme-core' ) => 'center',
							esc_html__( 'Left', 'total-theme-core' ) => 'left',
							esc_html__( 'Right', 'total-theme-core' ) => 'right',
						),
						'edit_field_class' => 'vc_col-sm-3 vc_column',
						'dependency' => array( 'element' => 'grid_style', 'value' => array( 'justified' ) ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Randomize Images', 'total-theme-core' ),
						'param_name' => 'randomize_images',
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Images Per Page', 'total-theme-core' ),
						'param_name' => 'posts_per_page',
						'value' => '-1',
						'description' => esc_html__( 'This will enable pagination for your gallery. Enter -1 or leave blank to display all images without pagination.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Pagination', 'total-theme-core' ),
						'param_name' => 'pagination',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Load More Button', 'total-theme-core' ),
						'param_name' => 'pagination_loadmore',
					),
					// Links
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Image Link', 'total-theme-core' ),
						'param_name' => 'thumbnail_link',
						'std' => 'lightbox',
						'choices' => array(
							'none' => esc_html__( 'None', 'total-theme-core' ),
							'lightbox' => esc_html__( 'Lightbox', 'total-theme-core' ),
							'full_image' => esc_html__( 'Full Image', 'total-theme-core' ),
							'attachment_page' => esc_html__( 'Attachment Page', 'total-theme-core' ),
							'custom_link' => esc_html__( 'Custom Links', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Links', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Link Title Attribute', 'total-theme-core' ),
						'param_name' => 'link_title_tag',
						'group' => esc_html__( 'Links', 'total-theme-core' ),
						'description' => esc_html__( 'Enables the title tag on the links, based on the image alt text.', 'total-theme-core' ),
						'dependency' => array(
							'element' => 'thumbnail_link',
							'value' => array( 'lightbox', 'attachment_page', 'custom_link', 'full_image' )
						),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Lightbox Title', 'total-theme-core' ),
						'param_name' => 'lightbox_title',
						'std' => 'alt',
						'choices' => array(
							'false' => esc_html__( 'None', 'total-theme-core' ),
							'alt' => esc_html__( 'Alt', 'total-theme-core' ),
							'title' => esc_html__( 'Title', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Links', 'total-theme-core' ),
						'dependency' => array( 'element' => 'thumbnail_link', 'value' => 'lightbox' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Lightbox Gallery', 'total-theme-core' ),
						'param_name' => 'lightbox_gallery',
						'group' => esc_html__( 'Links', 'total-theme-core' ),
						'dependency' => array( 'element' => 'thumbnail_link', 'value' => 'lightbox' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'true',
						'heading' => esc_html__( 'Lightbox Caption', 'total-theme-core' ),
						'param_name' => 'lightbox_caption',
						'group' => esc_html__( 'Links', 'total-theme-core' ),
						'dependency' => array( 'element' => 'thumbnail_link', 'value' => 'lightbox' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Link Target', 'total-theme-core' ),
						'param_name' => 'custom_links_target',
						'group' => esc_html__( 'Links', 'total-theme-core' ),
						'value' => array(
							esc_html__( 'Same window', 'total-theme-core' ) => '_self',
							esc_html__( 'New window', 'total-theme-core' ) => '_blank'
						),
						'dependency' => array( 'element' => 'thumbnail_link', 'value' => array( 'custom_link' ) ),
					),
					array(
						'type' => 'exploded_textarea',
						'heading' => esc_html__( 'Custom links', 'total-theme-core' ),
						'param_name' => 'custom_links',
						'description' => esc_html__( 'Enter links for each slide here. Divide links with linebreaks (Enter). For images without a link enter a # symbol.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'thumbnail_link', 'value' => array( 'custom_link' ) ),
						'group' => esc_html__( 'Links', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Link Meta Key', 'total-theme-core' ),
						'param_name' => 'link_meta_key',
						'description' => esc_html__( 'If you are using a meta value (custom field) for your image links you can enter the meta key here.', 'total-theme-core' ),
						'dependency' => array( 'element' => 'thumbnail_link', 'value' => 'custom_link' ),
						'group' => esc_html__( 'Links', 'total-theme-core' ),
					),
					array(
						'type' => 'exploded_textarea',
						'heading' => esc_html__( 'Custom Attributes', 'total-theme-core' ),
						'param_name' => 'link_attributes',
						'description' => esc_html__( 'Enter your custom attributes in the format of key|value. Hit enter after each set of attributes.', 'total-theme-core' ),
						'group' => esc_html__( 'Links', 'total-theme-core' ),
					),
					// Image
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
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
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Image Border Radius', 'total' ),
						'param_name' => 'img_border_radius',
						'value' => vcex_border_radius_choices(),
						'group' => esc_html__( 'Image', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_overlay',
						'heading' => esc_html__( 'Image Overlay', 'total-theme-core' ),
						'param_name' => 'overlay_style',
						'group' => esc_html__( 'Image', 'total-theme-core' ),
						'exclude_choices' => array( 'thumb-swap', 'category-tag', 'category-tag-two' ),
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
						'heading' => esc_html__( 'CSS3 Image Hover', 'total-theme-core' ),
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
						'std' => 'no',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'title',
						'vcex' => array( 'off' => 'no', 'on' => 'yes' ),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' ),
						),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Tag', 'total-theme-core' ),
						'param_name' => 'title_tag',
						'std' => 'h2',
						'choices' => array(
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5',
							'div' => 'div',
						),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Based On', 'total-theme-core' ),
						'param_name' => 'title_type',
						'std' => 'title',
						'choices' => array(
							'title' => esc_html__( 'Title', 'total-theme-core' ),
							'alt' => esc_html__( 'Alt', 'total-theme-core' ),
							'caption' => esc_html__( 'Caption', 'total-theme-core' ),
							'description' => esc_html__( 'Description', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'title_color',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'title_font_family',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'title_weight',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'title_transform',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'title_size',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'title_line_height',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'title_margin',
						'group' => esc_html__( 'Title', 'total-theme-core' ),
						'dependency' => array( 'element' => 'title', 'value' => 'yes' ),
					),
					// Excerpt
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Enable', 'total-theme-core' ),
						'param_name' => 'excerpt',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array(
							'element' => 'grid_style',
							'value' => array( 'default', 'fit-rows', 'masonry', 'no-margins' ),
						),
					),
					array(
						'type' => 'vcex_select_buttons',
						'heading' => esc_html__( 'Based On', 'total-theme-core' ),
						'param_name' => 'excerpt_type',
						'std' => 'caption',
						'choices' => array(
							'caption' => esc_html__( 'Caption', 'total-theme-core' ),
							'description' => esc_html__( 'Description', 'total-theme-core' ),
						),
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Color', 'total-theme-core' ),
						'param_name' => 'excerpt_color',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type'  => 'vcex_font_family_select',
						'heading' => esc_html__( 'Font Family', 'total-theme-core' ),
						'param_name' => 'excerpt_font_family',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_font_weight',
						'heading' => esc_html__( 'Font Weight', 'total-theme-core' ),
						'param_name' => 'excerpt_weight',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_text_transforms',
						'heading' => esc_html__( 'Text Transform', 'total-theme-core' ),
						'param_name' => 'excerpt_transform',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Font Size', 'total-theme-core' ),
						'param_name' => 'excerpt_size',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Line Height', 'total-theme-core' ),
						'param_name' => 'excerpt_line_height',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_trbl',
						'heading' => esc_html__( 'Margin', 'total-theme-core' ),
						'param_name' => 'excerpt_margin',
						'group' => esc_html__( 'Excerpt', 'total-theme-core' ),
						'dependency' => array( 'element' => 'excerpt', 'value' => 'true' ),
					),
					// Design Options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'Wrap CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'Entry CSS box', 'total-theme-core' ),
						'param_name' => 'entry_css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					// Deprecated params
					array( 'type' => 'hidden', 'param_name' => 'lightbox_path' ),
					array( 'type' => 'hidden', 'param_name' => 'lightbox_loop' ),
					array( 'type' => 'hidden', 'param_name' => 'rounded_image' ),
				)
			);

		}

	}
}
new VCEX_Image_Grid;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_image_grid' ) ) {
	class WPBakeryShortCode_vcex_image_grid extends WPBakeryShortCode {}
}
