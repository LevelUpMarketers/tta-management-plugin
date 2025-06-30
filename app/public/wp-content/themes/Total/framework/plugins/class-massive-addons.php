<?php
/**
 * Massive Addons Tweaks
 *
 * @package Total WordPress Theme
 * @subpackage MassiveAddons
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class MassiveAddons {

	/**
	 * Our single MassiveAddons instance.
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
	 * Create or retrieve the instance of MassiveAddons.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new MassiveAddons;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Disable Total advanced parallax since it conflicts with Massive Addons
		add_filter( 'vcex_supports_advanced_parallax', '__return_false' );

	}

}
MassiveAddons::instance();