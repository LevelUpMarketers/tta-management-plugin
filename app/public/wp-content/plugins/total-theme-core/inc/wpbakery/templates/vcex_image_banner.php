<?php
/**
 * vcex_image_banner shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_image_banner';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Checks
$use_img_tag = ( 'true' === $use_img_tag ) ? true : false;

// Default animation speed class
$transition_duration = 'wpex-duration-500';

// RTL fixes
$align         = vcex_parse_direction( $align );
$content_align = vcex_parse_direction( $content_align );
$text_align    = vcex_parse_direction( $text_align );

// Check links
$link       = vcex_build_link( $link );
$has_link   = isset( $link['url'] ) ? true : false;
$has_button = ( 'true' == $button && $button_text ) ? true : false;

// Wrap classes
$wrap_classes = array(
	'vcex-module',
	'vcex-image-banner',
	'wpex-relative',
	'wpex-overflow-hidden',
	'wpex-max-w-100',
	'wpex-bg-gray-900',
	'wpex-text-white',
);

// Bottom margin
if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

// Alignment
$align = $align ? $align : 'center';
$wrap_classes[] = 'wpex-float-' . sanitize_html_class( $align );

// CSS animation
if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = trim( vcex_get_css_animation( $css_animation ) );
}

// Text alignment
if ( ! $content_align && ! $text_align ) {
	$wrap_classes[] = 'wpex-text-center';
} else {
	if ( $text_align ) {
		$wrap_classes[] = 'wpex-text-' . sanitize_html_class( $text_align );
	} elseif ( $content_align ) {
		$wrap_classes[] = 'wpex-text-' . sanitize_html_class( $content_align );
	}
}

// Custom border radius
if ( $border_radius ) {
	$border_radius = 'wpex-' . vcex_sanitize_border_radius( $border_radius );
	$wrap_classes[] = $border_radius;
}

// Hover classes
if ( 'true' == $show_on_hover ) {
	$wrap_classes[] = 'vcex-soh';
	$wrap_classes[] = 'vcex-anim-' . sanitize_html_class( $show_on_hover_anim );
}

// Zoom class
if ( 'true' == $image_zoom ) {
	$wrap_classes[] = 'vcex-h-zoom';
}

// Image tag class
if ( $use_img_tag ) {
	$wrap_classes[] = 'vcex-has-img-tag';
}

// Button class
if ( $has_button ) {
	$wrap_classes[] = 'vcex-has-button';
}

// Custom Class
if ( $el_class ) {
	$wrap_classes[] = vcex_get_extra_class( $el_class );
}

// Wrap inline CSS
$wrap_style = vcex_inline_style( array(
	'width' => $width,
) );

/*-------------------------------------------------------------------------------*/
/* [ Output Starts here ]
/*-------------------------------------------------------------------------------*/
$output = '<div class="' . esc_attr( implode( ' ' , $wrap_classes ) ) . '" ' . $wrap_style . '>';

	/*-------------------------------------------------------------------------------*/
	/* [ Link Open tag ]
	/*-------------------------------------------------------------------------------*/
	if ( $has_link ) {

		$link_classes = array(
			'vcex-ib-link',
			'wpex-block',
			'wpex-inherit-color-important',
			'wpex-no-underline',
		);

		if ( 'true' == $show_on_hover ) {
			$link_classes[] = 'overlay-parent';
		}

		if ( 'true' == $link_local_scroll ) {
			$link_classes[] = 'local-scroll-link';
		}

		$link_attrs = array(
			'href'   => do_shortcode( $link['url'] ),
			'class'  => $link_classes,
			'title'  => isset( $link['title'] ) ? do_shortcode( $link['title'] ) : '',
			'rel'    => isset( $link['rel'] ) ? $link['rel'] : '',
			'target' => isset( $link['target'] ) ? $link['target'] : '',
		);

		$output .= '<a' . vcex_parse_html_attributes( $link_attrs ) . '>';

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Image ]
	/*-------------------------------------------------------------------------------*/
	if ( 'featured' == $image_source ) {
		$image = get_post_thumbnail_id( vcex_get_the_ID() );
	} elseif ( 'custom_field' == $image_source ) {
		if ( $image_custom_field ) {
			$custom_field_val = get_post_meta( vcex_get_the_ID(), $image_custom_field, true );
			$image = intval( $custom_field_val );
		}
	}

	// Generate image URL
	if ( $image ) {
		$image_url = vcex_get_post_thumbnail( array(
			'attachment' => $image,
			'size'       => $img_size,
			'crop'       => $img_crop,
			'width'      => $img_width,
			'height'     => $img_height,
			'return'     => 'url',
		) );
	} else {
		$image_url = '';
	}

	if ( $image_url ) {

		$img_classes = array(
			'vcex-ib-img',
			'wpex-block',
			'wpex-z-1',
			'wpex-transition-all',
			$transition_duration,
		);

		if ( $border_radius ) {
			$img_classes[] = $border_radius;
		}

		if ( $use_img_tag ) {

			$style = vcex_inline_style( array(
				'transition_speed' => ( $image_zoom_speed && '0.4' != $image_zoom_speed ) ? $image_zoom_speed : '',
			) );

			$output .= '<img src="' . esc_url( $image_url ) . '" class="' . esc_attr( implode( ' ', $img_classes ) ) . '"' . $style . ' />';

		} else {

			$img_classes[] = 'wpex-bg-cover';
			$img_classes[] = 'wpex-absolute';
			$img_classes[] = 'wpex-inset-0';

			$style = vcex_inline_style( array(
				'background_image'    => esc_url( $image_url ),
				'background_position' => $image_position,
				'transition_speed'    => ( $image_zoom_speed && '0.4' != $image_zoom_speed ) ? $image_zoom_speed : '',
			) );

			$output .= '<span class="' . esc_attr( implode( ' ', $img_classes ) ) . '"' . $style . '></span>';

		}

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Overlay ]
	/*-------------------------------------------------------------------------------*/
	if ( 'true' == $overlay ) {

		$overlay_classes = array(
			'vcex-ib-overlay',
			'wpex-absolute',
			'wpex-z-5',
			'wpex-inset-0',
			'wpex-transition-all',
			$transition_duration,
		);

		$output .= '<div class="' . esc_attr( implode( ' ', $overlay_classes ) ) . '">';

			$overlay_color_classes = array(
				'vcex-ib-overlay-bg',
				'wpex-absolute',
				'wpex-inset-0',
				'wpex-bg-black',
				'wpex-opacity-30'
			);

			$overlay_color_style = vcex_inline_style( array(
				'background' => $overlay_color,
				'opacity'    => $overlay_opacity,
			) );

			$output .= '<div class="' . esc_attr( implode( ' ', $overlay_color_classes ) ) . '"' . $overlay_color_style . '></div>'; // apply color and opacity to inner for smooth transitions

		$output .= '</div>';

	}

	/*-------------------------------------------------------------------------------*/
	/* [ Inner Border ]
	/*-------------------------------------------------------------------------------*/
	if ( 'true' == $inner_border ) {

		$inner_border_margin = $inner_border_margin ? absint( $inner_border_margin ) : '15';
		$inner_border_style  = $inner_border_style ? $inner_border_style : 'solid';

		$inner_border_class = array(
			'vcex-ib-border',
			'wpex-absolute',
			'wpex-z-5',
			'wpex-inset-0',
			'wpex-m-' . sanitize_html_class( $inner_border_margin ),
			'wpex-border',
			'wpex-border-' . sanitize_html_class( $inner_border_style ),
			'wpex-pointer-events-none',
			'wpex-transition-all',
			$transition_duration,
		);

		if ( $inner_border_radius ) {
			$inner_border_class[] = 'wpex-' . vcex_sanitize_border_radius( $inner_border_radius );
		}

		if ( ! $inner_border_color ) {
			$inner_border_class[] = 'wpex-border-white';
		}

		$border_style = vcex_inline_style( array(
			'border_color' => $inner_border_color,
			'border_width' => $inner_border_width ? absint( $inner_border_width ) . 'px' : '',
		) );

		$output .= '<div class="' . esc_attr( implode( ' ', $inner_border_class ) ) . '"' . $border_style . '></div>';
	}

	/*-------------------------------------------------------------------------------*/
	/* [ Content wrap open]
	/*-------------------------------------------------------------------------------*/

	$content_classes = array(
		'vcex-ib-content-wrap',
		'wpex-z-10',
		'wpex-w-100',
		'wpex-transition-all',
		$transition_duration,
	);

	if ( $use_img_tag ) {
		$content_classes[] = 'wpex-absolute';
		$content_classes[] = 'wpex-inset-0';
		$content_classes[] = 'wpex-flex';
		$flex_align = $flex_align ? $flex_align : 'center';
		$content_classes[] = 'wpex-items-' . sanitize_html_class( $flex_align );
	} else {
		$content_classes[] = 'wpex-relative';
	}

	if ( 'true' == $show_on_hover && 'fade-up' == $show_on_hover_anim ) {
		$content_classes[] = 'wpex-translate-y-50';
	}

	$content_style = array();

	if ( $padding ) {
		$content_style['padding'] = $padding;
	}

	$content_classes[] = 'wpex-clr';

	$content_style = $content_style ? vcex_inline_style( $content_style ) : '';

	$output .= '<div class="' . esc_attr( implode( ' ', $content_classes ) ) . '"' . $content_style . '>';

		// Content class
		$content_class = array(
			'vcex-ib-content',
		);

		if ( $use_img_tag ) {
			$content_class[] = 'wpex-flex-grow';
		}

		// Inner style
		$content_style = vcex_inline_style( array(
			'width' => $content_width,
		) );

		if ( $content_align ) {

			switch ( $content_align ) {
				case 'center' :
					$content_class[] = 'wpex-mx-auto';
					break;
				case 'left':
					$content_class[] = 'wpex-float-left';
					$content_class[] = 'wpex-mr-auto'; // fixes flex items issue
					break;
				case 'right':
					$content_class[] = 'wpex-float-right';
					$content_class[] = 'wpex-ml-auto'; // fixes flex items issue
					break;
			}

		} else {
			$content_class[] = 'wpex-mx-auto';
		}

		if ( $content_width ) {
			$content_class[] = 'wpex-max-w-100';
		}

		$content_class[] = 'wpex-clr';

		$output .= '<div class="' . esc_attr( implode( ' ', $content_class ) ) . '"' . $content_style . '>';

			/*-------------------------------------------------------------------------------*/
			/* [ Heading ]
			/*-------------------------------------------------------------------------------*/
			if ( $heading ) {

				$heading_tag_escaped = $heading_tag ? tag_escape( $heading_tag ) : 'div';

				if ( $heading_font_family ) {
					vcex_enqueue_font( $heading_font_family );
				}

				$heading_classes = array(
					'vcex-ib-title',
				//	'wpex-font-semibold', // these need to be added with css to prevent conflicts
				//	'wpex-text-4xl',      // these need to be added with css to prevent conflicts
					'wpex-heading',
				);

				if ( empty( $heading_color ) ) {
					$heading_classes[] = 'wpex-inherit-color-important';
				}

				$heading_classes = apply_filters( 'vcex_image_banner_heading_class', $heading_classes, $atts );

				$attrs = array(
					'class' => $heading_classes,
					'style' => vcex_inline_style( array(
						'font_family'    => $heading_font_family,
						'font_weight'    => $heading_font_weight,
						'font_size'      => $heading_font_size,
						'letter_spacing' => $heading_letter_spacing,
						'italic'         => $heading_italic,
						'line_height'    => $heading_line_height,
						'color'          => $heading_color,
						'padding_bottom' => $heading_bottom_padding,
					), false )
				);

				if ( $rfont_size = vcex_get_responsive_font_size_data( $heading_font_size ) ) {
					$attrs['data-wpex-rcss'] = "data-wpex-rcss='" . htmlspecialchars( wp_json_encode( array( 'font-size' => $rfont_size ) ) ) . "'";
				}

				$output .= '<' . $heading_tag_escaped . vcex_parse_html_attributes( $attrs ) . '>';

					$output .= wp_kses_post( do_shortcode( $heading ) );

				$output .= '</' . $heading_tag_escaped . '>';
			}

			/*-------------------------------------------------------------------------------*/
			/* [ Caption ]
			/*-------------------------------------------------------------------------------*/
			if ( $caption ) {

				if ( $caption_font_family ) {
					vcex_enqueue_font( $caption_font_family );
				}

				$caption_classes = array(
					'vcex-ib-caption',
					'wpex-text-lg',
					'wpex-last-mb-0',
				);

				if ( $has_button && ! $caption_bottom_padding ) {
					$caption_classes[] = 'wpex-pb-10';
				}

				$caption_classes = apply_filters( 'vcex_image_banner_caption_class', $caption_classes, $atts );

				$attrs = array(
					'class' => $caption_classes,
					'style' => vcex_inline_style( array(
						'font_family'    => $caption_font_family,
						'font_weight'    => $caption_font_weight,
						'font_size'      => $caption_font_size,
						'letter_spacing' => $caption_letter_spacing,
						'italic'         => $caption_italic,
						'line_height'    => $caption_line_height,
						'color'          => $caption_color,
						'padding_bottom' => $caption_bottom_padding,
					), false )
				);

				if ( $rfont_size = vcex_get_responsive_font_size_data( $caption_font_size ) ) {
					$attrs['data-wpex-rcss'] = "data-wpex-rcss='" . htmlspecialchars( wp_json_encode( array( 'font-size' => $rfont_size ) ) ) . "'";
				}

				$output .= '<div' . vcex_parse_html_attributes( $attrs ) . '>' . wp_kses_post( do_shortcode( $caption ) ) . '</div>';

			}

			/*-------------------------------------------------------------------------------*/
			/* [ Button ]
			/*-------------------------------------------------------------------------------*/
			if ( $has_button ) {

				if ( $button_font_family ) {
					vcex_enqueue_font( $button_font_family );
				}

				$attrs = array(
					'class' => vcex_get_button_classes( $button_style, $button_color ),
					'style' => vcex_inline_style( array(
						'font_family'    => $button_font_family,
						'font_weight'    => $button_font_weight,
						'font_size'      => $button_font_size,
						'letter_spacing' => $button_letter_spacing,
						'italic'         => $button_italic,
						'color'          => $button_custom_color,
						'background'     => $button_custom_background,
						'width'          => $button_width,
						'padding'        => $button_padding,
						'border_radius'  => $button_border_radius,
					), false )
				);

				if ( $rfont_size = vcex_get_responsive_font_size_data( $button_font_size ) ) {
					$attrs['data-wpex-rcss'] = "data-wpex-rcss='" . htmlspecialchars( wp_json_encode( array( 'font-size' => $rfont_size ) ) ) . "'";
				}

				$hover_data = array();
				if ( $button_custom_hover_color ) {
					$hover_data['color'] = esc_attr( $button_custom_hover_color );
				}
				if ( $button_custom_hover_background ) {
					$hover_data['background'] = esc_attr( $button_custom_hover_background );
				}
				if ( $hover_data ) {
					$attrs['data-wpex-hover'] = htmlspecialchars( wp_json_encode( $hover_data ) );
				}

				$output .= '<div class="vcex-ib-button"><span' . vcex_parse_html_attributes( $attrs ) . '>' . do_shortcode( wp_kses_post( $button_text ) ) . '</span></div>';

			}

		$output .= '</div>';

	$output .= '</div>';

	if ( $has_link ) {
		$output .= '</a>';
	}

$output .= '</div>';

if ( $align && vcex_vc_is_inline() ) {
	$output .= '<div class="wpex-clear"></div>';
}

// @codingStandardsIgnoreLine
echo $output;
