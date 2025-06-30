<?php
/**
 * vcex_testimonials_carousel shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_testimonials_carousel';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define output
$output = '';

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Define attributes
$atts['post_type'] = 'testimonials';
$atts['taxonomy']  = 'testimonials_category';
$atts['tax_query'] = '';

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

//Output posts
if ( $vcex_query->have_posts() ) :

	// Enqueue scripts
	vcex_enqueue_carousel_scripts();

	// Extract attributes
	extract( $atts );

	// Define wrap attributes
	$wrap_attrs = array();

	// Add unique ID to wrap attributes
	if ( $unique_id ) {
		$wrap_attrs['id'] = esc_attr( $unique_id );
	}

	// Main Wrap Classes
	$wrap_classes = array(
		'vcex-module',
		'wpex-carousel',
		'vcex-testimonials-carousel',
		'owl-carousel',
		'wpex-clr',
	);

	$arrows_style = $arrows_style ? $arrows_style : 'default';
	$wrap_classes[] = 'arrwstyle-' . sanitize_html_class( $arrows_style );

	if ( $arrows_position && 'default' != $arrows_position ) {
		$wrap_classes[] = 'arrwpos-' . sanitize_html_class( $arrows_position );
	}

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

	if ( $css ) {
		$wrap_classes[] = vcex_vc_shortcode_custom_css_class( $css );
	}

	$wrap_attrs['class'] = esc_attr( vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ) , $shortcode_tag, $atts ) );

	// Disable autoplay
	if ( vcex_vc_is_inline() || '1' == count( $vcex_query->posts ) ) {
		$atts['auto_play'] = false;
	}

	// Image Style
	$img_style = vcex_inline_style( array(
		'border_radius' => $img_border_radius,
	), false );

	// Load custom title font
	vcex_enqueue_font( $title_font_family );

	// Title style
	$title_style = '';
	if ( 'true' == $title ) {
		$title_style = vcex_inline_style( array(
			'font_size'     => $title_font_size,
			'font_family'   => $title_font_family,
			'color'         => $title_color,
			'margin_bottom' => $title_bottom_margin,
		) );
	}

	// Excerpt style
	$content_style = vcex_inline_style( array(
		'font_size' => $content_font_size,
		'color'     => $content_color,
	) );

	// Open wrapper for auto height
	if ( 'true' == $auto_height ) {
		$output .= '<div class="owl-wrapper-outer">';
	}

	// Display header if enabled
	if ( $header ) {

		$output .= vcex_get_module_header( array(
			'style'   => $header_style,
			'content' => $header,
			'classes' => array( 'vcex-module-heading vcex_testimonials_carousel-heading' ),
		) );

	}

	/*--------------------------------*/
	/* [ Carousel Start ]
	/*--------------------------------*/
	$output .= '<div' . vcex_parse_html_attributes( $wrap_attrs ) . ' data-wpex-carousel="' . vcex_get_carousel_settings( $atts, $shortcode_tag ) . '">';

		// Start loop
		$first_run = true;
		while ( $vcex_query->have_posts() ) :

			// Get post from query
			$vcex_query->the_post();

			// Post VARS
			$atts['post_id']           = get_the_ID();
			$atts['post_title']        = get_the_title();
			$atts['post_permalink']    = vcex_get_permalink();
			$atts['post_meta_author']  = get_post_meta( $atts['post_id'], 'wpex_testimonial_author', true );
			$atts['post_meta_company'] = get_post_meta( $atts['post_id'], 'wpex_testimonial_company', true );
			$atts['post_meta_url']     = get_post_meta( $atts['post_id'], 'wpex_testimonial_url', true );

			/*--------------------------------*/
			/* [ Entry Start ]
			/*--------------------------------*/
			$output .= '<div class="wpex-carousel-slide">';

				$output .= '<div ' . vcex_get_post_class( array( 'testimonial-entry' ) ) . '>';

					$content_class = (array) apply_filters( 'wpex_testimonials_entry_content_class', array(
						'testimonial-entry-content',
						'wpex-relative', // for caret
						'wpex-boxed',
						'wpex-border-0',
						'wpex-clr',
					) );

					$output .= '<div class="' . esc_attr( implode( ' ', $content_class ) ) . '">';

						$output .= '<span class="testimonial-caret"></span>';

						/*--------------------------------*/
						/* [ Title ]
						/*--------------------------------*/
						$title_output = '';
						if ( 'true' == $title ) :

							$title_tag_escaped = tag_escape( $title_tag );

							$title_class = array(
								'testimonial-entry-title',
								'entry-title',
								'wpex-mb-10',
							);

							$title_class = (array) apply_filters( 'wpex_testimonials_entry_title_class', $title_class );

							$title_output .= '<' . $title_tag_escaped . ' class="' . esc_attr( implode( ' ', $title_class ) ) . '"' . $title_style . '>';

								// Title with link
								if ( 'true' == $atts['title_link'] ) {

									$title_output .= '<a href="' . esc_url( $atts['post_permalink'] ) . '">';

										$title_output .= esc_html( $atts['post_title'] );

									$title_output .= '</a>';

								}

								// Title without link
								else {

									$title_output .= esc_html( $atts['post_title'] );

								}

							$title_output .= '</' . $title_tag_escaped . '>';

							$output .= apply_filters( 'vcex_testimonials_carousel_title', $title_output, $atts );

						endif;

						/*--------------------------------*/
						/* [ Details ]
						/*--------------------------------*/
						$output .= '<div class="testimonial-entry-details testimonial-entry-text wpex-last-mb-0 wpex-clr"'. $content_style .'>';

							// Display excerpt if enabled (default dispays full content )
							$excerpt_output = '';
							if ( 'true' == $excerpt ) :

								// Custom readmore text
								if ( 'true' == $read_more ) :

									// Add arrow
									if ( 'false' != $read_more_rarr ) {
										$read_more_rarr_html = ' <span>' . vcex_readmore_button_arrow() . '</span>';
									} else {
										$read_more_rarr_html = '';
									}

									// Read more text
									if ( is_rtl() ) {
										$read_more_link = '&#8230;<a href="' . esc_url( $atts['post_permalink'] ) . '" title="' . esc_attr( $read_more_text ) . '">' . wp_kses_post( $read_more_text ) .'</a>';
									} else {
										$read_more_link = '&#8230;<a href="' . esc_url( $atts['post_permalink'] ) . '" title="' . esc_attr( $read_more_text ) . '">' . wp_kses_post( $read_more_text ) . $read_more_rarr_html .'</a>';
									}

								else :

									$read_more_link = '&#8230;';

								endif;

								// Custom Excerpt function
								$excerpt_output .= vcex_get_excerpt( array(
									'post_id' => $atts['post_id'],
									'length'  => $excerpt_length,
									'more'    => $read_more_link,
									'context' => $shortcode_tag,
								) );

							// Display full post content
							else :

								$excerpt_output .= vcex_the_content( get_the_content(), $shortcode_tag );

							// End excerpt check
							endif;

							$output .= apply_filters( 'vcex_testimonials_carousel_excerpt', $excerpt_output, $atts );

						$output .= '</div>';

					$output .= '</div>';

					/*--------------------------------*/
					/* [ Bottom ]
					/*--------------------------------*/
					$bottom_class = (array) apply_filters( 'wpex_testimonials_entry_bottom_class', array(
						'testimonial-entry-bottom',
						'wpex-flex',
						'wpex-flex-wrap',
						'wpex-mt-20',
					) );

					$bottom_output = '<div class="' . esc_attr( implode( ' ', $bottom_class ) ) . '">';

						/*--------------------------------*/
						/* [ Thumbnail ]
						/*--------------------------------*/
						$media_output = '';
						if ( has_post_thumbnail( $atts['post_id'] ) && 'true' == $entry_media ) {

							$media_class = array(
								'testimonial-entry-thumb',
								'wpex-max-w-100',
								'wpex-flex-shrink-0',
								'wpex-mr-20',
							);

							if ( $img_width || $img_height || ! in_array( $img_size, array( 'wpex_custom', 'testimonials_entry' ) ) ) {
								$media_class[] = 'custom-dims';
							} else {
								$media_class[] = 'default-dims';
							}

							$media_class = (array) apply_filters( 'wpex_testimonials_entry_media_class', $media_class );

							$media_output .= '<div class="' . esc_attr( implode( ' ', $media_class ) ) . '">';

								// Thumbnail classes
								$thumb_class = array(
									'testimonials-entry-img',
									'wpex-align-middle',
									'wpex-round',
									'wpex-border',
									'wpex-border-solid',
									'wpex-border-main',
								);

								$thumb_class = apply_filters( 'wpex_testimonials_entry_thumbnail_class', $thumb_class );

								// Display post thumbnail
								$media_output .= vcex_get_post_thumbnail( array(
									'attachment'    => get_post_thumbnail_id( $atts['post_id'] ),
									'size'          => $img_size,
									'width'         => $img_width,
									'height'        => $img_height,
									'style'         => $img_style,
									'crop'          => $img_crop,
									'class'         => $thumb_class,
									'attributes'    => array( 'data-no-lazy' => 1 ),
									'apply_filters' => 'vcex_testimonials_carousel_thumbnail_args',
									'filter_arg1'   => $atts,
								) );

							$media_output .= '</div>';

						}

						$bottom_output .= apply_filters( 'vcex_testimonials_carousel_media', $media_output, $atts );

						/*--------------------------------*/
						/* [ Meta ]
						/*--------------------------------*/
						$meta_class = array(
							'testimonial-entry-meta',
							'wpex-flex-grow',
						);

						$meta_class = (array) apply_filters( 'wpex_testimonials_entry_meta_class', $meta_class );

						$bottom_output .= '<div class="' . esc_attr( implode( ' ', $meta_class ) ) . '">';

							/*--------------------------------*/
							/* [ Author ]
							/*--------------------------------*/
							$author_output = '';
							if ( 'true' == $author && $atts['post_meta_author'] ) :

								$author_class = array(
									'testimonial-entry-author',
									'entry-title',
									'wpex-m-0',
								);

								$author_class = (array) apply_filters( 'wpex_testimonials_entry_author_class', $author_class );

								$author_output .= '<span class="' . esc_attr( implode( ' ', $author_class ) ) .'">';

									$author_output .= wp_kses_post( $atts['post_meta_author'] );

								$author_output .= '</span>';

								$bottom_output .= apply_filters( 'vcex_testimonials_carousel_author', $author_output, $atts );

							endif;

							/*--------------------------------*/
							/* [ Company ]
							/*--------------------------------*/
							$company_output = '';
							if ( 'true' == $company ) {

								if ( $atts['post_meta_company'] ) {

									if ( $first_run ) {

										$company_class = array(
											'testimonial-entry-company',
											'wpex-text-gray-500',
										);

										$company_class = (array) apply_filters( 'wpex_testimonials_entry_company_class', $company_class );

										$company_class = implode( ' ', $company_class );

										if ( function_exists( 'wpex_get_testimonial_company_url_target' ) ) {
											$company_target = wpex_get_testimonial_company_url_target();
										} else {
											$company_target = '_blank';
										}

										if ( 'blank' === $company_target || '_blank' === $company_target ) {
											$company_target_escaped = ' target="_blank" rel="noopener noreferrer"';
										} else {
											$company_target_escaped = '';
										}

									}

									// Display testimonial company with URL
									if ( $atts['post_meta_url'] ) {

										$company_output .= '<a href="'. esc_url( $atts['post_meta_url'] ) .'" class="' . esc_attr( $company_class ) . '"' . $company_target_escaped . '>';

											$company_output .= wp_kses_post( $atts['post_meta_company'] );

										$company_output .= '</a>';

									// Display testimonial company without URL since it's not defined
									} else {

										$company_output .= '<span class="' . esc_attr( $company_class ) . '">';

											$company_output .= wp_kses_post( $atts['post_meta_company'] );

										$company_output .= '</span>';

									}

								}

								$bottom_output .= apply_filters( 'vcex_testimonials_carousel_company', $company_output, $atts );

							}

							/*--------------------------------*/
							/* [ Rating ]
							/*--------------------------------*/
							$rating_output = '';
							if ( 'true' == $rating ) {

								$atts['post_rating'] = vcex_get_star_rating( '', $atts['post_id'] );

								if ( ! empty( $atts['post_rating'] ) ) {

									$rating_class = array(
										'testimonial-entry-rating',
									);

									$rating_class = (array) apply_filters( 'wpex_testimonials_entry_rating_class', $rating_class );

									$rating_output .= '<div class="' . esc_attr( implode( ' ', $rating_class ) ) . '">'. $atts['post_rating'] .'</div>';

								}

								$bottom_output .= apply_filters( 'vcex_testimonials_carousel_rating', $rating_output, $atts );

							}

						$bottom_output .= '</div>'; // close meta

					$bottom_output .= '</div>'; // close bottom

					$output .= apply_filters( 'vcex_testimonials_carousel_bottom', $bottom_output, $atts );

				$output .= '</div>';

			$output .= '</div>';

			$first_run = false;

		endwhile;

	$output .= '</div>';

	// Close wrap for single item auto height
	if ( 'true' == $auto_height ) {
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
endif;