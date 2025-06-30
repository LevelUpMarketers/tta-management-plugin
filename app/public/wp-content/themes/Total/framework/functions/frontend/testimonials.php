<?php
/**
 * Helper functions for the testimonials post type
 *
 * @package Total WordPress Theme
 * @subpackage Testimonials Functions
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*/

	# Core
	# Archives
	# Entries
	# Single
	# Cards

/*-------------------------------------------------------------------------------*/
/* [ Core ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if a given testimonial has a rating.
 *
 * @since 5.0
 */
function wpex_has_testimonial_rating( $post = null ) {
	$rating  = wpex_get_testimonial_rating( $post );
    $has_rating = (bool) $rating;
    return (bool) apply_filters( 'wpex_has_testimonial_rating', $has_rating, $post, $rating );
}

/**
 * Get testimonial rating.
 *
 * @since 5.0
 */
function wpex_get_testimonial_rating( $post = null ) {
	$post = get_post( $post );
    if ( ! $post ) {
        return '';
    }
    $rating = get_post_meta( $post->ID, 'wpex_post_rating', true );
    return apply_filters( 'wpex_testimonial_rating', wpex_get_star_rating( $rating ) );
}

/**
 * Display testimonial rating.
 *
 * @since 5.0
 */
function wpex_testimonial_rating( $post = null ) {
	echo wpex_get_testimonial_rating( $post );
}

/**
 * Check if a given testimonial has an author.
 *
 * @since 5.0
 */
function wpex_has_testimonial_author( $post = null ) {
	$author  = wpex_get_testimonial_author( $post );
    $has_author = (bool) $author;
    return (bool) apply_filters( 'wpex_has_testimonial_author', $has_author, $post, $author );
}

/**
 * Get testimonial author.
 *
 * @since 5.0
 */
function wpex_get_testimonial_author( $post = null ) {
	$post = get_post( $post );
    if ( ! $post ) {
        return '';
    }
    return apply_filters( 'wpex_testimonial_author', get_post_meta( $post->ID, 'wpex_testimonial_author', true ) );
}

/**
 * Check if a given testimonial has a company set.
 *
 * @since 5.0
 */
function wpex_has_testimonial_company( $post = null ) {
	$company  = wpex_get_testimonial_company( $post );
    $has_company = (bool) $company;
    return (bool) apply_filters( 'wpex_has_testimonial_company', $has_company, $post, $company );
}

/**
 * Get testimonial company.
 *
 * @since 5.0
 */
function wpex_get_testimonial_company( $post = null ) {
	$post = get_post( $post );
    if ( ! $post ) {
        return '';
    }
    return apply_filters( 'wpex_testimonial_company', get_post_meta( $post->ID, 'wpex_testimonial_company', true ) );
}

/**
 * Check if a given testimonial has a company url set.
 *
 * @since 5.0
 */
function wpex_has_testimonial_company_link( $post = null ) {
	$company  = wpex_get_testimonial_company_url( $post );
    $has_company_url = (bool) $company;
    return (bool) apply_filters( 'wpex_has_testimonial_company_link', $has_company_url, $post, $company );
}

/**
 * Get testimonial company url.
 *
 * @since 5.0
 */
function wpex_get_testimonial_company_url( $post = null ) {
	$post = get_post( $post );
    if ( ! $post ) {
        return '';
    }
    $url = apply_filters( 'wpex_testimonial_company_url', get_post_meta( $post->ID, 'wpex_testimonial_url', true ) );
    return esc_url( $url );
}

/**
 * Get testimonial company url target
 *
 * @since 5.0
 * @todo should target the grid/carousel also?
 */
function wpex_get_testimonial_company_link_target() {
    return apply_filters( 'wpex_testimonial_company_link_target', '_blank' );
}

/*-------------------------------------------------------------------------------*/
/* [ Archives ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns testimonials archive grid style.
 *
 * @since 5.0
 */
function wpex_testimonials_archive_grid_style() {
	$style = ( $style = get_theme_mod( 'testimonials_archive_grid_style', 'fit-rows' ) ) ? $style : 'fit-rows';
	return apply_filters( 'wpex_testimonials_archive_grid_style', $style );
}

/*-------------------------------------------------------------------------------*/
/* [ Entries ]
/*-------------------------------------------------------------------------------*/

/**
 * Testimonials entry class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_class() {

	$grid_style = wpex_testimonials_archive_grid_style();
	$columns    = wpex_testimonials_archive_columns();

	$class = array(
		'testimonial-entry',
	);

	// Add grid column class
	if ( $col_class = wpex_grid_class( $columns ) ) {
		$class[] = 'col';
		$class[] = $col_class;
	}

	// Add counter class
	$loop_counter = wpex_get_loop_counter();

	if ( $loop_counter ) {
		$class[] = 'col-' . sanitize_html_class( $loop_counter );
	}

	if ( 'masonry' == $grid_style ) {
		$class[] = 'wpex-masonry-col';
	}

	$class = (array) apply_filters( 'wpex_testimonials_entry_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonials entry content class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_content_class() {

	$class = array(
		'testimonial-entry-content',
		'wpex-relative', // for caret
		'wpex-boxed',
		'wpex-border-0',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonials entry title class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_title_class() {

	$class = array(
		'testimonial-entry-title',
		'entry-title',
		'wpex-mb-10',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonials entry bottom class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_bottom_class() {

	$class = array(
		'testimonial-entry-bottom',
		'wpex-flex',
		'wpex-flex-wrap',
		'wpex-mt-20',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_bottom_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonials entry media class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_media_class() {

	$class = array(
		'testimonial-entry-thumb',
		'default-dims',
		'wpex-flex-shrink-0',
		'wpex-mr-20',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonials entry title class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_meta_class() {

	$class = array(
		'testimonial-entry-meta',
		'wpex-flex-grow',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_meta_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonials entry author class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_author_class() {

	$class = array(
		'testimonial-entry-author',
		'entry-title',
		'wpex-m-0',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_author_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonials entry rating class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_rating_class() {

	$class = array(
		'testimonial-entry-rating',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_rating_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonial entry company class.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_company_class() {

	$class = array(
		'testimonial-entry-company',
		'wpex-text-gray-500',
	);

	$class = (array) apply_filters( 'wpex_testimonials_entry_company_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonial entry link target
 *
 * @since 5.0
 */
function wpex_testimonials_entry_company_link_target() {

	$target = wpex_get_testimonial_company_link_target();

	if ( 'blank' === $target || '_blank' === $target ) {
		echo ' target="_blank" rel="noopener noreferrer"';
	}

}

/**
 * Display testimonial thumbnail
 *
 * @since 5.0
 */
function wpex_testimonials_entry_thumbnail( $args = array() ) {
	echo wpex_get_testimonials_entry_thumbnail( $args );
}

/**
 * Returns correct thumbnail HTML for the testimonials entries.
 *
 * @since 2.0.0
 */
function wpex_get_testimonials_entry_thumbnail( $args = array() ) {

	$classes = array(
		'testimonials-entry-img',
		'wpex-align-middle',
		'wpex-round',
		'wpex-border',
		'wpex-border-solid',
		'wpex-border-main',
	);

	$classes = apply_filters( 'wpex_testimonials_entry_thumbnail_class', $classes );

	$defaults = array(
        'size'  => 'testimonials_entry',
        'class' => $classes,
    );
    $args = wp_parse_args( $args, $defaults );
    return wpex_get_post_thumbnail( $args );
}

/**
 * Returns testimonials archive columns.
 *
 * @since 2.0.0
 */
function wpex_testimonials_archive_columns() {
	return get_theme_mod( 'testimonials_entry_columns', '4' );
}

/**
 * Returns the testimonials loop top class.
 *
 * @since 5.0
 */
function wpex_testimonials_loop_top_class() {

	$classes = (array) apply_filters( 'wpex_testimonials_loop_top_class', wpex_get_testimonials_wrap_classes() );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Returns correct classes for the testimonials archive wrap.
 *
 * @since 2.0.0
 */
function wpex_get_testimonials_wrap_classes() {

	// Define main classes
	$classes = array(
		'wpex-row',
	);

	// Get grid style
	$grid_style = wpex_testimonials_archive_grid_style();

	// This is a good spot to enqueue grid scripts
	if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
		$classes[] = 'wpex-masonry-grid';
		wpex_enqueue_isotope_scripts();
	}

	// Get grid style
	if ( 'masonry' === get_theme_mod( 'testimonials_archive_grid_style', 'fit-rows' ) ) {
		$classes[] = 'testimonials-masonry';
	}

	// Add gap
	if ( $gap = get_theme_mod( 'testimonials_archive_grid_gap' ) ) {
		$classes[] = 'gap-' . sanitize_html_class( $gap );
	}

	// Clear floats
	$classes[] = 'wpex-clr';

	// Sanitize
	$classes = array_map( 'esc_attr', $classes );

	// Apply filters
	$classes = apply_filters( 'wpex_testimonials_wrap_classes', $classes );

	// Turn array into string
	$classes = implode( ' ', $classes );

	// Return
	return $classes;

}

/*-------------------------------------------------------------------------------*/
/* [ Single ]
/*-------------------------------------------------------------------------------*/

/**
 * Testimonials single layout.
 *
 * @since 5.0
 */
function wpex_get_testimonials_single_layout() {
	return apply_filters( 'wpex_testimonials_single_layout', get_theme_mod( 'testimonial_post_style', 'blockquote' ) );
}

/**
 * Testimonials single content class.
 *
 * @since 5.0
 */
function wpex_testimonials_single_content_class() {

	$class = array(
		'single-content',
		'entry',
	);

	if ( ! wpex_has_post_wpbakery_content( get_the_ID() ) ) {
		$class[] = 'wpex-mb-40';
	}

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_testimonials_single_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Testimonial single comments class.
 *
 * @since 5.0
 */
function wpex_testimonials_single_comments_class() {

	$class = array(
		'single-comments',
		'wpex-mb-40',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_testimonials_single_comments_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Cards ]
/*-------------------------------------------------------------------------------*/

/**
 * Testimonial Card Entry.
 *
 * @since 5.0
 */
function wpex_testimonials_entry_card() {

	$card_style = get_theme_mod( 'testimonials_entry_card_style' );

	if ( 'related' === wpex_get_loop_instance() ) {
		$card_style = wpex_get_mod( 'testimonials_related_entry_card_style', $card_style, true );
	} else {
		$term_meta_check = wpex_get_term_meta( '', 'wpex_entry_card_style', true );
		if ( ! empty( $term_meta_check ) ) {
			$card_style = $term_meta_check;
		}
	}

	$card_style = apply_filters( 'wpex_testimonials_entry_card_style', $card_style );

	if ( ! $card_style ) {
		return false;
	}

	$args = array(
		'style'          => $card_style,
		'post_id'        => get_the_ID(),
		'thumbnail_size' => 'testimonials_entry',
		'excerpt_length' => get_theme_mod( 'testimonials_entry_excerpt_length', '-1' ),
	);

	$args = apply_filters( 'wpex_testimonials_entry_card_args', $args );

	wpex_card( $args );

	return true;

}