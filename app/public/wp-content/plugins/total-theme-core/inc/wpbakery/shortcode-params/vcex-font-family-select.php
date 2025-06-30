<?php
/**
 * Adds a new custom font family select parameter to the VC
 *
 * @package Total Theme Core
 * @subpackage WPBakery
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'vcex_font_family_select_shortcode_param' ) ) {

	function vcex_font_family_select_shortcode_param( $settings, $value ) {

		if ( defined( 'TOTAL_THEME_ACTIVE' ) ) {

			$output = '<select name="'
					. $settings['param_name']
					. '" class="wpb_vc_param_value wpb-input wpb-select vcex-chosen '
					. $settings['param_name']
					. ' ' . $settings['type'] .'">'
					. '<option value="" '. selected( $value, '', false ) .'>'. esc_html__( 'Default', 'total-theme-core' ) .'</option>';

			$value_exists = false;

			// User fonts
			if ( function_exists( 'wpex_get_registered_fonts' ) ) {
				$user_fonts = wpex_get_registered_fonts();
				if ( ! empty( $user_fonts ) ) {
					$output .= '<optgroup label="' . esc_html__( 'My Fonts', 'total-theme-core' ) . '">';
					foreach ( $user_fonts as $font_name => $font_settings ) {
						if ( $font_name === $value ) {
							$value_exists = true;
						}
						$output .= '<option value="' . esc_html( $font_name ) . '" ' . selected( $font_name, $value, false ) . '>' . esc_html( $font_name ) . '</option>';
					}
					$output .= '</optgroup>';
				}
			}

			// Custom fonts
			if ( function_exists( 'wpex_add_custom_fonts' ) ) {
				$custom_fonts = wpex_add_custom_fonts();
				if ( $custom_fonts && is_array( $custom_fonts ) ) {
					$output .= '<optgroup label="' . esc_html__( 'Custom Fonts', 'total-theme-core' ) . '">';
					foreach ( $custom_fonts as $font ) {
						if ( $font === $value ) {
							$value_exists = true;
						}
						$output .= '<option value="' . esc_html( $font ) . '" ' . selected( $font, $value, false ) .'>' . esc_html( $font ) . '</option>';
					}
					$output .= '</optgroup>';
				}
			}

			if ( ! function_exists( 'wpex_has_registered_fonts' ) || ! wpex_has_registered_fonts() ) {

				// Standard fonts
				if ( function_exists( 'wpex_standard_fonts' ) ) {
					$std_fonts = wpex_standard_fonts();
					if ( $std_fonts && is_array( $std_fonts ) ) {
						$output .= '<optgroup label="' . esc_html__( 'Standard Fonts', 'total-theme-core' ) . '">';
							foreach ( $std_fonts as $font ) {
								if ( $font === $value ) {
									$value_exists = true;
								}
								$output .= '<option value="' . esc_html( $font ) . '" ' . selected( $font, $value, false ) . '>' . esc_html( $font ) .'</option>';
							}
						$output .= '</optgroup>';
					}
				}

				// Google fonts
				if ( $google_fonts = wpex_google_fonts_array() ) {
					$output .= '<optgroup label="'. esc_html__( 'Google Fonts', 'total-theme-core' ) .'">';
						foreach ( $google_fonts as $font ) {
							if ( $font === $value ) {
								$value_exists = true;
							}
							$output .= '<option value="' . esc_html( $font ) . '" ' . selected( $font, $value, false ) . '>' . esc_html( $font ) .'</option>';
						}
					$output .= '</optgroup>';
				}

			}

			if ( ! empty( $value ) && false === $value_exists ) {
				$output .= '<optgroup label="' . esc_html__( 'Non Registered Fonts', 'total-theme-core' ) . '">';
					$output .= '<option value="' . esc_html( $value ) . '" selected="selected">' . esc_html( $value ) .'</option>';
				$output .= '</optgroup>';
			}

			$output .= '</select>';

			//$output .= '<div class="vc_description vc_clearfix"></div>';

		} else {
			$output = vcex_total_exclusive_notice();
			$output .= '<input type="hidden" class="wpb_vc_param_value '
				. esc_attr( $settings['param_name'] ) . ' '
				. esc_attr( $settings['type'] ) . '" name="' . esc_attr( $settings['param_name'] ) . '" value="' . esc_attr( $value ) . '"/>';
		}

		return $output;

	}

}

vc_add_shortcode_param(
	'vcex_font_family_select',
	'vcex_font_family_select_shortcode_param',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);