<?php
/**
 * bbPress Configuration Class
 *
 * @package Total WordPress Theme
 * @subpackage BuddyPress
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class BuddyPress {

	/**
	 * Our single BuddyPress instance.
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
	 * Create or retrieve the instance of BuddyPress.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new BuddyPress;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 20 );
		add_filter( 'wpex_post_layout_class', array( $this, 'layouts' ), 11 ); // on 11 due to bbPress issues
	}

	/**
	 * Load custom CSS.
	 *
	 * @since  4.0
	 */
	public function scripts() {
		wp_enqueue_style(
			'wpex-buddypress',
			wpex_asset_url( 'css/wpex-buddypress.css' ),
			array(),
			WPEX_THEME_VERSION
		);
	}

	/**
	 * Set layouts.
	 *
	 * @version 4.5
	 */
	public function layouts( $layout ) {
		if ( is_buddypress() ) {
			//$layout = get_theme_mod( 'bp_layout', 'left-sidebar' );
			if ( bp_is_user() ) {
				$layout = get_theme_mod( 'bp_user_layout', wpex_get_default_content_area_layout() );
			}
		}
		return $layout;
	}

}
BuddyPress::instance();