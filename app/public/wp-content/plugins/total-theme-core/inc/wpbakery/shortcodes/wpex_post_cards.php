<?php
/**
 * Post Cards Shortcode
 *
 * @package TotalThemeCore
 * @version 1.2.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Post_Cards_Shortcode' ) ) {

	class WPEX_Post_Cards_Shortcode {

		/**
		 * Define shortcode name.
		 */
		public $shortcode = 'wpex_post_cards';

		/**
		 * Main constructor.
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, array( $this, 'output' ) );

			if ( function_exists( 'vc_lean_map' ) ) {
				add_action( 'vc_after_mapping', array( $this, 'vc_after_mapping' ) );
			}

		}

		/**
		 * Shortcode output => Get template file and display shortcode.
		 */
		public function output( $atts, $content = null ) {

			if ( ! vcex_maybe_display_shortcode( $this->shortcode, $atts ) ) {
				return;
			}

			// Required functions
			if ( ! function_exists( 'vcex_build_wp_query' ) || ! function_exists( 'wpex_get_card' ) ) {
				return;
			}

			// Store orginal atts
			$og_atts = $atts;

			// Run checks before parsing atts
			$entry_count = ! empty( $og_atts['entry_count'] ) ? absint( $og_atts['entry_count'] ) : 0;

			// Parse atts
			$atts = vcex_vc_map_get_attributes( $this->shortcode, $atts, $this );

			// Check if the featured card is enabled
			$has_featured_card = $this->has_featured_card( $atts );
			$is_featured_card_top = $this->is_featured_card_top( $atts );
			$fc_bk = ! empty( $atts['featured_breakpoint'] ) ? $atts['featured_breakpoint'] : 'sm';

			// Set original paged var
			if ( ! empty( $og_atts['paged'] ) ) {
				$atts['paged'] = $og_atts['paged'];
			}

			// Update atts
			if ( empty( $atts['card_style'] ) ) {
				$atts['card_style'] = 'blog-1';
			}

			// Define card args
			$card_args = $this->get_card_args( $atts );

			// Query posts
			$vcex_query = vcex_build_wp_query( $atts );

			$output = '';

			// Display posts if we found any
			if ( $vcex_query->have_posts() ) {

				$wrap_class = array(
					'wpex-post-cards',
					'wpex-post-cards-' . sanitize_html_class( $atts['card_style'] ),
				);

				if ( $has_featured_card ) {
					$wrap_class[] = 'wpex-post-cards-has-featured';
				}

				if ( ! empty( $atts['bottom_margin'] ) ) {
					$wrap_class[] = vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
				}

				if ( ! empty( $atts['el_class'] ) ) {
					$wrap_class[] = wp_strip_all_tags( $atts['el_class'] );
				}

				/*-------------------------------------*/
				/* [ Output Starts Here ]
				/*-------------------------------------*/
				$output .= '<div';

					if ( ! empty( $atts['unique_id'] ) ) {

						$output .= ' id="' . esc_attr( $atts['unique_id'] ) . '"';

					}

					$output .= ' class="' . esc_attr( implode( ' ', $wrap_class ) ) . '"';

				$output .= '>';

					// Add flex wrap
					if ( $has_featured_card && ! $is_featured_card_top ) {

						$inner_classes = array(
							'wpex-post-cards-inner',
						);

						if ( ! $is_featured_card_top ) {
							$inner_classes[] = 'wpex-' . $fc_bk . '-flex';
						}

						if ( 'right' == $atts['featured_location'] ) {
							$inner_classes[] = 'wpex-' . $fc_bk . '-flex-row-reverse';
						}

						$output .= '<div class="' . esc_attr( implode( ' ', $inner_classes ) ) . '">';
					}

					/*-------------------------------------*/
					/* [ Featured Card ]
					/*-------------------------------------*/
					if ( $has_featured_card ) {

						$fc_width = apply_filters( 'wpex_post_cards_featured_width', 50 );
						$fc_width = ! empty( $atts['featured_width'] ) ? $atts['featured_width'] : $fc_width;

						$featured_card_classes = array(
							'wpex-post-cards-featured',
						);

						// Featured card flex classes
						if ( ! $is_featured_card_top ) {
							$featured_card_classes[] = 'wpex-' . $fc_bk . '-w-' . trim( absint( $fc_width ) );
							$featured_card_classes[] = 'wpex-' . $fc_bk . '-flex-shrink-0';
						}

						// Featured card bottom margin
						if ( empty( $atts['featured_divider'] ) || ! $is_featured_card_top ) {
							$fc_margin = $atts['featured_margin'] ? absint( $atts['featured_margin'] ) : 30;
							$featured_card_classes[] = 'wpex-mb-' . $fc_margin;
						}

						// Featured card side margin
						switch ( $atts['featured_location'] ) {
							case 'left':
								$featured_card_classes[] = 'wpex-' . $fc_bk . '-mb-0';
								$featured_card_classes[] = 'wpex-' . $fc_bk . '-mr-' . $fc_margin;
								break;
							case 'right':
								$featured_card_classes[] = 'wpex-' . $fc_bk . '-mb-0';
								$featured_card_classes[] = 'wpex-' . $fc_bk . '-ml-' . $fc_margin;
								break;
						}

						// Display featured card
						$output .= '<div class="' . esc_attr( implode( ' ', $featured_card_classes ) ) . '">';

							$count=0;
							while ( $vcex_query->have_posts() ) :
								$count++;
								if ( 2 === $count ) {
									break;
								}

								$vcex_query->the_post();

								$featured_card_id = get_the_ID();

								$output .= wpex_get_card( $this->get_featured_card_args( $featured_card_id, $atts ) );

							endwhile;

						$output .= '</div>';

						wp_reset_postdata();

						$vcex_query->rewind_posts();

						if ( ! empty( $atts['featured_divider'] ) && $is_featured_card_top ) {
							$output .= $this->featured_divider( $atts );
						}

					}

					/*-------------------------------------*/
					/* [ Entries start here ]
					/*-------------------------------------*/
					if ( $has_featured_card && ! $is_featured_card_top ) {
						$output .= '<div class="wpex-post-cards-aside wpex-min-w-0 wpex-' . $fc_bk . '-flex-grow">';
					}

					$inner_class = array();

					switch ( $atts['display_type'] ) {

						case 'carousel':

							vcex_enqueue_carousel_scripts();

							$items_data['data-wpex-carousel'] = vcex_get_carousel_settings( $atts, $this->shortcode );
							$inner_class[] = 'wpex-posts-card-carousel';
							$inner_class[] = 'wpex-carousel';
							$inner_class[] = 'owl-carousel';

							// Arrow style
							$arrows_style = ! empty( $atts['arrows_style'] ) ? $atts['arrows_style'] : 'default';
							$inner_class[] = 'arrwstyle-' . sanitize_html_class( $arrows_style );

							// Arrow position
							if ( ! empty( $atts['arrows_position'] ) && 'default' != $atts['arrows_position'] ) {
								$inner_class[] = 'arrwpos-' . sanitize_html_class( $atts['arrows_position'] );
							}

							break;

						case 'list':
							$inner_class[] = 'wpex-post-cards-list wpex-last-mb-0';
							if ( vcex_validate_boolean( $atts['list_divider_remove_last'] ) ) {
								$inner_class[] = 'wpex-last-divider-none';
							}
							break;

						case 'grid':
						default:

							$inner_class[] = 'wpex-post-cards-grid';
							$inner_class[] = 'wpex-row';
							$inner_class[] = 'wpex-clr';

							if ( 'masonry' == $atts['grid_style'] ) {
								$inner_class[] = 'wpex-masonry-grid';
								if ( function_exists( 'wpex_enqueue_masonry_scripts' ) ) {
									wpex_enqueue_masonry_scripts();
								}
							}

							if ( ! empty( $atts['grid_spacing'] ) ) {
								$inner_class[] = 'gap-' . sanitize_html_class( $atts['grid_spacing'] );
							}

							break;

					} // end switch

					$output .= '<div class="' . esc_attr( implode( ' ', $inner_class ) ) . '"';

						if ( ! empty( $items_data ) ) {

							foreach ( $items_data as $key => $value ) {
								$output .= ' ' . $key ."='" . esc_attr( $value ) . "'";
							}

						}

						$output .= '>';

						$running_count = 0;

						while ( $vcex_query->have_posts() ) :

							$vcex_query->the_post(); // !!! Important !!!

							$post_id = get_the_ID();

							if ( ! empty( $featured_card_id ) && $post_id === $featured_card_id ) {
								continue;
							}

							$post_type            = get_post_type( $post_id );
							$card_args['post_id'] = $post_id;

							$entry_count++;

							$running_count++;
							set_query_var( 'wpex_loop_running_count', intval( $running_count ) );

							if ( ! empty( $featured_card_id )
								&& ( $running_count + 1 ) === absint( $vcex_query->post_count )
							) {
								$atts['last_post_id'] = get_the_ID();
							}

							$item_class = array(
								'wpex-post-cards-entry',
								'post-' . sanitize_html_class( $post_id ),
								'type-' . sanitize_html_class( $post_type ),
							);

							switch ( $atts['display_type'] ) {
								case 'carousel':
									$item_class[] = 'wpex-carousel-slide';
									break;
								case 'list':
									$list_spacing = ! empty( $atts['list_spacing'] ) ? $atts['list_spacing'] : 15;
									if ( empty( $atts['list_divider'] ) ) {
										$item_class[] = 'wpex-mb-' . sanitize_html_class( absint( $list_spacing ) );
									}
									break;
								case 'grid':
								default:

									if ( vcex_validate_boolean( $atts['grid_columns_responsive'] ) ) {
										$item_class[] = 'col';
									} else {
										$item_class[] = 'nr-col';
									}

									$item_class[] = 'col-' . sanitize_html_class( $entry_count );

									if ( ! empty( $atts['grid_columns'] ) ) {
										$item_class[] = 'span_1_of_' . sanitize_html_class( $atts['grid_columns'] );
									}

									if ( vcex_validate_boolean( $atts['grid_columns_responsive'] ) ) {
										$rs = vcex_parse_multi_attribute( $atts['grid_columns_responsive_settings'], array() );
										foreach ( $rs as $key => $val ) {
											if ( $val ) {
												$item_class[] = 'span_1_of_' . sanitize_html_class( $val ) . '_' . sanitize_html_class( $key );
											}
										}
									}

									if ( 'masonry' == $atts['grid_style'] ) {
										$item_class[] = 'wpex-masonry-col';
									}

									break;
							}

						$terms = function_exists( 'vcex_get_post_term_classes' ) ? vcex_get_post_term_classes() : '';

						if ( $terms ) {
							$item_class[] = $terms;
						}

						// Begin entry output
						$output .= '<div class="' . esc_attr( implode( ' ', $item_class ) ) . '">';

							// Get card output
							$output .= wpex_get_card( $card_args );

							// List Divider
							if ( 'list' == $atts['display_type'] && ! empty( $atts['list_divider'] ) ) {
								$output .= $this->list_divider( $atts );
							}

						$output .= '</div>';

						// Reset entry count
						if ( 'grid' == $atts['display_type']
							&& 'fit_rows' == $atts['grid_style']
							&& $entry_count == absint( $atts['grid_columns'] )
						) {
							$entry_count = 0;
						}

						endwhile;

					wp_reset_postdata(); // !!! Important !!!

					set_query_var( 'wpex_loop_running_count', null );

				$output .= '</div>'; // close grid/list/carousel

				/*-------------------------------------*/
				/* [ Pagination ]
				/*-------------------------------------*/
				if ( 'grid' == $atts['display_type'] || 'list' == $atts['display_type'] ) {

					// Load more button
					if ( vcex_validate_boolean( $atts['pagination_loadmore'] ) ) {
						if ( ! empty( $vcex_query->max_num_pages ) ) {
							vcex_loadmore_scripts();
							$atts['entry_count'] = $entry_count; // Update counter
							$output .= vcex_get_loadmore_button( $this->shortcode, $atts, $vcex_query );
						}
					}

					// Standard pagination
					elseif ( vcex_validate_boolean( $atts['pagination'] )
						|| vcex_validate_boolean( $atts['auto_query'] )
						|| ( vcex_validate_boolean( $atts['custom_query'] ) && ! empty( $vcex_query->query['pagination'] ) )
					) {
						$output .= vcex_pagination( $vcex_query, false );
					}

				}

				// Close featured aside wrap
				if ( $has_featured_card && ! $is_featured_card_top ) {
					$output .= '</div>';
				}

				// Close featured flex wrap
				if ( $has_featured_card && ! $is_featured_card_top ) {
					$output .= '</div>';
				}

			$output .= '</div>'; // close wrap

			// If no posts are found display message
			} else {

				// Display no posts found error if function exists
				$output .= vcex_no_posts_found_message( $atts );

			// End post check
			}

			return $output;

		}

		/**
		 * Return card args based on shortcode atts.
		 *
		 * @since 5.0
		 */
		private function get_card_args( $atts ) {

			$card_args = array(
				'style' => $atts['card_style'],
			);

			if ( ! empty( $atts['display_type'] ) ) {
				$card_args['display_type'] = $atts['display_type'];
			}

			if ( ! empty( $atts['link_type'] ) ) {
				$card_args['link_type'] = $atts['link_type'];
			}

			if ( ! empty( $atts['link_target'] ) ) {
				$card_args['link_target'] = $atts['link_target'];
			}

			if ( ! empty( $atts['link_rel'] ) ) {
				$card_args['link_rel'] = $atts['link_rel'];
			}

			if ( ! empty( $atts['title_font_size'] ) ) {
				$card_args['title_font_size'] = $atts['title_font_size'];
			}

			if ( ! empty( $atts['css_animation'] ) ) {
				$card_args['css_animation'] = $atts['css_animation'];
			}

			if ( ! empty( $atts['more_link_text'] ) || '0' === $atts['more_link_text'] ) {
				$card_args['more_link_text'] = $atts['more_link_text'];
			}

			if ( ! empty( $atts['media_width'] ) ) {
				$card_args['media_width'] = $atts['media_width'];
			}

			if ( empty( $atts['thumbnail_size'] ) || 'wpex_custom' == $atts['thumbnail_size'] ) {
				$card_args['thumbnail_size'] = array(
					$atts['thumbnail_width'],
					$atts['thumbnail_height'],
					$atts['thumbnail_crop'],
				);
			} else {
				$card_args['thumbnail_size'] = $atts['thumbnail_size'];
			}

			if ( ! empty( $atts['thumbnail_overlay_style'] ) ) {
				$card_args['thumbnail_overlay_style'] = $atts['thumbnail_overlay_style'];
			}

			if ( ! empty( $atts['thumbnail_overlay_button_text'] ) ) {
				$card_args['thumbnail_overlay_button_text'] = $atts['thumbnail_overlay_button_text'];
			}

			if ( ! empty( $atts['thumbnail_hover'] ) ) {
				$card_args['thumbnail_hover'] = $atts['thumbnail_hover'];
			}

			if ( ! empty( $atts['thumbnail_filter'] ) ) {
				$card_args['thumbnail_filter'] = $atts['thumbnail_filter'];
			}

			if ( isset( $atts['excerpt_length'] ) && '' !== $atts['excerpt_length'] ) {
				$card_args['excerpt_length'] = $atts['excerpt_length'];
			}

			return $card_args;

		}

		/**
		 * Check if featured card is enabled.
		 *
		 * @since 5.0
		 */
		public function has_featured_card( $atts ) {

			// Do not show featured card on ajax requests to prevent issues with load more button
			if ( vcex_doing_loadmore() ) {
				return false;
			}

			// Check if the featured card is enabled
			$check = vcex_validate_boolean( $atts['featured_card'] );
			$check = apply_filters( 'wpex_post_cards_has_featured_card', $check, $atts );

			// Do show featured card on paginated pages
			if ( ! vcex_validate_boolean( $atts['featured_show_on_paged'] ) && is_paged() ) {
				$check = false;
			}

			return $check;

		}

		/**
		 * Featured card args.
		 *
		 * @since 5.0
		 */
		private function get_featured_card_args( $post_id, $atts ) {

			$featured_card_style = ! empty( $atts['featured_style'] ) ? $atts['featured_style'] : $atts['card_style'];

			$args = array(
				'post_id'  => $post_id,
				'style'    => $featured_card_style,
				'featured' => true,
			);

			if ( ! empty( $atts['featured_title_font_size'] ) ) {
				$args['title_font_size'] = $atts['featured_title_font_size'];
			}

			if ( empty( $atts['featured_thumbnail_size'] ) || 'wpex_custom' == $atts['featured_thumbnail_size'] ) {
				$args['thumbnail_size'] = array(
					$atts['featured_thumbnail_width'],
					$atts['featured_thumbnail_height'],
					$atts['featured_thumbnail_crop'],
				);
			} else {
				$args['thumbnail_size'] = $atts['featured_thumbnail_size'];
			}

			if ( ! empty( $atts['featured_more_link_text'] ) ) {
				$args['more_link_text'] = $atts['featured_more_link_text'];
			}

			if ( isset( $atts['featured_excerpt_length'] ) && '' !== $atts['featured_excerpt_length'] ) {
				$args['excerpt_length'] = $atts['featured_excerpt_length'];
			}

			if ( ! empty( $atts['thumbnail_overlay_style'] ) ) {
				$args['thumbnail_overlay_style'] = $atts['thumbnail_overlay_style'];
			}

			if ( ! empty( $atts['thumbnail_overlay_button_text'] ) ) {
				$args['thumbnail_overlay_button_text'] = $atts['thumbnail_overlay_button_text'];
			}

			if ( ! empty( $atts['thumbnail_hover'] ) ) {
				$args['thumbnail_hover'] = $atts['thumbnail_hover'];
			}

			if ( ! empty( $atts['thumbnail_filter'] ) ) {
				$args['thumbnail_filter'] = $atts['thumbnail_filter'];
			}

			if ( ! empty( $atts['link_type'] ) ) {
				$args['link_type'] = $atts['link_type'];
			}

			if ( ! empty( $atts['link_target'] ) ) {
				$args['link_target'] = $atts['link_target'];
			}

			if ( ! empty( $atts['link_rel'] ) ) {
				$args['link_rel'] = $atts['link_rel'];
			}

			if ( ! empty( $atts['featured_media_width'] ) ) {
				$args['media_width'] = $atts['featured_media_width'];
			}

			return apply_filters( 'wpex_post_cards_featured_card_args', $args, $atts );

		}

		/**
		 * Check if featured card is enabled.
		 *
		 * @since 5.0
		 */
		public function is_featured_card_top( $atts ) {
			if ( 'left' == $atts['featured_location'] || 'right' == $atts['featured_location'] ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Featured Card Divider
		 *
		 * @since 5.0
		 */
		public function featured_divider( $atts = array() ) {

			$divider_class = array(
				'wpex-post-cards-featured-card-divider',
				'wpex-divider',
				'wpex-divider-' . sanitize_html_class( $atts['featured_divider'] ),
			);

			if ( ! empty( $atts['featured_divider_size'] ) ) {
				$divider_size = absint( $atts['featured_divider_size'] );
				if ( 1 === $divider_size ) {
					$divider_class[] = 'wpex-border-b';
				} else {
					$divider_class[] = 'wpex-border-b-' . $divider_size;
				}
			}

			$divider_style = '';

			if ( ! empty( $atts['featured_divider_color'] ) ) {
				$divider_style = vcex_inline_style( array(
					'border_color' => $atts['featured_divider_color'],
				) );
			}

			$spacing = ! empty( $atts['featured_divider_margin'] ) ? $atts['featured_divider_margin'] : 15;

			if ( ! empty( $atts['featured_margin'] ) ) {
				$divider_class[] = 'wpex-mt-' . sanitize_html_class( absint( $spacing ) );
				$divider_class[] = 'wpex-mb-' . sanitize_html_class( absint( $atts['featured_margin'] ) );
			} else {
				$divider_class[] = 'wpex-my-' . sanitize_html_class( absint( $spacing ) );
			}

			return '<div class="' . esc_attr( implode( ' ', $divider_class ) ) . '"' . $divider_style . '></div>';

		}

		/**
		 * List Divider.
		 *
		 * @since 5.0
		 */
		public function list_divider( $atts = array() ) {

			$divider_class = array(
				'wpex-card-list-divider',
				'wpex-divider',
				'wpex-divider-' . sanitize_html_class( $atts['list_divider'] ),
			);

			$list_spacing = ! empty( $atts['list_spacing'] ) ? $atts['list_spacing'] : 15;

			$divider_class[] = 'wpex-my-' . sanitize_html_class( absint( $list_spacing ) );

			if ( ! empty( $atts['list_divider_size'] ) ) {
				$divider_size = absint( $atts['list_divider_size'] );
				if ( 1 === $divider_size ) {
					$divider_class[] = 'wpex-border-b';
				} else {
					$divider_class[] = 'wpex-border-b-' . $divider_size;
				}
			}

			$divider_style = '';

			if ( ! empty( $atts['list_divider_color'] ) ) {
				$divider_style = vcex_inline_style( array(
					'border_color' => $atts['list_divider_color'],
				) );
			}

			return '<div class="' . esc_attr( implode( ' ', $divider_class ) ) . '"' . $divider_style . '></div>';

		}

		/**
		 * Map shortcode to WPBakery.
		 *
		 * @since 5.0
		 */
		public function vc_after_mapping() {
			vc_lean_map( $this->shortcode, array( $this, 'map' ) );

			$vc_action = vc_request_param( 'action' );

			if ( 'vc_get_autocomplete_suggestion' === $vc_action || 'vc_edit_form' === $vc_action ) {

				// Tax query
				add_filter( 'vc_autocomplete_wpex_post_cards_tax_query_taxonomy_callback', 'vcex_suggest_taxonomies' );
				add_filter( 'vc_autocomplete_wpex_post_cards_tax_query_taxonomy_render', 'vcex_render_taxonomies' );

				// Categories taxonomy
				add_filter( 'vc_autocomplete_wpex_post_cards_categories_taxonomy_callback', 'vcex_suggest_taxonomies' );
				add_filter( 'vc_autocomplete_wpex_post_cards_categories_taxonomy_render', 'vcex_render_taxonomies' );

				// Terms query
				add_filter( 'vc_autocomplete_wpex_post_cards_tax_query_terms_callback', 'vcex_suggest_terms' );
				add_filter( 'vc_autocomplete_wpex_post_cards_tax_query_terms_render', 'vcex_render_terms' );

				// Author query
				add_filter( 'vc_autocomplete_wpex_post_cards_author_in_callback', 'vcex_suggest_users' );
				add_filter( 'vc_autocomplete_wpex_post_cards_author_in_render', 'vcex_render_users' );

				// Posts In query
				add_filter( 'vc_autocomplete_wpex_post_cards_posts_in_callback', 'vc_include_field_search' );
				add_filter( 'vc_autocomplete_wpex_post_cards_posts_in_render', 'vc_include_field_render' );

			}

		}

		/**
		 * Map shortcode to VC.
		 */
		public function map() {

			$params = array(
				// General
				array(
					'type' => 'vcex_wpex_card_select',
					'heading' => esc_html__( 'Card Style', 'total-theme-core' ),
					'param_name' => 'card_style',
					'description' => esc_html__( 'Select your card style. Note: Not all settings are used for every card style.', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Bottom Margin', 'total-theme-core' ),
					'param_name' => 'bottom_margin',
					'value' => vcex_margin_choices(),
					'admin_label' => true,
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Display Type', 'total-theme-core' ),
					'param_name' => 'display_type',
					'value' => array(
						esc_html__( 'Grid', 'total-theme-core' ) => 'grid',
						esc_html__( 'List', 'total-theme-core' ) => 'list',
						esc_html__( 'Carousel', 'total-theme-core' ) => 'carousel',
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Grid Style', 'total-theme-core' ),
					'param_name' => 'grid_style',
					'value' => array(
						esc_html__( 'Default', 'total-theme-core' ) => 'fit_rows',
						esc_html__( 'Masonry', 'total-theme-core' ) => 'masonry',
					),
					'edit_field_class' => 'vc_col-sm-3 vc_column clear',
					'dependency' => array( 'element' => 'display_type', 'value' => array( 'grid', 'masonry_grid' ) ),
				),
				array(
					'type' => 'vcex_grid_columns',
					'heading' => esc_html__( 'Columns', 'total-theme-core' ),
					'param_name' => 'grid_columns',
					'std' => '3',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
					'dependency' => array( 'element' => 'display_type', 'value' => array( 'grid', 'masonry_grid' ) ),
				),
				array(
					'type' => 'vcex_column_gaps',
					'heading' => esc_html__( 'Gap', 'total-theme-core' ),
					'param_name' => 'grid_spacing',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
					'dependency' => array( 'element' => 'display_type', 'value' => array( 'grid', 'masonry_grid' ) ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Responsive', 'total-theme-core' ),
					'param_name' => 'grid_columns_responsive',
					'value' => array(
						esc_html__( 'Yes', 'total-theme-core' ) => 'true',
						esc_html__( 'No', 'total-theme-core' ) => 'false',
					),
					'edit_field_class' => 'vc_col-sm-3 vc_column',
					'dependency' => array( 'element' => 'grid_columns', 'value' => array( '2', '3', '4', '5', '6', '7', '8', '9', '10' ) ),
				),
				array(
					'type' => 'vcex_grid_columns_responsive',
					'heading' => esc_html__( 'Responsive Settings', 'total-theme-core' ),
					'param_name' => 'grid_columns_responsive_settings',
					'dependency' => array( 'element' => 'grid_columns_responsive', 'value' => 'true' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'List Spacing', 'total-theme-core' ),
					'param_name' => 'list_spacing',
					'value' => vcex_margin_choices(),
					'dependency' => array( 'element' => 'display_type', 'value' => 'list' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'List Divider', 'total-theme-core' ),
					'param_name' => 'list_divider',
					'value' => array(
						esc_html__( 'None', 'total-theme-core' ) => '',
						esc_html__( 'Solid', 'total-theme-core' ) => 'solid',
						esc_html__( 'Dashed', 'total-theme-core' ) => 'dashed',
						esc_html__( 'Dotted', 'total-theme-core' ) => 'dotted',
					),
					'dependency' => array( 'element' => 'display_type', 'value' => 'list' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'List Divider Size', 'total-theme-core' ),
					'param_name' => 'list_divider_size',
					'dependency' => array( 'element' => 'list_divider', 'not_empty' => true ),
					'value' => vcex_border_width_choices(),
				),
				array(
					'type' => 'colorpicker',
					'heading' => esc_html__( 'List Divider Color', 'total-theme-core' ),
					'param_name' => 'list_divider_color',
					'dependency' => array( 'element' => 'list_divider', 'not_empty' => true ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Remove Divider on Last Entry?', 'total-theme-core' ),
					'param_name' => 'list_divider_remove_last',
					'std' => 'false',
					'dependency' => array( 'element' => 'list_divider', 'not_empty' => true ),
				),
				// General
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Element ID', 'total-theme-core' ),
					'param_name' => 'unique_id',
					'admin_label' => true,
					'description' => sprintf( esc_html__( 'Enter element ID (Note: make sure it is unique and valid according to %sw3c specification%s).', 'total-theme-core' ), '<a href="https://www.w3schools.com/tags/att_global_id.asp" target="_blank" rel="noopener noreferrer">', '</a>' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Extra class name', 'total-theme-core' ),
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'total-theme-core' ),
					'param_name' => 'el_class',
					'admin_label' => true,
				),
				vcex_vc_map_add_css_animation(),
				// Query
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Automatic Query', 'total-theme-core' ),
					'param_name' => 'auto_query',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'Enable to display items from the current query. For use when overriding an archive (such as categories) with a template.', 'total-theme-core' ),
				),
				array(
					'type' => 'textfield',
					'std' => 'post',
					'heading' => esc_html__( 'Automatic Query Preview Post Type', 'total-theme-core' ),
					'param_name' => 'auto_query_preview_pt',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'Enter a post type name to use as the placeholder for the preview while editing in the WPBakery live editor.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'auto_query', 'value' => 'true' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Advanced Query', 'total-theme-core' ),
					'param_name' => 'custom_query',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'Enable to build a custom query using your own parameters.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'auto_query', 'value' => 'false' ),
				),
				array(
					'type' => 'textarea_safe',
					'heading' => esc_html__( 'Query Parameter String or Callback Function Name', 'total-theme-core' ),
					'param_name' => 'custom_query_args',
					'description' => esc_html__( 'Build a query according to the WordPress Codex in string format. Example: posts_per_page=-1&post_type=portfolio&post_status=publish&orderby=title or enter a custom callback function name that will return an array of query arguments.', 'total-theme-core' ),
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => array( 'true' ) ),
				),
				array(
					'type' => 'posttypes', // @todo update to allow attachments post type.
					'heading' => esc_html__( 'Post types', 'total-theme-core' ),
					'param_name' => 'post_types',
					'std' => 'post',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'admin_label' => true,
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Posts Per Page', 'total-theme-core' ),
					'param_name' => 'posts_per_page',
					'value' => '12',
					'description' => esc_html__( 'You can enter "-1" to display all posts.', 'total-theme-core' ),
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Pagination', 'total-theme-core' ),
					'param_name' => 'pagination',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Load More Button', 'total-theme-core' ),
					'param_name' => 'pagination_loadmore',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Sticky Posts Only', 'total-theme-core' ),
					'param_name' => 'show_sticky_posts',
					'std' => 'false',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Ignore Sticky Posts', 'total-theme-core' ),
					'param_name' => 'ignore_sticky_posts',
					'std' => 'false',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'Include sticky posts, but not at the top.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Exclude Sticky Posts', 'total-theme-core' ),
					'param_name' => 'exclude_sticky_posts',
					'std' => 'false',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'Remove sticky posts completely.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => array( 'false' ) ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Post With Thumbnails Only', 'total-theme-core' ),
					'param_name' => 'thumbnail_query',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				/*
				@todo Enable when we add Card styles for WooCommerce
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Featured Products Only', 'total-theme-core' ),
					'param_name' => 'featured_products_only',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Exclude Out of Stock Products', 'total-theme-core' ),
					'param_name' => 'exclude_products_out_of_stock',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),*/
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'total-theme-core' ),
					'param_name' => 'offset',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'Number of post to displace or pass over. Warning: Setting the offset parameter overrides/ignores the paged parameter and breaks pagination. The offset parameter is ignored when posts per page is set to -1.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Query Specific Posts', 'total-theme-core' ),
					'param_name' => 'posts_in',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => false,
						'unique_values' => true,
						'display_inline' => true,
						'delay' => 0,
						'auto_focus' => true,
					),
					'description' => esc_html__( 'Start typing a post name to locate and add it. Make sure you have selected the Post Types above so they match the post types of the selected posts.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Limit By Author', 'total-theme-core' ),
					'param_name' => 'author_in',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => false,
						'unique_values' => true,
						'display_inline' => true,
						'delay' => 0,
						'auto_focus' => true,
					),
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Query by Taxonomy', 'total-theme-core' ),
					'param_name' => 'tax_query',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Taxonomy Name', 'total-theme-core' ),
					'param_name' => 'tax_query_taxonomy',
					'settings' => array(
						'multiple' => false,
						'min_length' => 1,
						'groups' => false,
						'display_inline' => true,
						'delay' => 0,
						'auto_focus' => true,
					),
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'If you do not see your taxonomy in the dropdown you can still enter the taxonomy name manually.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'tax_query', 'value' => 'true' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Terms', 'total-theme-core' ),
					'param_name' => 'tax_query_terms',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						'display_inline' => true,
						'delay' => 0,
						'auto_focus' => true,
					),
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'description' => esc_html__( 'If you do not see your terms in the dropdown you can still enter the term slugs manually seperated by a space.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'tax_query', 'value' => 'true' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Order', 'total-theme-core' ),
					'param_name' => 'order',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'value' => array(
						esc_html__( 'Default', 'total-theme-core' ) => 'default',
						esc_html__( 'DESC', 'total-theme-core' ) => 'DESC',
						esc_html__( 'ASC', 'total-theme-core' ) => 'ASC',
					),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Order By', 'total-theme-core' ),
					'param_name' => 'orderby',
					'value' => vcex_orderby_array(),
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'custom_query', 'value' => 'false' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Orderby: Meta Key', 'total-theme-core' ),
					'param_name' => 'orderby_meta_key',
					'group' => esc_html__( 'Query', 'total-theme-core' ),
					'dependency' => array( 'element' => 'orderby', 'value' => array( 'meta_value_num', 'meta_value' ) ),
				),
				// Entry
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Title Font Size', 'total' ),
					'param_name' => 'title_font_size',
					'value' => vcex_font_size_choices(),
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Media Width', 'total' ),
					'param_name' => 'media_width',
					'value' => array(
						esc_html__( 'Default', 'total' ) => '',
						'20%'  => '20',
						'25%'  => '25',
						'30%'  => '30',
						'33%'  => '33',
						'40%'  => '40',
						'50%'  => '50',
						'60%'  => '60',
					),
					'description' => esc_html__( 'Applies to card styles that have the media (image/video) displayed to the side.', 'total-theme-core' ),
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_image_sizes',
					'heading' => esc_html__( 'Thumbnail Size', 'total-theme-core' ),
					'param_name' => 'thumbnail_size',
					'std' => 'full',
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
					'description' => esc_html__( 'Note: For security reasons custom cropping only works on images hosted on your own server in the WordPress uploads folder. If you are using an external image it will display in full.', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_image_crop_locations',
					'heading' => esc_html__( 'Thumbnail Crop Location', 'total-theme-core' ),
					'param_name' => 'thumbnail_crop',
					'std' => 'center-center',
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
					'dependency' => array( 'element' => 'thumbnail_size', 'value' => 'wpex_custom' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Thumbnail Crop Width', 'total-theme-core' ),
					'param_name' => 'thumbnail_width',
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
					'dependency' => array( 'element' => 'thumbnail_size', 'value' => 'wpex_custom' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Thumbnail Crop Height', 'total-theme-core' ),
					'param_name' => 'thumbnail_height',
					'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
					'dependency' => array( 'element' => 'thumbnail_size', 'value' => 'wpex_custom' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Excerpt Length', 'total-theme-core' ),
					'param_name' => 'excerpt_length',
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
					'description' => esc_html__( 'Enter how many words to display for the excerpt. To display the full post content enter "-1". To display the full post content up to the "more" tag enter "9999".', 'total-theme-core' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Custom More Link Text', 'total-theme-core' ),
					'param_name' => 'more_link_text',
					'group' => esc_html__( 'Entry', 'total-theme-core' ),
					'description' => esc_html__( 'You can enter "0" to disable.', 'total-theme-core' ),
				),
				// Carousel Settings
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Arrows', 'total-theme-core' ),
					'param_name' => 'arrows',
					'std' => 'true',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_carousel_arrow_styles',
					'heading' => esc_html__( 'Arrows Style', 'total-theme-core' ),
					'param_name' => 'arrows_style',
					'dependency' => array( 'element' => 'arrows', 'value' => 'true' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_carousel_arrow_positions',
					'heading' => esc_html__( 'Arrows Position', 'total-theme-core' ),
					'param_name' => 'arrows_position',
					'dependency' => array( 'element' => 'arrows', 'value' => 'true' ),
					'std' => 'default',
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Dot Navigation', 'total-theme-core' ),
					'param_name' => 'dots',
					'std' => 'false',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Auto Play', 'total-theme-core' ),
					'param_name' => 'auto_play',
					'std' => 'false',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Autoplay interval timeout.', 'total-theme-core' ),
					'param_name' => 'timeout_duration',
					'value' => '5000',
					'description' => esc_html__( 'Time in milliseconds between each auto slide. Default is 5000.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'auto_play', 'value' => 'true' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Infinite Loop', 'total-theme-core' ),
					'param_name' => 'infinite_loop',
					'std' => 'true',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Center Item', 'total-theme-core' ),
					'param_name' => 'center',
					'std' => 'false',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Animation Speed', 'total-theme-core' ),
					'param_name' => 'animation_speed',
					'value' => '250',
					'description' => esc_html__( 'Default is 250 milliseconds. Enter 0.0 to disable.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Items To Display', 'total-theme-core' ),
					'param_name' => 'items',
					'value' => '4',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'vcex_ofswitch',
					'std' => 'false',
					'heading' => esc_html__( 'Auto Height?', 'total-theme-core' ),
					'param_name' => 'auto_height',
					'dependency' => array( 'element' => 'items', 'value' => '1' ),
					'description' => esc_html__( 'Allows the carousel to change height based on the active item. This setting is used only when you are displaying 1 item per slide.', 'total-theme-core' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Items To Scrollby', 'total-theme-core' ),
					'param_name' => 'items_scroll',
					'value' => '1',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Tablet: Items To Display', 'total-theme-core' ),
					'param_name' => 'tablet_items',
					'value' => '3',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Mobile Landscape: Items To Display', 'total-theme-core' ),
					'param_name' => 'mobile_landscape_items',
					'value' => '2',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Mobile Portrait: Items To Display', 'total-theme-core' ),
					'param_name' => 'mobile_portrait_items',
					'value' => '1',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Margin Between Items', 'total-theme-core' ),
					'param_name' => 'items_margin',
					'value' => '15',
					'dependency' => array( 'element' => 'display_type', 'value' => 'carousel' ),
					'group' => esc_html__( 'Carousel Settings', 'total-theme-core' )
				),
				// Media
				array(
					'type' => 'vcex_overlay',
					'heading' => esc_html__( 'Thumbnail Overlay', 'total-theme-core' ),
					'param_name' => 'thumbnail_overlay_style',
					'group' => esc_html__( 'Media', 'total-theme-core' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Overlay Button Text', 'total-theme-core' ),
					'param_name' => 'thumbnail_overlay_button_text',
					'group' => esc_html__( 'Media', 'total-theme-core' ),
					'dependency' => array( 'element' => 'thumbnail_overlay_style', 'value' => 'hover-button' ),
				),
				array(
					'type' => 'vcex_image_hovers',
					'heading' => esc_html__( 'Image Hover', 'total-theme-core' ),
					'param_name' => 'thumbnail_hover',
					'group' => esc_html__( 'Media', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_image_filters',
					'heading' => esc_html__( 'Image Filter', 'total-theme-core' ),
					'param_name' => 'thumbnail_filter',
					'group' => esc_html__( 'Media', 'total-theme-core' ),
				),
				// Link
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Link Type', 'total-theme-core' ),
					'param_name' => 'link_type',
					'group' => esc_html__( 'Link', 'total-theme-core' ),
					'value' => array(
						esc_html__( 'Default', 'total-theme-core' ) => '',
						esc_html__( 'Lightbox', 'total-theme-core' ) => 'lightbox',
						esc_html__( 'Modal Popup', 'total-theme-core' ) => 'modal',
						esc_html__( 'None', 'total-theme-core' ) => 'none',
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Link Target', 'total-theme-core' ),
					'param_name' => 'link_target',
					'group' => esc_html__( 'Link', 'total-theme-core' ),
					'value' => array(
						esc_html__( 'Same Tab', 'total-theme-core' ) => '',
						esc_html__( 'New Tab', 'total-theme-core' ) => '_blank',
					),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Link Rel', 'total-theme-core' ),
					'param_name' => 'link_rel',
					'group' => esc_html__( 'Link', 'total-theme-core' ),
				),
				// Featured
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Featured Card', 'total-theme-core' ),
					'param_name' => 'featured_card',
					'std' => 'false',
					'description' => esc_html__( 'Enable to display the first entry as a "featured" card with it\'s own unique style above the other entries.', 'total-theme-core' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_wpex_card_select',
					'heading' => esc_html__( 'Featured Card Style', 'total-theme-core' ),
					'param_name' => 'featured_style',
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'vcex_ofswitch',
					'heading' => esc_html__( 'Display on Paginated Pages', 'total-theme-core' ),
					'param_name' => 'featured_show_on_paged',
					'std' => 'true',
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Placement', 'total-theme-core' ),
					'param_name' => 'featured_location',
					'value' => array(
						esc_html__( 'Top', 'total-theme-core' ) => 'top',
						esc_html__( 'Left', 'total-theme-core' ) => 'left',
						esc_html__( 'Right', 'total-theme-core' ) => 'right',
					),
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Width', 'total-theme-core' ),
					'param_name' => 'featured_width',
					'value' => array(
						esc_html__( 'Default', 'total-theme-core' ) => '',
						'70%' => '70%',
						'67%' => '67%',
						'60%' => '60%',
						'50%' => '50%',
					),
					'dependency' => array( 'element' => 'featured_location', 'value' => array( 'left', 'right' ) ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Breakpoint', 'total' ),
					'param_name' => 'featured_breakpoint',
					'value' => vcex_breakpoint_choices(),
					'dependency' => array( 'element' => 'featured_location', 'value' => array( 'left', 'right' ) ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Margin', 'total-theme-core' ),
					'param_name' => 'featured_margin',
					'value' => vcex_margin_choices(),
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Divider Style', 'total-theme-core' ),
					'param_name' => 'featured_divider',
					'value' => array(
						esc_html__( 'None', 'total-theme-core' ) => '',
						esc_html__( 'Solid', 'total-theme-core' ) => 'solid',
						esc_html__( 'Dashed', 'total-theme-core' ) => 'dashed',
						esc_html__( 'Dotted', 'total-theme-core' ) => 'dotted',
					),
					'dependency' => array( 'element' => 'featured_location', 'value' => 'top' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Divider Size', 'total-theme-core' ),
					'param_name' => 'featured_divider_size',
					'value' => vcex_border_width_choices(),
					'dependency' => array( 'element' => 'featured_divider', 'not_empty' => true ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Divider Margin', 'total-theme-core' ),
					'param_name' => 'featured_divider_margin',
					'value' => vcex_margin_choices(),
					'dependency' => array( 'element' => 'featured_divider', 'not_empty' => true ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'colorpicker',
					'heading' => esc_html__( 'List Divider Color', 'total-theme-core' ),
					'param_name' => 'featured_divider_color',
					'dependency' => array( 'element' => 'featured_divider', 'not_empty' => true ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Title Font Size', 'total' ),
					'param_name' => 'featured_title_font_size',
					'value' => vcex_font_size_choices(),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Media Width', 'total' ),
					'param_name' => 'featured_media_width',
					'value' => array(
						esc_html__( 'Default', 'total' ) => '',
						'20%'  => '20',
						'25%'  => '25',
						'30%'  => '30',
						'33%'  => '33',
						'40%'  => '40',
						'50%'  => '50',
						'60%'  => '60',
					),
					'description' => esc_html__( 'Applies to card styles that have the media (image/video) displayed to the side.', 'total-theme-core' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
				),
				array(
					'type' => 'vcex_image_sizes',
					'heading' => esc_html__( 'Thumbnail Size', 'total-theme-core' ),
					'param_name' => 'featured_thumbnail_size',
					'std' => 'full',
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'description' => esc_html__( 'Note: For security reasons custom cropping only works on images hosted on your own server in the WordPress uploads folder. If you are using an external image it will display in full.', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
				),
				array(
					'type' => 'vcex_image_crop_locations',
					'heading' => esc_html__( 'Thumbnail Crop Location', 'total-theme-core' ),
					'param_name' => 'featured_thumbnail_crop',
					'std' => 'center-center',
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_thumbnail_size', 'value' => 'wpex_custom' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Thumbnail Crop Width', 'total-theme-core' ),
					'param_name' => 'featured_thumbnail_width',
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_thumbnail_size', 'value' => 'wpex_custom' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Thumbnail Crop Height', 'total-theme-core' ),
					'param_name' => 'featured_thumbnail_height',
					'description' => esc_html__( 'Leave empty to disable vertical cropping and keep image proportions.', 'total-theme-core' ),
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_thumbnail_size', 'value' => 'wpex_custom' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Excerpt Length', 'total-theme-core' ),
					'param_name' => 'featured_excerpt_length',
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'description' => esc_html__( 'Enter how many words to display for the excerpt. To display the full post content enter "-1". To display the full post content up to the "more" tag enter "9999".', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Custom More Link Text', 'total-theme-core' ),
					'param_name' => 'featured_more_link_text',
					'group' => esc_html__( 'Featured', 'total-theme-core' ),
					'dependency' => array( 'element' => 'featured_card', 'value' => 'true' ),
				),
				// Hidden fields
				array( 'type' => 'hidden', 'param_name' => 'query_vars' ), // used for load more
				array( 'type' => 'hidden', 'param_name' => 'last_post_id' ),
			);

			return array(
				'name' => esc_html__( 'Post Cards', 'total-theme-core' ),
				'description' => esc_html__( 'Post based card list, grid or carousel.', 'total-theme-core' ),
				'base' => 'wpex_post_cards',
				'icon' => 'vcex-icon ticon ticon-address-card-o',
				'category' => vcex_shortcodes_branding(),
				'params' => $params,
			);

		}

	}

}
new WPEX_Post_Cards_Shortcode;

if ( class_exists( 'WPBakeryShortCode' ) && ! class_exists( 'WPBakeryShortCode_wpex_post_cards' ) ) {
	class WPBakeryShortCode_wpex_post_cards extends WPBakeryShortCode {}
}