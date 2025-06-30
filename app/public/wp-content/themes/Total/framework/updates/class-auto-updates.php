<?php
/**
 * Disable updates for built-in plugins/addons.
 *
 * @package Total WordPress theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Auto_Updates {

	/**
	 * Our single Auto_Updates instance.
	 *
	 * @var Auto_Updates
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
	 *
	 * @return void
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of Auto_Updates.
	 *
	 * @return Auto_Updates
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Auto_Updates;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Run action hooks.
	 */
	public function init_hooks() {
		add_filter( 'auto_update_plugin', array( $this, 'plugin_auto_updates' ), 10, 2 );
	}

	/**
	 * Filter plugin auto updates.
	 */
	public function plugin_auto_updates( $update, $item ) {

		if ( 'total-theme-core' == $item->slug ) {
			return false;
		}

		return $update;

	}

}
Auto_Updates::instance();