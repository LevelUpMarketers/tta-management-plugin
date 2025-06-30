<?php
/**
 * Image Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_Image_Shortcode' ) ) {

	class VCEX_Image_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'vcex_image';

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
		}

		/**
		 * Map shortcode to VC
		 *
		 * @since 4.5
		 */
		public function map() {
			return array(
				'name' => esc_html__( 'Image', 'total-theme-core' ),
				'description' => esc_html__( 'Single Image', 'total-theme-core' ),
				'base' => $this->shortcode,
				'category' => vcex_shortcodes_branding(),
				'icon' => 'vcex-image vcex-icon ticon ticon-picture-o',
				'admin_enqueue_js' => vcex_asset_url( 'js/backend-editor/vcex-image-view.min.js' ),
				'js_view' => 'vcexImageView',
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Source', 'total-theme-core' ),
						'param_name' => 'source',
						'std' => 'media_library',
						'value' => array(
							esc_html__( 'Media Library', 'total-theme-core' ) => 'media_library',
							esc_html__( 'External', 'total-theme-core' ) => 'external',
							esc_html__( 'Custom Field', 'total-theme-core' ) => 'custom_field',
							esc_html__( 'Featured Image', 'total-theme-core' ) => 'featured',
							esc_html__( 'Post Author Avatar', 'total-theme-core' ) => 'author_avatar',
							esc_html__( 'Current User Avatar', 'total-theme-core' ) => 'user_avatar',
							esc_html__( 'Callback Function', 'total-theme-core' ) => 'callback_function',
						),
						'admin_label' => true,
					),
					array(
						'type' => 'attach_image',
						'heading' => esc_html__( 'Image', 'total-theme-core' ),
						'param_name' => 'image_id',
						'dependency' => array( 'element' => 'source', 'value' => 'media_library' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'External Image URL', 'total-theme-core' ),
						'param_name' => 'external_image',
						'dependency' => array( 'element' => 'source', 'value' => 'external' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Field Name', 'total-theme-core' ),
						'param_name' => 'custom_field_name',
						'dependency' => array( 'element' => 'source', 'value' => 'custom_field' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Callback Function', 'total-theme-core' ),
						'param_name' => 'callback_function',
						'dependency' => array( 'element' => 'source', 'value' => 'callback_function' ),
						'admin_label' => true,
					),
					array(
						'type' => 'vc_link',
						'heading' => esc_html__( 'Link', 'total-theme-core' ),
						'param_name' => 'link',
						'dependency' => array( 'element' => 'lightbox', 'value' => 'false' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Link: Local Scroll', 'total-theme-core' ),
						'param_name' => 'link_local_scroll',
						'dependency' => array( 'element' => 'link', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'param_name' => 'lightbox',
						'std' => 'false',
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Caption', 'total-theme-core' ),
						'param_name' => 'caption',
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
						'param_name' => 'bottom_margin', // can't name it margin_bottom due to WPBakery parsing issue
						'value' => vcex_margin_choices(),
					),
					array(
						'type' => 'vcex_text_alignments',
						'heading' => esc_html__( 'Align', 'total-theme-core' ),
						'param_name' => 'align',
						'std' => '',
					),
					array(
						'type' => 'vcex_visibility',
						'heading' => esc_html__( 'Visibility', 'total-theme-core' ),
						'param_name' => 'visibility',
					),
					vcex_vc_map_add_css_animation(),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Alt Attribute', 'total-theme-core' ),
						'param_name' => 'alt_attr',
					),
					// Crop
					array(
						'type' => 'vcex_image_sizes',
						'heading' => esc_html__( 'Image Size', 'total-theme-core' ),
						'param_name' => 'img_size',
						'std' => 'wpex_custom',
						'group' => esc_html__( 'Size', 'total-theme-core' ),
						'description' => esc_html__( 'Note: For security reasons custom cropping only works on images hosted on your own server in the WordPress uploads folder. If you are using an external image it will display in full.', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_crop_locations',
						'heading' => esc_html__( 'Image Crop Location', 'total-theme-core' ),
						'param_name' => 'img_crop',
						'std' => 'center-center',
						'group' => esc_html__( 'Size', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Width', 'total-theme-core' ),
						'param_name' => 'img_width',
						'group' => esc_html__( 'Size', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Image Crop Height', 'total-theme-core' ),
						'param_name' => 'img_height',
						'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
						'group' => esc_html__( 'Size', 'total-theme-core' ),
						'dependency' => array( 'element' => 'img_size', 'value' => 'wpex_custom' ),
					),
					// Lightbox
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Custom Lightbox URL', 'total-theme-core' ),
						'param_name' => 'lightbox_url',
						'group' => esc_html__( 'lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox', 'value' => 'true' ),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Lightbox Type', 'total-theme-core' ),
						'param_name' => 'lightbox_type',
						'std' => '',
						'value' => array(
							esc_html__( 'Auto Detect (Image, Video or Inline)', 'total-theme-core' ) => '',
							esc_html__( 'Image', 'total-theme-core' ) => 'image',
							esc_html__( 'Video', 'total-theme-core' ) => 'video',
							esc_html__( 'Iframe', 'total-theme-core' ) => 'iframe',
							esc_html__( 'URL', 'total-theme-core' ) => 'url',
							esc_html__( 'Inline Content', 'total-theme-core' ) => 'inline',
							esc_html__( 'HTML5', 'total-theme-core' ) => 'html5',
						),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox_url', 'not_empty' => true ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'heading' => esc_html__( 'Video Overlay Icon?', 'total-theme-core' ),
						'param_name' => 'lightbox_video_overlay_icon',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'std' => 'false',
						'dependency' => array( 'element' => 'lightbox_type', 'value' => 'video' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Lightbox Title', 'total-theme-core' ),
						'param_name' => 'lightbox_title',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox', 'value' => 'true' ),
					),
					array(
						'type' => 'textarea',
						'heading' => esc_html__( 'Lightbox Caption', 'total-theme-core' ),
						'param_name' => 'lightbox_caption',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox', 'value' => 'true' ),
						'description' => esc_html__( 'The lightbox caption can be used to add a longer caption to your image lighbox. This setting is exclusive for singular images.', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Lightbox Dimensions', 'total-theme-core' ),
						'param_name' => 'lightbox_dimensions',
						'description' => esc_html__( 'Enter a custom width and height for your lightbox pop-up window. Use format widthxheight. Example: 1920x1080.', 'total-theme-core' ),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox_type', 'value' => array( 'video', 'url', 'html5', 'iframe' ) ),
					),
					array(
						'type' => 'attach_image',
						'admin_label' => false,
						'heading' => esc_html__( 'Custom Image', 'total-theme-core' ),
						'param_name' => 'lightbox_custom_img',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_attach_images',
						'admin_label' => false,
						'heading' => esc_html__( 'Custom Gallery', 'total-theme-core' ),
						'param_name' => 'lightbox_gallery',
						'description' => esc_html__( 'Select images to create a lightbox Gallery.', 'total-theme-core' ),
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox', 'value' => 'true' ),
					),
					array(
						'type' => 'vcex_ofswitch',
						'std' => 'false',
						'heading' => esc_html__( 'Post Gallery', 'total-theme-core' ),
						'param_name' => 'lightbox_post_gallery',
						'group' => esc_html__( 'Lightbox', 'total-theme-core' ),
						'dependency' => array( 'element' => 'lightbox', 'value' => 'true' ),
					),
					// Overlay and Hover
					array(
						'type' => 'vcex_hover_animations',
						'heading' => esc_html__( 'Hover Animation', 'total-theme-core'),
						'param_name' => 'hover_animation',
						'group' => esc_html__( 'Hovers', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_overlay',
						'heading' => esc_html__( 'Image Overlay', 'total-theme-core' ),
						'param_name' => 'overlay_style',
						'std' => 'none',
						'group' => esc_html__( 'Hovers', 'total-theme-core' ),
						'exclude_choices' => array(
							'thumb-swap',
							'thumb-swap-title',
							'category-tag',
							'category-tag-two'
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Overlay Excerpt Length', 'total-theme-core' ),
						'param_name' => 'overlay_excerpt_length',
						'value' => '15',
						'group' => esc_html__( 'Hovers', 'total-theme-core' ),
						'dependency' => array( 'element' => 'overlay_style', 'value' => 'title-excerpt-hover' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Overlay Button Text', 'total-theme-core' ),
						'param_name' => 'overlay_button_text',
						'group' => esc_html__( 'Hovers', 'total-theme-core' ),
						'dependency' => array( 'element' => 'overlay_style', 'value' => 'hover-button' ),
					),
					array(
						'type' => 'vcex_image_hovers',
						'heading' => esc_html__( 'CSS3 Image Hover', 'total-theme-core' ),
						'param_name' => 'img_hover_style',
						'group' => esc_html__( 'Hovers', 'total-theme-core' ),
						'dependency' => array( 'element' => 'hover_animation', 'is_empty' => true ),
					),
					// Design Options
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'total-theme-core' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Width', 'total-theme-core' ),
						'param_name' => 'width',
						'description' => esc_html__( 'The width function can be used to constrict your image to a specific width without having to crop the image. It also can be used in combination with different overlay styles that require the image to stretch to fit the parent container', 'total-theme-core' ),
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Border Radius', 'total-theme-core' ),
						'param_name' => 'border_radius',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
					array(
						'type' => 'vcex_image_filters',
						'heading' => esc_html__( 'Image Filter', 'total-theme-core' ),
						'param_name' => 'img_filter',
						'group' => esc_html__( 'Design Options', 'total-theme-core' ),
					),
				)
			);
		}

	}
}
new VCEX_Image_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_vcex_image' ) ) {
	class WPBakeryShortCode_vcex_image extends WPBakeryShortCode {}
}

/**
 * Ajax function used to display preview in backend-editor
 */
function vcex_image_preview() {
	vc_user_access()->checkAdminNonce()->validateDie()->wpAny( 'edit_posts', 'edit_pages' )->validateDie();

	$content = vc_post_param( 'content' );
	$params  = vc_post_param( 'params' );
	$post_id = (int) vc_post_param( 'post_id' );
	$source  = vc_post_param( 'image_source', 'media_library' );
	$img     = '';

	switch ( $source ) {

		case 'media_library':
		case 'featured':

			if ( 'featured' === $source ) {
				if ( $post_id && has_post_thumbnail( $post_id ) && 'templatera' != get_post_type( $post_id ) ) {
					$img_id = get_post_thumbnail_id( $post_id );
				} else {
					$img_id = 0;
				}
			} else {
				$img_id = preg_replace( '/[^\d]/', '', intval( $content ) );
			}

			if ( $img_id ) {
				$img = wp_get_attachment_image_src( $img_id, 'thumbnail' );
				if ( $img ) {
					$img = $img[0];
				}
			}

			break;

		case 'author_avatar';

			$img = get_avatar_url( get_post_field( 'post_author', $post_id ), array( 'size' => get_option( 'thumbnail_size_w' ) ) );

			break;

		case 'user_avatar';

			$img = get_avatar_url( wp_get_current_user(), array( 'size' => get_option( 'thumbnail_size_w' ) ) );

			break;

		case 'external';

			if ( $content ) {
				$img = $content;
			}

			break;

		case 'custom_field';

			if ( $content ) {
				$img = get_post_meta( $post_id, $content, true );
				if ( is_numeric( $img ) ) {
					$img = wp_get_attachment_image_src( $img, 'thumbnail' );
					if ( $img ) {
						$img = $img[0];
					}
				}
			}

			break;

	}

	echo esc_url( $img );

	die();
}
add_action( 'wp_ajax_vcex_image_preview', 'vcex_image_preview' );