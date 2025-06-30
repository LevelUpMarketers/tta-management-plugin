<?php
/**
 * Total theme breadcrumbs.
 *
 * @package Total WordPress Theme
 * @version 5.0.6
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Breadcrumbs' ) ) {

	class WPEX_Breadcrumbs {

		/**
		 * HTML output for breadcrumbs.
		 *
		 * @var string
		 */
		public $output = '';

		/**
		 * Main constructor.
		 *
		 * @since 3.0.9
		 */
		public function __construct() {

			$gcrumbs = $this->generate_crumbs();

			if ( $gcrumbs ) {

				$has_container = wpex_has_breadcrumbs_container();

				$this->output .= '<nav class="' . esc_attr( self::wrap_classes() ) . '"' . wpex_get_aria_landmark( 'breadcrumbs' ) . '>';

					if ( $has_container ) {
						$this->output .= '<div class="container">';
					}

					$this->output .= '<span class="breadcrumb-trail wpex-clr" itemscope itemtype="http://schema.org/BreadcrumbList">';

						$this->output .= $gcrumbs;

					$this->output .= '</span>';

					if ( $has_container ) {
						$this->output .= '</div>';
					}

				$this->output .= '</nav>';

			}

		}

		/**
		 * Outputs the generated breadcrumbs.
		 *
		 * @since      3.0.9
		 * @deprecated 3.6.0 // Will be removed at some point since it's not needed
		 */
		public function display( $echo = true ) {
			return $this->output;
		}

		/* Generates the breadcrumbs and updates the $trail var.
		 *
		 * @since 3.0.9
		 */
		public function generate_crumbs() {

			// Globals
			global $wp_query, $wp_rewrite;

			// Define main variables
			$breadcrumb = '';

			// Home text
			$home_text = wpex_get_translated_theme_mod( 'breadcrumbs_home_title' );
			$home_text = $home_text ? $home_text : esc_html__( 'Home', 'total' );

			// Get trail separator
			$separator = ( $separator = get_theme_mod( 'breadcrumbs_separator' ) ) ? $separator : '&raquo;';

			// Default arguments
			$args = apply_filters( 'wpex_breadcrumbs_args', array(
				'home_text'        => $home_text,
				'home_link'        => home_url( '/' ),
				'separator'        => do_shortcode( wp_strip_all_tags( trim( $separator ) ) ),
				'front_page'       => false,
				'show_parents'     => true,
				'cpt_find_parents' => false,
			) );

			// Extract args for easy variable naming.
			extract( $args );

			/*-----------------------------------------------------------------------------------*/
			/*  - Homepage link
			/*  - Note: can't use get_crumb_html() because text must support shortcodes + html
			/*-----------------------------------------------------------------------------------*/
			$trail['trail_start'] = '<span class="trail-begin" ' . self::get_item_sd_markup() . '><a ' . self::get_link_sd_markup() . ' href="'. esc_url( $home_link ) .'" rel="home"><span itemprop="name">' . do_shortcode( wp_kses_post( $home_text ) ) . '</span></a><meta itemprop="position" content="1" /></span>';

			/*-----------------------------------------------------------------------------------*/
			/*  - Front Page
			/*-----------------------------------------------------------------------------------*/
			if ( is_front_page() && ! $front_page ) {
				$trail = false;
			}

			/*-----------------------------------------------------------------------------------*/
			/*  - Homepage or posts page
			/*-----------------------------------------------------------------------------------*/
			elseif ( is_home() ) {
				$home_page = get_page( $wp_query->get_queried_object_id() );
				if ( is_object( $home_page ) ) {
					$trail = array_merge( $trail, self::get_post_parents( $home_page->post_parent, '' ) );
					$trail['trail_end'] = get_the_title( $home_page->ID );
				} else {
					$trail = false;
				}
			}

			/*-----------------------------------------------------------------------------------*/
			/*  - Singular: Page, Post, Attachment...etc
			/*-----------------------------------------------------------------------------------*/
			elseif ( $wp_query->is_singular ) {

				// Get singular vars
				$post                       = $wp_query->get_queried_object();
				$post_id                    = absint( $wp_query->get_queried_object_id() );
				$post_type                  = $post->post_type;
				$post_type_obj              = get_post_type_object( $post_type );
				$parent                     = $show_parents ? $post->post_parent : '';
				$trail['post_type_archive'] = ''; // Add empty post type trail for custom types

				// If parent is the same as front-page set to empty
				if ( $parent == get_option( 'page_on_front' ) ) {
					$parent = '';
				}

				// Get Post types primary page
				switch ( $post_type ) {

					case 'page':

						// Woo pages
						if ( WPEX_WOOCOMMERCE_ACTIVE ) {

							// Add shop page to cart
							if ( is_cart() || is_checkout() ) {

								// Get shop data
								$shop_data  = self::get_shop_data();
								$shop_url   = $shop_data['url'];
								$shop_title = $shop_data['title'];

								// Add shop link
								if ( $shop_url && $shop_title ) {
									$trail['shop'] = self::get_crumb_html( $shop_title, $shop_url, 'trail-shop' );
								}

							}

							// Add cart to checkout
							if ( apply_filters( 'wpex_breadcrumbs_checkout_cart', false )
								&& is_checkout()
								&& $cart_id = wpex_parse_obj_id( wc_get_page_id( 'cart' ), 'page' )
							) {
								$trail['cart'] = self::get_crumb_html( get_the_title( $cart_id ), get_permalink( $cart_id ), 'trail-cart' );
							}

						}

						// Add page parents
						if ( $parent ) {
							$trail = array_merge( $trail, self::get_post_parents( $parent ) );
						}

						break;

					case 'post';

						// Main Blog URL
						if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'blog_page' ), 'page' ) ) {
							if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
								$trail = array_merge( $trail, $parents );
							} else {
								$trail['blog'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-blog-url' );
							}
						}

						// Add URL based on posts page
						elseif ( $page_for_posts = get_option( 'page_for_posts' ) ) {
							$trail['blog'] = self::get_crumb_html( get_the_title( $page_for_posts ), get_permalink( $page_for_posts ), 'trail-blog-url' );
						}

						// Categories
						if ( $terms = self::get_post_terms( 'category' ) ) {
							$trail['categories'] = '<span class="trail-post-categories">' . $terms . '</span>';
						}

						break;

					case 'tribe_events';

						if ( function_exists( 'tribe_get_events_link' ) ) {
							if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'tribe_events_main_page' ), 'page' ) ) {
								$title = get_the_title( $page_id );
								$link  = get_permalink( $page_id );
							} else {
								$title = esc_html__( 'All Events', 'total' );
								$link  = tribe_get_events_link();
							}
							$trail['tribe_events'] = self::get_crumb_html( $title, $link, 'trail-all-events' );
						}

						break;

					case 'portfolio':

						// Portfolio main page
						if ( ! empty( $post_type_obj->has_archive ) ) {
							$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
						} else {
							if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'portfolio_page' ), 'page' ) ) {
								if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
									$trail = array_merge( $trail, $parents );
								} else {
									$trail['portfolio'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-portfolio-url' );
								}
							}
						}

						// Portfolio Categories
						if ( $terms = self::get_post_terms( 'portfolio_category' ) ) {
							$trail['categories'] = '<span class="trail-post-categories">'. $terms .'</span>';
						}

						break;

					case 'staff':

						// Staff main page
						if ( ! empty( $post_type_obj->has_archive ) ) {
							$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
						} else {
							if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'staff_page' ), 'page' ) ) {
								if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
									$trail = array_merge( $trail, $parents );
								} else {
									$trail['staff'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-staff-url' );
								}
							}
						}

						// Staff Categories
						if ( $terms = self::get_post_terms( 'staff_category' ) ) {
							$trail['categories'] = '<span class="trail-post-categories">' . $terms . '</span>';
						}

						break;

					case 'testimonials':

						// Testimonials main page
						if ( ! empty( $post_type_obj->has_archive ) ) {
							$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
						} else {
							if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'testimonials_page' ), 'page' ) ) {
								if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
									$trail = array_merge( $trail, $parents );
								} else {
									$trail['testimonials'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-testimonials-url' );
								}
							}
						}

						// Testimonials Categories
						if ( $terms = self::get_post_terms( 'testimonials_category' ) ) {
							$trail['categories'] = '<span class="trail-post-categories">' . $terms . '</span>';
						}

						break;

					case 'product':

						// Get shop data
						$shop_data  = self::get_shop_data();
						$shop_id    = $shop_data['id'];
						$shop_url   = $shop_data['url'];
						$shop_title = $shop_data['title'];

						// Add shop page to product post
						if ( $shop_url && $shop_title && $shop_id != get_option( 'page_on_front' ) ) {
							$trail['shop'] = self::get_crumb_html( $shop_title, $shop_url, 'trail-shop' );
						}

						// Add categories to product post
						if ( $terms = self::get_post_terms( 'product_cat' ) ) {
							$trail['categories'] = '<span class="trail-post-categories">' . $terms . '</span>';
						}

						// Add cart to product post
						if ( apply_filters( 'wpex_breadcrumbs_single_product_cart', false )
							&& $page_id = wpex_parse_obj_id( wc_get_page_id( 'cart' ) )
						) {
							$trail['cart'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-cart' );
						}

						break;

					default:

						// If archive page exists add to trail
						if ( ! empty( $post_type_obj->has_archive ) ) {

							if ( ! is_singular( 'product' ) ) {
								$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
							}

						} elseif ( empty( $ptu_check ) && $cpt_find_parents ) {
							$trail = array_merge( $trail, self::cpt_find_parents( $post_type_obj ) );
						}

						// Check PTU settings which override the default post_type_archive
						if ( WPEX_PTU_ACTIVE ) {

							$ptu_check = wpex_get_ptu_type_mod( $post_type, 'main_page' );

							if ( $ptu_check && get_post_status( $ptu_check ) ) {
								$trail['post_type_archive'] = self::get_crumb_html( get_the_title( $ptu_check ), get_permalink( $ptu_check ) );
							}


						}

						// Add post type parent posts
						if ( $parent ) {
							$trail = array_merge( $trail, self::get_post_parents( $parent ) );
						}

						// Get PTU categories
						if ( WPEX_PTU_ACTIVE ) {

							$ptu_main_tax = wpex_get_ptu_type_mod( $post_type, 'main_taxonomy' );

							if ( $ptu_main_tax && taxonomy_exists( $ptu_main_tax ) ) {

								if ( $terms = self::get_post_terms( $ptu_main_tax ) ) {
									$trail['categories'] = '<span class="trail-categories">' . $terms . '</span>';
								}

							}

						}

						// Add empty category to array for addition of taxonomies via filters
						if ( empty( $trail['categories'] ) ) {
							$trail['categories'] = '';
						}

						break;

				} // End $post_type switch

				// End trail with post title
				if ( $post_title = get_the_title( $post_id ) ) {
					$trim_title = get_theme_mod( 'breadcrumbs_title_trim' );
					$show_title = true;
					if ( isset( $trim_title ) && '0' === $trim_title ) {
						$show_title = false;
					}
					if ( $show_title ) {
						if ( $trim_title ) {
							$post_title = wp_trim_words( $post_title, $trim_title );
						}
						$trail['trail_end'] = $post_title;
					}
				}

			}

			/*-----------------------------------------------------------------------------------*/
			/*  - ALL Archives
			/*-----------------------------------------------------------------------------------*/
			elseif ( is_archive() ) {

				/*-----------------------------------------------------------------------------------*/
				/*  - Post Type Archive
				/*-----------------------------------------------------------------------------------*/
				if ( is_post_type_archive() ) {

					// Shop Archive
					if ( function_exists( 'is_shop' ) && is_shop() ) {

						if ( apply_filters( 'wpex_breadcrumbs_shop_cart', false ) ) {
							global $woocommerce;
							if ( sizeof( $woocommerce->cart->cart_contents ) > 0 ) {
								if ( $page_id = wpex_parse_obj_id( wc_get_page_id( 'cart' ), 'page' ) ) {
									$trail['cart'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-cart' );
								}
							}
						}

						// Store shop data
						$shop_data = self::get_shop_data();

						// Add shop page title to trail end
						if ( $shop_data['title'] ) {
							$trail['trail_end'] = $shop_data['title'];
						} else {
							$trail['trail_end'] = post_type_archive_title( '', false );
						}

					}

					// Topics Post Type Archive
					elseif ( is_post_type_archive( 'topic' ) ) {

						if ( is_object( $forum_obj = get_post_type_object( 'forum' ) ) ) {

							if ( $forums_link = get_post_type_archive_link( 'forum' ) ) {
								$trail['forum'] = self::get_crumb_html( $forum_obj->labels->name, $forums_link, 'trail-forum' );
							}

							$trail['trail_end'] = $forum_obj->labels->name;

						}

					// All other post type archives
					} else {

						// Add post type name to trail end
						$post_type_obj      = get_post_type_object( get_query_var( 'post_type' ) );
						$trail['trail_end'] = $post_type_obj->labels->name;

					}

				}

				/*-----------------------------------------------------------------------------------*/
				/*  - Taxonomy Archive
				/*-----------------------------------------------------------------------------------*/
				elseif ( ! is_search() && ( is_tax() || is_category() || is_tag() ) ) {

					$post_type     = get_post_type();
					$post_type_obj = isset( $post_type ) ? get_post_type_object( $post_type ) : '';

					// Link to main portfolio page
					if ( wpex_is_portfolio_tax() ) {
						if ( ! empty( $post_type_obj->has_archive ) ) {
						$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
						} else {
							if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'portfolio_page' ), 'page' ) ) {
								if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
									$trail = array_merge( $trail, $parents );
								} else {
									$trail['portfolio'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-portfolio-url' );
								}
							}
						}
					}

					// Link to main staff page
					elseif ( wpex_is_staff_tax() ) {
						if ( ! empty( $post_type_obj->has_archive ) ) {
						$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
						} else {
							if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'staff_page' ), 'page' ) ) {
								if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
									$trail = array_merge( $trail, $parents );
								} else {
									$trail['staff'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-staff-url' );
								}
							}
						}
					}

					// Testimonials Tax
					elseif ( wpex_is_testimonials_tax() ) {
						if ( ! empty( $post_type_obj->has_archive ) ) {
						$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
						} else {
							if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'testimonials_page' ), 'page' ) ) {
								if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
									$trail = array_merge( $trail, $parents );
								} else {
									$trail['testimonials'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-testimonials-url' );
								}
							}
						}
					}

					// Display main blog page on post archives
					elseif ( is_category() || is_tag()  ) {
						$blog_page = wpex_parse_obj_id( get_theme_mod( 'blog_page' ), 'page' );
						if ( $blog_page ) {
							if ( $show_parents && $parents = self::get_post_parents( $blog_page ) ) {
								$trail = array_merge( $trail, $parents );
							} else {
								$trail['blog'] = self::get_crumb_html( get_the_title( $blog_page ), get_permalink( $blog_page ), 'trail-blog-url' );
							}
						} elseif ( $page_for_posts = get_option( 'page_for_posts' ) ) {
							$trail['blog'] = self::get_crumb_html( get_the_title( $page_for_posts ), get_permalink( $page_for_posts ), 'trail-blog-url' );
						}
					}

					// Woo Product Tax
					elseif ( wpex_is_woo_tax() ) {

						// Get shop data
						$shop_data  = self::get_shop_data();
						$shop_url   = $shop_data['url'];
						$shop_title = $shop_data['title'];

						// Add shop page to product post
						if ( $shop_url && $shop_title ) {
							$trail['shop'] = self::get_crumb_html( $shop_title, $shop_url, 'trail-shop' );
						}

					}

					// For all other taxonomies get post type archive URL or locate a page
					else {

						if ( ! empty( $post_type_obj->has_archive ) ) {
							$trail['post_type_archive'] = self::get_crumb_html( $post_type_obj->labels->name, get_post_type_archive_link( $post_type ), 'trail-type-archive' );
						}

						// No archive defined so lets try and locate a parent cpt page
						elseif ( $cpt_find_parents ) {
							$trail = array_merge( $trail, self::cpt_find_parents( $post_type_obj ) );
						}


					}

					// Add term parents
					$term = $wp_query->get_queried_object();
					if ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) {
						$trail = array_merge( $trail, $this->get_term_parents( $term ) );
					}

					// Add term name to trail end
					$trail['trail_end'] = $term->name;

				}

				/*-----------------------------------------------------------------------------------*/
				/*  - Author Archive
				/*-----------------------------------------------------------------------------------*/
				elseif ( is_author() ) {

					// Add main blog
					if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'blog_page' ), 'page' ) ) {
						if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
							$trail = array_merge( $trail, $parents );
						} else {
							$trail['blog'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-blog-url' );
						}
					}

					// Add the author display name to end
					$trail['trail_end'] = get_the_author_meta( 'display_name', get_query_var( 'author' ) );

				}

				/*-----------------------------------------------------------------------------------*/
				/*  - Time Archive
				/*-----------------------------------------------------------------------------------*/
				elseif ( is_time() ) {

					// Display minute and hour
					if ( get_query_var( 'minute' ) && get_query_var( 'hour' ) ) {
						$trail['trail_end'] = get_the_time( 'g:i a' );
					}

					// Display minute only
					elseif ( get_query_var( 'minute' ) ) {
						$trail['trail_end'] = sprintf( esc_html__( 'Minute %1$s', 'total' ), get_the_time( 'i' ) );
					}

					// Display hour only
					elseif ( get_query_var( 'hour' ) ) {
						$trail['trail_end'] = get_the_time( 'g a' );
					}

				}

				/*-----------------------------------------------------------------------------------*/
				/*  - Date Archive
				/*-----------------------------------------------------------------------------------*/
				elseif ( is_date() ) {

					// Add main blog
					if ( $page_id = wpex_parse_obj_id( get_theme_mod( 'blog_page' ), 'page' ) ) {
						if ( $show_parents && $parents = self::get_post_parents( $page_id ) ) {
							$trail = array_merge( $trail, $parents );
						} else {
							$trail['blog'] = self::get_crumb_html( get_the_title( $page_id ), get_permalink( $page_id ), 'trail-blog-url' );
						}
					} elseif ( $page_for_posts = get_option( 'page_for_posts' ) ) {
						$trail['blog'] = self::get_crumb_html( get_the_title( $page_for_posts ), get_permalink( $page_for_posts ), 'trail-blog-url' );
					}

					// If $front is set check for parents
					if ( $wp_rewrite->front ) {
						$trail = array_merge( $trail, self::get_post_parents( '', $wp_rewrite->front ) );
					}

					// Day archive
					if ( is_day() ) {

						// Link to year archive
						$title = date_i18n( 'Y', strtotime( get_the_time( 'd.m.Y' ) ) );
						$link  = get_year_link( get_the_time( 'Y' ) );
						$trail['year'] = self::get_crumb_html( $title, $link, 'trail-year' );

						// Link to month archive
						$title = date_i18n( 'F', strtotime( get_the_time( 'd.m.Y' ) ) );
						$link  = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
						$trail['month'] = self::get_crumb_html( $title, $link, 'trail-month' );

						// Add time to end
						$trail['trail_end'] = sprintf( esc_html__( 'Daily Archives: %s', 'total' ), get_the_date() );

					}

					// Week archive
					if ( get_query_var( 'w' ) ) {

						// Link to year archive
						$title = date_i18n( 'Y', strtotime( get_the_time( 'd.m.Y' ) ) );
						$link  = get_year_link( get_the_time( 'Y' ) );
						$trail['year'] = self::get_crumb_html( $title, $link, 'trail-year' );

						// Add week to end
						$trail['trail_end'] = sprintf( esc_html__( 'Week %1$s', 'total' ), get_the_time( 'W' ) );

					}

					// Month archive
					if ( is_month() ) {

						// Link to year archive
						$title = date_i18n( 'Y', strtotime( get_the_time( 'd.m.Y' ) ) );
						$link  = get_year_link( get_the_time( 'Y' ) );
						$trail['year'] = self::get_crumb_html( $title, $link, 'trail-year' );

						// Add month to end
						$trail['trail_end'] = esc_html( sprintf( esc_html__( 'Monthly Archives: %s', 'total' ), get_the_date( 'F Y' ) ) );

					}

					// Year archive
					if ( is_year() ) {
						$trail['trail_end'] = esc_html( sprintf( esc_html__( 'Yearly Archives: %s', 'total' ), get_the_date( 'Y' ) ) );
					}

				}
			}

			/*-----------------------------------------------------------------------------------*/
			/*  - Search
			/*-----------------------------------------------------------------------------------*/
			elseif ( is_search() ) {
				$trail['trail_end'] = esc_html__( 'Search Results', 'total' );
			}

			/*-----------------------------------------------------------------------------------*/
			/*  - 404
			/*-----------------------------------------------------------------------------------*/
			elseif ( is_404() ) {
				$trail['trail_end'] = esc_html__( '404 Error Page', 'total' );;
			}

			/*-----------------------------------------------------------------------------------*/
			/*  - Tribe Calendar Month
			/*-----------------------------------------------------------------------------------*/
			elseif ( function_exists( 'tribe_is_month' ) && tribe_is_month() ) {
				$trail['trail_end'] = esc_html__( 'Events Calendar', 'total' );
			}

			/*-----------------------------------------------------------------------------------*/
			/*  - Create and return the breadcrumbs
			/*-----------------------------------------------------------------------------------*/

			// Add a before trail_end empty item for easier addition of items before the title
			if ( isset( $trail['trail_end'] ) ) {
				$trail_end = $trail['trail_end'];
				unset( $trail['trail_end'] );
				$trail['pre_trail_end'] = '';
				$trail['trail_end'] = $trail_end;
			}

			// Apply filters so developers can alter the trail
			$trail = apply_filters( 'wpex_breadcrumbs_trail', $trail );

			// Return trail
			if ( $trail && is_array( $trail ) ) {

				// Remove empty vals
				$trail = array_filter( $trail );

				// Add to trail
				if ( isset( $trail['trail_end'] ) ) {
					$trail_end_allowed_tags = apply_filters( 'wpex_breadcrumbs_trail_end_allowed_html_tags', array(
						'abbr' => array(),
					) );
					$trail['trail_end'] = '<span class="trail-end">' . wp_kses( $trail['trail_end'], $trail_end_allowed_tags ) . '</span>';
				}

				// Join all trail items into a string
				$all_count = count( $trail );
				$count     = 0;
				foreach ( $trail as $key => $val ) {
					$count++;
					$val = str_replace( '</a></span>', '<meta itemprop="position" content="' . absint( $count ) . '" /></a></span>', $val );
					$breadcrumb .= $val;
					if ( $all_count != $count ) {
						$breadcrumb .= '<span class="sep sep-' . esc_attr( $count ) . '"> ' . $separator . ' </span>';
					}
				}

			}

			// Return crumbs
			return $breadcrumb;

		} // End generate_crumbs

		/**
		 * Generate single crumb html.
		 *
		 * @since 3.6.0
		 */
		public static function get_crumb_html( $label, $link, $class = '', $rel = '' ) {
			if ( ! $link ) {
				return; // Link required
			}
			$item_markup = self::get_item_sd_markup();
			$class = $class ? ' class="' . esc_attr( $class ) . '"': '';
			$rel   = $rel ? ' rel="' . esc_attr( $rel ) . '"': '';
			return '<span ' . $item_markup . $class . $rel . '><a '. self::get_link_sd_markup() . ' href="' . esc_url( $link ) . '"><span itemprop="name">' . wp_strip_all_tags( $label ) . '</span></a></span>';
		}

		/**
		 * Returns correct item scope.
		 *
		 * @since 4.4
		 */
		public static function get_item_sd_markup() {
			return 'itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"';
		}

		/**
		 * Returns correct thing scope.
		 *
		 * @since 4.4
		 */
		public static function get_link_sd_markup() {
			return 'itemprop="item"';
		}

		/**
		 * Display terms.
		 *
		 * @since 1.0.0
		 */
		public static function get_post_terms( $taxonomy = '' ) {

			// Return null if disabled
			if ( get_theme_mod( 'breadcrumbs_disable_taxonomies', false ) ) {
				return null;
			}

			// Make sure taxonomy exists
			if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
				return null;
			}

			// Make sure taxonomy is public
			$taxonomy_obj = get_taxonomy( $taxonomy );

			if ( ! is_wp_error( $taxonomy_obj )
				&& is_object( $taxonomy_obj )
				&& false === $taxonomy_obj->publicly_queryable
			) {
				return null;
			}

			// Terms empty by default
			$terms = apply_filters( 'wpex_breadcrumbs_terms', null, $taxonomy );

			// Return terms if filtered
			if ( $terms ) {
				return $terms;
			}

			// Get terms
			$list_terms = array();
			$args       = apply_filters( 'wpex_breadcrumbs_wp_get_post_terms_args', array() ); // @todo rename to "get_the_terms" instead of "wp_get_post_terms"
			$terms      = get_the_terms( get_the_ID(), $taxonomy, $args );

			// Return if no terms are found
			if ( ! $terms ) {
				return;
			}

			// Itemscope
			$itemscope = self::get_item_sd_markup();

			// Check if it should display all terms or only first term
			$show_all_terms = get_theme_mod( 'breadcrumbs_first_cat_only', true ) ? false : true; // newer setting
			$show_all_terms = apply_filters( 'wpex_breadcrumbs_terms_all', $show_all_terms ); // older filter

			// Loop through terms
			if ( $show_all_terms ) {

				foreach ( $terms as $term ) {

					// Translation fixes
					$translated_term = wpex_parse_obj_id( $term->term_id, 'term', $taxonomy );
					if ( $term->term_id != $translated_term ) {
						$term = get_term( $translated_term, $taxonomy );
					}

					$list_terms[] = self::get_crumb_html( $term->name, get_term_link( $term->term_id, $taxonomy ), 'term-' . $term->term_id );

				}

			}

			// Return first term only
			// @todo update to check for primary category.
			else {

				$primary_term = wpex_get_post_primary_term( get_the_ID(), $taxonomy );

				if ( $primary_term && ! is_wp_error( $primary_term ) ) {
					$term = $primary_term;
				} else {
					$term = $terms[0];
				}

				$term_link = get_term_link( $term->term_id, $taxonomy );

				$term_class = 'term-' . sanitize_html_class( $term->term_id );

				$list_terms[] = self::get_crumb_html( $term->name, $term_link, $term_class );

			}

			// Sanitize terms
			$terms = ! empty( $list_terms ) ? implode( wpex_inline_list_sep( 'breadcrumbs' ), $list_terms ) : '';

			// Turn into comma seperated string
			return $terms;

		}

		/**
		 * Tries to locate a custom post type parent page based on the path.
		 *
		 * @since 4.4
		 */
		public static function cpt_find_parents( $post_type_obj = '' ) {

			if ( ! $post_type_obj ) {
				return $trail;
			}

			global $wp_query, $wp_rewrite;
			$trail = array();
			$path  = '';

			// Add $front to the path
			if ( $post_type_obj->rewrite['with_front'] && $wp_rewrite->front ) {
				$path .= trailingslashit( $wp_rewrite->front );
			}

			// Add slug to $path
			if ( ! empty( $post_type_obj->rewrite['slug'] ) ) {
				$path .= $post_type_obj->rewrite['slug'];
			}

			// If we can't find a path then return trail
			if ( ! $path ) {
				return $trail;
			}

			// Get parent post by the path.
			$parent_page = get_page_by_path( $path );

			// Try to get by title with single word
			if ( empty( $parent_page ) ) {
				$parent_page = get_page_by_title( $path );
			}

			// Try again based on title with multiple words
			if ( empty( $parent_page ) ) {
				$parent_page = get_page_by_title ( str_replace( array('-', '_'), ' ', $path ) );
			}

			// Parent is found so lets return the ID
			if ( ! empty( $parent_page ) ) {
				$post_id = $parent_page->ID;
			}

			if ( $parent_page ) {
				$trail[] = self::get_crumb_html( get_the_title( $post_id ), get_permalink( $post_id ), 'trail-parent' );
			}

			// Return parent posts
			return $trail;

		}

		/**
		 * Searches for post parents and adds them to the trail.
		 *
		 * @since 1.0.0
		 */
		public static function get_post_parents( $post_id = '' ) {

			// Define new trail var
			$trail = array();

			// Return empty array if the post id and path are both empty
			if ( empty( $post_id ) ) {
				return $trail;
			}

			// Define empty parents array
			$parents = array();

			// Loop through and add parents to parents array
			while ( $post_id ) {

				// Get the post by ID.
				$post = get_post( $post_id );

				// Add the post link to the array
				$parents[] = self::get_crumb_html( get_the_title( $post_id ), get_permalink( $post_id ), 'trail-parent' );

				// Set the parent post's parent to the post ID.
				$post_id = $post ? $post->post_parent : '';

			}

			// If parent pages are found reverse order so they are correct
			if ( $parents ) {
				$trail = array_reverse( $parents );
			}

			// Return parent posts
			return $trail;

		}

		/**
		 * Searches for term parents and adds them to the trail.
		 *
		 * @since 3.0.9
		 */
		private function get_term_parents( $term = '' ) {

			// New trail
			$trail = array();

			// Term check
			if ( empty( $term->taxonomy ) ) {
				return $trail;
			}

			// Define parents array and get term taxonomy
			$parents  = array();
			$taxonomy = $term->taxonomy;

			// Get parents
			if ( is_taxonomy_hierarchical( $taxonomy ) && $term->parent != 0 ) {

				// While there is a parent ID, add the parent term link to the $parents array.
				$count='';
				while ( $term->parent != 0 ) {
					$count ++;

					// Get term
					$term = get_term( $term->parent, $taxonomy );

					// Add the formatted term link to the array of parent terms.
					$parents['parent_term_'. $count ] = self::get_crumb_html( $term->name, get_term_link( $term, $taxonomy ), 'trail-parent-term' );

				}

				// If we have parent terms, reverse the array to put them in the proper order for the trail.
				if ( ! empty( $parents ) ) {
					$trail = array_reverse( $parents );
				}

			}

			// Return the trail of parent terms.
			return $trail;

		} // End get_term_parents

		/**
		 * Get the parent category if only one term exists for the post
		 *
		 * @since 3.3.2
		 */
		public static function get_singular_first_cat_parents( $taxonomy = '' ) {

			// Default empty trail
			$trail = array();

			// Make sure taxonomy exists
			if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
				return null;
			}

			// Get terms
			$terms = get_the_terms( get_the_ID(), $taxonomy );

			// Get parent
			if ( $terms && isset( $terms[0] ) and 1 == count( $terms ) ) {
				$term = get_term( $terms[0] );
				$trail = self::get_term_parents( $term );
			}

			// Return trail
			return $trail;

		}

		/**
		 * Gets Woo Shop data.
		 *
		 * @since 3.0.9
		 */
		public static function get_shop_data( $return = '' ) {

			// Define data
			$data = array(
				'id'    => '',
				'url'   => '',
				'title' => esc_html__( 'Shop', 'total' ),
			);

			// If wc_get_page_id function doesn't exist return empty data array
			if ( ! function_exists( 'wc_get_page_id' ) ) {
				return $data;
			}

			// Get Woo page ID
			$id = intval( wpex_parse_obj_id( wc_get_page_id( 'shop' ), 'page' ) );

			// Get data from ID
			if ( $id ) {
				$data['id']    = $id;
				$data['url']   = get_permalink( $id );
				$data['title'] = get_the_title( $id );
			}

			// Apply filters to title
			$data['title'] = apply_filters( 'wpex_breadcrumbs_shop_title', $data['title'] );

			// Return data
			return $data;

		}

		/**
		 * Returns breadcrumbs classes.
		 *
		 * @since 4.0
		 * @todo convert into function wpex_breadcrumbs_class? (keep this method as legacy incase anyone used it)
		 */
		public static function wrap_classes() {

			// Get customizer options
			$visibility = get_theme_mod( 'breadcrumbs_visibility' );
			$padding_y  = get_theme_mod( 'breadcrumbs_py' );
			$margin_t   = get_theme_mod( 'breadcrumbs_mt' );
			$margin_b   = get_theme_mod( 'breadcrumbs_mb' );

			// Breadcrumbs classes
			$classes = array(
				'site-breadcrumbs',
			);

			// Position class
			$classes[] = 'position-' . sanitize_html_class( wpex_breadcrumbs_position() );

			// Visibility
			if ( $visibility ) {
				$classes[] = sanitize_html_class( $visibility );
			}

			// Utility Classes start here
			$classes[] = 'wpex-text-sm';

			// Add container settings
			if ( wpex_has_breadcrumbs_container() ) {

				//$classes[] = 'wpex-bg-gray-100';

				if ( ! $padding_y ) {
					$classes[] = 'wpex-py-15';
				}

			}

			// Vertical Padding
			if ( $padding_y ) {
				$classes[] = 'wpex-py-' . sanitize_html_class( absint( $padding_y ) );
			}

			// Top margin
			if ( $margin_t ) {
				$classes[] = 'wpex-mt-' . sanitize_html_class( absint( $margin_t ) );
			}

			// Bottom margin
			if ( $margin_b ) {
				$classes[] = 'wpex-mb-' . sanitize_html_class( absint( $margin_b ) );
			}

			// Apply filters to classes
			$classes = (array) apply_filters( 'wpex_breadcrumbs_classes', $classes );

			// Return classes
			return implode( ' ', array_unique( $classes ) );

		}

	}

}