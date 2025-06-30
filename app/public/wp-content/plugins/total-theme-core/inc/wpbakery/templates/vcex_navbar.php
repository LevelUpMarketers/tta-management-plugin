<?php
/**
 * vcex_navbar shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_navbar';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define output var
$output = '';

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Deprecated params
$atts['border_radius'] = ! empty( $atts['border_radius'] ) ? $atts['border_radius'] : '';

// Return if no menu defined
if ( empty( $atts['menu'] ) ) {
	return;
}

// Old style param fallback
if ( isset( $atts['style'] ) && 'simple' == $atts['style'] ) {
	$button_style = 'plain-text';
}

// Define new vars
$preset_design     = $atts['preset_design'] ? $atts['preset_design'] : 'none';
$has_mobile_select = vcex_validate_boolean( $atts['mobile_select'] );

// Hover animation
if ( $atts['hover_animation'] ) {
	$atts['hover_animation'] = vcex_hover_animation_class( $atts['hover_animation'] );
	vcex_enque_style( 'hover-animations' );
}

// CSS class
$css_class = vcex_vc_shortcode_custom_css_class( $atts['css'] );

// Border radius
if ( $atts['border_radius'] ) {
	$atts['border_radius'] = vcex_get_border_radius_class( $atts['border_radius'] );
}

// Define wrap attributes
$wrap_attrs = array(
	'id' => $atts['unique_id'],
);

// Wrap style
$wrap_style = vcex_inline_style( array(
	'font_size'      => $atts['font_size'],
	'letter_spacing' => $atts['letter_spacing'],
	'font_family'    => $atts['font_family'],
), false );

// Load custom fonts
if ( ! empty( $atts['font_family'] ) ) {
	vcex_enqueue_font( $atts['font_family'] );
}

// Classes
$wrap_classes = array(
	'vcex-module',
	'vcex-navbar',
	'wpex-clr'
);

if ( $atts['bottom_margin'] ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $atts['bottom_margin'], 'wpex-mb-' );
}

$wrap_data = array();

if ( $atts['filter_menu'] ) {
	vcex_enqueue_navbar_filter_scripts();
	$wrap_classes[] = 'vcex-filter-nav';
	$wrap_data[]    = 'data-filter-grid="' . esc_attr( $atts['filter_menu'] ) . '"';
	if ( 'fitRows' == $atts['filter_layout_mode'] ) {
		$wrap_data[] = 'data-layout-mode="fitRows"';
	}
	if ( $atts['filter_transition_duration'] ) {
		$wrap_data[] = 'data-transition-duration="'. esc_attr( $atts['filter_transition_duration'] ) .'"';
	}
	if ( $filter_active_category = vcex_grid_filter_get_active_item() ) {
		$wrap_data[] = 'data-filter=".' . $filter_active_category . '"';
	} elseif ( ! empty( $atts['filter_active_item'] ) ) {
		$wrap_data[] = 'data-filter=".cat-' . absint( $atts['filter_active_item'] ) . '"';
	}
}

if ( 'none' != $atts['preset_design'] ) {
	$wrap_classes[] = 'vcex-navbar-' . $atts['preset_design'];
}

if ( 'true' == $atts['sticky'] ) {
	$wrap_classes[] = 'vcex-navbar-sticky';
	if ( 'true' == $atts['sticky_offset_nav_height'] ) {
		$wrap_classes[] = 'vcex-navbar-sticky-offset';
	}
	if ( isset( $atts['sticky_endpoint'] ) ) {
		$wrap_data[] = 'data-sticky-endpoint="' . esc_attr( $atts['sticky_endpoint'] ) . '"';
	}
}

if ( $atts['classes'] ) {
	$wrap_classes[] = vcex_get_extra_class( $atts['classes'] );
}

if ( $atts['visibility'] ) {
	$wrap_classes[] = sanitize_html_class( $atts['visibility'] );
}

if ( $atts['align'] ) {
	$wrap_classes[] = 'align-' . sanitize_html_class( $atts['align'] );
}

if ( $atts['css_animation'] && 'none' != $atts['css_animation'] ) {
	$wrap_classes[] = vcex_get_css_animation( $atts['css_animation'] );
}

if ( $atts['wrap_css'] ) {
	$wrap_classes[] = vcex_vc_shortcode_custom_css_class( $atts['wrap_css'] );
}

// Responsive styles
if ( $responsive_data = vcex_get_module_responsive_data( $atts ) ) {
	$wrap_data['data-wpex-rcss'] = $responsive_data;
}

// Parse wrap attributes
$wrap_attrs['class'] = esc_attr( vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts ) );
$wrap_attrs['style'] = $wrap_style;
$wrap_attrs['data']  = $wrap_data;

// Begin output
$output .= '<nav' . vcex_parse_html_attributes( $wrap_attrs ) . '>';

	// Inner classes
	$inner_classes = 'vcex-navbar-inner clr';

	if ( 'true' == $atts['full_screen_center'] ) {
		$inner_classes .= ' container';
	}

	if ( $has_mobile_select ) {
		$inner_classes .= ' visible-desktop';
	}

	$output .= '<div class="'. esc_attr( $inner_classes ) .'">';

		// Get menu object
		$menu = wp_get_nav_menu_object( $atts['menu'] );

		// If menu isn't empty display items
		if ( ! empty( $menu ) ) :

			// Get menu items
			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			// Link style
			$link_style = vcex_inline_style( array(
				'background' => $atts['background'],
				'color'      => $atts['color'],
			), false );

			// Make sure we have menu items
			if ( $menu_items && is_array( $menu_items ) ) :

				// Define counter var
				$counter = 0;

				// Check if visitor is viewing a singular post/page
				$is_singular = is_singular();

				// Singular check
				if ( $is_singular ) {
					$post_id      = get_the_ID();
					$post_parents = get_post_ancestors( $post_id );
					$post_type    = get_post_type();
				}

				// Used for mobile select
				$select_ops = array();

				// Loop through menu items
				foreach ( $menu_items as $menu_item ) :

					// Define link style and reset for each item to prevent issues with active item
					$item_link_style = $link_style;

					// Define active class
					$is_active = false;

					// Add to counter
					$counter++;

					// Link Classes
					$link_classes = array( 'vcex-navbar-link' );
					if ( 'none' == $atts['preset_design'] ) {
						$link_classes[] = vcex_get_button_classes( $atts['button_style'], $atts['button_color'] );
						if ( $atts['button_layout'] ) {
							$link_classes[] = $atts['button_layout'];
						}
					}
					if ( $atts['font_weight'] ) {
						$link_classes[] = vcex_font_weight_class( $atts['font_weight'] );
					}
					if ( 'true' == $atts['local_scroll'] && ! in_array( 'local-scroll', $menu_item->classes ) ) {
						$link_classes[] = 'local-scroll';
					}
					if ( $css_class ) {
						$link_classes[] = $css_class;
					}
					if ( $atts['hover_animation'] ) {
						$link_classes[] = $atts['hover_animation'];
					}
					if ( $atts['hover_bg'] ) {
						$link_classes[] = 'has-bg-hover';
					}
					if ( $atts['border_radius'] ) {
						$link_classes[] = $atts['border_radius'];
					}
					if ( $menu_item->classes ) {
						$link_classes = array_merge( $link_classes, $menu_item->classes );
					}

					// Add active item item for singular pages
					if ( $is_singular && 'taxonomy' != $menu_item->type ) {

						if ( $menu_item->object_id == $post_id || in_array( $menu_item->object_id, $post_parents ) ) {
							$is_active = true;
						}

						// Active based on main post type page setting
						if ( in_array( $post_type, array( 'portfolio', 'staff', 'testimonials', 'post' ) ) ) {

							$type_page = ( 'post' == $post_type ) ? get_theme_mod( 'blog_page' ) : get_theme_mod( $post_type .'_page' );

							if ( $menu_item->object_id == $type_page ) {
								$is_active = true;
							}

						}

					}

					// Add special classes for filtering by terms
					$data_filter = ''; // reset filter
					if ( $atts['filter_menu'] ) {

						// Active tax link
						if ( ! empty( $atts['filter_active_item'] ) ) {
							if ( $atts['filter_active_item'] == $menu_item->object_id ) {
								$link_classes[] = 'active';
							}
						} elseif ( '1' == $counter && '#' == $menu_item->url ) {
							$data_filter = '*';
							if ( ! $filter_active_category ) {
								$link_classes[] = 'active';
							}
						}

						// Taxonomy links
						if ( 'taxonomy' == $menu_item->type ) {
							$obj = $menu_item->object;
							if ( $obj ) {
								$prefix = $menu_item->object;
								if ( 'category' == $obj ) {
									$prefix = 'cat';
								} else {
									$parse_types = vcex_theme_post_types();
									foreach ( $parse_types as $type ) {
										if ( strpos( $prefix, $type ) !== false ) {
											$search  = array( $type .'_category', $type .'_tag' );
											$replace = array( 'cat', 'tag' );
											$prefix  = str_replace( $search, $replace, $prefix );
										}
									}
								}
								$data_filter = '.' . $prefix . '-' . $menu_item->object_id;
								$menu_item_term = get_term_by( 'id', $menu_item->object_id, $obj );
								if ( $menu_item_term ) {
									$menu_item_filter_slug = $menu_item_term->slug;
								}
							}
						}

					}

					// Add active styles and class
					if ( $is_active ) {

						$link_classes[] = 'active';

						$item_link_style .= vcex_inline_style( array(
							'background' => $atts['hover_bg'],
							'color'      => $atts['hover_color'],
						), false );

					}

					// Define href
					if ( $atts['filter_menu'] && $data_filter ) {
						$href = ! empty( $menu_item_filter_slug ) ? wpex_get_current_url() . '?' . vcex_grid_filter_url_param() . '=' . str_replace( '.', '', $data_filter )  : '#';
					} else {
						$href = $menu_item->url;
					}

					// Link attributes
					$link_attrs = array(
						'href'        => esc_url( $href ),
						'title'       => esc_attr( $menu_item->attr_title ? $menu_item->attr_title : '' ),
						'class'       => implode( ' ', array_filter( $link_classes, 'trim' ) ),
						'target'      => $menu_item->target,
						'style'       => $item_link_style,
					);

					// Add data filter
					if ( $data_filter ) {
						$link_attrs[ 'data-filter' ] = $data_filter;
					}

					// Add active filter class
					if ( $atts['filter_menu'] && $data_filter == '.' . $filter_active_category ) {
						$link_attrs['class'] .= ' active';
						$mobile_select_selected = $href;
					}

					// Add hover data
					$hover_data = array();
					if ( $atts['hover_bg'] ) {
						$hover_data['background'] = esc_attr( $atts['hover_bg'] );
					}
					if ( $atts['hover_color'] ) {
						$hover_data['color'] = esc_attr( $atts['hover_color'] );
					}
					if ( $hover_data ) {
						$link_attrs['data-wpex-hover'] = htmlspecialchars( wp_json_encode( $hover_data ) );
					}

					// Open list item div
					if ( 'list' == $atts['button_layout'] ) {
						$output .= '<div class="wpex-list-item wpex-clr">';
					}

						// Link item output
						$output .= '<a' . vcex_parse_html_attributes( $link_attrs ) .'>';

							$link_text_excaped = do_shortcode( wp_kses_post( $menu_item->title ) );

							$output .= '<span>' . $link_text_excaped . '</span>';

						$output .= '</a>';

					// Close list item div
					if ( 'list' == $atts['button_layout'] ) {
						$output .= '</div>';
					}

					// Save links into select options array
					$select_ops[] = array(
						'href'         => $href,
						'text_escaped' => $link_text_excaped,
					);

				endforeach; // End menu item loop

			endif; // End menu_items check

		endif; // End menu check

	$output .= '</div>';

	if ( $has_mobile_select && ! empty( $select_ops ) ) {

		$output .= '<div class="vcex-navbar-mobile-select hidden-desktop wpex-select-wrap"><select>';

			if ( $atts['mobile_select_browse_txt'] ) {
				$output .= '<option value="">' . do_shortcode( esc_html( $atts['mobile_select_browse_txt'] ) ) . '</option>';
			}

			foreach ( $select_ops as $option ) {

				$output .= '<option value="' . esc_attr( $option['href'] ) . '">' . wp_strip_all_tags( $option['text_escaped'] ) . '</option>';
			}

		$output .= '</select></div>';

	}

$output .= '</nav>';

// @codingStandardsIgnoreLine
echo $output;
