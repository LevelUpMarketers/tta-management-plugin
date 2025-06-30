<?php
/**
 * Font Weight VC param
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

function vcex_ofswitch_shortcode_param( $settings, $value ) {

	if ( isset( $value ) && $value == '' && ! empty( $settings['std'] ) ) {
		$value = $settings['std']; // fix for empty values
	}

	$on  = 'true';
	$off = 'false';

	if ( isset( $settings[ 'vcex' ][ 'on' ] ) ) {
		$on = $settings[ 'vcex' ][ 'on' ];
		if ( 'true' == $value ) {
			$value = $on; // fixes issues when a value is set to true instead of the custom on value
		}
	}

	if ( isset( $settings[ 'vcex' ][ 'off' ] ) ) {
		$off = $settings[ 'vcex' ][ 'off' ];
		if ( 'false' == $value ) {
			$value = $off; // fixes issues when a value is set to false instead of the custom on value
		}
	}

	$output = '<div class="vcex-ofswitch vcex-noselect">';

		$active = $value == $on ? ' vcex-active' : '';

		$output .= '<button class="vcex-btn vcex-on' . $active . '" data-value="' . esc_attr( $on ) . '">' . esc_html__( 'on', 'total-theme-core' ) . '</button>';

		$active = $value == $off ? ' vcex-active' : '';

		$output .= '<button class="vcex-btn vcex-off' . $active . '" data-value="' . esc_attr( $off ) . '">' . esc_html__( 'off', 'total-theme-core' ) . '</button>';

		$output .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="vcex-hidden-input wpb-input wpb_vc_param_value  ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '" />';

	$output .= '</div>';

	return $output;

}

vc_add_shortcode_param(
	'vcex_ofswitch',
	'vcex_ofswitch_shortcode_param',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);