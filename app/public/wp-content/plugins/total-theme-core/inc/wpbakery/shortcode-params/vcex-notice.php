<?php
/**
 * Notice VC param
 *
 * @package TotalThemeCore
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

function vcex_notice_shortcode_param( $settings, $value ) {

	return '<div style="color: #9d8967;border: 1px solid #ffeccc;background-color:#fff4e2;padding:1em;">' . esc_html( $settings['text'] ) . '<input class="wpb_vc_param_value" type="hidden" name="' . esc_attr( $settings['param_name'] ) . '" value=""></div>';

}

vc_add_shortcode_param(
	'vcex_notice',
	'vcex_notice_shortcode_param'
);