<?php
/**
 * vcex_button shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_button';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define output
$output = '';

// Deprecated Attributes
if ( ! empty( $atts['class'] ) && empty( $classes ) ) {
	$atts['classes'] = $atts['class'];
}

if ( isset( $atts['lightbox'] ) && 'true' == $atts['lightbox'] ) {
	$atts['onclick'] = 'lightbox';
}

if ( ! empty( $atts['lightbox_image'] ) ) {
	$atts['image_attachment'] = $atts['lightbox_image'];
}

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Extract shortcode attributes
extract( $atts );

// Sanitize & declare vars
$button_data = array();

// Define URL
$url = $url ? $url : '#';

// Internal links
if ( 'internal_link' == $onclick ) {
	$internal_link = vcex_build_link( $internal_link );
	if ( ! empty( $internal_link['url'] ) ) {
		$url = $internal_link[ 'url' ];
	} else {
		$url = '#';
	}
}

// Sanitize content
if( 'custom_field' == $text_source ) {
	$content = $text_custom_field ? get_post_meta( vcex_get_the_ID(), $text_custom_field, true ) : '';
} elseif( 'callback_function' == $text_source ) {
	$content = ( $text_callback_function && function_exists( $text_callback_function ) ) ? call_user_func( $text_callback_function ) : '';
} else {
	$content = ! empty( $content ) ? $content : esc_html__( 'Button Text', 'total' );
}

// Load custom font
if ( $font_family ) {
	vcex_enqueue_font( $font_family );
}

// Button Classes
$button_classes = array(
	'vcex-button'
);

$button_classes[] = vcex_get_button_classes( $style, $color, $size, $align );

if ( $bottom_margin ) {
	$button_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $layout ) {
	$button_classes[] = $layout;
}

if ( $classes ) {
	$button_classes[] = vcex_get_extra_class( $classes );
}

if ( $hover_animation ) {
	$button_classes[] = vcex_hover_animation_class( $hover_animation );
	vcex_enque_style( 'hover-animations' );
}

if ( 'local' == $target ) {
	$button_classes[] = 'local-scroll-link';
}

if ( $css_animation && 'none' != $css_animation && ! $css_wrap ) {
	$button_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $visibility ) {
	$button_classes[] = $visibility;
}

// Custom field link
if ( 'custom_field' == $onclick ) {
	$url = get_post_meta( vcex_get_the_ID(), $url_custom_field, true );
	if ( ! $url ) {
		return; // Lets not show any button if the custom field is empty
	}
}

// Callback function link
elseif ( 'callback_function' == $onclick && function_exists( $url_callback_function ) ) {
	$url = call_user_func( $url_callback_function );
	if ( ! $url ) {
		return; // Lets not show any button if the callback is empty
	}
}

// Image link
elseif ( 'image' == $onclick || 'lightbox' == $onclick ) {
	$url = $image_attachment ? wp_get_attachment_url( $image_attachment ) : $url;
}

// Lightbox classes and data
if ( 'lightbox' == $onclick || 'image' == $onclick ) {

	// Enqueue lightbox scripts
	vcex_enqueue_lightbox_scripts();

	// Lightbox gallery
	if ( 'true' == $lightbox_post_gallery && $gallery_ids = vcex_get_post_gallery_ids() ) {
		$lightbox_gallery = $gallery_ids;
	}

	if ( $lightbox_gallery ) {
		$button_classes[] = 'wpex-lightbox-gallery';
		$gallery_ids = is_array( $lightbox_gallery ) ? $lightbox_gallery : explode( ',', $lightbox_gallery );
		if ( $gallery_ids && is_array( $gallery_ids ) ) {
			$button_data[] = 'data-gallery="' . vcex_parse_inline_lightbox_gallery( $gallery_ids ) . '"';
		}
	}

	// Iframe lightbox
	elseif ( 'iframe' == $lightbox_type ) {
		$button_classes[] = 'wpex-lightbox';
		$button_data[]    = 'data-type="iframe"';
	}

	// Image lightbox
	elseif ( 'image' == $lightbox_type ) {
		$button_classes[] = 'wpex-lightbox';
		if ( $image_attachment ) {
			$url = wp_get_attachment_url( $image_attachment );
		}
	}

	// Video embed lightbox
	elseif ( 'video_embed' == $lightbox_type ) {
		$url = vcex_get_video_embed_url( $url );
		$button_classes[] = 'wpex-lightbox';
	}

	// Html5 lightbox
	elseif ( 'html5' == $lightbox_type ) {
		$lightbox_video_html5_webm = $lightbox_video_html5_webm ? $lightbox_video_html5_webm : $url;
		$button_classes[] = 'wpex-lightbox';
		if ( $lightbox_video_html5_webm ) {
			$url = $lightbox_video_html5_webm;
		}
	}

	// Auto-detect lightbox ($url can't be empty)
	elseif ( $url ) {
		$button_classes[] = 'wpex-lightbox';
	}

	// Disable title
	if ( 'false' == $lightbox_title ) {
		$button_data[] = 'data-show_title="false"';
	}

	// Add lightbox dimensions
	if ( in_array( $lightbox_type, array( 'video', 'video_embed', 'url', 'html5', 'iframe', 'inline' ) ) ) {
		$lightbox_dims = vcex_parse_lightbox_dims( $lightbox_dimensions, 'array' );
		if ( $lightbox_dims ) {
			$button_data[] = 'data-width="' . esc_attr( $lightbox_dims['width'] ) . '"';
			$button_data[] = 'data-height="' . esc_attr( $lightbox_dims['height'] ) . '"';
		}
	}

}

// Custom data attributes
if ( $data_attributes ) {
	$data_attributes = explode( ',', $data_attributes );
	if ( is_array( $data_attributes ) ) {
		foreach( $data_attributes as $attribute ) {
			if ( false !== strpos( $attribute, '|' ) ) {
				$attribute = explode( '|', $attribute );
				$button_data[] = 'data-' . esc_attr( $attribute[0] ) .'="' . esc_attr( do_shortcode( $attribute[1] ) ) . '"';
			} else {
				$button_data[] = 'data-' . esc_attr( $attribute );
			}
		}
	}
}

// Wrap classes
$wrap_classes = array();

if ( 'center' == $align ) {
	$wrap_classes[] = 'textcenter';
}

if ( 'block' == $layout ) {
	$wrap_classes[] = 'theme-button-block-wrap';
	$wrap_classes[] = 'wpex-block';
	$wrap_classes[] = 'wpex-clear';
}

if ( 'expanded' == $layout ) {
	$wrap_classes[]   = 'theme-button-expanded-wrap';
	$button_classes[] = 'expanded';
}

if ( $wrap_classes ) {
	$wrap_classes[] = 'theme-button-wrap';
	$wrap_classes[] = 'wpex-clr';
	$wrap_classes   = implode( ' ', $wrap_classes );
}

$wrap_classes = vcex_parse_shortcode_classes( $wrap_classes, $shortcode_tag, $atts );

// Custom Style
$inline_style = vcex_inline_style( array(
	'background'     => $custom_background,
	'padding'        => $font_padding,
	'color'          => $custom_color,
	'border'         => $border,
	'font_size'      => $font_size,
	'font_weight'    => $font_weight,
	'letter_spacing' => $letter_spacing,
	'border_radius'  => $border_radius,
	'margin'         => $margin,
	'width'          => $width,
	'text_transform' => $text_transform,
	'font_family'    => $font_family,
), false );
if ( $custom_color && 'outline' == $style ) {
	$inline_style .= 'border-color:'. $custom_color .';';
}
if ( $inline_style ) {
	$inline_style = ' style="'. esc_attr( $inline_style ) .'"';
}

// Custom hovers
$hover_data = array();
if ( $custom_hover_background ) {
	$hover_data['background'] = esc_attr( $custom_hover_background );
}
if ( $custom_hover_color ) {
	$hover_data['color'] = esc_attr( $custom_hover_color );
}
if ( $hover_data ) {
	$button_data[] = "data-wpex-hover='" . htmlspecialchars( wp_json_encode( $hover_data ) ) . "'";
}

// Get responsive data
if ( $responsive_data = vcex_get_module_responsive_data( $atts ) ) {
	$button_data['data-wpex-rcss'] = $responsive_data;
}

// Define button icon_classes
$icon_left  = vcex_get_icon_class( $atts, 'icon_left' );
$icon_right = vcex_get_icon_class( $atts, 'icon_right' );

// Icon right style
if ( $icon_right ) {
	$icon_right_style = vcex_inline_style ( array(
		'padding_left' => $icon_right_padding,
	) );
}

// Turn arrays into strings
$button_classes = implode( ' ', $button_classes );
$button_data    = implode( ' ', $button_data );

// Open CSS wrapper
if ( $css_wrap ) {

	$output .= '<div class="' . vcex_vc_shortcode_custom_css_class( $css_wrap ) . vcex_get_css_animation( $css_animation ) . ' wpex-clr">';

}

	// Open wrapper for specific button styles
	if ( $wrap_classes ) {
		$output .= '<div class="' . esc_attr( $wrap_classes ) . '">';
	}

		$link_attrs = array(
			'id'       => vcex_get_unique_id( $unique_id ),
			'href'     => esc_url( do_shortcode( $url ) ),
			'title'    => $title ? esc_attr( do_shortcode( $title ) ) : '',
			'class'    => esc_attr( $button_classes ),
			'target'   => $target,
			'style'    => $inline_style,
			'rel'      => $rel,
			'data'     => $button_data,
			'download' => ( 'true' == $download_attribute ) ? 'download' : '',
		);

		// Open Link
		$output .= '<a' . vcex_parse_html_attributes( $link_attrs ) . '>';

			// Open inner span
			$output .= '<span class="theme-button-inner">';

				// Left Icon
				if ( $icon_left ) {

					vcex_enqueue_icon_font( $icon_type, $icon_left );

					$icon_left_style = vcex_inline_style ( array(
						'padding_right' => $icon_left_padding,
					) );

					$attrs = array(
						'class' => array(
							'vcex-icon-wrap',
							'theme-button-icon-left',
						),
						'style' => $icon_left_style,
					);

					if ( $icon_left_transform ) {
						$attrs['class'][] = 'wpex-transition-transform wpex-duration-200';
						$attrs['data-wpex-hover'] = htmlspecialchars( wp_json_encode( array(
							'parent'    => '.vcex-button',
							'transform' => 'translateX(' . vcex_validate_font_size( $icon_left_transform ) . ')',
						) ) );
					}

					$output .= '<span' . vcex_parse_html_attributes( $attrs ) . '>';

						$output .= '<span class="' . esc_attr( $icon_left ) . '" aria-hidden="true"></span>';

					$output .= '</span>';

				}

				// Text
				$output .= do_shortcode( $content );

				// Icon Right
				if ( $icon_right ) {

					vcex_enqueue_icon_font( $icon_type, $icon_right );

					$attrs = array(
						'class' => array(
							'vcex-icon-wrap',
							'theme-button-icon-right',
						),
						'style' => $icon_right_style,
					);

					if ( $icon_right_transform ) {
						$attrs['class'][] = 'wpex-transition-transform wpex-duration-200';
						$attrs['data-wpex-hover'] = htmlspecialchars( wp_json_encode( array(
							'parent'    => '.vcex-button',
							'transform' => 'translateX(' . vcex_validate_font_size( $icon_right_transform ) . ')',
						) ) );
					}

					$output .= '<span' . vcex_parse_html_attributes( $attrs ) . '>';

						$output .= '<span class="' . esc_attr( $icon_right ) . '" aria-hidden="true"></span>';

					$output .= '</span>';

				}

			// Close inner span
			$output .= '</span>';

		// Close link
		$output .= '</a>';

	// Close wrapper for specific button styles
	if ( $wrap_classes ) {
		$output .=  '</div>';
	}

// Close css wrap div
if ( $css_wrap ) {
	$output .= '</div>';
}

// @codingStandardsIgnoreLine
echo $output . ' ';
