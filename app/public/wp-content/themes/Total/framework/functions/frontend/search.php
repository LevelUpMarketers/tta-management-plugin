<?php
/**
 * Search frontend functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*/

	# Archives
	# Entry
	# Cards

/*-------------------------------------------------------------------------------*/
/* [ Archives ]
/*-------------------------------------------------------------------------------*/

/**
 * Defines your default search results page style.
 *
 * @since 1.5.4
 */
function wpex_search_results_style() {
	$style = '';
	if ( ! wpex_search_entry_card_style() ) {
		$style = get_theme_mod( 'search_style', 'default' );
		if ( wp_validate_boolean( get_theme_mod( 'search_results_cpt_loops', true ) ) ) {
			$post_type = ! empty ( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : '';
			if ( $post_type && 'product' !== $post_type ) {
				if ( in_array( $post_type, wpex_theme_post_types() ) ) {
					$style = $post_type;
				} elseif ( 'post' == $post_type ) {
					$style = 'blog';
				}
			}
		}
	}
	$style = $style ? $style : 'default'; // Important: Can't be empty or it will look like the blog
	return apply_filters( 'wpex_search_results_style', $style );
}

/**
 * Returns the search loop top class.
 *
 * @since 5.0
 */
function wpex_search_loop_top_class() {

	$classes = array(
		'wpex-clr',
	);

	if ( wpex_search_entry_card_style() ) {

		$classes[] = 'wpex-row';

		$grid_style = get_theme_mod( 'search_archive_grid_style', 'fit-rows' );

		if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
			$classes[] = 'wpex-masonry-grid';
			wpex_enqueue_isotope_scripts(); // This is a good spot to enqueue grid scripts
		}

		if ( $gap = get_theme_mod( 'search_archive_grid_gap' ) ) {
			$classes[] = wpex_gap_class( $gap );
		}

	}

	$classes = (array) apply_filters( 'wpex_search_loop_top_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Entry ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns search entry excerpt length.
 *
 * @since 5.0
 */
function wpex_search_entry_excerpt_length() {
	return apply_filters( 'wpex_search_entry_excerpt_length', get_theme_mod( 'search_entry_excerpt_length', '30' ) );
}

/**
 * Returns search entry excerpt length.
 *
 * @since 5.0
 */
function wpex_search_archive_columns() {
	$columns = '1';
	if ( wpex_search_entry_card_style() ) {
		$columns = get_theme_mod( 'search_entry_columns', '2' );
	}
	return apply_filters( 'wpex_search_archive_columns', $columns );
}

/**
 * Search entry class.
 *
 * @since 5.0
 */
function wpex_search_entry_class() {

	$classes = array(
		'search-entry',
	);

	$columns = absint( wpex_search_archive_columns() );

	if ( $columns > 1 || wpex_search_entry_card_style() ) {

		$col_class = wpex_grid_class( $columns );

		if ( $col_class ) {
			$classes[] = 'col';
			$classes[] = $col_class;
		}

		$loop_counter = wpex_get_loop_counter();

		if ( $loop_counter ) {
			$classes[] = 'col-' . absint( $loop_counter );
		}

		$grid_style = get_theme_mod( 'search_archive_grid_style', 'fit-rows' );

		if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
			$classes[] = 'wpex-masonry-col';
		}

	}

	$classes = (array) apply_filters( 'wpex_search_entry_class', $classes );

	post_class( $classes );

}

/**
 * Search entry inner class.
 *
 * @since 5.0
 */
function wpex_search_entry_inner_class() {

	$class = array(
		'search-entry-inner',
		'wpex-flex',
		'wpex-last-mb-0',
	);

	$class = (array) apply_filters( 'wpex_search_entry_inner_class', $class );

	post_class( $class );

}

/**
 * Search entry content class.
 *
 * @since 5.0
 */
function wpex_search_entry_content_class() {

	$class = array(
		'search-entry-text',
		'wpex-flex-grow',
		'wpex-last-mb-0',
	);

	$class = (array) apply_filters( 'wpex_search_entry_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Search entry header class.
 *
 * @since 5.0
 */
function wpex_search_entry_header_class() {

	$class = array(
		'search-entry-header',
	);

	$class = (array) apply_filters( 'wpex_search_entry_header_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Search entry title class.
 *
 * @since 5.0
 */
function wpex_search_entry_title_class() {

	$class = array(
		'search-entry-header-title',
		'entry-title',
		'wpex-text-lg',
		'wpex-m-0',
	);

	$class = (array) apply_filters( 'wpex_search_entry_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Search entry excerpt class.
 *
 * @since 5.0
 */
function wpex_search_entry_excerpt_class() {

	$class = array(
		'search-entry-excerpt',
		'wpex-my-15',
		'wpex-last-mb-0',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_search_entry_excerpt_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Search Entry divider.
 *
 * @since 5.0
 */
function wpex_search_entry_divider() {

	$columns = wpex_get_grid_entry_columns();

	$divider = '<div class="search-entry-divider wpex-divider wpex-my-25"></div>';

	echo apply_filters( 'wpex_search_entry_divider', $divider );
}

/*-------------------------------------------------------------------------------*/
/* [ Cards ]
/*-------------------------------------------------------------------------------*/

/**
 * Search card entry style
 *
 * @since 5.0
 */
function wpex_search_entry_card_style() {
	return apply_filters( 'wpex_search_entry_card_style', get_theme_mod( 'search_entry_card_style' ) );
}

/**
 * Search card entry.
 *
 * @since 5.0
 */
function wpex_search_entry_card() {

	$card_style = wpex_search_entry_card_style();

	if ( ! $card_style ) {
		return false;
	}

	$args = array(
		'style'          => $card_style,
		'post_id'        => get_the_ID(),
		'thumbnail_size' => 'search_results', // @todo rename to 'search_entry'?
		'excerpt_length' => wpex_search_entry_excerpt_length(),
	);

	if ( $overlay_style = wpex_overlay_style() ) {
		$args['thumbnail_overlay_style'] = $overlay_style;
	}

	$args = apply_filters( 'wpex_search_entry_card_args', $args );

	wpex_card( $args );

	return true;

}
