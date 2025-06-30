<?php
/**
 * vcex_portfolio_carousel shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_portfolio_carousel';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Deprecated Attributes
if ( ! empty( $atts['term_slug'] ) && empty( $atts['include_categories']) ) {
	$atts['include_categories'] = $atts['term_slug'];
}

// Define output
$output = '';

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Define attributes
$atts['post_type'] = 'portfolio';
$atts['taxonomy']  = 'portfolio_category';
$atts['tax_query'] = '';

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

//Output posts
if ( $vcex_query->have_posts() ) :

	// Enqueue scripts
	vcex_enqueue_carousel_scripts();

	// Extract attributes
	extract( $atts );

	// IMPORTANT: Fallback required from VC update when params are defined as empty
	// AKA - set things to enabled by default
	$media   = ( ! $media ) ? 'true' : $media;
	$title   = ( ! $title ) ? 'true' : $title;
	$excerpt = ( ! $excerpt ) ? 'true' : $excerpt;

	// Items to scroll fallback for old setting
	if ( 'page' == $items_scroll ) {
		$items_scroll = $items;
	}

	// Main Classes
	$wrap_classes = array(
		'vcex-module',
		'wpex-carousel',
		'wpex-carousel-portfolio',
		'owl-carousel',
		'wpex-clr',
	);

	// Carousel style
	if ( $style && 'default' != $style ) {
		$wrap_classes[] = $style;
		$arrows_position = ( 'no-margins' == $style && 'default' == $arrows_position ) ? 'abs' : $arrows_position;
	}

	// Alignment
	if ( $content_alignment ) {
		$wrap_classes[] = 'text' . $content_alignment;
	}

	// Arrow style
	$arrows_style = $arrows_style ? $arrows_style : 'default';
	$wrap_classes[] = 'arrwstyle-' . $arrows_style;

	// Arrow position
	if ( $arrows_position && 'default' != $arrows_position ) {
		$wrap_classes[] = 'arrwpos-' . $arrows_position;
	}

	// Bottom margin
	if ( $bottom_margin ) {
		$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
	}

	// Visiblity
	if ( $visibility ) {
		$wrap_classes[] = $visibility;
	}

	// CSS animations
	if ( $css_animation && 'none' != $css_animation ) {
		$wrap_classes[] = vcex_get_css_animation( $css_animation );
	}

	// Lightbox classes & scripts
	if ( 'lightbox' == $thumbnail_link ) {
		vcex_enqueue_lightbox_scripts();
		if ( 'true' == $lightbox_gallery ) {
			$wrap_classes[] = 'wpex-carousel-lightbox';
		}
	}

	// Custom Classes
	if ( $classes ) {
		$wrap_classes[] = vcex_get_extra_class( $classes );
	}

	// Disable autoplay
	if ( vcex_vc_is_inline() || '1' == count( $vcex_query->posts ) ) {
		$atts['auto_play'] = false;
	}

	// Turn arrays into strings
	$wrap_classes = implode( ' ', $wrap_classes );

	// Apply filters
	$wrap_classes = vcex_parse_shortcode_classes( $wrap_classes, $shortcode_tag, $atts );

	// Display header if enabled
	if ( $header ) {

		$output .= vcex_get_module_header( array(
			'style'   => $header_style,
			'content' => $header,
			'classes' => array( 'vcex-module-heading vcex_portfolio_carousel-heading' ),
		) );

	}

	/*--------------------------------*/
	/* [ Begin Carousel Output ]
	/*--------------------------------*/
	$output .= '<div class="' . esc_attr( $wrap_classes ) . '" data-wpex-carousel="' . vcex_get_carousel_settings( $atts, $shortcode_tag ) . '"' . vcex_get_unique_id( $unique_id ) . '>';

		// Start loop
		$lcount = 0;
		$first_run = true;
		while ( $vcex_query->have_posts() ) :

			// Get post from query
			$vcex_query->the_post();

			// Post VARS
			$atts['post_id']        = get_the_ID();
			$atts['post_permalink'] = vcex_get_permalink( $atts['post_id'] );
			$atts['post_title']     = get_the_title( $atts['post_id'] );
			$atts['post_esc_title'] = vcex_esc_title();
			$atts['post_format']    = get_post_format();

			/*--------------------------------*/
			/* [ Begin Entry ]
			/*--------------------------------*/
			$output .= '<div class="wpex-carousel-slide">';

				// Display media
				if ( 'true' == $media ) :

					$media_output = '';

					/*--------------------------------*/
					/* [ Featured Image ]
					/*--------------------------------*/
					if ( has_post_thumbnail() ) {

						$atts['media_type'] = 'thumbnail';

						$thumbnail_class = implode( ' ' , vcex_get_entry_thumbnail_class(
							array( 'wpex-carousel-entry-img' ),
							$shortcode_tag,
							$atts
						) );

						// Image html
						$img_html = vcex_get_post_thumbnail( array(
							'size'          => $img_size,
							'crop'          => $img_crop,
							'width'         => $img_width,
							'height'        => $img_height,
							'class'         => $thumbnail_class,
							'attributes'    => array( 'data-no-lazy' => 1 ),
							'apply_filters' => 'vcex_portfolio_carousel_thumbnail_args',
							'filter_arg1'   => $atts,
						) );

						$media_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_media_class( array( 'wpex-carousel-entry-media' ), $shortcode_tag, $atts ) ) ) . '">';

							switch ( $thumbnail_link ) {

								// No links
								case 'none':

									$media_output .= $img_html;
									$media_output .= vcex_get_entry_media_after( $shortcode_tag );

									break;

								// Lightbox
								case 'lightbox':

									$lcount ++;

									$atts['lightbox_data'] = array(); // must reset for each item

									$lightbox_image_escaped = vcex_get_lightbox_image();

									$atts['lightbox_link'] = $lightbox_image_escaped;

									if ( 'true' == $lightbox_gallery ) {
										$atts['lightbox_class'] = 'wpex-carousel-lightbox-item';
									} else {
										$atts['lightbox_class'] = 'wpex-lightbox';
									}

									// Check for video
									if ( $oembed_video_url = vcex_get_post_video_oembed_url( $atts['post_id'] ) ) {
										$embed_url = vcex_get_video_embed_url( $oembed_video_url );
										if ( $embed_url ) {
											$atts['lightbox_link']               = esc_url( $embed_url );
											$atts['lightbox_data']['data-thumb'] = 'data-thumb="' . $lightbox_image_escaped . '"';
										}
									}

									$link_attrs = array(
										'href'       => $atts['lightbox_link'],
										'class'      => 'wpex-carousel-entry-img ' . $atts['lightbox_class'],
										'title'      => $atts['post_esc_title'],
										'data-title' => $atts['post_esc_title'],
										'data-count' => $lcount,
									);

									if ( ! empty( $atts['lightbox_data'] ) ) {
										foreach ( $atts['lightbox_data'] as $ld_k => $ld_v ) {
											$link_attrs[$ld_k] = $ld_v;
										}
									}

									$media_output .= '<a'. vcex_parse_html_attributes( $link_attrs ) . '>';

									$media_output .= $img_html;

									break;

								// Link to post
								default :

									$media_output .= '<a href="' . esc_url( $atts['post_permalink'] ) . '" title="' . $atts['post_esc_title'] . '" class="wpex-carousel-entry-img">';

									$media_output .= $img_html;

									break;

							} // end switch

							// Inner Overlay
							$media_output .= vcex_get_entry_image_overlay( 'inside_link', $shortcode_tag, $atts );

							// Entry after media hook
							$media_output .= vcex_get_entry_media_after( $shortcode_tag );

							// Close link
							if ( 'none' != $thumbnail_link ) {
								$media_output .= '</a>';
							}

							// Outside Overlay
							$media_output .= vcex_get_entry_image_overlay( 'outside_link', $shortcode_tag, $atts );

						$media_output .= '</div>';

					}

					$output .= apply_filters( 'vcex_portfolio_carousel_media', $media_output, $atts );

				endif;

				/*--------------------------------*/
				/* [ Entry Details ]
				/*--------------------------------*/
				if ( 'true' == $title || 'true' == $excerpt || 'true' == $read_more ) :

					if ( $first_run ) {

						$content_style = array(
							'opacity' => $content_opacity,
						);

						if ( empty( $content_css ) ) {

							if ( ! empty( $content_background ) ) {
								$content_style['background'] = $content_background;
							}

							if ( ! empty( $content_padding ) ) {
								$content_style['padding'] = $content_padding;
							}

							if ( ! empty( $content_margin ) ) {
								$content_style['margin'] = $content_margin;
							}

							if ( ! empty( $content_border ) ) {
								$content_style['border'] = $content_border;
							}

						}

						$content_style = vcex_inline_style( $content_style );

					}

					$output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_details_class( array( 'wpex-carousel-entry-details' ), $shortcode_tag, $atts ) ) ) . '"' . $content_style . '>';

						/*--------------------------------*/
						/* [ Entry Title ]
						/*--------------------------------*/
						if ( 'true' == $title && $atts['post_title'] ) {

							$title_output = '';

							if ( $first_run ) {

								// Title design
								$heading_style = vcex_inline_style( array(
									'margin'         => $content_heading_margin,
									'text_transform' => $content_heading_transform,
									'font_size'      => $content_heading_size,
									'font_weight'    => $content_heading_weight,
									'line_height'    => $content_heading_line_height,
								) );

								// Heading color
								$content_heading_color = vcex_inline_style( array(
									'color' => $content_heading_color,
								) );

							}

							$title_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_title_class( array( 'wpex-carousel-entry-title' ), $shortcode_tag, $atts ) ) ) . '"' . $heading_style . '>';

								$title_output .= '<a href="' . esc_url( $atts['post_permalink'] ) . '"' . $content_heading_color . '>';

									$title_output .= esc_html( $atts['post_title'] );

								$title_output .= '</a>';

							$title_output .= '</div>';

							$output .= apply_filters( 'vcex_portfolio_carousel_title', $title_output, $atts );

						}

						/*--------------------------------*/
						/* [ Entry Excerpt ]
						/*--------------------------------*/
						if ( 'true' == $excerpt ) {

							$excerpt_output = '';

							if ( $first_run ) {
								$excerpt_styling = vcex_inline_style( array(
									'color'     => $content_color,
									'font_size' => $content_font_size,
								) );
							}

							// Generate excerpt
							$atts['post_excerpt'] = vcex_get_excerpt( array(
								'length'  => $excerpt_length,
								'context' => $shortcode_tag,
							) );

							if ( $atts['post_excerpt'] ) {

								$excerpt_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_excerpt_class( array( 'wpex-carousel-entry-excerpt' ), $shortcode_tag, $atts ) ) ) . '"' . $excerpt_styling . '>';

									$excerpt_output .= $atts['post_excerpt']; // Escaped via wp_trim_words

								$excerpt_output .= '</div>';

							}

							$output .= apply_filters( 'vcex_portfolio_carousel_excerpt', $excerpt_output, $atts );

						}

						/*--------------------------------*/
						/* [ Entry Read More ]
						/*--------------------------------*/
						if ( 'true' == $read_more ) {

							$readmore_output = '';

							if ( $first_run ) {

								// Readmore classes
								$readmore_classes = vcex_get_button_classes( $readmore_style, $readmore_style_color );

								// Readmore style
								$readmore_style = vcex_inline_style( array(
									'background'    => $readmore_background,
									'color'         => $readmore_color,
									'font_size'     => $readmore_size,
									'padding'       => $readmore_padding,
									'border_radius' => $readmore_border_radius,
									'margin'        => $readmore_margin,
								), false );

								// Readmore data
								$readmore_hover_data = array();

								if ( $readmore_hover_background ) {
									$readmore_hover_data['background'] = $readmore_hover_background;
								}

								if ( $readmore_hover_color ) {
									$readmore_hover_data['color'] = $readmore_hover_color;
								}

								if ( $readmore_hover_data ) {
									$readmore_hover_data = htmlspecialchars( wp_json_encode( $readmore_hover_data ) );
								}

							}

							$readmore_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_button_wrap_class( array( 'wpex-carousel-entry-button' ), $shortcode_tag, $atts ) ) ) . '">';

								$attrs = array(
									'href'  => esc_url( $atts['post_permalink'] ),
									'class' => $readmore_classes,
									'style' => $readmore_style,
								);

								if ( $readmore_hover_data ) {
									$attrs['data-wpex-hover'] = $readmore_hover_data;
								}

								$readmore_output .= '<a' . vcex_parse_html_attributes( $attrs ) . '>';

									if ( $read_more_text ) {
										$readmore_output .= $read_more_text;
									} else {
										$readmore_output .= esc_html__( 'read more', 'total' );
									}

									if ( 'true' == $readmore_rarr ) {

										$readmore_output .= ' <span class="vcex-readmore-rarr">' . vcex_readmore_button_arrow() . '</span>';

									}

								$readmore_output .= '</a>';

							$readmore_output .= '</div>';

							$output .= apply_filters( 'vcex_portfolio_carousel_readmore', $readmore_output, $atts );

						}

					$output .= '</div>';

				endif;

			$output .= '</div>';

		$first_run = false;

	endwhile;

	$output .= '</div>';

	// Reset the post data to prevent conflicts with WP globals
	wp_reset_postdata();

	// @codingStandardsIgnoreLine
	echo $output;


// If no posts are found display message
else :

	// Display no posts found error if function exists
	echo vcex_no_posts_found_message( $atts );

// End post check
endif;