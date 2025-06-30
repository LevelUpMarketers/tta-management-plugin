<?php
/**
 * Newsletter widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class NewsletterWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_mailchimp',
			'name'    => $this->branding() . esc_html__( 'Newsletter Form', 'total-theme-core' ),
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
					'id'      => 'style',
					'label'   => esc_html__( 'Style', 'total-theme-core' ),
					'type'    => 'select',
					'default' => 'boxed',
					'choices' => array(
						'boxed'    => esc_html__( 'Boxed', 'total-theme-core' ),
						'bordered' => esc_html__( 'Bordered', 'total-theme-core' ),
						'plain'    => esc_html__( 'Plain', 'total-theme-core' ),
					),
				),
				array(
					'id'      => 'border_radius',
					'label'   => esc_html__( 'Border Radius', 'total-theme-core' ),
					'type'    => 'select',
					'default' => 'square',
					'choices' => array(
						'' => esc_html__( 'None', 'total-theme-core' ),
						'semi-rounded' => esc_html__( 'Semi Rounded', 'total-theme-core' ),
						'rounded' => esc_html__( 'Rounded', 'total-theme-core' ),
					),
				),
				array(
					'id'      => 'alignment',
					'label'   => esc_html__( 'Alignment', 'total-theme-core' ),
					'type'    => 'select',
					'default' => 'center',
					'choices' => array(
						'center' => esc_html__( 'Center', 'total-theme-core' ),
						'right'  => esc_html__( 'Right', 'total-theme-core' ),
						'left'   => esc_html__( 'Left', 'total-theme-core' ),
					),
				),
				array(
					'id'    => 'heading',
					'label' => esc_html__( 'Heading', 'total-theme-core' ),
					'type'  => 'text',
					'std'   => esc_html__( 'Newsletter', 'total-theme-core' ),
				),
				array(
					'id'          => 'form_action',
					'label'       => esc_html__( 'Form Action URL', 'total-theme-core' ),
					'type'        => 'text',
					'description' => '<a href="https://wpexplorer-themes.com/total/docs/mailchimp-form-action-url/" target="_blank">' . esc_html__( 'Learn more', 'total-theme-core' ) . '&rarr;</a>',
				),
				array(
					'id'    => 'description',
					'label' => esc_html__( 'Description', 'total-theme-core' ),
					'type'  => 'textarea',
				),
				array(
					'id'    => 'name_field',
					'label' => esc_html__( 'Display First Name Field?', 'total-theme-core' ),
					'type'  => 'checkbox',
				),
				array(
					'id'      => 'name_placeholder_text',
					'label'   => esc_html__( 'First Name Placeholder Text', 'total-theme-core' ),
					'type'    => 'text',
					'default' => esc_html__( 'First name', 'total-theme-core' ),
				),
				array(
					'id'    => 'last_name_field',
					'label' => esc_html__( 'Display Last Name Field?', 'total-theme-core' ),
					'type'  => 'checkbox',
				),
				array(
					'id'      => 'last_name_placeholder_text',
					'label'   => esc_html__( 'Last Name Placeholder Text', 'total-theme-core' ),
					'type'    => 'text',
					'default' => esc_html__( 'Last name', 'total-theme-core' ),
				),
				array(
					'id'      => 'placeholder_text',
					'label'   => esc_html__( 'Email Input Placeholder Text', 'total-theme-core' ),
					'type'    => 'text',
					'default' => esc_html__( 'Your email address', 'total-theme-core' ),
				),
				array(
					'id'      => 'button_text',
					'label'   => esc_html__( 'Button Text', 'total-theme-core' ),
					'type'    => 'text',
					'default' => esc_html__( 'Subscribe', 'total-theme-core' ),
				),
				// Attributes
				array(
					'id'      => 'email_input_name',
					'label'   => esc_html__( 'Email Input Attribute', 'total-theme-core' ),
					'type'    => 'text',
					'default' =>'EMAIL',
					'description' => esc_html__( 'Used for the input name attribute value.', 'total-theme-core' ),
				),
				array(
					'id'      => 'name_input_name',
					'label'   => esc_html__( 'First Name Input Attribute', 'total-theme-core' ),
					'type'    => 'text',
					'default' => 'FNAME',
					'description' => esc_html__( 'Used for the input name attribute value.', 'total-theme-core' ),
				),
				array(
					'id'      => 'last_name_input_name',
					'label'   => esc_html__( 'Last Name Input Attribute', 'total-theme-core' ),
					'type'    => 'text',
					'default' => 'LNAME',
					'description' => esc_html__( 'Used for the input name attribute value.', 'total-theme-core' ),
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

		// Set default alignment if not defined or correct
		if ( ! $alignment || ! in_array( $alignment, array( 'left', 'center', 'right' ) ) ) {
			$alignment = 'center';
		}

		// Style can't be empty
		$style = ! empty( $style ) ? $style : 'boxed';

		// Define widget output
		$output = '';

		$classes = array(
			'wpex-newsletter-widget',
			'wpex-' . sanitize_html_class( $style ),
			'wpex-text-' . sanitize_html_class( $alignment ),
		);

		if ( $border_radius ) {
			$classes[] = 'wpex-' . sanitize_html_class( $border_radius );
		}

		$output .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

			// Display the heading
			if ( $heading ) {

				$output .= ' <div class="wpex-newsletter-widget-heading wpex-heading wpex-widget-heading wpex-text-lg wpex-mb-10">' . wp_kses_post( $heading ) . '</div>';

			}

			// Display the description
			if ( $description ) {

				$output .= '<div class="wpex-newsletter-widget-description wpex-text-sm wpex-mb-15 wpex-last-mb-0">';

					$output .= wp_kses_post( $description );

				$output .= '</div>';

			}

			$output .= '<form action="'. esc_attr( $form_action ) .'" method="post">';

				$input_class = 'wpex-bg-white wpex-w-100 wpex-mb-5 wpex-text-' . sanitize_html_class( $alignment );

				// Name field
				if ( $name_field ) {

					$name_input_name = $name_input_name ? $name_input_name : 'FNAME';

					$output .= '<label>';

						$output .= '<span class="screen-reader-text">' . esc_html( $name_placeholder_text ) . '</span>';

						$output .= '<input type="text" placeholder="' . esc_attr( $name_placeholder_text ) . '" name="' . esc_attr( $name_input_name ) . '" autocomplete="off" class="' . esc_attr( $input_class ) . '">';

					$output .= '</label>';

				}

				// Lastname field
				if ( $last_name_field ) {

					$last_name_input_name = $last_name_input_name ? $last_name_input_name : 'LNAME';

					$output .= '<label>';

						$output .= '<span class="screen-reader-text">' . esc_html( $last_name_placeholder_text ) . '</span>';

						$output .= '<input type="text" placeholder="' . esc_attr( $last_name_placeholder_text ) . '" name="' . esc_attr( $last_name_input_name ) . '" autocomplete="off" class="' . esc_attr( $input_class ) . '">';

					$output .= '</label>';

				}

				// Email input
				$email_input_name = $email_input_name ? $email_input_name : 'EMAIL';

				$output .= '<label>';

					$output .= '<span class="screen-reader-text">' . esc_html( $placeholder_text ) . '</span>';

					$output .= '<input type="email" name="' . esc_attr( $email_input_name ) . '" placeholder="' . esc_attr( $placeholder_text ) . '" autocomplete="off" class="' . esc_attr( $input_class ) . '">';

				$output .= '</label>';

				$output .= apply_filters( 'wpex_mailchimp_widget_form_extras', null );

				// Submit button
				$output .= '<button type="submit" value="" name="subscribe" class="wpex-block wpex-w-100 wpex-mt-5 wpex-p-10 wpex-text-base wpex-text-center">' . wp_strip_all_tags( $button_text ) . '</button>';

			$output .= '</form>';

		$output .= '</div>';

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\NewsletterWidget' );