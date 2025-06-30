<?php
/**
 * Custom Post Types (cpt) functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*/

	# Entry
	# Single
	# Related
	# Cards

/*-------------------------------------------------------------------------------*/
/* [ Entry ]
/*-------------------------------------------------------------------------------*/

/**
 * Post Type Entry Supported Media Types.
 *
 * @since 5.0
 */
function wpex_cpt_entry_supported_media() {

	return (array) apply_filters( 'wpex_cpt_entry_supported_media', array(
		'video',
		'audio',
		//'gallery', // not supported by default needs to be enabled via child theme
		'thumbnail',
	), get_post_type() );

}

/**
 * Get Post Type Entry media type.
 *
 * @since 5.0
 */
function wpex_cpt_entry_media_type() {

	$supported_media = wpex_cpt_entry_supported_media();

	if ( in_array( 'video', $supported_media ) && wpex_has_post_video() ) {
		$type = 'video';
	} elseif ( in_array( 'audio', $supported_media ) && wpex_has_post_audio() ) {
		$type = 'audio';
	} elseif ( in_array( 'gallery', $supported_media ) && wpex_has_post_gallery() ) {
		$type = 'gallery';
	} elseif ( in_array( 'thumbnail', $supported_media ) && has_post_thumbnail() ) {
		$type = 'thumbnail';
	} else {
		$type = ''; //important
	}

	return apply_filters( 'wpex_cpt_entry_media_type', $type );

}

/**
 * Get post type entry thumbnail size.
 *
 * @since 5.0.5
 */
function wpex_cpt_entry_thumbnail_size() {
	$size      = 'full';
	$post_type = get_post_type();
	$instance  = wpex_get_loop_instance();

	// Related entry image size
	if ( 'related' === $instance ) {
		$size = $post_type . '_single_related';
	}

	// Standard entry image size
	else {

		$size = $post_type . '_archive';

		if ( WPEX_PTU_ACTIVE ) {

			if ( is_tax() ) {
				$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'entry_image_size' );
				if ( ! empty( $ptu_check ) ) {
					$size = $ptu_check;
				}
			}

		}

	}

	return apply_filters( "wpex_{$post_type}_entry_thumbnail_size", $size );

}

/**
 * Get Post Type Entry overlay style.
 *
 * @since 5.0
 */
function wpex_cpt_entry_overlay_style() {

	$post_type = get_post_type();

	$overlay = '';

	if ( 'related' === wpex_get_loop_instance() ) {
		$overlay = wpex_get_ptu_type_mod( $post_type, 'related_entry_overlay_style' );
		$overlay = apply_filters( 'wpex_cpt_single_related_overlay', $overlay ); // legacy
		$overlay = apply_filters( "wpex_{$post_type}_related_entry_overlay_style", $overlay ); // new in 5.0
	} else {
		if ( is_post_type_archive() ) {
			$overlay = wpex_get_ptu_type_mod( $post_type, 'entry_overlay_style' );
		} elseif ( is_tax() ) {
			$overlay = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'entry_overlay_style' );
		}
		$overlay = apply_filters( "wpex_{$post_type}_entry_overlay_style", $overlay );
	}

	if ( ! $overlay ) {
		$overlay = 'none';
	}

	return $overlay;
}

/**
 * Get post type entry excerpt length.
 *
 * @since 5.0.5
 */
function wpex_cpt_entry_excerpt_length() {
	$post_type = get_post_type();
	$instance  = wpex_get_loop_instance();

	// Related entry excerpt length
	if ( 'related' == $instance ) {
		$length = '15';
		if ( WPEX_PTU_ACTIVE ) {
			$ptu_check = wpex_get_ptu_type_mod( $post_type, 'related_entry_excerpt_length' );
			if ( isset( $ptu_check ) && '' !== trim( $ptu_check ) ) {
				$length = $ptu_check;
			}
		}
		$length = apply_filters( 'wpex_cpt_single_related_excerpt_length', $length ); // legacy
		$length = apply_filters( "wpex_{$post_type}_single_related_excerpt_length", $length );
	}

	// Archives excerpt length
	else {
		$length = '40';

		if ( WPEX_PTU_ACTIVE ) {

			// Allow the archive setting to work for all entries
			$ptu_check = wpex_get_ptu_type_mod( $post_type, 'entry_excerpt_length' );
			if ( isset( $ptu_check ) && '' !== trim( $ptu_check ) ) {
				$length = $ptu_check;
			}

			// Custom tax entry excerpt length
			if ( is_tax() ) {
				$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'entry_excerpt_length' );
				if ( isset( $ptu_check ) && '' !== trim( $ptu_check ) ) {
					$length = $ptu_check;
				}
			}

		}

		$length = apply_filters( "wpex_{$post_type}_entry_excerpt_length", $length );

	}

	return intval( $length ); // note use intval since we support -1 for full excerpt.
}

/**
 * Post Type Entry Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_class() {

	$class = (array) apply_filters( 'wpex_cpt_entry_class', wpex_get_archive_grid_entry_class() );

	post_class( $class );

}

/**
 * Post Type Entry Inner Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_inner_class() {

	$class = array(
		'cpt-entry-inner',
		'entry-inner',
	);

	$class[] = 'wpex-last-mb-0';
	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_cpt_entry_inner_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Entry Media Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_media_class() {

	$media_type = wpex_cpt_entry_media_type();

	$class = array(
		'cpt-entry-media',
		'cpt-entry-' . sanitize_html_class( $media_type ),
		'entry-media',
	);

	if ( 'thumbnail' === $media_type ) {

		$overlay = wpex_cpt_entry_overlay_style();

		if ( $overlay ) {

			$overlay_classes = wpex_overlay_classes( $overlay );

			if ( $overlay_classes ) {
				$class[] = $overlay_classes;
			}

		}

	}

	$class[] = 'wpex-mb-20';

	$class = (array) apply_filters( 'wpex_cpt_entry_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Entry Header Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_header_class() {

	$class = array(
		'cpt-entry-header',
		'entry-header',
	);

	$class = (array) apply_filters( 'wpex_cpt_entry_header_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Entry Title Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_title_class() {
	$columns = wpex_get_grid_entry_columns();

	$class = array(
		'cpt-entry-title',
		'entry-title',
	);

	if ( '1' == $columns ) {

		$class[] = 'wpex-text-3xl';

	}

	$class = (array) apply_filters( 'wpex_cpt_entry_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Entry Meta Class.
 *
 * @since 5.0
 */
function wpex_cpt_meta_class() {

	$singular = is_singular( get_post_type() );

	$classes = array(
		'meta',
		'wpex-text-sm',
		'wpex-text-gray-600',
		'wpex-mt-10',
	);

	if ( $singular ) {
		$classes[] = 'wpex-mb-20';
	} else {
		$columns = wpex_get_grid_entry_columns();
		if ( '1' == $columns ) {
			$classes[] = 'wpex-mb-20';
		} else {
			$classes[] = 'wpex-mb-15';
		}
	}

	$classes[] = 'wpex-last-mr-0';

	if ( $singular ) {
		$classes = (array) apply_filters( 'wpex_cpt_single_meta_class', $classes );
	} else {
		$classes = (array) apply_filters( 'wpex_cpt_entry_meta_class', $classes );
	}

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Post Type Entry Excerpt Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_excerpt_class() {

	$class = array(
		'cpt-entry-excerpt',
		'entry-excerpt',
	);

	$columns = wpex_get_grid_entry_columns();

	if ( '1' == $columns ) {
		$class[] = 'wpex-my-20';
	} else {
		$class[] = 'wpex-my-15';
	}

	$class[] = 'wpex-last-mb-0';

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_cpt_entry_excerpt_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Entry Button Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_button_wrap_class() {

	$class = array(
		'cpt-entry-readmore-wrap',
		'entry-readmore-wrap',
	);

	$columns = wpex_get_grid_entry_columns();

	if ( '1' == $columns ) {
		$class[] = 'wpex-my-20';
	} else {
		$class[] = 'wpex-my-15';
	}

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_cpt_entry_button_wrap_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Entry Button Class.
 *
 * @since 5.0
 */
function wpex_cpt_entry_button_class() {

	$button_class = wpex_get_button_classes( apply_filters( 'wpex_' . get_post_type() . '_entry_button_args', array(
		'style' => '',
		'color' => '',
	) ) );

	if ( is_array( $button_class ) ) {
		$class = $button_class;
	} else {
		$class = explode( ' ', $button_class );
	}

	$class = (array) apply_filters( 'wpex_cpt_entry_button_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Entry Button Text.
 *
 * @since 5.0
 */
function wpex_cpt_entry_button_text() {
	$post_type = get_post_type();
	$text      = get_theme_mod( $post_type . '_readmore_text' );
	$text      = apply_filters( "wpex_{$post_type}_readmore_link_text", $text ); //@todo deprecate?
	$text      = $text ? $text : esc_html__( 'Read more', 'total' );
	echo wp_kses_post( apply_filters( 'wpex_cpt_entry_button_text', $text ) );
}

/**
 * Post Type Entry Thumbnail.
 *
 * @since 5.0
 */
function wpex_cpt_entry_thumbnail() {

	$post_type = get_post_type();

	$thumb_args = apply_filters( "wpex_{$post_type}_entry_thumbnail_args", array(
		'size'          => wpex_cpt_entry_thumbnail_size(),
		'schema_markup' => true,
		'class'         => 'cpt-entry-media-img wpex-align-middle',
	), $post_type );

	wpex_post_thumbnail( $thumb_args );

}

/**
 * Post Type Entry divider.
 *
 * @since 5.0
 */
function wpex_cpt_entry_divider() {

	$columns = wpex_get_grid_entry_columns();

	switch ( $columns ) {
		case '1':
			$divider = '<div class="cpt-entry-sep entry-sep wpex-divider wpex-my-40"></div>';
			break;
		default:
			$divider = '';
			break;
	}

	echo apply_filters( 'wpex_cpt_entry_sep', $divider );
}

/*-------------------------------------------------------------------------------*/
/* [ Single ]
/*-------------------------------------------------------------------------------*/

/**
 * Get Post Type single supported media types.
 *
 * @since 5.0
 */
function wpex_cpt_single_supported_media() {

	return (array) apply_filters( 'wpex_cpt_single_supported_media', array(
		'video',
		'audio',
		'gallery',
		'thumbnail',
	) );

}

/**
 * Get Post type single format.
 *
 * @since 5.0
 */
function wpex_cpt_single_media_type() {

	$supported_media = wpex_cpt_single_supported_media();

	if ( in_array( 'video', $supported_media ) && wpex_has_post_video() ) {
		$type = 'video';
	} elseif ( in_array( 'audio', $supported_media ) && wpex_has_post_audio() ) {
		$type = 'audio';
	} elseif ( in_array( 'gallery', $supported_media ) && wpex_has_post_gallery() ) {
		$type = 'gallery';
	} elseif ( in_array( 'thumbnail', $supported_media ) && has_post_thumbnail() ) {
		$type = 'thumbnail';
	} else {
		$type = ''; //important
	}

	return apply_filters( 'wpex_cpt_single_media_type', $type );

}

/**
 * Post Type single blocks class.
 *
 * @since 5.0
 */
function wpex_cpt_single_blocks_class() {

	$class = array(
		'wpex-first-mt-0',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_cpt_single_blocks_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type Single Thumbnail.
 *
 * @since 5.0
 */
function wpex_cpt_single_thumbnail() {

	$post_type = get_post_type();

	wpex_post_thumbnail( apply_filters( "wpex_{$post_type}_single_thumbnail_args", array(
		'size'          => $post_type . '_single',
		'schema_markup' => true,
		'class'         => 'cpt-single-media-img wpex-align-middle',
	), $post_type ) );

}

/**
 * Post Type single media class.
 *
 * @since 5.0
 */
function wpex_cpt_single_media_class() {

	$class = array(
		'single-media',
		'wpex-mb-20',
	);

	if ( 'above' == wpex_get_custom_post_media_position() ) {
		$class[] = 'wpex-md-mb-30';
	}

	$class = (array) apply_filters( 'wpex_cpt_single_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type single header class.
 *
 * @since 5.0
 */
function wpex_cpt_single_header_class() {

	$class = array(
		'single-header',
		'wpex-mb-10',
		'wpex-clr'
	);

	$class = (array) apply_filters( 'wpex_cpt_single_header_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type single title class.
 *
 * @since 5.0
 */
function wpex_cpt_single_title_class() {

	$class = array(
		'entry-title',
		'single-post-title',
		'wpex-text-3xl',
	);

	$class = (array) apply_filters( 'wpex_cpt_single_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type single content class.
 *
 * @since 5.0
 */
function wpex_cpt_single_content_class() {

	$class = array(
		'single-content',
		'wpex-mt-20',
		'entry',
	);

	if ( ! wpex_has_post_wpbakery_content( get_the_ID() ) ) {
		$class[] = 'wpex-mb-40';
	}

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_cpt_single_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Related ]
/*-------------------------------------------------------------------------------*/

/**
 * Post Type single related class.
 *
 * @since 5.0
 */
function wpex_cpt_single_related_class() {

	$class = array(
		'single-related',
		'related-posts',
		'wpex-mb-20',
	);

	if ( 'full-screen' == wpex_content_area_layout() ) {
		$class[] = 'container';
	}

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_cpt_single_related_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Post Type single related row class.
 *
 * @since 5.0
 */
function wpex_cpt_single_related_row_class() {

	$classes = array(
		'wpex-row',
		'wpex-clr'
	);

	$gap = wpex_get_ptu_type_mod( get_post_type(), 'related_gap' );

	if ( $gap ) {
		$classes[] = wpex_gap_class( $gap );
	}

	$classes = (array) apply_filters( 'wpex_cpt_single_related_row_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Return cpt single related query.
 *
 * @since 5.0
 */
function wpex_cpt_single_related_query() {
	$post_id = get_the_ID();
	$post_type = get_post_type();

	// Return if disabled via post meta
	if ( wpex_validate_boolean( get_post_meta( $post_id, 'wpex_disable_related_items', true ) ) ) {
		return false;
	}

	// Posts count
	$count = wpex_get_ptu_type_mod( $post_type, 'related_count' );
	if ( ! $count ) {
		$count = get_theme_mod( $post_type . '_related_count', '3' );
	}

	// Return if count is empty or 0
	if ( empty( $count ) || '0' == $count ) {
		return false;
	}

	$order = wpex_get_ptu_type_mod( $post_type, 'related_order' );
	$orderby = wpex_get_ptu_type_mod( $post_type, 'related_orderby' );

	// Related query arguments
	$args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $count,
		'order'          => $order ? $order : get_theme_mod( $post_type . '_related_order', 'desc' ),
		'orderby'        => $orderby ? $orderby : get_theme_mod( $post_type . '_related_orderby', 'date' ),
		'post__not_in'   => array( $post_id ),
		'no_found_rows'  => true,
	);

	// Related by taxonomy
	$same_cat = apply_filters( 'wpex_cpt_single_related_in_same_term', true ); // legacy filter
	if ( apply_filters( 'wpex_related_in_same_cat', $same_cat ) ) {

		// Add categories to query
		$related_taxonomy = wpex_get_ptu_type_mod( $post_type, 'related_taxonomy' );
		if ( empty( $related_taxonomy ) ) {
			$related_taxonomy = get_theme_mod( $post_type . '_related_taxonomy', wpex_get_post_type_cat_tax() );
		}

		// Generate related by taxonomy args
		if ( 'null' !== $related_taxonomy && taxonomy_exists( $related_taxonomy ) ) {

			$terms = array();

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

	// Apply filters to query args
	$args = (array) apply_filters( 'wpex_cpt_single_related_query_args', $args ); // legacy filter
	$args = (array) apply_filters( "wpex_related_{$post_type}_args", $args );

	if ( $args ) {
		return new wp_query( $args );
	}

}

/**
 * CPT single related entry class.
 *
 * @since 5.0
 */
function wpex_cpt_single_related_entry_class() {

	$classes = array(
		'related-post',
		'col'
	);

	$columns = wpex_cpt_single_related_columns();

	if ( $columns ) {
		$classes[] = wpex_grid_class( $columns );
	}

	$counter = wpex_get_loop_counter();

	if ( $counter ) {
		$classes[] = 'col-' . sanitize_html_class( $counter );
	}

	$classes[] = 'wpex-clr';

	$classes = (array) apply_filters( 'wpex_blog_single_related_entry_class', $classes );

	post_class( $classes );

}

/**
 * Return cpt single related columns.
 *
 * @since 5.0
 */
function wpex_cpt_single_related_columns() {
	$post_type = get_post_type();
	$columns = wpex_get_ptu_type_mod( $post_type, 'related_columns' );
	if ( ! $columns ) {
		$columns = get_theme_mod( $post_type . '_related_columns', 3 );
	}
	return apply_filters( 'wpex_cpt_single_related_columns', $columns );
}

/*-------------------------------------------------------------------------------*/
/* [ Cards ]
/*-------------------------------------------------------------------------------*/

/**
 * CPT entry card style.
 *
 * @since 5.0
 */
function wpex_cpt_entry_card_style() {

	$post_type = get_post_type();

	if ( 'related' === wpex_get_loop_instance() ) {
		$card_style = wpex_get_ptu_type_mod( $post_type, 'related_entry_card_style' );
		if ( ! $card_style ) {
			$card_style = get_theme_mod( $post_type . '_related_entry_card_style' );
		}
	} else {
		if ( is_post_type_archive() ) {
			$card_style = wpex_get_ptu_type_mod( $post_type, 'entry_card_style' );
		} elseif ( is_tax() ) {
			$term_meta_check = wpex_get_term_meta( '', 'wpex_entry_card_style', true );
			if ( ! empty( $term_meta_check ) ) {
				$card_style = $term_meta_check;
			} else {
				$card_style = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'entry_card_style' );
			}
		}
		if ( empty( $card_style ) ) {
			$card_style = get_theme_mod( $post_type . '_entry_card_style' );
		}
	}

	$card_style = apply_filters( "wpex_{$post_type}_entry_card_style", $card_style );

	return $card_style;

}

/**
 * CPT entry card.
 *
 * @since 5.0
 */
function wpex_cpt_entry_card() {

	$card_style = wpex_cpt_entry_card_style();

	if ( ! $card_style ) {
		return false; // !!! important !!!
	}

	$post_type = get_post_type();

	$args = array(
		'style'          => $card_style,
		'post_id'        => get_the_ID(),
		'thumbnail_size' => wpex_cpt_entry_thumbnail_size(),
		'excerpt_length' => wpex_cpt_entry_excerpt_length(),
	);

	if ( $overlay_style = wpex_cpt_entry_overlay_style() ) {
		$args['thumbnail_overlay_style'] = $overlay_style;
	}

	$args = apply_filters( "wpex_{$post_type}_entry_card_args", $args );

	wpex_card( $args );

	return true;

}