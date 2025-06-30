<?php
/**
 * vcex_alert shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_alert';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

if ( empty( $content ) ) {
	return;
}

$class = array(
	'vcex-module',
	'wpex-alert',
);

if ( $type ) {
	$class[] = 'wpex-alert-' . sanitize_html_class( $type );
}

if ( $bottom_margin ) {
	$class[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $padding_y ) {
	$class[] = 'wpex-py-' . sanitize_html_class( absint( $padding_y ) );
}

if ( $font_size ) {
	if ( function_exists( 'wpex_sanitize_utl_font_size' ) ) {
		$class[] = wpex_sanitize_utl_font_size( $font_size );
	} else {
		$class[] = 'wpex-text-' . sanitize_html_class( $font_size );
	}
}

if ( $shadow ) {
	$class[] = 'wpex-' . sanitize_html_class( $shadow );
}

if ( $visibility ) {
	$class[] = sanitize_html_class( $visibility );
}

if ( $css_animation = vcex_get_css_animation( $css_animation ) ) {
	$class[] = $css_animation;
}

if ( $el_class ) {
	$class[] = vcex_get_extra_class( $el_class );
}

$class = vcex_parse_shortcode_classes( implode( ' ', $class ), $shortcode_tag, $atts );

// Begin output
$output = '<div class="' . esc_attr( trim( $class ) ) . '">';

	if ( $heading ) {
		$output .= '<h4>' . do_shortcode( wp_kses_post( $heading ) ) . '</h4>';
	}

	$output .= do_shortcode( wp_kses_post( $content ) );

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;