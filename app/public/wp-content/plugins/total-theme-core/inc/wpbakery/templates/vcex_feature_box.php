<?php
/**
 * vcex_feature_box shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_feature_box';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Output
$output = '';

// Get and extract shortcode attributes
$atts = extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Sanitize vars
$image         = $image ? $image : 'placeholder';
$equal_heights = $video ? 'false' : $equal_heights;

// Add style
$wrap_style = vcex_inline_style( array(
	'padding'    => $padding,
	'background' => $background,
	'border'     => $border,
	'text_align' => $text_align,
) );

// Classes
$wrap_classes = array(
	'vcex-module',
	'vcex-feature-box',
	'wpex-clr'
);

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $visibility ) {
	$wrap_classes[] = $visibility;
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

if ( $style ) {
	$wrap_classes[] = $style;
}

if ( 'true' == $equal_heights ) {
	$wrap_classes[] = 'vcex-feature-box-match-height';
}

if ( $tablet_widths ) {
	$wrap_classes[] = 'vcex-tablet-collapse';
} elseif ( $phone_widths ) {
	$wrap_classes[] = 'vcex-phone-collapse';
}

if ( 'true' == $content_vertical_align && 'true' !== $equal_heights ) {
	$wrap_classes[] = 'v-align-middle';
	$wrap_classes[] = 'wpex-flex';
	$wrap_classes[] = 'wpex-items-center';
	if ( 'left-content-right-image' == $style ) {
		$wrap_classes[] = 'wpex-flex-row-reverse';
	}
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . $wrap_style . '>';

	// Image/Video check
	if ( $image || $video ) {

		// Add classes
		$media_classes = array(
			'vcex-feature-box-media',
			'wpex-w-50',
		);

		if ( 'left-content-right-image' == $style ) {
			$media_classes[] = 'wpex-float-right';
		} elseif ( 'left-image-right-content' == $style ) {
			$media_classes[] = 'wpex-float-left';
		}

		if ( 'true' == $equal_heights ) {
			$media_classes[] = 'vcex-match-height';
			$media_classes[] = 'wpex-relative';
			$media_classes[] = 'wpex-overflow-hidden';
		}

		// Media style
		$media_style = vcex_inline_style( array(
			'width' => $media_width,
		) );

		$output .= '<div class="' . esc_attr( implode( ' ', $media_classes ) ) . '"' . $media_style . '>';

			// Display Video
			if ( $video ) {

				if ( apply_filters( 'wpex_has_oembed_cache', true ) ) { // filter added for testing purposes.
					global $wp_embed;
					if ( $wp_embed && is_object( $wp_embed ) ) {
						$video_html = $wp_embed->shortcode( array(), $video );
						// Check if output is a shortcode because if the URL is self hosted
						// it will pass through wp_embed_handler_video which returns a video shortcode
						if ( ! empty( $video_html )
							&& is_string( $video_html )
							&& false !== strpos( $video_html, '[video' )
						) {
							$video_html = do_shortcode( $video_html );
						}
						$output .= $video_html;
					}
				} else {
					$video_html = wp_oembed_get( $video );
					if ( ! empty( $video_html ) && ! is_wp_error( $video_html ) ) {
						$output .= '<div class="wpex-responsive-media">' . $video_html . '</div>';
					}
				}

			}

			// Display Image
			elseif ( $image ) {

				// Define thumbnail args
				$thumbnail_args = array(
					'attachment' => $image,
					'size'       => $img_size,
					'width'      => $img_width,
					'height'     => $img_height,
					'crop'       => $img_crop,
					'class'      => 'wpex-block wpex-m-auto',
				);

				// Image inline CSS
				$image_style = '';
				if ( $img_border_radius ) {
					$image_style = vcex_inline_style( array(
						'border_radius' => $img_border_radius,
					) );
					$thumbnail_args['style'] = 'border-radius:' . $img_border_radius . ';';
				}

				// Image classes
				$image_classes = array(
					'vcex-feature-box-image'
				);

				if ( $img_filter ) {
					$image_classes[] = vcex_image_filter_class( $img_filter );
				}

				if ( $img_hover_style && 'true' != $equal_heights ) {
					$image_classes[] = vcex_image_hover_classes( $img_hover_style );
				}

				if ( 'true' == $equal_heights ) {
					$image_classes[] = 'wpex-absolute';
					$image_classes[] = 'wpex-inset-0';
					$image_classes[] = 'wpex-w-100';
					$image_classes[] = 'wpex-h-100';
					$thumbnail_args['class'] .= ' wpex-absolute wpex-max-w-none';
				}

				// Image URL
				if ( $image_url || 'image' == $image_lightbox ) {

					// Standard URL
					$link     = vcex_build_link( $image_url );
					$a_href   = isset( $link['url'] ) ? $link['url'] : '';
					$a_title  = isset( $link['title'] ) ? $link['title'] : '';
					$a_target = isset( $link['target'] ) ? $link['target'] : '';
					$a_target = ( false !== strpos( $a_target, 'blank' ) ) ? ' target="_blank"' : '';

					// Image lightbox
					$data_attributes = '';

					if ( $image_lightbox ) {

						vcex_enqueue_lightbox_scripts();

						if ( 'image' == $image_lightbox || 'self' == $image_lightbox ) {
							$a_href = vcex_get_lightbox_image( $image );
						} elseif ( 'url' == $image_lightbox || 'iframe' == $image_lightbox ) {
							$data_attributes .= ' data-type="iframe"';
						} elseif ( 'video_embed' == $image_lightbox ) {
							$a_href = vcex_get_video_embed_url( $a_href );
						} elseif ( 'inline' == $image_lightbox ) {
							$data_attributes .= ' data-type="inline"';
						}

						if ( $a_href ) {
							$image_classes[] = 'wpex-lightbox';
						}

						// Add lightbox dimensions
						if ( in_array( $image_lightbox, array( 'video_embed', 'url', 'html5', 'iframe', 'inline' ) ) ) {
							$lightbox_dims = vcex_parse_lightbox_dims( $lightbox_dimensions, 'array' );
							if ( $lightbox_dims ) {
								$data_attributes .= ' data-width="' . $lightbox_dims['width'] . '"';
								$data_attributes .= ' data-height="' . $lightbox_dims['height'] . '"';
							}
						}

					}

				}

				// Open link if defined
				if ( ! empty( $a_href ) ) {

					$link_classes = array(
						'vcex-feature-box-image-link',
						'wpex-block',
						'wpex-m-auto',
						'wpex-overflow-hidden' // used for border radius or other mods to the image
					);

					$link_classes = array_merge( $link_classes, $image_classes );

					$output .= '<a href="' . esc_url( $a_href ) . '" title="' . esc_attr( $a_title ) . '" class=" ' . esc_attr( implode( ' ', $link_classes ) ) . '"' . $image_style . '' . $data_attributes . '' . $a_target . '>';


				// Link isn't defined open div
				} else {

					$output .= '<div class="' . esc_attr( implode( ' ', $image_classes ) ) . '"' . $image_style . '>';

				}

				// Display image
				$output .= vcex_get_post_thumbnail( $thumbnail_args );

				// Close link
				if ( isset( $a_href ) && $a_href ) {

					$output .= '</a>';

				// Link not defined, close div
				} else {

					$output .= '</div>';

				}

				} // End video check

			$output .= '</div>'; // close media

		} // $video or $image check

		// Content area
		if ( $content || $heading ) {

			$content_classes = array(
				'vcex-feature-box-content',
				'wpex-w-50',
			);

			if ( 'left-content-right-image' == $style ) {
				$content_classes[] = 'wpex-float-left';
				$content_classes[] = 'wpex-pr-30';
			} elseif ( 'left-image-right-content' == $style ) {
				$content_classes[] = 'wpex-float-right';
				$content_classes[] = 'wpex-pl-30';
			}

			if ( 'true' == $equal_heights ) {
				$content_classes[] = 'vcex-match-height';
			}

			$content_classes[] = 'wpex-clr';

			$content_style = vcex_inline_style( array(
				'width'      => $content_width,
				'background' => $content_background
			) );

			$output .= '<div class="' . esc_attr( implode( ' ', $content_classes ) ) . '"' . $content_style . '>';

			if ( $content_padding ) {

				$style = vcex_inline_style( array(
					'padding' => $content_padding,
				) );

				$output .= '<div class="vcex-feature-box-padding-container wpex-clr"' . $style . '>';

			}

			// Heading
			if ( $heading ) {

				if ( empty( $heading_tag ) ) {
					$heading_tag = apply_filters( 'vcex_feature_box_heading_default_tag', 'h2' );
				}

				$safe_heading_tag = tag_escape( $heading_tag );

				// Load custom font
				if ( $heading_font_family ) {
					vcex_enqueue_font( $heading_font_family );
				}

				// Classes
				$heading_attrs = array(
					'class' => '',
				);

				$heading_class = array(
					'vcex-feature-box-heading',
					'wpex-heading',
					'wpex-text-lg',
					'wpex-mb-20',
				);

				// Heading style
				$heading_attrs['style'] = vcex_inline_style( array(
					'font_family'    => $heading_font_family,
					'color'          => $heading_color,
					'font_size'      => $heading_size,
					'font_weight'    => $heading_weight,
					'margin'         => $heading_margin,
					'letter_spacing' => $heading_letter_spacing,
					'text_transform' => $heading_transform,
				), false );

				// Get responsive data
				if ( $responsive_data = vcex_get_module_responsive_data( $heading_size, 'font_size' ) ) {
					$heading_attrs['data-wpex-rcss'] = $responsive_data;
				}

				// Heading URL
				$a_href = '';
				if ( $heading_url && '||' != $heading_url ) {
					$link     = vcex_build_link( $heading_url );
					$a_href   = isset( $link['url'] ) ? $link['url'] : '';
					$a_title  = isset( $link['title'] ) ? $link['title'] : '';
					$a_target = isset( $link['target'] ) ? $link['target'] : '';
					$a_target = ( false !== strpos( $a_target, 'blank' ) ) ? ' target="_blank"' : '';
				}

				if ( isset( $a_href ) && $a_href ) {

					$output .= '<a href="' . esc_url( do_shortcode( $a_href ) ) . '" title="' . esc_attr( do_shortcode( $a_title ) ) . '"class="vcex-feature-box-heading-link wpex-no-underline"' . $a_target . '>';

				}

				$heading_attrs['class'] = $heading_class;

				$heading_attrs = apply_filters( 'vcex_feature_box_heading_attrs', $heading_attrs, $atts );

				$output .= '<' . $safe_heading_tag . vcex_parse_html_attributes( $heading_attrs ) . '>';

					$output .= wp_kses_post( do_shortcode( $heading ) );

				$output .= '</' . $safe_heading_tag .'>';

				if ( isset( $a_href ) && $a_href ) {
					$output .= '</a>';
				}

			} //  End heading

			// Text
			if ( $content ) {

				$content_attrs = array(
					'class' => 'vcex-feature-box-text wpex-last-mb-0 wpex-clr'
				);

				$content_attrs['style'] = vcex_inline_style( array(
					'font_size'   => $content_font_size,
					'color'       => $content_color,
					'font_weight' => $content_font_weight,
				), false );

				// Get responsive data
				if ( $responsive_data = vcex_get_module_responsive_data( $content_font_size, 'font_size' ) ) {
					$content_attrs['data-wpex-rcss'] = $responsive_data;
				}

				// Output content
				$output .= '<div' . vcex_parse_html_attributes( $content_attrs ) . '>';

					$output .= do_shortcode( wpautop( wp_kses_post( $content ) ) );

				$output .= '</div>';

			} // End content

			// Close padding container
			if ( $content_padding ) {

				$output .= '</div>';

			}

		$output .= '</div>';

	} // End content + Heading wrap

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
