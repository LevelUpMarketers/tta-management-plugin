<?php
/**
 * Button Colors Select Param
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

function vcex_sorter_param( $settings, $value ) {

	$choices = ! empty( $settings['choices'] ) ? $settings['choices'] : '';

	if ( ! $choices ) {
		return;
	}

	$enabled_blocks = $value ? explode( ',', $value ) : array();

	$output .= '<ul class="vcex-sorter-param">';

		// Display all enabled blocks at the top.
		foreach( $enabled_blocks as $block ) {

			if ( ! isset( $choices[$block] ) ) {
				continue;
			}

			$output .= '<li data-value="' . esc_attr( $block ) . '">';

			$output .= esc_html( $choices[$block] );

				$output .= '<a href="#" aria-role="button">';

					$output .= '<span class="ticon ticon-toggle-on"></span>';

					$output .= '<span class="screen-reader-text">' . esc_html__( 'Toggle on/off', 'total-theme-core' ) . '</span>';

				$output .= '</a>';

			$output .= '</li>';

			unset( $choices[$block] );

		}

		// Display disabled blocks below.
		if ( ! empty( $choices ) ) {

			foreach ( $choices as $c_val => $c_label ) {

				$output .= '<li class="vcex-disabled" data-value="' . esc_attr( $c_val ) . '">';

					$output .= esc_html( $c_label );

					$output .= '<a href="#" aria-role="button">';

						$output .= '<span class="ticon ticon-toggle-on ticon-rotate-180"></span>';

						$output .= '<span class="screen-reader-text">' . esc_html__( 'Toggle on/off', 'total-theme-core' ) . '</span>';

					$output .= '</a>';

				$output .= '</li>';
			}

		}

	$output .= '</ul>';

	$output .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value  ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '">';

	return $output;

}
vc_add_shortcode_param(
	'vcex_sorter',
	'vcex_sorter_param',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);