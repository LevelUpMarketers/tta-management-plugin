<?php
/**
 * Text Transforms VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_text_alignments_shortcode_param( $settings, $value ) {

	$options = array(
		'default' => esc_html( 'Default', 'total-theme-core' ),
		'left'    => esc_html( 'Left', 'total-theme-core' ),
		'center'  => esc_html( 'Center', 'total-theme-core' ),
		'right'   => esc_html( 'Right', 'total-theme-core' ),
	);

	$output = '<div class="vcex-alignments-param vcex-noselect wpex-clr">';

	$excluded = isset( $settings[ 'exclude_choices' ] ) ? $settings[ 'exclude_choices' ] : array();

	foreach ( $options as $option => $label ) {

		if ( in_array( $option, $excluded ) ) {
			continue;
		}

		$option = ( 'default' == $option ) ? '' : $option; // Set default option value to empty

		if ( $option ) {

			$active = $value === $option ? ' vcex-active' : '';

			if ( defined( 'TOTAL_THEME_ACTIVE' ) && TOTAL_THEME_ACTIVE ) {
				$icon_class = 'ticon ticon-align-' . esc_attr( $option );
			} else {
				$icon_class = 'fa fa-align-' . esc_attr( $option );
			}

			$output .= '<button class="vcex-alignment-opt' . $active . '" data-value="' . esc_attr( $option )  . '" arial-label="' . $label . '"><span class="' . esc_attr( $icon_class ) . '"></span></button>';

		} else {

			$active = ! $value ? ' vcex-active' : '';

			$output .= '<button class="vcex-alignment-opt vcex-default' . $active . '" data-value="' . esc_attr( $option )  . '">' . esc_html( 'Default', 'total-theme-core' ) . '</button>';

		}

	}

	$output .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="vcex-hidden-input wpb-input wpb_vc_param_value  ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '" />';

	$output .= '</div>';

	return $output;

}

vc_add_shortcode_param(
	'vcex_text_alignments',
	'vcex_text_alignments_shortcode_param'
);