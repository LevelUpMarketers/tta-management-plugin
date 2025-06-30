<?php
/**
 * Archives functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get index loop type.
 *
 * @since 4.5
 * @todo rename to wpex_get_archive_loop_type?
 */
function wpex_get_index_loop_type() {
	$loop_type = '';

	// Blog Query
	if ( wpex_is_blog_query() ) {
		$loop_type = 'blog';
	}

	// Search results
	elseif ( is_search() ) {
		$search_style = wpex_search_results_style();
		$loop_type = ( 'default' == $search_style ) ? 'search' : $search_style;
	}

	// Taxonomies
	elseif ( is_tax() ) {

		$taxonomy = get_query_var( 'taxonomy' );

		switch ( $taxonomy ) {

			case 'portfolio_tag':
			case 'portfolio_category':
				$loop_type = 'portfolio';
				break;

			case 'staff_tag':
			case 'staff_category':
				$loop_type = 'staff';
				break;

			case 'testimonials_tag':
			case 'testimonials_category':
				$loop_type = 'testimonials';
				break;

		}

	}

	// Post Type archives
	elseif ( is_post_type_archive() ) {
		$loop_type = get_query_var( 'post_type' );
	}

	// If empty try to get value from get_post_type
	if ( empty( $loop_type ) ) {
		$loop_type = get_post_type();
	}

	// Apply filters and return
	return apply_filters( 'wpex_get_index_loop_type', $loop_type );

}

/**
 * Returns true if the current Query is a query related to standard blog posts.
 *
 * @since 1.6.0
 */
function wpex_is_blog_query() {

	// False by default
	$bool = false;

	// Return true for blog archives
	if ( is_search() ) {
		if ( 'blog' === wpex_search_results_style() ) {
			$bool = true;
		} else {
			$bool = false;
		}
	} elseif (
		is_home()
		|| is_category()
		|| is_tag()
		|| is_date()
		|| is_author()
		|| is_page_template( 'templates/blog.php' )
		|| is_page_template( 'templates/blog-content-above.php' )
		|| ( is_tax( 'post_series' ) && 'post' == get_post_type() )
		|| ( is_tax( 'post_format' ) && 'post' == get_post_type() )
	) {
		$bool = true;
	}

	// Apply filters and return
	return apply_filters( 'wpex_is_blog_query', $bool );

}

/**
 * Return archive grid style.
 *
 * @since 5.0
 */
function wpex_archive_grid_style() {
	$style = 'fit-rows';

	if ( WPEX_PTU_ACTIVE ) {

		if ( is_post_type_archive() ) {

			$ptu_check = wpex_get_ptu_type_mod( get_query_var( 'post_type' ), 'archive_grid_style' );

			if ( $ptu_check ) {
				$style = $ptu_check;
			}

		}

		if ( is_tax() ) {

			$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'grid_style' );

			if ( $ptu_check ) {
				$style = $ptu_check;
			}

		}

	}

	return apply_filters( 'wpex_archive_grid_style', $style );
}

/**
 * Return archive grid gap.
 *
 * @since 5.0
 */
function wpex_archive_grid_gap() {

	$gap = '';

	if ( WPEX_PTU_ACTIVE ) {

		if ( is_post_type_archive() ) {

			$ptu_check = wpex_get_ptu_type_mod( get_query_var( 'post_type' ), 'archive_grid_gap' );

			if ( $ptu_check ) {
				$gap = $ptu_check;
			}

		}

		if ( is_tax() ) {

			$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'grid_gap' );

			if ( $ptu_check ) {
				$gap = $ptu_check;
			}

		}

	}

	return apply_filters( 'wpex_archive_grid_gap', $gap );

}

/**
 * Returns correct classes for archive grid.
 *
 * @since 3.6.0
 */
function wpex_get_archive_grid_class() {

	// Define class array
	$classes = array(
		'archive-grid',
		'entries',
		'wpex-row',
	);

	// Get grid style
	$grid_style = wpex_archive_grid_style();

	if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
		$classes[] = 'wpex-masonry-grid';
		wpex_enqueue_isotope_scripts(); // This is a good spot to enqueue grid scripts
	}

	// Grid gap
	$gap = wpex_archive_grid_gap();

	if ( $gap ) {
		$classes[] = wpex_gap_class( $gap );
	}

	// Clear floats
	$classes[] = 'wpex-clr';

	// Apply filters
	$classes = apply_filters( 'wpex_get_archive_grid_class', $classes ); // legacy
	$classes = apply_filters( 'wpex_archive_grid_class', $classes ); // @since 5.0

	// Return classes as a string
	return implode( ' ', $classes );

}

/**
 * Returns correct grid columns for custom types.
 *
 * @since 3.6.0
 * @todo rename to wpex_cpt_entry_columns for consistency?
 */
function wpex_get_grid_entry_columns() {
	$columms = '1';

	if ( is_post_type_archive() ) {
		$columms = get_theme_mod( get_post_type() . '_grid_entry_columns', $columms );
	}

	if ( WPEX_PTU_ACTIVE ) {

		if ( is_post_type_archive() ) {

			$ptu_check = wpex_get_ptu_type_mod( get_query_var( 'post_type' ), 'archive_grid_columns' );

			if ( $ptu_check ) {
				$columms = $ptu_check;
			}

		}

		if ( is_tax() ) {

			$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'grid_columns' );

			if ( $ptu_check ) {
				$columms = $ptu_check;
			}

		}

	}

	return apply_filters( 'wpex_get_grid_entry_columns', $columms );
}

/**
 * Returns classes for archive grid entries.
 *
 * @since 3.6.0
 */
function wpex_get_archive_grid_entry_class() {

	$classes = array(
		'cpt-entry',
		'wpex-clr'
	);

	$columns = wpex_get_grid_entry_columns();

	if ( $columns ) {

		$col_class = wpex_grid_class( $columns );

		if ( $col_class ) {

			$classes[] = 'col';

			$classes[] = $col_class;

			$counter = wpex_get_loop_counter();

			if ( $counter ) {
				$classes[] = 'col-' . sanitize_html_class( $counter );
			}

		}

	}

	$grid_style = wpex_archive_grid_style();

	if ( absint( $columns ) > 1 && 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
		$classes[] = 'wpex-masonry-col';
	}

	return apply_filters( 'wpex_get_archive_grid_entry_class', $classes );

}

/**
 * Get term description location.
 *
 * @since 5.0
 */
function wpex_term_description_location() {

	if ( is_category() || is_tag() ) {
		$location = get_theme_mod( 'category_description_position' );
	} else {

		if ( wpex_is_woo_tax() ) {
			$location = get_theme_mod( 'woo_category_description_position' );
		}

		if ( WPEX_PTU_ACTIVE ) {

			$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'term_description_position' );

			if ( $ptu_check ) {
				$location = $ptu_check;
			}

		}

	}

	if ( empty( $location ) ) {
		$location = wpex_has_page_header() ? 'under_title' : 'above_loop'; // @todo change to page_subheading? and add default mod option.
	}

	return apply_filters( 'wpex_term_description_location', $location );

}

/**
 * Check if term description should display above the loop.
 *
 * By default the term description displays in the subheading in the page header,
 * however, there are some built-in settings to enable the term description above the loop.
 * This function returns true if the term description should display above the loop and not in the header.
 *
 * @since 2.0.0
 */
function wpex_has_term_description_above_loop() {
	$check = false;

	$location = wpex_term_description_location();

	if ( 'above_loop' == $location ) {
		$check = true;
	}

	return (bool) apply_filters( 'wpex_has_term_description_above_loop', $check );

}

/**
 * Check if page header image is enabled for term.
 *
 * @since 3.6.0
 * @todo rename to wpex_has_term_page_header_image
 */
function wpex_term_page_header_image_enabled( $term_id = '' ) {

	// Enabled by default
	$check = true;

	// Disable for WooCommerce by default
	if ( wpex_is_woo_tax() ) {
		$check = false;
	}

	if ( WPEX_PTU_ACTIVE ) {

		$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'term_page_header_image_enabled' );

		if ( isset( $ptu_check ) ) {
			$check = wp_validate_boolean( $ptu_check );
		}

	}

	// Default return (apply filters here because meta should override)
	$check = apply_filters( 'wpex_term_page_header_image_enabled', $check );

	// Get term id
	$term_id = $term_id ? $term_id : get_queried_object_id();

	// Term id isn't empty so lets locate the thumbnail
	if ( $term_id ) {

		$meta_check = get_term_meta( $term_id, 'page_header_bg', true );

		if ( ! isset( $meta_check ) ) {

			// Get data
			$term_data = get_option( 'wpex_term_data' );
			$term_data = ! empty( $term_data[ $term_id ] ) ? $term_data[ $term_id ] : '';

			// Check setting
			if ( $term_data && isset( $term_data['page_header_bg'] ) ) {
				$meta_check = $term_data['page_header_bg'];
			}

		}

		// Validate meta
		if ( is_bool( $meta_check ) ) {
			$check = $meta_check;
		} elseif ( is_string( $meta_check ) && '' != $meta_check ) {
			$meta_check = strtolower( $meta_check );
			if ( in_array( $meta_check, array( 'false', 'off', 'disabled' ) ) ) {
				$meta_check = false;
			}
			if ( in_array( $meta_check, array( 'true', 'on', 'enabled' ) ) ) {
				$meta_check = true;
			}
			$check = $meta_check;
		}

	}

	// Return bool
	return (bool) $check;

}