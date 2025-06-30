<?php
/**
 * vcex_image shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.4
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_image';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Extract attributes for easier usage
extract( $atts );

// Define vars
$output = $image = $attachment = $image_style = $lightbox_source = '';
$overlay_style = $overlay_style ? $overlay_style : 'none';
$has_link = false;
$image_classes = 'wpex-align-middle'; // wpex-w-100 is an IE fix for images inside inline-blocks

// Sanitize vars
$lightbox_url = $lightbox_url ? do_shortcode( $lightbox_url ) : ''; // Allow shortcodes for lightbox URL

// Get image attachment ID
switch ( $source ) {
	case 'featured':
		if ( is_tax() || is_tag() || is_category() ) {
			$attachment = vcex_get_term_thumbnail_id( get_queried_object_id() );
		} else {
			$attachment = get_post_thumbnail_id( vcex_get_the_ID() );
		}
		break;
	case 'custom_field':
		if ( $custom_field_name ) {
			$custom_field_val = get_post_meta( vcex_get_the_ID(), $custom_field_name, true );
			if ( is_numeric( $custom_field_val ) ) {
				$attachment = $custom_field_val;
			} elseif( is_string( $custom_field_val ) ) {
				$image_url = $custom_field_val;
			}
		}
		break;
	case 'callback_function':
		if ( ! empty( $atts['callback_function'] ) && function_exists( $atts['callback_function'] ) ) {
			$callback_val = call_user_func( $atts['callback_function'] );
			if ( is_numeric( $callback_val ) ) {
				$attachment = $callback_val;
			} elseif( is_string( $callback_val ) ) {
				$image_url = $callback_val;
			}
		}
		break;
	case 'media_library':
	default:
		$attachment = $image_id;
		break;
}

// Generate image html
if ( $attachment ) {

	$image_style = vcex_inline_style( array(
		'border_radius' => $border_radius,
	), false );

	$img_args = array(
		'attachment' => $attachment,
		'size'       => $img_size,
		'crop'       => $img_crop,
		'width'      => $img_width,
		'height'     => $img_height,
		'style'      => $image_style,
		'class'      => esc_attr( $image_classes ),
	);

	if ( $alt_attr ) {
		$img_args[ 'alt' ] = esc_attr( $alt_attr );
	}

	$image = vcex_get_post_thumbnail( $img_args );

	$lightbox_source = ( 'true' == $lightbox && empty( $lightbox_url ) ) ? vcex_get_lightbox_image( $attachment ) : '';

} else {

	switch ( $source ) {
		case 'external':
			$image_url = $external_image;
			break;
		case 'author_avatar':
			$image_url = get_avatar_url( get_post(), array( 'size' => $img_width ) );
			break;
		case 'user_avatar':
			$image_url = get_avatar_url( wp_get_current_user(), array( 'size' => $img_width ) );
			break;
		default:
			if ( ! empty( $custom_field_val ) ) {
				$image_url = $custom_field_val;
			}
			break;
	}

	if ( ! empty( $image_url ) ) {

		$image_style = vcex_inline_style( array(
			'border_radius' => $border_radius,
		), true );

		$image = '<img src="' . set_url_scheme( esc_url( $image_url ) ) . '" class="' . esc_attr( $image_classes ) . '"' . $image_style . ' />';

		$lightbox_source = $image_url;

	}

}

// Return if no image has been added
if ( ! $image ) {
	if ( vcex_vc_is_inline() && function_exists( 'wpex_get_placeholder_image' ) ) {
		$image = wpex_get_placeholder_image();
	} else {
		return;
	}
}

// Define wrap classes
$wrap_classes = array(
	'vcex-image',
	'vcex-module',
	'wpex-clr'
);

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $align ) {
	$wrap_classes[] = 'text' . sanitize_html_class( $align );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $el_class ) {
	$wrap_classes[] = vcex_get_extra_class( $el_class );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Lightbox setup
if ( 'true' == $lightbox ) {

	vcex_enqueue_lightbox_scripts();

	$has_link = true;

	$lightbox_data = array();

	$link_attrs = array(
		'href'  => esc_url( $lightbox_source ),
		'class' => 'wpex-lightbox',
	);

	if ( 'true' == $lightbox_post_gallery && $gallery_ids = vcex_get_post_gallery_ids() ) {
		$lightbox_gallery = $gallery_ids;
	}

	// Lightbo Gallery
	if ( $lightbox_gallery ) {
		$link_attrs['href'] = '#';
		$gallery_ids = is_array( $lightbox_gallery ) ? $lightbox_gallery : explode( ',', $lightbox_gallery );
		if ( is_array( $gallery_ids ) ) {
			$lightbox_data['data-gallery'] = vcex_parse_inline_lightbox_gallery( $gallery_ids );
			$link_attrs['class']           = str_replace( 'wpex-lightbox', 'wpex-lightbox-gallery', $link_attrs['class'] );
			$atts['lightbox_class']        = 'wpex-lightbox-gallery';
		}
	}

	// Custom Lightbox Image
	elseif( $lightbox_custom_img ) {
		$link_attrs['href'] = vcex_get_lightbox_image( intval( $lightbox_custom_img ) );
	}

	// Custom Lightbox
	elseif ( $lightbox_url ) {

		$lightbox_url = set_url_scheme( esc_url( $lightbox_url ) );

		if ( 'video' == $lightbox_type ) {
			$lightbox_url = vcex_get_video_embed_url( $lightbox_url );
		} elseif( 'url' == $lightbox_type || 'iframe' == $lightbox_type ) {
			$lightbox_data['data-type'] = 'iframe';
		} elseif( 'image' == $lightbox_type ) {
			$lightbox_data['data-type'] = 'image';
		}

		if ( in_array( $lightbox_type, array( 'video', 'url', 'html5', 'iframe' ) ) ) {
			$lightbox_dims = vcex_parse_lightbox_dims( $lightbox_dimensions, 'array' );
			if ( $lightbox_dims ) {
				$lightbox_data['data-width']  = $lightbox_dims['width'];
				$lightbox_data['data-height'] = $lightbox_dims['height'];
			}
		}

		$link_attrs['href'] = esc_url( $lightbox_url );

	}

	if ( $lightbox_title ) {
		$lightbox_data['data-title'] = esc_attr( $lightbox_title );
	}

	if ( $lightbox_caption ) {
		$lightbox_data['data-caption'] = str_replace( '"',"'", wp_kses_post( $lightbox_caption ) );
	}

	$atts['lightbox_link'] = $link_attrs['href'];

	if ( $lightbox_data && is_array( $lightbox_data ) ) {
		$parsed_data = array(); // overlays require data to be in the value
		foreach ( $lightbox_data as $k => $v ) {
			$link_attrs[$k] = $v;
			$parsed_data[]  = $k . '="' . $v . '"';
		}
		$atts['lightbox_data'] = $parsed_data;
	}

} elseif ( $link ) {

	$link = vcex_build_link( $link );

	if ( ! empty( $link['url'] ) ) {

		$has_link = true;

		$link['url'] = do_shortcode( $link['url'] ); // allow shortcodes for custom url

		$link_attrs = array(
			'href'   => esc_url( $link['url'] ),
			'title'  => isset( $link['title'] ) ? $link['title'] : '',
			'rel'    => isset( $link['rel'] ) ? $link['rel'] : '',
			'class'  => '', // add empty class so we can add more as needed
		);

		if ( isset( $link['target'] )  ) {
			$link_attrs['target'] = $link['target'];
			$atts['link_target']  = $link['target']; // // save in atts for use with overlay styles
		}

		if ( 'true' == $link_local_scroll ) {
			$link_attrs[ 'class' ] .= ' local-scroll-link';
		}

		$atts['post_permalink'] = esc_url( $link['url'] ); // For overlays

	}

}

// Start output
$output .= '<figure class="' . esc_attr( $wrap_classes ) . '">';

	$inner_classes = array(
		'vcex-image-inner',
		'wpex-inline-block',
		'wpex-relative',
	);

	if ( $img_filter ) {
		$inner_classes[] = vcex_image_filter_class( $img_filter );
	}

	if ( $img_hover_style ) {
		$inner_classes[] = vcex_image_hover_classes( $img_hover_style );
	}

	if ( $overlay_style && 'none' != $overlay_style ) {
		$inner_classes[] = vcex_image_overlay_classes( $overlay_style );
	}

	if ( $hover_animation ) {
		$inner_classes[] = vcex_hover_animation_class( $hover_animation );
		vcex_enque_style( 'hover-animations' );
	}

	if ( $css ) {
		$inner_classes[] = vcex_vc_shortcode_custom_css_class( $css );
	}

	if ( $width ) {
		$inner_style = vcex_inline_style( array(
			'max_width' => esc_attr( $width ),
		) );
	} else {
		$inner_style = '';
	}

	// Setup post data which is used for image overlays
	if ( $attachment ) {
		global $post;
		$get_post = get_post( $attachment );
		setup_postdata( $get_post );
		$post = $get_post;
	}

	// Begin module output
	$output .= '<div class="' . esc_attr( implode( ' ', $inner_classes ) ) . '"' . $inner_style . '>';

		if ( $has_link ) {
			$output .= '<a' . vcex_parse_html_attributes( $link_attrs ) . '>';
		}

		$output .= $image;

		if ( 'none' != $overlay_style ) {
			ob_start();
			vcex_image_overlay( 'inside_link', $overlay_style, $atts );
			$output .= ob_get_clean();
		}

		if ( $has_link ) {
			if ( 'true' == $lightbox_video_overlay_icon ) {
				$output .= '<div class="overlay-icon"><span>&#9658;</span></div>';
			}
			$output .= '</a>';
		}

		if ( 'none' != $overlay_style ) {
			ob_start();
			vcex_image_overlay( 'outside_link', $overlay_style, $atts );
			$output .= ob_get_clean();
		}

	$output .= '</div>'; // close inner class

	if ( $attachment && 'true' === $caption && $post->post_excerpt ) {
		$output .= '<figcaption class="wpex-mt-10">' . wp_kses_post( $post->post_excerpt ) . '</figcaption>';
	}

	wp_reset_postdata();

$output .= '</figure>';

// @codingStandardsIgnoreLine
echo $output;