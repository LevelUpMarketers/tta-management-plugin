<?php
/**
 * Staff post type helper functions
 *
 * @package Total WordPress Theme
 * @subpackage Staff Functions
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*/

	# General
	# Archives
	# Entries
	# Single
	# Related
	# Social
	# Cards
	# Deprecated

/*-------------------------------------------------------------------------------*/
/* [ General ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if a staff member has a defined position.
 *
 * @since 5.0
 */
function wpex_has_staff_member_position( $post = '' ) {
	return (bool) wpex_get_staff_member_position( $post );
}

/**
 * Returns staff members position.
 *
 * @since 5.0
 */
function wpex_get_staff_member_position( $post = '' ) {

	$post = get_post( $post );

	if ( ! $post ) {
		return;
	}

	$position = get_post_meta( get_the_ID(), 'wpex_staff_position', true );

	return apply_filters( 'wpex_staff_member_position', $position, $post );

}

/*-------------------------------------------------------------------------------*/
/* [ Archives ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns staff archive grid style.
 *
 * @since 5.0
 */
function wpex_staff_archive_grid_style() {
	$style = ( $style = get_theme_mod( 'staff_archive_grid_style', 'fit-rows' ) ) ? $style : 'fit-rows';
	return apply_filters( 'wpex_staff_archive_grid_style', $style );
}

/**
 * Returns the staff loop top class.
 *
 * @since 5.0
 */
function wpex_staff_loop_top_class() {

	$classes = (array) apply_filters( 'wpex_staff_loop_top_class', wpex_get_staff_wrap_classes() );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Returns correct classes for the staff wrap.
 *
 * @since 1.5.3
 */
function wpex_get_staff_wrap_classes() {

	// Define main classes
	$classes = array(
		'wpex-row',
	);

	// Get grid style
	$grid_style = wpex_staff_archive_grid_style();

	// Masonry class
	if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
		$classes[] = 'wpex-masonry-grid';
		wpex_enqueue_isotope_scripts(); // This is a good spot to enqueue grid scripts
	}

	// Add grid style
	$classes[] = 'staff-' . sanitize_html_class( $grid_style );

	// Add gap
	if ( 'no-margins' === $grid_style ) {
		$classes[] = wpex_gap_class( '0px' );
	} elseif ( $gap = get_theme_mod( 'staff_archive_grid_gap' ) ) {
		$classes[] = wpex_gap_class( $gap );
	}

	// Add clearfix
	$classes[] = 'wpex-clr';

	// Sanitize
	$classes = array_map( 'esc_attr', $classes );

	// Apply filters
	$classes = (array) apply_filters( 'wpex_staff_wrap_classes', $classes );

	// Turn into space seperated string
	$classes = implode( ' ', $classes );

	// Return
	return $classes;

}

/**
 * Returns staff archive columns.
 *
 * @since 2.0.0
 */
function wpex_staff_archive_columns() {
	return get_theme_mod( 'staff_entry_columns', '3' );
}

/**
 * Returns correct classes for the staff grid.
 *
 * @since Total 1.5.2
 */
if ( ! function_exists( 'wpex_staff_column_class' ) ) {
	function wpex_staff_column_class( $query ) {
		if ( 'related' == $query ) {
			return wpex_grid_class( get_theme_mod( 'staff_related_columns', '3' ) );
		} else {
			return wpex_grid_class( get_theme_mod( 'staff_entry_columns', '3' ) );
		}
	}
}

/**
 * Checks if match heights are enabled for the staff.
 *
 * @since 1.5.3
 */
if ( ! function_exists( 'wpex_staff_match_height' ) ) {
	function wpex_staff_match_height() {
		$grid_style = get_theme_mod( 'staff_archive_grid_style', 'fit-rows' ) ? get_theme_mod( 'staff_archive_grid_style', 'fit-rows' ) : 'fit-rows';
		$columns    = get_theme_mod( 'staff_entry_columns', '4' ) ? get_theme_mod( 'staff_entry_columns', '4' ) : '4';
		if ( 'fit-rows' == $grid_style && get_theme_mod( 'staff_archive_grid_equal_heights' ) && $columns > '1' ) {
			return true;
		} else {
			return false;
		}
	}
}

/*-------------------------------------------------------------------------------*/
/* [ Entries ]
/*-------------------------------------------------------------------------------*/

/**
 * Get staff entry supported media types.
 *
 * @since 5.0
 */
function wpex_staff_entry_supported_media() {
	return (array) apply_filters( 'wpex_staff_entry_supported_media', array(
		'thumbnail',
	) );
}

/**
 * Get Staff entry media type.
 *
 * @since 5.0
 */
function wpex_staff_entry_media_type() {

	$supported_media = wpex_staff_entry_supported_media();

	if ( in_array( 'thumbnail', $supported_media ) && has_post_thumbnail() ) {
		$type = 'thumbnail';
	} else {
		$type = '';
	}

	return apply_filters( 'wpex_staff_entry_media_type', $type );

}

/**
 * Staff entry class.
 *
 * @since 5.0
 */
function wpex_staff_entry_class( $loop = '' ) {

	$loop = $loop ? $loop : wpex_get_loop_instance();

	// Default entry classes
	$class = array(
		'staff-entry',
	);

	// Add grid column class
	if ( $col_class = wpex_staff_column_class( $loop ) ) {
		$class[] = 'col';
		$class[] = $col_class;
	}

	// Add counter class
	$loop_counter = wpex_get_loop_counter();

	if ( $loop_counter ) {
		$class[] = 'col-' . sanitize_html_class( $loop_counter );
	}

	// Masonry Classes
	if ( 'archive' === $loop ) {

		$grid_style = wpex_staff_archive_grid_style();

		if ( 'masonry' === $grid_style || 'no-margins' === $grid_style ) {
			$class[] = 'wpex-masonry-col';
		}

	}

	// Apply filters
	$class = (array) apply_filters( 'wpex_staff_entry_class', $class );

	// Echo class attribute
	post_class( $class );

}


/**
 * Staff entry inner class.
 *
 * @since 5.0
 */
function wpex_staff_entry_inner_class( $loop = '' ) {

	$loop = $loop ? $loop : wpex_get_loop_instance();

	$class = array(
		'staff-entry-inner',
		'wpex-last-mb-0',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_staff_entry_inner_class', $class, $loop );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff entry media class.
 *
 * @since 5.0
 */
function wpex_staff_entry_media_class() {

	$media_type = wpex_staff_entry_media_type();

	$class = array(
		'staff-entry-media',
		'entry-media',
		'wpex-mb-20'
	);

	if ( 'thumbnail' === $media_type ) {

		if ( $overlay = wpex_overlay_classes() ) {
			$class[] = $overlay;
		}

		if ( $animation = wpex_get_entry_image_animation_classes() ) {
			$class[] = $animation;
		}

	}

	$class = (array) apply_filters( 'wpex_staff_entry_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff entry title class.
 *
 * @since 5.0
 */
function wpex_staff_entry_title_class() {

	$class = array(
		'staff-entry-title',
		'entry-title',
		'wpex-mb-5',
	);

	$class = (array) apply_filters( 'wpex_staff_entry_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}


/**
 * Staff entry content class.
 *
 * @since 5.0
 */
function wpex_staff_entry_content_class() {

	$class = array(
		'staff-entry-details',
	);

	if ( wpex_staff_match_height() ) {
		$class[] = 'match-height-content';
	}

	$class[] = 'wpex-last-mb-0';
	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_staff_entry_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff entry excerpt class.
 *
 * @since 5.0
 */
function wpex_staff_entry_excerpt_class() {

	$class = array(
		'staff-entry-excerpt',
		'wpex-my-15',
		'wpex-last-mb-0',
	);

	$class = (array) apply_filters( 'wpex_staff_entry_excerpt_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff entry position class.
 *
 * @since 5.0
 */
function wpex_staff_entry_position_class() {

	$class = array(
		'staff-entry-position',
		'entry-position',
		'wpex-mb-15',
		'wpex-text-sm',
		'wpex-text-gray-600',
	);

	$class = (array) apply_filters( 'wpex_staff_entry_position_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Echo staff entry thumbnail.
 *
 * @since 5.0
 */
function wpex_staff_entry_thumbnail() {
	echo wpex_get_staff_entry_thumbnail();
}

/**
 * Returns correct thumbnail HTML for the staff entries.
 *
 * @since 2.0.0
 */
function wpex_get_staff_entry_thumbnail( $loop = '', $args = array() ) {

	$loop = $loop ? $loop : wpex_get_loop_instance();

	$defaults = array(
		'size'  => ( 'archive' === $loop ) ? 'staff_entry' : 'staff_related',
		'class' => 'staff-entry-media-img staff-entry-img wpex-align-middle',
	);

	$args = wp_parse_args( $args, $defaults );

	return wpex_get_post_thumbnail( apply_filters( 'wpex_get_staff_entry_thumbnail_args', $args ) );

}

/**
 * Check if staff entry content is enabled.
 *
 * @since 5.0
 */
function wpex_has_staff_entry_content() {

	$loop = wpex_get_loop_instance();

	if ( 'related' === $loop ) {
		$bool = get_theme_mod( 'staff_related_excerpts', true );
	} else {
		$bool = get_theme_mod( 'staff_entry_details', true );
	}

	return (bool) apply_filters( 'wpex_has_staff_entry_content', $bool );

}

/**
 * Returns staff entry excerpt length.
 *
 * @since 5.0
 */
function wpex_staff_entry_excerpt_length() {

	$length = get_theme_mod( 'staff_entry_excerpt_length', 20 );

	if ( 'related' === wpex_get_loop_instance() ) {
		$length = get_theme_mod( 'staff_related_entry_excerpt_length', $length );
	}

	return apply_filters( 'wpex_staff_entry_excerpt_length', $length );

}

/*-------------------------------------------------------------------------------*/
/* [ Single ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns staff post blocks.
 *
 * @since 2.1.0
 */
function wpex_staff_single_blocks() {

	// Defaults
	$defaults = array( 'content', 'related' );

	// Get layout blocks
	$blocks = get_theme_mod( 'staff_post_composer' );

	// If blocks are 100% empty return defaults
	$blocks = $blocks ? $blocks : $defaults;

	// Convert blocks to array so we can loop through them
	if ( ! is_array( $blocks ) ) {
		$blocks = explode( ',', $blocks );
	}

	// Apply filters to entry layout blocks
	$blocks = apply_filters( 'wpex_staff_single_blocks', $blocks, 'front-end' );

	// Return blocks
	return $blocks;

}

/**
 * Get staff single supported media types.
 *
 * @since 5.0
 */
function wpex_staff_single_supported_media() {

	return (array) apply_filters( 'wpex_staff_single_supported_media', array(
		'gallery',
		'thumbnail',
	) );

}

/**
 * Get staff single format.
 *
 * @since 5.0
 */
function wpex_staff_single_media_type() {

	$supported_media = wpex_staff_single_supported_media();

	if ( in_array( 'gallery', $supported_media ) && wpex_has_post_gallery() ) {
		$type = 'gallery';
	} elseif ( in_array( 'thumbnail', $supported_media ) && has_post_thumbnail() ) {
		$type = 'thumbnail';
	} else {
		$type = ''; //important
	}

	return apply_filters( 'wpex_staff_single_media_type', $type );

}

/**
 * Returns staff single meta sections.
 *
 * @since 3.5.0
 */
function wpex_staff_single_meta_sections() {

	// Default sections
	$sections = array( 'date', 'categories' );

	// Apply filters for easy modification
	$sections = apply_filters( 'wpex_staff_single_meta_sections', $sections );

	// Turn into array if string
	if ( $sections && ! is_array( $sections ) ) {
		$sections = explode( ',', $sections );
	}

	// Return sections
	return $sections;

}

/**
 * Returns correct thumbnail HTML for the staff posts.
 *
 * @since 2.0.0
 */
function wpex_get_staff_post_thumbnail( $args = '' ) {

	// Define thumbnail args
	$defaults = array(
		'size'          => 'staff_post',
		'class'         => 'staff-single-media-img wpex-align-middle',
		'schema_markup' => true,
	);

	// Parse arguments
	$args = wp_parse_args( $args, $defaults );

	// Apply filters
	$args = apply_filters( 'wpex_get_staff_post_thumbnail_args', $args );

	// Return thumbanil
	return wpex_get_post_thumbnail( $args );

}

/**
 * Staff single blocks class.
 *
 * @since 5.0
 */
function wpex_staff_single_blocks_class() {

	$class = array(
		'wpex-first-mt-0',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_staff_single_blocks_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff single media class.
 *
 * @since 5.0
 */
function wpex_staff_single_media_class() {

	$class = array(
		'single-media',
		'wpex-mb-20',
	);

	$class = (array) apply_filters( 'wpex_staff_single_media_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff single header class.
 *
 * @since 5.0
 */
function wpex_staff_single_header_class() {

	$class = array(
		'single-header',
		'wpex-mb-20',
	);

	$class = (array) apply_filters( 'wpex_staff_single_header_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff single title class.
 *
 * @since 5.0
 */
function wpex_has_staff_single_title_position() {
	$check = false;
	if ( get_theme_mod( 'staff_single_header_position', true ) && wpex_has_staff_member_position() ) {
		$check = true;
	}
	return (bool) apply_filters( 'wpex_has_staff_single_title_position', $check );
}

/**
 * Staff single title class.
 *
 * @since 5.0
 */
function wpex_staff_single_title_class() {

	$class = array(
		'single-post-title',
		'entry-title',
		'wpex-text-3xl',
	);

	if ( wpex_has_staff_single_title_position() ) {
		$class[] = 'wpex-m-0';
	}

	$class = (array) apply_filters( 'wpex_staff_single_title_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff Single Meta Class.
 *
 * @since 5.0
 */
function wpex_staff_single_meta_class() {

	$class = array(
		'meta',
		'wpex-text-sm',
		'wpex-text-gray-600',
		'wpex-mb-20',
		'wpex-last-mr-0',
	);

	$class = (array) apply_filters( 'wpex_staff_single_meta_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/**
 * Staff single position class.
 *
 * @since 5.0
 */
function wpex_staff_single_position_class() {

	$class = array(
		'single-staff-position',
		'wpex-text-md',
		'wpex-text-gray-500',
	);

	$class = (array) apply_filters( 'wpex_staff_single_position_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff single content class.
 *
 * @since 5.0
 */
function wpex_staff_single_content_class() {

	$class = array(
		'single-content',
		'wpex-mt-20',
		'entry',
	);

	if ( ! wpex_has_post_wpbakery_content( get_the_ID() ) ) {
		$class[] = 'wpex-mb-40';
	}

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_staff_single_content_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff single comments class.
 *
 * @since 5.0
 */
function wpex_staff_single_comments_class() {

	$class = array(
		'single-comments',
		'wpex-mb-40',
		'wpex-clr',
	);

	$class = (array) apply_filters( 'wpex_staff_single_comments_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Related ]
/*-------------------------------------------------------------------------------*/

/**
 * Return staff single related query.
 *
 * @since 5.0
 */
function wpex_staff_single_related_query() {
	$post_id = get_the_ID();

	// Return if disabled via post meta
	if ( wpex_validate_boolean( get_post_meta( $post_id, 'wpex_disable_related_items', true ) ) ) {
		return false;
	}

	// Posts count
	$posts_count = get_theme_mod( 'staff_related_count', '3' );

	// Return if count is empty or 0
	if ( empty( $posts_count ) || '0' == $posts_count ) {
		return false;
	}

	// Related query arguments
	$args = array(
		'post_type'      => 'staff',
		'posts_per_page' => $posts_count,
		'order'          => get_theme_mod( 'staff_related_order', 'desc' ),
		'orderby'        => get_theme_mod( 'staff_related_orderby', 'date' ),
		'post__not_in'   => array( $post_id ),
		'no_found_rows'  => true,
	);

	// Related by taxonomy
	if ( apply_filters( 'wpex_related_in_same_cat', true ) ) {

		// Add categories to query
		$related_taxonomy = get_theme_mod( 'staff_related_taxonomy', 'staff_category' );

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

	// Apply filters to query args
	$args = (array) apply_filters( 'wpex_related_staff_args', $args );

	if ( $args ) {
		return new wp_query( $args );
	}

}

/**
 * Display staff single related heading.
 *
 * @since 5.0
 */
function wpex_staff_single_related_heading() {
	// Get and translate heading text
	$heading = wpex_get_translated_theme_mod( 'staff_related_title' );
	$heading = $heading ? $heading : esc_html__( 'Related Staff', 'total' );

	// Display heading text isn't empty
	if ( $heading ) {

		wpex_heading( array(
			'content'		=> $heading,
			'classes'		=> array( 'related-staff-posts-heading' ),
			'apply_filters'	=> 'staff_related',
		) );

	}
}

/**
 * Staff single related class.
 *
 * @since 5.0
 */
function wpex_staff_single_related_class() {

	$class = array(
		'related-staff-posts',
		'wpex-mb-20',
	);

	if ( 'full-screen' == wpex_content_area_layout() ) {
		$class[] = 'container';
	}

	$class[] = 'wpex-clr';

	$class = (array) apply_filters( 'wpex_staff_single_related_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $class ) ) ) . '"';
	}

}

/**
 * Staff single related row class.
 *
 * @since 5.0
 * @todo deprecate wpex_related_staff_row_classes filter
 */
function wpex_staff_single_related_row_class() {

	$classes = array(
		'wpex-row',
		'wpex-clr'
	);

	if ( $gap = get_theme_mod( 'staff_related_gap' ) ) {
		$classes[] = wpex_gap_class( $gap );
	}

	$classes = (array) apply_filters( 'wpex_staff_single_related_row_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( apply_filters( 'wpex_related_staff_row_classes', implode( ' ', $classes ) ) ) . '"';
	}

}



/*-------------------------------------------------------------------------------*/
/* [ Social ]
/*-------------------------------------------------------------------------------*/

/**
 * Outputs the staff social options.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wpex_get_staff_social' ) ) {
	function wpex_get_staff_social( $atts = NULL ) {

		// Extract staff social args
		extract( shortcode_atts( array(
			'before'        => '',
			'after'         => '',
			'post_id'       => '',
			'font_size'     => '',
			'css_animation' => '',
			'css'           => '',
			'inline_style'  => '',
			'icon_margin'   => '',
			'spacing'       => '5',
			'show_icons'    => wp_validate_boolean( get_theme_mod( 'staff_social_show_icons', true ) ),
			'style'         => get_theme_mod( 'staff_social_default_style', 'minimal-round' ),
			'link_target'   => get_theme_mod( 'staff_social_link_target', 'blank' ),
		), $atts, 'staff_social' ) );

		// Define output
		$output = '';

		// Get social profiles array
		$profiles = wpex_staff_social_array();

		// Define post_id
		$post_id = $post_id ? $post_id : get_the_ID();

		// Convert icon margin to style
		if ( $icon_margin && function_exists( 'vcex_inline_style' ) ) {
			$icon_margin = vcex_inline_style( array(
				'margin' => $icon_margin,
			), false );
		}

		// Parse style to return correct classname
		if ( $show_icons ) {
			$style_class = wpex_get_social_button_class( $style );
		} else {
			$style_class = '';
		}

		// Wrap classes
		$wrap_classes = array(
			'staff-social',
			'wpex-social-btns',
			'wpex-mt-10',
			'wpex-last-mr-0',
		);

		if ( $css ) {
			$wrap_classes[] = vc_shortcode_custom_css_class( $css );
		}
		if ( $css_animation && 'none' != $css_animation && function_exists( 'vcex_get_css_animation' ) ) {
			$wrap_classes[] = vcex_get_css_animation( $css_animation );
		}

		// Font size
		if ( $font_size ) {
			$font_size = wpex_sanitize_data( $font_size, 'font_size' );
			if ( $font_size ) {
				$inline_style .= 'font-size:' . esc_attr( $font_size ) . ';';
			}
		}

		// Wrap attributes
		$wrap_attrs = array(
			'class' => array_map( 'esc_attr', $wrap_classes ),
			'style' => $inline_style,
		);

		// Before output
		if ( $before ) {
			$output .= $before;
		}

		// Start output
		$output .= '<div '. wpex_parse_attrs( $wrap_attrs ) .'>';

			// Loop through social options
			foreach ( $profiles as $profile ) :

				// Get meta
				$meta = $profile['meta'];

				// Display link if one exists
				if ( $meta_value = get_post_meta( $post_id, $meta, true ) ) :

					// Add "mailto" for emails
					if ( 'wpex_staff_email' == $meta && is_email( $meta_value ) ) {
						$url = 'mailto:' . $meta_value;
					}

					// Add "callto" to skype
					elseif ( 'wpex_staff_skype' == $meta ) {
						if ( strpos( $meta_value, 'skype' ) === false ) {
							$url = str_replace( 'callto:', '', $meta_value );
							$url = 'callto:' . $url;
						}
					}

					// Add "tel" for phones
					elseif ( 'wpex_staff_phone_number' === $meta ) {
						if ( strpos( $meta_value, 'callto' ) === false ) {
							$url = str_replace( 'tel:', '', $meta_value );
							$url = 'tel:' . $url;
						}
					} else {
						$url = esc_url( $meta_value );
					}

					// Link classes
					$link_classes = array(
						'wpex-' . sanitize_html_class( str_replace( '_', '-', $profile['key'] ) ),
						$style_class,
						'wpex-mr-' . absint( $spacing ),
						'wpex-mt-' . absint( $spacing ),
					);

					if ( ! $show_icons ) {
						$link_classes[] = 'wpex-inline-block';
					}

					// Link attributes
					$link_attrs  = array(
						'href'   => $url,
						'class'  => $link_classes,
						'target' => ( 'wpex_staff_email' == $meta ) ? '' : $link_target,
						'style'  => $icon_margin,
					);

					$output .= '<a '. wpex_parse_attrs( $link_attrs ) .'>';

						if ( $show_icons ) {

							$output .= '<span class="' . esc_attr( $profile['icon_class'] ) . '" aria-hidden="true"></span>';

							$output .= '<span class="screen-reader-text">' . esc_html( $profile['label'] ) . '</span>';

						} else {

							if ( in_array( $profile['key'], array(
									'telephone',
									'fax',
									'phone_number',
									'phone',
									'email',
									'website'
								) )
							) {

								$output .= esc_html( $meta_value );

							} else {

								$output .= esc_html( $profile['label'] );

							}

						}


					$output .= '</a>';

				endif; // URL check

			endforeach; // End profiles loop

		// End output
		$output .= '</div>';

		// After output
		if ( $after ) {
			$output .= $after;
		}

		// Return output
		return $output;

	}

}

/*-------------------------------------------------------------------------------*/
/* [ Cards ]
/*-------------------------------------------------------------------------------*/

/**
 * Staff Card Entry.
 *
 * @since 5.0
 */
function wpex_staff_entry_card() {

	$instance = wpex_get_loop_instance();

	if ( 'related' === $instance ) {
		$card_style = get_theme_mod( 'staff_related_entry_card_style' );
	} else {
		$term_meta_check = wpex_get_term_meta( '', 'wpex_entry_card_style', true );
		if ( ! empty( $term_meta_check ) ) {
			$card_style = $term_meta_check;
		} else {
			$card_style = get_theme_mod( 'staff_entry_card_style' );
		}
	}

	$card_style = apply_filters( 'wpex_staff_entry_card_style', $card_style );

	if ( ! $card_style ) {
		return false;
	}

	$args = array(
		'style'          => $card_style,
		'post_id'        => get_the_ID(),
		'thumbnail_size' => ( 'related' === $instance ) ? 'staff_related' : 'staff_entry',
		'excerpt_length' => wpex_staff_entry_excerpt_length(),
	);

	if ( $overlay_style = wpex_overlay_style() ) {
		$args['thumbnail_overlay_style'] = $overlay_style;
	}

	$args = apply_filters( 'wpex_staff_entry_card_args', $args );

	wpex_card( $args );

	return true;

}

/*-------------------------------------------------------------------------------*/
/* [ Deprecated ]
/*-------------------------------------------------------------------------------*/

/**
 * Staff Overlay.
 *
 * Function is deprecated and no longer used?
 *
 * @since 1.0.0
 * @todo deprecate
 */
if ( ! function_exists( 'wpex_get_staff_overlay' ) ) {
	function wpex_get_staff_overlay( $id = NULL ) {
		$post_id  = $id ? $id : get_the_ID();
		$position = wpex_get_staff_member_position( $post_id );
		if ( ! $position ) {
			return;
		} ?>
		<div class="staff-entry-position"><span><?php echo esc_html( $position ); ?></span></div>
		<?php
	}
}