<?php
/**
 * vcex_icon shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_icon';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// FALLBACK VARS => NEVER REMOVE !!!
$padding     = isset( $atts['padding'] ) ? $atts['padding'] : '';
$style       = isset( $atts['style'] ) ? $atts['style'] : '';
$link_title  = isset( $atts['link_title'] ) ? $atts['link_title'] : '';
$link_target = isset( $atts['link_target'] ) ? $atts['link_target'] : '';

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Sanitize data & declare vars
$output          = '';
$icon            = vcex_get_icon_class( $atts );
$data_attributes = '';

// Icon Classes
$wrap_classes = array(
	'vcex-module',
	'vcex-icon',
	'wpex-no-underline', // never allow underline on icons
	'wpex-clr'
);

if ( $style ) {
	$wrap_classes[] = 'vcex-icon-' . sanitize_html_class( $style );
}

if ( $size ) {
	$wrap_classes[] = 'vcex-icon-' . sanitize_html_class( $size );
}

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $float ) {

	// For RTL left is right and right is left (this is legacy as the icons always worked like this)
	$float = vcex_parse_direction( $float );

	switch ( $float ) {
		case 'left':
			$wrap_classes[] = 'wpex-float-left';
			$wrap_classes[] = 'wpex-mr-20';
			break;
		case 'center':
			$wrap_classes[] = 'wpex-float-none';
			$wrap_classes[] = 'wpex-m-auto';
			if ( empty( $align ) ) {
				$wrap_classes[] = 'wpex-text-center';
			}
			break;
		case 'right':
			$wrap_classes[] = 'wpex-float-right';
			$wrap_classes[] = 'wpex-ml-20';
			break;
	}

} elseif ( $align ) {
	$wrap_classes[] = 'wpex-text-' . sanitize_html_class( $align );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = trim( vcex_get_css_animation( $css_animation ) );
}

if ( $el_class ) {
	$wrap_classes[] = vcex_get_extra_class( $el_class );
}

// Apply core VC filter to classes
$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Open link wrapper
$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . '>';

	// Open custom link
	if ( $link_url ) {

		$link_data = vcex_build_link( $link_url );
		$link_url  = isset( $link_data['url'] ) ? $link_data['url'] : $link_url;
		$link_url  = esc_url( do_shortcode( $link_url ) );

		if ( $link_url ) {

			$link_attrs  = array(
				'href'  => $link_url,
				'class' => array( 'vcex-icon-link' ),
			);
			$link_attrs['title']  = isset( $link_data['title'] ) ? $link_data['title'] : '';
			$link_attrs['target'] = isset( $link_data['target'] ) ? $link_data['target'] : '';
			$link_attrs['rel']    = isset( $link_data['rel'] ) ? $link_data['rel'] : '';

			if ( 'true' == $link_local_scroll || 'local' == $link_target ) {
				unset( $link_attrs['target'] );
				unset( $link_attrs['rel'] );
				$link_attrs['class'][] = 'local-scroll-link';
			}

			$output .= '<a' . vcex_parse_html_attributes( $link_attrs ) . '>';

		}

	}

	// Icon classes
	$icon_wrap_attrs = array(
		'class' => array(
			'vcex-icon-wrap',
			'wpex-leading-none',
		),
	);

	if ( 'true' === $color_accent && ! $color ) {
		$icon_wrap_attrs['class'][] = 'wpex-text-accent';
	}

	if ( 'true' === $background_accent && ! $background ) {
		$icon_wrap_attrs['class'][] = 'wpex-bg-accent';
	}

	if ( $background || 'true' === $background_accent ) {
		$icon_wrap_attrs['class'][] = 'wpex-inline-block'; // force inline block
		if ( empty( $height ) && empty( $width ) ) {
			$icon_wrap_attrs['class'][] = 'wpex-p-20';
		}
	}

	if ( $hover_animation ) {
		$icon_wrap_attrs['class'][] = vcex_hover_animation_class( $hover_animation );
		vcex_enque_style( 'hover-animations' );
	}

	if ( $border ) {
		$icon_wrap_attrs['class'][] = 'wpex-box-content'; // prevent issues when adding borders to icons
	}

	if ( ! $hover_animation && ( $background_hover || $color_hover ) ) {
		$icon_wrap_attrs['class'][] = 'wpex-transition-colors';
		$icon_wrap_attrs['class'][] = 'wpex-duration-200';
	}

	// Icon hovers
	$hover_data = array();

	if ( $background_hover ) {
		$hover_data['background'] = esc_attr( $background_hover );
	}

	if ( $color_hover ) {
		$hover_data['color'] = esc_attr( $color_hover );
	}

	if ( $hover_data ) {
		$icon_wrap_attrs['data-wpex-hover'] = htmlspecialchars( wp_json_encode( $hover_data ) );
	}

	// Add Style
	$icon_wrap_attrs['style'] = vcex_inline_style( array(
		'font_size'        => $custom_size,
		'color'            => $color,
		'padding'          => $padding,
		'background_color' => $background,
		'border_radius'    => $border_radius,
		'height'           => $height,
		'line_height'      => vcex_validate_px( $height, 'px' ),
		'width'            => $width,
		'border'           => $border,
	), false );

	// Open Icon div
	$output .= '<div' . vcex_parse_html_attributes( $icon_wrap_attrs ) . '>';

		// Display alternative icon
		if ( $icon_alternative_classes ) {

			$output .= '<span class="' . esc_attr( do_shortcode( $icon_alternative_classes ) ) . '"></span>';

		// Display theme supported icon
		} else {

			vcex_enqueue_icon_font( $icon_type, $icon );

			$output .= '<span class="' . esc_attr( $icon ) . '"></span>';

		}

	// Close Icon Div
	$output .= '</div>';

	// Close link tag
	if ( $link_url ) {

		$output .= '</a>';

	}

$output .= '</div>';

if ( $float && vcex_vc_is_inline() ) {
	$output .= '<div class="wpex-clear"></div>';
}

// @codingStandardsIgnoreLine
echo $output;
