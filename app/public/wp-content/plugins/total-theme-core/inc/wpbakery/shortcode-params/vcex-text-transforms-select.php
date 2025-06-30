<?php
/**
 * Text Transforms VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_text_transforms_shortcode_param( $settings, $value ) {

	if ( function_exists( 'wpex_text_transforms' ) ) {

		$output = '<select name="'
			. $settings['param_name']
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. $settings['param_name']
			. ' ' . $settings['type'] .'">';

		$options = wpex_text_transforms();

		foreach ( $options as $key => $name ) {

			$output .= '<option value="' . esc_attr( $key )  . '" ' . selected( $value, $key, false ) . '>' . esc_attr( $name ) . '</option>';

		}

		$output .= '</select>';

	} else {
		$output .= '<input type="text" class="wpb_vc_param_value '
			. esc_attr( $settings['param_name'] ) . ' '
			. esc_attr( $settings['type'] ) . '" name="' . esc_attr( $settings['param_name'] ) . '" value="' . esc_attr( $value ) . '"/>';
	}

	return $output;

}
vc_add_shortcode_param( 'vcex_text_transforms', 'vcex_text_transforms_shortcode_param' );