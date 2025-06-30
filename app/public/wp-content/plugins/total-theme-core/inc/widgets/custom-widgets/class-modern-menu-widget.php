<?php
/**
 * Modern Menu widget
 *
 * @package Total Theme Core
 * @subpackage Widgets
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class ModernMenuWidget extends WidgetBuilder {
	private $args;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'wpex_modern_menu',
			'name'    => $this->branding() . esc_html__( 'Modern Sidebar Menu', 'total-theme-core' ),
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
					'id'      => 'nav_menu',
					'label'   => esc_html__( 'Select Menu', 'total-theme-core' ),
					'type'    => 'select',
					'choices' => 'menus',
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

		// Output the menu
		if ( $nav_menu ) {

			echo wp_nav_menu( array(
				'menu_class'  => 'modern-menu-widget',
				'fallback_cb' => '',
				'menu'        => $nav_menu,
			) );

		}

		// After widget hook
		echo wp_kses_post( $args['after_widget'] );

	}

}
register_widget( 'TotalThemeCore\ModernMenuWidget' );