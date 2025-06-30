<?php
/**
 * Flickr widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 *
 *
 * @deprecated Total v5.0
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class FlickrWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_flickr',
			'name'    => $this->branding() . esc_html__( 'Flickr', 'total-theme-core' ),
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
					'id'    => 'id',
					'label' => esc_html__( 'Flickr ID', 'total-theme-core' ),
					'type'  => 'text',
				),
				array(
					'id'      => 'display',
					'label'   => esc_html__( 'Display', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => array(
						'latest' => esc_html__( 'Latest', 'total-theme-core' ),
						'random' => esc_html__( 'Random', 'total-theme-core' ),
					),
					'default' => 'latest',
				),
				array(
					'id'      => 'number',
					'label'   => esc_html__( 'Number', 'total-theme-core' ),
					'type'    => 'number',
					'default' => 8,
				),
				array(
					'id'          => 'tag',
					'label'       => esc_html__( 'Tags', 'total-theme-core' ),
					'type'        => 'text',
					'description' => esc_html__( 'Enter a comma seperated list of tags to include.', 'total-theme-core' ),
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

		// Display flickr feed if ID is defined
		if ( $id ) {

			$output .= '<div class="wpex-flickr-widget">';

			$url_args = array(
				'count'   => $number,
				'display' => $display,
				'size'    => 's',
				'layout'  => 'x',
				'source'  => 'user',
				'user'    => $id,
			);

			if ( ! empty( $tag ) ) {
				$url_args['tag']    = $tag;
				$url_args['source'] = 'user_tag';
			}

			$url = esc_url( add_query_arg( $url_args, 'https://www.flickr.com/badge_code_v2.gne' ) );

			$output .= '<script src="' . $url . '"></script>';

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
register_widget( 'TotalThemeCore\FlickrWidget' );