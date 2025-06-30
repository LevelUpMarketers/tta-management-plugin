<?php
/**
 * Parses old deprecated row CSS in VC
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 *
 * @todo deprecate completely?
 */

defined( 'ABSPATH' ) || exit;

if ( ! apply_filters( 'wpex_vc_parse_deprecated_css_options', true ) ) {
	return;
}

/**
 * Parses deprecated css fields into new css_editor field.
 */
function vcex_parse_deprecated_row_css( $atts, $return = 'temp_class' ) {

	$new_css = '';

	// Margin top
	if ( ! empty( $atts['margin_top'] ) ) {
		$new_css .= 'margin-top: '. wpex_sanitize_data( $atts['margin_top'], 'px-pct' ) .';';
	}

	// Margin bottom
	if ( ! empty( $atts['margin_bottom'] ) ) {
		$new_css .= 'margin-bottom: '. wpex_sanitize_data( $atts['margin_bottom'], 'px-pct' ) .';';
	}

	// Margin right
	if ( ! empty( $atts['margin_right'] ) ) {
		$new_css .= 'margin-right: '. wpex_sanitize_data( $atts['margin_right'], 'px-pct' ) .';';
	}

	// Margin left
	if ( ! empty( $atts['margin_left'] ) ) {
		$new_css .= 'margin-left: '. wpex_sanitize_data( $atts['margin_left'], 'px-pct' ) .';';
	}

	// Padding top
	if ( ! empty( $atts['padding_top'] ) ) {
		$new_css .= 'padding-top: '. wpex_sanitize_data( $atts['padding_top'], 'px-pct' ) .';';
	}

	// Padding bottom
	if ( ! empty( $atts['padding_bottom'] ) ) {
		$new_css .= 'padding-bottom: '. wpex_sanitize_data( $atts['padding_bottom'], 'px-pct' ) .';';
	}

	// Padding right
	if ( ! empty( $atts['padding_right'] ) ) {
		$new_css .= 'padding-right: '. wpex_sanitize_data( $atts['padding_right'], 'px-pct' ) .';';
	}

	// Padding left
	if ( ! empty( $atts['padding_left'] ) ) {
		$new_css .= 'padding-left: '. wpex_sanitize_data( $atts['padding_left'], 'px-pct' ) .';';
	}

	// Border
	if ( ! empty( $atts['border_width'] ) && ! empty( $atts['border_color'] ) ) {
		$border_width = explode( ' ', $atts['border_width'] );
		$border_style = isset( $atts['border_style'] ) ? $atts['border_style'] : 'solid';
		$bcount = count( $border_width );
		if ( '1' == $bcount ) {
			$new_css .= 'border: '. $border_width[0] . ' '. $border_style .' '. $atts['border_color'] .';';
		} else {
			$new_css .= 'border-color: '. $atts['border_color'] .';';
			$new_css .= 'border-style: '. $border_style .';';
			if ( '2' == $bcount ) {
				$new_css .= 'border-top-width: '. $border_width[0] .';';
				$new_css .= 'border-bottom-width: '. $border_width[0] .';';
				$bw = isset( $border_width[1] ) ? $border_width[1] : '0px';
				$new_css .= 'border-left-width: '. $bw .';';
				$new_css .= 'border-right-width: '. $bw .';';
			} else {
				$new_css .= 'border-top-width: '. $border_width[0] .';';
				$bw = isset( $border_width[1] ) ? $border_width[1] : '0px';
				$new_css .= 'border-right-width: '. $bw .';';
				$bw = isset( $border_width[2] ) ? $border_width[2] : '0px';
				$new_css .= 'border-bottom-width: '. $bw .';';
				$bw = isset( $border_width[3] ) ? $border_width[3] : '0px';
				$new_css .= 'border-left-width: '. $bw .';';
			}
		}
	}

	// Background image
	if ( ! empty( $atts['bg_image'] ) ) {
		if ( 'temp_class' == $return ) {
			$bg_image = wp_get_attachment_url( $atts['bg_image'] ) .'?id='. $atts['bg_image'];
		} elseif ( 'inline_css' == $return ) {
			if ( is_numeric( $atts['bg_image'] ) ) {
				$bg_image = wp_get_attachment_url( $atts['bg_image'] );
			} else {
				$bg_image = $atts['bg_image'];
			}
		}
	}

	// Background Image & Color
	if ( ! empty( $bg_image ) && ! empty( $atts['bg_color'] ) ) {
		$style = ! empty( $atts['bg_style'] ) ? $atts['bg_style'] : 'stretch';
		$position = '';
		$repeat   = '';
		$size     = '';
		if ( 'stretch' == $style ) {
			$position = 'center';
			$repeat   = 'no-repeat';
			$size     = 'cover';
		}
		if ( 'fixed' == $style ) {
			$position = '0 0';
			$repeat   = 'no-repeat';
		}
		if ( 'repeat' == $style ) {
			$position = '0 0';
			$repeat   = 'repeat';
		}
		$new_css .= 'background: '. $atts['bg_color'] .' url('. $bg_image .' );';
		if ( $position ) {
			$new_css .= 'background-position: '. $position .';';
		}
		if ( $repeat ) {
			$new_css .= 'background-repeat: '. $repeat .';';
		}
		if ( $size ) {
			$new_css .= 'background-size: '. $size .';';
		}
	}

	// Background Image - No Color
	if ( ! empty( $bg_image ) && empty( $atts['bg_color'] ) ) {
		$new_css .= 'background-image: url('. $bg_image .' );'; // Add image
		$style = ! empty( $atts['bg_style'] ) ? $atts['bg_style'] : 'stretch'; // Generate style
		$position = '';
		$repeat   = '';
		$size     = '';
		if ( 'stretch' == $style ) {
			$position = 'center';
			$repeat   = 'no-repeat';
			$size     = 'cover';
		}
		if ( 'fixed' == $style ) {
			$position = '0 0';
			$repeat   = 'no-repeat';
		}
		if ( 'repeat' == $style ) {
			$position = '0 0';
			$repeat   = 'repeat';
		}
		if ( $position ) {
			$new_css .= 'background-position: '. $position .';';
		}
		if ( $repeat ) {
			$new_css .= 'background-repeat: '. $repeat .';';
		}
		if ( $size ) {
			$new_css .= 'background-size: '. $size .';';
		}
	}

	// Background Color - No Image
	if ( ! empty( $atts['bg_color'] ) && empty( $bg_image ) ) {
		$new_css .= 'background-color: '. $atts['bg_color'] .';';
	}

	// Return new css
	if ( $new_css ) {
		if ( 'temp_class' == $return ) {
			return '.temp{'. $new_css .'}';
		} elseif ( 'inline_css' == $return ) {
			return $new_css;
		}
	}

}