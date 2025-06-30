<?php
/**
 * About widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class AboutWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_about',
			'name' => $this->branding() . esc_html__( 'About', 'total-theme-core' ),
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
					'id'    => 'image',
					'label' => esc_html__( 'Image', 'total-theme-core' ),
					'type'  => 'media_upload',
				),
				array(
					'id'      => 'img_size',
					'label'   => esc_html__( 'Image Size', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'intermediate_image_sizes',
					'exclude_custom' => true,
				),
				array(
					'id'      => 'img_style',
					'label'   => esc_html__( 'Image Style', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => array(
						'plain'   => esc_html__( 'Plain', 'total-theme-core' ),
						'rounded' => esc_html__( 'Rounded', 'total-theme-core' ),
						'round'   => esc_html__( 'Round', 'total-theme-core' ),
					),
					'default' => 'plain',
				),
				array(
					'id'      => 'alignment',
					'label'   => esc_html__( 'Alignment', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => array(
						''       => esc_html__( 'Default', 'total-theme-core' ),
						'left'   => esc_html__( 'Left', 'total-theme-core' ),
						'center' => esc_html__( 'Center', 'total-theme-core' ),
						'right'  => esc_html__( 'Right', 'total-theme-core' ),
					),
				),
				array(
					'id'    => 'description',
					'label' => esc_html__( 'Description', 'total-theme-core' ),
					'type'  => 'textarea',
				),
				array(
					'id'    => 'wpautop',
					'label' => esc_html__( 'Automatically add paragraphs', 'total-theme-core' ),
					'type'  => 'checkbox',
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

		// Wrap classes
		$classes = array(
			'wpex-about-widget',
			'wpex-clr'
		);

		if ( $alignment ) {
			$classes[] = 'text' . sanitize_html_class( $alignment );
		}

		// Begin widget wrap
		$output .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		// Sanitize image
		if ( is_numeric( $image ) ) {
			$img_size = $img_size ? $img_size : 'full';
			$image    = wp_get_attachment_image_url( $image, $img_size );
		}

		// Display the image
		if ( $image ) {

			// Image classes
			$img_classes = array( 'wpex-align-middle' );
			if ( 'round' === $img_style || 'rounded' === $img_style ) {
				$img_classes[] = 'wpex-' . sanitize_html_class( $img_style );
			}

			$output .= '<div class="wpex-about-widget-image wpex-mb-20">';

				$output .= '<img class="' . esc_attr( implode( ' ', $img_classes ) ) . '" src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '" />';

			$output .= '</div>';

		}

		// Display the description
		if ( $description ) {

			$output .= '<div class="wpex-about-widget-description wpex-last-mb-0 wpex-clr">';

				if ( true == wp_validate_boolean( $wpautop ) ) {
					$output .= wpautop( wp_kses_post( $description ) );
				} else {
					$output .= wp_kses_post( $description );
				}

			$output .= '</div>';

		}

		// Close widget wrap
		$output .= '</div>';

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\AboutWidget' );