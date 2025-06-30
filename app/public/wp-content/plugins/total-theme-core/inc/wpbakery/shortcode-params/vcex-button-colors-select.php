<?php
/**
 * Button Colors Select Param
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

function vcex_button_colors_shortcode_param( $settings, $value ) {

	if ( function_exists( 'wpex_get_accent_colors' ) ) {

		$output = '<select name="'
			. $settings['param_name']
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. $settings['param_name']
			. ' ' . $settings['type'] .'">';

		$colors = ( array ) wpex_get_accent_colors();

		foreach ( $colors as $key => $settings ) {

			$key    = $key == 'default' ? '' : $key;
			$hex    = isset( $settings[ 'hex' ] ) ?  $settings[ 'hex' ] : '#fff';
			$label  = isset( $settings[ 'label' ] ) ?  $settings[ 'label' ] : '';

			$output .= '<option';

				$output .= ' value="' . esc_attr( $key )  . '"';

				$output .= ' ' . selected( $value, $key, false );

				/*if ( $hex ) {
					$output .= ' style="background-color:' . wp_strip_all_tags( $hex ) . ';"';
				}*/

			$output .= '>';

				$output .= esc_attr( $label );

			$output .= '</option>';

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
	'vcex_button_colors',
	'vcex_button_colors_shortcode_param',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);