<?php
/**
 * vcex_recent_news shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_recent_news';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define output var
$output = '';

// Deprecated Attributes
$term_slug = isset( $atts['term_slug'] ) ? $atts['term_slug'] : '';
if ( empty( $atts['divider_color'] ) && ! empty( $atts['entry_bottom_border_color'] ) ) {
	$atts['divider_color'] = $atts['entry_bottom_border_color'];
}

// Store orginal atts value for use in non-builder params
$og_atts = $atts;

// Define entry counter
$entry_count = ! empty( $og_atts['entry_count'] ) ? $og_atts['entry_count'] : 0;

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Add paged attribute for load more button (used for WP_Query)
if ( ! empty( $og_atts['paged'] ) ) {
	$atts['paged'] = $og_atts['paged'];
}

// Define non-vc attributes
$atts['tax_query']  = '';
$atts['taxonomies'] = 'category';

// Extract shortcode atts
extract( $atts );

// IMPORTANT: Fallback required from VC update when params are defined as empty
// AKA - set things to enabled by default
$title     = ( ! $title ) ? 'true' : $title;
$date      = ( ! $date ) ? 'true' : $date;
$excerpt   = ( ! $excerpt ) ? 'true' : $excerpt;
$read_more = ( ! $read_more ) ? 'true' : $read_more;

// Fallback for term slug
if ( ! empty( $term_slug ) && empty( $include_categories ) ) {
	$atts['include_categories'] = $term_slug;
}

// Get Standard posts
if ( 'standard_post_types' == $get_posts ) {
	$atts['post_types'] = 'post';
}

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

// Output posts
if ( $vcex_query->have_posts() ) :

	// Sanitize grid columns
	$grid_columns = $grid_columns ? absint( $grid_columns ) : 1;

	// Set correct category taxonomy
	if ( ! $categories_taxonomy ) {
		$categories_taxonomy = strpos( $post_types, ',' ) === false ? vcex_get_post_type_cat_tax( $post_types ) : 'category';
	}

	// Set show_categories to false if taxonomy doesn't exist
	if ( 'true' == $show_categories && ! taxonomy_exists( $categories_taxonomy ) ) {
		$show_categories = false;
	}

	// Wrap Classes
	$wrap_classes = array(
		'vcex-module',
		'vcex-recent-news-wrap',
		'wpex-clr',
	);

	if ( $bottom_margin ) {
		$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
	}

	if ( $classes ) {
		$wrap_classes[] = vcex_get_extra_class( $classes );
	}

	if ( $visibility ) {
		$wrap_classes[] = $visibility;
	}

	if ( $css ) {
		$wrap_classes[] = vcex_vc_shortcode_custom_css_class( $css );
	}

	// Row classes
	$row_classes = array(
		'vcex-recent-news',
		'wpex-clr',
	);

	// Single column
	if ( 1 === $grid_columns ) {

		if ( vcex_validate_boolean( $divider_remove_last ) ) {
			$row_classes[] = 'wpex-last-divider-none';
		}

	}

	// Multiple columns
	else {

		$row_classes[] = 'wpex-row';

		if ( $columns_gap ) {
			$row_classes[] = 'gap-' . sanitize_html_class( $columns_gap );
		}

		$atts['columns'] = $grid_columns;

		$grid_columns_class = vcex_get_grid_column_class( $atts );

	}

	// Add WPBakery filter for classes
	$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

	/*-------------------------------------------*/
	/* [ Begin Output ]
	/*-------------------------------------------*/

	// Add wrapper (introduced in 4.8 for load more function)
	$output .= '<div class="' . esc_attr( $wrap_classes ) . '">';

	// Output module
	$output .= '<div class="' . esc_attr( implode( ' ', $row_classes ) ) . '"' . vcex_get_unique_id( $unique_id ) . '>';

		/*-------------------------------------------*/
		/* [ Header ]
		/*-------------------------------------------*/
		if ( $header ) {

			$output .= vcex_get_module_header( array(
				'style'   => $header_style,
				'content' => $header,
				'classes' => array(
					'vcex-recent-news-header',
					'vcex-module-heading',
				),
			) );

		}

		// Loop through posts
		$total_count = 0;
		$first_run = true;
		while ( $vcex_query->have_posts() ) :

			// Get post from query
			$vcex_query->the_post();

			// Add to counters
			$entry_count++;
			$total_count++;

			// Create new post object.
			$post = new stdClass();

			// Post vars
			$post->ID                  = get_the_ID();
			$post->permalink           = vcex_get_permalink( $post->ID );
			$post->the_title           = get_the_title( $post->ID );
			$post->the_title_escaped   = esc_attr( the_title_attribute( 'echo=0' ) );
			$post->type                = get_post_type( $post->ID );
			$post->video_embed_escaped = vcex_get_post_video_html();
			$post->format              = get_post_format( $post->ID );

			/*-------------------------------------------*/
			/* [ Open entry wrap ]
			/*-------------------------------------------*/

			if ( $first_run ) {

				$entry_wrap_classes = array(
					'vcex-recent-news-entry-wrap',
					'vcex-grid-item'
				);

				if ( $grid_columns > 1 ) {
					$entry_wrap_classes[] = 'col';
					$entry_wrap_classes[] = sanitize_html_class( $grid_columns_class );
				}

			}

			$entry_wrap_classes[ 'count' ] = 'col-' . sanitize_html_class( $entry_count );


			$output .= '<div class="' . esc_attr( implode( ' ', $entry_wrap_classes ) ) . '">';

			/*-------------------------------------------*/
			/* [ Open entry ]
			/*-------------------------------------------*/
			if ( $first_run ) {

				$entry_classes = array(
					'vcex-recent-news-entry',
					'wpex-flex',
				);

				if ( 'true' != $date ) {
					$entry_classes[] = 'no-left-padding';
				}

				if ( $css_animation && 'none' != $css_animation ) {
					$entry_classes[] = vcex_get_css_animation( $css_animation );
				}

			}

			$output .= '<article ' . vcex_get_post_class( $entry_classes, $post->ID ) . '>';

				/*-------------------------------------------*/
				/* [ Date ]
				/*-------------------------------------------*/
				if ( 'true' == $date ) {

					$date_output = '';

					if ( $first_run ) {

						$date_side_margin = $date_side_margin ? absint( $date_side_margin ) : '20';

						$date_class = apply_filters( 'vcex_recent_news_date_class', array(
							'vcex-recent-news-date',
							'wpex-mr-' . sanitize_html_class( $date_side_margin ),
							'wpex-flex-shrink-0',
							'wpex-text-center',
						) );

						$month_style = vcex_inline_style( array(
							'background_color' => $month_background,
							'color'            => $month_color,
						) );

						$date_style = vcex_inline_style( array(
							'min_width' => $date_min_width,
						) );

					}

					$date_output .= '<div class="' . esc_attr( implode( ' ', $date_class ) ) . '" ' . $date_style . '>';

						// Display date day
						if ( $first_run ) {

							$date_day_class = apply_filters( 'vcex_recent_news_date_day_class', array(
								'day',
								'wpex-block',
								'wpex-p-5',
								'wpex-bg-gray-100',
								'wpex-border',
								'wpex-border-b-0',
								'wpex-border-solid',
								'wpex-border-gray-300',
								'wpex-font-light',
								'wpex-text-gray-800',
							) );

							$day_format = $day_format ? wp_strip_all_tags( $day_format ) : 'd';

						}

						$date_output .= '<span class="' . esc_attr( implode( ' ', $date_day_class ) ) . '">';

							if ( 'tribe_events' == $post->type && function_exists( 'tribe_get_start_date' ) ) {
								$day = tribe_get_start_date( $post->ID, false, $day_format );
							} else {
								$day = get_the_time( $day_format, $post->ID );
							}

							// Apply filters and return date
							$date_output .= apply_filters( 'vcex_recent_news_day_output', $day );

						$date_output .= '</span>';

						// Display date month
						if ( $first_run ) {

							$date_month_class = apply_filters( 'vcex_recent_news_date_month_class', array(
								'month',
								'wpex-block',
								'wpex-p-5',
								'wpex-bg-accent',
								'wpex-text-white',
								'wpex-uppercase',
								'wpex-leading-none',
							) );

							$month_year_format = $month_year_format ? wp_strip_all_tags( $month_year_format ) : 'M y';

						}

						$date_output .= '<span class="' . esc_attr( implode( ' ', $date_month_class ) ) . '"' . $month_style . '>';

							if ( 'tribe_events' == $post->type && function_exists( 'tribe_get_start_date' ) ) {
								$month_year = tribe_get_start_date( $post->ID, false, $month_year_format );
							} else {
								$month_year = get_the_time( $month_year_format, $post->ID );
							}

							// Echo the month/year
							$date_output .= apply_filters( 'vcex_recent_news_month_year_output', $month_year );

						// Close month
						$date_output .= '</span>';

					$date_output .= '</div>';

					$output .= apply_filters( 'vcex_recent_news_date', $date_output, $atts );

				}

				/*-------------------------------------------*/
				/* [ Details ]
				/*-------------------------------------------*/
				$output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_details_class( array( 'vcex-news-entry-details wpex-flex-grow' ), $shortcode_tag, $atts ) ) ) . '">';

					/*-------------------------------------------*/
					/* [ Media ]
					/*-------------------------------------------*/
					$media_output = '';
					if ( 'true' == $featured_image ) {

						/*-------------------------------------------*/
						/* [ Video ]
						/*-------------------------------------------*/
						if ( 'true' == $featured_video && $post->video_embed_escaped ) {

							$atts['media_type'] = 'video';

							$media_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_media_class( array( 'vcex-news-entry-video' ), $shortcode_tag, $atts ) ) ) . '">' . $post->video_embed_escaped . '</div>';

						}

						/*-------------------------------------------*/
						/* [ Featured Image ]
						/*-------------------------------------------*/
						elseif ( has_post_thumbnail( $post->ID ) ) {

							$atts['media_type'] = 'thumbnail';

							$media_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_media_class( array( 'vcex-news-entry-thumbnail' ), $shortcode_tag, $atts ) ) ) . '">';

								$media_output .= '<a href="' . esc_url( $post->permalink ) . '" title="' . vcex_esc_title() . '">';

									// Thumbnail args
									$thumbnail_class = implode( ' ' , vcex_get_entry_thumbnail_class(
										null,
										$shortcode_tag,
										$atts
									) );

									// Display thumbnail
									$media_output .= vcex_get_post_thumbnail( array(
										'size'          => $img_size,
										'crop'          => $img_crop,
										'width'         => $img_width,
										'height'        => $img_height,
										'class'         => $thumbnail_class,
										'apply_filters' => 'vcex_recent_news_thumbnail_args',
										'filter_arg1'   => $atts,
									) );

									$media_output .= vcex_get_entry_media_after( $shortcode_tag );

									// Inner overlay
									$media_output .= vcex_get_entry_image_overlay( 'inside_link', $shortcode_tag, $atts );

								$media_output .= '</a>';

								// Outer link overlay HTML
								$media_output .= vcex_get_entry_image_overlay( 'outside_link', $shortcode_tag, $atts );

							$media_output .= '</div>';

						} // End thumbnail check

					} // End featured image check

					$output .= apply_filters( 'vcex_recent_news_media', $media_output, $atts );

					/*-------------------------------------------*/
					/* [ Categories ]
					/*-------------------------------------------*/
					if ( 'true' == $show_categories ) {

						$categories_output = '';
						$get_categories    = '';

						// Generate inline CSS for categories but we only need to do this 1x
						if ( $first_run ) {

							$categories_class = apply_filters( 'vcex_recent_news_categories_class', array(
								'vcex-recent-news-entry-categories',
								'entry-categories',
								'wpex-mb-5',
								'wpex-text-xs',
								'wpex-text-gray-600',
								'wpex-uppercase',
								'wpex-child-inherit-color',
								'wpex-clr',
							) );

							$categories_style = vcex_inline_style( array(
								'margin'    => $categories_margin,
								'font_size' => $categories_font_size,
								'color'     => $categories_color,
							) );

						}

						if ( 'true' == $show_first_category_only ) {

							if ( ! vcex_validate_boolean( $categories_links ) ) {

								$get_categories = vcex_get_first_term( $post->ID, $categories_taxonomy );

							} else {

								$get_categories = vcex_get_first_term_link( $post->ID, $categories_taxonomy );

							}

						} else {

							$get_categories = vcex_get_list_post_terms( $categories_taxonomy, vcex_validate_boolean( $categories_links ) );

						}

						$get_categories = apply_filters( 'vcex_post_type_grid_get_categories', $get_categories, $atts );

						if ( $get_categories ) {

							$categories_output .= '<div class="' . esc_attr( implode( ' ', $categories_class ) ) . '"' . $categories_style . '>';

								$categories_output .= $get_categories; // already sanitized

							$categories_output .= '</div>';

						}

						$output .= apply_filters( 'vcex_recent_news_categories', $categories_output, $atts );

					}

					/*-------------------------------------------*/
					/* [ Title ]
					/*-------------------------------------------*/
					if ( 'true' == $title ) {

						$title_output = '';

						if ( $first_run ) {

							$title_tag_escaped = $title_tag ? tag_escape( $title_tag ) : apply_filters( 'vcex_recent_news_default_title_tag', 'h2', $atts );

							$heading_style = vcex_inline_style( array(
								'font_size'      => $title_size,
								'font_weight'    => $title_weight,
								'text_transform' => $title_transform,
								'line_height'    => $title_line_height,
								'margin'         => $title_margin,
								'color'          => $title_color,
							) );

							$title_class = array(
								'vcex-recent-news-entry-title-heading'
							);

							if ( 1 === $grid_columns ) {
								$title_class[] = 'wpex-text-lg';
							}


						}

						$title_output .= '<header class="vcex-recent-news-entry-title">';

							$title_output .= '<' . $title_tag_escaped . ' class="' . esc_attr( implode( ' ', vcex_get_entry_title_class( $title_class, $shortcode_tag, $atts ) ) ) . '"' . $heading_style . '"' . $heading_style . '>';

								$title_output .= '<a href="' . esc_url( $post->permalink ) . '">' . wp_kses_post( $post->the_title ) . '</a>';

							$title_output .= '</' . $title_tag_escaped . '>';

						$title_output .= '</header>';

						$output .= apply_filters( 'vcex_recent_news_title', $title_output, $atts );

					} // End title check

					/*-------------------------------------------*/
					/* [ Excerpt & Read More ]
					/*-------------------------------------------*/
					if ( 'true' == $excerpt || 'true' == $read_more ) {

							/*-------------------------------------------*/
							/* [ Excerpt ]
							/*-------------------------------------------*/
							if ( 'true' == $excerpt ) {

								$excerpt_output   = '';

								if ( $first_run ) {
									$excerpt_style = vcex_inline_style( array(
										'font_size' => $excerpt_font_size,
										'color'     => $excerpt_color,
									) );
								}

								$excerpt_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_excerpt_class( array( 'vcex-recent-news-entry-excerpt' ), $shortcode_tag, $atts ) ) ) . '"' . $excerpt_style . '>';

									// Output excerpt
									$excerpt_output .= vcex_get_excerpt( array(
										'length'  => $excerpt_length,
										'context' => $shortcode_tag,
									) );

								$excerpt_output .= '</div>';

								$output .= apply_filters( 'vcex_recent_news_excerpt', $excerpt_output, $atts );

							}

							/*-------------------------------------------*/
							/* [ Read More ]
							/*-------------------------------------------*/
							if ( 'true' == $read_more ) {

								$read_more_output = '';

								if ( $first_run ) {

									// Readmore text
									$read_more_text = $read_more_text ? $read_more_text : esc_html__( 'read more', 'total' );

									// Readmore classes
									$readmore_classes = vcex_get_button_classes( $readmore_style, $readmore_style_color );

									// Read more style
									$readmore_border_color  = ( 'outline' == $readmore_style ) ? $readmore_color : '';
									$readmore_style = vcex_inline_style( array(
										'background'    => $readmore_background,
										'color'         => $readmore_color,
										'border_color'  => $readmore_border_color,
										'font_size'     => $readmore_size,
										'padding'       => $readmore_padding,
										'border_radius' => $readmore_border_radius,
										'margin'        => $readmore_margin,
									) );

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

								$attrs = array(
									'href'  => esc_url( $post->permalink ),
									'class' => esc_attr( $readmore_classes ),
									'style' => $readmore_style,
								);

								if ( $readmore_hover_data ) {
									$attrs['data-wpex-hover'] = $readmore_hover_data;
								}

								$read_more_output .= '<div class="vcex-recent-news-entry-readmore-wrap entry-readmore-wrap clr">';

									$read_more_output .= '<a' . vcex_parse_html_attributes( $attrs ) . '>';

										$read_more_output .= do_shortcode( wp_strip_all_tags( $read_more_text ) );

										if ( 'true' == $readmore_rarr ) {

											$read_more_output .= ' <span class="vcex-readmore-rarr">' . vcex_readmore_button_arrow() . '</span>';

										}

									$read_more_output .= '</a>';

								$read_more_output .= '</div>';

								$output .= apply_filters( 'vcex_recent_news_read_more', $read_more_output, $atts );

							} // End readmore text

					} // End excerpt + readmore

				$output .= '</div>';

			$output .= '</article>';


			// Add entry divider
			if ( 1 === $grid_columns ) {

				if ( $first_run ) {

					$divider_margin = $divider_margin ? absint( $divider_margin ) : 20;

					$divider_class = array(
						'wpex-divider',
						'wpex-my-' . sanitize_html_class( $divider_margin ),
					);

					if ( $divider_style && 'solid' !== $divider_style ) {
						$divider_class[] = 'wpex-divider-' . sanitize_html_class( $divider_style );
					}

					if ( $divider_width = absint( $divider_width ) ) {
						if ( 1 == $divider_width ) {
							$divider_class[] = 'wpex-border-b';
						} else {
							$divider_class[] = 'wpex-border-b-' . sanitize_html_class( $divider_width );
						}
					}

					$divider_class = implode( ' ', $divider_class );

					$divider_css_escaped = vcex_inline_style( array(
						'border_color' => $divider_color,
					) );

				}

				$output .= '<div class="' . esc_attr( $divider_class ) . '"' . $divider_css_escaped . '></div>';
			}

			$output .= '</div>'; // entry wrap close

			if ( $entry_count == $grid_columns ) {
				$entry_count=0;
			}

		$first_run = false;
		$post = null;

	endwhile;

	// End vcex-module
	$output .= '</div>';

	/*-------------------------------------------*/
	/* [ Standard Pagination ]
	/*-------------------------------------------*/
	if ( ( 'true' == $atts['pagination'] || ( 'true' == $atts['custom_query'] && ! empty( $vcex_query->query['pagination'] ) ) )
		&& 'true' != $atts['pagination_loadmore']
	) {

		$output .= vcex_pagination( $vcex_query, false );

	}

	/*-------------------------------------------*/
	/* [ Load More ]
	/*-------------------------------------------*/
	if ( 'true' == $atts['pagination_loadmore'] && ! empty( $vcex_query->max_num_pages ) ) {

		vcex_loadmore_scripts();
		$og_atts['entry_count'] = $entry_count; // Update counter
		$output .= vcex_get_loadmore_button( $shortcode_tag, $og_atts, $vcex_query );

	}

	// Close wrap
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