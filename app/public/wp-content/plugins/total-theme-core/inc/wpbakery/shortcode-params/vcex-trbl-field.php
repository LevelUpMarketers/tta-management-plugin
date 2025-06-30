<?php
/**
 * Margin/Padding VC param
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

function vcex_trbl_shortcode_param( $settings, $value ) {

	$defaults = array(
		'top'    => '',
		'right'  => '',
		'bottom' => '',
		'left'   => '',
	);

	// Convert none multi_attribute to multi_attribute
	if ( false === strpos( $value, ':' ) ) {

		$array = explode( ' ', $value );
		$count = count( $array );

		if ( $array ) {

			if ( 1 == $count ) {
				$field_values = array(
					'top'    => $array[0],
					'right'  => $array[0],
					'bottom' => $array[0],
					'left'   => $array[0],
				);
			} elseif ( 2 == $count ) {
				$field_values = array(
					'top'    => isset( $array[0] ) ? $array[0] : '',
					'right'  => isset( $array[1] ) ? $array[1] : '',
					'bottom' => isset( $array[0] ) ? $array[0] : '',
					'left'   => isset( $array[1] ) ? $array[1] : '',
				);
			} else {
				$field_values = array(
					'top'    => isset( $array[0] ) ? $array[0] : '',
					'right'  => isset( $array[1] ) ? $array[1] : '',
					'bottom' => isset( $array[2] ) ? $array[2] : '',
					'left'   => isset( $array[3] ) ? $array[3] : '',
				);
			}

		}

	} else {

		$field_values = vcex_parse_multi_attribute( $value, $defaults );

	}

	$output = '<div class="vcex-trbl-param">';

		foreach( $field_values as $k => $v ) {

			$icon = $k;
			if ( 'top' == $icon ) {
				$icon = 'up';
			} elseif ( 'bottom' == $icon ) {
				$icon = 'down';
			}

			$output .= '<span class="vcex-item"><span class="vcex-icon"><span class="dashicons dashicons-arrow-' . esc_attr( $icon ) . '-alt"></span></span><input class="vcex-input" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '" type="text" placeholder="-"></span>';

		}

		$output .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="vcex-hidden-input wpb-input wpb_vc_param_value  ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '" />';

	$output .= '</div>';

	return $output;

}
vc_add_shortcode_param(
	'vcex_trbl',
	'vcex_trbl_shortcode_param',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);