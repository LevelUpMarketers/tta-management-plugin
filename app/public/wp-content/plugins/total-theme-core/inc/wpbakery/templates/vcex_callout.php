<?php
/**
 * vcex_callout shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_callout';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Checks & sanitization
$is_full      = '100-100' == $layout ? true : false;
$has_button   = ( $button_url && $button_text ) ? true : false;
$breakpoint   = ( $breakpoint && ! $is_full ) ? $breakpoint : 'md';
$border_width = ! empty( $border_width ) ? absint( $border_width ) : 1;

// Get layout
if ( $layout && in_array( $layout, array( '75-25', '60-40', '50-50', '100-100' ) ) ) {
	$layout = explode( '-', $layout );
	$content_width = $layout[0];
	$button_width  = $layout[1];
} else {
	$content_width = '75';
	$button_width  = '25';
}

// Add Classes
$wrap_classes = array(
	'vcex-module',
	'vcex-callout',
);

if ( $wrap_classes ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $style && 'none' !== $style ) {
	$wrap_classes[] = 'wpex-' . sanitize_html_class( $style );
}

if ( $is_full ) {
	$wrap_classes[] = 'wpex-text-center';
}

if ( $border_radius ) {
	$wrap_classes[] = 'wpex-' . sanitize_html_class( $border_radius );
}

if ( $border_width > 1 ) {
	$wrap_classes[] = 'wpex-border-' . sanitize_html_class( $border_width );
}

if ( $shadow ) {
	$wrap_classes[] = 'wpex-' . sanitize_html_class( $shadow );
	if ( 'none' === $style || ! $style ) {
		$wrap_classes[] = 'wpex-p-20';
	}
}

if ( $has_button ) {
	$wrap_classes[] = 'with-button';
	if ( ! $is_full ) {
		$wrap_classes[] = 'wpex-text-center';
		$wrap_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-text-initial';
		$wrap_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-flex';
		$wrap_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-items-center';
	}
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

if ( $css ) {
	$wrap_classes[] = vcex_vc_shortcode_custom_css_class( $css );
}

$wrap_classes[] = 'wpex-clr';

$wrap_classes = implode( ' ', $wrap_classes );

$wrap_classes = vcex_parse_shortcode_classes( $wrap_classes, $shortcode_tag, $atts );

$wrap_inline_style = vcex_inline_style( array(
	'border_color' => $border_color,
) );

$output = '';

$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . $wrap_inline_style . '>';

	// Display content
	if ( $content ) {

		$content_classes = array(
			'vcex-callout-caption',
			'wpex-text-md',
			'wpex-last-mb-0',
		);

		if ( $has_button ) {

			$content_classes[] = 'wpex-mb-20';

			if ( ! $is_full ) {
				$content_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-w-' . sanitize_html_class( $content_width );
				$content_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-pr-20';
				$content_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-mb-0';
			}

		}

		$content_classes[] = 'wpex-clr';

		$content_inline_style = vcex_inline_style( array(
			'color'          => $content_color,
			'font_size'      => $content_font_size,
			'letter_spacing' => $content_letter_spacing,
			'font_family'    => $content_font_family,
		) );

		if ( $content_font_family ) {
			vcex_enqueue_font( $content_font_family );
		}

		$output .= '<div class="' . esc_attr( implode( ' ', $content_classes ) ) . '"' . $content_inline_style . '>';

			$output .= vcex_the_content( $content );

		$output .= '</div>';

	}

	// Display button
	if ( $has_button ) {

		$button_wrap_classes = array(
			'vcex-callout-button',
		);

		if ( $is_full ) {
			$button_align = $button_align ? $button_align : 'center';
			$button_wrap_classes[] = 'wpex-text-' . sanitize_html_class( $button_align );
		} else {
			$button_align = $button_align ? $button_align : 'right';
			$button_wrap_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-w-' . sanitize_html_class( $button_width );
			$button_wrap_classes[] = 'wpex-' . sanitize_html_class( $breakpoint ) . '-text-' . sanitize_html_class( $button_align );
		}

		$output .= '<div class="' . esc_attr( implode( ' ', $button_wrap_classes ) ) . '">';

			$button_inline_style = vcex_inline_style( array(
				'color'          => $button_custom_color,
				'background'     => $button_custom_background,
				'padding'        => $button_padding,
				'border_radius'  => $button_border_radius,
				'font_size'      => $button_font_size,
				'letter_spacing' => $button_letter_spacing,
				'font_family'    => $button_font_family,
				'font_weight'    => $button_font_weight,
			), false );

			if ( $button_font_family ) {
				vcex_enqueue_font( $button_font_family );
			}

			$button_attrs = array(
				'href'   => esc_url( do_shortcode( $button_url ) ),
				'title'  => esc_attr( do_shortcode( $button_text ) ),
				'target' => $button_target,
				'rel'    => $button_rel,
				'style'  => $button_inline_style,
			);

			$button_classes = array( vcex_get_button_classes( $button_style, $button_color ) );

			if ( 'local' == $button_target ) {
				$button_classes[] = 'local-scroll-link';
			}

			if ( 'true' == $button_full_width ) {
				$button_classes[] = 'full-width';
			}

			$button_hover_data = array();
			if ( $button_custom_hover_background ) {
				$button_hover_data[ 'background' ] = esc_attr( $button_custom_hover_background );
			}
			if ( $button_custom_hover_color ) {
				$button_hover_data[ 'color' ] = esc_attr( $button_custom_hover_color );
			}
			if ( $button_hover_data ) {
				$button_attrs[ 'data-wpex-hover' ] = htmlspecialchars( wp_json_encode( $button_hover_data ) );
			}

			$button_classes[] = 'wpex-text-center';
			$button_classes[] = 'wpex-text-base';

			$button_attrs['class'] = $button_classes;

			$output .= '<a' . vcex_parse_html_attributes( $button_attrs ) . '>';

				$icon_left  = vcex_get_icon_class( $atts, 'button_icon_left' );
				$icon_right = vcex_get_icon_class( $atts, 'button_icon_right' );

				if ( $icon_left ) {
					vcex_enqueue_icon_font( $icon_type, $icon_left );
					$output .= '<span class="theme-button-icon-left ' . esc_attr( $icon_left ) . '" aria-hidden="true"></span>';
				}

				$output .= wp_kses_post( do_shortcode( $button_text ) );

				if ( $icon_right ) {
					vcex_enqueue_icon_font( $icon_type, $icon_right );
					$output .= '<span class="theme-button-icon-right ' . esc_attr( $icon_right ) . '" aria-hidden="true"></span>';
				}

			$output .= '</a>';

		$output .= '</div>';

	}

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
