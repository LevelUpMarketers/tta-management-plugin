<?php
/**
 * Minimal Newsletter widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class SimpleNewsletterWiget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_newsletter',
			'name'    => $this->branding() . esc_html__( 'Newsletter Form v2', 'total-theme-core' ),
			'options' => array(
				'customize_selective_refresh' => true,
				'description' => esc_html__( 'Single line newsletter form.', 'total-theme-core' ),
			),
			'fields'  => array(
				array(
					'id'    => 'title',
					'label' => esc_html__( 'Title', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'          => 'form_action',
					'label'       => esc_html__( 'Form Action URL', 'total-theme-core' ),
					'type'        => 'text',
					'description' => '<a href="https://wpexplorer-themes.com/total/docs/mailchimp-form-action-url/" target="_blank">' . esc_html__( 'Learn more', 'total-theme-core' ) . '&rarr;</a>',
				),
				array(
					'id'      => 'placeholder_text',
					'label'   => esc_html__( 'Email Placeholder Text', 'total-theme-core' ),
					'type'    => 'text',
					'default' => esc_html__( 'Your email address', 'total-theme-core' ),
				),
				array(
					'id'          => 'input_name',
					'label'       => esc_html__( 'Email Input Attribute', 'total-theme-core' ),
					'type'        => 'text',
					'default'     =>'EMAIL',
					'description' => esc_html__( 'Used for the input name attribute value.', 'total-theme-core' ),
				),
				array(
					'id'      => 'button_text',
					'label'   => esc_html__( 'Button Text', 'total-theme-core' ),
					'type'    => 'text',
					'default' => esc_html__( 'Sign Up', 'total-theme-core' ),
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

		// Sanitize args
		$input_name = ! empty( $input_name ) ? $input_name : 'EMAIL';

		// Begin output
		$output = '<form action="'. esc_attr( $form_action ) .'" method="post" class="wpex-simple-newsletter wpex-flex wpex-w-100 wpex-justify-center validate">';

			$output .= '<label class="wpex-flex-grow">';

				$output .= '<span class="screen-reader-text">' . esc_html( $placeholder_text ) . '</span>';

				$output .= '<input type="email" name="' . esc_attr( $input_name ) . '" placeholder="' . esc_attr( $placeholder_text ) . '" autocomplete="off" class="wpex-p-10 wpex-w-100 wpex-bg-white wpex-p-10">';

			$output .= '</label>';

			// Extra fields
			$output .= apply_filters( 'wpex_newsletter_widget_form_extras', null );

			// Submit button
			$output .= '<button type="submit" value="" name="subscribe" class="wpex-uppercase wpex-semibold wpex-text-center wpex-p-10 wpex-text-xs wpex-truncate">' . strip_tags( $button_text ) . '</button>';

		$output .= '</form>';

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\SimpleNewsletterWiget' );