<?php
/**
 * Orderby VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_orderby_shortcode_param( $settings, $value ) {

	$output = '<select name="'
			. esc_attr( $settings['param_name'] )
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. esc_attr( $settings['param_name'] )
			. ' ' . esc_attr( $settings['type'] ) .'">';

	$post_type = isset( $settings['post_type'] ) ? $settings['post_type'] : 'post';

	$options = vcex_orderby_array( $post_type );

	foreach ( $options as $name => $key ) {

		$output .= '<option value="' . esc_attr( $key )  . '" ' . selected( $value, $key, false ) . '>' . esc_attr( $name ) . '</option>';

	}

	$output .= '</select>';

	return $output;

}

vc_add_shortcode_param(
	'vcex_orderby',
	'vcex_orderby_shortcode_param'
);