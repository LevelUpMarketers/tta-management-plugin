<?php
/**
 * vcex_form_shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_form_shortcode';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

if ( ! empty( $atts['cf7_id'] ) ) {
	$content = '[contact-form-7 id="' . intval( $atts['cf7_id'] ) . '"]';
}

// Return if no content (shortcode needed)
if ( empty( $content ) ) {
	return;
}

// Add classes
$classes = array(
	'vcex-module',
	'vcex-form-shortcode',
	'wpex-form',
	'wpex-m-auto',
	'wpex-max-w-100',
);

if ( $atts['style'] ) {

	if ( 'white' == $atts['style'] ) {
		$classes[] = 'light-form';
	} else {
		$classes[] = 'wpex-form-' . sanitize_html_class( $atts['style'] );
	}

}

if ( $atts['bottom_margin'] ) {
	$classes[] = vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

if ( 'true' == $atts['full_width'] ) {
	$classes[] = 'full-width-input';
}

if ( $atts['css'] ) {
	$classes[] = vcex_vc_shortcode_custom_css_class( $atts['css'] );
}

if ( $atts['css_animation'] && 'none' != $atts['css_animation'] ) {
	$classes[] = vcex_get_css_animation( $atts['css_animation'] );
}

$classes = vcex_parse_shortcode_classes( implode( ' ', $classes ), $shortcode_tag, $atts );

// Inline CSS
$inline_style = vcex_inline_style( array(
	'font_size' => $atts['font_size'],
	'width'     => $atts['width'],
) );

// Output
echo '<div class="' . esc_attr( $classes ) . '"'. $inline_style .'>' . do_shortcode( $content ) . '</div>';