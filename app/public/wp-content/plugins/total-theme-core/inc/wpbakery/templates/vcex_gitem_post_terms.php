<?php
/**
 * vcex_gitem_post_terms shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

// Taxonomy is required
if ( ! $atts['taxonomy'] ) {
	return;
}

// Load Google Fonts if needed
if ( $atts['button_font_family'] ) {
	vcex_enqueue_font( $atts['button_font_family'] );
}

// Get terms for live preview
if ( 'vc_grid_item' == get_post_type( $post->ID ) ) {

	$terms = get_terms( $atts['taxonomy'], array(
		'hide_empty' => false,
		'number'     => 4,
	) );

}

// Get terms for live site
else {

	$query_args = array(
		'order'   => $atts['order'],
		'orderby' => $atts['orderby'],
		'fields'  => 'all',
	);

	$query_args = apply_filters( 'vcex_post_terms_query_args', $query_args );

	$terms = wp_get_post_terms( get_the_ID(), $atts['taxonomy'], $query_args );
}

// Terms needed
if ( ! $terms || is_wp_error( $terms ) ) {
	return;
}

// Wrap classes
$wrap_classes = 'vcex-post-terms wpex-clr';

if ( $atts['visibility'] ) {

	$wrap_classes .= ' ' . sanitize_html_class( $atts['visibility'] );
}

if ( $atts['classes'] ) {
	$wrap_classes .= ' ' . vcex_get_extra_class( $atts['classes'] );
}

if ( 'center' == $atts['button_align'] ) {
	$wrap_classes .= ' textcenter';
}

if ( ! empty( $atts['css'] ) ) {
	$wrap_classes .= ' ' . vcex_vc_shortcode_custom_css_class( $atts['css'] );
}

// Get button classes
$button_classes = vcex_get_button_classes( $atts['button_style'], $atts['button_color_style'], $atts['button_size'], $atts['button_align'] );
if ( ! empty( $atts['css_animation'] ) && 'none' != $atts['css_animation'] ) {
	$button_classes .= ' ' . vcex_get_css_animation( $atts['css_animation'] );
}

// Button Style
$button_style = vcex_inline_style( array(
	'margin'         => $atts['button_margin'],
	'color'          => $atts['button_color'],
	'background'     => $atts['button_background'],
	'padding'        => $atts['button_padding'],
	'font_size'      => $atts['button_font_size'],
	'font_weight'    => $atts['button_font_weight'],
	'border_radius'  => $atts['button_border_radius'],
	'text_transform' => $atts['button_text_transform'],
) );

// Button data
$button_hover_data = array();
$button_data = '';
if ( $atts['button_hover_background'] ) {
	$button_hover_data['background'] = $atts['button_hover_background'];
}
if ( $atts['button_hover_color'] ) {
	$button_hover_data['color'] = $atts['button_hover_color'];
}
if ( $button_hover_data ) {
	$button_data = " data-wpex-hover='" . htmlspecialchars( wp_json_encode( $button_hover_data ) ) . "'";
}

// Get excluded terms
if ( $atts['exclude_terms'] ) {
	$exclude_terms = preg_split( '/\,[\s]*/', $atts['exclude_terms'] );
} else {
	$exclude_terms = array();
}

// Define output var
$output = '';

// Get total count
$tcount = count( $terms );

// VC filter
$wrap_classes = vcex_parse_shortcode_classes( $wrap_classes, 'vcex_post_terms', $atts );

// Begin output
$output .= '<div class="' . esc_attr( $wrap_classes ) . '">';

	// Loop through terms
	foreach ( $terms as $term ) :

		// Skip excluded terms
		if ( in_array( $term->slug, $exclude_terms ) ) {
			continue;
		}

		// Open link if enabled
		if ( 'true' == $atts['archive_link'] ) {

			$output .= '<a href="' . get_term_link( $term, $atts['taxonomy'] ) . '" class="' . esc_attr( $button_classes ) . '"' . $button_style . $button_data . '>';

		}

		// Span
		else {

			$output .= '<span class="' . esc_attr( $button_classes ) . '"' . $button_style . $button_data . '>';

		}

		// Display title
		$output .= $term->name;

		// Close link if enabled
		if ( 'true' == $atts['archive_link'] ) {

			$output .= '</a>';

		}

		// Close span
		else {
			$output .= '</span>';
		}

	endforeach;

// Close main wrapper
$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
