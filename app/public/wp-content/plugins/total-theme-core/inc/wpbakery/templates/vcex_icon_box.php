<?php
/**
 * vcex_icon_box shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.3
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_icon_box';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// FALLBACK VARS => NEVER REMOVE!!
$padding          = ( isset( $atts['padding'] ) && empty( $atts['wpex_padding'] ) ) ? $atts['padding'] : '';
$background       = isset( $atts['background'] ) ? $atts['background'] : '';
$background_image = isset( $atts['background_image'] ) ? $atts['background_image'] : '';
$margin_bottom    = isset( $atts['margin_bottom'] ) ? $atts['margin_bottom'] : '';
$border_color     = isset( $atts['border_color'] ) ? $atts['border_color'] : '';

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Extract shortcode atts for easier usage
extract( $atts );

// Sanitize data & declare main vars
$output           = '';
$style            = ! empty( $style ) ? $style : 'one';
$url              = ! empty( $url ) ? do_shortcode( $url ) : '';
$outer_wrap_class = array(); // formally known as "vcex-icon-box-css-wrap" before v5.0
$has_side_icon    = in_array( $style, array( 'one', 'seven' ) );
$has_top_icon     = in_array( $style, array( 'two', 'three', 'four', 'five', 'six' ) );
$clickable_boxes  = array( 'four', 'five', 'six' );
$image            = ( 'attachment' == get_post_type( $image ) ) ? $image : '';
$icon             = ( $image || $icon_alternative_classes ) ? '' : vcex_get_icon_class( $atts, 'icon' );
$heading          = $heading ? do_shortcode( $heading ) : '';
$url_wrap         = in_array( $style, $clickable_boxes ) ? 'true' : $url_wrap;
$url_wrap         = ( 'true' == $url_wrap ) ? true : false;
$has_outer_wrap   = $width ? true : false; // add auter_wrap for custom widths
$icon_spacing     = $icon_spacing ? $icon_spacing : '20px';

// Disable URL wrap if the content has a link to prevent conflicts
if ( false !== strpos( $content, '<a href=' ) ) {
	$url_wrap = false;
}

// Define link attributes
if ( $url ) {

	if ( $url_wrap ) {

		$url_classes = array(
			'vcex-icon-box-link-wrap',
			'wpex-inherit-color',
			'wpex-no-underline'
		);

		if ( $visibility ) {
			$url_classes[] = sanitize_html_class( $visibility );
		}

	} else {

		$url_classes = array(
			'vcex-icon-box-link',
			'wpex-no-underline',
		);

	}

	if ( 'local' == $url_target ) {
		$url_classes[] = 'local-scroll-link';
		$url_target = '';
	}

	$url_attrs = array(
		'href'   => esc_url( $url ),
		'class'  => array_map( 'esc_attr', $url_classes ),
		'target' => $url_target ? wp_strip_all_tags( $url_target ) : '',
		'rel'    => $url_rel ? wp_strip_all_tags( $url_rel ) : '',
	);

}

// Define main wrap attributes
$wrap_attrs = array(
	'id'    => trim( vcex_get_unique_id( $unique_id ) ),
	'class' => array(
		'vcex-module',
		'vcex-icon-box',
		'vcex-icon-box-' . sanitize_html_class( $style ),
		'wpex-relative',
	),
);

// Flex styles
if ( $has_side_icon ) {
	$wrap_attrs['class'][] = 'wpex-flex';
	if ( 'true' === $align_center ) {
		$wrap_attrs['class'][] = 'wpex-items-center';
	}
}

// No icon class
if ( ! $icon && ! $image && ! $icon_alternative_classes ) {
	$wrap_attrs['class'][] = 'vcex-icon-box-wo-icon';
}

// Shadow
if ( $shadow ) {
	$wrap_attrs['class'][] = 'wpex-' . sanitize_html_class( $shadow );
}

// Shadow: Hover
if ( $shadow_hover ) {
	$wrap_attrs['class'][] = 'wpex-hover-' . sanitize_html_class( $shadow_hover );
	if ( ( ! $hover_animation && ! $hover_background ) ) {
		$wrap_attrs['class'][] = 'wpex-transition-shadow';
		$wrap_attrs['class'][] = 'wpex-duration-300';
	}
}

// Bottom Margin
if ( $bottom_margin ) {
	$wrap_attrs['class'][] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

// Padding
if ( $wpex_padding ) {
	$wrap_attrs['class'][] = 'wpex-p-' . sanitize_html_class( absint( $wpex_padding ) );
}

// Custom Alignment for Top Icon only.
if ( $alignment && 'two' == $style ) {
	$wrap_attrs['class'][] = 'wpex-text-' . sanitize_html_class( $alignment );
}

// Default alignments
else {

	switch ( $style ) {
		case 'one':
			$wrap_attrs['class'][] = 'wpex-text-left';
			break;
		case 'seven':
			$wrap_attrs['class'][] = 'wpex-text-right';
			break;
		default:
			if ( $has_top_icon ) {
				$wrap_attrs['class'][] = 'wpex-text-center';
			}
			break;
	}

}

if ( 'true' == $hover_white_text ) {
	$wrap_attrs['class']['wpex-hover-white-text'] = 'wpex-hover-white-text';
}

if ( $visibility ) {
	$wrap_attrs['class'][] = $visibility;
}

// Style specific classes
switch ( $style ) {

	// Right Icon
	case 'seven':
		$wrap_attrs['class'][] = 'wpex-flex-row-reverse';
		break;

	// Top Icon Bordered
	case 'four':
		if ( empty( $wpex_padding ) ) {
			$wrap_attrs['class'][] = 'wpex-p-30';
		}
		$wrap_attrs['class'][] = 'wpex-bordered';
		$border_width = ! empty( $border_width ) ? absint( $border_width ) : 1;
		if ( $border_width > 1 ) {
			$wrap_attrs['class'][] = 'wpex-border-' . sanitize_html_class( $border_width );
		}
		break;

	// Top Icon w Gray Background
	case 'five':
		if ( empty( $wpex_padding ) ) {
			$wrap_attrs['class'][] = 'wpex-p-30';
		}
		$wrap_attrs['class'][] = 'wpex-bg-gray-100';
		break;

	// Black background
	case 'six':
		$wrap_attrs['class'][] = 'wpex-bg-black';
		$wrap_attrs['class'][] = 'wpex-text-white';
		$wrap_attrs['class'][] = 'wpex-child-inherit-color';
		if ( empty( $wpex_padding ) ) {
			$wrap_attrs['class'][] = 'wpex-p-30';
		}
		break;

}

if ( $hover_animation ) {
	vcex_enque_style( 'hover-animations' );
	$wrap_attrs['class'][] = esc_attr( vcex_hover_animation_class( $hover_animation ) );
}

// Add Design Options CSS class to proper container
if ( $css ) {
	$wrap_attrs['class'][] = vcex_vc_shortcode_custom_css_class( $css );
}

// Wrap Style
$wrap_style = array();

if ( $border_radius ) {
	$wrap_style['border_radius'] = $border_radius;
}

if ( 'four' == $style && $border_color ) {
	$wrap_style['border_color'] = $border_color;
}

// Fallback styles if $css is empty
if ( empty( $css ) ) {
	if ( $padding ) {
		$wrap_style['padding'] = $padding;
	}
	if ( 'six' == $style && $background ) {
		$wrap_style['background_color'] = $background;
	}
	if ( $background && in_array( $style, $clickable_boxes ) ) {
		$wrap_style['background_color'] = $background;
	}
	if ( $background_image && in_array( $style, $clickable_boxes ) ) {
		$background_image = wp_get_attachment_url( $background_image );
		$wrap_style['background_image'] = $background_image;
		$wrap_attrs['class'][] = 'vcex-background-' . sanitize_html_class( $background_image_style );
	}
	if ( $margin_bottom ) {
		$wrap_style['margin_bottom'] = $margin_bottom;
	}
}

// Hover Background
if ( $hover_background ) {
	$wrap_attrs['data-wpex-hover'] = htmlspecialchars( wp_json_encode( array( 'background' => $hover_background ) ) );
}

// Open outer wrap which is used when adding a custom width to limit the icon box size
if ( $has_outer_wrap ) {

	$outer_wrap_class[] = 'wpex-block';
	$outer_wrap_class[] = 'wpex-m-auto';
	$outer_wrap_class[] = 'wpex-max-w-100';

	if ( $visibility ) {
		$outer_wrap_class[] = sanitize_html_class( $visibility );
	}

	$outer_wrap_attrs = array(
		'class' => $outer_wrap_class,
		'style' => vcex_inline_style( array(
			'width' => $width,
		), false ),
	);

}

// Add style to wrap_attrs
$wrap_attrs['style'] = vcex_inline_style( $wrap_style );

// Add custom classes last
if ( $classes ) {
	$wrap_attrs['class'][] = vcex_get_extra_class( $classes );
}

// Make sure classes are unique
$wrap_attrs['class'] = array_unique( $wrap_attrs['class'] );

// Apply filters to wrap class and add to wrap_attrs
$wrap_attrs['class'] = trim( vcex_parse_shortcode_classes( implode( ' ', $wrap_attrs['class'] ), $shortcode_tag, $atts ) );

/*-------------------------------------------------------------------------------*/
/* [ Output Starts here ]
/*-------------------------------------------------------------------------------*/

// Open css_animation element (added in it's own element to prevent conflicts with inner styling)
if ( $css_animation && 'none' !== $css_animation ) {

	$animation_classes = array( trim( vcex_get_css_animation( $css_animation ) ) );

	if ( $visibility ) {
		$animation_classes[] = sanitize_html_class( $visibility );
	}

	$output .= '<div class="' . esc_attr( implode( ' ', $animation_classes ) ) . '">';
}

// Open outer_wrap if needed for custom widths
if ( $has_outer_wrap ) {
	$output .= '<div' . vcex_parse_html_attributes( $outer_wrap_attrs ) . '>';
}

// Open link if url is defined and set to wrap the whole container
if ( $url && $url_wrap ) {
	$output .= '<a' . vcex_parse_html_attributes( $url_attrs ) . '>';
}

/*-------------------------------------------------------------------------------*/
/* [ Inner vcex-icon-box element starts here ]
/*-------------------------------------------------------------------------------*/
$output .= '<div' . vcex_parse_html_attributes( $wrap_attrs ) . '>';

	/*-------------------------------------------------------------------------------*/
	/* [ Container for Icon/Image ]
	/*-------------------------------------------------------------------------------*/
	if ( $image || $icon || $icon_alternative_classes ) {

		$symbol_classes = array(
			'vcex-icon-box-symbol'
		);

		$symbol_style = array();

		// Prevent flex shrink on side icon styles
		if ( $has_side_icon ) {
			$symbol_classes[] = 'wpex-flex-shrink-0';
		}

		// Add icon spacing
		if ( $icon_spacing ) {

			switch ( $style ) {
				case 'one':
					$symbol_classes[] = 'wpex-mr-' . sanitize_html_class( absint( $icon_spacing ) );
					break;
				case 'seven':
					$symbol_classes[] = 'wpex-ml-' . sanitize_html_class( absint( $icon_spacing ) );
					break;
				default:
					if ( $has_top_icon ) {
						$symbol_classes[] = 'wpex-mb-' . sanitize_html_class( absint( $icon_spacing ) );
					}
					break;
			}

		}

		// Image specific style
		if ( $image ) {
			// None needed yet.
		} elseif ( $icon || $icon_alternative_classes ) {

			if ( $icon_width && $has_side_icon ) {
				$symbol_style['width'] = $icon_width; // add width to this container for left/right style icons
			}

			if ( $icon_bottom_margin && $has_top_icon ) {
				$symbol_style['margin_bottom'] = $icon_bottom_margin;
			}

		}

		// Apply filters to classes
		$symbol_classes = (array) apply_filters( 'vcex_icon_box_symbol_class', $symbol_classes );

		// Open .vcex-icon-box-symbol element
		$output .= '<div class="' . esc_attr( implode( ' ', $symbol_classes ) )  . '"' . vcex_inline_style( $symbol_style, true ) . '>';

		// Filter check to see if links should be added to symbols
		$symbol_link = apply_filters( 'vcex_icon_box_symbol_link', $url, $atts );

		// Add link to symbol
		if ( $symbol_link && ! $url_wrap ) {
			$symbol_url_attrs = $url_attrs;
			$symbol_url_attrs['href'] = esc_url( $symbol_link );
			$symbol_url_attrs['class'] = 'wpex-no-underline';
			$output .= '<a' . vcex_parse_html_attributes( $symbol_url_attrs ) . '>';
		}

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Image ]
	/*-------------------------------------------------------------------------------*/
	if ( $image ) {

		$image_style = vcex_inline_style( array(
			'width'         => $image_width,
			'margin_bottom' => $image_bottom_margin,
		), false );

		$image_classes = array(
			'vcex-icon-box-image',
			'wpex-align-middle'
		);

		// Image with custom resizing
		if ( 'true' == $resize_image ) {

			$output .= vcex_get_post_thumbnail( array(
				'size'       => 'wpex-custom',
				'attachment' => $image,
				'alt'        => apply_filters( 'vcex_icon_box_image_auto_alt', false ) ? $heading : '',
				'width'      => ( 'true' == $resize_image ) ? $image_width : '',
				'height'     => ( 'true' == $resize_image ) ? $image_height : '',
				'crop'       => 'center-center',
				'style'      => $image_style,
				'class'      => $image_classes,
			) );

		}

		// Image with inline sizing
		else {

			$image_style = '';

			if ( $image_width ) {
				$image_style .= 'width:' . vcex_validate_px_pct( $image_width ) . ';';
			}

			if ( $image_height ) {
				$image_style .= 'height:' . vcex_validate_px_pct( $image_height ) . ';';
			}

			$output .= '<img' . vcex_parse_html_attributes( array(
				'src'   => wp_get_attachment_url( $image ),
				'alt'   => apply_filters( 'vcex_icon_box_image_auto_alt', false ) ? $heading : '',
				'class' => $image_classes,
				'style' => $image_style,
			) ) . ' />';

		}

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Icon ]
	/*-------------------------------------------------------------------------------*/
	elseif ( $icon || $icon_alternative_classes ) {

		// Load icon family CSS
		if ( $icon ) {
			vcex_enqueue_icon_font( $icon_type, $icon );
		}

		// Define icon attributes
		$icon_classes = array(
			'vcex-icon-box-icon',
			'wpex-inline-block',
			'wpex-child-inherit-color',
			'wpex-text-center',
			'wpex-leading-none',
		);

		// Expand icon when needed
		if ( $has_side_icon && $icon_width ) {
			$icon_classes[] = 'wpex-w-100';
		}

		// Add text accent
		if ( empty( $icon_color ) ) {
			if ( 'true' === $icon_color_accent ) {
				$icon_classes[] = 'wpex-text-accent';
			} elseif ( 'six' !== $style ) {
				$icon_classes[] = 'wpex-text-black';
			}
		}

		// Add Background accent classes
		if ( 'true' === $icon_background_accent ) {
			$icon_classes[] = 'wpex-bg-accent';
			if ( empty( $icon_color ) ) {
				if ( ( $key = array_search( 'wpex-text-black', $icon_classes) ) !== false ) {
					unset( $icon_classes[$key] );
				}
				$icon_classes[] = 'wpex-text-white';
			}

		}

		// Icon shadow
		if ( $icon_shadow ) {
			$icon_classes[] = 'wpex-' . sanitize_html_class( $icon_shadow );
		}

		// Icon Style
		$icon_style = array(
			'color'         => $icon_color,
			'font_size'     => $icon_size,
			'border_radius' => $icon_border_radius,
			'background'    => $icon_background,
		);

		// Add custom width for Top style icons
		if ( $icon_width && $has_top_icon ) {
			$icon_style['width'] = $icon_width; // add width to this container for left/right style icons
		}

		// Add custom height
		if ( $icon_height ) {
			$icon_style['height'] = $icon_height;
			$icon_style['line_height'] = intval( $icon_height ) . 'px';
		}

		// Add padding when custom height is empty so custom backgrounds look ok
		elseif ( ! empty( $icon_background ) || 'true' === $icon_background_accent ) {
			$icon_classes[] = 'wpex-p-15';
		}

		// Convert icon style array to inline style
		$icon_style = vcex_inline_style( $icon_style );

		// Apply filters to icon classes
		$icon_classes = (array) apply_filters( 'vcex_icon_box_icon_class', $icon_classes );

		// Display Icon
		$output .= '<div class="' . esc_attr( implode( ' ', $icon_classes ) ) . '"' . $icon_style . '>';

			if ( $icon_alternative_classes ) {

				$output .= '<span class="' . esc_attr( do_shortcode( $icon_alternative_classes ) ) . '" aria-hidden="true"></span>';

			} else {

				$output .= '<span class="' . esc_attr( $icon ) . '" aria-hidden="true"></span>';

			}

		$output .= '</div>';

	}

	// Close symbol link
	if ( $symbol_link && ! $url_wrap ) {
		$output .= '</a>';
	}

	// Close symbol div (icon/image)
	if ( $image || $icon || $icon_alternative_classes ) {
		$output .= '</div>';
	}

	/*-------------------------------------------------------------------------------*/
	/* [ Container for Heading + Content ]
	/*-------------------------------------------------------------------------------*/

	$text_classes = array(
		'vcex-icon-box-text'
	);

	if ( $has_side_icon ) {
		$text_classes[] = 'wpex-flex-grow';
	}

	$output .= '<div class="' . esc_attr( implode( ' ', $text_classes ) )  . '">';

		/*-------------------------------------------------------------------------------*/
		/* [ Heading ]
		/*-------------------------------------------------------------------------------*/
		if ( $heading ) {

			if ( $url && ! $url_wrap ) {
				$output .= '<a' . vcex_parse_html_attributes( $url_attrs ) . '>';
			}

			$heading_tag = $heading_type ? $heading_type : apply_filters( 'vcex_icon_box_heading_default_tag', 'h2' );
			$heading_tag_escaped = tag_escape( $heading_tag );

			$heading_attrs = array(
				'class' => apply_filters( 'vcex_icon_box_heading_class', array(
					'vcex-icon-box-heading',
					'wpex-heading',
					'wpex-text-md',
					'wpex-mb-10',
				) ),
			);

			switch ( $style ) {
				case 'six':
					$heading_attrs['class'][] = ! empty( $heading_color ) ? 'wpex-inherit-color' : 'wpex-inherit-color-important';
					break;
			}

			if ( $heading_font_family ) {
				vcex_enqueue_font( $heading_font_family );
			}

			$heading_attrs['style'] = vcex_inline_style( array(
				'font_family'    => $heading_font_family,
				'font_weight'    => $heading_weight,
				'color'          => $heading_color,
				'font_size'      => $heading_size,
				'letter_spacing' => $heading_letter_spacing,
				'margin_bottom'  => $heading_bottom_margin,
				'text_transform' => $heading_transform,
				'line_height'    => $heading_line_height,
			), false );

			if ( $heading_responsive_font_size = vcex_get_module_responsive_data( $heading_size, 'font_size' ) ) {
				$heading_attrs['data-wpex-rcss'] = $heading_responsive_font_size;
			}

			$heading_attrs = apply_filters( 'vcex_icon_box_heading_attrs', $heading_attrs, $atts );

			$output .= '<' . $heading_tag_escaped . vcex_parse_html_attributes( $heading_attrs ) . '>';

				// Heading text
				$output .= wp_kses_post( $heading );

				// Badge
				if ( ! empty( $heading_badge ) ) {
					$output .= ' <span class="wpex-badge">' . wp_strip_all_tags( $heading_badge ) . '</span>';
				}

			$output .= '</' . $heading_tag_escaped . '>';

		} // End heading

		// Close link around heading and icon
		if ( $url && ! $url_wrap ) {
			$output .= '</a>';
		}

		/*-------------------------------------------------------------------------------*/
		/* [ Content ]
		/*-------------------------------------------------------------------------------*/
		if ( $content ) {

			$content_escaped = vcex_the_content( $content );

			$content_attrs = array(
				'class' => apply_filters( 'vcex_icon_box_content_class', array(
					'vcex-icon-box-content',
					'wpex-last-mb-0',
					'wpex-clr'
				) ),
			);

			$content_attrs['style'] = vcex_inline_style( array(
				'color'       => $font_color,
				'font_size'   => $font_size,
				'font_weight' => $font_weight,
			), false );

			if ( $content_responsive_font_size = vcex_get_module_responsive_data( $font_size, 'font_size' ) ) {
				$content_attrs['data-wpex-rcss'] = $content_responsive_font_size;
			}

			$output .= '<div' . vcex_parse_html_attributes( $content_attrs ) . '>';

				$output .= $content_escaped;

			$output .= '</div>';

		}

	// Close heading/text wrapper
	$output .= '</div>';

// Close Icon Box element
$output .= '</div>';

// Close outer link wrap
if ( $url && $url_wrap ) {
	$output .= '</a>';
}

// Close css wrapper for icon style one
if ( $has_outer_wrap ) {
	$output .= '</div>';
}

// Close animation wrapper
if ( $css_animation && 'none' !== $css_animation ) {
	$output .= '</div>';
}

// @codingStandardsIgnoreLine
echo $output;
