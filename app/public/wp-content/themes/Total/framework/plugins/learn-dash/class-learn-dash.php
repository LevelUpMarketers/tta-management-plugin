<?php
/**
 * LearnDash Config
 *
 * @package Total WordPress Theme
 * @subpackage LearnDash
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class LearnDash {

	/**
	 * Our single LearnDash instance.
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
	 * Create or retrieve the instance of LearnDash.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new LearnDash;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_filter( 'wpex_customizer_panels', array( $this, 'customizer_settings' ) );
		add_filter( 'wpex_post_layout_class', array( $this, 'layouts' ) );
		add_filter( 'wpex_main_metaboxes_post_types', array( $this, 'page_settings_meta' ) );
		add_filter( 'wpex_has_breadcrumbs', array( $this, 'wpex_has_breadcrumbs' ) );
	}

	/**
	 * Adds Customizer settings for LearnDash.
	 */
	public function customizer_settings( $panels ) {
		$branding = ( $branding = wpex_get_theme_branding() ) ? ' (' . $branding . ')' : '';
		$panels['learndash'] = array(
			'title'      => esc_html__( 'LearnDash', 'total' ) . $branding,
			'settings'   => WPEX_FRAMEWORK_DIR . 'plugins/learn-dash/customizer.php'
		);
		return $panels;
	}

	/**
	 * Alter default layout.
	 */
	public function layouts( $layout ) {
		$types = $this->get_learndash_types();

		foreach( $types as $type ) {

			// Archives
			if ( is_post_type_archive( $type ) ) {
				return get_theme_mod( $type . '_archives_layout', get_theme_mod( 'learndash_layout' ) );
			}

			// Single posts
			if ( is_singular( $type ) ) {
				return get_theme_mod( $type . '_single_layout', get_theme_mod( 'learndash_layout' ) );
			}

		}

		// Return layout
		return $layout;

	}

	/**
	 * Add LearnDash post types to array of post types to use with Total page settings metabox.
	 */
	public function page_settings_meta( $types ) {
		if ( get_theme_mod( 'learndash_wpex_metabox', true ) ) {
			$types = array_merge( $types, $this->get_learndash_types() );
		}
		return $types;
	}

	/**
	 * Disable breadcrumbs for LearnDash.
	 */
	public function wpex_has_breadcrumbs( $bool ) {

		$types = $this->get_learndash_types();

		foreach( $types as $type ) {

			if ( is_post_type_archive( $type ) || is_singular( $type ) ) {
				$bool = get_theme_mod( 'learndash_breadcrumbs', true );
			}

		}

		return $bool;
	}

	/**
	 * Return array of learndash post types.
	 */
	public function get_learndash_types() {

		if ( function_exists( 'learndash_get_post_types' ) ) {
			return learndash_get_post_types();
		}

		return array(
			'sfwd-courses',
			'sfwd-lessons',
			'sfwd-topic',
			'sfwd-quiz',
			'sfwd-question',
			'sfwd-certificates',
			'sfwd-assignment',
			'sfwd-groups',
		);

	}

}
LearnDash::instance();