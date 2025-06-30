<?php
/**
 * Elementor Configuration Class
 *
 * @package Total WordPress Theme
 * @subpackage Elementor
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Elementor {

	/**
	 * Our single Elementor instance.
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disabled instantiation.
	}

	/**
	 * Disable the cloning of this class.
	 *
	 * @return void
	 */
	final public function __clone() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Disable the wakeup of this class.
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of Elementor.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Elementor;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'elementor/theme/register_locations', array( $this, 'register_locations' ) );
	}

	/**
	 * Loads Gravity Forms stylesheet
	 *
	 * @since 4.9.5
	 */
	public function register_locations( $elementor_theme_manager ) {

		if ( apply_filters( 'total_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}

		$elementor_theme_manager->register_location( 'togglebar', array(
			'label'           => __( 'Togglebar', 'total' ),
			'multiple'        => true,
			'edit_in_content' => false,
		) );

		$elementor_theme_manager->register_location( 'topbar', array(
			'label'           => __( 'Top Bar', 'total' ),
			'multiple'        => true,
			'edit_in_content' => false,
		) );

		$elementor_theme_manager->register_location( 'page_header', array(
			'label'           => __( 'Page Header', 'total' ),
			'multiple'        => true,
			'edit_in_content' => false,
		) );

		$elementor_theme_manager->register_location( 'footer_callout', array(
			'label'           => __( 'Footer Callout', 'total' ),
			'multiple'        => true,
			'edit_in_content' => false,
		) );

		$elementor_theme_manager->register_location( 'footer_bottom', array(
			'label'           => __( 'Footer Bottom', 'total' ),
			'multiple'        => true,
			'edit_in_content' => false,
		) );

	}

}
Elementor::instance();