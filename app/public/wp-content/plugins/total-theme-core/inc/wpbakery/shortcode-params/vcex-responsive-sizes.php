<?php
/**
 * Responsive Sizes VC param
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

function vcex_responsive_sizes_shortcode_param( $settings, $value ) {

	if ( $value && strpos( $value, ':' ) === false ) {
		$ogvalue = $value;
		$value = 'd:'. $value;
	}

	$medias = array(
		'd'  => array(
			'label' => esc_html__( 'Desktop', 'total-theme-core' ),
			'icon'  => 'dashicons dashicons-desktop',
		),
		'tl' => array(
			'label' => esc_html__( 'Tablet Landscape', 'total-theme-core' ),
			'icon'  => 'dashicons dashicons-tablet',
		),
		'tp' => array(
			'label' => esc_html__( 'Tablet Portrait', 'total-theme-core' ),
			'icon'  => 'dashicons dashicons-tablet',
		),
		'pl' => array(
			'label' => esc_html__( 'Phone Landscape', 'total-theme-core' ),
			'icon'  => 'dashicons dashicons-smartphone',
		),
		'pp' => array(
			'label' => esc_html__( 'Phone Portrait', 'total-theme-core' ),
			'icon'  => 'dashicons dashicons-smartphone',
		),
	);

	$defaults = array();

	foreach ( $medias as $key => $val ) {
		$defaults[$key] = '';
	}

	$field_values = vcex_parse_multi_attribute( $value, $defaults );

	// Begin output
	$output = '<div class="vcex-rs-param vc_clearfix">';

	$count = 0;

	foreach ( $medias as $key => $val ) {

		$count++;

		$classes = 'vcex-item vcex-item-' . $count;

		if ( $count > 1 && ! $field_values['d'] ) {
			$classes .= ' vcex-hidden';
		}

		$output .= '<div class="' . $classes . '">';

			$icon_classes = 'vcex-icon';

			if ( 'pl' == $key || 'tl' == $key ) {
				$icon_classes .= ' vcex-flip';
			}

			$output .= '<span class="'. esc_attr( $icon_classes ) .'"><span class="'. esc_attr( $val['icon'] ) .'"></span></span>';

			$output .= '<input class="vcex-input" name="' . esc_attr( $key ) . '" value="'. esc_attr( $field_values[$key] ) .'" type="text" placeholder="-">';

		$output .= '</div>';

	}

	if ( ! empty( $ogvalue ) ) {
		$value = $ogvalue;
	}

	// Add hidden field
	$output .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value  ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '">';

    // Close wrapper
	$output .= '</div>';

	// Return output
	return $output;

}
vc_add_shortcode_param(
	'vcex_responsive_sizes',
	'vcex_responsive_sizes_shortcode_param',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);