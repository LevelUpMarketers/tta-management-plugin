<?php
/**
 * vcex_post_type_flexslider shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_post_type_flexslider';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Query posts with thumbnails_only
if ( 'over-image' == $caption_location ) {
	$atts['thumbnail_query'] = 'true';
}

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

//Output posts
if ( $vcex_query->have_posts() ) :

	// Load slider scripts
	if ( vcex_vc_is_inline() ) {
		vcex_enqueue_slider_scripts();
		vcex_enqueue_slider_scripts( true ); // needs both in builder incase user switches settings.
	} else {
		$noCarouselThumbnails = ( 'true' == $control_thumbs_carousel ) ? false : true;
		vcex_enqueue_slider_scripts( $noCarouselThumbnails );
	}

	$output = '';

	// Sanitize data, declare main vars & fallbacks
	$wrap_data           = array();
	$slideshow           = vcex_vc_is_inline() ? 'false' : $slideshow;
	$caption             = ! empty( $caption ) ? $caption : 'true';
	$title               = ! empty( $title ) ? $title : 'true';
	$caption_breakpoint  = ! empty( $caption_breakpoint ) ? $caption_breakpoint : 'md';
	$caption_bkp_escaped = sanitize_html_class( $caption_breakpoint );

	// Slider attributes
	if ( 'fade' == $animation || 'fade_slides' == $animation ) {
		$wrap_data[] = 'data-fade="true"';
	}

	if ( apply_filters( 'vcex_sliders_disable_desktop_swipe', true, $shortcode_tag ) ) {
		$wrap_data[] = 'data-touch-swipe-desktop="false"';
	}

	if ( 'true' == $randomize ) {
		$wrap_data[] = 'data-shuffle="true"';
	}

	if ( 'true' == $loop ) {
		$wrap_data[] = ' data-loop="true"';
	}

	if ( 'false' == $slideshow ) {
		$wrap_data[] = 'data-auto-play="false"';
	}

	if ( $slideshow && $slideshow_speed ) {
		$wrap_data[] = 'data-auto-play-delay="' . esc_attr( $slideshow_speed ) . '"';
	}

	if ( 'false' == $direction_nav ) {
		$wrap_data[] = 'data-arrows="false"';
	}

	if ( 'false' == $control_nav ) {
		$wrap_data[] = 'data-buttons="false"';
	}

	if ( 'false' == $direction_nav_hover ) {
		$wrap_data[] = 'data-fade-arrows="false"';
	}

	if ( 'true' == $control_thumbs ) {
		$wrap_data[] = 'data-thumbnails="true"';
	}

	if ( 'true' == $control_thumbs && 'true' == $control_thumbs_pointer ) {
		$wrap_data[] = 'data-thumbnail-pointer="true"';
	}

	if ( $animation_speed ) {
		$wrap_data[] = 'data-animation-speed="' . intval( $animation_speed ) . '"';
	}

	if ( $height_animation ) {
		$height_animation = intval( $height_animation );
		$height_animation = 0 == $height_animation ? '0.0' : $height_animation;
		$wrap_data[] = 'data-height-animation-duration="'. $height_animation . '"';
	}

	if ( 'true' == $control_thumbs && $control_thumbs_height ) {
		$wrap_data[] = 'data-thumbnail-height="' . intval( $control_thumbs_height ) . '"';
	}

	if ( 'true' == $control_thumbs && $control_thumbs_width ) {
		$wrap_data[] = 'data-thumbnail-width="' . intval( $control_thumbs_width ) . '"';
	}

	// Main Classes
	$wrap_classes = array(
		'vcex-module',
		'vcex-posttypes-slider',
		'wpex-slider',
		'slider-pro',
		'vcex-image-slider',
	);

	if ( $bottom_margin ) {
		$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
	}

	if ( $classes ) {
		$wrap_classes[] = vcex_get_extra_class( $classes );
	}

	if ( 'under-image' == $caption_location ) {
		$wrap_classes[] = 'arrows-topright';
	}

	if ( $visibility ) {
		$wrap_classes[] = sanitize_html_class( $visibility );
	}

	if ( 'true' == $excerpt && $excerpt_length ) {
		$wrap_classes[] = 'vcex-posttypes-slider-w-excerpt';
	}

	if ( 'true' == $control_thumbs ) {
		$wrap_classes[] = 'vcex-posttypes-slider-w-thumbnails';
	}

	$wrap_classes[] = 'wpex-clr';

	// Apply filters
	$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

	// Open css wrapper
	if ( $css ) {

		$output .= '<div class="vcex-posttype-slider-css-wrap ' . vcex_vc_shortcode_custom_css_class( $css ) . '">';

	}

	// Display the first image of the slider as a "preloader"
	if ( $first_post = $vcex_query->posts[0]->ID ) {

		$output .= '<div class="wpex-slider-preloaderimg">';

			$output .= vcex_get_post_thumbnail( array(
				'attachment'    => get_post_thumbnail_id( $first_post ),
				'size'          => $img_size,
				'crop'          => $img_crop,
				'width'         => $img_width,
				'height'        => $img_height,
				'attributes'    => array( 'data-no-lazy' => 1 ),
				'apply_filters' => 'vcex_post_type_flexslider_thumbnail_args',
			) );

		$output .= '</div>';

	}

	$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_unique_id( $unique_id ) . implode( ' ', $wrap_data ) . '>';

		$output .= '<div class="wpex-slider-slides sp-slides">';

			// Store posts in an array for use with the thumbnails later
			$posts_cache = array();

			// Loop through posts
			$first_run = true;
			while ( $vcex_query->have_posts() ) :

				// Get post from query
				$vcex_query->the_post();

				if ( ! has_post_thumbnail() ) {
					continue;
				}

				// Get post data
				$post_id   = get_the_ID();
				$post_type = get_post_type();
				$permalink = vcex_get_permalink();
				$esc_title = vcex_esc_title();

				// Store post ids
				$posts_cache[] = $post_id;

				$output .= '<div class="wpex-slider-slide sp-slide">';

					if ( $first_run ) {

						$media_classes = array(
							'wpex-slider-media',
							'wpex-relative',
						);

						if ( ! empty( $img_filter ) ) {
							$media_classes[] = vcex_image_filter_class( $img_filter );
						}

						if ( ! empty( $overlay_style ) && 'none' !== $overlay_style ) {
							$media_classes[] = vcex_image_overlay_classes( $overlay_style );
						}

						$media_classes = apply_filters( 'vcex_post_type_flexslider_media_class', $media_classes );

						$media_classes = implode( ' ', $media_classes );

					}

					$output .= '<div class="' . esc_attr( $media_classes ) . '">';

						$output .= '<a ' . vcex_parse_html_attributes( array(
							'href'   => esc_url( $permalink ),
							'title'  => $esc_title,
							'target' => $link_target,
							'class'  => 'wpex-slider-media-link',
						) ) . '>';

							$output .= vcex_get_post_thumbnail( array(
								'size'          => $img_size,
								'crop'          => $img_crop,
								'width'         => $img_width,
								'height'        => $img_height,
								'attributes'    => array( 'data-no-lazy' => 1 ),
								'apply_filters' => 'vcex_post_type_flexslider_thumbnail_args',
							) );

							// Inner overlay
							$output .= vcex_get_entry_image_overlay( 'inside_link', $shortcode_tag, $atts );

						$output .= '</a>';

						// Outer overlay
						$output .= vcex_get_entry_image_overlay( 'outside_link', $shortcode_tag, $atts );

						// WooComerce Price
						if ( 'product' == $post_type ) {

							$output .= '<div class="vcex-posttype-slider-price wpex-absolute wpex-right-0 wpex-top-0 wpex-mt-20 wpex-mr-20 wpex-py-5 wpex-px-10 wpex-bg-accent wpex-text-white wpex-backface-hidden">';

								$output .= vcex_get_woo_product_price();

							$output .= '</div>';

						}

						if ( 'true' == $caption ) {

							if ( $first_run ) {

								// Caption attributes and classes
								$caption_data = '';
								$caption_classes = array(
									'vcex-posttypes-slider-caption',
									'wpex-backface-hidden',
									sanitize_html_class( $caption_location ),
								);

								if ( $caption_visibility ) {
									$caption_classes[] = sanitize_html_class( $caption_visibility );
								}

								switch ( $caption_location ) {
									case 'over-image':
										$caption_classes[] = 'wpex-z-1';
										$caption_classes[] = 'wpex-text-gray-500';
										$caption_classes[] = 'wpex-' . $caption_bkp_escaped . '-absolute';
										$caption_classes[] = 'wpex-' . $caption_bkp_escaped . '-bottom-0';
										$caption_classes[] = 'wpex-' . $caption_bkp_escaped . '-inset-x-0';
										$caption_classes[] = 'wpex-p-20';
										break;
									case 'under-image':
										if ( vcex_validate_boolean( $control_thumbs ) ) {
											$caption_classes[] = 'wpex-mt-20';
											$caption_classes[] = 'wpex-mb-15';
										} else {
											$caption_classes[] = 'wpex-mt-20';
										}
										break;
								} // end switch $caption_location

								$caption_classes[] = 'wpex-last-mb-0';

								$caption_classes = apply_filters( 'vcex_post_type_flexslider_caption_class', $caption_classes );

								$caption_classes = implode( ' ', $caption_classes );

							}

							$output .= '<div class="' . esc_attr( $caption_classes ) . '"' . $caption_data . '>';

								if ( 'over-image' == $caption_location ) {

									if ( $first_run ) {

										$caption_opacity = ! empty( $caption_opacity ) ? absint( $caption_opacity ) : '80';

										$caption_bg_classes = array(
											'vcex-posttype-slider-caption-bg',
											'wpex-absolute',
											'wpex-inset-0',
											'-wpex-z-1',
											'wpex-bg-black',
											'wpex-' . $caption_bkp_escaped . '-opacity-' . sanitize_html_class( $caption_opacity ),
										);

										$caption_bg_classes = apply_filters( 'vcex_post_type_flexslider_caption_class', $caption_bg_classes );

										$caption_bg_classes = implode( ' ', $caption_bg_classes );

									}

									$output .= '<div class="' . esc_attr( $caption_bg_classes ) . '"></div>';

								}


								if ( 'true' == $title || 'true' == $meta ) {

									$output .= '<header class="vcex-posttype-slider-header wpex-mb-10 wpex-clr">';

										// Display title
										if ( 'true' == $title ) {

											if ( $first_run ) {

												$title_class = array(
													'vcex-posttype-slider-title',
													'entry-title',
													'wpex-text-xl',
													'wpex-mb-5',
												);

												switch ( $caption_location ) {
													case 'over-image':
														$title_class[] = 'wpex-text-white';
														break;
													case 'under-image':
														break;
												}

												$title_class = apply_filters( 'vcex_post_type_flexslider_title_class', $title_class );

												$title_class = implode( ' ', $title_class );

											}

											$output .= '<div class="' . esc_attr( $title_class ) . '">';

												$output .= '<a ' . vcex_parse_html_attributes( array(
													'href'   => esc_url( $permalink ),
													'title'  => $esc_title,
													'target' => $link_target,
													'class'  => 'title',
												) ) . '>' . wp_kses_post( get_the_title() ) . '</a>';

											$output .= '</div>';

										} // End title

										// Meta
										if ( 'true' == $meta ) {

											switch ( $post_type ) {

												case 'staff':

													$position = get_post_meta( $post_id, 'wpex_staff_position', true );

													$output .= '<div class="vcex-posttypes-slider-staff-position wpex-uppercase wpex-text-xs wpex-text-">';

														$output .= do_shortcode( wp_kses_post( $position ) );

													$output .= '</div>';

													break;

												default:

													$output .= '<ul class="vcex-posttypes-slider-meta meta wpex-clr">';

														$output .= '<li class="meta-date"><span class="ticon ticon-clock-o"></span><span class="updated">' . esc_html( get_the_date() ) . '</span></li>';

														$author_link = get_the_author_posts_link();

														if ( $author_link ) {

															if ( false !== strpos( $author_link, 'class="' ) ) {
																$author_link = str_replace( 'class="', 'class="wpex-inherit-color-important', $author_link );
															} else {
																$author_link = str_replace( '<a', '<a class="wpex-inherit-color-important"', $author_link );
															}

															$output .= '<li class="meta-author"><span class="ticon ticon-user-o"></span><span class="vcard author">' . $author_link . '</span></li>';

														}

														// Display category
														if ( 'yes' !== $tax_query ) {

															$category = vcex_get_post_type_cat_tax( $post_type );

															if ( $category ) {

																$terms = vcex_get_list_post_terms( array(
																	'taxonomy' => $category,
																	'class'    => 'wpex-inherit-color-important',
																) );

																if ( $terms ) {

																	$output .= '<li class="meta-category"><span class="ticon ticon-folder-open-o"></span>' . $terms . '</li>';

																}

															}

														}

													$output .= '</ul>';

													break;

											} // end $post_type switch

										} // End meta

									$output .= '</header>';

								}

								// Display excerpt
								if ( 'true' == $excerpt && $excerpt_length ) {

									$output .= '<div class="excerpt wpex-last-mb-0 wpex-clr">';

										$output .= vcex_get_excerpt( array(
											'length' => $excerpt_length,
										) );

									$output .= '</div>';

								}

							$output .= '</div>';

						}

					$output .= '</div>';

				$output .= '</div>';

				$first_run = false;

			endwhile;

		$output .= '</div>';

		// Thumbnails
		if ( vcex_validate_boolean( $control_thumbs ) ) {

			$container_classes = array(
				'wpex-slider-thumbnails',
			);

			if ( vcex_validate_boolean( $control_thumbs_carousel ) ) {
				$container_classes[] = 'sp-thumbnails';
			} else {
				$container_classes[] = 'sp-nc-thumbnails wpex-clr';
			}

			$output .= '<div class="' . esc_attr( implode( ' ', $container_classes ) ) . '">';

				$thumb_args = array(
					'size'          => $img_size,
					'crop'          => $img_crop,
					'width'         => $img_width,
					'height'        => $img_height,
					'attributes'    => array( 'data-no-lazy' => 1 ),
					'apply_filters' => 'vcex_post_type_flexslider_nav_thumbnail_args',
				);

				if ( 'true' == $control_thumbs_carousel ) {
					$thumb_args['class'] = 'wpex-slider-thumbnail sp-thumbnail';
				} else {
					$thumb_args['class'] = 'wpex-slider-thumbnail sp-nc-thumbnail';
					if ( $control_thumbs_height || $control_thumbs_width ) {
						$thumb_args['size']   = null;
						$thumb_args['width']  = $control_thumbs_width ? $control_thumbs_width : null;
						$thumb_args['height'] = $control_thumbs_height ? $control_thumbs_height : null;
					}
				}

				if ( $img_filter ) {
					$thumb_args['class'] .= ' ' . trim( vcex_image_filter_class( $img_filter ) );
				}

				foreach ( $posts_cache as $post_id ) {

					$thumb_args['attachment'] = get_post_thumbnail_id( $post_id );

					$output .= vcex_get_post_thumbnail( $thumb_args );

				}

			$output .= '</div>';

		}

	$output .= '</div>';

	// Close css wrapper
	if ( $css ) {
		$output .= '</div>';
	}

	// Reset the post data to prevent conflicts with WP globals
	wp_reset_postdata();

	// @codingStandardsIgnoreLine
	echo $output;


// If no posts are found display message
else :

	// Display no posts found error if function exists
	echo vcex_no_posts_found_message( $atts );

// End post check
endif; ?>