<?php
/**
 * Alter form fields when clicking edit on various modules
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parses icon parameter to make sure the icon & icon_type is set properly
 */
function vcex_parse_icon_param( $atts, $icon_param = 'icon', $icon_type_param = 'icon_type' ) {
	$icon = ! empty( $atts[$icon_param] ) ? $atts[$icon_param] : '';
	if ( $icon && empty( $atts[$icon_type_param] ) ) {
		$get_icon_type = vcex_get_icon_type_from_class( $icon );
		$atts[$icon_type_param] = ( 'ticons' == $get_icon_type ) ? '' : $get_icon_type;
		if ( 'fontawesome' == $get_icon_type ) {
			$atts[$icon_param . '_fontawesome'] = $icon;
		} elseif ( 'ticons' == $get_icon_type ) {
			$atts[$icon_param] = str_replace( 'fa fa-', 'ticon ticon-', $icon );
		} elseif ( ! $get_icon_type ) {
			$atts[$icon_param] = vcex_add_default_icon_prefix( $icon );
		}
	}
	return $atts;
}

/**
 * Sets the default image size to "full" if it's set to custom but img height and width are empty.
 *
 * @deprecated 1.1
 * @todo remove completely.
 */
function vcex_parse_image_size( $atts ) {
	$img_size = ( isset( $atts['img_size'] ) && 'wpex_custom' == $atts['img_size'] ) ? 'wpex_custom' : '';
	$img_size = empty( $atts['img_size'] ) ? 'wpex_custom' : '';
	if ( 'wpex_custom' == $img_size && empty( $atts['img_height'] ) && empty( $atts['img_width'] ) ) {
		$atts['img_size'] = 'full';
	}
	return $atts;
}

/**
 * Parses old content CSS params.
 *
 * IMPORTANT: For this to work there MUST be space between : and val in the CSS !!!
 */
function vcex_parse_deprecated_grid_entry_content_css( $atts ) {

	if ( empty( $atts['content_css'] ) ) {

		// Define css var
		$css = '';

		// Background Color
		if ( ! empty( $atts['content_background'] ) ) {
			$css .= 'background-color: ' . $atts['content_background'] . ';';
		}

		// Border
		if ( ! empty( $atts['content_border'] ) ) {
			$border = $atts['content_border'];
			if ( '0px' == $border || 'none' == $border ) {
				$css .= 'border: 0px none rgba(255,255,255,0.01);'; // reset border
			} else {
				$css .= 'border: ' . $border . ';';
			}
		}

		// Padding
		if ( ! empty( $atts['content_padding'] ) ) {
			$css .= 'padding: ' . $atts['content_padding'] . ';';
		}

		// Margin
		if ( ! empty( $atts['content_margin'] ) ) {
			$css .= 'margin: ' . $atts['content_margin'] . ';';
		}

		// Update css var
		if ( $css ) {
			$atts['content_css'] = '.temp{' . wp_strip_all_tags( $css ) . '}';
		}

		// Unset old vars
		unset( $atts['content_background'] );
		unset( $atts['content_padding'] );
		unset( $atts['content_margin'] );
		unset( $atts['content_border'] );

	}

	return $atts;

}