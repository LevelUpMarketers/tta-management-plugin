<?php
/**
 * Sanitization functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize data via the TotalTheme\SanitizeData class.
 *
 * @since 2.0.0
 * @todo deprecate
 */
function wpex_sanitize_data( $data = '', $type = '' ) {
	if ( $data && $type ) {
		$class = new TotalTheme\SanitizeData();
		return $class->parse_data( $data, $type );
	}
}

/**
 * Validate Boolean.
 *
 * @since 4.9
 */
function wpex_validate_boolean( $var ) {

	if ( is_bool( $var ) ) {
		return $var;
	}

	if ( is_string( $var ) ) {

		$var = strtolower( $var );

		switch ( $var ) {
			case 'false':
			case 'off':
			case 'disabled':
				return false;
				break;
			case 'true':
			case 'on':
			case 'enabled':
				return true;
				break;

		}

	}

	return (bool) $var;

}

/**
 * Echo escaped post title.
 *
 * @since 2.0.0
 */
function wpex_esc_title( $post = '' ) {
	echo wpex_get_esc_title( $post );
}

/**
 * Return escaped post title.
 *
 * @since 1.5.4
 */
function wpex_get_esc_title( $post = '' ) {
	return the_title_attribute( array(
		'echo' => false,
		'post' => $post,
	) );
}

/**
 * Wrapper for esc_attr with a fallback.
 *
 * @since 3.3.5
 */
function wpex_esc_attr( $val = null, $fallback = null ) {
	if ( $val = esc_attr( $val ) ) {
		return $val;
	}
	return $fallback;
}

/**
 * Wrapper for esc_html with a fallback.
 *
 * @since 3.3.5
 */
function wpex_esc_html( $val = null, $fallback = null ) {
	if ( $val = esc_html( $val ) ) {
		return $val;
	}
	return $fallback;
}

/**
 * Wrapper function for intval with a fallback.
 *
 * @since 3.3.5
 */
function wpex_intval( $val = null, $fallback = null ) {
	if ( 0 == $val ) {
		return 0; // Some settings may need this
	} elseif ( $val = intval( $val ) ) {
		return $val;
	} else {
		return $fallback;
	}
}

/**
 * Sanitize checkbox.
 *
 * @since 4.3
 */
function wpex_sanitize_checkbox( $checked ) {
	return ! empty( $checked ) ? true : false;
}

/**
 * Sanitize font-family.
 *
 * @since 4.4
 */
function wpex_sanitize_font_family( $font_family ) {
	if ( 'system-ui' == $font_family ) {
		$font_family = wpex_get_system_ui_font_stack();
	}
	$font_family = str_replace( "``", "'", $font_family ); // Fixes issue with fonts saved in shortcodes
	$font_family = wpex_get_font_family_stack( $font_family );
	return wp_specialchars_decode( $font_family );
}

/**
 * Sanitize visibility.
 *
 * @since 5.0
 */
function wpex_sanitize_visibility( $input ) {
	if ( empty( $input ) ) {
		return '';
	}
	$input = str_replace( '-portrait', '', $input );
	$input = str_replace( '-landscape', '', $input );
	return sanitize_html_class( $input );
}

/**
 * Sanitize visibility for the customizer
 *
 * @since 5.0
 */
function wpex_customizer_sanitize_visibility( $input, $setting = '' ) {
	if ( $input ) {
		$input = str_replace( '-portrait', '', $input );
		$input = str_replace( '-landscape', '', $input );
	}
	if ( 'always-visible' == $input ) {
		return '';
	}
	if ( array_key_exists( $input, wpex_visibility() ) ) {
		return $input;
	}
	if ( ! empty( $setting->default ) ) {
		return $setting->default;
	}
}

/**
 * Sanitize font-size customizer value.
 *
 * @since 4.9.5
 */
function wpex_sanitize_font_size_mod( $input ) {
	// Sanitize array values
	if ( is_array( $input ) ) {
		return array_map( 'wpex_sanitize_font_size', $input );
	}
	// Convert jSON to array and sanitize each value while doing so
	if ( strpos( $input, '{' ) !== false ) {
		$input = json_decode( $input, true );
		return array_map( 'wpex_sanitize_font_size', $input );
	}
	return wpex_sanitize_font_size( $input ); // Most likely a string.
}

/**
 * Sanitize font-size.
 *
 * @since 4.3
 */
function wpex_sanitize_font_size( $input ) {
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
 * Sanitize letter spacing.
 *
 * @since 4.3
 */
function wpex_sanitize_letter_spacing( $input ) {
	if ( '' == $input ) {
		return '';
	}
	if ( strpos( $input, 'px' ) || strpos( $input, 'em' ) || strpos( $input, 'vmin' ) || strpos( $input, 'vmax' ) ) {
		$input = esc_attr( $input );
	} else {
		$input = absint( $input );
		if ( $input ) {
			$input =  $input . 'px';
		}
	}
	return $input;
}

/**
 * Sanitize line-height.
 *
 * @since 4.3
 */
function wpex_sanitize_line_height( $input ) {
	return esc_html( $input );
}

/**
 * Sanitize image.
 *
 * @since 5.0
 */
function wpex_sanitize_image( $input ) {
	return wp_kses( $input, array(
		'img' => array(
			'src'    => array(),
			'alt'    => array(),
			'srcset' => array(),
			'id'     => array(),
			'class'  => array(),
			'height' => array(),
			'width'  => array(),
			'data'   => array(),
		),
	) );
}

/**
 * Sanitize customizer select.
 *
 * @since 4.3
 */
function wpex_sanitize_customizer_select( $input, $setting ) {
	$input   = sanitize_key( $input );
	$choices = $setting->manager->get_control( $setting->id )->choices;
	if ( array_key_exists( $input, $choices ) ) {
		return sanitize_text_field( $input );
	}
	if ( ! empty( $setting->default ) ) {
		return $setting->default;
	}
}

/**
 * Sanitize customizer columns control.
 *
 * @since 4.9.9
 */
function wpex_sanitize_customizer_columns( $input, $setting ) {
	if ( is_numeric( $input ) ) {
		return absint( $input );
	}
	$input = (array) $input;
	return array_map( 'absint', $input );
}

/**
 * Sanitize Template Content.
 *
 * @since 5.0
 */
function wpex_sanitize_template_content( $template_content = null ) {
	$template_content = wpex_parse_vc_content( $template_content ); // remove weird p tags and extra code
	$template_content = wp_kses_post( $template_content ); // security sanitization
	$template_content = do_shortcode( $template_content ); // parse shortcodes
	return $template_content;
}

/**
 * Parse visual composer content to remove extra p tags.
 *
 * @since 4.9
 */
function wpex_parse_vc_content( $shortcode ) {
	$shortcode = str_replace( '<p>[vc_row', '[vc_row', $shortcode );
	$shortcode = str_replace( '[/vc_row]</p>', '[/vc_row]', $shortcode );
	return $shortcode;
}

/**
 * Clean up shortcodes.
 *
 * @since 4.9.6
 */
function wpex_clean_up_shortcodes( $content = '' ) {
	if ( $content ) {
		return strtr( $content, array (
			'<p>['    => '[',
			']</p>'   => ']',
			']<br />' => ']'
		) );
	}
}