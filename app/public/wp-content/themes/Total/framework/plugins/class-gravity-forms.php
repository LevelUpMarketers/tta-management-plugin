<?php
/**
 * Gravity Forms Configuration Class
 *
 * @package Total WordPress Theme
 * @subpackage GravityForms
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class GravityForms {

	/**
	 * Our single GravityForms instance.
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
	 * Create or retrieve the instance of GravityForms.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new GravityForms;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		if ( wpex_is_request( 'frontend' ) && apply_filters( 'wpex_gravity_forms_css', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'gravity_forms_css' ), 40 );
		}

	}

	/**
	 * Loads Gravity Forms stylesheet.
	 *
	 * @since 4.6.5
	 */
	public function gravity_forms_css() {

		global $post;

		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'gravityform' ) ) {

			wp_enqueue_style(
				'wpex-gravity-forms',
				wpex_asset_url( 'css/wpex-gravity-forms.css' ),
				array(),
				WPEX_THEME_VERSION
			);

		}

	}

}
GravityForms::instance();