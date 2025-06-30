<?php
/**
 * Theme layout functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns correct site layout.
 *
 * @since 4.0
 */
function wpex_site_layout( $post_id = '' ) {

	// Get layout from theme mod
	$layout = get_theme_mod( 'main_layout_style' );

	// Get post ID
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Check meta
	if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_main_layout', true ) ) {
		$layout = $meta;
	}

	// Apply filters
	$layout = apply_filters( 'wpex_main_layout', $layout );

	// Sanitize layout => Can't be empty!!
	if ( empty( $layout ) ) {
		$layout = 'full-width';
	}

	// Return layout
	return $layout;

}

/**
 * Returns default content layout.
 *
 * @since 4.5
 */
function wpex_get_default_content_area_layout() {
	$default = is_rtl() ? 'left-sidebar' : 'right-sidebar';
	$mod = get_theme_mod( 'content_layout', $default );
	return $mod ? $mod : $default;
}

/**
 * Returns correct content area layout.
 *
 * @since 4.0
 * @todo convert into a class with a helper function so it's easier to read.
 */
function wpex_content_area_layout( $post_id = '' ) {

	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	$default = wpex_get_default_content_area_layout();

	$layout = $default;

	$instance = ''; // @todo deprecate instance

	// Singular checks // Must use the post_id check to prevent issues
	// with custom pages like Events Calendar, 404 page, etc
	if ( $post_id ) {

		// Check meta first to override and return (prevents filters from overriding meta)
		if ( $post_id && $meta = get_post_meta( $post_id, 'wpex_post_layout', true ) ) {
			return $meta;
		}

		// Get post type
		$post_type = get_post_type();
		$instance  = 'singular_' . $post_type;

		// Singular Page
		if ( 'page' == $post_type ) {

			// Get page layout setting value
			$layout = get_theme_mod( 'page_single_layout' );

			// Get page template layouts
			if ( $page_template = get_page_template_slug( $post_id ) ) {

				// Blog template
				if ( $page_template == 'templates/blog.php' ) {
					$layout = get_theme_mod( 'blog_archives_layout' );
				}

				// Landing Page
				elseif ( $page_template == 'templates/landing-page.php' ) {
					$layout = 'full-width';
				}

				// Full Screen
				elseif ( $page_template == 'templates/full-screen.php' ) {
					$layout = 'full-screen';
				}

				// No Sidebar
				elseif ( $page_template == 'templates/no-sidebar.php' ) {
					$layout = 'full-width';
				}

				// Left Sidebar
				elseif ( $page_template == 'templates/left-sidebar.php' ) {
					$layout = 'left-sidebar';
				}

				// Right Sidebar
				elseif ( $page_template == 'templates/right-sidebar.php' ) {
					$layout = 'right-sidebar';
				}

			}

		}

		// Singular Post
		elseif ( 'post' == $post_type ) {
			$layout = get_theme_mod( 'blog_single_layout' );
		}

		// Attachment
		elseif ( 'attachment' == $post_type ) {
			$layout = 'full-width';
		}

		// Templatera
		elseif ( 'templatera' == $post_type ) {
			return 'full-width'; // Always return full-width
		}

		// Elementor
		elseif( 'elementor_library' == $post_type ) {
			return 'full-width'; // Always return full-width
		}

		if ( WPEX_PTU_ACTIVE ) {

			$ptu_layout = wpex_get_ptu_type_mod( $post_type, 'post_layout' );

			if ( isset( $ptu_layout ) ) {
				$layout = $ptu_layout;
			}

		}

	} // End singular

	// 404 page => must check before archives due to WP bug with pagination
	elseif ( is_404() ) {
		$instance = '404';
		if ( ! get_theme_mod( 'error_page_content_id' ) ) {
			$layout = 'full-width';
		}
	}

	// Home
	elseif ( is_home() ) {
		$instance = 'home';
		$layout = get_theme_mod( 'blog_archives_layout' );
	}

	// Search => MUST BE BEFORE TAX CHECK, WP returns true for is_tax on search results
	elseif ( is_search() ) {
		$instance = 'search';
		$layout = get_theme_mod( 'search_layout' );
	}

	// Define tax instance
	elseif ( is_tax() ) {
		$instance = 'tax';

		if ( WPEX_PTU_ACTIVE ) {

			$ptu_layout = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'layout' );

			if ( isset( $ptu_layout ) ) {
				$layout = $ptu_layout;
			}

		}

	}

	// Define post type archive instance
	elseif( is_post_type_archive() ) {
		$instance = 'post_type_archive';

		if ( WPEX_PTU_ACTIVE ) {

			$ptu_layout = wpex_get_ptu_type_mod( get_query_var( 'post_type' ), 'archive_layout' );

			if ( isset( $ptu_layout ) ) {
				$layout = $ptu_layout;
			}

		}

	}

	// Blog Query => Must come before category check
	elseif ( wpex_is_blog_query() ) {
		$instance = 'wpex_is_blog_query';
		$layout = get_theme_mod( 'blog_archives_layout' );

		// Extra check for categories with custom meta
		if ( is_category() ) {
			$instance = 'category';
			$layout = get_theme_mod( 'blog_archives_layout' );
			$term  = get_query_var( 'cat' );
			if ( $term_data = get_option( "category_$term" ) ) {
				if ( ! empty( $term_data['wpex_term_layout'] ) ) {
					$layout = $term_data['wpex_term_layout'];
				}
			}
		}

	}

	// All else
	else {
		$layout = $default;
	}

	// WooCommerce layouts (added here to provide support for vanilla WooCommerce)
	if ( wpex_is_woocommerce_active() ) {
		if ( wpex_is_woo_shop() ) {
			$layout = get_theme_mod( 'woo_shop_layout', 'full-width' );
		} elseif ( wpex_is_woo_tax() ) {
			$layout = get_theme_mod( 'woo_shop_layout', 'full-width' );
		} elseif ( wpex_is_woo_single() ) {
			$layout = get_theme_mod( 'woo_product_layout', 'full-width' );
		} elseif ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$layout = 'full-width';
		}
	}

	// Apply filters
	$layout = apply_filters( 'wpex_post_layout_class', $layout, $instance );

	// Class should never be empty
	if ( empty( $layout ) ) {
		$layout = $default;
	}

	// Return class
	return $layout;

}

/**
 * Check if primary wrapper should have bottom margin.
 *
 * @since 5.0
 */
function wpex_has_primary_bottom_spacing() {

	$check = true;

	$post_id = wpex_get_current_post_id();

	// Disable on single post types when using page builders or dynamic templates.
	if ( $post_id && $check ) {

		// Disable on single posts
		if ( is_singular( 'post' ) ) {
			$check = false;
		}

		// Disable on WooCommerce products
		elseif ( function_exists( 'is_product' ) && is_product() ) {
			$check = false;
		}

		// Disable on elementor pages
		elseif ( WPEX_ELEMENTOR_ACTIVE && get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
			$check = false;
		}

		// Disable when using WPBakery
		elseif ( wpex_has_post_wpbakery_content( $post_id ) ) {

			$page_template = get_page_template_slug( $post_id );

			if ( ! in_array( $page_template, array( 'templates/blog.php', 'templates/blog-content-above.php' ) ) ) {
				$check = false;
			}

			if ( function_exists( 'is_shop' ) && is_shop() ) {
				$check = true; // make sure the shop has padding
			}

		}

		// Disable when using dynamic templates
		elseif ( wpex_get_singular_template_content( get_post_type( $post_id ) ) ) {
			$check = false;
		}

	}

	// Return filters and return check
	return (bool) apply_filters( 'wpex_has_primary_bottom_spacing', $check );

}