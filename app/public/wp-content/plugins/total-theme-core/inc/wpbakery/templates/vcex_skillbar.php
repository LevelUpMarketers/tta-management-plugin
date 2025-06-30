<?php
/**
 * vcex_skillbar shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_skillbar';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Define & sanitize vars
$style = $style ? $style : 'default';
$title_position = ( 'default' == $style ) ? 'inside' : 'outside';

// Define output var
$output = '';

// Get percentage based on source
$source = $source ? $source : 'custom';
if ( 'custom' !== $source ) {
	$percentage = vcex_get_source_value( $source, $atts );
}

// Allow shortcodes for percentage
$percentage = do_shortcode( $percentage );

if ( 'custom' !== $source && empty( $percentage ) ) {
	return;
}

// Classes
$wrap_classes = array(
	'vcex-module',
	'vcex-skillbar-wrap',
	'wpex-mb-10',
);

$wrap_classes[] = 'vcex-skillbar-style-' . sanitize_html_class( $style );

if ( $visibility ) {
    $wrap_classes[] = $visibility;
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Start shortcode output
$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . '>';

	// Generate icon output if defined
	if ( vcex_validate_boolean( $show_icon ) ) {

		$icon = vcex_get_icon_class( $atts, 'icon' );

		if ( $icon ) {

			vcex_enqueue_icon_font( $icon_type, $icon );

			$icon_class = 'vcex-icon-wrap';
			$icon_margin = $icon_margin ? $icon_margin : 10;

			if ( $icon_margin ) {
				$icon_class .= ' wpex-mr-' . sanitize_html_class( absint( $icon_margin ) );
			}

			$icon_output = '<span class="' . esc_attr( $icon_class ) . '">';

				$icon_output .= '<span class="' . esc_attr( $icon ) . '" aria-hidden="true"></span>';

			$icon_output .= '</span>';

		}
	}

	// Generate percent output
	if ( vcex_validate_boolean( $show_percent ) ) {

		$percentage_class = array(
			'vcex-skill-bar-percent',
			'wpex-absolute',
			'wpex-right-0',
		);

		switch ( $title_position ) {
			case 'inside':
				$percentage_class[] = 'wpex-mr-15';
				break;
			case 'outside':
				$percentage_class[] = 'wpex-text-sm';
				$percentage_class[] = 'wpex-top-50';
				$percentage_class[] = '-wpex-translate-y-50';
				$percentage_class[] = 'wpex-mr-10';
				break;
		}

		$percentage_style = vcex_inline_style( array(
			'color' => $percentage_color,
			'font_size' => $percentage_font_size,
		) );

		$percent_output = '<div class="' . esc_attr( implode( ' ', $percentage_class ) ) . '"' . $percentage_style . '>' . intval( $percentage ) . '&#37;</div>';

	}

	/*
	 * Title (outside of skillbar)
	 */
	if ( 'alt-1' == $style ) {

		$label_class = array(
			'vcex-skillbar-title',
			'wpex-font-semibold',
			'wpex-mb-5',
		);

		$label_style = array();

		$label_style = vcex_inline_style( array(
			'font_size' => $font_size,
			'color'     => $label_color,
		) );

		$output .= '<div class="' . esc_attr( implode( ' ', $label_class ) ) . '"' . $label_style . '>';

			if ( ! empty( $icon_output ) ) {
				$output .= $icon_output;
			}

			$output .= wp_kses_post( do_shortcode( $title ) );

		$output .= '</div>';

	}

	/*
	 * Inner wrap open
	 *
	 */
	$inner_class = array(
		'vcex-skillbar',
		'wpex-block',
		'wpex-relative',
	);

	switch ( $title_position ) {
		case 'inside':
			$inner_class[] = 'wpex-bg-gray-100';
			if ( vcex_validate_boolean( $box_shadow ) ) {
		  		$inner_class[] = 'wpex-shadow-inner';
			}
			$inner_class[] = 'wpex-text-white';
			break;
		case 'outside':
			$inner_class[] = 'wpex-bg-gray-200';
			$inner_class[] = 'wpex-text-gray-600';
			$inner_class[] = 'wpex-font-semibold';
			break;
	}

	$inner_style = array(
		'background'     => $background,
		'height_px'      => $container_height,
		'line_height_px' => $container_height,
	);

	if ( 'inside' == $title_position ) {
		$inner_style['font_size'] = $font_size;
	}

	$inner_style = vcex_inline_style( $inner_style, false );

	$inner_attrs = array(
		'class' => $inner_class,
		'style' => $inner_style,
	);

	if ( 'true' === $animate_percent && $percentage )  {
		$this->enqueue_scripts();
		$inner_attrs['data-percent'] = intval( $percentage ) . '&#37;';
	}

	$output .= '<div' . vcex_parse_html_attributes( $inner_attrs ) . '>';

		/*
		 * Percentage
		 */
		if ( $percentage ) {

			$bar_style = vcex_inline_style( array(
				'background' => $color,
				'width'      => ( 'true' !== $animate_percent ) ? intval( $percentage ) . '%' : '',
			) );

			$output .= '<div class="vcex-skillbar-bar wpex-relative wpex-w-0 wpex-h-100 wpex-bg-accent"' . $bar_style . '>';

				if ( 'inside' == $title_position && ! empty( $percent_output ) ) {
					$output .= $percent_output;
				}

			$output .= '</div>';

		}

		/*
		 * Title
		 */
		if ( 'inside' === $title_position ) {

			$dir = is_rtl() ? 'right' : 'left';

			$title_style = vcex_inline_style( array(
				'background'      => $color,
				'padding_' . $dir => $container_padding_left,
				'color'           => $label_color,
			) );

			$output .= '<div class="vcex-skillbar-title wpex-absolute wpex-top-0 wpex-left-0"' . $title_style . '>';

				$output .= '<div class="vcex-skillbar-title-inner wpex-px-15">';

					// Display Icon
					if ( ! empty( $icon_output ) ) {
						$output .= $icon_output;
					}

					// Title
					if ( 'default' == $style ) {
						$output .= wp_kses_post( do_shortcode( $title ) );
					}

				$output .= '</div>';

			$output .= '</div>';

		}

		// Display percent outside of colored background
		if ( 'outside' == $title_position && ! empty( $percent_output ) ) {
			$output .= $percent_output;
		}

	$output .= '</div>';

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
