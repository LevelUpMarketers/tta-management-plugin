<?php
/**
 * Font Icon functions.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns correct icon family for specific icon class
 */
function vcex_get_icon_type_from_class( $icon ) {
	if ( strpos( $icon, 'ticon' ) !== false || strpos( $icon, 'fa fa-' ) !== false ) {
		return 'ticons';
	} elseif ( strpos( $icon, 'fa-' ) !== false ) {
		return 'fontawesome';
	} elseif ( strpos( $icon, 'ticon' ) !== false ) {
		return 'ticons';
	} elseif ( strpos( $icon, 'vc-oi' ) !== false ) {
		return 'openiconic';
	} elseif ( strpos( $icon, 'typcn' ) !== false ) {
		return 'typicons';
	} elseif ( strpos( $icon, 'entypo-icon' ) !== false ) {
		return 'entypo';
	} elseif ( strpos( $icon, 'vc_li' ) !== false ) {
		return 'linecons';
	} elseif ( strpos( $icon, 'vc-material' ) !== false ) {
		return 'material';
	}
}

/**
 * Returns correct icon class based on icon type.
 */
function vcex_get_icon_class( $atts, $icon_location = 'icon' ) {

	$icon = '';
	$icon_type = ! empty( $atts['icon_type'] ) ? $atts['icon_type'] : '';

	// Custom icon set for specific library
	if ( $icon_type && ! empty( $atts[$icon_location . '_' . $icon_type] ) ) {
		$icon = $atts[$icon_location . '_' . $icon_type];
	}

	// Parse the default icon parameter which could be anything really
	elseif ( ! empty( $atts[ $icon_location ] ) ) {

		// Get icon value
		$icon = $atts[$icon_location];

		// Get icon type if not set
		if ( ! $icon_type ) {
			$icon_type = vcex_get_icon_type_from_class( $icon );
		}

		// converts old 4.7 fontawesome icons to ticons
		if ( 'ticons' == $icon_type ) {
			$icon = str_replace( 'fa fa-', 'ticon ticon-', $icon );
		}

		// Icon type is unknown so lets add prefixes
		if ( ! $icon_type ) {
			$icon = vcex_add_default_icon_prefix( $icon );
		}

	}

	// Extra checks
	if ( ! $icon || in_array( $icon, array( 'icon', 'none' ) ) ) {
		return '';
	}

	// Return icon class
	return $icon;

}

/**
 * Adds default icon prefix to a non-prefixed icon.
 */
function vcex_add_default_icon_prefix( $icon ) {
	return 'ticon ticon-' . sanitize_html_class( $icon );
}