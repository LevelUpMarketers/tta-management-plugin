<?php
/**
 * Grid Columns Resonsive VC param
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

function vcex_grid_columns_responsive_shortcode_param( $settings, $value ) {

	if ( function_exists( 'wpex_grid_columns' ) ) {

		$medias = array(
			'tl' => esc_html__( 'Tablet Landscape', 'total-theme-core' ),
			'tp' => esc_html__( 'Tablet Portrait', 'total-theme-core' ),
			'pl' => esc_html__( 'Phone Landscape', 'total-theme-core' ),
			'pp' => esc_html__( 'Phone Portrait', 'total-theme-core' ),
		);

		$defaults = array();

		foreach ( $medias as $key => $val ) {
			$defaults[$key] = '';
		}

		$field_values = vcex_parse_multi_attribute( $value, $defaults );

		$output = '<div class="vcex-responsive-columns-param"><div class="wpex-row wpex-clr">';

			$options = wpex_grid_columns();

			foreach ( $medias as $id => $name ) {

				$field_value = $field_values[$id];

				$output .= '<div class="vc_col-sm-6">';

					$output .= '<div class="wpb_element_label">' . esc_attr( $name ) . '</div>';

					$output .= '<select name="' . esc_attr( $id ) . '" class="vcex-responsive-column-select">';

						$output .= '<option value="" '. selected( '', $key, false ) .'>'. esc_attr__( 'Default', 'total-theme-core' ) .'</option>';

						foreach ( $options as $key => $name ) {

							$output .= '<option value="'. esc_attr( $key )  .'" '. selected( $field_value, $key, false ) .'>'. esc_attr( $name ) .'</option>';

						}

					$output .= '</select>';

				$output .= '</div>';

			}

		// Add hidden field
		$output .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value  ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '" />';

   		// Close wrapper
		$output .= '</div></div>';

	} else {
		$output = vcex_total_exclusive_notice();
		$output .= '<input type="hidden" class="wpb_vc_param_value '
				. esc_attr( $settings['param_name'] ) . ' '
				. esc_attr( $settings['type'] ) . '" name="' . esc_attr( $settings['param_name'] ) . '" value="' . esc_attr( $value ) . '"/>';
	}

	return $output;

}
vc_add_shortcode_param(
	'vcex_grid_columns_responsive',
	'vcex_grid_columns_responsive_shortcode_param',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);