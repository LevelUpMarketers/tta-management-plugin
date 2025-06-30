<?php
/**
 * vcex_portfolio_grid shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2.3
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_portfolio_grid';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define output var
$output = '';

// Deprecated Attributes
if ( ! empty( $atts['term_slug'] ) && empty( $atts['include_categories']) ) {
	$atts['include_categories'] = $atts['term_slug'];
}

// Store orginal atts value for use in non-builder params
$og_atts = $atts;

// Define entry counter
$entry_count = ! empty( $og_atts['entry_count'] ) ? absint( $og_atts['entry_count'] ) : 0;

// Get shortcode attributes based on vc_lean_map => This makes sure no attributes are empty
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Add paged attribute for load more button (used for WP_Query)
if ( ! empty( $og_atts['paged'] ) ) {
	$atts['paged'] = $og_atts['paged'];
}

// Add base to attributes
$atts['base'] = $shortcode_tag;

// Define user-generated attributes
$atts['post_type'] = 'portfolio';
$atts['taxonomy']  = 'portfolio_category';
$atts['tax_query'] = '';

// Build the WordPress query
$vcex_query = vcex_build_wp_query( $atts );

// Output posts
if ( $vcex_query->have_posts() ) :

	// IMPORTANT: Fallback required from VC update when params are defined as empty
	// AKA - set things to enabled by default
	$atts['entry_media'] = empty( $atts['entry_media'] ) ? 'true' : $atts['entry_media'];
	$atts['title']       = empty( $atts['title'] ) ? 'true' : $atts['title'];
	$atts['excerpt']     = empty( $atts['excerpt'] ) ? 'true' : $atts['excerpt'];
	$atts['read_more']   = empty( $atts['read_more'] ) ? 'true' : $atts['read_more'];

	// Declare main vars and parse data
	$grid_data                  = array();
	$wrap_classes               = array( 'vcex-module', 'vcex-portfolio-grid-wrap', 'wpex-clr' );
	$grid_classes               = array( 'wpex-row', 'vcex-portfolio-grid', 'wpex-clr', 'entries' );
	$is_isotope                 = false;
	$atts['excerpt_length']     = $atts['excerpt_length'] ? $atts['excerpt_length'] : '30';
	$atts['css_animation']      = vcex_get_css_animation( $atts['css_animation'] );
	$atts['css_animation']      = ( 'true' == $atts['filter'] ) ? false : $atts['css_animation'];
	$atts['equal_heights_grid'] = ( 'true' == $atts['equal_heights_grid'] && $atts['columns'] > '1' ) ? 'true' : 'false';

	// Get title tag
	$title_tag_escaped = $atts['title_tag'] ? tag_escape( $atts['title_tag'] ) : apply_filters( 'vcex_grid_default_title_tag', 'h2', $atts );

	// Enable Isotope
	if ( 'true' == $atts['filter']
		|| 'masonry' == $atts['grid_style']
		|| 'no_margins' == $atts['grid_style']
	) {
		$is_isotope = true;
		vcex_enqueue_isotope_scripts();
	}

	// Get filter taxonomy
	if ( 'true' == $atts['filter'] ) {
		$filter_taxonomy = apply_filters( 'vcex_filter_taxonomy', $atts['taxonomy'], $atts );
		$filter_taxonomy = taxonomy_exists( $filter_taxonomy ) ? $filter_taxonomy : '';
		if ( $filter_taxonomy ) {
			$atts['filter_taxonomy'] = $filter_taxonomy; // Add to array to pass on to vcex_grid_filter_args()
		}
	} else {
		$filter_taxonomy = null;
	}

	// Get filter terms
	if ( $filter_taxonomy ) {

		// Get filter terms
		$filter_terms = get_terms( $filter_taxonomy, vcex_grid_filter_args( $atts, $vcex_query ) );

		// Make sure we have terms before doing things
		if ( $filter_terms ) {

			// Translate filter_active_category
			if ( class_exists( 'SitePress' ) && ! empty( $atts['filter_active_category'] ) ) {
				global $sitepress;
				$atts['filter_active_category'] = apply_filters(
					'wpml_object_id',
					$atts['filter_active_category'],
					$filter_taxonomy,
					true
				);
			}

			// Check url for filter cat
			if ( $active_cat_query_arg = vcex_grid_filter_get_active_item( $filter_taxonomy ) ) {
				$atts['filter_active_category'] = $active_cat_query_arg;
			}

			// Check if filter active cat exists on current page
			$filter_has_active_cat = in_array( $atts['filter_active_category'], wp_list_pluck( $filter_terms, 'term_id' ) ) ? true : false;

			// Add show on load animation when active filter is enabled to prevent double animation
			if ( $filter_has_active_cat ) {
				$grid_classes[] = 'wpex-show-on-load';
			}

		} else {
			$filter = false; // no terms
		}

	}

	// Wrap classes
	if ( $atts['bottom_margin'] ) {
		$wrap_classes[] = vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
	}

	if ( $atts['visibility'] ) {
		$wrap_classes[] = $atts['visibility'];
	}

	if ( $atts['classes'] ) {
		$wrap_classes[] = vcex_get_extra_class( $atts['classes'] );
	}

	// Main grid classes
	if ( $atts['columns_gap'] ) {
		$grid_classes[] = 'gap-'. $atts['columns_gap'];
	}
	if ( 'true' == $atts['equal_heights_grid'] ) {
		$grid_classes[] = 'match-height-grid';
	}
	if ( $is_isotope ) {
		$grid_classes[] = 'vcex-isotope-grid';
	}
	if ( 'no_margins' == $atts['grid_style'] ) {
		$grid_classes[] = 'vcex-no-margin-grid';
	}
	if ( 'left_thumbs' == $atts['single_column_style'] ) {
		$grid_classes[] = 'left-thumbs';
	}
	if ( 'lightbox' == $atts['thumb_link'] || 'lightbox_gallery' == $atts['thumb_link'] ) {
		if ( 'true' == $atts['thumb_lightbox_gallery'] ) {
			$grid_classes[] = 'wpex-lightbox-group';
			$lightbox_single_class = ' wpex-lightbox-group-item';
		} else {
			$lightbox_single_class = ' wpex-lightbox';
		}
		if ( 'true' != $atts['thumb_lightbox_title'] ) {
			$grid_data[] = 'data-show_title="false"';
		}
	}

	// Grid data attributes
	if ( 'true' == $atts['filter'] ) {
		if ( 'fitRows' == $atts['masonry_layout_mode'] ) {
			$grid_data[] = 'data-layout-mode="fitRows"';
		}
		if ( $atts['filter_speed'] ) {
			$grid_data[] = 'data-transition-duration="'. esc_attr( $atts['filter_speed'] ) .'"';
		}
		if ( ! empty( $filter_has_active_cat ) ) {
			$grid_data[] = 'data-filter=".cat-' . esc_attr( $atts['filter_active_category'] ) . '"';
		}
	} else {
		$isotope_transition_duration = apply_filters( 'vcex_isotope_transition_duration', null, $shortcode_tag );
		if ( $isotope_transition_duration ) {
			$grid_data[] = 'data-transition-duration="' . esc_attr( $isotope_transition ) . '"';
		}
	}

	// Content Design
	$content_style = array(
		'color'   => $atts['content_color'],
		'opacity' => $atts['content_opacity'],
	);

	if ( empty( $atts['content_css'] ) ) {

		if ( isset( $atts['content_background'] ) ) {
			$content_style['background'] = $atts['content_background'];
		}

		if ( isset( $atts['content_padding'] ) ) {
			$content_style['padding'] = $atts['content_padding'];
		}

		if ( isset( $atts['content_margin'] ) ) {
			$content_style['margin'] = $atts['content_margin'];
		}

		if ( isset( $atts['content_border'] ) ) {
			$content_style['border'] = $atts['content_border'];
		}
	}

	$content_style = vcex_inline_style( $content_style );

	// Heading style
	if ( 'true' == $atts['title'] ) {

		// Heading Design
		$heading_style = vcex_inline_style( array(
			'margin'         => $atts['content_heading_margin'],
			'font_size'      => $atts['content_heading_size'],
			'color'          => $atts['content_heading_color'],
			'font_weight'    => $atts['content_heading_weight'],
			'text_transform' => $atts['content_heading_transform'],
			'line_height'    => $atts['content_heading_line_height'],
		) );

	}

	// Categories style
	if ( 'true' == $atts['show_categories'] ) {
		$categories_style = vcex_inline_style( array(
			'margin'    => $atts['categories_margin'],
			'font_size' => $atts['categories_font_size'],
			'color'     => $atts['categories_color'],
		) );
	}

	// Readmore design
	if ( 'true' == $atts['read_more'] ) {

		// Read more text
		$read_more_text = $atts['read_more_text'] ? $atts['read_more_text'] : esc_html__( 'read more', 'total' );

		// Readmore classes
		$readmore_classes = vcex_get_button_classes( $atts['readmore_style'], $atts['readmore_style_color'] );

		// Readmore style
		$readmore_inline_style = vcex_inline_style( array(
			'background'    => $atts['readmore_background'],
			'color'         => $atts['readmore_color'],
			'font_size'     => $atts['readmore_size'],
			'padding'       => $atts['readmore_padding'],
			'border_radius' => $atts['readmore_border_radius'],
			'margin'        => $atts['readmore_margin'],
		), false );

		// Readmore data
		$readmore_hover_data = array();
		if ( $atts['readmore_hover_background'] ) {
			$readmore_hover_data['background'] = $atts['readmore_hover_background'];
		}
		if ( $atts['readmore_hover_color'] ) {
			$readmore_hover_data['color'] = $atts['readmore_hover_color'];
		}
		if ( $readmore_hover_data ) {
			$readmore_hover_data = htmlspecialchars( wp_json_encode( $readmore_hover_data ) );
		}

	}

	// Apply filters before implode
	$wrap_classes = apply_filters( 'vcex_portfolio_grid_wrap_classes', $wrap_classes ); // @todo remove deprecated
	$grid_classes = apply_filters( 'vcex_portfolio_grid_classes', $grid_classes );
	$grid_data    = apply_filters( 'vcex_portfolio_grid_data_attr', $grid_data );

	// Convert arrays into strings
	$wrap_classes = implode( ' ', $wrap_classes );
	$grid_classes = implode( ' ', $grid_classes );
	$grid_data    = $grid_data ? ' ' . implode( ' ', $grid_data ) : '';

	// VC filters
	$wrap_classes = vcex_parse_shortcode_classes( $wrap_classes, $shortcode_tag, $atts );

	/*--------------------------------*/
	/* [ Begin Output ]
	/*--------------------------------*/
	$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $atts['unique_id'] ) . '>';

		/*--------------------------------*/
		/* [ Header ]
		/*--------------------------------*/
		if ( ! empty( $atts['header'] ) ) {

			$output .= vcex_get_module_header( array(
				'style'   => ! empty( $atts['header_style'] ) ? $atts['header_style'] : '',
				'content' => $atts['header'],
				'classes' => array( 'vcex-module-heading vcex_portfolio_grid-heading' ),
			) );

		}

		/*--------------------------------*/
		/* [ Filter Links ]
		/*--------------------------------*/
		if ( 'true' == $atts['filter'] && ! empty( $filter_terms ) ) :

			// Sanitize all text
			$all_text = $atts['all_text'] ? $atts['all_text'] : esc_html__( 'All', 'total' );

			// Filter button classes
			$filter_button_classes = vcex_get_button_classes( $atts['filter_button_style'], $atts['filter_button_color'] );

			// Filter font size
			$filter_style = vcex_inline_style( array(
				'font_size' => $atts['filter_font_size'],
			) );

			$filter_classes = 'vcex-portfolio-filter vcex-filter-links wpex-clr';
			if ( 'yes' == $atts['center_filter'] ) {
				$filter_classes .= ' center';
			}

			$output .= '<ul class="' . esc_attr( $filter_classes ) . '"' . $filter_style . '>';

				if ( 'true' == $atts['filter_all_link'] ) {

					$output .= '<li';

						if ( empty( $filter_has_active_cat ) ) {
							$output .= ' class="active"';
						}

					$output .= '>';

						$output .= '<a href="#" data-filter="*" class="' . $filter_button_classes . '"><span>' . esc_html( $all_text ) . '</span></a>';

					$output .= '</li>';

				}

				foreach ( $filter_terms as $term ) :

					// Open Filter link
					$output .= '<li class="filter-cat-'. $term->term_id;

						if ( $atts['filter_active_category'] == $term->term_id ) {
							$output .= ' active';
						}

					$output .= '">';

						// Add main filter cat link
						$output .= '<a href="#" data-filter=".cat-' . absint( $term->term_id ) . '" class="' . esc_attr( $filter_button_classes ) . '">';

							$output .= esc_html( $term->name );

						$output .= '</a>';

					$output .= '</li>';

				endforeach;

				if ( $vcex_after_grid_filter = apply_filters( 'vcex_after_grid_filter', '', $atts, $filter_terms ) ) {

					$output .= wp_kses_post( $vcex_after_grid_filter );

				}

			$output .= '</ul>';

		endif; // End filter

		/*--------------------------------*/
		/* [ Grid ]
		/*--------------------------------*/
		$output .= '<div class="' . esc_attr( $grid_classes ) . '"' . $grid_data . '>';

			// Start loop
			while ( $vcex_query->have_posts() ) :

				// Get post from query
				$vcex_query->the_post();

				// Add to the counter var
				$entry_count++;

				// Post Data
				$atts['post_id']            = get_the_ID();
				$atts['post_permalink']     = vcex_get_permalink( $atts['post_id'] );
				$atts['post_title']         = get_the_title();
				$atts['post_esc_title']     = esc_attr( $atts['post_title'] );
				$atts['post_video']         = ( 'true' == $atts['featured_video'] ) ? vcex_get_post_video_html() : '';
				$atts['post_excerpt']       = '';
				$atts['has_post_thumbnail'] = has_post_thumbnail( $atts['post_id'] );

				// Post Excerpt
				if ( 'true' == $atts['excerpt'] || 'true' == $atts['thumb_lightbox_caption'] ) {

					$atts['post_excerpt'] = vcex_get_excerpt( array(
						'length'  => $atts['excerpt_length'],
						'context' => $shortcode_tag,
					) );

				}

				// Readmore link - allow it to be filterable
				if ( 'true' == $atts['read_more'] ) {
					$atts['readmore_link'] = $atts['post_permalink'];
				}

				// Categories tax
				if ( 'true' == $atts['show_categories'] ) {
					$atts['show_categories_tax'] = 'portfolio_category';
				}

				// Apply filters to attributes
				$latts = apply_filters( 'vcex_shortcode_loop_atts', $atts, $shortcode_tag );

				// Does entry have details?
				if ( 'true' == $latts['title']
					|| 'true' == $latts['show_categories']
					|| ( 'true' == $latts['excerpt'] && $latts['post_excerpt'] )
					|| 'true' == $latts['read_more']
				) {
					$entry_has_details = true;
				} else {
					$entry_has_details = false;
				}

				// Add classes to the entries
				$entry_classes = array(
					'portfolio-entry',
					'vcex-grid-item'
				);

				if ( $entry_has_details ) {
					$entry_classes[] = 'entry-has-details';
				}

				$entry_classes[] = vcex_get_grid_column_class( $atts );

				if ( 'false' == $atts['columns_responsive'] ) {
					$entry_classes[] = 'nr-col';
				} else {
					$entry_classes[] = 'col';
				}

				if ( $entry_count ) {
					$entry_classes[] = 'col-' . sanitize_html_class( $entry_count );
				}

				if ( $atts['css_animation'] ) {
					$entry_classes[] = $atts['css_animation'];
				}

				if ( $is_isotope ) {
					$entry_classes[] = 'vcex-isotope-entry';
				}

				if ( 'no_margins' == $atts['grid_style'] ) {
					$entry_classes[] = 'vcex-no-margin-entry';
				}

				if ( $latts['content_alignment'] ) {
					$entry_classes[] = 'text' . sanitize_html_class( $latts['content_alignment'] );
				}

				// Get and save lightbox data for use with media and title
				if ( ( $latts['has_post_thumbnail'] && ( 'lightbox' == $latts['thumb_link'] || 'lightbox_gallery' == $latts['thumb_link'] ) )
					|| 'lightbox' == $latts['title_link']
				) {

					// Enqueue lightbox scripts
					vcex_enqueue_lightbox_scripts();

					// Define vars
					$latts['lightbox_data'] = array();
					$lightbox_gallery_imgs  = null;

					// Save correct lightbox class
					$latts['lightbox_class'] = ! empty( $lightbox_single_class ) ? $lightbox_single_class : 'wpex-lightbox';

					// Gallery
					if ( 'lightbox_gallery' == $latts['thumb_link'] ) {
						if ( $lightbox_gallery_imgs = vcex_get_post_gallery_ids( $latts['post_id'] ) ) {
							$latts['lightbox_class']  = ' wpex-lightbox-gallery';
							$latts['lightbox_data'][] = 'data-gallery="' . vcex_parse_inline_lightbox_gallery( $lightbox_gallery_imgs ) . '"';
						}
					}

					// Generate lightbox image
					$lightbox_image = vcex_get_lightbox_image();

					// Get lightbox link
					$latts['lightbox_link'] = $lightbox_image;

					// Add lightbox data attributes
					if ( 'true' == $atts['thumb_lightbox_title'] ) {
						$latts['lightbox_data'][] = 'data-title="' . vcex_esc_title() . '"';
					}
					if ( 'true' == $atts['thumb_lightbox_caption'] && $latts['post_excerpt'] ) {
						$latts['lightbox_data'][] = 'data-caption="' . str_replace( '"',"'", $latts['post_excerpt'] ) . '"';
					}

					// Check for video
					if ( ! $lightbox_gallery_imgs
						&& $oembed_video_url = vcex_get_post_video_oembed_url( $atts['post_id'] )
					) {
						$embed_url = vcex_get_video_embed_url( $oembed_video_url );
						if ( $embed_url ) {
							$latts['lightbox_link']               = $embed_url;
							$latts['lightbox_data']['data-thumb'] = 'data-thumb="' . $lightbox_image . '"';
						}
					}

					$lightbox_data = ! empty( $latts['lightbox_data']  ) ? ' ' . implode( ' ', $latts['lightbox_data'] ) : '';

				}

				// Begin entry output
				$output .= '<div '. vcex_grid_get_post_class( $entry_classes, $atts['post_id'] ) .'>';

					$output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_inner_class( array( 'portfolio-entry-inner' ), $shortcode_tag, $latts ) ) ) . '">';

						// Entry Media
						$media_output = '';
						if ( 'true' == $latts['entry_media'] ) {

							/*--------------------------------*/
							/* [ Video ]
							/*--------------------------------*/
							if ( $latts['post_video'] ) {

								$latts[ 'media_type' ] = 'video';

								$media_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_media_class( array( 'portfolio-entry-media', 'portfolio-featured-video' ), $shortcode_tag, $latts ) ) ) . '">';

									$media_output .= $latts['post_video'];

								$media_output .= '</div>';

							}

							/*--------------------------------*/
							/* [ Featured Image ]
							/*--------------------------------*/
							elseif ( $latts['has_post_thumbnail'] ) {

								$latts[ 'media_type' ] = 'thumbnail';

								$media_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_media_class( array( 'portfolio-entry-media' ), $shortcode_tag, $latts ) ) ) . '">';

									// Open link tag if thumblink does not equal nowhere
									if ( 'nowhere' != $latts['thumb_link'] ) {

										// Lightbox (only add if overlay with lightbox isn't enabled to prevent duplicate lightbox items)
										if ( ! in_array( $latts['overlay_style'], array( 'view-lightbox-buttons-buttons', 'view-lightbox-buttons-text' ) )
											&& ( 'lightbox' == $latts['thumb_link'] || 'lightbox_gallery' == $latts['thumb_link'] )
										) {

											$media_output .= '<a href="' . $latts["lightbox_link"] . '" title="' . $latts['post_esc_title'] . '" class="portfolio-entry-media-link' . $latts['lightbox_class'] . '"' . $lightbox_data . '>';

										// Standard post link
										} else {

											$media_output .= '<a href="' . esc_url( $latts['post_permalink'] ) . '" title="' . $latts['post_esc_title'] . '" class="portfolio-entry-media-link"' . vcex_html( 'target_attr', $latts['link_target'] ) . '>';

										}

									} // End Opening link

									$thumbnail_class = implode( ' ' , vcex_get_entry_thumbnail_class(
										array( 'portfolio-entry-img' ),
										$shortcode_tag,
										$latts
									) );

									// Define thumbnail args
									$thumbnail_args = array(
										'width'         => $latts['img_width'],
										'height'        => $latts['img_height'],
										'crop'          => $latts['img_crop'],
										'size'          => $latts['img_size'],
										'class'         => $thumbnail_class,
										'apply_filters' => 'vcex_grid_thumbnail_args', // @todo rename filter to vcex_portfolio_grid_thumbnail_args
										'filter_arg1'   => $latts,
									);

									// Add data-no-lazy to prevent conflicts with WP-Rocket
									if ( $is_isotope ) {
										$thumbnail_args['attributes'] = array( 'data-no-lazy' => 1 );
									}

									// Display post thumbnail
									$media_output .= vcex_get_post_thumbnail( $thumbnail_args );

									// Inner link overlay HTML
									$media_output .= vcex_get_entry_image_overlay( 'inside_link', $shortcode_tag, $atts );

									// Entry media after
									$media_output .= vcex_get_entry_media_after( $shortcode_tag );

									// Close link tag
									if ( 'nowhere' != $latts['thumb_link'] ) {
										$media_output .= '</a>';
									}

									// Outer link overlay HTML
									$media_output .= vcex_get_entry_image_overlay( 'outside_link', $shortcode_tag, $atts );

								$media_output .= '</div>';

							} // End has_post_thumbnail check

							$output .= apply_filters( 'vcex_portfolio_grid_media', $media_output, $atts );


						} // End media

						/*--------------------------------*/
						/* [ Content ]
						/*--------------------------------*/
						if ( $entry_has_details ) :

							// Entry details start
							$output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_details_class( array( 'portfolio-entry-details' ), $shortcode_tag, $atts ) ) ) . '"' . $content_style . '>';

								// Equal height div
								if ( 'true' == $atts['equal_heights_grid'] ) {
									$output .= '<div class="match-height-content">';
								}

								/*--------------------------------*/
								/* [ Title ]
								/*--------------------------------*/
								$title_output = '';
								if ( 'true' == $latts['title'] ) {

									$title_output .= '<'. $title_tag_escaped .' class="' . esc_attr( implode( ' ', vcex_get_entry_title_class( array( 'portfolio-entry-title' ), $shortcode_tag, $atts ) ) ) . '"'. $heading_style .'>';

										// Display title without link
										if ( 'nowhere' == $latts['title_link'] ) {

											$title_output .= wp_kses_post( $latts['post_title'] );

										// Link title to lightbox
										} elseif ( 'lightbox' == $latts['title_link'] ) {

											if ( $latts["lightbox_link"] ) {

												$title_output .= '<a href="' . esc_url( $latts["lightbox_link"] ) . '" title="' . $latts['post_esc_title'] . '" class="wpex-lightbox"' . $lightbox_data . '>';

													$title_output .= wp_kses_post( $latts['post_title'] );

												$title_output .= '</a>';

											} else {

												$title_output .= wp_kses_post( $latts['post_title'] );

											}

										// Link title to post
										} else {

											$title_output .= '<a href="' . esc_url( $latts['post_permalink'] ) . '" title="' . $latts['post_esc_title'] . '"' . vcex_html( 'target_attr', $latts['link_target'] ) . '>';

												$title_output .= wp_kses_post( $latts['post_title'] );

											$title_output .= '</a>';

										}

									$title_output .= '</' . $title_tag_escaped . '>';

									$output .= apply_filters( 'vcex_portfolio_grid_title', $title_output, $atts );

								}

								/*--------------------------------*/
								/* [ Categories ]
								/*--------------------------------*/
								if ( 'true' == $latts['show_categories'] ) {

									$categories_output = '';

									$get_categories = '';

									// Get first category
									if ( 'true' == $latts['show_first_category_only'] ) {

										if ( ! vcex_validate_boolean( $latts[ 'categories_links' ] ) ) {

											$get_categories .= vcex_get_first_term(
												$latts['post_id'],
												$latts['show_categories_tax']
											);

										} else {

											$get_categories .= vcex_get_first_term_link(
												$latts['post_id'],
												$latts['show_categories_tax']
											);

										}

									}

									// Get all categories
									else {

										$get_categories .= vcex_get_list_post_terms( $latts['show_categories_tax'], vcex_validate_boolean( $latts[ 'categories_links' ] ) );

									}

									if ( $get_categories ) {

										$categories_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_categories_class( array( 'portfolio-entry-categories' ), $shortcode_tag, $atts ) ) ) . '"' . $categories_style . '>';

											$categories_output .= $get_categories;

										$categories_output .= '</div>';

									}

									$output .= apply_filters( 'vcex_portfolio_grid_categories', $categories_output, $atts );

								} // End categories

								/*--------------------------------*/
								/* [ Excerpt ]
								/*--------------------------------*/
								if ( 'true' == $latts['excerpt'] && $latts['post_excerpt'] ) {

									if ( empty( $excerpt_style ) ) {
										$excerpt_style = vcex_inline_style( array(
											'font_size' => $atts['content_font_size'],
										) );
									}

									$excerpt_output = '';

									$excerpt_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_excerpt_class( array( 'portfolio-entry-excerpt' ), $shortcode_tag, $atts ) ) ) . '"' . $excerpt_style . '>';

										$excerpt_output .= $latts['post_excerpt']; // Already sanitized

									$excerpt_output .= '</div>';

									$output .= apply_filters( 'vcex_portfolio_grid_excerpt', $excerpt_output, $atts );

								} // End excerpt

								/*--------------------------------*/
								/* [ Read More ]
								/*--------------------------------*/
								if ( 'true' == $latts['read_more'] ) {

									$readmore_output = '';

									$readmore_output .= '<div class="' . esc_attr( implode( ' ', vcex_get_entry_button_wrap_class( array( 'portfolio-entry-readmore-wrap' ), $shortcode_tag, $atts ) ) ) . '">';

										$attrs = array(
											'href'   => esc_url( $atts['readmore_link'] ),
											'class'  => $readmore_classes,
											'style'  => $readmore_inline_style,
											'target' => $latts['link_target'],
										);

										if ( $readmore_hover_data ) {
											$attrs['data-wpex-hover'] = $readmore_hover_data;
										}

										$readmore_output .= '<a' . vcex_parse_html_attributes( $attrs ) . '>';

											$readmore_output .= $read_more_text;

											if ( 'true' == $latts['readmore_rarr'] ) {
												$readmore_output .= ' <span class="vcex-readmore-rarr">' . vcex_readmore_button_arrow() . '</span>';
											}

										$readmore_output .= '</a>';

									$readmore_output .= '</div>';

									$output .= apply_filters( 'vcex_portfolio_grid_readmore', $readmore_output, $atts );

								}

								// Close Equal height container
								if ( 'true' == $atts['equal_heights_grid'] ) {
									$output .= '</div>';
								}

							$output .= '</div>';

						endif; // End details check

					$output .= '</div>'; // Close entry inner

				$output .= '</div>'; // Close entry

				// Reset entry counter
				if ( $entry_count === absint( $atts['columns'] ) ) {
					$entry_count = 0;
				}

			endwhile; // End post loop

		$output .= '</div>';

		/*--------------------------------*/
		/* [ Pagination ]
		/*--------------------------------*/
		if ( ( 'true' == $atts['pagination'] || ( 'true' == $atts['custom_query'] && ! empty( $vcex_query->query['pagination'] ) ) )
			&& 'true' != $atts['pagination_loadmore']
		) {

			$output .= vcex_pagination( $vcex_query, false );

		}

		/*--------------------------------*/
		/* [ Pagination ]
		/*--------------------------------*/
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