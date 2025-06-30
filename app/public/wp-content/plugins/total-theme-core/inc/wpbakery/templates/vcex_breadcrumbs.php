<?php
/**
 * vcex_breadcrumbs shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_breadcrumbs';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Return if crumbs class doesn't exist
if ( ! class_exists( 'WPEX_Breadcrumbs' ) ) {
	return;
}

// Define crumbs
$crumbs = '';

// Custom crumbs check.
$is_custom = false;

// Yoast Crumbs
if ( function_exists( 'yoast_breadcrumb' )
	&& current_theme_supports( 'yoast-seo-breadcrumbs' )
	&& get_theme_mod( 'enable_yoast_breadcrumbs', true )
) {
	$crumbs = yoast_breadcrumb( '', '', false );
	$is_custom = true;
}

// Custom breadcrumbs
elseif ( $custom_breadcrumbs = apply_filters( 'wpex_custom_breadcrumbs', null ) ) {
	$crumbs = wp_kses_post( $custom_breadcrumbs );
	$is_custom = true;
} elseif ( class_exists( 'WPEX_Breadcrumbs' ) ) {
	// Generate breadcrumbs (stores trail in $ouput var)
	$crumbs = new WPEX_Breadcrumbs();
	$crumbs = $crumbs->generate_crumbs(); // needs to generate it's own to prevent issues with theme stuff
}

// Return if no crumbs
if ( ! $crumbs ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Get inline styles
$inline_style = vcex_inline_style( array(
	'color'       => $atts['color'],
	'font_size'   => $atts['font_size'],
	'font_family' => $atts['font_family'],
	'text_align'  => $atts['align'],
), false );

// Load custom font
if ( ! empty( $atts['font_family'] ) ) {
	vcex_enqueue_font( $atts['font_family'] );
}

// Define wrapper attributes
$wrap_attrs = array(
	'class' => 'vcex-breadcrumbs',
	'style' => $inline_style
);

// Bottom margin
if ( $atts['bottom_margin'] ) {
	$wrap_attrs['class'] .= ' ' . vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

// Extra classname
if ( $atts['el_class'] ) {
	$wrap_attrs['class'] .= ' ' . vcex_get_extra_class( $atts['el_class'] );
}

// Visibility
if ( $atts['visibility'] ) {
	$wrap_attrs['class'] .= ' ' . sanitize_html_class( $atts['visibility'] );
}

// Responsive settings
if ( $responsive_data = vcex_get_module_responsive_data( $atts['font_size'], 'font_size' ) ) {
	$wrap_attrs['data-wpex-rcss'] = $responsive_data;
}

// Get aria tag
if ( function_exists( 'wpex_get_aria_landmark' ) ) {
	$aria = wpex_get_aria_landmark( 'breadcrumbs' );
} else {
	$aria = '';
}

if ( $is_custom ) {
	$schema = '';
} else {
	$schema = ' itemscope itemtype="http://schema.org/BreadcrumbList"';
}

// Display breadcrumbs
echo '<nav' . vcex_parse_html_attributes( $wrap_attrs ) . $aria . $schema . '>' . $crumbs . '</nav>';