<?php
/**
 * vcex_custom_field shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.4
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_custom_field';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// name required
if ( empty( $name ) ) {
	return;
}

$cf_value = '';

if ( shortcode_exists( 'acf' ) ) {
	$cf_value = do_shortcode( '[acf field="' . $name . '" post_id="' . vcex_get_the_ID() . '"]' );
}

if ( empty( $cf_value ) && 0 !== $cf_value ) {
	$cf_value = get_post_meta( vcex_get_the_ID(), $name, true );
	if ( $cf_value && is_string( $cf_value ) ) {
		$cf_value = wp_kses_post( $cf_value );
	}
}

if ( empty( $cf_value ) && 0 !== $cf_value ) {
	$cf_value = $fallback;
}

if ( empty( $cf_value ) || ! is_string( $cf_value ) ) {
	return;
}

// Define classes
$classes = array(
	'vcex-custom-field',
	'wpex-clr',
);

if ( $bottom_margin ) {
	$classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $visibility ) {
	$classes[] = sanitize_html_class( $visibility );
}

if ( $css_animation = vcex_get_css_animation( $css_animation ) ) {
	$classes[] = $css_animation;
}

if ( $align ) {
	$classes[] = 'text' . sanitize_html_class( $align );
}

if ( $el_class = vcex_get_extra_class( $el_class ) ) {
	$classes[] = $el_class;
}

$classes = vcex_parse_shortcode_classes( implode( ' ', $classes ), $shortcode_tag, $atts );

$output = '';

// Wrap attributes
$wrap_attrs = array(
	'class' => esc_attr( $classes ),
);

// Shortcode style
$wrap_attrs['style'] = vcex_inline_style( array(
	'color'       => $color,
	'font_family' => $font_family,
	'font_size'   => $font_size,
), false );

// Get responsive data
if ( $responsive_data = vcex_get_module_responsive_data( $atts ) ) {
	$wrap_attrs['data-wpex-rcss'] = $responsive_data;
}

// Shortcode Output
$output .= '<div' . vcex_parse_html_attributes( $wrap_attrs ) . '>';

	if ( $icon = vcex_get_icon_class( $atts, 'icon' ) ) {

		$icon_class = $icon; // can't use sanitize_html_class because it's multiple classes

		$icon_side_margin = $icon_side_margin ? absint( $icon_side_margin ) : '5';
		$icon_class .= ' wpex-mr-' . sanitize_html_class( $icon_side_margin );

		vcex_enqueue_icon_font( $icon_type, $icon );

		$output .= '<span class="' . esc_attr( $icon_class ) . '" aria-hidden="true"></span> ';

	}

	if ( $before ) {
		$output .= '<span class="vcex-custom-field-before wpex-font-bold">' . esc_html( $before ) . '</span> ';
	}

	$output .= apply_filters( 'vcex_custom_field_value_output', $cf_value, $atts );

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;