<?php
/**
 * vcex_animated_text shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_animated_text';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

extract( $atts );

$strings = (array) vcex_vc_param_group_parse_atts( $strings );

if ( ! $strings ) {
	return;
}

$this->enqueue_scripts();

$wrap_classes = array(
	'vcex-animated-text',
	'vcex-module',
	'wpex-m-0',
	'wpex-text-xl',
	'wpex-text-gray-900',
	'wpex-font-semibold',
	'wpex-leading-none',
	'vcex-typed-text-wrap',
);

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $el_class = vcex_get_extra_class( $el_class ) ) {
	$wrap_classes[] = $el_class;
}

if ( $visibility ) {
	$wrap_classes[] = $visibility;
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $css ) {
	$wrap_classes[] = vcex_vc_shortcode_custom_css_class( $css );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

$data_attr = '';

$data = array();
foreach ( $strings as $string ) {
	if ( isset( $string['text'] ) ) {
		$data[] = esc_html( $string['text'] );
	}
}

$settings = array(
	'typeSpeed'  => $speed ? intval( $speed ) : '40',
	'loop'       => vcex_validate_boolean( $loop ),
	'showCursor' => vcex_validate_boolean( $type_cursor ),
	'backDelay'  => $back_delay ? intval( $back_delay ) : '0',
	'backSpeed'  => $back_speed ? intval( $back_speed ) : '0',
	'startDelay' => $start_delay ? intval( $start_delay ) : '0',
);

$inline_style = vcex_inline_style( array(
	'color'       => $color,
	'font_size'   => $font_size,
	'font_weight' => $font_weight,
	'font_style'  => $font_style,
	'font_family' => $font_family,
	'text_align'  => $text_align,
) );

$typed_inline_style = vcex_inline_style( array(
	'color'           => $animated_color,
	'font_weight'     => $animated_font_weight,
	'font_style'      => $animated_font_style,
	'font_family'     => $animated_font_family,
	'text_decoration' => $animated_text_decoration,
	'width'           => $animated_span_width,
	'text_align'      => $animated_text_align,
) );

// Get responsive data
if ( $responsive_data = vcex_get_module_responsive_data( $atts ) ) {
	$data_attr .= ' ' . $responsive_data;
}

$tag_escaped = $tag ? tag_escape( $tag ) : 'div';

// Output Shortcode
$output = '<' . $tag_escaped . ' class="' . esc_attr( $wrap_classes ) . '"' . $inline_style . $data_attr . '>';

	if ( 'true' == $static_text && $static_before ) {
		$output .= '<span class="vcex-before">' . do_shortcode( wp_kses_post( $static_before ) ) . '</span> ';
	}

	if ( ( $animated_css || $typed_inline_style ) ) {
		$animated_css = $animated_css ? ' ' . vcex_vc_shortcode_custom_css_class( $animated_css ) : '';
		$output .= '<span class="vcex-typed-text-css wpex-inline-block wpex-max-w-100' . $animated_css . '"' . $typed_inline_style . '>';
	}

	$tmp_data = array();
	foreach ( $data as $val ) {
		$tmp_data[] = do_shortcode( $val );
	}
	$data = $tmp_data;

	$output .= '<span class="screen-reader-text">';

		foreach ( $data as $string ) {
			$output .= '<span>' . esc_html( do_shortcode( $string ) ) . '</span>';
		}

	$output .= '</span>';

	$output .= '<span class="vcex-ph wpex-inline-block wpex-invisible"></span>'; // Add empty span 1px wide to prevent bouce

	$output .= '<span class="vcex-typed-text" data-settings="' . htmlspecialchars( wp_json_encode( $settings ) ) . '" data-strings="' . htmlspecialchars( wp_json_encode( $data ) ) . '"></span>';

	if ( ( $animated_css || $typed_inline_style ) ) {
		$output .= '</span>';
	}

	if ( 'true' == $static_text && $static_after ) {
		$output .= ' <span class="vcex-after">' . do_shortcode( wp_kses_post( $static_after ) ) . '</span>';
	}

$output .= '</' . $tag_escaped . '>';

// @codingStandardsIgnoreLine
echo $output;