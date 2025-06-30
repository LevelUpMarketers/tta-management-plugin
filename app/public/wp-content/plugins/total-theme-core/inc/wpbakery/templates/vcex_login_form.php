<?php
/**
 * vcex_login_form shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_login_form';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Get and extract shortcode attributes
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );
extract( $atts );

// Define main vars
$output = '';
$wrap_style = '';
$style = $style ? $style : 'bordered';

// Get classes
$wrap_classes = array(
	'vcex-module',
	'vcex-login-form',
	'wpex-clr'
);

switch ( $style ) {
	case 'boxed':
		$wrap_classes[] = 'wpex-boxed';
		break;
	case 'bordered':
		$wrap_classes[] = 'wpex-bordered';
		break;
}

if ( $form_style ) {
	$wrap_classes[] = 'wpex-form-' . sanitize_html_class( $form_style );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( $css ) {
	$wrap_classes[] = vcex_vc_shortcode_custom_css_class( $css );
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

if ( $text_color || $text_font_size ) {

	$wrap_style = vcex_inline_style( array(
		'color'     => $text_color,
		'font_size' => $text_font_size,
	) );

}

// Apply filters
$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Check if user is logged in and not in front-end editor
if ( is_user_logged_in() && ! vcex_vc_is_inline() ) :

	// Add logged in class
	$wrap_classes .= ' logged-in';

	$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . '>' . do_shortcode( $content ) . '</div>';


// If user is not logged in display login form
else :

	$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . $wrap_style . vcex_get_unique_id( $unique_id ) . '>';

		$output .= wp_login_form( array(
			'echo'           => false,
			'redirect'       => $redirect ? esc_url( $redirect ) : esc_url( wpex_get_current_url() ),
			'form_id'        => 'vcex-loginform',
			'label_username' => $label_username ? $label_username : esc_html__( 'Username', 'total' ),
			'label_password' => $label_password ? $label_password : esc_html__( 'Password', 'total' ),
			'label_remember' => $label_remember ? $label_remember : esc_html__( 'Remember Me', 'total' ),
			'label_log_in'   => $label_log_in ? $label_log_in : esc_html__( 'Log In', 'total' ),
			'remember'       => 'true' == $remember ? true : false,
			'value_username' => NULL,
			'value_remember' => false,
		) );

		if ( 'true' == $register || 'true' == $lost_password ) {

			$output .= '<div class="vcex-login-form-nav wpex-clr">';

				if ( 'true' == $register ) {

					$label        = $register_label ? $register_label :  esc_html__( 'Register', 'total' );
					$register_url = $register_url ? $register_url : wp_registration_url();

					$output .= '<a href="' . esc_url( $register_url ) . '" class="vcex-login-form-register">' . esc_html( $label ) . '</a>';

				}

				if ( 'true' == $register && 'true' == $lost_password ) {
					$output .= '<span class="pipe">|</span>';
				}

				if ( 'true' == $lost_password ) {

					$label    = $lost_password_label ? $lost_password_label :  esc_html__( 'Lost Password?', 'total' );
					$redirect = get_permalink();

					$output .= '<a href="' . esc_url( wp_lostpassword_url( $redirect ) ) . '" class="vcex-login-form-lost">' . esc_html( $label ) . '</a>';
				}

			$output .= '</div>';

		}

	$output .= '</div>';

endif;

// @codingStandardsIgnoreLine
echo $output;
