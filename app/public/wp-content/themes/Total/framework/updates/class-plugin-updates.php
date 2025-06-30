<?php
/**
 * Displays plugin update notifications for some bundled theme plugins to make it easier for the end user.
 *
 * @package Total WordPress theme
 * @subpackage Framework
 * @version 5.0.4
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Plugin_Updates {

	/**
	 * Our single Plugin_Updates instance.
	 *
	 * @var Plugin_Updates
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
	 * Create or retrieve the instance of Plugin_Updates.
	 *
	 * @return Plugin_Updates
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Plugin_Updates;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Run action hooks.
	 */
	public function init_hooks() {

		// For testing purposes only !!!
		//set_site_transient( 'update_plugins', null );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
	}

	/**
	 * Returns list of plugins to check
	 */
	public function get_plugins_to_check() {

			if ( ! function_exists( 'wpex_recommended_plugins' ) ) {
				return;
			}

			$recommended_plugins = (array) wpex_recommended_plugins();

			if ( empty( $recommended_plugins ) ) {
				return;
			}

			$plugins_to_check = array(
				'total-theme-core',
			);

			foreach ( $plugins_to_check as $k => $v ) {

				if ( array_key_exists( $v, $recommended_plugins ) ) {
					$plugins_to_check[$k] = $recommended_plugins[$v];
				} else {
					unset( $plugins_to_check[$k] );
				}

			}

			return $plugins_to_check;

	}

	/**
	 * Check transients
	 */
	public function check_for_updates( $transient ) {

		// Transient already checked
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get plugins to check
		$plugins_to_check = $this->get_plugins_to_check();

		if ( empty( $plugins_to_check ) ) {
			return;
		}

		// Return array of installed plugins
		$installed_plugins = $this->get_installed_plugins();

		// No plugins installed
		if ( empty( $installed_plugins ) || ! is_array( $installed_plugins ) ) {
			return $transient;
		}

		// Loop through plugins and check if an update is available
		foreach ( $plugins_to_check as $plugin ) {

			if ( $this->is_plugin_installed( $plugin, $installed_plugins ) ) {
				$has_update = $this->has_update( $plugin, $installed_plugins );
				if ( $has_update ) {
					$response = array(
						'new_version' => $plugin[ 'version' ],
						'package'     => $plugin[ 'source' ],
						'slug'        => $plugin[ 'slug' ]
					);
					$transient->response[ $this->get_plugin_base( $plugin ) ] = (object) $response;
				}
			}
		}

		//var_dump( $transient );

		// Return transient
		return $transient;

	}

	/**
	 * Get list of installed plugins.
	 */
	public function get_installed_plugins( $plugin_folder = '' ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return get_plugins( $plugin_folder );
	}

	/**
	 * Check if a specific plugin is installed.
	 */
	public function is_plugin_installed( $plugin, $installed_plugins ) {
		return array_key_exists( $this->get_plugin_base( $plugin ), $installed_plugins );
	}

	/**
	 * Check if a plugin has an update available
	 */
	private function has_update( $plugin, $installed_plugins ) {
		$base = $this->get_plugin_base( $plugin );
		if ( ! empty( $installed_plugins[$base]['Version'] ) ) {
			return version_compare( $plugin['version'], $installed_plugins[$base]['Version'], '>' );
		}
	}

	/**
	 * Returns plugin base from slug
	 */
	private function get_plugin_base( $plugin ) {
		return $plugin['slug'] . '/' . $plugin['slug'] . '.php';
	}

}
Plugin_Updates::instance();