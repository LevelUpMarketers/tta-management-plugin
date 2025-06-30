<?php
/**
 * vcex_select_buttons WPBakery param
 *
 * @package Total Theme Core
 * @subpackage WPBakery
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

function vcex_select_buttons( $settings, $value ) {

	$wrap_classes = array(
		'vcex-select-buttons-param',
		'vcex-custom-select',
		'vcex-noselect',
		'wpex-clr',
	);

	$choices = $settings['choices'] ? $settings['choices'] : array();

	switch ( $choices ) {
		case 'alert' :
			$choices = array(
				''        => esc_html__( 'Default', 'total-theme-core' ),
				'info'    => esc_html__( 'Info', 'total-theme-core' ),
				'success' => esc_html__( 'Success', 'total-theme-core' ),
				'warning' => esc_html__( 'Warning', 'total-theme-core' ),
				'error'   => esc_html__( 'Error', 'total-theme-core' ),
			);
			break;
		case 'button_size':
			$choices = array(
				''       => esc_html__( 'Default', 'total-theme-core' ),
				'small'  => esc_html__( 'Small', 'total-theme-core' ),
				'medium' => esc_html__( 'Medium', 'total-theme-core' ),
				'large'  => esc_html__( 'Large', 'total-theme-core' ),
			);
			break;
		case 'button_layout':
				$choices = array(
					'inline'   => esc_html__( 'Inline', 'total-theme-core' ),
					'block'    => esc_html__( 'Block', 'total-theme-core' ),
					'expanded' => esc_html__( 'Expanded', 'total-theme-core' ),
				);
				break;
		case 'link_target':
			$choices = array(
				'self'   => esc_html__( 'Same tab', 'total-theme-core' ),
				'_blank' => esc_html__( 'New tab', 'total-theme-core' )
			);
			break;
		case 'html_tag':
			$choices = array(
				''     => esc_html__( 'Default', 'total-theme-core' ),
				'h1'   => 'h1',
				'h2'   => 'h2',
				'h3'   => 'h3',
				'h4'   => 'h4',
				'h5'   => 'h5',
				'div'  => 'div',
				'span' => 'span',
			);
			break;
		case 'masonry_layout_mode':
			$choices = array(
				'masonry' => esc_html__( 'Masonry', 'total-theme-core' ),
				'fitRows' => esc_html__( 'Fit Rows', 'total-theme-core' ),
			);
			break;
		case 'filter_layout_mode':
			$choices = array(
				'masonry' => esc_html__( 'Masonry', 'total-theme-core' ),
				'fitRows' => esc_html__( 'Fit Rows', 'total-theme-core' ),
			);
			break;
		case 'grid_style':
			$choices = array(
				'fit_columns' => esc_html__( 'Fit Columns', 'total-theme-core' ),
				'masonry'     => esc_html__( 'Masonry', 'total-theme-core' ),
			);
			break;
		case 'slider_animation':
			$choices = array(
				'fade_slides' => esc_html__( 'Fade', 'total-theme-core' ),
				'slide'       => esc_html__( 'Slide', 'total-theme-core' ),
			);
			break;
		case 'text_decoration':
			$choices = vcex_text_decorations();
			break;
		case 'font_style':
			$choices = vcex_font_styles();
			break;
		default:
			if ( is_callable( $choices ) ) {
				$choices = call_user_func( $choices );
			}
			break;
	}

	if ( ! $choices ) {
		$output .= '<input type="text" class="wpb_vc_param_value '
				. esc_attr( $settings['param_name'] ) . ' '
				. esc_attr( $settings['type'] ) . '" name="' . esc_attr( $settings['param_name'] ) . '" value="' . esc_attr( $value ) . '"/>';
	}

	$output = '<div class="' . esc_attr( implode( ' ', $wrap_classes ) ) . '">';

	if ( ! $value ) {
		if ( isset( $settings['std'] ) ) {
			$value = $settings['std'];
		} else {
			$temp_choices = $choices;
			reset( $temp_choices );
			$value = key( $temp_choices );
		}
	}

	foreach ( $choices as $id => $label ) {

		$choice_class = array( 'vcex-opt' );

		if ( $id == $value ) {
			$choice_class[] = 'vcex-active';
		}
		if ( $id ) {
			$choice_class[] = 'vcex-opt-' . sanitize_html_class( $id );
		}

		if ( ! defined( 'TOTAL_THEME_ACTIVE' ) ) {
			$label = str_replace( 'ticon', 'fa', $label );
		}

		$output .= '<button class="' . esc_attr( implode( ' ', $choice_class ) ) . '" data-value="' . esc_attr( $id )  . '">' . wp_kses_post( $label ) . '</button>';

	}

	$output .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="vcex-hidden-input wpb-input wpb_vc_param_value  ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '" />';

	$output .= '</div>';

	return $output;

}

vc_add_shortcode_param(
	'vcex_select_buttons',
	'vcex_select_buttons',
	vcex_asset_url( 'js/params/vcex-params.min.js?v=' . TTC_VERSION )
);