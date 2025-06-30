<?php
/**
 * vcex_terms_grid shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_terms_grid';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Migrate old font_container params
$atts = vcex_migrate_font_container_param( 'title_typo', 'title', $atts );
$atts = vcex_migrate_font_container_param( 'description_typo', 'description', $atts );

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Taxonomy is required
if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
	return;
}

// Define output var
$output = '';

// Query Terms
$query_args = array(
	'order'      => $order,
	'orderby'    => $orderby,
	'hide_empty' => vcex_validate_boolean( $hide_empty ),
);

if ( vcex_validate_boolean ( $parent_terms ) ) {
	$query_args['parent'] = 0;
}

if ( $child_of ) {
	$child_of = get_term_by( 'slug', $child_of, $taxonomy );
	if ( $child_of && ! is_wp_error( $child_of ) ) {
		$query_args[ 'child_of' ] = $child_of->term_id;
	}
}

// Get terms for current post only
if ( vcex_validate_boolean( $get_post_terms ) ) {
	$query_args['object_ids'] = vcex_get_the_ID();
}

// Get terms
$query_args = apply_filters( 'vcex_terms_grid_query_args', $query_args, $atts );
$terms = get_terms( $taxonomy, $query_args );

// Terms needed
if ( ! $terms || is_wp_error( $terms ) ) {
	return;
}

// Sanitize atts
$exclude_terms = $exclude_terms ? preg_split( '/\,[\s]*/', $exclude_terms ) : array();
$title_tag_escaped = $title_tag ? tag_escape( $title_tag ) : 'h2';
$title_overlay_align_items = $title_overlay_align_items ? $title_overlay_align_items : 'center';
$title_overlay_opacity = $title_overlay_opacity ? $title_overlay_opacity : '60';
$title_overlay_style = '';

if ( $title_overlay_bg ) {
	$title_overlay_style = vcex_inline_style( array(
		'background_color' => $title_overlay_bg,
	) );
}

// Validate on/off settings
$title_overlay    = vcex_validate_boolean( $title_overlay );
$img              = vcex_validate_boolean( $img );
$title            = vcex_validate_boolean( $title );
$description      = vcex_validate_boolean( $description );
$term_count       = vcex_validate_boolean( $term_count );
$term_count_block = vcex_validate_boolean( $term_count_block );
$button           = vcex_validate_boolean( $button );

// Load Google Fonts if needed
if ( $title_font_family ) {
	vcex_enqueue_font( $title_font_family );
}

if ( $description_font_family ) {
	vcex_enqueue_font( $description_font_family );
}

// Define post type based on the taxonomy
$taxonomy  = get_taxonomy( $taxonomy );
$post_type = $taxonomy->object_type[0];

// Wrap classes
$wrap_classes = array(
	'vcex-module',
	'vcex-terms-grid',
	'wpex-row',
	'wpex-clr'
);

if ( 'masonry' === $grid_style ) {
	$wrap_classes[] = 'vcex-isotope-grid';
	vcex_enqueue_isotope_scripts();
}

if ( $columns_gap ) {
	$wrap_classes[] = 'gap-' . sanitize_html_class( $columns_gap );
}

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Entry CSS wrapper
$entry_css_class = $entry_css ? vcex_vc_shortcode_custom_css_class( $entry_css ) : '';

// Title style
$title_style = array(
	'font_family'   => $title_font_family,
	'font_size'     => $title_font_size,
	'color'         => $title_color,
	'font_weight'   => $title_font_weight,
	'line_height'   => $title_line_height,
	'text_align'    => $title_text_align,
	'margin_bottom' => $title_bottom_margin,
);
$title_style = vcex_inline_style( $title_style );

// Description style
$description_style = array(
	'font_family' => $description_font_family,
	'font_size'   => $description_font_size,
	'color'       => $description_color,
	'line_height' => $description_line_height,
	'text_align'  => $description_text_align,
);
$description_style = vcex_inline_style( $description_style );

// Display header if enabled
if ( $header ) {

	$output .= vcex_get_module_header( array(
		'style'   => $header_style,
		'content' => $header,
		'classes' => array( 'vcex-module-heading', 'vcex_terms_grid-heading' ),
	) );

}

// Begin output
$output .= '<div class="' . esc_attr( $wrap_classes ) . '">';

	// Start counter
	$counter = 0;

	// Loop through terms
	$first_run = true;
	foreach( $terms as $term ) :

		$term_link = get_term_link( $term, $taxonomy );

		// Excluded
		if ( in_array( $term->slug, $exclude_terms ) ) {
			continue;
		}

		// Add to counter
		$counter++;

		if ( $first_run ) {

			// Entry classes
			$entry_classes = array(
				'vcex-terms-grid-entry',
				'wpex-last-mb-0',
				'wpex-clr',
			);

			if ( 'masonry' == $grid_style ) {
				$entry_classes[] = 'vcex-isotope-entry';
			}

			$entry_classes[] = vcex_get_grid_column_class( $atts );;

			if ( 'false' == $columns_responsive ) {
				$entry_classes[] = 'nr-col';
			} else {
				$entry_classes[] = 'col';
			}

			if ( $css_animation && 'none' != $css_animation ) {
				$entry_classes[] = vcex_get_css_animation( $css_animation );
			}

			$entry_classes = implode( ' ', $entry_classes );

		}

		$output .= '<div class="' . esc_attr( $entry_classes ) . ' term-' . sanitize_html_class( $term->term_id ) . ' term-' . sanitize_html_class( $term->slug ) . ' col-' . sanitize_html_class( $counter ) . '">';

			if ( $entry_css_class ) {
				$output .= '<div class="' . esc_attr( $entry_css_class ) . '">';
			}

				// Display image if enabled
				if ( $img ) {

					// Get term thumbnail
					$img_id = vcex_get_term_thumbnail_id( $term->term_id );

					// Get woo product image
					if ( 'product' == $post_type ) {
						$img_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
					}

					// Image not defined via meta, display image from first post in term
					if ( ! $img_id ) {

						// Query first post in term
						$vcex_query = new WP_Query( array(
							'post_type'      => $post_type,
							'posts_per_page' => '1',
							'no_found_rows'  => true,
							'tax_query'      => array(
								array(
									'taxonomy' => $term->taxonomy,
									'field'    => 'id',
									'terms'    => $term->term_id,
								)
							),
						) );

						// Get featured image of first post
						if ( $vcex_query->have_posts() ) {

							while ( $vcex_query->have_posts() ) : $vcex_query->the_post();

								$img_id = get_post_thumbnail_id();

							endwhile;

						}

						// Reset query
						wp_reset_postdata();

					}

					if ( $img_id ) {

						if ( $first_run ) {

							$media_classes = array(
								'vcex-terms-grid-entry-image',
								'wpex-clr'
							);

							if ( $title_overlay ) {
								$media_classes[] = 'vcex-has-overlay';
								$media_classes[] = 'overlay-parent';
							} else {
								$media_classes[] = 'wpex-mb-20';
							}

							if ( $img_filter ) {
								$media_classes[] = vcex_image_filter_class( $img_filter );
							}

							if ( $img_hover_style ) {
								$media_classes[] = vcex_image_hover_classes( $img_hover_style );
							}

							$overlay_style = $overlay_style;

							if ( $overlay_style ) {
								$media_classes[] = vcex_image_overlay_classes( $overlay_style );
							}

							$media_classes = implode( ' ', array_unique( $media_classes ) );

						}

						$output .= '<div class="' . esc_attr( $media_classes ) . '">';

							$output .= '<a href="' . esc_url( $term_link ) . '" title="' . esc_attr( $term->name ) . '">';

								if ( $first_run ) {

									$thumbnail_class = 'wpex-align-middle';

									if ( $title_overlay ) {
										$thumbnail_class .= ' wpex-w-100';
									}

								}

								// Display post thumbnail
								$output .= vcex_get_post_thumbnail( array(
									'attachment' => $img_id,
									'alt'        => $term->name,
									'width'      => $img_width,
									'height'     => $img_height,
									'crop'       => $img_crop,
									'size'       => $img_size,
									'class'      => $thumbnail_class,
								) );

								// Overlay title
								if ( $title_overlay && $title && ! empty( $term->name ) ) :

									$output .= '<div class="vcex-terms-grid-entry-overlay wpex-absolute wpex-inset-0 wpex-text-white">';

										$output .= '<span class="vcex-terms-grid-entry-overlay-bg wpex-block wpex-absolute wpex-inset-0 wpex-bg-black wpex-opacity-' . sanitize_html_class( $title_overlay_opacity ) . '"' . $title_overlay_style . '></span>';

										$output .= '<div class="vcex-terms-grid-entry-overlay-content wpex-relative wpex-flex wpex-items-' . sanitize_html_class( $title_overlay_align_items ) . ' wpex-p-20 wpex-h-100 wpex-w-100">';

												$title_classes = array(
													'vcex-terms-grid-entry-title',
													'entry-title',
													'wpex-flex-grow',
													'wpex-text-xl',
													'wpex-text-center',
												);

												if ( empty( $title_color ) ) {
													$title_classes[] = 'wpex-inherit-color-important';
												}

												$output .= '<' . $title_tag_escaped . ' class="' . esc_attr( implode( ' ', $title_classes ) ) . '"' . $title_style . '>';

													$output .= esc_html( $term->name );

													if ( $term_count ) {

														$term_count_class = 'vcex-terms-grid-entry-count';

														if ( $term_count_block ) {
															$term_count_class .= ' wpex-block';
														} else {
															$term_count_class .= ' wpex-ml-5';
														}

														$output .= '<span class="' . esc_attr( $term_count_class ) . '">(' . absint( $term->count ) . ')</span>';
													}

												$output .= '</' . $title_tag_escaped . '>';

										$output .= '</div>';

									$output .= '</div>';

								endif;

								// Data for overlays
								if ( $img_id ) {
									$atts[ 'lightbox_link' ] = wpex_get_lightbox_image( $img_id );
								}
								$atts['overlay_link']     = get_term_link( $term, $taxonomy );
								$atts['post_title']       = $term->name;
								$atts['overlay_excerpt' ] = $term->description;

								// Inner Overlay
								$output .= vcex_get_entry_image_overlay( 'inside_link', $shortcode_tag, $atts );

							$output .= '</a>';

							// Outside Overlay
							$output .= vcex_get_entry_image_overlay( 'outside_link', $shortcode_tag, $atts );

						$output .= '</div>';

					} // End img ID check

				} // End image check

				// Inline title and description
				if ( ! $title_overlay || ! $img ) {

					// Show title
					if ( $title && ! $title_overlay && ! empty( $term->name ) ) {

						$output .= '<' . $title_tag_escaped . ' class="vcex-terms-grid-entry-title entry-title wpex-mb-5"' . $title_style . '>';

							$output .= '<a href="' . get_term_link( $term, $taxonomy ) . '">';

								$output .= esc_html( $term->name );

								if ( 'true' == $atts[ 'term_count' ] ) {
									$output .= ' <span class="vcex-terms-grid-entry-count">(' . absint( $term->count ) . ')</span>';
								}

							$output .= '</a>';
						$output .= '</' . $title_tag_escaped . '>';

					}

					// Display term description
					if ( $description && $term->description ) {

						$output .= '<div class="vcex-terms-grid-entry-excerpt wpex-mb-15 wpex-clr"' . $description_style . '>';

							$output .= do_shortcode( wp_kses_post( $term->description ) );

						$output .= '</div>';

					}

					// Display button
					if ( $button ) {

						if ( $first_run ) {

							$button_data    = array();
							$button_text    = $button_text ? $button_text : esc_html__( 'visit category', 'total' );
							$button_align   = $button_align ? ' text' . $button_align  : '';
							$button_classes = vcex_get_button_classes( $button_style, $button_style_color );

							$button_style = vcex_inline_style( array(
								'background'    => $button_background,
								'color'         => $button_color,
								'font_size'     => $button_size,
								'padding'       => $button_padding,
								'border_radius' => $button_border_radius,
								'margin'        => $button_margin,
							) );

							$button_hover_data = array();

							if ( $button_hover_background ) {
								$button_hover_data[ 'background' ] = $button_hover_background;
							}

							if ( $button_hover_color ) {
								$button_hover_data[ 'color' ] = $button_hover_color;
							}

							if ( $button_hover_data ) {
								$button_hover_data = htmlspecialchars( wp_json_encode( $button_hover_data ) );
							}

						}

						$output .= '<div class="vcex-terms-grid-entry-button wpex-my-15 wpex-clr' . esc_attr( $button_align ) . '">';

							$button_attrs = array(
								'href'            => esc_url( $term_link ),
								'class'           => esc_attr( $button_classes ),
								'style'           => $button_style,
								'data-wpex-hover' => $button_hover_data,
							);

							$output .= '<a' . vcex_parse_html_attributes( $button_attrs ) . '>';

								$output .= do_shortcode( wp_kses_post( $button_text ) );

							$output .= '</a>';

						$output .= '</div>';

					} // end button check

				}

			$output .= '</div>';

		// Close entry
		if ( $entry_css_class ) {
			$output .= '</div>';
		}

		// Clear counter
		if ( $counter === absint( $columns ) ) {
			$counter = 0;
		}

		$first_run = false;

	endforeach;

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
