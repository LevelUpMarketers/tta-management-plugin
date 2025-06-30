<?php
/**
 * Hover CSS animations VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_hover_animations_shortcode_param( $settings, $value ) {

	if ( function_exists( 'wpex_hover_css_animations' ) ) {

		$output = '<select name="'
			. esc_attr( $settings['param_name'] )
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. esc_attr( $settings['param_name'] )
			. ' ' . esc_attr( $settings['type'] ) .'">';


		$options = wpex_hover_css_animations();

		foreach ( $options as $key => $name ) {

			$output .= '<option value="' . esc_attr( $key )  . '" ' . selected( $value, $key, false ) . '>' . esc_attr( $name ) . '</option>';

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
	'vcex_hover_animations',
	'vcex_hover_animations_shortcode_param'
);