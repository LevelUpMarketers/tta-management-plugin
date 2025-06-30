<?php
/**
 * Image Crop Locations VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_image_crop_locations_shortcode_param( $settings, $value ) {

	if ( function_exists( 'wpex_image_crop_locations' ) ) {

		$output = '<select name="'
			. esc_attr( $settings['param_name'] )
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. esc_attr( $settings['param_name'] )
			. ' ' . esc_attr( $settings['type'] ) .'">';

		$options = wpex_image_crop_locations();

		foreach ( $options as $key => $name ) {

			$output .= '<option value="' . esc_attr( $key )  . '" ' . selected( $value, $key, false ) . '>' . esc_attr( $name ) . '</option>';

		}

		$output .= '</select>';

	} else {
		$output = '<input type="text" class="wpb_vc_param_value '
				. esc_attr( $settings['param_name'] ) . ' '
				. esc_attr( $settings['type'] ) . '" name="' . esc_attr( $settings['param_name'] ) . '" value="' . esc_attr( $value ) . '"/>';
	}

	return $output;

}

vc_add_shortcode_param(
	'vcex_image_crop_locations',
	'vcex_image_crop_locations_shortcode_param'
);