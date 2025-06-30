<?php
/**
 * vcex_testimonials_grid shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_testimonials_grid';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define output
$output = '';

// Deprecated Attributes
if ( ! empty( $atts['term_slug'] ) && empty( $atts['include_categories'] ) ) {
	$atts['include_categories'] = $atts['term_slug'];
}

// Store orginal atts value for use in non-builder params
$og_atts = $atts;

// Define entry counter
$entry_count = ! empty( $og_atts['entry_count'] ) ? $og_atts['entry_count'] : 0;

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Add paged attribute for load more button (used for WP_Query)
if ( ! empty( $og_atts['paged'] ) ) {
	$atts['paged'] = $og_atts['paged'];
}

// Define user-generated attributes
$atts['post_type'] = 'testimonials';
$atts['taxonomy']  = 'testimonials_category';
$atts['tax_query'] = '';

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

// Output posts
if ( $vcex_query->have_posts() ) :

	// IMPORTANT: Fallback required from VC update when params are defined as empty such as entry_media="" which would result in (bool) True
	// AKA - set things to enabled by default
	$entry_media = ( ! $entry_media ) ? 'true' : $entry_media;
	$title       = ( ! $title ) ? 'true' : $title;
	$excerpt     = ( ! $excerpt ) ? 'true' : $excerpt;
	$read_more   = ( ! $read_more ) ? 'true' : $read_more;

	// Declare and sanitize vars
	$wrap_classes  = array( 'vcex-module', 'vcex-testimonials-grid-wrap', 'wpex-clr' );
	$grid_classes  = array( 'wpex-row', 'vcex-testimonials-grid', 'wpex-clr' );
	$grid_data     = array();
	$is_isotope    = false;
	$css_animation = vcex_get_css_animation( $css_animation );
	$css_animation = ( 'true' == $filter ) ? false : $css_animation;
	$title_tag     = $title_tag ? $title_tag : 'div';

	// Is Isotope var
	if ( 'true' == $filter || 'masonry' == $grid_style ) {
		$is_isotope = true;
		vcex_enqueue_isotope_scripts();
	}

	// Get filter taxonomy
	if ( 'true' == $filter ) {
		$filter_taxonomy = apply_filters( 'vcex_filter_taxonomy', $atts['taxonomy'], $atts );
		$filter_taxonomy = taxonomy_exists( $filter_taxonomy ) ? $filter_taxonomy : '';
		if ( $filter_taxonomy ) {
			$atts['filter_taxonomy'] = $filter_taxonomy; // Add to array to pass on to vcex_grid_filter_args()
		}
	} else {
		$filter_taxonomy = null;
	}

	// Get filter categories
	if ( $filter_taxonomy ) {

		// Get filter terms
		$filter_terms = get_terms( $filter_taxonomy, vcex_grid_filter_args( $atts, $vcex_query ) );

		// Make sure we have terms before doing things
		if ( $filter_terms ) {

			// Check url for filter cat
			if ( $active_cat_query_arg = vcex_grid_filter_get_active_item( $filter_taxonomy ) ) {
				$filter_active_category = $active_cat_query_arg;
			}

			// Check if filter active cat exists on current page
			$filter_has_active_cat = in_array( $filter_active_category, wp_list_pluck( $filter_terms, 'term_id' ) ) ? true : false;

			// Add show on load animation when active filter is enabled to prevent double animation
			if ( $filter_has_active_cat ) {
				$grid_classes[] = 'wpex-show-on-load';
			}

		} else {

			$filter = false; // No terms so we can't have a filter

		}

	}

	// Wrap classes
	if ( $bottom_margin ) {
		$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
	}

	if ( $visibility ) {
		$wrap_classes[] = $visibility;
	}

	if ( $classes ) {
		$wrap_classes[] = vcex_get_extra_class( $classes );
	}

	// Grid Classes
	if ( $columns_gap ) {
		$grid_classes[] = 'gap-' . sanitize_html_class( $columns_gap );
	}

	if ( $is_isotope ) {
		$grid_classes[] = 'vcex-isotope-grid';
	}

	// Data
	if ( $is_isotope && 'true' == $filter ) {
		if ( 'no_margins' != $grid_style && $masonry_layout_mode ) {
			$grid_data[] = 'data-layout-mode="' . esc_attr( $masonry_layout_mode ) . '"';
		}
		if ( $filter_speed ) {
			$grid_data[] = 'data-transition-duration="' . esc_attr( $filter_speed ) . '"';
		}
		if ( ! empty( $filter_has_active_cat ) ) {
			$grid_data[] = 'data-filter=".cat-' . esc_attr( $filter_active_category ) . '"';
		}
	} else {

		$isotope_transition_duration = apply_filters( 'vcex_isotope_transition_duration', null, $shortcode_tag );
		if ( $isotope_transition_duration ) {
			$grid_data[] = 'data-transition-duration="' . esc_attr( $isotope_transition ) . '"';
		}

	}

	// Load custom fonts
	vcex_enqueue_font( $title_font_family );

	// Columns classes
	$columns_class = vcex_get_grid_column_class( $atts );

	// Excerpt style
	$content_style = vcex_inline_style( array(
		'font_size' => $content_font_size,
		'color'     => $content_color,
	) );

	// Apply filters
	$wrap_classes  = (array) apply_filters( 'vcex_testimonials_grid_wrap_classes', $wrap_classes ); // @todo deprecate?
	$grid_classes  = (array) apply_filters( 'vcex_testimonials_grid_classes', $grid_classes );
	$grid_data     = apply_filters( 'vcex_testimonials_grid_data_attr', $grid_data );

	// Convert arrays into strings
	$wrap_classes  = implode( ' ', $wrap_classes );
	$grid_classes  = implode( ' ', $grid_classes );
	$grid_data     = $grid_data ? ' '. implode( ' ', $grid_data ) : '';

	// VC filter
	$wrap_classes = vcex_parse_shortcode_classes( $wrap_classes, $shortcode_tag, $atts );

	// Begin shortcode output
	$output .= '<div class="'. esc_attr( $wrap_classes ) .'"'. vcex_get_unique_id( $unique_id ) .'>';

		// Display header if enabled
		if ( $header ) {

			$output .= vcex_get_module_header( array(
				'style'   => $header_style,
				'content' => $header,
				'classes' => array( 'vcex-module-heading vcex_testimonials_grid-heading' ),
			) );

		}

		/*--------------------------------*/
		/* [ Entry Filter ]
		/*--------------------------------*/
		if ( 'true' == $filter && ! empty( $filter_terms ) ) {

			// Sanitize all text
			$all_text = $all_text ? $all_text : esc_html__( 'All', 'total' );

			// Filter button classes
			$filter_button_classes = vcex_get_button_classes( $filter_button_style, $filter_button_color );

			// Filter font size
			$filter_style_escaped = vcex_inline_style( array(
				'font_size' => $filter_font_size,
			) );

			$filter_classes = array(
				'vcex-testimonials-filter',
				'vcex-filter-links',
				'wpex-clr',
			);

			if ( 'yes' == $center_filter ) {
				$filter_classes[] = 'center';
			}

			$output .= '<ul class="' . esc_attr( implode( ' ', $filter_classes ) ) . '"' . $filter_style_escaped . '>';

				if ( 'true' == $filter_all_link ) {

					$output .= '<li';

						if ( ! $filter_has_active_cat ) {
							$output .= ' class="active"';
						}

					$output .= '>';

						$output .= '<a href="#" data-filter="*" class="' . esc_attr( $filter_button_classes ) . '"><span>' . wp_strip_all_tags( $all_text ) . '</span></a>';

					$output .= '</li>';

				}

				foreach ( $filter_terms as $term ) :

					$output .= '<li class="filter-cat-' . sanitize_html_class( $term->term_id );

						if ( $filter_active_category == $term->term_id ) {
							$output .= ' active';
						}

					$output .= '">';

					$output .= '<a href="#" data-filter=".cat-' . sanitize_html_class( $term->term_id ) . '" class="' . esc_attr( $filter_button_classes ) . '">';

						$output .= wp_strip_all_tags( $term->name );

					$output .= '</a></li>';

				endforeach;

				if ( $vcex_after_grid_filter = apply_filters( 'vcex_after_grid_filter', '', $atts ) ) {
					$output .= $vcex_after_grid_filter;
				}

			$output .= '</ul>';

		}

		$output .= '<div class="' . esc_attr( $grid_classes ) . '"' . $grid_data . '>';

			// Start loop
			$first_run = true;
			while ( $vcex_query->have_posts() ) :

				// Get post from query
				$vcex_query->the_post();

				// Add to the counter var
				$entry_count++;

				// Get post data
				$atts['post_id']           = get_the_ID();
				$atts['post_title']        = get_the_title();
				$atts['post_esc_title']    = vcex_esc_title();
				$atts['post_permalink']    = vcex_get_permalink();
				$atts['post_meta_author']  = get_post_meta( $atts['post_id'], 'wpex_testimonial_author', true );
				$atts['post_meta_company'] = get_post_meta( $atts['post_id'], 'wpex_testimonial_company', true );
				$atts['post_meta_url']     = get_post_meta( $atts['post_id'], 'wpex_testimonial_url', true );

				// Add classes to the entries
				$entry_classes = array(
					'testimonial-entry',
					'vcex-grid-item'
				);

				$entry_classes[] = $columns_class;

				$entry_classes[] = 'col-' . sanitize_html_class( $entry_count );

				if ( 'false' == $columns_responsive ) {
					$entry_classes[] = 'nr-col';
				} else {
					$entry_classes[] = 'col';
				}

				if ( $css_animation ) {
					$entry_classes[] = $css_animation;
				}

				if ( $is_isotope ) {
					$entry_classes[] = 'vcex-isotope-entry';
				}

				/*--------------------------------*/
				/* [ Begin Entry Output ]
				/*--------------------------------*/
				$output .= '<div ' . vcex_grid_get_post_class( $entry_classes, $atts['post_id'] ) . '>';

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

							if ( $first_run ) {

								$title_tag_escaped = tag_escape( $title_tag );

								$title_class = array(
									'testimonial-entry-title',
									'entry-title',
									'wpex-mb-10',
								);

								$title_class = (array) apply_filters( 'wpex_testimonials_entry_title_class', $title_class );

								$title_style = vcex_inline_style( array(
									'font_size'     => $title_font_size,
									'font_family'   => $title_font_family,
									'color'         => $title_color,
									'margin_bottom' => $title_bottom_margin,
								) );

							}

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

							$title_output .= '</'. $title_tag_escaped .'>';

							$output .= apply_filters( 'vcex_testimonials_grid_title', $title_output, $atts );

						endif;

						$output .= '<div class="testimonial-entry-details testimonial-entry-text wpex-last-mb-0 wpex-clr"' . $content_style . '>';

							/*--------------------------------*/
							/* [ Excerpt ]
							/*--------------------------------*/
							$excerpt_output = '';
							if ( 'true' == $excerpt ) {

								// Custom readmore text
								if ( 'true' == $read_more ) {

									// Add arrow
									if ( 'false' != $read_more_rarr ) {
										$read_more_rarr_html = ' <span>' . vcex_readmore_button_arrow() . '</span>';
									} else {
										$read_more_rarr_html = '';
									}

									// Read more text
									if ( is_rtl() ) {
										$read_more_link = '&#8230;<a href="' . esc_url( $atts['post_permalink'] ) . '">' . $read_more_text . '</a>';
									} else {
										$read_more_link = '&#8230;<a href="' . esc_url( $atts['post_permalink'] ) . '">' . esc_html( $read_more_text ) . $read_more_rarr_html . '</a>';
									}

								} else {
									$read_more_link = '&#8230;';
								}

								// Custom Excerpt function
								$excerpt_output .= vcex_get_excerpt( array(
									'post_id' => $atts['post_id'],
									'length'  => $excerpt_length,
									'more'    => $read_more_link,
									'context' => $shortcode_tag,
								) );

							// Display full post content
							} else {

								$excerpt_output .= vcex_the_content( get_the_content(), $shortcode_tag );

							// End excerpt check
							}

							$output .= apply_filters( 'vcex_testimonials_grid_excerpt', $excerpt_output, $atts );

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
						if ( 'true' == $atts['entry_media'] ) {

							if ( has_post_thumbnail( $atts['post_id'] ) ) {

								if ( $first_run ) {

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

									$media_class = implode( ' ', $media_class );

									$img_style = vcex_inline_style( array(
										'border_radius' => $img_border_radius,
									), false );

									$thumb_class = array(
										'testimonials-entry-img',
										'wpex-align-middle',
										'wpex-round',
										'wpex-border',
										'wpex-border-solid',
										'wpex-border-main',
									);

									$thumb_class = (array) apply_filters( 'wpex_testimonials_entry_thumbnail_class', $thumb_class );

								}

								$media_output .= '<div class="' . esc_attr( $media_class ) . '">';

									// Define thumbnail args
									$thumbnail_args = array(
										'attachment'    => get_post_thumbnail_id( $atts['post_id'] ),
										'size'          => $img_size,
										'width'         => $img_width,
										'height'        => $img_height,
										'style'         => $img_style,
										'crop'          => $img_crop,
										'class'         => $thumb_class,
										'apply_filters' => 'vcex_testimonials_grid_thumbnail_args',
										'filter_arg1'   => $atts,
									);

									// Add data-no-lazy to prevent conflicts with WP-Rocket
									if ( $is_isotope ) {
										$thumbnail_args['attributes'] = array( 'data-no-lazy' => 1 );
									}

									// Display post thumbnail
									$media_output .= vcex_get_post_thumbnail( $thumbnail_args );

								$media_output .= '</div>';

							}

							$bottom_output .= apply_filters( 'vcex_testimonials_grid_media', $media_output, $atts );

						}

						/*--------------------------------*/
						/* [ Meta ]
						/*--------------------------------*/

						if ( $first_run ) {

							$meta_class = array(
								'testimonial-entry-meta',
								'wpex-flex-grow',
							);

							$meta_class = (array) apply_filters( 'wpex_testimonials_entry_meta_class', $meta_class );

							$meta_class = implode( ' ', $meta_class );

						}

						$bottom_output .= '<div class="' . esc_attr( $meta_class ) . '">';

							/*--------------------------------*/
							/* [ Author ]
							/*--------------------------------*/
							$author_output = '';
							if ( 'true' == $atts['author'] ) :

								if ( $atts['post_meta_author'] ) {

									if ( $first_run ) {

										$author_class = array(
											'testimonial-entry-author',
											'entry-title',
											'wpex-m-0',
										);

										$author_class = (array) apply_filters( 'wpex_testimonials_entry_author_class', $author_class );

										$author_class = implode( ' ', $author_class );

									}

									$author_output .= '<span class="' . esc_attr( $author_class ) .'">';

										$author_output .= wp_kses_post( $atts['post_meta_author'] );

									$author_output .= '</span>';

								}

								$bottom_output .= apply_filters( 'vcex_testimonials_grid_author', $author_output, $atts );

							endif;

							/*--------------------------------*/
							/* [ Company ]
							/*--------------------------------*/
							$company_output = '';
							if ( 'true' == $atts['company'] ) {

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

								$bottom_output .= apply_filters( 'vcex_testimonials_grid_company', $company_output, $atts );

							}

							/*--------------------------------*/
							/* [ Entry Rating ]
							/*--------------------------------*/
							$rating_output = '';
							if ( 'true' == $rating ) {

								$atts['post_rating'] = vcex_get_star_rating( '', $atts['post_id'] );

								if ( ! empty( $atts['post_rating'] ) ) {

									if ( $first_run ) {

										$rating_class = array(
											'testimonial-entry-rating',
										);

										$rating_class = (array) apply_filters( 'wpex_testimonials_entry_rating_class', $rating_class );

										$rating_class = implode( ' ', $rating_class );

									}

									$rating_output .= '<div class="' . esc_attr( $rating_class ) . '">'. $atts['post_rating'] .'</div>';

								}

								$bottom_output .= apply_filters( 'vcex_testimonials_grid_rating', $rating_output, $atts );

							}

						$bottom_output .= '</div>';

					$bottom_output .= '</div>';

					$output .= apply_filters( 'vcex_testimonials_grid_bottom', $bottom_output, $atts );

				$output .= '</div>';

				if ( $entry_count === absint( $columns ) ) {
					$entry_count=0;
				}

				$first_run = false;

			endwhile; // End loop

		$output .= '</div>';

		/*--------------------------------*/
		/* [ Pagination ]
		/*--------------------------------*/
		if ( ( 'true' == $atts['pagination'] || ( 'true' == $atts['custom_query'] && ! empty( $vcex_query->query['pagination'] ) ) )
			&& 'true' != $atts['pagination_loadmore']
		) {

			$output .= vcex_pagination( $vcex_query, false );

		}

		// Load more button
		if ( 'true' == $atts['pagination_loadmore'] && ! empty( $vcex_query->max_num_pages ) ) {
			vcex_loadmore_scripts();
			$og_atts['entry_count'] = $entry_count; // Update counter
			$output .= vcex_get_loadmore_button( $shortcode_tag, $og_atts, $vcex_query );
		}

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