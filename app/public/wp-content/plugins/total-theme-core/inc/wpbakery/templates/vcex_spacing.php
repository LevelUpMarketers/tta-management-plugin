<?php
/**
 * vcex_spacing shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_spacing';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Core class
$classes = array(
	'vcex-spacing',
	'wpex-w-100',
	'wpex-clear',
);

// Custom Class
if ( $class ) {
    $classes[] = vcex_get_extra_class( $class );
}

// Visiblity Class
if ( $visibility ) {
    $classes[] = $visibility;
}

// Front-end composer class
if ( vcex_vc_is_inline() ) {
    $classes[] = 'vc-spacing-shortcode';
}

// Apply filters
$classes = vcex_parse_shortcode_classes( implode( ' ', $classes ), $shortcode_tag, $atts );

// Sanitize size - supports %, em, vh and px
if ( ( strpos( $size, '%' ) !== false )
	|| ( strpos( $size, 'em' ) !== false )
	|| ( strpos( $size, 'vh' ) !== false )
) {
	$size = wp_strip_all_tags( $size );
} elseif ( $size = floatval( $size ) ) {
	$size = wp_strip_all_tags( $size ) . 'px';
}

// Echo output
echo '<div class="' . esc_attr( $classes ) . '" style="height:' . esc_attr( $size ) . '"></div>';