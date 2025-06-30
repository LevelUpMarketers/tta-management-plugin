<?php
/**
 * Video widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class VideoWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_video',
			'name'    => $this->branding() . esc_html__( 'Video', 'total-theme-core' ),
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
					'id'          => 'video_url',
					'label'       => esc_html__( 'Video URL', 'total-theme-core' ),
					'type'        => 'url',
					'description' => esc_html__( 'Enter in a video URL that is compatible with WordPress\'s built-in oEmbed feature.', 'total-theme-core' ) . ' (<a href="https://wordpress.org/support/article/embeds/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn More', 'total-theme-core' ) . '</a>)'
				),
				array(
					'id'    => 'video_description',
					'label' => esc_html__( 'Description', 'total-theme-core' ),
					'type'  => 'textarea',
				),
				array(
					'id'      => 'text_align',
					'label'   => esc_html__( 'Text Align', 'total-theme-core' ),
					'type'    => 'select',
					'default' => 'center',
					'choices' => array(
						'left'   => esc_html__( 'Left', 'total-theme-core' ),
						'center' => esc_html__( 'Center', 'total-theme-core' ),
						'right'  => esc_html__( 'Right', 'total-theme-core' ),
					),
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

		// Show video
		if ( $video_url )  {

			$output .= '<div class="responsive-video-wrap wpex-clr">';

				$output .= wp_oembed_get( $video_url, array(
					'width' => 270
				) );

			$output .= '</div>';

		} else {

			$output .= esc_html__( 'You forgot to enter a video URL.', 'total-theme-core' );

		}

		// Show video description if field isn't empty
		if ( $video_description ) {

			if ( true === wp_validate_boolean( $wpautop ) ) {
				$video_description = wpautop( $video_description );
			}

			$output .= '<div class="wpex-video-widget-description text' . wp_strip_all_tags( $text_align ) . ' wpex-mt-15 wpex-last-mb-0">' . wp_kses_post( $video_description ) . '</div>';

		}

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\VideoWidget' );