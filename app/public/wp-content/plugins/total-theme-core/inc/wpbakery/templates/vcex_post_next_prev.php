<?php
/**
 * vcex_post_next_prev shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_post_next_prev';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

$prev = $next = $icon_left = $icon_right = $prev_format = $next_format = '';

// Sanitize atts
$in_same_term  = ( 'true' == $atts['in_same_term'] ) ? true : false;
$same_term_tax = $same_term_tax ? $same_term_tax : 'category';

$classes = array(
	'vcex-post-next-prev',
);

if ( 'true' === $expand ) {
	$classes[] = 'wpex-flex';
	$classes[] = 'wpex-justify-between';
}

if ( $align ) {
	$classes[] = 'text' . sanitize_html_class( $align );
}

if ( 'icon' === $link_format ) {
	$classes[] = 'vcex-icon-only';
}

if ( $bottom_margin ) {
	$classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

// Extra classname
if ( $atts['el_class'] ) {
	$classes[] = vcex_get_extra_class( $el_class );
}

// Apply filters to classname
$classes = vcex_parse_shortcode_classes( implode( ' ', $classes ), $shortcode_tag, $atts );

// Inline CSS
$inline_css = vcex_inline_style( array(
	'font_size' => $atts['font_size'],
) );

// Begin output
$output = '<div class="' . esc_attr( $classes ) . '"' . $inline_css . '>';

	// Define icon HTML
	if ( ! empty( $icon_style ) ) {

		// Sanitize icon spacing
		$icon_margin_escaped = $icon_margin ? absint( $icon_margin ) : 10;

		// Define left/right directions for RTL
		$left_dir = vcex_parse_direction( 'left' );
		$right_dir = vcex_parse_direction( 'right' );

		// Sanitize icon style
		$icon_style_escaped = sanitize_html_class( $icon_style );

		// Left Icon
		$icon_left_class = array(
			'ticon',
			'ticon-' . sanitize_html_class( $icon_style ) . '-' . vcex_parse_direction( 'left' ),
		);

		if ( 'icon' !== $link_format ) {
			$icon_left_class[] = 'wpex-mr-' . $icon_margin_escaped;
		}

		$icon_left = '<span class="' . esc_attr( implode( ' ', $icon_left_class ) ) . '"></span>';

		// Right Icon
		$icon_right_class = array(
			'ticon',
			'ticon-' . sanitize_html_class( $icon_style ) . '-' . vcex_parse_direction( 'right' ),
		);

		if ( 'icon' !== $link_format ) {
			$icon_right_class[] = 'wpex-ml-' . $icon_margin_escaped;
		}

		$icon_right = '<span class="' . esc_attr( implode( ' ', $icon_right_class ) ) . '"></span>';

	}

	// Get correct button class
	$button_class = vcex_get_button_classes( $atts['button_style'], $atts['button_color'] );

	// Get button inline style
	$button_style_escaped = vcex_inline_style( array(
		'min_width' => $button_min_width,
	) );

	// Display previous link
	if ( 'true' == $previous_link ) {

		$get_prev = get_previous_post( $in_same_term, '', $same_term_tax );

		if ( $get_prev ) {

			switch ( $link_format ) {
				case 'icon':
					$prev_format_escaped = ( 'true' == $reverse_order ) ? $icon_right : $icon_left;
					break;
				case 'title':
					$title = get_the_title( $get_prev->ID );
					$prev_format_escaped = ( 'true' == $reverse_order ) ? $title . $icon_right : $icon_left . $title;
					break;
				case 'custom':
					$prev_format_escaped = ( 'true' == $reverse_order ) ? esc_html( $atts['previous_link_custom_text'] ) . $icon_right : $icon_left . esc_html( $atts['previous_link_custom_text'] ) ;
					break;
				default :
					$prev_format_escaped = '';
					break;
			}

			if ( $prev_format_escaped ) {

				$prev = '<a href="' . esc_url( get_permalink( $get_prev->ID ) ) . '" class="' . esc_attr( $button_class ) . ' wpex-text-center wpex-max-w-100"' . $button_style_escaped . '>' . $prev_format_escaped . '</a>';

			}

		}

	}

	if ( 'true' == $next_link ) {

		$get_next = get_next_post( $in_same_term, '', $same_term_tax );

		if ( $get_next ) {

			switch ( $link_format ) {

				case 'icon':
					$next_format_escaped = ( 'true' == $reverse_order ) ? $icon_left : $icon_right;
					break;
				case 'title':
					$title = get_the_title( $get_next->ID );
					$next_format_escaped = ( 'true' == $reverse_order ) ? $icon_left . $title : $title . $icon_right;
					break;
				case 'custom':
					$next_format_escaped = ( 'true' == $reverse_order ) ? $icon_left . esc_html( $atts['next_link_custom_text'] ) : esc_html( $atts['next_link_custom_text'] ) . $icon_right;
					break;
				default:
					$next_format_escaped = '';
					break;

			}

			if ( $next_format_escaped ) {

				$next = '<a href="' . esc_url( get_permalink( $get_next->ID ) ) . '" class="' . esc_attr( $button_class ) . ' wpex-text-center wpex-max-w-100"' . $button_style_escaped . '>' . $next_format_escaped . '</a>';

			}

		}

	}

	// Sanitize col spacing
	$col_spacing = $spacing ? absint( $spacing ) : 5;

	// Col Classes
	$first_col_class = 'vcex-col wpex-inline-block wpex-mr-' . sanitize_html_class( $col_spacing );
	$second_col_class = 'vcex-col wpex-inline-block wpex-ml-' . sanitize_html_class( $col_spacing );

	if ( 'true' == $reverse_order ) {
		$output .= '<div class="' . esc_attr( $first_col_class ) . '">' . $next .'</div>';
		$output .= '<div class="' . esc_attr( $second_col_class ) . '">' . $prev .'</div>';
	} else {
		$output .= '<div class="' . esc_attr( $first_col_class ) . '">' . $prev .'</div>';
		$output .= '<div class="' . esc_attr( $second_col_class ) . '">' . $next .'</div>';
	}

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
