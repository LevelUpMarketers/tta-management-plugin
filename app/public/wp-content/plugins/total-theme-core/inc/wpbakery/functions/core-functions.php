<?php
/**
 * Helper functions for custom WPBakery modules.
 *
 * @package TotalThemeCore
 * @version 1.2.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return correct branding.
 */
function vcex_shortcodes_branding() {
	if ( function_exists( 'wpex_get_theme_branding' ) ) {
		return wpex_get_theme_branding();
	}
	return 'Total Theme';
}

/**
 * Total exclusive setting notice.
 */
function vcex_total_exclusive_notice() {
	return '<div class="vcex-t-exclusive">' . esc_html__( 'This is a Total theme exclusive function.', 'total-theme-core' ) . '</div>';
}

/**
 * Locate shortcode template.
 */
function vcex_get_shortcode_template( $shortcode_tag ) {
	$user_template = locate_template( 'vcex_templates/' . $shortcode_tag . '.php' );
	if ( $user_template ) {
		return $user_template;
	}
	return TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/templates/' . $shortcode_tag . '.php';
}

/**
 * Check if a given shortcode should display.
 */
function vcex_maybe_display_shortcode( $shortcode_tag, $atts ) {

	$check = true;

	if ( is_admin() && ! wp_doing_ajax() ) {
		$check = false; // shortcodes are for front-end only. Prevents issues with Gutenberg. !!! important !!!
	}

	return (bool) apply_filters( 'vcex_maybe_display_shortcode', $check, $shortcode_tag, $atts );
}

/**
 * Call any shortcode function by it's tagname.
 */
function vcex_do_shortcode_function( $tag, $atts = array(), $content = null ) {
	global $shortcode_tags;

    if ( ! isset( $shortcode_tags[ $tag ] ) ) {
        return false;
    }

    return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
}

/**
 * Return correct asset path.
 */
function vcex_asset_url( $part = '' ) {
	return TTC_PLUGIN_DIR_URL . 'inc/wpbakery/assets/' . $part;
}

/**
 * Return correct asset dir path.
 */
function vcex_asset_dir_path( $part = '' ) {
	return TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/assets/' . $part;
}

/**
 * Check if currently working in the wpbakery front-end editor.
 */
function vcex_vc_is_inline() {
	if ( function_exists( 'vc_is_inline' ) ) {
		return vc_is_inline();
	}
	return false; // prevents things from running if wpbakery is disabled
}

/**
 * Get post type cat tax.
 */
function vcex_get_post_type_cat_tax( $post_type = '' ) {
	if ( function_exists( 'wpex_get_post_type_cat_tax' ) ) {
		return wpex_get_post_type_cat_tax( $post_type );
	}
	$post_type = $post_type ? $post_type : get_post_type();
	$tax = '';
	if ( 'post' == $post_type ) {
		$tax = 'category';
	} elseif ( 'portfolio' == $post_type ) {
		$tax = 'portfolio_category';
	} elseif ( 'staff' == $post_type ) {
		$tax = 'staff_category';
	} elseif ( 'testimonials' == $post_type ) {
		$tax = 'testimonials_category';
	}
	return apply_filters( 'wpex_get_post_type_cat_tax', $tax, $post_type );
}

/**
 * Wrapper for intval with fallback.
 */
function vcex_intval( $val = null, $fallback = null ) {
	if ( 0 == $val ) {
		return 0; // Some settings may need this
	}
	$val = intval( $val );
	return $val ? $val : intval( $fallback );
}

/**
 * WPBakery vc_param_group_parse_atts wrapper function
 */
function vcex_vc_param_group_parse_atts( $atts_string ) {
	if ( function_exists( 'vc_param_group_parse_atts' ) ) {
		return vc_param_group_parse_atts( $atts_string );
	}
	$array = json_decode( urldecode( $atts_string ), true );
	return $array;
}

/**
 * Takes array of html attributes and converts into a string.
 */
function vcex_parse_html_attributes( $attrs ) {

	if ( ! $attrs || ! is_array( $attrs ) ) {
		return $attrs;
	}

	// Define output
	$output = '';

	// Add noopener noreferrer automatically to nofollow links if rel attr isn't set
	if ( isset( $attrs['href'] )
		&& isset( $attrs['target'] )
		&& in_array( $attrs['target'], array( '_blank', 'blank' ) )
	) {
		$rel = apply_filters( 'wpex_targeted_link_rel', 'noopener noreferrer', $attrs['href'] );
		if ( ! empty( $rel ) ) {
			if ( ! empty( $attrs['rel'] ) ) {
				$attrs['rel'] .= ' ' . $rel;
			} else {
				$attrs['rel'] = $rel;
			}
		}
	}

	// Loop through attributes
	foreach ( $attrs as $key => $val ) {

		// Skip
		if ( 'content' == $key ) {
			continue;
		}

		// If the attribute is an array convert to string
		if ( is_array( $val ) ) {
			$val = array_filter( $val, 'trim' ); // Remove extra space
			$val = implode( ' ', $val );
		}

		// Sanitize rel attribute
		if ( 'rel' == $key ) {
			$val = wp_strip_all_tags( $val );
		}

		// Sanitize id
		elseif ( 'id' == $key ) {
			$val = trim ( str_replace( '#', '', $val ) );
			$val = str_replace( ' ', '', $val );
		}

		// Sanitize class
		elseif ( 'class' == $key ) {
			$val = esc_attr( trim( $val ) );
		}

		// Sanitize targets
		elseif ( 'target' == $key ) {
			$val = ( 'blank' == $val ) ? '_blank' : $val;
			if ( ! in_array( $val, array( '_blank', 'blank', '_self', '_parent', '_top' ) ) ) {
				$val = '';
			}
		}

		// Add attribute to output
		if ( $val ) {

			// Add download attribute (doesn't have values)
			if ( in_array( $key, array( 'download' ) ) ) {
				$output .= ' ' . trim( $val ); // Used for example on total button download attribute
			}

			// Add attribute | value
			else {
				$needle = ( 'data' == $key ) ? 'data-' : $key . '=';
				if ( $val && strpos( $val, $needle ) !== false ) {
					$output .= ' ' . trim( $val ); // Already has tag added
				} else {
					$output .= ' ' . $key . '="' . $val . '"';
				}
			}

		}

		// Items with empty vals
		else {

			// Empty alts are allowed
			if ( 'alt' == $key ) {
				$output .= " alt='" . esc_attr( $val ) . "'";
			}

			// Data attributes
			elseif ( strpos( $key, 'data-' ) !== false ) {
				$output .= ' ' . $key;
			}

		}

	}

	// Return output
	return ' ' . trim( $output ); // Must always have empty space infront
}

/**
 * Validate Font Size.
 */
function vcex_validate_font_size( $input ) {
	if ( strpos( $input, 'px' ) || strpos( $input, 'em' ) || strpos( $input, 'vw' ) || strpos( $input, 'vmin' ) || strpos( $input, 'vmax' ) ) {
		$input = esc_html( $input );
	} else {
		$input = absint( $input ) . 'px';
	}
	if ( $input != '0px' && $input != '0em' ) {
		return esc_html( $input );
	}
	return '';
}

/**
 * Validate Boolean.
 */
function vcex_validate_boolean( $var ) {
	if ( is_bool( $var ) ) {
        return $var;
    }
    if ( is_string( $var ) ) {
		if ( 'true' === $var || 'yes' === $var ) {
			return true;
		} elseif ( 'false' === $var || 'no' === $var ) {
			return false;
		}
	}
	return (bool) $var;
}

/**
 * Validate px.
 */
function vcex_validate_px( $input ) {
	if ( ! $input ) {
		return;
	}
	if ( 'none' == $input ) {
		return '0';
	} else {
		return floatval( $input ) . 'px';
	}
}

/**
 * Validate px or percentage value.
 */
function vcex_validate_px_pct( $input ) {
	if ( ! $input ) {
		return;
	}
	if ( 'none' == $input || '0px' == $input ) {
		return '0';
	} elseif ( strpos( $input, '%' ) ) {
		return wp_strip_all_tags( $input );
	} elseif ( $input = floatval( $input ) ) {
		return wp_strip_all_tags( $input ) . 'px';
	}
}

/**
 * Get site default font size.
 */
function vcex_get_body_font_size() {
	if ( function_exists( 'wpex_get_body_font_size' ) ) {
		return wpex_get_body_font_size();
	}
	return apply_filters( 'vcex_get_body_font_size', '13px' );
}

/**
 * Check if an attachment id exists.
 */
function vcex_validate_attachment( $attachment = '' ) {
	if ( 'attachment' == get_post_type( $attachment ) ) {
		return $attachment;
	}
}

/**
 * Get encoded vc data.
 */
function vcex_vc_value_from_safe( $value, $encode = false ) {
	if ( function_exists( 'vc_value_from_safe' ) ) {
		return vc_value_from_safe( $value );
	}
	$value = preg_match( '/^#E\-8_/', $value ) ? rawurldecode( base64_decode( preg_replace( '/^#E\-8_/', '', $value ) ) ) : $value;
	if ( $encode ) {
		$value = htmlentities( $value, ENT_COMPAT, 'UTF-8' );
	}
	return $value;
}

/**
 * REturns theme post types.
 */
function vcex_theme_post_types() {
	if ( function_exists( 'wpex_theme_post_types' ) ) {
		return wpex_theme_post_types();
	}
	return array();
}

/**
 * Parses multi attribute setting.
 */
function vcex_parse_multi_attribute( $value = '', $default = array() ) {
	$result = $default;
	$params_pairs = explode( '|', $value );
	if ( ! empty( $params_pairs ) ) {
		foreach ( $params_pairs as $pair ) {
			$param = preg_split( '/\:/', $pair );
			if ( ! empty( $param[0] ) && isset( $param[1] ) ) {
				if ( 'http' == $param[1] && isset( $param[2] ) ) {
					$param[1] = rawurlencode( 'http:' . $param[2] ); // fix for incorrect urls that are not encoded
				}
				$result[ $param[0] ] = rawurldecode( $param[1] );
			}
		}
	}
	return $result;
}

/**
 * Parses textarea HTML.
 */
function vcex_parse_textarea_html( $html = '' ) {
	if ( $html && base64_decode( $html, true ) ) {
		return rawurldecode( base64_decode( strip_tags( $html ) ) );
	}
	return $html;
}

/**
 * Parses the font_control / typography param (used for mapper and front-end)
 */
function vcex_parse_typography_param( $value ) {
	$defaults = array(
		'tag'               => '',
		'text_align'        => '',
		'font_size'         => '',
		'line_height'       => '',
		'color'             => '',
		'font_style_italic' => '',
		'font_style_bold'   => '',
		'font_family'       => '',
		'letter_spacing'    => '',
		'font_family'       => '',
	);
	if ( ! function_exists( 'vc_parse_multi_attribute' ) ) {
		return $defaults;
	}
	$values = wp_parse_args( vc_parse_multi_attribute( $value ), $defaults );
	return $values;
}

/**
 * Convert to array.
 *
 * @todo deprecate - no longer in use
 */
function vcex_string_to_array( $value = array() ) {

	// Return null for empty array
	if ( empty( $value ) && is_array( $value ) ) {
		return null;
	}

	// Return if already array
	if ( ! empty( $value ) && is_array( $value ) ) {
		return $value;
	}

	// Clean up value
	$items  = preg_split( '/\,[\s]*/', $value );

	// Create array
	foreach ( $items as $item ) {
		if ( strlen( $item ) > 0 ) {
			$array[] = $item;
		}
	}

	// Return array
	return $array;

}

/**
 * Combines multiple top/right/bottom/left fields.
 */
function vcex_combine_trbl_fields( $top = '', $right = '', $bottom = '', $left = '' ) {

	$margins = array();

	if ( $top ) {
		$margins['top'] = 'top:' . wp_strip_all_tags( $top );
	}

	if ( $right ) {
		$margins['right'] = 'right:' . wp_strip_all_tags( $right );
	}

	if ( $bottom ) {
		$margins['bottom'] = 'bottom:' . wp_strip_all_tags( $bottom );
	}

	if ( $left ) {
		$margins['left'] = 'left:' . wp_strip_all_tags( $left );
	}

	if ( $margins ) {
		return implode( '|', $margins );
	}

}

/**
 * Migrate font_container field to individual params.
 */
function vcex_migrate_font_container_param( $font_container_field = '', $target = '', $atts = array() ) {

	if ( empty( $atts[ $font_container_field ] ) ) {
		return $atts;
	}

	$get_typo = vcex_parse_typography_param( $atts[ $font_container_field ] );

	if ( empty( $get_typo ) ) {
		return $atts;
	}

	$params_to_migrate = array(
		'font_size',
		'text_align',
		'line_height',
		'color',
		'font_family',
		'tag',
	);

	foreach( $params_to_migrate as $param ) {

		if ( empty( $get_typo[ $param ] ) ) {
			continue;
		}

		$value = $get_typo[ $param ];

		if ( 'text_align' === $param && ( 'left' === $value || 'justify' === $value ) ) {
			continue; // left text align was never & justify isn't available in the theme so don't migrate
		}

		if ( empty( $atts[ $target . '_' . $param ] ) ) {
			$atts[ $target . '_' . $param ] = $value;
		}

	}

	return $atts;
}