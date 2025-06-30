<?php
/**
 * Business Info widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class BusinessInfoWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_info_widget',
			'name'    => $this->branding() . esc_html__( 'Business Info', 'total-theme-core' ),
			'options' => array(
				'customize_selective_refresh' => true,
			),
			'fields'  => array(
				array(
					'id'    => 'title',
					'label' => esc_html__( 'Title', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'address',
					'label' => esc_html__( 'Address', 'total-theme-core' ),
					'type'  => 'textarea',
				),
				array(
					'id'    => 'phone_number',
					'label' => esc_html__( 'Phone Number', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'phone_number_mobile',
					'label' => esc_html__( 'Mobile Phone Number', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'phone_number_tel_link',
					'label' => esc_html__( 'Add "tel" link to the phone number?', 'total-theme-core' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'fax_number',
					'label' => esc_html__( 'Fax Number', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'email',
					'label' => esc_html__( 'Email', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'          => 'email_label',
					'label'       => esc_html__( 'Email Label', 'total-theme-core' ),
					'type'        => 'text',
					'description' => esc_html__( 'Will display your email by default if this field is empty.', 'total-theme-core' ),
				),
			),
		);

		$this->create_widget( $this->args );

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @since 1.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Parse and extract widget settings
		extract( $this->parse_instance( $instance ) );

		// Before widget hook
		echo wp_kses_post( $args['before_widget'] );

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		$output .= '<ul class="wpex-info-widget wpex-last-mb-0">';

		// Address
		if ( $address ) {

			$output .= '<li class="wpex-info-widget-address wpex-flex wpex-mb-10">';

				if ( function_exists( 'wpex_get_theme_icon_html' ) ) {

					$output .= '<div class="wpex-info-widget-icon wpex-mr-15">' . wpex_get_theme_icon_html( 'map-marker' ) . '</div>';

				}

				$output .= '<div class="wpex-info-widget-data wpex-flex-grow wpex-last-mb-0">' . wpautop( wp_kses_post( $address ) ) . '</div>';

			$output .= '</li>';

		}

		// Phone number
		if ( $phone_number ) {

			$output .= '<li class="wpex-info-widget-phone wpex-flex wpex-mb-10">';

				if ( function_exists( 'wpex_get_theme_icon_html' ) ) {

					$output .= '<div class="wpex-info-widget-icon wpex-mr-15">' . wpex_get_theme_icon_html( 'phone' ) . '</div>';

				}

				$output .= '<div class="wpex-info-widget-data wpex-flex-grow">';

					if ( true == wp_validate_boolean( $phone_number_tel_link ) ) {

						$output .= '<a href="tel:' . wp_strip_all_tags( $phone_number ) . '">' . wp_strip_all_tags( $phone_number ) . '</a>';

					} else {

						$output .= wp_strip_all_tags( $phone_number );

					}

				$output .= '</div>';

			$output .= '</li>';

		}

		// Phone number mobile
		if ( $phone_number_mobile ) {

			$output .= '<li class="wpex-info-widget-phone-mobile wpex-flex wpex-mb-10">';

				if ( function_exists( 'wpex_get_theme_icon_html' ) ) {

					$output .= '<div class="wpex-info-widget-icon wpex-mr-15">' . wpex_get_theme_icon_html( 'mobile' ) . '</div>';

				}

				$output .= '<div class="wpex-info-widget-data wpex-flex-grow">';

					if ( true == wp_validate_boolean( $phone_number_tel_link ) ) {

						$output .= '<a href="tel:' . wp_strip_all_tags( $phone_number_mobile ) . '">' . wp_strip_all_tags( $phone_number_mobile ) . '</a>';

					} else {

						$output .= wp_strip_all_tags( $phone_number_mobile );

					}

				$output .= '</div>';

			$output .= '</li>';

		}

		// Fax number
		if ( $fax_number ) {

			$output .= '<li class="wpex-info-widget-fax wpex-flex wpex-mb-10">';

				if ( function_exists( 'wpex_get_theme_icon_html' ) ) {

					$output .= '<div class="wpex-info-widget-icon wpex-mr-15">' . wpex_get_theme_icon_html( 'fax' ) . '</div>';

				}

				$output .= '<div class="wpex-info-widget-data wpex-flex-grow">' . wp_strip_all_tags( $fax_number ) . '</div>';

			$output .= '</li>';

		}

		// Email
		if ( $email ) {

			// Sanitize email
			$sanitize_email = sanitize_email( $email );
			$is_email       = is_email( $sanitize_email );

			// Spam protect email address
			$protected_email = $is_email ? antispambot( $sanitize_email ) : $sanitize_email;

			// Sanitize & fallback for email label
			$email_label = ( ! $email_label && $is_email ) ? $protected_email : $email_label;

			// Email output
			$output .= '<li class="wpex-info-widget-email wpex-flex wpex-mb-10">';

				if ( function_exists( 'wpex_get_theme_icon_html' ) ) {

					$output .= '<div class="wpex-info-widget-icon wpex-mr-15">' . wpex_get_theme_icon_html( 'envelope' ) . '</div>';

				}

				$output .= '<div class="wpex-info-widget-data wpex-flex-grow">';

					if ( $is_email ) {

						$output .= '<a href="mailto:' . $protected_email . '" class="wpex-inherit-color">' . wp_strip_all_tags( $email_label ) . '</a>';

					} else {

						$parse_email_url = parse_url( $email );

						if ( ! empty( $parse_email_url['scheme'] ) ) {
							$output .= '<a href="' . esc_url( $email ) . '">' . wp_strip_all_tags( $email_label ) . '</a>';
						} else {
							$output .= wp_strip_all_tags( $email_label );
						}

					}

				$output .= '</div>';

			$output .= '</li>';

		}

		$output .= '</ul>';

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\BusinessInfoWidget' );