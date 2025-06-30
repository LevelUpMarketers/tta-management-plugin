<?php
/**
 * Advertisement widget
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class AdvertisementWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_advertisement',
			'name' => $this->branding() . esc_html__( 'Advertisement', 'total-theme-core' ),
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
					'id'      => 'columns',
					'label'   => esc_html__( 'Columns', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => array(
						'1' => '1',
						'2' => '2',
					),
				),
				array(
					'id'      => 'columns_gap',
					'label'   => esc_html__( 'Column Gap', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'grid_gaps',
					'default' => 10,
				),
				array(
					'id'    => 'stretch_img',
					'label' => esc_html__( 'Stretch Images?', 'total-theme-core' ),
					'type'  => 'checkbox',
					'description' => esc_html__( 'Force a 100% width on the advertisement images so they fill up the parent container.', 'total-theme-core' ),
				),
				array(
					'id'    => 'nofollow',
					'label' => esc_html__( 'Add "nofollow" to links?', 'total-theme-core' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'target_blank',
					'label' => esc_html__( 'Open links in a new tab?', 'total-theme-core' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'ads',
					'label' => esc_html__( 'Ads', 'total-theme-core' ),
					'type'  => 'repeater',
					'fields' => array(
						array(
							'id' => 'url',
							'label' => esc_html__( 'Link URL', 'total-theme-core' ),
							'type'  => 'text',
						),
						array(
							'id' => 'image',
							'label' => esc_html__( 'Image', 'total-theme-core' ),
							'type'  => 'media_upload',
						),
					),
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
		echo wp_kses_post( $args[ 'before_widget' ] );

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		if ( $ads && is_array( $ads ) ) {

			$output .= '<div class="wpex-ads-widget wpex-row gap-' . esc_attr( $columns_gap ) .' wpex-clr">';

			$count = 0;

			foreach ( $ads as $ad ) :

				if ( empty( $ad[ 'url' ] ) && empty( $ad[ 'image' ] ) ) {
					continue;
				}

				$count ++;

				$col_count = ( $columns > 1 ) ? ' col-' . $count : '';

				$output .= '<div class="col span_1_of_' . esc_attr( $columns ) . $col_count . '">';

				$image = $ad[ 'image' ];
				$alt = '';

				if ( is_numeric( $image ) ) {
					$alt = get_post_meta( $image, '_wp_attachment_image_alt', TRUE );
					$image = wp_get_attachment_url( $image );
				}

				if ( $image ) {

					// Add link tag
					$output .= '<a href="' . esc_url( $ad[ 'url' ] ) . '"';

						if ( wp_validate_boolean( $target_blank ) ) {

							$output .= ' target="_blank"';

						}

						if ( wp_validate_boolean( $nofollow ) ) {

							$output .= ' rel="nofollow"';

						}

					$output .= '>';

					// Display Image

					$image_class = 'wpex-align-bottom';

					if ( wp_validate_boolean( $stretch_img ) ) {

						$image_class .= ' stretch-image';

					}

					$output .= '<img src="' . esc_url( $image ) . '" class="' . esc_attr( $image_class ) . '"';

						if ( $alt ) {

							$output .= ' alt="' . esc_attr( $alt ) . '"';

						}

					$output .= '/>';

					$output .= '</a>';

				}

				$output .= '</div>';

				if ( $columns > 1 && $columns == $count ) {
					$count = 0;
				}

			endforeach;

			$output .= '</div>';

		}


		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args[ 'after_widget' ] );

	}

}
register_widget( 'TotalThemeCore\AdvertisementWidget' );