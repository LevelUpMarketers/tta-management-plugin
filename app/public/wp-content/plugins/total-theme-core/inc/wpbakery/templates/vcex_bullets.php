<?php
/**
 * vcex_bullets shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.3
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_bullets';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Return if no content
if ( empty( $content ) ) {
	return;
}

// Define output
$output = '';

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Check if icon is enabled
$has_icon = isset( $atts['has_icon'] ) && 'true' == $atts['has_icon'] ? true : false;

// Define wrap attributes
$wrap_attrs = array(
	'id'   => vcex_get_unique_id( $atts['unique_id'] ),
	'data' => '',
);

// Wrap classes
$wrap_classes = array(
	'vcex-module',
	'vcex-bullets',
);

if ( $atts['bottom_margin'] ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

if ( $atts['text_align'] && in_array( $atts['text_align'], array( 'left', 'center', 'right' ) ) ) {
	$wrap_classes[] = 'wpex-text-' . sanitize_html_class( $atts['text_align'] );
}

if ( $atts['css_animation'] && 'none' != $atts['css_animation'] ) {
	$wrap_classes[] = vcex_get_css_animation( $atts['css_animation'] );
}

if ( $has_icon ) {

	// Pre-defined bullet styles
	if ( $atts['style'] && ! $atts['icon_type'] ) {
		$wrap_classes[] = 'vcex-bullets-' . sanitize_html_class( $atts['style'] );
	}

	// Custom Icon
	elseif ( $icon = vcex_get_icon_class( $atts, 'icon' )  ) {

		// Enqueue icon font
		vcex_enqueue_icon_font( $atts['icon_type'], $icon );

		// Icon inline style
		$icon_style = vcex_inline_style( array(
			'color' => $atts['icon_color']
		) );

		// Icon HTML
		$add_icon = '<div class="vcex-bullets-ci-wrap wpex-inline-flex"><span class="vcex-icon-wrap wpex-mr-10"><span class="vcex-icon '. $icon .'" '. $icon_style .' aria-hidden="true"></span></span><div class="vcex-content wpex-flex-grow">';

		// Standard bullets search/replace
		$content = str_replace( '<li>', '<li>' . $add_icon, $content );

		// Fix bugs with inline center align (lots of customers centered the bullets before align option was added)
		$content = str_replace( '<li style="text-align:center">', '<li style="text-align:center;">', $content );
		$content = str_replace( '<li style="text-align: center">', '<li style="text-align:center;">', $content );
		$content = str_replace( '<li style="text-align: center;">', '<li style="text-align:center;">', $content );
		$content = str_replace( '<li style="text-align:center;">', '<li style="text-align:center;">' . $add_icon, $content );

		// Close elements
		$content = str_replace( '</li>', '</div></div></li>', $content );

		// Add custom icon wrap class
		$wrap_classes[] = 'custom-icon';
	}

} else {
	$wrap_classes[] = 'vcex-bullets-ni';
}

// Wrap Style
$wrap_attrs['style'] = vcex_inline_style( array(
	'color'          => $atts[ 'color' ],
	'font_family'    => $atts[ 'font_family' ],
	'font_size'      => $atts[ 'font_size' ],
	'letter_spacing' => $atts[ 'letter_spacing' ],
	'font_weight'    => $atts[ 'font_weight' ],
	'line_height'    => $atts[ 'line_height' ],
) );

// Load custom font
if ( ! empty( $atts['font_family'] ) ) {
	vcex_enqueue_font( $atts['font_family'] );
}

// Responsive settings
if ( $responsive_data = vcex_get_module_responsive_data( $atts['font_size'], 'font_size' ) ) {
	$wrap_attrs['data-wpex-rcss'] = $responsive_data;
}

// Add custom classes last
if ( $atts['classes'] ) {
	$wrap_classes[] = $atts['classes'];
}

// Turn wrap classes into string
$wrap_classes = implode( ' ', $wrap_classes );

// Add filters to wrap classes and add to attributes
$wrap_attrs['class'] = esc_attr( vcex_parse_shortcode_classes( $wrap_classes, $shortcode_tag, $atts ) );

// Output
$output .= '<div' . vcex_parse_html_attributes( $wrap_attrs ) . '>';

	$output .= do_shortcode( wp_kses_post( $content ) );

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;