<?php
/**
 * Contact Form 7 Form Select
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_cf7_select_shortcode_param( $settings, $value ) {

	$cf7 = get_posts( 'post_type="wpcf7_contact_form"&numberposts=-1' );

	if ( $cf7 ) {

		$output = '<select name="'
				. $settings['param_name']
				. '" class="wpb_vc_param_value wpb-input wpb-select '
				. $settings['param_name']
				. ' ' . $settings['type'] .'">';

		$output .= '<option value="" ' . selected( $value, '', false ) . '>&#8212; ' . esc_html__( 'Select', 'total-theme-core' ) . ' &#8212;</option>';

		foreach ( $cf7 as $cform ) {
			$output .= '<option value="' . esc_attr( $cform->ID )  . '" ' . selected( $value, $cform->ID, false ) . '>' . esc_attr( $cform->post_title ) . '</option>';
		}

		$output .= '</select>';

		return $output;

	}

}
vc_add_shortcode_param( 'vcex_cf7_select', 'vcex_cf7_select_shortcode_param' );