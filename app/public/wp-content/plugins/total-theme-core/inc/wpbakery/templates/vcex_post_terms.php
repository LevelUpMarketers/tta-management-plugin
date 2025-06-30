<?php
/**
 * vcex_post_terms shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_post_terms';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Renamed atts
if ( empty( $atts['archive_link_target'] ) && ! empty( $atts['target'] ) ) {
	$atts['archive_link_target'] = $atts['target'];
}

// Get shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Extract atts
extract( $atts );

// Locate taxonomy if one isn't defined
if ( empty( $taxonomy ) && function_exists( 'wpex_get_post_primary_taxonomy' ) ) {
	$taxonomy = wpex_get_post_primary_taxonomy();
}

// Taxonomy is required
if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
	return;
}

// Load Google Fonts if needed
if ( ! empty( $button_font_family ) ) {
	vcex_enqueue_font( $button_font_family );
}

// Get module style
$module_style = ! empty( $style ) ? $style : 'buttons';

// Define terms
$terms = array();

// Get featured term
if ( 'true' == $first_term_only && function_exists( 'wpex_get_post_primary_term' ) ) {
	$primary_term = wpex_get_post_primary_term( '', $taxonomy );
	if ( $primary_term ) {
		$terms = array( $primary_term );
	}
}

// If terms is empty lets query them
if ( ! $terms ) {

	// Query arguments
	$query_args = array(
		'order'   => $order,
		'orderby' => $orderby,
		'fields'  => 'all',
	);

	// Apply filters to query args
	$query_args = apply_filters( 'vcex_post_terms_query_args', $query_args, $atts );

	// Get terms
	$terms = wp_get_post_terms( vcex_get_the_ID(), $taxonomy, $query_args );

	// Get first term only
	if ( 'true' == $first_term_only ) {
		$terms = array( $terms[0] );
	}

}

// Terms needed
if ( ! $terms || is_wp_error( $terms ) ) {
	return;
}

// Wrap classes
$wrap_classes = array(
	'vcex-post-terms',
	'wpex-clr',
);

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

if ( 'center' == $button_align && 'buttons' == $style ) {
	$wrap_classes[] = 'textcenter';
	$wrap_classes[] = 'wpex-last-mr-0';
}

if ( $button_color && 'buttons' !== $style ) {
	$wrap_classes[] = 'wpex-child-inherit-color';
}

// Define output var
$output = '';

// VC filter
$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Wrap style
if ( 'buttons' !== $module_style ) {

	$wrap_style_args = array(
		'font_family'    => $button_font_family,
		'color'          => $button_color,
		'font_size'      => $button_font_size,
		'font_weight'    => $button_font_weight,
		'text_transform' => $button_text_transform,
		'letter_spacing' => $button_letter_spacing,
	);

}

$wrap_style = ( ! empty( $wrap_style_args ) ) ? vcex_inline_style( $wrap_style_args ) : '';

// Begin output
$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . $wrap_style . '>';

	// Define link vars
	$link_style = '';
	$link_class = array();
	$link_hover_data = array();

	// Button Style Classes and inline styles
	if ( 'buttons' == $module_style ) {

		$link_class = array();

		$link_class[] = vcex_get_button_classes(
			$button_style,
			$button_color_style,
			$button_size,
			$button_align
		);

		$spacing = $spacing ? $spacing : '5';
		$spacing_direction = ( 'right' == $button_align ) ? 'l' : 'r';

		$link_class[] = 'wpex-m' . $spacing_direction . '-' . sanitize_html_class( absint( $spacing ) );
		$link_class[] = 'wpex-mb-' . sanitize_html_class( absint( $spacing ) );

		if ( $css_animation && 'none' != $css_animation ) {
			$link_class[] = vcex_get_css_animation( $css_animation );
		}

		if ( 'false' == $archive_link || ! $archive_link ) {
			$link_class[] = 'wpex-cursor-default';
		}

		// Button Style
		$link_style = vcex_inline_style( array(
			'margin'         => $button_margin,
			'color'          => $button_color,
			'background'     => $button_background,
			'padding'        => $button_padding,
			'font_size'      => $button_font_size,
			'font_weight'    => $button_font_weight,
			'border_radius'  => $button_border_radius,
			'text_transform' => $button_text_transform,
			'font_family'    => $button_font_family,
			'letter_spacing' => $button_letter_spacing,
		) );

		// Button data
		if ( $button_hover_background ) {
			$link_hover_data['background'] = esc_attr( $button_hover_background );
		}

		if ( $button_hover_color ) {
			$link_hover_data['color'] = esc_attr( $button_hover_color );
		}

		$link_hover_data = $link_hover_data ? htmlspecialchars( wp_json_encode( $link_hover_data ) ) : '';

	}

	// Get child_of value
	if ( ! empty( $child_of ) ) {
		$get_child_of = get_term_by( 'slug', trim( $child_of ), $taxonomy );
		if ( $get_child_of ) {
			$child_of_id = $get_child_of->term_id;
		}
	}


	// Get excluded terms
	if ( ! empty( $exclude_terms ) ) {
		$exclude_terms = preg_split( '/\,[\s]*/', $exclude_terms );
	} else {
		$exclude_terms = array();
	}

	// Before Text
	if ( 'inline' == $module_style && ! empty( $before_text ) ) {
		$output .= '<span class="vcex-label">' . do_shortcode( wp_strip_all_tags( $before_text ) ) . '</span> ';
	}

	// Open UL list
	elseif ( 'ul' == $module_style ) {
		$output .= '<ul>';
	}

	// Open OL list
	elseif ( 'ol' == $module_style ) {
		$output .= '<ol>';
	}

	// Loop through terms
	$terms_count = 0;
	$first_run = true;
	foreach ( $terms as $term ) :
		$terms_count ++;

		// Skip items that aren't a child of a specific parent.
		if ( ! empty( $child_of_id ) && $term->parent != $child_of_id ) {
			continue;
		}

		// Skip excluded terms
		if ( in_array( $term->slug, $exclude_terms ) ) {
			continue;
		}

		// Add li tags
		if ( in_array( $module_style, array( 'ul', 'ol' ) ) ) {
			$output .= '<li>';
		}

		// Open term element
		if ( 'true' == $atts['archive_link'] ) {

			$output .= '<a' . vcex_parse_html_attributes( array(
				'href'            => esc_url( get_term_link( $term, $taxonomy ) ),
				'class'           => array_map( 'esc_attr', $link_class ),
				'style'           => $link_style,
				'target'          => $archive_link_target,
				'data-wpex-hover' => $link_hover_data,
			) ) . '>';

		} else {

			$output .= '<span' . vcex_parse_html_attributes( array(
				'class' => array_map( 'esc_attr', $link_class ),
				'style' => $link_style,
				'data-wpex-hover' => $link_hover_data,
			) ) . '>';

		}

		// Display title
		$output .= esc_html( $term->name );

		// Close term element
		if ( 'true' == $archive_link ) {
			$output .= '</a>';
		} else {
			$output .= '</span>';
		}

		// Add spacer for inline style
		if ( 'inline' == $module_style && $terms_count < count( $terms ) ) {

			$spacer = '&comma;';

			$custom_spacer = ! empty( $spacer ) ? $spacer : apply_filters( 'vcex_post_terms_default_spacer', '' );

			if ( $custom_spacer ) {
				$output .= ' ';
				$spacer = $custom_spacer;
			}

			$output .= '<span class="vcex-spacer">' . do_shortcode( wp_strip_all_tags( $spacer ) ) . '</span> ';

		}

		// Close li tags
		if ( in_array( $module_style, array( 'ul', 'ol' ) ) ) {
			$output .= '</li>';
		}

		$first_run = false;

	endforeach;

	// Close UL list
	if ( 'ul' == $module_style ) {
		$output .= '</ul>';
	}

	// Open OL list
	elseif ( 'ol' == $module_style ) {
		$output .= '</ol>';
	}

// Close main wrapper
$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
