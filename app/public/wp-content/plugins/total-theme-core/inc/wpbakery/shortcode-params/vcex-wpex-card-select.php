<?php
/**
 * Creates card style select dropdown for WPBakery.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_wpex_card_select_param( $settings, $value, $tag, $single = false ) {

	if ( function_exists( 'wpex_card_select' ) ) {

		return wpex_card_select( array(
			'echo'     => 0,
			'name'     => $settings['param_name'],
			'selected' => $value,
			'class'    => 'wpb_vc_param_value wpb-input vcex-chosen wpb-select ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ), // add vcex-chosen for chosen select
		) );

	}

}

vc_add_shortcode_param( 'vcex_wpex_card_select', 'vcex_wpex_card_select_param' );