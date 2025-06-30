<?php
/**
 * vcex_newsletter_form shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_newsletter_form';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

// Define output var
$output = '';

// Deprecated atts
if ( empty( $atts['form_action'] ) && ! empty( $atts['mailchimp_form_action'] ) ) {
	$atts['form_action'] = $atts['mailchimp_form_action'];
}

// Get and extract shortcode atts
$atts = vcex_vc_map_get_attributes( $shortcode_tag, $atts, $this );

// Extract shortcode atts
extract( $atts );

// Wrapper classes
$wrap_classes = array(
	'vcex-module',
	'vcex-newsletter-form',
	'wpex-clr',
);

if ( $bottom_margin ) {
	$wrap_classes[] = vcex_sanitize_margin_class( $bottom_margin, 'wpex-mb-' );
}

if ( 'true' == $fullwidth_mobile ) {
	$wrap_classes[] = 'vcex-fullwidth-mobile';
}

if ( $classes ) {
	$wrap_classes[] = vcex_get_extra_class( $classes );
}

if ( $visibility ) {
	$wrap_classes[] = sanitize_html_class( $visibility );
}

if ( $css_animation && 'none' != $css_animation ) {
	$wrap_classes[] = vcex_get_css_animation( $css_animation );
}

// Apply filters
$wrap_classes = vcex_parse_shortcode_classes( implode( ' ', $wrap_classes ), $shortcode_tag, $atts );

// Begin output
$output .= '<div class="' . esc_attr( $wrap_classes ) . '"' . vcex_get_unique_id( $unique_id ) . '>';

	$input_width = $input_width ? ' style="width:' . esc_attr( $input_width ) . '"' : '';
	$input_align = $input_align ? ' float' . sanitize_html_class( trim( $input_align ) ) : '';

	$output .= '<div class="vcex-newsletter-form-wrap wpex-max-w-100' . $input_align . '"' . $input_width . '>';

		$output .= '<form action="' . esc_url( $form_action ) . '" method="post" class="wpex-flex">';

			/** Input ***/
			$input_style = vcex_inline_style( array(
				'border'         => $input_border,
				'border_radius'  => $input_border_radius,
				'padding'        => $input_padding,
				'letter_spacing' => $input_letter_spacing,
				'height'         => $input_height,
				'background'     => $input_bg,
				'border_color'   => $input_border_color,
				'color'          => $input_color,
				'font_size'      => $input_font_size,
				'font_weight'    => $input_weight,
			) );

			$input_style = $input_style ? ' ' . $input_style : '';

			$output .= '<label class="vcex-newsletter-form-label wpex-flex-grow">';

				$output .= '<span class="screen-reader-text">' . esc_html( $placeholder_text ) . '</span>';

				$input_name = $input_name ? $input_name : 'EMAIL';

				$output .= '<input type="email" name="' . esc_attr( $input_name ) . '" placeholder="' . esc_attr( $placeholder_text ) . '" autocomplete="off"' . $input_style . '>';

			$output .= '</label>';

			/** Hidden Fields **/
			if ( ! empty( $hidden_fields ) ) {
				$hidden_fields = explode( ',', $hidden_fields );
				if ( is_array( $hidden_fields ) ) {
					foreach( $hidden_fields as $field ) {
						$field_attrs = explode( '|', $field );
						if ( isset( $field_attrs[0] ) && isset( $field_attrs[1] ) ) {
							$output .= '<input type="hidden" name="' . esc_attr( $field_attrs[0] ) . '" value="' . esc_attr( $field_attrs[1] ) . '" />';
						}
					}
				}
			}

			ob_start();
				do_action( 'vcex_newsletter_form_extras' );
			$output .= ob_get_clean();

			/** Submit Button ***/
			if ( $submit_text ) {

				$attrs = array(
					'type'  => 'submit',
					'value' => '',
					'class' => 'vcex-newsletter-form-button',
					'style' => vcex_inline_style( array(
						'height'         => $submit_height,
						'border'         => $submit_border,
						'letter_spacing' => $submit_letter_spacing,
						'padding'        => $submit_padding,
						'background'     => $submit_bg,
						'color'          => $submit_color,
						'font_size'      => $submit_font_size,
						'font_weight'    => $submit_weight,
						'border_radius'  => $submit_border_radius,
					), false ),
				);

				// Add hover data
				$hover_data = array();

				if ( $submit_hover_bg ) {
					$hover_data['background'] = esc_attr( $submit_hover_bg );
				}

				if ( $submit_hover_color ) {
					$hover_data['color'] = esc_attr( $submit_hover_color );
				}

				if ( $hover_data ) {
					$attrs['data-wpex-hover'] = htmlspecialchars( wp_json_encode( $hover_data ) );
				}

				$output .= '<button' . vcex_parse_html_attributes( $attrs ) . '>';

					$output .= do_shortcode( wp_kses_post( $submit_text ) );

				$output .= '</button>';

			}

		$output .= '</form>';

	$output .= '</div>';

$output .= '</div>';

// @codingStandardsIgnoreLine
echo $output;
