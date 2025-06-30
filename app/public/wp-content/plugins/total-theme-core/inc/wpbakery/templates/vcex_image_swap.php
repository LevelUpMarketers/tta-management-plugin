<?php
/**
 * vcex_image_swap shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_image_swap';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Fallbacks (old atts)
$link_title  = isset( $atts['link_title'] ) ? $atts['link_title'] : '';
$link_target = isset( $atts['link_target'] ) ? $atts['link_target'] : '';

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Output var
$output = '';

// Auto images
if ( vcex_validate_boolean( $dynamic_images ) ) {
	$post_id = vcex_get_the_ID();
	if ( ! empty( $post_id ) ) {
		$primary_image = get_post_thumbnail_id( $post_id );
		if ( function_exists( 'wpex_get_secondary_thumbnail' ) ) {
			$secondary_image = wpex_get_secondary_thumbnail( $post_id );
		}
	}
}

// Apply filters to images for advanced child theming
$primary_image   = apply_filters( 'vcex_image_swap_primary_image', $primary_image );
$secondary_image = apply_filters( 'vcex_image_swap_secondary_image', $secondary_image );

// Primary and secondary imags required
if ( empty( $primary_image ) || empty( $secondary_image ) ) {
	return;
}

// Add styles
$wrapper_inline_style = vcex_inline_style( array(
	'width' => $container_width,
) );

$image_style = vcex_inline_style( array(
	'border_radius' => $border_radius,
), false );

// Add classes
$wrap_classes = array(
	'vcex-module',
	'vcex-image-swap',
	'wpex-block',
	'wpex-relative',
	'wpex-mx-auto',
	'wpex-max-w-100',
	'wpex-overflow-hidden',
	'wpex-clr',
);

if ( $bottom_margin && empty( $css ) ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $align && ( 'left' == $align || 'right' == $align ) ) {
	$wrap_classes[] = 'float' . sanitize_html_class( $align );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

if ( $css_animation && 'none' != $css_animation && empty( $css ) ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

if ( $css ) {

	$css_wrap_class = vcex_vc_shortcode_custom_css_class( $css );

	$css_wrap_class .= ' wpex-mx-auto';

	if ( $bottom_margin ) {
		$css_wrap_class .= ' wpex-mb-' . absint( $bottom_margin );
	}

	if ( $css_animation && 'none' != $css_animation ) {
		$css_wrap_class .= ' ' . trim( vcex_get_css_animation( $css_animation ) );
	}

	$output .='<div class="' . esc_attr( $css_wrap_class )  . '"' . $wrapper_inline_style . '>';
}

$output .='<figure class="' . esc_attr( $wrap_classes ) . '"' . $wrapper_inline_style . vcex_get_unique_id( $unique_id ) . '>';

	// Get link data
	$link_data = vcex_build_link( $link );

	// Output link
	if ( ! empty( $link_data['url'] ) ) {

		// Define link attributes
		$link_attrs = array(
			'href'  => '',
			'class' => 'vcex-image-swap-link',
		);

		// Link attributes
		$link_attrs['href']   = isset( $link_data['url'] ) ? esc_url( $link_data['url'] ) : $link;
		$link_attrs['title']  = isset( $link_data['title'] ) ? esc_attr( $link_data['title'] ) : '';
		$link_attrs['rel']    = isset( $link_data['rel'] ) ? $link_data['rel'] : '';
		$link_attrs['target'] = isset( $link_data['target'] ) ? $link_data['target'] : '';

		$output .='<a' . vcex_parse_html_attributes( $link_attrs ) . '>';

	}

	// Primary image
	$transition_duration = ! empty( $hover_speed ) ? 'wpex-duration-' . absint( $hover_speed ) : 'wpex-duration-300';

	$output .= vcex_get_post_thumbnail( array(
		'attachment' => $primary_image,
		'size'       => $img_size,
		'crop'       => $img_crop,
		'width'      => $img_width,
		'height'     => $img_height,
		'class'      => 'vcex-image-swap-primary wpex-block wpex-relative wpex-z-5 wpex-w-100 wpex-overflow-hidden wpex-transition-opacity ' . $transition_duration,
		'style'      => $image_style,
	) );

	// Secondary image
	$output .= vcex_get_post_thumbnail( array(
		'attachment' => $secondary_image,
		'size'       => $img_size,
		'crop'       => $img_crop,
		'width'      => $img_width,
		'height'     => $img_height,
		'class'      => 'vcex-image-swap-secondary wpex-block wpex-absolute wpex-inset-0 wpex-z-1 wpex-w-100 wpex-overflow-hidden',
		'style'      => $image_style,
	) );

	// Close link wrapper
	if ( ! empty( $link_data['url'] ) ) {
		$output .='</a>';
	}

$output .='</figure>'; // Close main wrap

// Close CSS wrapper
if ( $css ) {
	$output .='</div>';
}

if ( $align && ( 'left' == $align || 'right' == $align ) ) {
	$output .= '<div class="vcex-image-swap-clear-align wpex-clear"></div>';
}

// @codingStandardsIgnoreLine
echo $output;