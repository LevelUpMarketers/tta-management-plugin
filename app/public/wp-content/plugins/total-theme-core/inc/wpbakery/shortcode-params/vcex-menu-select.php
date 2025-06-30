<?php
/**
 * Menu Select VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_menu_select_shortcode_param( $settings, $value ) {

	$output = '<select name="'
			. esc_attr( $settings['param_name'] )
			. '" class="wpb_vc_param_value wpb-input wpb-select '
			. esc_attr( $settings['param_name'] )
			. ' ' . esc_attr( $settings['type'] ) . '">';

	$output .= '<option value="" ' . selected( $value, '', false ) . '>' . esc_html( 'Select', 'total-theme-core' ) . '</option>';

	$menus_array = array();
	$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
	if ( $menus && is_array( $menus ) ) {
		foreach ( $menus as $menu ) {
			$output .= '<option value="' . esc_attr( $menu->term_id ) . '" ' . selected( $value, $menu->term_id, false ) . '>' . esc_attr( $menu->name ) . '</option>';
		}
	}

	$output .= '</select>';

	return $output;

}

vc_add_shortcode_param(
	'vcex_menus_select',
	'vcex_menu_select_shortcode_param'
);