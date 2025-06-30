<?php
/**
 * Button Styles VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_button_styles_shortcode_param( $settings, $value ) {

	if ( function_exists( 'wpex_button_styles' ) ) {

		$output = '<select name="'
			. esc_attr( $settings['param_name'] )
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. esc_attr( $settings['param_name'] )
			. ' ' . esc_attr( $settings['type'] ) .'">';

		$excluded = isset( $settings['exclude_choices'] ) ? $settings['exclude_choices'] : array();

		$options = wpex_button_styles();

		foreach ( $options as $key => $name ) {

			if ( in_array( $key, $excluded ) ) {
				continue;
			}

			$output .= '<option value="'. esc_attr( $key )  .'" '. selected( $value, $key, false ) .'>'. esc_attr( $name ) .'</option>';

		}

		$output .= '</select>';

	} else {
		$output = vcex_total_exclusive_notice();
		$output .= '<input type="hidden" class="wpb_vc_param_value '
				. esc_attr( $settings['param_name'] ) . ' '
				. esc_attr( $settings['type'] ) . '" name="' . esc_attr( $settings['param_name'] ) . '" value="' . esc_attr( $value ) . '"/>';
	}

	return $output;

}

vc_add_shortcode_param(
	'vcex_button_styles',
	'vcex_button_styles_shortcode_param'
);