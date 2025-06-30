<?php
/**
 * vcex_divider shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_divider';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Define output var
$output = '';

// Sanitize data
$style            = $style ? $style : 'solid';
$icon             = vcex_get_icon_class( $atts, 'icon' );
$icon_spacing     = $icon_spacing ? absint( $icon_spacing ) : 20;
$height           = $height ? vcex_validate_px( $height, 'px' ) : '';
$icon_padding     = ( $icon_height || $icon_width ) ? '' : $icon_padding;

/*-------------------------------------------------*/
/* [ Style Based Utility Classes ]
/*-------------------------------------------------*/
$util_border        = '';
$util_border_style  = '';
$util_border_color  = '';
$util_inner_padding = '';

switch ( $style ) {

	case 'solid' :
		$util_border       = 'wpex-border-b';
		$util_border_style = 'wpex-border-solid';
		$util_border_color = 'wpex-border-gray-200';
	break;

	case 'dashed' :
		$util_border       = 'wpex-border-b-2';
		$util_border_style = 'wpex-border-dashed';
		$util_border_color = 'wpex-border-gray-200';
	break;

	case 'dotted-line' :
		$util_border       = 'wpex-border-b-2';
		$util_border_style = 'wpex-border-dotted';
		$util_border_color = 'wpex-border-gray-200';
	break;

	case 'double' :
		$util_border        = 'wpex-border-y';
		$util_border_style  = 'wpex-border-solid';
		$util_border_color  = 'wpex-border-gray-200';
	break;

}

/*-------------------------------------------------*/
/* [ Wrap Classes ]
/*-------------------------------------------------*/
$wrap_classes = array(
	'vcex-module',
	'vcex-divider',
	'vcex-divider-' . sanitize_html_class( $style ),
);

if ( $margin_y ) {
	$wrap_classes[] = 'wpex-my-' . sanitize_html_class( absint( $margin_y ) );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $align ) {
	$wrap_classes[] = 'vcex-divider-' . sanitize_html_class( $align );
	$wrap_classes[] = 'wpex-float-' . sanitize_html_class( vcex_parse_direction( $align ) );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

// Add core utility classes
$wrap_classes[] = 'wpex-max-w-100';
$wrap_classes[] = 'wpex-mx-auto';

// Add icon utility classes
if ( $icon ) {
	$wrap_classes[] = 'vcex-divider-has-icon';
	$wrap_classes[] = 'wpex-flex';
	$wrap_classes[] = 'wpex-items-center';
}

// Add border utility classes (only when an icon isn't defined)
else {

	$wrap_classes[] = 'wpex-block';
	$wrap_classes[] = 'wpex-h-0';

	if ( $util_border ) {
		$wrap_classes[] = $util_border;
	}

	if ( $util_border_style ) {
		$wrap_classes[] = $util_border_style;
	}

	if ( $util_border_color ) {
		$wrap_classes[] = $util_border_color;
	}

	if ( $util_inner_padding ) {
		$wrap_classes[] = $util_inner_padding;
	}

	switch ( $style ) {
		case 'double':
			$wrap_classes[] = 'wpex-pb-5';
			break;
	}

}

// Add custom classes last
if ( $el_class ) {
	$wrap_classes[] = vcex_get_extra_class( $el_class );
}

// Turn wrap classes into a string
$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

/*-------------------------------------------------*/
/* [ Icon Checks ]
/*-------------------------------------------------*/
if ( $icon ) {

	// Load icon font family
	vcex_enqueue_icon_font( $icon_type, $icon );

	// Icon style
	$icon_style = vcex_inline_style( array(
		'font_size'     => $icon_size,
		'border_radius' => $icon_border_radius,
		'color'         => $icon_color ? $icon_color : $color,
		'background'    => $icon_bg,
		'padding'       => $icon_padding,
		'height'        => $icon_height,
		'line_height'   => vcex_validate_px( $icon_height, 'px' ),
		'width'         => $icon_width,
	) );

	// Inner border style
	$inner_border_style = array();

	switch ( $style ) {
		case 'dotted':

			if ( $dotted_height ) {
				$inner_border_style['height'] = $dotted_height;
			}

			break;
		default:

			if ( $color ) {
				$inner_border_style['border_color'] = $color;
			}

			if ( $height ) {
				$inner_border_style['border_bottom_width'] = $height;
			}

			if ( 'double' == $style ) {
				$inner_border_style['border_top_width'] = $height;
			}

			break;
	}

	$inner_border_style = $inner_border_style ? vcex_inline_style( $inner_border_style ) : '';

	// Inner border class
	$inner_border_class = array(
		'vcex-divider-border',
		'wpex-flex-grow',
	);

	if ( $util_border ) {
		$inner_border_class[] = $util_border;
	}

	if ( $util_border_style ) {
		$inner_border_class[] = $util_border_style;
	}

	if ( $util_border_color ) {
		$inner_border_class[] = $util_border_color;
	}

	if ( $util_inner_padding ) {
		$inner_border_class[] = $util_inner_padding;
	}

	switch ( $style ) {
		case 'double':
			$inner_border_class[] = 'wpex-pb-5';
			break;
	}

	// Reset vars if icon is defined so styles aren't duplicated in main wrapper - important!!!
	$height = $color = '';

}

/*-------------------------------------------------*/
/* [ Inline Wrap Style ]
/*-------------------------------------------------*/
$wrap_style = array(
	'width'  => $width,
	'margin' => $margin,
);

switch ( $style ) {
	case 'dotted':
		$wrap_style['height'] = $dotted_height;
		break;
	default:
		if ( 'double' == $style ) {
			$wrap_style['border_top_width'] = $height;
		}
		$wrap_style['border_bottom_width'] = $height;
		$wrap_style['border_color'] = $color;
		break;
}

$wrap_style = vcex_inline_style( $wrap_style );

/*-------------------------------------------------*/
/* [ Divider Output Starts Here ]
/*-------------------------------------------------*/
$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . $wrap_style . '>';

	/*-------------------------------------------------*/
	/* [ Display icon if set ]
	/*-------------------------------------------------*/
	if ( $icon ) {

		// Icon before span (left border when icon is set)
		$output .= '<div class="' . esc_attr( implode( ' ', $inner_border_class ) ) . '"' . $inner_border_style . '></div>';

		// Icon output
		$icon_inner_class = array(
			'vcex-divider-icon-span',
			'wpex-box-content',
			'wpex-inline-block',
			'wpex-text-center',
			'wpex-text-gray',
			'wpex-text-lg',
		);

		if ( $icon_bg ) {
			$icon_inner_class[] = 'wpex-mx-' . sanitize_html_class( $icon_spacing );
		}

		if ( ! $icon_height ) {
			$icon_inner_class[] = 'wpex-py-10';
		}

		if ( ! $icon_width ) {
			$icon_inner_class[] = 'wpex-px-' . sanitize_html_class( $icon_spacing );
		}

		$output .= '<span class="' . esc_attr( implode( ' ', $icon_inner_class ) ) . '"' . $icon_style . '>';

			$output .= '<span class="' . esc_attr( $icon ) . '" aria-hidden="true"></span>';

		$output .= '</span>';

		// Icon after span (right border when icon is set)
		$output .= '<div class="' . esc_attr( implode( ' ', $inner_border_class ) ) . '"' . $inner_border_style . '></div>';

	}

// Close main wrapper
$output .= '</div>';

// Clear floats if needed
if ( 'left' == $align || 'right' == $align ) {
	$output .= '<div class="wpex-clear"></div>';
}

// @codingStandardsIgnoreLine
echo $output;
