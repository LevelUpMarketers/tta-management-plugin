<?php
/**
 * Grid filter functions.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Url param to check for for filters.
 */
function vcex_grid_filter_url_param() {
	return apply_filters( 'vcex_grid_filter_url_param', 'filter' );
}

/**
 * Get vcex grid filter active item.
 */
function vcex_grid_filter_get_active_item( $tax = '' ) {
	$param = vcex_grid_filter_url_param();
	if ( empty( $_GET[$param] ) ) {
		return;
	}
	$paramv = esc_html( $_GET[$param] );
	if ( $tax && ! is_numeric( $paramv ) ) {
		$get_term = get_term_by( 'slug', $paramv, $tax );
		if ( ! $get_term ) {
			$get_term = get_term_by( 'name', $paramv, $tax );
		}
		if ( $get_term ) {
			$term_id = $get_term->term_id;
			if ( class_exists( 'SitePress' ) ) {
				global $sitepress;
				$term_id = apply_filters( 'wpml_object_id', $term_id, $taxonomy, true, $sitepress->get_default_language() );
			}
			return $term_id;
		}
	}
	return $paramv;
}

/**
 * Return grid filter arguments.
 */
function vcex_grid_filter_args( $atts = '', $query = '' ) {

	// Return if no attributes found
	if ( ! $atts ) {
		return;
	}

	// Define args
	$args = $include = array();

	// Don't get empty
	$args['hide_empty'] = true;

	// Taxonomy
	if ( ! empty( $atts['filter_taxonomy'] ) ) {
		$taxonomy = $atts['filter_taxonomy'];
	} elseif ( isset( $atts['taxonomy'] ) ) {
		$taxonomy = $atts['taxonomy']; // Fallback
	} else {
		$taxonomy = null;
	}

	// Define post type and taxonomy
	$post_type = ! empty( $atts['post_type'] ) ? $atts['post_type'] : '';

	// Define include/exclude category vars
	$include_cats = ! empty( $atts['include_categories'] ) ? vcex_string_to_array( $atts['include_categories'] ) : '';

	// Check if only 1 category is included
	// If so check if it's a parent item so we can display children as the filter links
	if ( $include_cats && '1' == count( $include_cats )
		&& $children = get_term_children( $include_cats[0], $taxonomy )
	) {
		$include = $children;
	}

	// Check for ajax pagination
	$ajax_pagination = ( isset( $atts['pagination_loadmore'] ) && 'true' == $atts['pagination_loadmore'] ) ? true : false;

	// Ajax pagination should include all categories or specified ones
	if ( $ajax_pagination ) {

		if ( $include_cats && is_array( $include_cats ) ) {
			$include = $include_cats;
		}

		$exclude_cats = ! empty( $atts['exclude_categories'] ) ? vcex_string_to_array( $atts['exclude_categories'] ) : '';
		$exclude = $exclude_cats;

	}

	// Include only terms from current query
	elseif ( empty( $include ) && $query ) {

		// Pluck ids from query
		$post_ids = wp_list_pluck( $query->posts, 'ID' );

		// Loop through post ids
		foreach ( $post_ids as $post_id ) {

			// Get post terms
			$terms = get_the_terms( $post_id, $taxonomy );

			// Make sure there is no errors with terms and post has terms
			if ( $terms && ! is_wp_error( $terms ) ) {

				// Loop through terms
				foreach( $terms as $term ) {

					// Store term id
					$term_id = $term->term_id;

					// WPML Check
					if ( class_exists( 'SitePress' ) ) {
						global $sitepress;
						$term_id = apply_filters( 'wpml_object_id', $term_id, $taxonomy, true, $sitepress->get_default_language() );
					}

					// Include terms if include_cats variable is empty
					if ( ! $include_cats ) {

						// Include term
						$include[$term_id] = $term_id;

						/* Include parent
						if ( $term->parent ) {
							$include[$term->parent] = $term->parent;
						}*/

					}

					// Include terms if include_cats is enabled and term is in var
					elseif ( $include_cats && in_array( $term_id, $include_cats ) ) {
						$include[$term_id] = $term_id;
					}

				}

			}

		}

		// Add included terms to include param
		$args['include'] = $include;

	}

	// Add to args
	if ( ! empty( $include ) ) {
		$args['include'] = $include;
	}
	if ( ! empty( $exclude ) ) {
		$args['exclude'] = $exclude;
	}

	// Apply filters @todo deprecate?
	if ( $post_type ) {
		$args = apply_filters( "vcex_{$post_type}_grid_filter_args", $args );
	}

	// Return args
	return apply_filters( 'vcex_grid_filter_args', $args, $post_type );

}