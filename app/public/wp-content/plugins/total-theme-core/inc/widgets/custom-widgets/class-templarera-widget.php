<?php
/**
 * Templatera widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class TemplateraWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_templatera',
			'name'    => $this->branding() . esc_html__( 'WPBakery Template', 'total-theme-core' ),
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
					'id'    => 'template',
					'label' => esc_html__( 'Template', 'total-theme-core' ),
					'type'  => 'select_templatera',
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

		// Get template content
		$temp_post = $template ? get_post( $template ) : '';

		if ( $temp_post ) {

			// Add inline styles
			$custom_css = esc_attr( get_post_meta( $template, '_wpb_shortcodes_custom_css', true ) );

			if ( ! empty( $custom_css ) ) {

				$output .= '<style data-type="vc_shortcodes-custom-css">';

					$output .= function_exists( 'wpex_minify_css' ) ? wpex_minify_css( $custom_css ) : wp_strip_all_tags( $custom_css );

				$output .= '</style>';

			}

			$template_content = $temp_post->post_content;

			if ( $template_content ) {

				if ( function_exists( 'wpex_the_content' ) ) {
					$template_content = wpex_the_content( $template_content );
				} else {
					$template_content = do_shortcode( wp_kses_post( $template_content ) );
				}

				// Output html
				$output .= '<div class="wpex-templatera-widget-content wpex-clr">' . $template_content . '</div>';

			}

		}

		// Echo output
		echo $output;

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\TemplateraWidget' );