<?php
/**
 * Sidebar frontend functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 * @todo create new class to return correct sidebar so we can clean up the code a bit.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return sidebar class
 *
 * @since 5.0
 */
function wpex_sidebar_class() {

	$classes = array(
		'sidebar-container',
		'sidebar-primary',
	);

	$classes = (array) apply_filters( 'wpex_sidebar_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}

/**
 * Return sidebar inner class
 *
 * @since 5.0
 */
function wpex_sidebar_inner_class() {

	$classes = array(
		'wpex-mb-40',
	);

	$classes = (array) apply_filters( 'wpex_sidebar_class', $classes );

	if ( $classes ) {
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}

}


/**
 * Returns the correct sidebar ID.
 *
 * @since  1.0.0
 */
function wpex_get_sidebar( $sidebar = '', $post_id = '' ) {
	$instance    = ''; // @todo deprecate instance variable.
	$fallback    = apply_filters( 'wpex_sidebar_has_fallback', true );
	$sidebar     = ( ! $sidebar && $fallback ) ? 'sidebar' : $sidebar;

	// Page Sidebar
	if ( is_singular() ) {

		$post_type = get_post_type();

		$instance = 'singular_' . $post_type;

		// Pages
		if ( 'page' == $post_type
			&& ! ( is_page_template( 'templates/blog.php' ) || is_page_template( 'templates/blog-content-above.php' ) )
		) {

			if ( get_theme_mod( 'pages_custom_sidebar', true ) ) {
				$sidebar = 'pages_sidebar';
			}

		}

		// Posts
		if ( 'post' == $post_type ) {

			if ( get_theme_mod( 'blog_custom_sidebar', false ) ) {
				$sidebar = 'blog_sidebar';
			}

		}

	// Archives
	} else {

		$instance = 'archive';

		// Search Sidebar
		if ( is_search() ) {
			$instance = 'search';
			if ( get_theme_mod( 'search_custom_sidebar', true ) ) {
				$sidebar = 'search_sidebar';
			}
		}

		// Blog sidebar
		elseif ( get_theme_mod( 'blog_custom_sidebar', false ) && wpex_is_blog_query() ) {
			$instance = 'wpex_is_blog_query';
			$sidebar = 'blog_sidebar';
		}

		// 404
		elseif ( is_404() ) {
			$instance = '404';
			if ( get_theme_mod( 'pages_custom_sidebar', true ) ) {
				$sidebar = 'pages_sidebar';
			}
		}

	}

	// WooCommerce sidebar
	if ( function_exists( 'is_woocommerce' ) && get_theme_mod( 'woo_custom_sidebar', true ) && is_woocommerce() ) {
		$sidebar = 'woo_sidebar';
	}

	// Post types Unlimited checks
	if ( WPEX_PTU_ACTIVE ) {

		if ( is_singular() ) {
			$ptu_check = wpex_get_ptu_type_mod( $post_type, 'custom_sidebar' );
			if ( $ptu_check ) {
				$sidebar = $ptu_check;
			}
		}

		if ( is_post_type_archive() ) {
			$ptu_check = wpex_get_ptu_type_mod( get_query_var( 'post_type' ), 'custom_sidebar' );
			if ( $ptu_check ) {
				$sidebar = $ptu_check;
			}
		}

		if ( is_tax() ) {
			$ptu_check = wpex_get_ptu_tax_mod( get_query_var( 'taxonomy' ), 'sidebar' );
			if ( $ptu_check ) {
				$sidebar = $ptu_check;
			} else {
				$ptu_check = wpex_get_ptu_type_mod( get_post_type(), 'custom_sidebar' );
				if ( $ptu_check ) {
					$sidebar = $ptu_check;
				}
			}

		}

	}

	/***
	 * FILTER    => Add filter for tweaking the sidebar display via child theme's
	 * IMPORTANT => Must be added before meta options so that it doesn't take priority
	 ***/
	$sidebar = apply_filters( 'wpex_get_sidebar', $sidebar, $instance );

	// Get current post id
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	// Check meta option after filter so it always overrides
	if ( $meta = get_post_meta( $post_id, 'sidebar', true ) ) {
		$sidebar = $meta;
	}

	// Get sidebar based on current post primary category setting
	if ( function_exists( 'get_term_meta' ) ) {

		$meta_sidebar = '';

		if ( is_singular() && 'page' != $post_type ) {

			$meta = '';
			$taxonomies = get_object_taxonomies( $post_type, 'names' );

			// Loop through taxonomies to see if a specific sidebar is set for a taxonomy
			if ( $taxonomies && is_array( $taxonomies ) ) {

				// Check if post has a primary taxonomy and if so add it to the top of the list to check
				$primary_tax = wpex_get_post_primary_taxonomy();

				if ( $primary_tax && in_array( $primary_tax, $taxonomies ) ) {

					if ( ( $key = array_search( $primary_tax, $taxonomies ) ) !== false) {
						unset( $taxonomies[$key] );
						array_unshift( $taxonomies , $primary_tax );
					}

				}

				foreach( $taxonomies as $taxonomy ) {
					if ( $meta ) {
						break;
					}
					$primary_term = wpex_get_post_primary_term( $post_id, $taxonomy );
					if ( $primary_term ) {
						$meta = get_term_meta( $primary_term->term_id, 'wpex_sidebar', true );
						if ( $meta ) {
							break;
						}
					}
					$terms = get_the_terms( get_the_ID(), $taxonomy );
					if ( $terms ) {
						foreach ( $terms as $term ) {
							if ( $meta ) {
								break;
							}
							$meta = get_term_meta( $term->term_id, 'wpex_sidebar', true );
						}
					}
				}

				if ( $meta ) {
					$meta_sidebar = $meta;
				}

			} // end taxonomies check

		} // end singular check

		// Taxonomies
		if ( is_tax() || is_category() || is_tag() ) {
			$term_id = get_queried_object_id();
			if ( $term_id && $meta = get_term_meta( $term_id, 'wpex_sidebar', true ) ) {
				$meta_sidebar = $meta;
			}
		}

		// Check if taxonomy sidebar is active and exits and if not fallback to filter
		global $wp_registered_sidebars;
		if ( is_array( $wp_registered_sidebars )
			&& array_key_exists( $meta_sidebar, $wp_registered_sidebars )
			&& is_active_sidebar( $meta_sidebar )
		) {
			$sidebar = $meta_sidebar;
		}

	} // end get_term_meta function_exists check

	// Never show empty sidebar
	if ( $sidebar && $fallback && ! is_active_sidebar( $sidebar ) ) {
		$sidebar = 'sidebar';
	}

	// Return the correct sidebar
	return $sidebar;

}