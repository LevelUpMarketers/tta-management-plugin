<?php
/**
 * vcex_divider_multicolor shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_divider_multicolor';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

$colors = (array) vcex_vc_param_group_parse_atts( $atts['colors'] );

if ( ! $colors ) {
	return;
}

$count = count( $colors );

// Define wrap classes
$wrap_classes = array(
	'vcex-module',
	'vcex-divider-multicolor',
	'wpex-flex',
	'wpex-max-w-100',
	'wpex-m-auto',
	'wpex-clr',
);

if ( $atts['bottom_margin'] ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

if ( ! empty( $atts['el_class'] ) ) {
	$wrap_classes[] = vcex_get_extra_class( $atts['el_class'] );
}

if ( ! empty( $atts['visibility'] ) ) {
	$wrap_classes[] = sanitize_html_class( $atts['visibility'] );
}

if ( ! empty( $atts['align'] ) && 'center' != $atts['align'] ) {
	$wrap_classes[] = 'float-' . sanitize_html_class( $atts['align'] );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Get inline wrap style
$wrap_style = vcex_inline_style( array(
	'width'         => ( $atts['width'] && '100%' != $atts['width'] ) ? $atts['width'] : '',
	'margin_bottom' => $atts['margin_bottom'],
), false );

// Define wrap attributes
$wrap_attrs = array(
	'class' => esc_attr( $wrap_classes ),
	'style' => $wrap_style,
);

// Output
$output = '<div' . vcex_parse_html_attributes( $wrap_attrs ) . '>';

	foreach ( $colors as $color ) {

		$inline_style_escaped = vcex_inline_style( array(
			'background' => isset( $color['value'] ) ? $color['value'] : '',
			'height'     => ( $atts['height'] && '8px' !== $atts['height'] ) ? intval( $atts['height'] ) : '',
		), true );

		$output .= '<span class="wpex-flex-grow"' . $inline_style_escaped . '></span>';

	}

$output .= '</div>';

if ( $atts['align'] && 'center' != $atts['align'] ) {
	$output .= '<div class="wpex-clear"></div>'; // Clear floats
}

// @codingStandardsIgnoreLine
echo $output;