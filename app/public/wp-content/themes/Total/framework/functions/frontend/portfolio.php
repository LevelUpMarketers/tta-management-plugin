<?php
/**
 * Portfolio post type helper functions
 *
 * @package Total WordPress Theme
 * @subpackage Portfolio Functions
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*/

	# Videos
	# Archives
	# Entries
	# Single
	# Related
	# Cards

/*-------------------------------------------------------------------------------*/
/* [ Videos ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns portfolio featured video url.
 *
 * @since 1.5.2
 */
if ( ! function_exists( 'wpex_get_portfolio_featured_video_url' ) ) {
	function wpex_get_portfolio_featured_video_url( $post_id = '') {
		return wpex_get_post_video( $post_id );
	}
}

/**
 * Gets the portfolio featured video.
 *
 * @since 1.5.2
 */
function wpex_get_portfolio_post_video( $video = '' ) {

	$video = $video ? $video : wpex_get_post_video();

	$video = wpex_get_post_video_html( $video );

	if ( empty( $video ) ) {
		return;
	}

	return '<div class="portfolio-featured-video">' . $video . '</div>';

}

/**
 * Displays the portfolio featured video.
 *
 * @since 1.5.2
 */
if ( ! function_exists( 'wpex_portfolio_post_video' ) ) {
	function wpex_portfolio_post_video( $post_id = '', $video = false ) {
		echo wpex_get_portfolio_post_video( $post_id, $video );
	}
}


/*-------------------------------------------------------------------------------*/
/* [ Archives ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns the portfolio loop top class.
 *
 * @since 5.0
 */
function wpex_portfolio_loop_top_class() {

	$classes = (array) apply_filters( 'wpex_portfolio_loop_top_class', wpex_get_portfolio_wrap_classes() );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Returns correct classes for the portfolio wrap
 *
 * @since 1.5.3
 */
if ( ! function_exists( 'wpex_get_portfolio_wrap_classes' ) ) {
	function wpex_get_portfolio_wrap_classes() {

		// Get grid style
		$grid_style = wpex_portfolio_archive_grid_style();

		// Add default classes
		$classes = array(
			'wpex-row',
		);

		// Masonry class
		if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
			$classes[] = 'wpex-masonry-grid';
			wpex_enqueue_isotope_scripts(); // This is a good spot to enqueue grid scripts
		}

		// Add grid style class
		$classes[] = 'portfolio-' . sanitize_html_class( $grid_style );

		// Add gap
		if ( 'no-margins' === $grid_style ) {
			$classes[] = wpex_gap_class( '0px' );
		} elseif ( $gap = get_theme_mod( 'portfolio_archive_grid_gap' ) ) {
			$classes[] = wpex_gap_class( $gap );
		}

		// Add equal height class
		if ( wpex_portfolio_match_height() ) {
			$classes[] = 'match-height-grid';
		}

		// Clear floats
		$classes[] = 'wpex-clr';

		// Sanitize
		$classes = array_map( 'esc_attr', $classes );

		// Apply filters
		$classes = (array) apply_filters( 'wpex_portfolio_wrap_classes', $classes );

		// Turn into string
		$classes = implode( ' ', $classes );

		// Return classes
		return $classes;

	}
}

/**
 * Returns portfolio archive columns.
 *
 * @since 2.0.0
 */
function wpex_portfolio_archive_columns() {
	$columns = ( $columns = get_theme_mod( 'portfolio_entry_columns', '4' ) ) ? $columns : '4';
	return apply_filters( 'wpex_portfolio_archive_columns', $columns );
}

/**
 * Returns portfolio archive grid style.
 *
 * @since 5.0
 */
function wpex_portfolio_archive_grid_style() {
	$style = ( $style = get_theme_mod( 'portfolio_archive_grid_style', 'fit-rows' ) ) ? $style : 'fit-rows';
	return apply_filters( 'wpex_portfolio_archive_grid_style', $style );
}

/**
 * Checks if match heights are enabled for the portfolio.
 *
 * @since 1.5.3
 */
if ( ! function_exists( 'wpex_portfolio_match_height' ) ) {
	function wpex_portfolio_match_height() {
		$grid_style = wpex_portfolio_archive_grid_style();
		$columns    = wpex_portfolio_archive_columns();
		if ( 'fit-rows' == $grid_style && get_theme_mod( 'portfolio_archive_grid_equal_heights' ) && $columns > '1' ) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Returns correct classes for the portfolio grid.
 *
 * @since 1.5.2
 */
if ( ! function_exists( 'wpex_portfolio_column_class' ) ) {
	function wpex_portfolio_column_class( $loop = '' ) {

		$loop = $loop ? $loop : wpex_get_loop_instance();

		if ( 'related' === $loop ) {
			$columns = get_theme_mod( 'portfolio_related_columns', '4' );
		} else {
			$columns = wpex_portfolio_archive_columns();
		}

		return wpex_grid_class( $columns );

	}
}

/*-------------------------------------------------------------------------------*/
/* [ Entries ]
/*-------------------------------------------------------------------------------*/

/**
 * Get portfolio entry supported media types.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_supported_media() {

	return (array) apply_filters( 'wpex_portfolio_entry_supported_media', array(
		'video',
		'thumbnail',
	) );

}

/**
 * Get Portfolio entry media type.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_media_type() {

	$supported_media = wpex_portfolio_entry_supported_media();

	if ( in_array( 'video', $supported_media ) && wpex_has_post_video() ) {
		$type = 'video';
	} elseif ( in_array( 'thumbnail', $supported_media ) && has_post_thumbnail() ) {
		$type = 'thumbnail';
	} else {
		$type = '';
	}

	return apply_filters( 'wpex_portfolio_entry_media_type', $type );

}

/**
 * Portfolio entry class.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_class() {

	$class = (array) apply_filters( 'wpex_portfolio_entry_class', wpex_portfolio_entry_classes() );

	post_class( $class );

}

/**
 * Returns portfolio entry classs.
 *
 * @since 4.9.8
 */
function wpex_portfolio_entry_classes( $loop = '' ) {

	$loop = $loop ? $loop : wpex_get_loop_instance();

	$classes   = array();
	$classes[] = 'portfolio-entry';
	$classes[] = 'loop-' . sanitize_html_class( $loop );
	$classes[] = 'col';
	$classes[] = wpex_portfolio_column_class( $loop );

	$loop_counter = wpex_get_loop_counter();

	if ( $loop_counter ) {
		$classes[] = 'col-' . sanitize_html_class( $loop_counter );
	}

	if ( 'archive' == $loop ) {

		$grid_style = wpex_portfolio_archive_grid_style();

		if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
			$classes[] = 'wpex-masonry-col';
		}

	}

	$classes = array_map( 'esc_attr', $classes );

	return apply_filters( 'wpex_portfolio_entry_classes', $classes, $loop ); // @todo deprecate filter?
}

/**
 * Portfolio entry inner class.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_inner_class( $loop = '' ) {

	$loop = $loop ? $loop : wpex_get_loop_instance();

	$class = array(
		'portfolio-entry-inner',
		'wpex-last-mb-0',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_portfolio_entry_inner_class', $class, $loop );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio entry media class.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_media_class() {

	$media_type = wpex_portfolio_entry_media_type();

	$class = array(
		'portfolio-entry-media',
		'entry-media',
	);

	if ( 'thumbnail' === $media_type ) {

		if ( $overlay_classes = wpex_overlay_classes() ) {
			$class[] = $overlay_classes;
		}

		if ( $animation_classes = wpex_get_entry_image_animation_classes() ) {
			$class[] = $animation_classes;
		}

	}

	$class = (array) apply_filters( 'wpex_portfolio_entry_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio entry details class.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_content_class() {

	$class = array(
		'portfolio-entry-details',
		'wpex-boxed',
	);

	if ( wpex_portfolio_match_height() ) {
		$class[] = 'match-height-content';
	}

	$class[] = 'wpex-last-mb-0';
	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_portfolio_entry_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio entry title class.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_title_class() {

	$class = array(
		'portfolio-entry-title',
		'entry-title',
		'wpex-mb-5',
	);

	$class = (array) apply_filters( 'wpex_portfolio_entry_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio entry excerpt class.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_excerpt_class() {

	$class = array(
		'portfolio-entry-excerpt',
		'wpex-my-15',
		'wpex-last-mb-0',
	);

	$class = (array) apply_filters( 'wpex_portfolio_entry_excerpt_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Displays the media (featured image or video ) for the portfolio entries.
 *
 * @since 1.3.6
 */
if ( ! function_exists( 'wpex_portfolio_entry_media' ) ) {
	function wpex_portfolio_entry_media() {
		get_template_part( 'partials/portfolio/entry-media' );
	}
}

/**
 * Returns correct thumbnail HTML for the portfolio entries.
 *
 * @since 2.0.0
 */
function wpex_get_portfolio_entry_thumbnail( $loop = '' ) {

	$loop = $loop ? $loop : wpex_get_loop_instance();

	return wpex_get_post_thumbnail( apply_filters( 'wpex_get_portfolio_entry_thumbnail_args', array(
		'size'  => 'archive' === $loop ? 'portfolio_entry' : 'portfolio_related',
		'class' => 'portfolio-entry-media-img portfolio-entry-img wpex-align-middle',
	) ) );
}

/**
 * Echo portfolio entry thumbnail.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_thumbnail() {
	echo wpex_get_portfolio_entry_thumbnail();
}

/**
 * Check if portfolio entry content is enabled.
 *
 * @since 5.0
 */
function wpex_has_portfolio_entry_content() {

	$loop = wpex_get_loop_instance();

	if ( 'related' === $loop ) {
		$bool = get_theme_mod( 'portfolio_related_excerpts', true );
	} else {
		$bool = get_theme_mod( 'portfolio_entry_details', true );
	}

	return (bool) apply_filters( 'wpex_has_portfolio_entry_content', $bool );

}

/**
 * Displays the details for the portfolio entries.
 *
 * @since 1.3.6
 * @deprecated
 */
if ( ! function_exists( 'wpex_portfolio_entry_content' ) ) {
	function wpex_portfolio_entry_content() {
		get_template_part( 'partials/portfolio/entry-content' );
	}
}

/**
 * Returns portfolio entry excerpt length.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_excerpt_length() {

	$length = get_theme_mod( 'portfolio_entry_excerpt_length', 20 );

	if ( 'related' === wpex_get_loop_instance() ) {
		$length = get_theme_mod( 'portfolio_related_entry_excerpt_length', $length );
	}

	return apply_filters( 'wpex_portfolio_entry_excerpt_length', $length );

}

/*-------------------------------------------------------------------------------*/
/* [ Single ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns portfolio post blocks.
 *
 * @since 2.1.0
 */
function wpex_portfolio_single_blocks() {

	// Get layout blocks
	$blocks = get_theme_mod( 'portfolio_post_composer' );

	// If blocks are empty return defaults - should never be empty!
	$blocks = $blocks ? $blocks : array( 'content', 'share', 'related' );

	// Convert blocks to array so we can loop through them
	if ( ! is_array( $blocks ) ) {
		$blocks = explode( ',', $blocks );
	}

	// Apply filters and return blocks
	return apply_filters( 'wpex_portfolio_single_blocks', $blocks, 'front-end' );

}

/**
 * Get Portfolio single supported media types.
 *
 * @since 5.0
 */
function wpex_portfolio_single_supported_media() {

	return (array) apply_filters( 'wpex_portfolio_single_supported_media', array(
		'gallery',
		'video',
		'thumbnail',
	) );

}

/**
 * Get Portfolio single format.
 *
 * @since 5.0
 */
function wpex_portfolio_single_media_type() {

	$supported_media = wpex_portfolio_single_supported_media();

	if ( in_array( 'video', $supported_media ) && wpex_has_post_video() ) {
		$type = 'video';
	} elseif ( in_array( 'gallery', $supported_media ) && wpex_has_post_gallery() ) {
		$type = 'gallery';
	} elseif ( in_array( 'thumbnail', $supported_media ) && has_post_thumbnail() ) {
		$type = 'thumbnail';
	} else {
		$type = ''; //important
	}

	return apply_filters( 'wpex_portfolio_single_media_type', $type );

}

/**
 * Returns portfolio single meta sections.
 *
 * @since 2.0.0
 */
function wpex_portfolio_single_meta_sections() {

	// Default sections
	$sections = array( 'date', 'author', 'categories', 'comments' );

	// Apply filters for easy modification
	$sections = apply_filters( 'wpex_portfolio_single_meta_sections', $sections );

	// Turn into array if string
	if ( $sections && ! is_array( $sections ) ) {
		$sections = explode( ',', $sections );
	}

	// Return sections
	return $sections;

}

/**
 * Returns correct thumbnail HTML for the portfolio posts.
 *
 * @since 2.0.0
 */
function wpex_get_portfolio_post_thumbnail( $args = array() ) {

	// Define thumbnail args
	$defaults = array(
		'size'          => 'portfolio_post',
		'class'         => 'portfolio-single-media-img wpex-align-middle',
		'schema_markup' => true,
	);

	// Parse arguments
	$args = wp_parse_args( $args, $defaults );

	// Return thumbanil
	return wpex_get_post_thumbnail( apply_filters( 'wpex_get_portfolio_post_thumbnail_args', $args ) );

}

/**
 * Portfolio single blocks class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_blocks_class() {

	$class = array(
		'wpex-first-mt-0',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_portfolio_single_blocks_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio single media class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_media_class() {

	$class = array(
		'single-media',
		'wpex-mb-20',
	);

	$class = (array) apply_filters( 'wpex_portfolio_single_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio single content class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_content_class() {

	$class = array(
		'single-content',
		'wpex-mt-20',
		'entry',
	);

	if ( ! wpex_has_post_wpbakery_content( get_the_ID() ) ) {
		$class[] = 'wpex-mb-40';
	}

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_portfolio_single_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio single header class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_header_class() {

	$class = array(
		'single-header',
	);

	$class = (array) apply_filters( 'wpex_portfolio_single_header_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio single title class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_title_class() {

	$class = array(
		'single-post-title',
		'entry-title',
		'wpex-text-3xl',
		'wpex-mb-10',
	);

	$class = (array) apply_filters( 'wpex_portfolio_single_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio single meta class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_meta_class() {

	$class = array(
		'meta',
		'wpex-text-sm',
		'wpex-text-gray-600',
		'wpex-mb-20',
		'wpex-last-mr-0',
	);

	$class = (array) apply_filters( 'wpex_portfolio_single_meta_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio single comments class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_comments_class() {

	$class = array(
		'single-comments',
		'wpex-mb-40',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_portfolio_single_comments_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Related ]
/*-------------------------------------------------------------------------------*/

/**
 * Portfolio related query.
 *
 * @since 5.0
 */
function wpex_portfolio_single_related_query() {

	$post_id = get_the_ID();

	if ( wpex_validate_boolean( get_post_meta( $post_id, 'wpex_disable_related_items', true ) ) ) {
		return false;
	}

	// Posts count
	$posts_count = get_theme_mod( 'portfolio_related_count', '4' );

	// Return if count is empty or 0
	if ( empty( $posts_count ) || '0' == $posts_count ) {
		return;
	}

	// Related query arguments
	$args = array(
		'post_type'      => 'portfolio',
		'posts_per_page' => $posts_count,
		'order'          => get_theme_mod( 'portfolio_related_order', 'desc' ),
		'orderby'        => get_theme_mod( 'portfolio_related_orderby', 'date' ),
		'post__not_in'   => array( $post_id ),
		'no_found_rows'  => true,
	);

	// Related by taxonomy
	if ( apply_filters( 'wpex_related_in_same_cat', true ) ) {

		// Add categories to query
		$related_taxonomy = get_theme_mod( 'portfolio_related_taxonomy', 'portfolio_category' );

		// Generate related by taxonomy args
		if ( 'null' !== $related_taxonomy && taxonomy_exists( $related_taxonomy ) ) {

			$terms = '';

			$primary_term = wpex_get_post_primary_term( $post_id, $related_taxonomy );

			if ( $primary_term ) {

				$terms = array( $primary_term->term_id );

			} else {

				$get_terms = get_the_terms( $post_id, $related_taxonomy );

				if ( $get_terms && ! is_wp_error( $get_terms ) ) {
					$terms = wp_list_pluck( $get_terms, 'term_id' );
				}

			}

			if ( $terms ) {

				$args['tax_query'] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => $related_taxonomy,
						'field'    => 'term_id',
						'terms'    => $terms,
					)
				);

			}

		}

	}

	// Add filter so you can alter the query via child theme without having to modify this file
	$args = (array) apply_filters( 'wpex_related_portfolio_args', $args );

	if ( $args ) {
		return new wp_query( $args );
	}

}

/**
 * Portfolio single related heading.
 *
 * @since 2.0.0
 */
function wpex_portfolio_single_related_heading() {
	$text = wpex_portfolio_related_heading();
	if ( $text ) {
		wpex_heading( array(
			'content'		=> $text,
			'classes'		=> array( 'related-portfolio-posts-heading' ),
			'apply_filters'	=> 'portfolio_related',
		) );
	}
}

/**
 * Portfolio related heading.
 *
 * @since 2.0.0
 */
function wpex_portfolio_related_heading() {
	$heading = wpex_get_translated_theme_mod( 'portfolio_related_title' );
	$heading = $heading ? esc_html( $heading ) : esc_html__( 'Related Projects', 'total' );
	return $heading;
}

/**
 * Portfolio related class.
 *
 * @since 5.0
 */
function wpex_portfolio_single_related_class() {

	$class = array(
		'related-portfolio-posts',
		'wpex-mb-40',
	);

	if ( 'full-screen' == wpex_content_area_layout() ) {
		$class[] = 'container';
	}

	$class = (array) apply_filters( 'wpex_portfolio_single_related_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Portfolio single related row class.
 *
 * @since 5.0
 * @todo deprecate wpex_related_portfolio_row_classes filter
 */
function wpex_portfolio_single_related_row_class() {

	$classes = array(
		'wpex-row',
		'wpex-clr'
	);

	if ( $gap = get_theme_mod( 'portfolio_related_gap' ) ) {
		$classes[] = wpex_gap_class( $gap );
	}

	$classes = (array) apply_filters( 'wpex_portfolio_single_related_row_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( apply_filters( 'wpex_related_portfolio_row_classes', implode( ' ', $classes ) ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Cards ]
/*-------------------------------------------------------------------------------*/

/**
 * Portfolio Card Entry.
 *
 * @since 5.0
 */
function wpex_portfolio_entry_card() {

	$instance = wpex_get_loop_instance();

	if ( 'related' === $instance ) {
		$card_style = get_theme_mod( 'portfolio_related_entry_card_style' );
	} else {
		$term_meta_check = wpex_get_term_meta( '', 'wpex_entry_card_style', true );
		if ( ! empty( $term_meta_check ) ) {
			$card_style = $term_meta_check;
		} else {
			$card_style = get_theme_mod( 'portfolio_entry_card_style' );
		}
	}

	$card_style = apply_filters( 'wpex_portfolio_entry_card_style', $card_style );

	if ( ! $card_style ) {
		return false;
	}

	$args = array(
		'style'          => $card_style,
		'post_id'        => get_the_ID(),
		'thumbnail_size' => ( 'related' === $instance ) ? 'portfolio_related' : 'portfolio_entry',
		'excerpt_length' => wpex_portfolio_entry_excerpt_length(),
	);

	if ( $overlay_style = wpex_overlay_style() ) {
		$args['thumbnail_overlay_style'] = $overlay_style;
	}

	$args = apply_filters( 'wpex_portfolio_entry_card_args', $args );

	wpex_card( $args );

	return true;

}
