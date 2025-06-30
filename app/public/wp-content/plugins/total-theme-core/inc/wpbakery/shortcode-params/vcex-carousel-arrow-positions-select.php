<?php
/**
 * Carousel Arrow Positions VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_carousel_arrow_positions_shortcode_param( $settings, $value ) {

	if ( function_exists( 'wpex_carousel_arrow_positions' ) ) {

		$output = '<select name="'
			. esc_attr( $settings['param_name'] )
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. esc_attr( $settings['param_name'] )
			. ' ' . esc_attr( $settings['type'] ) .'">';

		$options = wpex_carousel_arrow_positions();

		foreach ( $options as $key => $name ) {

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
	'vcex_carousel_arrow_positions',
	'vcex_carousel_arrow_positions_shortcode_param'
);