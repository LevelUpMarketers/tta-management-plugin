<?php
/**
 * vcex_image_ba shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_image_ba';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Output var
$output = '';

// Primary and secondary imags required
if ( empty( $atts['before_img'] ) || empty( $atts['after_img'] ) ) {
	return;
}

// Load scripts
self::enqueue_scripts();

$wrap_attrs = array(
	'class' => 'vcex-image-ba-wrap',
);

if ( $atts['bottom_margin'] ) {
	$wrap_attrs['class'] .= ' ' . vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

if ( $css = vcex_vc_shortcode_custom_css_class( $atts['css'] ) ) {
	$wrap_attrs['class'] .= ' ' . $css;
}

if ( $atts['width'] ) {
	$wrap_attrs['style'] = vcex_inline_style( array(
		'width' => $atts['width'],
	), false );
}

if ( $atts['align'] ) {
	$wrap_attrs['class'] .= ' align' . $atts['align'];
}

$output .= '<div' . vcex_parse_html_attributes( $wrap_attrs ) . '>';

	$figure_classes = array( 'vcex-module', 'vcex-image-ba', 'twentytwenty-container' );
	if ( $atts['el_class'] ) {
		$figure_classes[] = vcex_get_extra_class( $atts['el_class'] );
	}
	if ( $atts['css_animation'] && 'none' != $atts['css_animation'] ) {
		$figure_classes[] = vcex_get_css_animation( $atts['css_animation'] );
	}
	$figure_classes = implode( ' ', $figure_classes );
	$figure_classes = vcex_parse_shortcode_classes( $figure_classes, 'vcex_image_swap', $atts );

	$data = htmlspecialchars( wp_json_encode( array(
		'orientation'        => $atts['orientation'],
		'default_offset_pct' => ! empty( $atts['default_offset_pct'] ) ? $atts['default_offset_pct'] : '0.5',
		'no_overlay'         => ( 'false' == $atts['overlay'] ) ? true : null,
		'before_label'       => ! empty( $atts['before_label'] ) ? esc_attr( $atts['before_label'] ) : esc_attr__( 'Before', 'total' ),
		'after_label'        => ! empty( $atts['after_label'] ) ? esc_attr( $atts['after_label'] ) : esc_attr__( 'After', 'total' ),
	) ) );

	$figure_attrs = array(
		'class'        => esc_attr( $figure_classes ),
		'data-options' => $data,
	);

	$output .= '<figure' . vcex_parse_html_attributes( $figure_attrs ) . '">';

		// Before image
		$output .= vcex_get_post_thumbnail( array(
			'attachment' => $atts['before_img'],
			'size'       => $atts['img_size'],
			'crop'       => $atts['img_crop'],
			'width'      => $atts['img_width'],
			'height'     => $atts['img_height'],
			'class'      => 'vcex-before',
		) );

		// After image
		$output .= vcex_get_post_thumbnail( array(
			'attachment' => $atts['after_img'],
			'size'       => $atts['img_size'],
			'crop'       => $atts['img_crop'],
			'width'      => $atts['img_width'],
			'height'     => $atts['img_height'],
			'class'      => 'vcex-after',
		) );

	$output .= '</figure>';

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
