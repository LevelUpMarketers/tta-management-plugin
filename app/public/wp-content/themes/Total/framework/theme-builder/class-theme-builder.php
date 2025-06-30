<?php
/**
 * Theme Builder
 *
 * @package Total WordPress theme
 * @subpackage Theme Builder
 * @version 5.0
 */

namespace TotalTheme;

use TotalTheme\ThemeBuilder\Render_Template as Render_Template;
use TotalTheme\ThemeBuilder\Location_Template as Location_Template;

defined( 'ABSPATH' ) || exit;

final class Theme_Builder {

	/**
	 * Our single Theme_Builder instance.
	 *
	 * @var Theme_Builder
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disable instantiation.
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
	 * Main Theme_Builder Instance.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Theme_Builder ) ) {
			self::$instance = new Theme_Builder;
			self::$instance->includes();
		}

		return self::$instance;
	}

	/**
	 * Includes theme builder helper functions.
	 */
	private function includes() {
		require_once WPEX_FRAMEWORK_DIR . 'theme-builder/class-location-template.php';
		require_once WPEX_FRAMEWORK_DIR . 'theme-builder/class-render-template.php';
	}

	/**
	 * Do location.
	 */
	public function do_location( $location ) {

		// Check for elementor templates first
		if ( function_exists( 'elementor_theme_do_location' ) ) {
			$elementor_doc = elementor_theme_do_location( $location );
			if ( $elementor_doc ) {
				return true;
			}
		}

		// Check for theme templates
		$location_template = new Location_Template( $location );

		if ( ! empty( $location_template->template ) ) {

			$render_template = new Render_Template( $location_template->template, $location );

			return $render_template->render();

		}

	}

}