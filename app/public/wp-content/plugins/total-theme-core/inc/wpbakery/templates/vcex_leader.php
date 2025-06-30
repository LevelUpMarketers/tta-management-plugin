<?php
/**
 * vcex_leader shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_leader';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

$leaders = (array) vcex_vc_param_group_parse_atts( $leaders );

if ( ! $leaders ) {
	return;
}

$wrap_atrrs = array(
	'class' => '',
);

$wrap_classes = array(
	'vcex-module',
	'vcex-leader',
	'vcex-leader-' . sanitize_html_class( $style ),
	'wpex-overflow-hidden',
	'wpex-mx-auto',
	'wpex-max-w-100',
	'wpex-last-mb-0',
	'wpex-clr',
);

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( 'true' == $responsive && vcex_is_layout_responsive() ) {
	$wrap_classes[] = 'vcex-responsive';
}

if ( $el_class ) {
	$wrap_classes[] = vcex_get_extra_class( $el_class );
}

if ( $responsive_data = vcex_get_module_responsive_data( $atts ) ) {
	$wrap_atrrs['data-wpex-rcss'] = $responsive_data;
}

$wrap_atrrs['style'] = vcex_inline_style( array(
	'color'     => $color,
	'font_size' => $font_size,
), false );

// Add filters to the module classes
$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

$wrap_atrrs['class'] = $wrap_classes;

// Begin output
$output = '<div' . vcex_parse_html_attributes( $wrap_atrrs ) . '>';

// Label typography
$label_typo = vcex_inline_style( array(
	'color'       => $label_color,
	'font_weight' => $label_font_weight,
	'font_style'  => $label_font_style,
	'font_family' => $label_font_family,
	'background'  => $background,
) );

if ( $label_font_family ) {
	vcex_enqueue_font( $label_font_family );
}

// value typography
$value_typo = vcex_inline_style( array(
	'color'       => $value_color,
	'font_weight' => $value_font_weight,
	'font_style'  => $value_font_style,
	'font_family' => $value_font_family,
	'background'  => $background,
) );

if ( $value_font_family ) {
	vcex_enqueue_font( $value_font_family );
}

// Individual item classes
$leader_classes = array(
	'vcex-leader-item',
	'wpex-clr',
);

if ( $spacing ) {
	$leader_classes[] = 'wpex-mb-' . absint( $spacing );
}

if ( $css_animation && 'none' != $css_animation ) {
	$leader_classes[] = trim( vcex_get_css_animation( $css_animation ) );
}

// Loop through leaders and output it's content
foreach ( $leaders as $leader ) {

	$label = isset( $leader['label'] ) ? $leader['label'] : esc_html__( 'Label', 'total' );
	$value = isset( $leader['value'] ) ? $leader['value'] : esc_html__( 'Value', 'total' );

	$output .= '<div class="' . esc_attr( implode( ' ', $leader_classes ) ) . '">';

		$first_class = array(
			'vcex-first',
			'wpex-pr-5',
			'wpex-bg-white',
			'wpex-relative',
			'wpex-z-2',
		);

		$output .= '<span class="' . esc_attr( implode( ' ', $first_class ) ) . '"' . $label_typo . '>' . do_shortcode( wp_kses_post( $label ) ) . '</span>';

		if ( $responsive && 'minimal' != $style ) {

			$output .= '<span class="vcex-inner wpex-hidden">...</span>';

		}

		if ( 'Value' != $value ) {

			$last_class = array(
				'vcex-last',
				'wpex-float-right',
				'wpex-pl-5',
				'wpex-bg-white',
				'wpex-relative',
				'wpex-z-2',
			);

			$output .= '<span class="' . esc_attr( implode( ' ', $last_class ) ) . '"' . $value_typo . '>' . do_shortcode( wp_kses_post( $value ) ) . '</span>';

		}

	$output .= '</div>';

}

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
