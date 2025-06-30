<?php
/**
 * Helper functions for the blog
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*

	# Archives
	# Entry
	# Single
	# Slider
	# Related
	# Cards
	# Deprecated

/*-------------------------------------------------------------------------------*/
/* [ Archives ]
/*-------------------------------------------------------------------------------*/

/**
 * Exclude categories from the blog.
 * This function runs on pre_get_posts
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wpex_blog_exclude_categories' ) ) {
	function wpex_blog_exclude_categories( $deprecated = true ) {
		$cats = get_theme_mod( 'blog_cats_exclude' );
		if ( $cats && ! is_array( $cats ) ) {
			$cats = explode( ',', $cats ); // Convert to array
		}
		return $cats;
	}
}

/**
 * Returns the correct blog style.
 *
 * @since 1.5.3
 * @deprecated 5.0
 */
function wpex_blog_style() {
	return wpex_blog_entry_style();
}

/**
 * Returns the grid style.
 *
 * @since 1.5.3
 */
function wpex_blog_grid_style() {

	// Get default style from Customizer
	$style = get_theme_mod( 'blog_grid_style' );

	// Check custom category style
	$cat_meta = wpex_get_category_meta( '', 'wpex_term_grid_style' );
	if ( $cat_meta ) {
		$style = $cat_meta;
	}

	// Sanitize
	$style = $style ? $style : 'fit-rows';

	// Apply filters for child theming
	$style = apply_filters( 'wpex_blog_grid_style', $style );

	// Return style
	return $style;

}

/**
 * Checks if it's a fit-rows style grid.
 *
 * @since 1.5.3
 * @deprecated
 */
function wpex_blog_fit_rows() {

	// Return false by default
	$return = false;

	// Get current blog style
	if ( 'grid-entry-style' == wpex_blog_entry_style() ) {
		$return = true;
	} else {
		$return = false;
	}

	// Apply filters for child theming
	$return = apply_filters( 'wpex_blog_fit_rows', $return );

	// Return bool
	return $return;

}

/**
 * Returns the correct pagination style.
 *
 * @since 1.5.3
 */
function wpex_blog_pagination_style() {

	// Get default style from Customizer
	$style = get_theme_mod( 'blog_pagination_style' );

	// Check custom category style
	if ( is_category() ) {
		$cat_meta = wpex_get_category_meta( '', 'wpex_term_pagination' );
		if ( $cat_meta ) {
			$style = $cat_meta;
		}
	}

	// Apply filters for child theming
	$style = apply_filters( 'wpex_blog_pagination_style', $style );

	// Return style
	return $style;
}

/**
 * Get blog wrap classes.
 *
 * @since 4.9.8
 */
function wpex_get_blog_wrap_classes( $classes = NULL ) {

	if ( $classes ) {
		return $classes;
	}

	// Default classes
	$classes = array(
		'entries',
	);

	// Get blog style
	$style = wpex_blog_entry_style();

	// Grid classes
	if ( $style == 'grid-entry-style' || 'wpex_card' == $style ) {

		$classes[] = 'wpex-row';

		if ( 'masonry' == wpex_blog_grid_style() ) {
			$classes[] = 'wpex-masonry-grid';
			$classes[] = 'blog-masonry-grid';
			wpex_enqueue_isotope_scripts(); // good place to load masonry scripts
		} else {
			$classes[] = 'blog-grid';
		}

		$cat_meta_gap = wpex_get_category_meta( '', 'wpex_term_grid_gap' );
		if ( $cat_meta_gap ) {
			$gap = $cat_meta_gap;
		} else {
			$gap = get_theme_mod( 'blog_grid_gap' );
		}

		if ( $gap ) {
			$classes[] = 'gap-' . sanitize_html_class( $gap );
		}

	}

	// Left thumbs
	if ( 'thumbnail-entry-style' == $style ) {
		$classes[] = 'left-thumbs';
	}

	// Add some margin when author is enabled
	if ( $style == 'grid-entry-style' && get_theme_mod( 'blog_entry_author_avatar' ) ) {
		$classes[] = 'grid-w-avatars';
	}

	// Equal heights
	if ( wpex_blog_entry_equal_heights() ) {
		$classes[] = 'blog-equal-heights';
	}

	// Infinite scroll classes
	if ( 'infinite_scroll' == wpex_blog_pagination_style() ) {
		$classes[] = 'infinite-scroll-wrap';
	}

	// Clearfix
	$classes[] = 'wpex-clr';

	// Sanitize
	$classes = array_map( 'esc_attr', $classes );

	// Add filter for child theming
	$classes = apply_filters( 'wpex_blog_wrap_classes', $classes );

	// Turn classes into space seperated string
	if ( is_array( $classes ) ) {
		$classes = implode( ' ', $classes );
	}

	// Return classes as string
	return $classes;

}


/**
 * Adds main classes to blog post entries
 *
 * @since 1.1.6
 */
function wpex_blog_wrap_classes( $classes = NULL ) {
	echo wpex_get_blog_wrap_classes();
}

/*-------------------------------------------------------------------------------*/
/* [ Entries ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns correct blog entry style.
 *
 * @since 1.5.3
 */
function wpex_blog_entry_style() {

	if ( wpex_blog_entry_card_style() ) {
		return 'wpex_card';
	}

	// Get default style from Customizer
	$style = get_theme_mod( 'blog_style' );

	// Category meta check
	$cat_meta = wpex_get_category_meta( '', 'wpex_term_style' );
	if ( $cat_meta ) {
		$style = $cat_meta . '-entry-style';
	}

	// Sanitize
	$style = $style ? $style : 'large-image-entry-style';

	// Apply filters for child theming
	$style = apply_filters( 'wpex_blog_style', $style ); // Legacy @deprecated 5.0
	$style = apply_filters( 'wpex_blog_entry_style', $style );

	// Return style
	return $style;

}

/**
 * Blog Entry media type.
 *
 * @since 5.0
 */
function wpex_blog_entry_media_type() {

	$format = get_post_format();

	$type = 'thumbnail';

	switch ( $format ) {

		case 'video':

			if ( ! post_password_required()
				&& get_theme_mod( 'blog_entry_video_output', true )
				&& wpex_has_post_video()
			) {
				$type = 'video';
			}

			break;

		case 'audio':

			if ( apply_filters( 'wpex_blog_entry_audio_embed', get_theme_mod( 'blog_entry_audio_output', false ) )
				&& ! post_password_required()
				&& wpex_has_post_audio()
			) {
				$type = 'audio';
			}

			break;

		case 'gallery':

			if ( wpex_blog_entry_slider_enabled() && wpex_has_post_gallery() ) {
				$type = 'gallery';
			}

			break;

		case 'link':

			if ( wpex_has_post_redirection() ) {
				$type = 'link';
			}

			break;

	}

	// Check for thumbnail existense so we don't end up with empty media div
	if ( ( 'thumbnail' == $type || 'link' == $type ) && ! has_post_thumbnail() ) {
		$type = '';
	}

	return apply_filters( 'wpex_blog_entry_media_type', $type );

}

/**
 * Check if blog entry has an avatar enabled.
 *
 * @since 5.0
 */
function wpex_has_blog_entry_avatar() {
	return apply_filters( 'wpex_has_blog_entry_avatar', get_theme_mod( 'blog_entry_author_avatar', false ) );
}

/**
 * Blog entry divider.
 *
 * @since 5.0
 */
function wpex_blog_entry_divider() {
	$divider = '';

	$entry_style = wpex_blog_entry_style();

	switch ( $entry_style ) {
		case 'large-image-entry-style':
			$divider = 'wpex-divider wpex-my-40';
			break;
		case 'thumbnail-entry-style':
			$divider = 'wpex-divider wpex-my-30';
			break;
	}

	if ( $divider ) {
		$divider = '<div class="entry-divider ' . esc_attr( $divider ) . '"></div>';
	}

	echo apply_filters( 'wpex_blog_entry_divider', $divider );
}

/**
 * Checks if the blog entries should have equal heights.
 *
 * @since   2.0.0
 * @return  bool
 */
function wpex_blog_entry_equal_heights() {
	if ( ! get_theme_mod( 'blog_archive_grid_equal_heights', false ) ) {
		return false;
	}
	$entry_style = wpex_blog_entry_style();
	if ( 'grid-entry-style' == $entry_style && 'masonry' != $entry_style ) {
		return true;
	}
}

/**
 * Returns correct columns for the blog entries.
 *
 * @since 1.5.3
 */
function wpex_blog_entry_columns( $entry_style = '' ) {

	$entry_style = $entry_style ? $entry_style : wpex_blog_entry_style();

	if ( ! in_array( $entry_style, array( 'grid-entry-style', 'wpex_card' ) ) ) {
		return 1; // always 1 unless it's a grid
	}

	// Get columns from customizer setting
	$columns = get_theme_mod( 'blog_grid_columns', '2' );

	// Category meta check
	$cat_meta = wpex_get_category_meta( '', 'wpex_term_grid_cols' );
	if ( $cat_meta ) {
		$columns = $cat_meta;
	}

	// Set default columns to 2 if a value isn't set
	if ( is_array( $columns ) ) {
		if ( empty( $columns['d'] ) ) {
			$columns['d'] = '2';
		}
	} else {
		$columns = $columns ? $columns : '2';
	}

	// Apply filters for child theming
	$columns = apply_filters( 'wpex_blog_entry_columns', $columns );

	// Return columns
	return $columns;

}

/**
 * Blog Entry Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_class() {

	$classes = (array) apply_filters( 'wpex_blog_entry_class', wpex_blog_entry_classes() );

	post_class( $classes );

}

/**
 * Returns correct blog entry classes.
 *
 * @since 1.1.6
 */
function wpex_blog_entry_classes() {

	// Get entry
	$entry_style = wpex_blog_entry_style();

	// Define classes array
	$classes = array();

	// Core classes
	$classes[] = 'blog-entry';

	// Masonry classes
	if ( 'masonry' == wpex_blog_grid_style() ) {
		$classes[] = 'wpex-masonry-col';
	}

	// Add columns for grid style entries
	if ( 'grid-entry-style' == $entry_style || 'wpex_card' == $entry_style ) {

		$grid_class = wpex_grid_class( wpex_blog_entry_columns( $entry_style ) );

		if ( $grid_class ) {
			$classes[] = 'col';
			$classes[] = $grid_class;
		}

		$counter = wpex_get_loop_counter();

		if ( $counter ) {
			$classes[] = 'col-' . sanitize_html_class( $counter );
		}

	}

	// Blog entry style
	if ( 'wpex_card' !== $entry_style ) {
		$classes[] = sanitize_html_class( $entry_style );
	}

	// Avatar
	if ( $avatar_enabled = get_theme_mod( 'blog_entry_author_avatar' ) ) {
		$classes[] = 'entry-has-avatar';
	}

	// Add utility Classes
	$classes[] = 'wpex-relative';

	// Clear floats
	$classes[] = 'wpex-clr';

	// Sanitize
	$classes = array_map( 'esc_attr', $classes );

	// Apply filters to entry post class for child theming
	$classes = apply_filters( 'wpex_blog_entry_classes', $classes );

	// Rturn classes array
	return $classes;
}

/**
 * Blog Entry Inner Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_inner_class() {

	$classes = array(
		'blog-entry-inner',
		'entry-inner',
	);

	$entry_style = wpex_blog_entry_style();

	if ( 'grid-entry-style' == $entry_style ) {
		$classes[] = 'wpex-px-20';
		$classes[] = 'wpex-pb-20';
		$classes[] = 'wpex-border';
		$classes[] = 'wpex-border-solid';
		$classes[] = 'wpex-border-main';
	}

	$classes[] = 'wpex-last-mb-0';

	$classes[] = 'wpex-clr';

	$classes = (array) apply_filters( 'wpex_blog_entry_inner_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Quote Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_quote_class() {

	$classes = array(
		'post-quote-entry-inner',
		'wpex-boxed',
		'wpex-relative',
		'wpex-z-5',
		'wpex-text-lg',
		'wpex-italic',
		'wpex-last-mb-0',
	);

	if ( is_singular() ) {
		$classes[] = 'wpex-mb-40';
	}

	$classes[] = 'wpex-clr';

	$classes = (array) apply_filters( 'wpex_blog_entry_quote_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Quote Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_quote_icon_class() {

	$classes = array(
		'ticon',
		is_rtl() ? 'ticon-quote-left' : 'ticon-quote-right',
		'wpex-text-7xl',
		'wpex-opacity-10',
		'wpex-absolute',
		'wpex-right-0',
		'wpex-bottom-0',
		'wpex-mb-20',
		'wpex-mr-20',
		'wpex-z-1',
	);

	$classes = (array) apply_filters( 'wpex_blog_entry_quote_icon_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry overlay style.
 *
 * @since 5.0
 */
function wpex_blog_entry_overlay_style() {
	if ( 'related' === wpex_get_loop_instance() ) {
		$overlay_style = get_theme_mod( 'blog_related_overlay' );
	} else {
		$overlay_style = get_theme_mod( 'blog_entry_overlay' );
	}
	$overlay_style = apply_filters( 'wpex_blog_entry_overlay_style', $overlay_style );
	if ( ! $overlay_style ) {
		$overlay_style = 'none'; // !important
	}
	return $overlay_style;
}

/**
 * Blog Entry Media Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_media_class() {

	$entry_style = wpex_blog_entry_style();

	$classes = array(
		'blog-entry-media',
		'entry-media',
	);

	// Style specific styles
	if ( 'thumbnail-entry-style' !== $entry_style ) {
		$classes[] = 'wpex-mb-20';
	}

	if ( 'grid-entry-style' === $entry_style ) {
		$classes[] = '-wpex-mx-20';
	}

	// Media type
	$media_type = wpex_blog_entry_media_type();

	// Add overlay styles only if this is a thumbnail style entry
	if ( 'thumbnail' === $media_type || 'link' === $media_type ) {

		$overlay = wpex_blog_entry_overlay_style();

		if ( $overlay ) {

			$overlay_classes = wpex_overlay_classes( $overlay );

			if ( $overlay_classes ) {
				$classes[] = $overlay_classes;
			}

		}

		if ( $animation_classes = wpex_get_entry_image_animation_classes() ) {
			$classes[] = $animation_classes;
		}

	}

	$classes = (array) apply_filters( 'wpex_blog_entry_media_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Header Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_header_class() {

	$classes = array(
		'blog-entry-header',
		'entry-header',
	);

	if ( wpex_has_blog_entry_avatar() ) {
		$classes[] = 'wpex-flex';
		$classes[] = 'wpex-items-center';
	}

	$entry_style = wpex_blog_entry_style();

	switch ( $entry_style ) {

		case 'grid-entry-style':
			if ( ! wpex_post_has_media() ) {
				$classes[] = 'wpex-mt-20'; // prevent issues when there isn't any media
			}
			$classes[] = 'wpex-mb-10';
			break;

		default:
			$classes[] = 'wpex-mb-10';
			break;

	}

	$classes = (array) apply_filters( 'wpex_blog_entry_header_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Title Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_title_class() {

	$classes = array(
		'blog-entry-title',
		'entry-title',
	);

	if ( wpex_has_blog_entry_avatar() ) {
		$classes[] = 'wpex-flex-grow';
	}

	$entry_style = wpex_blog_entry_style();

	switch ( $entry_style ) {

		case 'grid-entry-style':
			$classes[] = 'wpex-text-lg';
			break;

		case 'thumbnail-entry-style':
			$classes[] = 'wpex-text-2xl';
			break;

		default:
			$classes[] = 'wpex-text-3xl';
			break;

	}

	$classes = (array) apply_filters( 'wpex_blog_entry_title_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Avatar Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_avatar_class() {

	$classes = array(
		'blog-entry-author-avatar',
		'wpex-flex-shrink-0',
		'wpex-mr-20',
		'wpex-mb-5',
	);

	$entry_style = wpex_blog_entry_style();

	$classes = (array) apply_filters( 'wpex_blog_entry_avatar_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Meta Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_meta_class() {

	$classes = array(
		'blog-entry-meta',
		'entry-meta',
		'meta',
		'wpex-text-sm',
		'wpex-text-gray-600',
		'wpex-last-mr-0',
	);

	$entry_style = wpex_blog_entry_style();

	switch ( $entry_style ) {
		case 'grid-entry-style':
			$classes[] = 'wpex-mb-15';
			break;
		default:
			$classes[] = 'wpex-mb-20';
			break;
	}

	$classes = (array) apply_filters( 'wpex_blog_entry_meta_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Content Class | used for left/right layouts.
 *
 * @since 5.0
 */
function wpex_blog_entry_content_class() {

	$classes = array(
		'blog-entry-content',
		'entry-details',
		'wpex-last-mb-0',
		'wpex-clr',
	);

	$classes = (array) apply_filters( 'wpex_blog_entry_content_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Excerpt Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_excerpt_class() {

	$classes = array(
		'blog-entry-excerpt',
		'entry-excerpt',
	);

	$entry_style = wpex_blog_entry_style();

	switch ( $entry_style ) {
		case 'grid-entry-style':
			$classes[] = 'wpex-my-15';
			break;
		default:
			$classes[] = 'wpex-my-20';
			break;
	}

	$classes[] = 'wpex-last-mb-0';

	$classes[] = 'wpex-clr';

	$classes = (array) apply_filters( 'wpex_blog_entry_excerpt_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Button Wrap Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_button_wrap_class() {

	$classes = array(
		'blog-entry-readmore',
		'entry-readmore-wrap',
	);

	$entry_style = wpex_blog_entry_style();

	switch ( $entry_style ) {
		case 'grid-entry-style':
			$classes[] = 'wpex-my-15';
			break;
		default:
			$classes[] = 'wpex-my-20';
			break;
	}

	$classes[] = 'wpex-clr';

	$classes = (array) apply_filters( 'wpex_blog_entry_button_wrap_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Entry Button Class.
 *
 * @since 5.0
 */
function wpex_blog_entry_button_class() {

	$button_class = wpex_get_button_classes( apply_filters( 'wpex_blog_entry_button_args', array(
		'style' => '',
		'color' => '',
	) ) );

	if ( is_array( $button_class ) ) {
		$classes = $button_class;
	} else {
		$classes = explode( ' ', $button_class );
	}

	$classes = (array) apply_filters( 'wpex_blog_entry_button_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Returns the blog entry thumbnail.
 *
 * @since 1.0.0
 */
function wpex_blog_entry_thumbnail( $args = '' ) {
	echo wpex_get_blog_entry_thumbnail( $args );
}

/**
 * Returns the blog entry thumbnail args.
 *
 * @since 1.0.0
 */
function wpex_get_blog_entry_thumbnail_args( $args = '' ) {

	// If args isn't array then it's the attachment
	if ( $args && ! is_array( $args ) ) {
		$args = array(
			'attachment' => $args,
		);
	}

	// Define thumbnail args
	$defaults = array(
		'attachment'    => get_post_thumbnail_id(),
		'size'          => 'blog_entry',
		'class'         => 'blog-entry-media-img wpex-align-middle',
		'apply_filters' => 'wpex_blog_entry_thumbnail_args',
	);

	// Parse arguments
	$args = wp_parse_args( $args, $defaults );

	// Check category image width meta
	$cat_meta_image_width = wpex_get_category_meta( '', 'wpex_term_image_width' );
	if ( $cat_meta_image_width ) {
		$args['size']  = 'wpex_custom';
		$args['width'] = $cat_meta_image_width;
	}

	// Check category image height meta
	$cat_meta_image_height = wpex_get_category_meta( '', 'wpex_term_image_height' );
	if ( $cat_meta_image_height ) {
		$args['size']  = 'wpex_custom';
		$args['height'] = $cat_meta_image_height;
	}

	// Return thumbnail args
	return $args;
}

/**
 * Returns the blog entry thumbnail.
 *
 * @since 1.0.0
 */
function wpex_get_blog_entry_thumbnail( $args = '' ) {
	return apply_filters( 'wpex_blog_entry_thumbnail', wpex_get_post_thumbnail( wpex_get_blog_entry_thumbnail_args( $args ) ) );
}

/**
 * Returns blog entry blocks.
 *
 * @since 2.0.0
 * @todo  rename to 'wpex_blog_entry_blocks' for consistency
 */
function wpex_blog_entry_layout_blocks() {

	// Get layout blocks
	$blocks = get_theme_mod( 'blog_entry_composer' );

	// If blocks are 100% empty return defaults
	$blocks = $blocks ? $blocks : 'featured_media,title,meta,excerpt_content,readmore';

	// Convert blocks to array so we can loop through them
	if ( ! is_array( $blocks ) ) {
		$blocks = explode( ',', $blocks );
	}

	// Set block keys equal to vals
	$blocks = $blocks ? array_combine( $blocks, $blocks ) : array();

	// Apply filters to entry layout blocks after they are turned into an array
	$blocks = apply_filters( 'wpex_blog_entry_layout_blocks', $blocks, 'front-end' );

	// Return blocks
	return $blocks;

}

/**
 * Returns blog entry meta sections.
 *
 * @since 2.0.0
 */
function wpex_blog_entry_meta_sections() {

	// Default sections
	$sections = array(
		'date',
		'author',
		'categories',
		'comments',
	);

	// Get Sections from Customizer
	$sections = get_theme_mod( 'blog_entry_meta_sections', $sections );

	// Turn into array if string
	if ( ! is_array( $sections ) ) {
		$sections = explode( ',', $sections );
	}

	// Array tweaks
	if ( $sections && is_array( $sections ) ) {

		// Set keys equal to values for easier modification
		$sections = array_combine( $sections, $sections );

		// Remove comments for link format
		if ( isset( $sections['comments'] ) && 'link' == get_post_format() ) {
			unset( $sections['comments'] );
		}

	}

	// Apply filters and return
	return apply_filters( 'wpex_blog_entry_meta_sections', $sections );

}

/**
 * Check if the blog slider is disabled.
 *
 * @since 4.0
 */
function wpex_blog_entry_slider_enabled() {
	$bool = get_theme_mod( 'blog_entry_gallery_output', true );
	if ( apply_filters( 'wpex_disable_entry_slider', false ) || post_password_required() ) {
		$bool = false;
	}
	return $bool;
}

/*-------------------------------------------------------------------------------*/
/* [ Single ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns single blog post blocks.
 *
 * @since 2.0.0
 * @todo  rename to 'wpex_blog_single_blocks' for consistency
 */
function wpex_blog_single_layout_blocks() {

	// Default blocks
	$defaults = array(
		'featured_media',
		'title',
		'meta',
		'post_series',
		'the_content',
		'post_tags',
		'social_share',
		'author_bio',
		'related_posts',
		'comments',
	);

	// Get layout blocks
	$blocks = get_theme_mod( 'blog_single_composer' );

	// If blocks are empty return defaults
	$blocks = $blocks ? $blocks : $defaults;

	// Convert blocks to array so we can loop through them
	if ( ! is_array( $blocks ) ) {
		$blocks = explode( ',', $blocks );
	}

	// Set block keys equal to vals
	$blocks = $blocks ? array_combine( $blocks, $blocks ) : array();

	// Remove items if post is password protected
	if ( post_password_required() ) {
		unset( $blocks['featured_media'] );
		unset( $blocks['post_tags'] );
		unset( $blocks['social_share'] );
		unset( $blocks['author_bio'] );
		unset( $blocks['author_bio'] );
	}

	// Apply filters to single layout blocks
	// This filter won't add setting to Customizer
	// This setting is simply for filtering on front-end > must be different.
	$blocks = apply_filters( 'wpex_blog_single_layout_blocks', $blocks, 'front-end' );

	// Return blocks
	return $blocks;

}

/**
 * Blog Single media type.
 *
 * @since 5.0
 */
function wpex_blog_single_media_type() {

	$format = get_post_format();

	$type = '';

	switch ( $format ) {

		case 'video':

			if ( ! post_password_required()
				&& wpex_has_post_video()
			) {
				$type = 'video';
			} else {
				$type = 'thumbnail';
			}

			break;

		case 'audio':

			if ( ! post_password_required() && wpex_has_post_audio() ) {
				$type = 'audio';
			} else {
				$type = 'thumbnail';
			}

			break;

		case 'gallery':

			$type = ( wpex_has_post_gallery() ) ? 'gallery' : 'thumbnail';

			break;

		case 'link':

			$type = 'link';

			break;

		default:

			$type = 'thumbnail';

			break;

	}

	// Check for thumbnail existense so we don't end up with empty media div
	if ( ( 'thumbnail' == $type || 'link' == $type ) && ! has_post_thumbnail() ) {
		$type = '';
	}

	return apply_filters( 'wpex_blog_single_media_type', $type );

}

/**
 * Returns single blog meta sections.
 *
 * @since 2.0.0
 */
function wpex_blog_single_meta_sections() {

	// Default sections
	$sections = array(
		'date',
		'author',
		'categories',
		'comments',
	);

	// Get Sections from Customizer
	$sections = get_theme_mod( 'blog_post_meta_sections', $sections );

	// Turn into array if string
	if ( $sections && ! is_array( $sections ) ) {
		$sections = explode( ',', $sections );
	}

	// Array tweaks
	if ( $sections && is_array( $sections ) ) {
		$sections = array_combine( $sections, $sections );
	}

	// Apply filters and return
	return apply_filters( 'wpex_blog_single_meta_sections', $sections );

}

/**
 * Blog Single lightbox check.
 *
 * @since 5.0
 */
function wpex_has_blog_single_thumbnail_lightbox() {
	return (bool) apply_filters( 'wpex_has_blog_single_thumbnail_lightbox', get_theme_mod( 'blog_post_image_lightbox', false ) );
}

/**
 * Blog Single caption.
 *
 * @since 5.0
 * @todo include support for custom post types?
 */
function wpex_blog_single_thumbnail_caption() {

	if ( ! get_theme_mod( 'blog_thumbnail_caption' ) ) {
		return;
	}

	$caption = wpex_featured_image_caption();
	$has_caption = (bool) $caption;

	if ( ! $has_caption ) {
		return;
	}

	$classes = array(
		'post-media-caption',
		'wpex-absolute',
		'wpex-inset-x-0',
		'wpex-bottom-0',
		'wpex-p-15',
		'wpex-text-white',
		'wpex-child-inherit-color',
		'wpex-text-sm',
		'wpex-text-center',
		'wpex-last-mb-0',
		'wpex-clr',
	);

	$classes = (array) apply_filters( 'wpex_blog_single_thumbnail_caption_class', $classes );

	?>

	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"><?php echo wp_kses_post( $caption ); ?></div>

<?php }

/**
 * Returns the blog entry thumbnail arhs
 *
 * @since 4.9
 * @todo rename to wpex_get_blog_single_thumbnail_args?
 */
function wpex_get_blog_post_thumbnail_args( $args = '' ) {

	// If args isn't array then it's an attachment such as a slider image
	if ( ! is_array( $args ) && ! empty( $args ) ) {
		$args = array(
			'attachment'    => $args,
			'schema_markup' => false,
		);
	}

	// Defaults
	$defaults = array(
		'size'          => 'blog_post',
		'schema_markup' => true,
		'class'         => 'blog-single-media-img wpex-align-middle',
		'apply_filters' => 'wpex_blog_post_thumbnail_args',
	);

	// Parse arguments
	$args = wp_parse_args( $args, $defaults );

	// Change size for above media
	if ( 'above' == wpex_get_custom_post_media_position() ) {
		$args['size'] = 'blog_post_full';
	}

	return $args;
}

/**
 * Displays the blog post thumbnail
 *
 * @since Total 1.0
 */
function wpex_blog_post_thumbnail( $args = '' ) {
	echo wpex_get_blog_post_thumbnail( $args );
}

/**
 * Returns the blog post thumbnail
 *
 * @since 1.0.0
 */
function wpex_get_blog_post_thumbnail( $args = '' ) {

	$supports_thumbnail = ( 'audio' == get_post_format() ) ? false : true;

	if ( apply_filters( 'wpex_blog_post_supports_thumbnail', $supports_thumbnail ) ) {
		return apply_filters( 'wpex_blog_post_thumbnail', wpex_get_post_thumbnail( wpex_get_blog_post_thumbnail_args( $args ) ) );
	}

}

/**
 * Blog Single Content Class
 *
 * @since 5.0
 */
function wpex_blog_single_content_class() {

	$classes = array(
		'single-blog-content',
		'entry',
		'wpex-mt-20',
		'wpex-mb-40',
		'wpex-clr',
	);

	$classes = (array) apply_filters( 'wpex_blog_single_content_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
	}

}

/**
 * Blog Single Header Class.
 *
 * @since 5.0
 */
function wpex_blog_single_header_class() {

	$classes = array(
		'single-blog-header',
		'wpex-mb-10',
	);

	$classes = (array) apply_filters( 'wpex_blog_single_header_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Blog Single Title Class.
 *
 * @since 5.0
 */
function wpex_blog_single_title_class() {

	$classes = array(
		'single-post-title',
		'entry-title',
		'wpex-text-3xl',
	);

	$classes = (array) apply_filters( 'wpex_blog_single_title_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Blog Single Meta Class.
 *
 * @since 5.0
 */
function wpex_blog_single_meta_class() {

	$classes = array(
		'meta',
		'wpex-text-sm',
		'wpex-text-gray-600',
		'wpex-mb-20',
		'wpex-last-mr-0',
	);

	$classes = (array) apply_filters( 'wpex_blog_single_meta_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Blog single blocks class.
 *
 * @since 5.0
 */
function wpex_blog_single_blocks_class() {

	$classes = array(
		'single-blog-article',
		'wpex-first-mt-0',
	);

	$classes[] = 'wpex-clr';

	$classes = (array) apply_filters( 'wpex_blog_single_blocks_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Blog single media class.
 *
 * @since 5.0
 */
function wpex_blog_single_media_class() {

	$classes = array(
		'single-blog-media',
		'single-media',
		'wpex-mb-20',
	);

	if ( 'above' == wpex_get_custom_post_media_position() ) {
		$classes[] = 'wpex-md-mb-30';
	}

	$classes = (array) apply_filters( 'wpex_blog_single_media_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Related ]
/*-------------------------------------------------------------------------------*/

/**
 * Blog single related query.
 *
 * @since 5.0
 */
function wpex_blog_single_related_query() {
	$post_id = get_the_ID();

	// Return if disabled via post meta
	if ( wpex_validate_boolean( get_post_meta( $post_id, 'wpex_disable_related_items', true ) ) ) {
		return false;
	}

	// Posts count
	$posts_count = absint( get_theme_mod( 'blog_related_count', 3 ) );

	// Return if count is empty
	if ( empty( $posts_count ) ) {
		return false;
	}

	// Query args
	$args = array(
		'posts_per_page'      => $posts_count,
		'order'               => get_theme_mod( 'blog_related_order', 'desc' ),
		'orderby'             => get_theme_mod( 'blog_related_orderby', 'date' ),
		'post__not_in'        => array( $post_id ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,

		// exclude quote and link formats from related items
		'tax_query'      => array(
			'relation'  => 'AND',
			array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => array( 'post-format-quote', 'post-format-link' ),
				'operator' => 'NOT IN',
			),
		),

	);

	// Related by taxonomy
	if ( apply_filters( 'wpex_related_in_same_cat', true ) ) {

		// Add categories to query
		$related_taxonomy = get_theme_mod( 'blog_related_taxonomy', 'category' );

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
				$args['tax_query'][] = array(
					'taxonomy' => $related_taxonomy,
					'field'    => 'term_id',
					'terms'    => $terms,
				);
			}

		}

	}

	// If content is disabled make sure items have featured images
	if ( ! get_theme_mod( 'blog_related_excerpt', true ) ) {
		$args['meta_key'] = '_thumbnail_id';
	}

	// Apply filters to arguments for child theme editing.
	$args = apply_filters( 'wpex_blog_post_related_query_args', $args );

	// Related query arguments
	if ( $args ) {
		return new wp_query( $args );
	}

}

/**
 * Gets correct heading for the related blog items
 *
 * @since 2.0.0
 */
function wpex_blog_related_heading() {
	$heading = wpex_get_translated_theme_mod( 'blog_related_title' );
	$heading = $heading ? $heading : esc_html__( 'Related Posts', 'total' );
	return $heading;
}

/**
 * Blog single related class.
 *
 * @since 5.0
 */
function wpex_blog_single_related_class() {

	$classes = array(
		'related-posts',
		'wpex-overflow-hidden', // for the negative margins on the row
		'wpex-mb-40',
	);

	if ( 'full-screen' == wpex_content_area_layout() ) {
		$classes[] = 'container';
	}

	$classes[] = 'wpex-clr';

	$classes = (array) apply_filters( 'wpex_blog_single_related_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Blog single related row class.
 *
 * @since 5.0
 */
function wpex_blog_single_related_row_class() {

	$classes = array(
		'wpex-row',
		'wpex-clr'
	);

	if ( $gap = get_theme_mod( 'blog_related_gap' ) ) {
		$classes[] = 'gap-' . sanitize_html_class( $gap );
	}

	$classes = (array) apply_filters( 'wpex_blog_single_related_row_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Blog single related entry class.
 *
 * @since 5.0
 */
function wpex_blog_single_related_entry_class() {

	$classes = array(
		'related-post',
		'col',
	);

	$columns = wpex_blog_single_related_columns();

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
 * Returns correct columns for the blog single related entries.
 *
 * @since 5.0
 * @todo rename filter to wpex_blog_single_related_columns
 */
function wpex_blog_single_related_columns() {
	return apply_filters( 'wpex_related_blog_posts_columns', get_theme_mod( 'blog_related_columns', '3' ) );
}

/*-------------------------------------------------------------------------------*/
/* [ Slider ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns data attributes for the blog gallery slider
 *
 * @since 2.0.0
 */
function wpex_blog_slider_data_atrributes() {
	echo wpex_get_slider_data( array(
		'filter_tag' => 'wpex_blog_slider_data_atrributes',
	) );
}

/**
 * Returns correct blog slider video embed code
 * Adds unique class for the slider
 *
 * @since 2.0.0
 */
function wpex_blog_slider_video( $attachment ) {
	$video = get_post_meta( $attachment, '_video_url', true );
	$video = wp_oembed_get( esc_url( $video ) );
	return wpex_add_sp_video_to_oembed( $video );
}

/*-------------------------------------------------------------------------------*/
/* [ Cards ]
/*-------------------------------------------------------------------------------*/

/**
 * Blog entry card style.
 *
 * @since 5.0
 */
function wpex_blog_entry_card_style() {
	$instance = wpex_get_loop_instance();
	$card_style = get_query_var( 'card_style' );
	if ( empty( $card_style ) ) {
		switch ( $instance ) {
			case 'related':
				$card_style = get_theme_mod( 'blog_related_entry_card_style' );
				break;
			default:
				$card_style = get_theme_mod( 'blog_entry_card_style' );
				$cat_meta_check = wpex_get_term_meta( '', 'wpex_entry_card_style', true );
				if ( $cat_meta_check ) {
					$card_style = $cat_meta_check;
				}
				break;
		}
	}
	return apply_filters( 'wpex_blog_entry_card_style', $card_style );
}

/**
 * Blog entry card.
 *
 * @since 5.0
 */
function wpex_blog_entry_card() {

	$instance = wpex_get_loop_instance();

	$card_style = wpex_blog_entry_card_style();

	if ( ! $card_style ) {
		return false;
	}

	if ( 'related' === $instance ) {
		$thumbnail_size = 'blog_related';
		$excerpt_length = get_theme_mod( 'blog_related_excerpt_length', '15' );
	} else {
		$thumbnail_size = 'blog_entry';
		$excerpt_length = wpex_excerpt_length();

		// Check category image size meta options
		$cat_meta_image_width = wpex_get_category_meta( '', 'wpex_term_image_width' );
		$cat_meta_image_height = wpex_get_category_meta( '', 'wpex_term_image_height' );

		if ( $cat_meta_image_width || $cat_meta_image_height ) {
			$thumbnail_size = array( $cat_meta_image_width, $cat_meta_image_height );
		}

	}

	$args = array(
		'style'          => $card_style,
		'post_id'        => get_the_ID(),
		'thumbnail_size' => $thumbnail_size,
		'excerpt_length' => $excerpt_length,
	);

	if ( $overlay_style = wpex_blog_entry_overlay_style() ) {
		$args['thumbnail_overlay_style'] = $overlay_style;
	}

	$args = apply_filters( 'wpex_blog_entry_card_args', $args, $card_style );

	wpex_card( $args );

	return true;

}

/*-------------------------------------------------------------------------------*/
/* [ Deprecated ]
/*-------------------------------------------------------------------------------*/

/**
 * Returns post video URL
 *
 * @since 1.0.0
 */
function wpex_post_video_url( $post_id = '' ) {

	// Sanitize post_id var
	$post_id = $post_id ? $post_id : get_the_ID();

	// Oembed
	if ( $meta = get_post_meta( $post_id, 'wpex_post_oembed', true ) ) {
		return esc_url( $meta );
	}

	// Self Hosted redux
	$video = get_post_meta( $post_id, 'wpex_post_self_hosted_shortcode_redux', true );
	if ( is_array( $video ) && ! empty( $video['url'] ) ) {
		return $video['url'];
	}

	// Self Hosted old - Thunder theme compatibility
	if ( $meta = get_post_meta( $post_id, 'wpex_post_self_hosted_shortcode', true ) ) {
		return $meta;
	}

}

/**
 * Returns post audio URL
 *
 * @since 1.0.0
 */
function wpex_post_audio_url( $post_id = '' ) {

	// Sanitize post_id var
	$post_id = $post_id ? $post_id : get_the_ID();

	// Oembed
	if ( $meta = get_post_meta( $post_id, 'wpex_post_oembed', true ) ) {
		return $meta;
	}

	// Self Hosted redux
	$audio = get_post_meta( $post_id, 'wpex_post_self_hosted_shortcode_redux', true );
	if ( is_array( $audio ) && ! empty( $audio['url'] ) ) {
		return $audio['url'];
	}

	// Self Hosted old - Thunder theme compatibility
	if ( $meta = get_post_meta( $post_id, 'wpex_post_self_hosted_shortcode', true ) ) {
		return $meta;
	}

}