<?php
/**
 * Parser functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cleans up an array, comma- or space-separated list of scalar values.
 *
 * @since 5.0
 */
function wpex_parse_list( $list ) {

	if ( function_exists( 'wp_parse_list' ) ) {
		return wp_parse_list( $list ); // added in WP 5.1
	}

    if ( ! is_array( $list ) ) {
        return preg_split( '/[\s,]+/', $list, -1, PREG_SPLIT_NO_EMPTY );
    }

    return $list;
}

/**
 * Parse CSS.
 */
function wpex_parse_css( $value = '', $property = '', $selector = '', $unit = '', $important = false ) {

	if ( ! $value || ! $selector || ! $property || ! $selector ) {
		return;
	}

	$value_escaped = wp_strip_all_tags( $value );

	if ( ! empty( $unit ) ) {
		$value_escaped .= $unit;
	}

	if ( $important ) {
		$value_escaped .= '!important';
	}

	return $selector . '{' . wp_strip_all_tags( $property ) . ':' . $value_escaped . ';}';

}

/**
 * Takes an array of attributes and outputs them for HTML.
 *
 * @since 3.4.0
 */
function wpex_parse_html( $tag = '', $attrs = array(), $content = '' ) {

	$attrs       = wpex_parse_attrs( $attrs );
	$tag_escaped = tag_escape( $tag );

	$output = '<' . $tag_escaped . ' ' . $attrs . '>';

	if ( $content ) {
		$output .= $content;
	}

	$output .= '</' . $tag_escaped . '>';

	return $output;
}

/**
 * Parses an html data attribute.
 *
 * @since 3.4.0
 * @todo add extra sanitization
 */
function wpex_parse_attrs( $attrs = null ) {

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

		// Attributes used for other things, we can skip these
		if ( 'content' == $key ) {
			continue;
		}

		// If the attribute is an array convert to string
		if ( is_array( $val ) ) {
			$val = array_map( 'trim', $val );
			$val = implode( ' ', $val );
		}

		// Sanitize rel attribute
		if ( 'rel' == $key ) {
			$val = wp_strip_all_tags( $val );
		}

		// Sanitize ID
		elseif ( 'id' == $key ) {
			$val = trim ( str_replace( '#', '', $val ) );
			$val = str_replace( ' ', '', $val );
		}

		// Sanitize targets
		elseif ( 'target' == $key ) {

			if ( ! in_array( $val, array( '_blank', 'blank', '_self', '_parent', '_top' ) ) ) {
				$val = '';
			} elseif ( 'blank' == $val ) {
				$val = '_blank';
			}

		}

		// Add attribute to output
		if ( $val ) {

			// Attributes that don't have values
			if ( in_array( $key, array( 'download' ) ) ) {
				$output .= ' ' . trim( $val ); // Used for example on total button download attribute
			}

			// Add attribute | value
			else {
				$needle = ( 'data' == $key ) ? 'data-' : $key . '=';
				if ( strpos( $val, $needle ) !== false ) {
					$output .= ' ' . trim( $val ); // Already has tag added
				} else {
					if ( 'data-wpex-hover' == $key ) {
						$output .= " " . $key . "='" . $val . "'";
					} else {
						$output .= ' ' . $key . '="' . $val . '"';
					}
				}
			}

		}

		// Items with empty vals
		else {

			// Empty alts are allowed
			if ( 'alt' == $key ) {
				$output .= ' alt=""';
			}

			// Empty data attributes
			elseif ( strpos( $key, 'data-' ) !== false ) {
				$output .= ' ' . $key;
			}

		}

	}

	// Return output
	return trim( $output ); // leave space in front-end

}

/**
 * Returns link target attribute.
 *
 * @since 4.9
 * @todo @todo deprecate and use parse_attrs instead?
 */
function wpex_parse_link_target( $target = true, $add_rel = true ) {
	$output = '';
	if ( 'blank' == $target || '_blank' == $target ) {
		$output = ' target="_blank"';
		if ( $add_rel ) {
			$output .= ' rel="noopener noreferrer"';
		}
	}
	return $output;
}