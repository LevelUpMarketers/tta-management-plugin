<?php
/**
 * Google Map widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class GoogleMapWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_gmap_widget',
			'name'    => $this->branding() . esc_html__( 'Google Map', 'total-theme-core' ),
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
					'id'    => 'description',
					'label' => esc_html__( 'Description', 'total-theme-core' ),
					'type'  => 'textarea',
				),
				array(
					'id'          => 'embed_code',
					'label'       => esc_html__( 'Embed Code or Embed SRC', 'total-theme-core' ),
					'type'        => 'textarea',
					'sanitize'    => 'google_map',
					'description' => esc_html__( 'Enter the full embed code from Google Maps or enter the src value of the embed code.', 'total-theme-core' ),
				),
				array(
					'id'    => 'height',
					'label' => esc_html__( 'Height', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'title_attr',
					'label' => esc_html__( 'iFrame Title Attribute', 'total-theme-core' ),
					'type'  => 'text',
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

		// Begin output
		$output .= '<div class="wpex-gmap-widget wpex-clr">';

			// Display description
			if ( $description ) {

				$output .= '<div class="wpex-gmap-widget-description wpex-clr">';

					$output .= wpautop( wp_kses_post( $description ) );

				$output .= '</div>';

			}

			// Display map
			if ( $embed_code ) {

				$src = '';

				if ( false !== strpos( $embed_code, 'iframe' ) ) {
					preg_match('/src="([^"]+)"/', $embed_code, $match );
					if ( ! empty( $match[1] ) ) {
						$src = $match[1];
					}
				} else {
					$src = $embed_code;
				}

				if ( $src ) {

					$title_attr = $title_attr ? $title_attr : esc_attr__( 'Google Map', 'total-theme-core' );
					$height     = ! empty( $height ) ? absint( $height ) : '';

					$output .= '<div class="wpex-gmap-widget-embed wpex-clr">';

						$output .= '<iframe class="wpex-block wpex-border-0 wpex-p-0 wpex-m-0 wpex-w-100" src="' . esc_url( $src ) . '" title="' . esc_attr( $title_attr ) . '" width="" height="' . esc_attr( $height ) . '" allowfullscreen></iframe>';

					$output .= '</div>';

				}

			}

		// End map element
		$output .= '</div>';

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\GoogleMapWidget' );