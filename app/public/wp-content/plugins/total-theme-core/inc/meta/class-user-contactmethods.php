<?php
/**
 * Register social options for users
 *
 * @package TotalThemeCore
 * @version 1.2.4
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class User_Contactmethods {

	/**
	 * Our single User_Contactmethods instance.
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
	 * Create or retrieve the instance of User_Contactmethods.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new User_Contactmethods;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_filter( 'user_contactmethods', array( $this, 'filter_methods' ) );
	}

	/**
	 * Filter methods.
	 */
	public function filter_methods( $contactmethods ) {

		if ( function_exists( 'wpex_get_user_social_profile_settings_array' ) ) {

			$settings = wpex_get_user_social_profile_settings_array();

			if ( ! empty( $settings ) && is_array( $settings ) ) {

				if ( function_exists( 'wpex_get_theme_branding' ) ) {
					$branding = wpex_get_theme_branding();
					$branding = $branding ? $branding . ' - ' : '';
				} else {
					$branding = '';
				}

				foreach ( $settings as $id => $settings ) {
					$label = isset( $settings['label'] ) ? $settings['label'] : $settings; // Fallback for pre 4.5
					$contactmethods[ 'wpex_' . $id ] = esc_html( $branding . $label );
				}

			}

		}

		return $contactmethods;

	}

}
User_Contactmethods::instance();