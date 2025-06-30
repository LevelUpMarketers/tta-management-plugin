<?php
/**
 * vcex_social_links shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_social_links';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
extract( vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this ) );

// Get social profiles array | Used for fallback method and to grab icon styles
$social_profiles = (array) vcex_social_links_profiles();

// Social profile array can't be empty
if ( ! $social_profiles ) {
	return;
}

// Define output var
$output = '';

// Sanitize style
$style = $style ? $style : 'flat';
$expand = vcex_validate_boolean( $expand );

// Get current author social links
if ( 'true' == $author_links ) {

	$post_tmp    = get_post( vcex_get_the_ID() );
	$post_author = $post_tmp->post_author;

	if ( ! $post_author ) {
		return;
	}

	$loop = array();
	$social_settings = wpex_get_user_social_profile_settings_array();

	foreach ( $social_settings as $id => $label ) {

		if ( $url = get_the_author_meta( 'wpex_'. $id, $post_author ) ) {

			$loop[$id] = $url;

		}

	}

	$post_tmp = '';

} else {

	// Display custom social links
	// New method since 3.5.0 | must check $atts value due to fallback and default var
	if ( ! empty( $atts['social_links'] ) ) {
		$social_links = (array) vcex_vc_param_group_parse_atts( $social_links );
		$loop = array();
		foreach ( $social_links as $key => $val ) {
			$loop[$val['site']] = isset( $val['link'] ) ? do_shortcode( $val['link'] ) : '';
		}
	} else {
		$loop = $social_profiles;
	}

}

// Loop is required
if ( ! is_array( $loop ) ) {
	return;
}

// Wrap attributes
$wrap_attrs = array(
	'id'   => $unique_id,
	'data' => '',
);

// Wrap classes
$wrap_classes = array( 'vcex-module' );
$wrap_classes[] = 'wpex-social-btns vcex-social-btns';

if ( $expand ) {
	$wrap_classes[] = 'wpex-flex';
	$wrap_classes[] = 'wpex-flex-wrap';
}

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $align ) {
	$wrap_classes[] = 'text' . sanitize_html_class( $align );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

$wrap_classes[] = 'wpex-last-mr-0';

// Wrap style
$wrap_style = vcex_inline_style( array(
	'color'         => $color,
	'font_size'     => $size,
	'border_radius' => $border_radius,
), false );

// Link Classes
$link_class   = array();
$link_class[] = vcex_get_social_button_class( $style );
$spacing = $spacing ? absint( $spacing ) : '5';
$link_class[] = 'wpex-mb-' . sanitize_html_class( $spacing );
$link_class[] = 'wpex-mr-' . sanitize_html_class( $spacing );

if ( $width || $height ) {

	if ( empty( $line_height ) && $height ) {
		$line_height = intval( $height ) . 'px';
	}

	$a_style = vcex_inline_style( array(
		'min_width'   => $width,
		'height'      => $height,
		'line_height' => $line_height,
	), false );

}

// Reset social button widths/paddings
if ( $expand || 'true' == $show_label ) {

	$link_class[] = 'wpex-flex-grow';
	$link_class[] = 'wpex-w-auto';
	$link_class[] = 'wpex-h-auto';
	$link_class[] = 'wpex-leading-normal';

	if ( empty( $padding_y ) ) {
		$padding_y = '5';
	}

	if ( empty( $padding_x ) ) {
		$padding_x = '15';
	}

}

// Vertical padding
if ( $padding_y ) {
	$link_class[] = 'wpex-py-' . absint( $padding_y );
}

// Horizontal padding
if ( $padding_x ) {
	$link_class[] = 'wpex-px-' . absint( $padding_x );
}

if ( $hover_animation ) {
	$link_class[] = vcex_hover_animation_class( $hover_animation );
	vcex_enque_style( 'hover-animations' );
}

if ( $css ) {
	$link_class[] = vcex_vc_shortcode_custom_css_class( $css );
}

// Hover data
$a_hover_data = array();

if ( $hover_bg ) {
	$a_hover_data['background'] = esc_attr( $hover_bg );
}

if ( $hover_color ) {
	$a_hover_data['color'] = esc_attr( $hover_color );
}

$a_hover_data = $a_hover_data ? htmlspecialchars( wp_json_encode( $a_hover_data ) ) : '';

// Responsive settings
if ( $responsive_data = vcex_get_module_responsive_data( $size, 'font_size' ) ) {
	$wrap_attrs['data-wpex-rcss'] = $responsive_data;
}

// Add attributes to array
$wrap_attrs['class'] = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );
$wrap_attrs['style'] = $wrap_style;

// Begin output
$output .= '<div' . vcex_parse_html_attributes(  $wrap_attrs ) . '>';

	// Loop through social profiles
	foreach ( $loop as $key => $val ) {

		// Google plus was shut down
		if ( 'googleplus' == $key || 'google-plus' == $key ) {
			continue;
		}

		// Sanitize classname
		$profile_class = $key;
		$profile_class = 'googleplus' == $key ? 'google-plus' : $key;

		// Get URL
		if ( 'true' != $author_links && empty( $atts['social_links'] ) ) {
			$url = isset( $atts[$key] ) ? $atts[$key] : '';
		} else {
			$url = $val;
		}

		// Link output
		if ( $url ) {

			$a_attrs = array(
				'href'   => esc_url( do_shortcode( $url ) ),
				'class'  => esc_attr( implode( ' ', $link_class ) . ' wpex-' . $profile_class ),
				'target' => $link_target,
			);

			if ( ! empty( $a_style ) ) {
				$a_attrs['style'] = $a_style;
			}

			if ( $a_hover_data ) {
				$a_attrs['data-wpex-hover'] = $a_hover_data;
			}

			$output .= '<a '. vcex_parse_html_attributes( $a_attrs ) .'>';

				$icon_class = $social_profiles[$key]['icon_class'];

				if ( 'true' == $show_label ) {
					$icon_class .= ' wpex-mr-10';
				}

				$output .= '<span class="' . esc_attr( $icon_class ) . '" aria-hidden="true"></span>';

				if ( 'true' == $show_label ) {
					$output .= '<span class="vcex-label">' . ucfirst( wp_strip_all_tags( $key ) ) . '</span>';
				} else {
					$output .= '<span class="screen-reader-text">' . ucfirst( wp_strip_all_tags( $key ) ) . '</span>';
				}

			$output .= '</a>';
		}

	}

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
