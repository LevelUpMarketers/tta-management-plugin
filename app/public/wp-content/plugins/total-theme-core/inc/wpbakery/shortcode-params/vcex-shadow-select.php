<?php
/**
 * Dropshadow select param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_shadow_select_shortcode_param( $settings, $value ) {

	$output = '<select name="'
		. esc_attr( $settings['param_name'] )
		. '" class="wpb_vc_param_value wpb-input wpb-select '
		. esc_attr( $settings['param_name'] )
		. ' ' . esc_attr( $settings['type'] ) .'">';

		$shadows = apply_filters( 'wpex_shadow_styles', array(
			'' => '- ' . esc_html( 'None', 'total-theme-core' ) . ' -',
			'wpex-shadow-xs'  => 'xs',
			'wpex-shadow-md'  => 'md',
			'wpex-shadow-lg'  => 'lg',
			'wpex-shadow-xl'  => 'xl',
			'wpex-shadow-2xl' => '2xl',
		) );

		foreach ( $shadows as $key => $name ) {

			$output .= '<option value="' . esc_attr( $key )  . '" ' . selected( $value, $key, false ) . '>' . esc_attr( $name ) . '</option>';

		}

		$output .= '</select>';

	return $output;

}
vc_add_shortcode_param( 'vcex_shadow_select', 'vcex_shadow_select_shortcode_param' );