<?php
/**
 * All page header functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.2
 *
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*

	# General
	# Content
	# Aside
	# Title
	# Subheading
	# Background
	# Inline CSS

/*-------------------------------------------------------------------------------*/
/* [ General ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if page header is enabled.
 *
 * @since 4.0
 */
function wpex_has_page_header() {

	// Get default value
	$return = get_theme_mod( 'enable_page_header', true );

	// Get page header style
	$page_header_style = wpex_page_header_style();
	$is_global_style = wpex_is_global_page_header_style( $page_header_style );

	// Hide by default if style is set to hidden
	if ( 'hidden' === $page_header_style ) {
		$return = false;
	}

	// Check meta options => MUST COME LAST
	if ( $post_id = wpex_get_current_post_id() ) {

		// Check Customizer setting only if not disabled globally
		if ( is_singular() && 'hidden' !== get_theme_mod( 'page_header_style' ) ) {
			$return = get_theme_mod( get_post_type() . '_singular_page_title', $return );
		}

		// Get page meta setting
		// @todo add new better meta field named something like wpex_has_title
		$meta = get_post_meta( $post_id, 'wpex_disable_title', true );

		if ( 'enable' === $meta ) {
			$return = true;
		} elseif ( 'on' === $meta ) {
			$return = false; // fallback for when setting only disabled the title and din't also enable it.
		}

	}

	// Re enable for background image style
	if ( 'background-image' === $page_header_style && ! $is_global_style ) {
		$return = true; // @todo deprecate this function?
	}

	// Woo Check
	if ( wpex_is_woo_shop() && ! get_theme_mod( 'woo_shop_title', true ) ) {
		$return = false;
	}

	// Apply filters and return
	return apply_filters( 'wpex_display_page_header', $return );

}

/**
 * Returns page header breakpoint.
 *
 * @since 5.0
 */
function wpex_page_header_breakpoint() {
	$bk = wpex_get_mod( 'page_header_breakpoint', 'md', true );
	return apply_filters( 'wpex_page_header_breakpoint', $bk );
}

/**
 * Returns correct page header style.
 *
 * @since 4.0
 */
function wpex_page_header_style() {

	$post_id = wpex_get_current_post_id();
	$style = get_theme_mod( 'page_header_style' );

	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_post_title_style', true ) ) {
		$style = $meta;
	}

	if ( WPEX_PTU_ACTIVE ) {
		if ( is_singular() ) {
			$custom_style = wpex_get_ptu_type_mod( get_post_type(), 'page_header_title_style' );
			if ( $custom_style ) {
				$style = $custom_style;
			}
		} elseif ( is_post_type_archive() ) {
			$custom_style = wpex_get_ptu_type_mod( get_query_var( 'post_type' ), 'archive_page_header_title_style' );
			if ( $custom_style ) {
				$style = $custom_style;
			}
		} elseif ( is_tax() ) {
			$custom_style = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'page_header_title_style' );
			if ( $custom_style ) {
				$style = $custom_style;
			}
		}
	}

	$style = apply_filters( 'wpex_page_header_style', $style );

	if ( empty( $style ) ) {
		$style = 'default';
	}

	return $style;

}

/**
 * Check if the current page header style is also the global style.
 *
 * @since 4.0
 */
function wpex_is_global_page_header_style( $style = '' ) {
	if ( $style === get_theme_mod( 'page_header_style' ) || $style === 'default' ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Outputs page header class tag.
 *
 * @since 5.0
 */
function wpex_page_header_class() {
	echo 'class="' . wpex_page_header_classes() . '"';
}

/**
 * Adds correct classes to the page header.
 *
 * @since 2.0.0
 */
function wpex_page_header_classes() {

	$post_id = wpex_get_current_post_id();
	$page_header_style = wpex_page_header_style();
	$is_global_style = wpex_is_global_page_header_style( $page_header_style );

	// Define main class
	$classes = array( 'page-header' );

	// Add classes for title style
	if ( $page_header_style ) {
		$classes[] = sanitize_html_class( $page_header_style ) . '-page-header';
	}

	// Allow customizations to this header style if it's the globally defined style
	if ( $is_global_style || ! in_array( $page_header_style, array( 'background-image', 'solid-color' ) ) ) {
		$classes[] = 'wpex-supports-mods';
	}

	// Add background image styles
	if ( ( $is_global_style || 'background-image' === $page_header_style ) && wpex_page_header_background_image() ) {
		$classes[] = 'has-bg-image';
		if ( $bg_style = wpex_page_header_background_image_style() ) {
			$classes[] = 'bg-' . sanitize_html_class( $bg_style );
		}
	}

	// Get custom text align
	$text_align = get_theme_mod( 'page_header_text_align' );

	// Utility classes
	$classes[] = 'wpex-relative';
	$classes[] = 'wpex-mb-40';

	switch ( $page_header_style ) {

		case 'background-image':
			$classes[] = 'wpex-flex';
			$classes[] = 'wpex-items-' . sanitize_html_class( wpex_get_mod( 'page_header_align_items', 'center', true ) );
			$classes[] = 'wpex-flex-wrap';
			$classes[] = 'wpex-bg-gray-900';
			$classes[] = 'wpex-text-white';
			if ( ! $text_align ) {
				$classes[] = 'wpex-text-center';
			}
			break;

		case 'solid-color':
			$classes[] = 'wpex-bg-accent';
			$classes[] = 'wpex-py-20';
			break;

		case 'centered':
			$classes[] = 'wpex-bg-gray-100';
			$classes[] = 'wpex-py-30';
			$classes[] = 'wpex-border-t';
			$classes[] = 'wpex-border-b';
			$classes[] = 'wpex-border-solid';
			$classes[] = 'wpex-border-gray-200';
			$classes[] = 'wpex-text-gray-700';
			if ( ! $text_align ) {
				$classes[] = 'wpex-text-center';
			}
			break;

		case 'centered-minimal':
			$classes[] = 'wpex-bg-white';
			$classes[] = 'wpex-py-30';
			$classes[] = 'wpex-border-t';
			$classes[] = 'wpex-border-b';
			$classes[] = 'wpex-border-solid';
			$classes[] = 'wpex-border-main';
			$classes[] = 'wpex-text-gray-700';
			if ( ! $text_align ) {
				$classes[] = 'wpex-text-center';
			}
			break;

		default:
			$classes[] = 'wpex-bg-gray-100';
			$classes[] = 'wpex-py-20';
			$classes[] = 'wpex-border-t';
			$classes[] = 'wpex-border-b';
			$classes[] = 'wpex-border-solid';
			$classes[] = 'wpex-border-gray-200';
			$classes[] = 'wpex-text-gray-700';
			break;

	}

	// Add text align
	if ( $text_align ) {
		$classes[] = 'wpex-text-' . sanitize_html_class( $text_align );
	}

	// Remove duplicate classes
	$classes = array_unique( $classes );

	// Sanitize
	$classes = array_map( 'esc_attr', $classes );

	// Apply filters
	$classes = apply_filters( 'wpex_page_header_classes', $classes ); // @todo deprecate filter.
	$classes = (array) apply_filters( 'wpex_page_header_class', $classes );

	// Return classes
	return implode( ' ', $classes );

}

/**
 * Page header inner class.
 *
 * @since 5.0
 */
function wpex_page_header_inner_class() {

	$class = array(
		'page-header-inner',
		'container',
	);

	$page_header_style = wpex_page_header_style();

	switch ( $page_header_style ) {

		case 'background-image':
			$class[] = 'wpex-py-20';
			$class[] = 'wpex-z-5';
			$class[] = 'wpex-relative';
		break;

		case 'solid-color':
			$classes[] = 'wpex-text-white';
		break;

	}

	// Flex styles
	if ( has_action( 'wpex_hook_page_header_content' ) ) {

		if ( ( 'default' === $page_header_style || 'solid-color' === $page_header_style )
			&& has_action( 'wpex_hook_page_header_aside' )
		) {

			$bk = wpex_page_header_breakpoint();
			$bk_escaped = sanitize_html_class( $bk );

			if ( $bk_escaped ) {

				$class[] = 'wpex-' . $bk_escaped . '-flex';
				$class[] = 'wpex-' . $bk_escaped . '-flex-wrap';
				$class[] = 'wpex-' . $bk_escaped . '-items-center';
				$class[] = 'wpex-' . $bk_escaped . '-justify-between';

			}

		}

	}

	$class = (array) apply_filters( 'wpex_page_header_inner_class', $class );

	if ( $class ) {
		echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Content ]
/*-------------------------------------------------------------------------------*/

/**
 * Page header content class
 *
 * @since 5.0
 */
function wpex_page_header_content_class() {

	$classes = array(
		'page-header-content',
	);

	if ( has_action( 'wpex_hook_page_header_aside' ) ) {

		$page_header_style = wpex_page_header_style();

		if ( 'default' === $page_header_style || 'solid-color' === $page_header_style ) {

			$bk = wpex_page_header_breakpoint();
			$bk_escaped = sanitize_html_class( $bk );

			if ( $bk_escaped ) {
				$classes[] = 'wpex-'. $bk_escaped . '-mr-15';
			}

		}

	}

	$classes = (array) apply_filters( 'wpex_page_header_content_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Aside ]
/*-------------------------------------------------------------------------------*/

/**
 * Page header aside class
 *
 * @since 5.0
 */
function wpex_page_header_aside_class() {

	$classes = array(
		'page-header-aside',
	);

	if ( has_action( 'wpex_hook_page_header_content' ) ) {

		$page_header_style = wpex_page_header_style();

		if ( 'default' === $page_header_style || 'solid-color' === $page_header_style ) {

			$bk = wpex_page_header_breakpoint();
			$bk_escaped = sanitize_html_class( $bk );

			if ( $bk_escaped ) {

				$classes[] = 'wpex-' . $bk_escaped . '-text-right';

			}

		} else {
			$classes[] = 'wpex-mt-5';
		}

	}

	$classes = (array) apply_filters( 'wpex_page_header_aside_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/*-------------------------------------------------------------------------------*/
/* [ Title ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if page header title is enabled.
 *
 * @since 4.0
 */
function wpex_has_page_header_title() {

	// Get current post ID
	$post_id = wpex_get_current_post_id();

	// Disable title if the page header is disabled via meta (ignore filter)
	if ( $post_id && 'on' === get_post_meta( $post_id, 'wpex_disable_title', true ) ) {
		return false;
	}

	// Apply filters and return
	return apply_filters( 'wpex_has_page_header_title', true );

}

/**
 * Echo page header title class.
 *
 * @since 5.0
 */
function wpex_page_header_title_class() {

	$classes = array(
		'page-header-title',
		'wpex-block',
		'wpex-m-0',
		// Font style resets to prevent issues with customizer h1
		'wpex-inherit-font-family',
		'wpex-not-italic',
		'wpex-tracking-normal',
		'wpex-leading-normal',
		'wpex-font-normal',
	);

	$page_header_style = wpex_page_header_style();

	switch ( $page_header_style ) {
		case 'centered':
			$classes[] = 'wpex-text-5xl';
			$classes[] = 'wpex-text-gray-900';
			break;
		case 'centered-minimal':
			$classes[] = 'wpex-text-5xl';
			$classes[] = 'wpex-text-gray-900';
			break;
		case 'background-image':
			$classes[] = 'wpex-text-7xl';
			$classes[] = 'wpex-text-white';
			break;
		case 'solid-color':
			$classes[] = 'wpex-text-2xl';
			break;
		default:
			$classes[] = 'wpex-text-2xl';
			$classes[] = 'wpex-text-gray-900';
			break;
	}

	$classes = (array) apply_filters( 'wpex_page_header_title_class', $classes );

	$classes = array_unique( $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}


/**
 * Return header title args.
 *
 * @since 5.0
 */
function wpex_page_header_title_args() {

	$args        = array();
	$is_singular = is_singular();
	$post_type   = $is_singular ? get_post_type() : '';

	// Single post markup
	if ( 'post' === $post_type ) {
		$blog_single_header = get_theme_mod( 'blog_single_header', 'custom_text' );
		if ( 'custom_text' == $blog_single_header || 'first_category' == $blog_single_header ) {
			$args['html_tag']      = 'span';
			$args['schema_markup'] = '';
		}
	}

	// Singular CPT
	elseif ( $is_singular && ! in_array( $post_type, array( 'page', 'attachment' ), true ) ) {
		$args['html_tag'] = 'span';
		$args['schema_markup'] = '';
	}

	if ( $is_singular && WPEX_PTU_ACTIVE ) {

		$ptu_tag = wpex_get_ptu_type_mod( $post_type, 'page_header_title_tag' );

		if ( $ptu_tag ) {
			$args['html_tag'] = $ptu_tag;
		}

	}

	// Apply filters
	$args = apply_filters( 'wpex_page_header_title_args', $args, null ); // second arg was deprecated in v5.0

	// Meta check - perform after filter to ensure meta takes priority
	$post_id = wpex_get_current_post_id();
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_post_title', true ) ) {
		$args['string'] = $meta;
	}

	// Parse args after filter to prevent empty attributes
	return wp_parse_args( $args, array(
		'html_tag'      => 'h1',
		'string'        => wpex_title(),
		'schema_markup' => wpex_get_schema_markup( 'headline' )
	) );

}

/*-------------------------------------------------------------------------------*/
/* [ Subheading ]
/*-------------------------------------------------------------------------------*/

/**
 * Return header subheading class.
 *
 * @since 5.0
 */
function wpex_page_header_subheading_class() {

	$page_header_style = wpex_page_header_style();

	$classes = array(
		'page-subheading',
		'wpex-last-mb-0',
	);

	switch ( $page_header_style ) {
		case 'centered':
			$classes[] = 'wpex-text-xl';
			$classes[] = 'wpex-font-light';
			break;
		case 'centered-minimal':
			$classes[] = 'wpex-text-xl';
			$classes[] = 'wpex-font-light';
			break;
		case 'background-image':
			$classes[] = 'wpex-text-3xl';
			$classes[] = 'wpex-text-white';
			$classes[] = 'wpex-font-light';
			break;
		default:
			$classes[] = 'wpex-text-md';
			break;
	}

	$classes = (array) apply_filters( 'wpex_page_header_subheading_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Check if page has header subheading.
 *
 * @since 4.0
 */
function wpex_page_header_has_subheading() {
	$check = wpex_page_header_subheading_content() ? true : false;
	return (bool) apply_filters( 'wpex_page_header_has_subheading', $check );
}

/**
 * Returns page header subheading content.
 *
 * @since 4.0
 */
function wpex_page_header_subheading_content() {

	// Subheading is empty by default
	$subheading = '';
	$instance   = ''; // @todo remove $instance.

	// Get post ID
	$post_id = wpex_get_current_post_id();

	// Posts & Pages
	if ( $post_id ) {
		if ( $meta = get_post_meta( $post_id, 'wpex_post_subheading', true ) ) {
			$subheading = $meta;
		}
		$instance = 'singular_' . get_post_type( $post_id );
	}

	// Categories
	elseif ( is_category() || is_tag() ) {
		$position = get_theme_mod( 'category_description_position' );
		$position = $position ? $position : 'under_title';
		if ( 'under_title' === $position ) {
			$subheading = term_description();
		}
		$instance = 'category';
	}

	// Author
	elseif ( is_author() ) {
		$subheading = esc_html__( 'This author has written', 'total' ) . ' ' . get_the_author_posts() . ' ' . esc_html__( 'articles', 'total' );
		$instance = 'author';
	}

	// All other Taxonomies
	elseif ( $tax = is_tax() ) {
		if ( ! wpex_has_term_description_above_loop() ) {
			$subheading = term_description(); // note: get_the_archive_description makes extra check to is_author() which isn't needed
		}
		$instance = 'tax';
	}

	// Apply filters and return
	return apply_filters( 'wpex_post_subheading', $subheading, $instance );

}

/*-------------------------------------------------------------------------------*/
/* [ Background ]
/*-------------------------------------------------------------------------------*/

/**
 * Get page header background image URL.
 *
 * @since 1.5.4
 */
function wpex_page_header_background_image() {

	// Get current post ID
	$post_id = wpex_get_current_post_id();

	// Get default Customizer value
	$image = get_theme_mod( 'page_header_background_img' );

	// Fetch from featured image
	if ( $image
		&& $post_id
		&& $fetch_thumbnail_types = get_theme_mod( 'page_header_background_fetch_thumbnail' )
	) {
		if ( ! is_array( $fetch_thumbnail_types ) ) {
			$fetch_thumbnail_types = explode( ',', $fetch_thumbnail_types );
		}
		if ( in_array( get_post_type( $post_id ), $fetch_thumbnail_types ) ) {
			$thumbnail = get_post_thumbnail_id( $post_id );
			if ( $thumbnail ) {
				$image = $thumbnail;
			}
		}
	}

	// Apply filters before meta checks => meta should always override
	$image = apply_filters( 'wpex_page_header_background_img', $image ); // @todo remove this deprecated filter
	$image = apply_filters( 'wpex_page_header_background_image', $image, $post_id );

	// Check meta for bg image
	if ( $post_id ) {

		$meta_image = '';

		// Get page header background from meta
		if ( $post_id && 'background-image' === get_post_meta( $post_id, 'wpex_post_title_style', true ) ) {

			// Redux fallback
			if ( $new_meta = get_post_meta( $post_id, 'wpex_post_title_background_redux', true ) ) {
				if ( is_array( $new_meta ) && ! empty( $new_meta['url'] ) ) {
					$meta_image = $new_meta['url'];
				} else {
					$meta_image = $new_meta;
				}
			}

			// Newer image title
			else {
				$meta_image = get_post_meta( $post_id, 'wpex_post_title_background', true );
			}

		}

		if ( $meta_image ) {

			if ( is_numeric( $meta_image ) ) {
				if ( wpex_attachment_exists( $meta_image ) ) {
					$image = $meta_image;
				}
			} else {
				$image = $meta_image;
			}

		}

	}

	// Return image
	if ( $image ) {
		return wpex_get_image_url( $image );
	}

}

/**
 * Get correct page header background image style.
 *
 * @since 5.0
 */
function wpex_page_header_background_image_style() {
	$page_header_style = wpex_page_header_style();

	$bg_style = ( 'background-image' == $page_header_style ) ? 'cover' : 'fixed';

	if ( $mod = get_theme_mod( 'page_header_background_img_style' ) ) {
		$bg_style = $mod;
	}

	if ( $meta_val = get_post_meta( wpex_get_current_post_id(), 'wpex_post_title_background_image_style', true ) ) {
		$bg_style = $meta_val;
	}

	$bg_style = apply_filters( 'wpex_page_header_background_img_style', $bg_style ); //deprecate old filter

	return apply_filters( 'wpex_page_header_background_image_style', $bg_style );

}

/**
 * Get correct page header overlay style.
 *
 * @since 3.6.0
 */
function wpex_get_page_header_overlay_style() {

	$overlay_style = 'dark';

	$page_header_style = wpex_page_header_style();

	if ( 'background-image' == $page_header_style ) {

		$post_id = wpex_get_current_post_id();

		if ( $post_id && 'background-image' === get_post_meta( $post_id, 'wpex_post_title_style', true ) ) {

			$overlay_style = get_post_meta( $post_id, 'wpex_post_title_background_overlay', true );

		}

	}

	if ( 'none' === $overlay_style ) {
		$overlay_style = '';
	}

	return (string) apply_filters( 'wpex_page_header_overlay_style', $overlay_style );

}

/**
 * Get correct page header overlay patttern.
 *
 * @since 5.0
 */
function wpex_get_page_header_overlay_pattern() {
	$pattern = '';
	$post_id = wpex_get_current_post_id();
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_post_title_background_overlay', true ) ) {
		if ( 'dotted' === $meta ) {
			$pattern = wpex_asset_url( 'images/dotted-overlay.png' );
		} elseif ( 'dashed' === $meta ) {
			$pattern = wpex_asset_url( 'images/dashed-overlay.png' );
		}
	}
	return apply_filters( 'wpex_get_page_header_overlay_pattern', $pattern );
}

/**
 * Get correct page header overlay opacity.
 *
 * @since 3.6.0
 */
function wpex_get_page_header_overlay_opacity() {
	$post_id = wpex_get_current_post_id();
	$opacity = '';
	if ( $post_id && 'background-image' === get_post_meta( $post_id, 'wpex_post_title_style', true ) ) {
		$meta = get_post_meta( $post_id, 'wpex_post_title_background_overlay_opacity', true );
		if ( $meta ) {
			$opacity = $meta;
		}
	}
	return apply_filters( 'wpex_page_header_overlay_opacity', $opacity );
}

/**
 * Outputs html for the page header overlay.
 *
 * @since 1.5.3
 */
function wpex_page_header_overlay( ) {

	// Only needed for the background-image style so return otherwise
	if ( 'background-image' !== wpex_page_header_style() ) {
		return;
	}

	// Define vars
	$return  = '';

	// Get settings
	$overlay_style = wpex_get_page_header_overlay_style();

	// Check that overlay style isn't set to none
	if ( $overlay_style ) {

		$classes = array(
			'background-image-page-header-overlay',
			'style-' . sanitize_html_class( $overlay_style ),
			'wpex-z-0',
			'wpex-bg-black',
			'wpex-absolute',
			'wpex-inset-0',
			'wpex-opacity-50',
		);

		$classes = apply_filters( 'wpex_page_header_overlay_class', $classes );

		$return = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '"></div>';

	}

	// Apply filters and echo
	echo apply_filters( 'wpex_page_header_overlay', $return );
}

/*-------------------------------------------------------------------------------*/
/* [ Inline CSS ]
/*-------------------------------------------------------------------------------*/

/**
 * Outputs Custom CSS for the page title.
 *
 * @since 1.5.3
 */
function wpex_page_header_css( $css_output ) {

	// Inline styles only needed if custom title style defined in meta and page header is enabled
	if ( ! wpex_has_post_meta( 'wpex_post_title_style' ) || ! wpex_has_page_header() ) {
		return $css_output;
	}

	// Get page header style
	$page_header_style = wpex_page_header_style();

	// Not needed for default header style
	if ( 'default' === wpex_page_header_style() ) {
		return $css_output;
	}

	// Get post id
	$post_id = wpex_get_current_post_id();

	// Define vars
	$add_css = '';
	$page_header_css = '';

	// Customize background color
	if ( 'solid-color' === $page_header_style || 'background-image' === $page_header_style ) {
		$bg_color = get_post_meta( $post_id, 'wpex_post_title_background_color', true );
		if ( $bg_color && '#' !== $bg_color ) {
			$page_header_css .= 'background-color:' . wp_strip_all_tags( $bg_color ) . '!important;';
		}
	}

	// Background image Style
	if ( 'background-image' === $page_header_style ) {

		// Background image
		$bg_img = wpex_page_header_background_image();

		if ( $bg_img ) {
			$page_header_css .= 'background-image:url(' . esc_url( $bg_img ) . ' )!important;';
		}

		// Background position
		$title_bg_position = apply_filters( 'wpex_page_header_background_position', get_post_meta( $post_id, 'wpex_post_title_background_position', true ) );

		if ( $title_bg_position ) {
			$page_header_css .= 'background-position:' . wp_strip_all_tags( $title_bg_position ) . ';';
		} else {
			$page_header_css .= 'background-position:50% 0;';
		}

		// Custom height
		$title_height = get_post_meta( $post_id, 'wpex_post_title_height', true );
		$title_height = apply_filters( 'wpex_post_title_height', $title_height ); // @todo rename filter to something more appropriate

		if ( $title_height ) {
			$add_css .= '.page-header.background-image-page-header{min-height:' . wpex_sanitize_data( $title_height, 'px' ) . '!important;;}';
		}

	}

	// Apply all css to the page-header class
	if ( ! empty( $page_header_css ) ) {
		$add_css .= '.page-header{' . $page_header_css . '}';
	}

	// Overlay Styles
	if ( ! empty( $bg_img ) && 'background-image' === $page_header_style ) {

		$overlay_style = wpex_get_page_header_overlay_style();

		if ( $overlay_style ) {

			$overlay_css = '';

			// Use bg_color for overlay background
			if ( ! empty( $bg_color ) && 'bg_color' === $overlay_style ) {
				$overlay_css .= 'background-color:' . wp_strip_all_tags( $bg_color ) . ' !important;';
			}

			// Overlay opacity
			if ( $opacity = wpex_get_page_header_overlay_opacity() ) {
				$overlay_css .= 'opacity:' . wp_strip_all_tags( $opacity ) . ';';
			}

			// Background pattern
			if ( $pattern = wpex_get_page_header_overlay_pattern() ) {
				if ( ! empty( $bg_color ) ) {
					$overlay_css .= 'background-color:' . wp_strip_all_tags( $bg_color ) . ';';
				} else {
					$overlay_css .= 'background-color:rgba(0,0,0,0.3);';
				}
				$overlay_css .= 'background-image: url(' . esc_url( $pattern ) . ');';
				$overlay_css .= 'background-repeat: repeat;';
			}

			// Add overlay CSS
			if ( $overlay_css ) {
				$add_css .= '.background-image-page-header-overlay{' . $overlay_css . '}';
			}

		}

	}

	// If css var isn't empty add to custom css output
	if ( ! empty( $add_css ) ) {
		$css_output .= $add_css;
	}

	// Return output
	return $css_output;

}
add_filter( 'wpex_head_css', 'wpex_page_header_css' );