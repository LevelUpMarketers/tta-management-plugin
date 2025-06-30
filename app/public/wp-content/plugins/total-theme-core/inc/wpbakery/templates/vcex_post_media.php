<?php
/**
 * vcex_post_media shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_post_media';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Get correct post ID
$post_id = vcex_get_the_ID();

// Thumbnail args
$thumbnail_args = array(
	'attachment' => get_post_thumbnail_id( $post_id ),
	'size'       => $img_size,
	'crop'       => $img_crop,
	'width'      => $img_width,
	'height'     => $img_height,
);

// Define wrap classes
$wrap_class = array(
	'vcex-post-media',
	'wpex-clr'
);

if ( $bottom_margin ) {
	$wrap_class[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $css ) {
	$wrap_class[] = vcex_vc_shortcode_custom_css_class( $css );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_class[] = vcex_get_css_animation( $css_animation );
}

if ( $classes ) {
	$wrap_class[] = vcex_get_extra_class( $classes );
}

if ( $visibility ) {
	$wrap_class[] = sanitize_html_class( $visibility );
}

$wrap_class = vcex_parse_shortcode_classes( implode( ' ', $wrap_class ), $shortcode_tag, $atts );

// Module output
$output = '<div class="' . esc_attr( $wrap_class ) . '">';

	if ( function_exists( 'wpex_get_post_media' ) ) {

		$output .= wpex_get_post_media( $post_id, array(
			'thumbnail_args' => $thumbnail_args,
			'lightbox'       => vcex_validate_boolean( $lightbox ),
		) );

	} else {

		$output .= get_the_post_thumbnail();

	}

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
