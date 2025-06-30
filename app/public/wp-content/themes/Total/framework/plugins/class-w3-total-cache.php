<?php
/**
 * W3 Total Cache Configuration Class.
 *
 * @package Total WordPress Theme
 * @subpackage plugins
 * @version 5.0.6
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class W3_Total_cache {

	/**
	 * Our single W3_Total_cache instance.
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
	 * Create or retrieve the instance of W3_Total_cache.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new W3_Total_cache;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 5.0.6
	 */
	public function init_hooks() {
		add_filter( 'w3tc_minify_css_do_tag_minification', array( $this, 'exclude_css_from_minify' ), 10, 3 );
	}

	/**
	 * Exclude certain theme files from the minification process.
	 *
	 * @since 5.0.6
	 */
	public function exclude_css_from_minify( $do_tag_minification, $style_tag, $file ) {

		if ( ! empty( $file ) ) {

			$exclude_files = array(
				'wpex-mobile-menu-breakpoint-max',
				'wpex-mobile-menu-breakpoint-min',
			);

			foreach ( $exclude_files as $excluded_file ) {

				if ( false !== strpos( $file, $excluded_file ) ) {
					return false;
				}

			}

		}

		return $do_tag_minification;

	}

}
W3_Total_cache::instance();