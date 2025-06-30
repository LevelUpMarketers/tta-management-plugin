<?php
/**
 * Frontend helper functions
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build Query.
 */
function vcex_build_wp_query( $atts ) {
	$query_builder = new VCEX_Query_Builder( $atts );
	return $query_builder->build();
}

/**
 * Get shortcode custom css class.
 */
function vcex_vc_shortcode_custom_css_class( $css = '' ) {
	if ( $css && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		return trim( vc_shortcode_custom_css_class( $css ) );
	}
}

/**
 * Adds the vc custom css filter tag.
 */
function vcex_parse_shortcode_classes( $classes = '', $shortcode_base = '', $atts = '' ) {
	if ( defined( 'VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG' ) ) {
		return apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $classes, $shortcode_base, $atts );
	}
	return $classes;
}

/**
 * Adds inline style for elements.
 */
function vcex_inline_style( $atts = array(), $add_style = true ) {
	$atts = array_filter( $atts ); // remove atts with empty values
	if ( ! empty( $atts ) && is_array( $atts ) ) {
		$inline_style = new VCEX_Inline_Style( $atts, $add_style );
		return $inline_style->return_style();
	}
}

/**
 * Return post id.
 */
function vcex_get_the_ID() {
	if ( function_exists( 'wpex_get_dynamic_post_id' ) ) {
		return wpex_get_dynamic_post_id();
	}
	return get_the_ID();
}

/**
 * Check if responsiveness is enabled.
 */
function vcex_is_layout_responsive() {
	return apply_filters( 'wpex_is_layout_responsive', get_theme_mod( 'responsive', true ) );
}

/**
 * Return post title.
 */
function vcex_get_the_title() {
	if ( function_exists( 'wpex_title' ) && function_exists( 'wpex_get_dynamic_post_id' ) ) {
		return wpex_title( wpex_get_dynamic_post_id() );
	} else {
		return get_the_title();
	}
}

/**
 * Return post title.
 */
function vcex_get_schema_markup( $location ) {
	if ( function_exists( 'wpex_get_schema_markup' ) ) {
		return wpex_get_schema_markup( $location );
	}
}

/**
 * Return post permalink.
 */
function vcex_get_permalink( $post_id = '' ) {
	if ( function_exists( 'wpex_get_permalink' ) ) {
		return wpex_get_permalink( $post_id );
	}
	return get_permalink();
}

/**
 * Return post class.
 */
function vcex_get_post_class( $class = '', $post_id = null ) {
	return 'class="' . esc_attr( implode( ' ', get_post_class( $class, $post_id ) ) ) . '"';
}

/**
 * Get module header output.
 */
function vcex_get_module_header( $args = array() ) {
	if ( function_exists( 'wpex_get_heading' ) ) {
		$output = wpex_get_heading( $args );
	} else {
		$output = '<h2 class="vcex-module-heading">' . do_shortcode( wp_kses_post( $header ) ) . '</h2>';
	}
	return apply_filters( 'vcex_get_module_header', $output, $args );
}

/**
 * Returns entry image overlay output.
 */
function vcex_get_entry_image_overlay( $position = '', $shortcode_tag = '', $atts = '' ) {

	if ( empty( $atts['overlay_style'] ) || 'none' == $atts['overlay_style'] ) {
		return '';
	}

	ob_start();
	vcex_image_overlay( $position, $atts['overlay_style'], $atts );
	$overlay = ob_get_clean();
	return apply_filters( 'vcex_entry_image_overlay', $overlay, $position, $shortcode_tag, $atts );

}

/**
 * Return post content.
 */
function vcex_the_content( $content = '', $context = '' ) {
	if ( empty( $content ) ) {
		return '';
	}
	if ( defined( 'TOTAL_THEME_ACTIVE' ) ) {
		return apply_filters( 'wpex_the_content', wp_kses_post( $content ), $context );
	} else {
		return do_shortcode( shortcode_unautop( wpautop( wp_kses_post( $content ) ) ) );
	}
}

/**
 * Return escaped post title.
 */
function vcex_esc_title( $post = '' ) {
	return the_title_attribute( array(
		'echo' => false,
		'post' => $post,
	) );
}

/**
 * Wrapper for esc_attr with fallback.
 */
function vcex_esc_attr( $val = null, $fallback = null ) {
	if ( ! $val ) {
		$val = $fallback;
	}
	return esc_attr( $val );
}

/**
 * Wrapper for the wpex_get_star_rating function.
 */
function vcex_get_star_rating( $rating = '', $post_id = '', $before = '', $after = '' ) {
	if ( function_exists( 'wpex_get_star_rating' ) ) {
		return wpex_get_star_rating( $rating, $post_id, $before, $after );
	}
	if ( $rating = get_post_meta( get_the_ID(), 'wpex_post_rating', true ) ) {
		echo esc_html( $trating );
	}
}

/**
 * Wrapper for the vcex_get_user_social_links function.
 */
function vcex_get_user_social_links( $user_id = '', $display = 'icons', $attr = '', $before = '', $after = '' ) {
	if ( function_exists( 'wpex_get_user_social_links' ) ) {
		return wpex_get_user_social_links( $user_id, $display, $attr, $before, $after );
	}
}

/**
 * Wrapper for the wpex_get_social_button_class function.
 */
function vcex_get_social_button_class( $style = 'default' ) {
	if ( function_exists( 'wpex_get_social_button_class' ) ) {
		return wpex_get_social_button_class( $style );
	}
}

/**
 * Get image filter class.
 */
function vcex_image_filter_class( $filter = '' ) {
	if ( function_exists( 'wpex_image_filter_class' ) ) {
		return wpex_image_filter_class( $filter );
	}
}

/**
 * Get image hover classes.
 */
function vcex_image_hover_classes( $hover = '' ) {
	if ( function_exists( 'wpex_image_hover_classes' ) ) {
		return wpex_image_hover_classes( $hover );
	}
}

/**
 * Get image overlay classes.
 */
function vcex_image_overlay_classes( $overlay = '', $args = array() ) {
	if ( function_exists( 'wpex_overlay_classes' ) ) {
		return wpex_overlay_classes( $overlay, $args );
	}
}

/**
 * Return image overlay.
 */
function vcex_image_overlay( $position = '', $style = '', $atts = '' ) {
	if ( function_exists( 'wpex_overlay' ) ) {
		wpex_overlay( $position, $style, $atts );
	}
}

/**
 * Return button classes.
 */
function vcex_get_button_classes( $style = '', $color = '', $size = '', $align = '' ) {
	if ( function_exists( 'wpex_get_button_classes' ) ) {
		return wpex_get_button_classes( $style, $color, $size, $align );
	}
}

/**
 * Return after media content.
 */
function vcex_get_entry_media_after( $instance = '' ) {
	return apply_filters( 'wpex_get_entry_media_after', '', $instance ); // do NOT rename filter!!!
}

/**
 * Return excerpt.
 */
function vcex_get_excerpt( $args = '' ) {
	if ( function_exists( 'wpex_get_excerpt' ) ) {
		return wpex_get_excerpt( $args );
	} else {
		$excerpt_length = isset( $args['length'] ) ? $args['length'] : 40;
		return wp_trim_words( get_the_excerpt(), $excerpt_length, null );
	}
}

/**
 * Return thumbnail.
 */
function vcex_get_post_thumbnail( $args = '' ) {
	if ( function_exists( 'wpex_get_post_thumbnail' ) ) {
		return wpex_get_post_thumbnail( $args );
	}
	if ( isset( $args[ 'attachment' ] ) ) {
		$size = isset( $args[ 'size' ] ) ? $args[ 'size' ] : 'full';
		return wp_get_attachment_image( $args[ 'attachment' ], $size );
	}
}

/**
 * Return WooCommerce price
 */
function vcex_get_woo_product_price( $post_id = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( 'product' == get_post_type( $post_id ) ) {
		$product = wc_get_product( $post_id );
		$price   = $product->get_price_html();
		if ( $price ) {
			return $price;
		}
	}
}

/**
 * Parses a direction for RTL compatibility.
 */
function vcex_parse_direction( $direction = '' ) {
	if ( ! $direction ) {
		return;
	}
	if ( is_rtl() ) {
		switch ( $direction ) {
			case 'left' :
				$direction = 'right';
			break;
			case 'right' :
				$direction = 'left';
			break;
		}
	}
	return $direction;
}

/**
 * Return button arrow.
 */
function vcex_readmore_button_arrow() {
	if ( is_rtl() ) {
		$arrow = '&larr;';
	} else {
		$arrow = '&rarr;';
	}
	return apply_filters( 'wpex_readmore_button_arrow', $arrow );
}

/**
 * Return correct font weight class
 */
function vcex_font_weight_class( $font_weight = '' ) {
	if ( ! $font_weight ) {
		return;
	}
	$font_weights = array(

		'hairline'  => 'wpex-font-hairline',
		'100'       => 'wpex-font-hairline',

		'thin'      => 'wpex-font-thin',
		'200'       => 'wpex-font-thin',

		'normal'    => 'wpex-font-normal',
		'400'       => 'wpex-font-normal',

		'semibold'  => 'wpex-font-semibold',
		'600'       => 'wpex-font-semibold',

		'bold'      => 'wpex-font-bold',
		'700'       => 'wpex-font-bold',

		'extrabold' => 'wpex-font-extrabold',
		'bolder'    => 'wpex-font-extrabold',
		'800'       => 'wpex-font-extrabold',

		'black'     => 'wpex-font-black',
		'900'       => 'wpex-font-black',

	);
	if ( isset( $font_weights[$font_weight] ) ) {
		return $font_weights[$font_weight];
	}
}

/**
 * Get theme term data.
 */
function vcex_get_term_data() {
	if ( function_exists( 'wpex_get_term_data' ) ) {
		return wpex_get_term_data();
	}
}

/**
 * Get term thumbnail.
 */
function vcex_get_term_thumbnail_id( $term_id = '' ) {
	if ( function_exists( 'wpex_get_term_thumbnail_id' ) ) {
		return wpex_get_term_thumbnail_id( $term_id );
	}
}

/**
 * Get post video.
 */
function vcex_get_post_video( $post_id = '' ) {
	if ( function_exists( 'wpex_get_post_video' ) ) {
		return wpex_get_post_video( $post_id );
	}
}

/**
 * Get post video html.
 */
function vcex_get_post_video_html() {
	if ( function_exists( 'wpex_get_post_video_html' ) ) {
		return wpex_get_post_video_html();
	}
}

/**
 * Get post video html.
 */
function vcex_video_oembed( $video = '', $classes = '', $params = array() ) {
	if ( function_exists( 'wpex_video_oembed' ) ) {
		return wpex_video_oembed( $video, $classes, $params );
	}
	return wp_oembed_get( $video );
}

/**
 * Get post video oembed URL.
 */
function vcex_get_post_video_oembed_url( $post_id = '' ) {
	if ( function_exists( 'wpex_get_post_video_oembed_url' ) ) {
		return wpex_get_post_video_oembed_url( $post_id );
	}
}

/**
 * Get post video oembed URL.
 */
function vcex_get_video_embed_url( $video = '' ) {
	if ( function_exists( 'wpex_get_video_embed_url' ) ) {
		return wpex_get_video_embed_url( $video );
	}
}

/**
 * Return inline gallery code.
 */
function vcex_parse_inline_lightbox_gallery( $attachements = '' ) {
	if ( function_exists( 'wpex_parse_inline_lightbox_gallery' ) ) {
		return wpex_parse_inline_lightbox_gallery( $attachements );
	}
}

/**
 * Get hover animation class
 */
function vcex_hover_animation_class( $animation = '' ) {
	if ( function_exists( 'wpex_hover_animation_class' ) ) {
		return wpex_hover_animation_class( $animation );
	}
}

/**
 * Get first post term.
 */
function vcex_get_first_term( $post_id = '', $taxonomy = 'category', $terms = '' ) {
	if ( function_exists( 'wpex_get_first_term' ) ) {
		return wpex_get_first_term( $post_id, $taxonomy, $terms );
	}
}

/**
 * Get post first term link.
 */
function vcex_get_first_term_link( $post_id = '', $taxonomy = 'category', $terms = '' ) {
	if ( function_exists( 'wpex_get_first_term_link' ) ) {
		return wpex_get_first_term_link( $post_id, $taxonomy, $terms );
	}
}

/**
 * Get post terms.
 */
function vcex_get_list_post_terms( $taxonomy = 'category', $show_links = true ) {
	if ( function_exists( 'wpex_get_list_post_terms' ) ) {
		return wpex_get_list_post_terms( $taxonomy, $show_links );
	}
}

/**
 * Get pagination.
 */
if ( ! function_exists( 'vcex_pagination' ) ) {
	function vcex_pagination( $query = '', $echo = true ) {
		if ( function_exists( 'wpex_pagination' ) ) {
			return wpex_pagination( $query, $echo );
		}
		if ( $query ) {
			global $wp_query;
			$temp_query = $wp_query;
			$wp_query = $query;
		}
		ob_start();
		posts_nav_link();
		$wp_query = $temp_query;
		return ob_get_clean();
	}
}

/**
 * Filters module grid to return active blocks.
 */
function vcex_filter_grid_blocks_array( $blocks ) {
	$new_blocks = array();
	foreach ( $blocks as $key => $value ) {
		if ( 'true' == $value ) {
			$new_blocks[$key] = '';
		}
	}
	return $new_blocks;
}

/**
 * Returns correct classes for grid modules
 * Does NOT use post_class to prevent conflicts.
 */
function vcex_grid_get_post_class( $classes = array(), $post_id = '', $media_check = true ) {

	// Get post ID
	$post_id = $post_id ? $post_id : get_the_ID();

	// Get post type
	$post_type = get_post_type( $post_id );

	// Add post ID class
	$classes[] = 'post-' . sanitize_html_class( $post_id );

	// Add entry class
	$classes[] = 'entry';

	// Add type class
	$classes[] = 'type-' . sanitize_html_class( $post_type );

	// Add has media class
	if ( $media_check && function_exists( 'wpex_post_has_media' ) ) {
		if ( wpex_post_has_media( $post_id, true ) ) {
			$classes[] = 'has-media';
		} else {
			$classes[] = 'no-media';
		}
	}

	// Add terms
	if ( $terms = vcex_get_post_term_classes( $post_id, $post_type ) ) {
		$classes[] = $terms;
	}

	// Custom link class
	if ( function_exists( 'wpex_get_post_redirect_link' ) && wpex_get_post_redirect_link() ) {
		$classes[] = 'has-redirect';
	}

	// Apply filters
	$classes = apply_filters( 'vcex_grid_get_post_class', $classes );

	// Sanitize classes
	$classes = array_map( 'esc_attr', $classes );

	// Return class
	return 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';

}

/**
 * Returns entry classes for vcex module entries.
 *
 */
function vcex_get_post_term_classes( $post_id = '', $post_type = '' ) {

	if ( ! defined( 'TOTAL_THEME_ACTIVE' ) ) {
		return array();
	}

	$post_id = $post_id ? $post_id : get_the_ID();
	$post_type = $post_type ? $post_type : get_post_type( $post_id );

	// Define vars
	$classes = array();

	// Loop through tax objects and save in taxonomies var
	$taxonomies = get_object_taxonomies( $post_type, 'names' );

	// Return of there is an error
	if ( is_wp_error( $taxonomies ) || ! $taxonomies ) {
		return;
	}

	// Loop through taxomies
	foreach ( $taxonomies as $tax ) {

		// Get terms
		$terms = get_the_terms( $post_id, $tax );

		// Make sure terms aren't empty before loop
		if ( $terms && ! is_wp_error( $terms ) ) {

			// Loop through terms
			foreach ( $terms as $term ) {

				// Set prefix as taxonomy name
				$prefix = esc_html( $term->taxonomy );

				// Add class if we have a prefix
				if ( $prefix ) {

					// Get total post types to parse
					$parse_types = vcex_theme_post_types();
					if ( in_array( $post_type, $parse_types ) ) {
						$search  = array( $post_type . '_category', $post_type . '_tag' );
						$replace = array( 'cat', 'tag' );
						$prefix  = str_replace( $search, $replace, $prefix );
					}

					// Category prefix
					if ( 'category' == $prefix ) {
						$prefix = 'cat';
					}

					// Add term
					$classes[] = sanitize_html_class( $prefix . '-' . $term->term_id );

					// Add term parent
					if ( $term->parent ) {
						$classes[] = sanitize_html_class( $prefix . '-' . $term->parent );
					}

				}

			}
		}
	}

	// Sanitize classes
	$classes_escaped = array_map( 'esc_attr', $classes );

	// Return classes
	return $classes_escaped ? implode( ' ', $classes_escaped ) : '';

}

/**
 * Returns correct class for columns.
 */
function vcex_get_grid_column_class( $atts ) {
	if ( isset( $atts['single_column_style'] ) && 'left_thumbs' == $atts['single_column_style'] ) {
		return;
	}
	$return_class = '';
	if ( isset( $atts['columns'] ) ) {
		$return_class .= ' span_1_of_' . sanitize_html_class( $atts['columns'] );
	}
	if ( ! empty( $atts['columns_responsive_settings'] ) ) {
		$rs = vcex_parse_multi_attribute( $atts['columns_responsive_settings'], array() );
		foreach ( $rs as $key => $val ) {
			if ( $val ) {
				$return_class .= ' span_1_of_' . sanitize_html_class( $val ) . '_' . sanitize_html_class( $key );
			}
		}
	}
	return trim( $return_class );
}

/**
 * Returns correct CSS for custom button color based on style.
 */
function vcex_get_button_custom_color_css( $style = '', $color ='' ) {
	if ( function_exists( 'wpex_get_button_custom_color_css' ) ) {
		return wpex_get_button_custom_color_css( $style, $color );
	}
}

/**
 * Get carousel settings.
 */
function vcex_get_carousel_settings( $atts, $shortcode ) {

	$settings = array(
		'nav'                  => ! empty( $atts['arrows'] ) ? $atts['arrows'] : 'true',
		'dots'                 => ! empty( $atts['dots'] ) ? $atts['dots'] : 'false',
		'autoplay'             => ! empty( $atts['auto_play'] ) ? $atts['auto_play'] : 'false',
		'loop'                 => ! empty( $atts['infinite_loop'] ) ? $atts['infinite_loop'] : 'true',
		'center'               => ! empty( $atts['center'] ) ? $atts[ 'center'] : 'false',
		'smartSpeed'           => ! empty( $atts['animation_speed'] ) ? absint( $atts['animation_speed'] ) : 250,
		'items'                => ! empty( $atts['items'] ) ? intval( $atts['items'] ) : 4,
		'slideBy'              => ! empty( $atts['items_scroll'] ) ? intval( $atts['items_scroll'] ) : 1,
		'autoplayTimeout'      => ! empty( $atts['timeout_duration'] ) ? intval( $atts['timeout_duration'] ) : 5000, // cant be 0
		'margin'               => ! empty( $atts['items_margin'] ) ? absint( $atts['items_margin'] ) : 15,
		'itemsTablet'          => ! empty( $atts['tablet_items'] ) ? absint( $atts['tablet_items'] ) : 3,
		'itemsMobileLandscape' => ! empty( $atts['mobile_landscape_items'] ) ? absint( $atts['mobile_landscape_items'] ) : 2,
		'itemsMobilePortrait'  => ! empty( $atts['mobile_portrait_items'] ) ? absint( $atts['mobile_portrait_items'] ) : 1,
	);

	if ( isset( $atts['style'] ) && $atts['style'] == 'no-margins' ) {
		$settings[ 'margin' ] = 0;
	}

	if ( isset( $atts['auto_width'] ) && 1 !== $settings['items'] ) {
		$settings['autoWidth'] = vcex_esc_attr( $atts[ 'auto_width' ], false );
	}

	if ( isset( $atts[ 'auto_height' ] ) ) {
		$settings['autoHeight'] = vcex_esc_attr( $atts[ 'auto_height' ], false );
	}

	$settings = apply_filters( 'vcex_get_carousel_settings', $settings, $atts, $shortcode );

	foreach( $settings as $k => $v ) {
		if ( 'true' == $v || 'false' == $v ) {
			$settings[$k] = vcex_validate_boolean( $v );
		}
	}

	return htmlspecialchars( wp_json_encode( $settings ) );
}

/**
 * Helper function enqueues icon fonts from Visual Composer.
 */
function vcex_enqueue_icon_font( $family = '', $icon = '' ) {

	// Return if there isn't an icon
	if ( ! $icon ) {
		return;
	}

	// If font family isn't defined lets get it from the icon class
	if ( ! $family ) {
		$family = vcex_get_icon_type_from_class( $icon );
	}

	// Return if we are using ticons
	if ( 'ticons' == $family || ! $family ) {
		wp_enqueue_style( 'ticons' );
		return;
	}

	// Check for custom enqueue
	$fonts = vcex_get_icon_font_families();

	// Custom stylesheet check
	if ( ! empty( $fonts[$family]['style'] ) ) {
		wp_enqueue_style( $fonts[$family]['style'] );
		return;
	}

	// Default vc font icons
	if ( function_exists( 'vc_icon_element_fonts_enqueue' ) ) {
		vc_icon_element_fonts_enqueue( $family );
	}

}

/**
 * Returns animation class and loads animation js.
 */
function vcex_get_css_animation( $css_animation = '' ) {
	if ( defined( 'WPB_VC_VERSION' ) && $css_animation && 'none' !== $css_animation ) {
		wp_enqueue_script( 'vc_waypoints' );
		wp_enqueue_style( 'vc_animate-css' );
		$css_animation = sanitize_html_class( $css_animation );
		return ' wpb_animate_when_almost_visible wpb_' . sanitize_html_class( $css_animation ) . ' ' . esc_attr( $css_animation );
	}
}

/**
 * Return unique ID for responsive class.
 */
function vcex_get_reponsive_unique_id( $unique_id = '' ) {
	return $unique_id ? '.wpex-' . $unique_id : uniqid( 'wpex-' );
}

/**
 * Return responsive font-size data.
 */
function vcex_get_responsive_font_size_data( $value ) {

	// Font size is needed
	if ( ! $value ) {
		return;
	}

	// Not needed for simple font_sizes
	if ( strpos( $value, '|' ) === false ) {
		return;
	}

	// Parse data to return array
	$data = vcex_parse_multi_attribute( $value );

	if ( ! $data && ! is_array( $data ) ) {
		return;
	}

	$sanitized_data = array();

	// Sanitize
	foreach ( $data as $key => $val ) {
		$sanitized_data[$key] = vcex_validate_font_size( $val, 'font_size' );
	}

	return $sanitized_data;

}

/**
 * Return responsive font-size data.
 */
function vcex_get_module_responsive_data( $atts, $type = '' ) {

	if ( ! $atts ) {
		return; // No need to do anything if atts is empty
	}

	$return      = array();
	$parsed_data = array();
	$settings    = array( 'font_size' );

	if ( $type && ! is_array( $atts ) ) {
		$settings = array( $type );
		$atts = array( $type => $atts );
	}

	foreach ( $settings as $setting ) {

		if ( 'font_size' == $setting ) {

			// Get value from params
			$value = isset( $atts['font_size'] ) ? $atts['font_size'] : '';

			// Value needed
			if ( ! $value ) {
				break;
			}

			// Get font size data
			$value = vcex_get_responsive_font_size_data( $value );

			// Add to new array
			if ( $value ) {
				$parsed_data['font-size'] = $value;
			}

		} // End font_size

	} // End foreach

	// Return
	if ( $parsed_data ) {
		return "data-wpex-rcss='" . htmlspecialchars( wp_json_encode( $parsed_data ) ) . "'";
	}

}

/**
 * Get Extra class.
 */
function vcex_get_extra_class( $classes = '' ) {
	$classes = trim( $classes );
	if ( $classes ) {
		return esc_attr( str_replace( '.', '', $classes ) );
	}
}

/**
 * Parses lightbox dimensions.
 */
function vcex_parse_lightbox_dims( $dims = '', $return = '' ) {

	// Return if no dims
	if ( ! $dims ) {
		return;
	}

	// Parse data
	$dims = explode( 'x', $dims );
	$w    = isset( $dims[0] ) ? absint( $dims[0] ) : null;
	$h    = isset( $dims[1] ) ? absint( $dims[1] ) : null;

	// Width and height required
	if ( ! $w || ! $h ) {
		return;
	}

	// Return width
	if ( 'width' == $return ) {
		return $w;
	}

	// Return height
	elseif ( 'height' == $return ) {
		return $h;
	}

	// Return height
	elseif ( 'array' == $return ) {
		return array(
			'width'  => $w,
			'height' => $h,
		);
	}

	// Return dimensions (deprecated in version 1.0.4)
	else {
		return 'width:' . esc_attr( $w ) . ',height:' . esc_attr( $h );
	}

}

/**
 * Generates various types of HTML based on a value.
 *
 * @todo deprecate
 */
function vcex_html( $type, $value, $trim = false ) {

	// Return nothing by default
	$return = '';

	// Return if value is empty
	if ( ! $value ) {
		return;
	}

	// Title attribute
	if ( 'id_attr' == $type ) {
		$value  = trim ( str_replace( '#', '', $value ) );
		$value  = str_replace( ' ', '', $value );
		if ( $value ) {
			$return = ' id="'. esc_attr( $value ) .'"';
		}
	}

	// Title attribute
	if ( 'title_attr' == $type ) {
		$return = ' title="'. esc_attr( $value ) .'"';
	}

	// Link Target
	elseif ( 'target_attr' == $type ) {
		if ( 'blank' == $value
			|| '_blank' == $value
			|| strpos( $value, 'blank' ) ) {
			$return = ' target="_blank"';
		}
	}

	// Link rel
	elseif ( 'rel_attr' == $type ) {
		if ( 'nofollow' == $value ) {
			$return = ' rel="nofollow"';
		}
	}

	// Return HTMl
	if ( $trim ) {
		return trim( $return );
	} else {
		return $return;
	}

}

/**
 * Notice when no posts are found.
 */
function vcex_no_posts_found_message( $atts ) {
	$message = null;
	if ( vcex_vc_is_inline() ) {
		$message = '<div class="vcex-no-posts-found">' . esc_html__( 'No posts found for your query.', 'total-theme-core' ) . '</div>';
	}
	return apply_filters( 'vcex_no_posts_found_message', $message, $atts );
}

/**
 * Echos unique ID html for VC modules.
 */
function vcex_unique_id( $id = '' ) {
	echo vcex_get_unique_id( $id );
}

/**
 * Returns unique ID html for VC modules.
 */
function vcex_get_unique_id( $id = '' ) {
	if ( $id ) {
		return ' id="' . esc_attr( $id ) . '"'; // do not remove empty space at front!!
	}
}

/**
 * Returns lightbox image.
 */
function vcex_get_lightbox_image( $thumbnail_id = '' ) {
	if ( function_exists( 'wpex_get_lightbox_image' ) ) {
		return wpex_get_lightbox_image( $thumbnail_id );
	} else {
		return esc_url( wp_get_attachment_url(  $thumbnail_id ) );
	}
}

/**
 * Returns attachment data
 */
function vcex_get_attachment_data( $attachment = '', $return = 'array' ) {

	if ( function_exists( 'wpex_get_attachment_data' ) ) {
		return wpex_get_attachment_data( $attachment, $return );
	}

	if ( ! $attachment || 'none' == $return ) {
		return;
	}

	switch ( $return ) {
		case 'url':
		case 'src':
			return wp_get_attachment_url( $attachment );
			break;
		case 'alt':
			return get_post_meta( $attachment, '_wp_attachment_image_alt', true );
			break;
		case 'title':
			return get_the_title( $attachment );
			break;
		case 'caption':
			return wp_get_attachment_caption( $attachment );
			break;
		case 'description':
			return get_post_field( 'post_content', $attachment );
			break;
		case 'video':
			return esc_url( get_post_meta( $attachment, '_video_url', true ) );
			break;
		default:

			$url = wp_get_attachment_url( $attachment );

			return array(
				'url'         => $url,
				'src'         => $url, // fallback
				'alt'         => get_post_meta( $attachment, '_wp_attachment_image_alt', true ),
				'title'       => get_the_title( $attachment ),
				'caption'     => wp_get_attachment_caption( $attachment ),
				'description' => get_post_field( 'post_content', $attachment ),
				'video'       => esc_url( get_post_meta( $attachment, '_video_url', true ) ),
			);
			break;
	}

}

/**
 * Returns post gallery ID's
 */
function vcex_get_post_gallery_ids( $post_id = '' ) {
	$filter_val = apply_filters( 'vcex_pre_get_post_gallery_ids', null );
	if ( $filter_val ) {
		return $filter_val;
	}
	if ( function_exists( 'wpex_get_gallery_ids' ) ) {
		return wpex_get_gallery_ids( $post_id );
	}
	$attachment_ids = '';
	$post_id = $post_id ? $post_id : vcex_get_the_ID();
	if ( class_exists( 'WC_product' ) && 'product' == get_post_type( $post_id ) ) {
		$product = new WC_product( $post_id );
		if ( $product && method_exists( $product, 'get_gallery_image_ids' ) ) {
			$attachment_ids = $product->get_gallery_image_ids();
		}
	}
	$attachment_ids = $attachment_ids ? $attachment_ids : get_post_meta( $post_id, '_easy_image_gallery', true );
	if ( $attachment_ids ) {
		$attachment_ids = is_array( $attachment_ids ) ? $attachment_ids : explode( ',', $attachment_ids );
		$attachment_ids = array_values( array_filter( $attachment_ids, 'wpex_sanitize_gallery_id' ) );
		return apply_filters( 'wpex_get_post_gallery_ids', $attachment_ids );
	}
}

/**
 * Used to enqueue styles for Visual Composer modules.
 */
function vcex_enque_style( $type, $value = '' ) {

	if ( 'ilightbox' == $type || 'lightbox' == $type ) {
		if ( function_exists( 'wpex_enqueue_lightbox_scripts' ) ) {
			wpex_enqueue_lightbox_scripts();
		} elseif ( function_exists( 'wpex_enqueue_ilightbox_skin' ) ) {
			wpex_enqueue_ilightbox_skin( $value );
		}
	}

	// Hover animation
	elseif ( 'hover-animations' == $type ) {
		wp_enqueue_style( 'wpex-hover-animations' );
	}

}

/**
 * Border Radius Classname.
 */
function vcex_get_border_radius_class( $val ) {
	if ( 'none' == $val || '' == $val ) {
		return;
	}
	return 'wpex-' . sanitize_html_class( $val );
}

/**
 * Helper function for building links using link param.
 */
function vcex_build_link( $link, $fallback = '' ) {

	// If empty return fallback
	if ( empty( $link ) ) {
		return $fallback;
	}

	// Return if there isn't any link
	if ( '||' == $link || '|||' == $link || '||||' == $link ) {
		return;
	}

	// Return simple link escaped (fallback for old textfield input)
	if ( false === strpos( $link, 'url:' ) ) {
		return esc_url( $link );
	}

	// Build link
	// Needs to use total function to fix issue with fallbacks
	$link = vcex_parse_multi_attribute( $link, array( 'url' => '', 'title' => '', 'target' => '', 'rel' => '' ) );

	// Sanitize
	$link = is_array( $link ) ? array_map( 'trim', $link ) : '';

	// Return link
	return $link;

}

/**
 * Returns link data (used for fallback link settings).
 */
function vcex_get_link_data( $return, $link, $fallback = '' ) {

	$link = vcex_build_link( $link, $fallback );

	if ( 'url' == $return ) {
		if ( is_array( $link ) && ! empty( $link['url'] ) ) {
			return $link['url'];
		} else {
			return is_array( $link ) ? $fallback : $link;
		}
	}

	if ( 'title' == $return ) {
		if ( is_array( $link ) && ! empty( $link['title'] ) ) {
			return $link['title'];
		} else {
			return $fallback;
		}
	}

	if ( 'target' == $return ) {
		if ( is_array( $link ) && ! empty( $link['target'] ) ) {
			return $link['target'];
		} else {
			return $fallback;
		}
	}

	if ( 'rel' == $return ) {
		if ( is_array( $link ) && ! empty( $link['rel'] ) ) {
			return $link['rel'];
		} else {
			return $fallback;
		}
	}

}

/**
 * Get source value
 */
function vcex_get_source_value( $source = '', $atts = array() ) {
	if ( empty( $source ) ) {
		return;
	}
	$source_val = new VCEX_Source_Value( $source, $atts );
	return $source_val->get_value();
}

/**
 * Return shortcode CSS.
 */
function vcex_wpb_shortcodes_custom_css( $post_id = '' ) {

	$css = '';

	$meta = get_post_meta( $post_id, '_wpb_shortcodes_custom_css', true );

	if ( $meta ) {
		$css .= '<style data-type="vc_shortcodes-custom-css">';
			$css .= wp_strip_all_tags( $meta );
		$css .= '</style>';
	}

	return $css;

}
