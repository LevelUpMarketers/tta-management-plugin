<?php
/**
 * vcex_divider_dots shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_divider_dots';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Sanitize vars
$count   = $count ? $count : '3';
$align   = $align ? $align : 'center';
$spacing = $spacing ? absint( $spacing ) : '10';

// Wrap classes
$wrap_classes   = array(
	'vcex-module',
	'vcex-divider-dots',
	'wpex-mr-auto',
	'wpex-ml-auto',
);

$span_class[] = 'wpex-last-mr-0';

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $align ) {
	$wrap_classes[] = 'text' . sanitize_html_class( $align );
}

if ( $visibility ) {
	$wrap_classes[] = $visibility;
}

if ( $el_class ) {
	$wrap_classes[] = vcex_get_extra_class( $el_class );
}

$wrap_classes = implode( ' ', $wrap_classes );
$wrap_classes = vcex_parse_shortcode_classes( $wrap_classes, $shortcode_tag, $atts );

// Define vars
$output = $wrap_style = $span_style = '';

// Wrap style
if ( $margin ) {
	$wrap_style = vcex_inline_style( array(
		'padding' => $margin,
	) );
}

// Span class
$span_class = array(
	'wpex-inline-block',
	'wpex-round',
	'wpex-bg-accent',
);
$span_class[] = 'wpex-mr-' . $spacing;
$span_class_escaped = esc_attr( implode( ' ', $span_class ) );

// Span style
if ( $size || $color ) {
	$span_style = vcex_inline_style( array(
		'height'     => $size,
		'width'      => $size,
		'background' => $color,
	) );
}

// Return output
$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . $wrap_style . '>';

	for ( $k = 0; $k < $count; $k++ ) {

		$output .= '<span class="' . $span_class_escaped . '"' . $span_style . '></span>';

	}

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
