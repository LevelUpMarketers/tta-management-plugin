<?php
/**
 * vcex_term_description shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_term_description';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

if ( vcex_vc_is_inline() ) {
	$term_description = esc_html( 'Term description placeholder for the live builder.', 'total' );
} else {
	$term_description = term_description();
}

if ( empty( $term_description ) ) {
	return;
}

$classes = array(
	'vcex-module',
	'vcex-term-description',
	'wpex-last-mb-0',
);

if ( $text_align ) {
	$classes[] = 'wpex-text-' . sanitize_html_class( $text_align );
}

if ( $bottom_margin ) {
	$classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $visibility ) {
	$classes[] = sanitize_html_class( $visibility );
}

if ( $css_animation = vcex_get_css_animation( $css_animation ) ) {
	$classes[] = $css_animation;
}

if ( $el_class ) {
	$classes[] = vcex_get_extra_class( $el_class );
}


if ( $css ) {
	$classes[] = vcex_vc_shortcode_custom_css_class( $css );
}

$classes = vcex_parse_shortcode_classes( implode( ' ', $classes ), $shortcode_tag, $atts );

if ( $font_family ) {
	vcex_enqueue_font( $font_family );
}

$inline_style = vcex_inline_style( array(
	'color'       => $color,
	'font_family' => $font_family,
	'font_size'   => $font_size,
), true );

$data = '';
if ( $rfont_size = vcex_get_responsive_font_size_data( $font_size ) ) {
	$data = "data-wpex-rcss='" . htmlspecialchars( wp_json_encode( array( 'font-size' => $rfont_size ) ) ) . "'";
}

// Begin output
$output = '<div class="' . esc_attr( trim( $classes ) ) . '"' . $inline_style . $data . '>';

	$output .= do_shortcode( wp_kses_post( $term_description ) );

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;