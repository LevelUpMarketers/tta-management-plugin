<?php
/**
 * Visual Composer Countdown
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 *
 * @todo update to use script template like main example here - http://hilios.github.io/jQuery.countdown/ - this way we can easily add custom classes.
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_countdown';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define vars
$output = '';

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Load js
$this->enqueue_scripts( $atts );

// Get end date data
$end_year  = ! empty( $atts['end_year'] ) ? intval( $atts['end_year'] ) : date( 'Y' );
$end_month = intval( $atts['end_month'] );
$end_day   = intval( $atts['end_day'] );

// Sanitize data to make sure input is not crazy
if ( $end_month > 12 ) {
	$end_month = '';
}
if ( $end_day > 31 ) {
	$end_day = '';
}

// Define end date
if ( $end_year && $end_month && $end_day ) {
	$end_date = $end_year . '-' . $end_month . '-' . $end_day;
} else {
	$end_date = '2018-12-15';
}

// Add end time
$atts['end_time'] = $atts['end_time'] ? $atts['end_time'] : '00:00';
$end_date = $end_date . ' ' . esc_html( $atts['end_time'] );

// Make sure date is in correct format
$end_date = date( 'Y-m-d H:i', strtotime( $end_date ) );

// Countdown data
$data = array();
$data['data-countdown'] = $end_date;
$data['data-days']      = $atts['days'] ? $atts['days'] : esc_html__( 'Days', 'total' );
$data['data-hours']     = $atts['hours'] ? $atts['hours'] : esc_html__( 'Hours', 'total' );
$data['data-minutes']   = $atts['minutes'] ? $atts['minutes'] : esc_html__( 'Minutes', 'total' );
$data['data-seconds']   = $atts['seconds'] ? $atts['seconds'] : esc_html__( 'Seconds', 'total' );

if ( $atts['timezone'] ) {
	$data['data-timezone'] = esc_attr( $atts['timezone'] );
}

$data = apply_filters( 'vcex_countdown_data', $data, $atts ); // Apply filters for translations

// Define wrap attributes
$wrap_attrs = array(
	'data' => ''
);

// Main classes
$wrap_classes = array(
	'vcex-module',
	'vcex-countdown-wrap'
);

if ( $atts['bottom_margin'] ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

if ( $atts['css_animation'] && 'none' != $atts['css_animation'] ) {
	$wrap_classes[] = vcex_get_css_animation( $atts['css_animation'] );
}

if ( $atts['el_class'] ) {
	$wrap_classes[] = vcex_get_extra_class( $atts['el_class'] );
}

// Style
$styles = array(
	'color'          => $atts['color'],
	'font_family'    => $atts['font_family'],
	'font_size'      => $atts['font_size'],
	'letter_spacing' => $atts['letter_spacing'],
	'font_weight'    => $atts['font_weight'],
	'text_align'     => $atts['text_align'],
	'line_height'    => $atts['line_height'],
);
if ( $atts['font_family'] ) {
	vcex_enqueue_font( $atts['font_family'] );
}
if ( 'true' == $atts['italic'] ) {
	$styles['font_style'] = 'italic';
}
$wrap_style = vcex_inline_style( $styles, false );

// Responsive styles
if ( $responsive_data = vcex_get_module_responsive_data( $atts ) ) {
	$wrap_attrs['data-wpex-rcss'] = $responsive_data;
}

// Add to attributes
$wrap_attrs['class'] = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );
$wrap_attrs['style'] = $wrap_style;

// Output
$output .= '<div' . vcex_parse_html_attributes( $wrap_attrs ) . '>';

	$output .= '<div class="vcex-countdown wpex-clr"';

		foreach ( $data as $name => $value ) {
			$output .= ' ' . $name . '=' . '"' . esc_attr( $value ) . '"';
		}

	$output .= '>';

	$output .='</div>';

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
