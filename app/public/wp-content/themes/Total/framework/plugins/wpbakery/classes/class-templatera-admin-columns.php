<?php
/**
 * WPBakery Templatera Admin Columns.
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

namespace TotalTheme\Plugins\WPBakery\Classes;

defined( 'ABSPATH' ) || exit;

final class Templatera_Admin_Columns {

	/**
	 * Our single Templatera_Admin_Columns instance.
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
	 * Create or retrieve the instance of Templatera_Admin_Columns.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Templatera_Admin_Columns;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_filter( 'manage_templatera_posts_columns', array( $this, 'define_columns' ) );
    	add_action( 'manage_templatera_posts_custom_column', array( $this, 'columns_display' ), 10, 2 );
	}

	/**
	 * Define Columns.
	 */
	public function define_columns( $columns ) {
		$columns['wpex_templatera_shortcode'] = esc_html__( 'Shortcode', 'total' );
		$columns['wpex_templatera_id'] = esc_html__( 'ID', 'total' );
    	return $columns;
	}

	/**
	 * Column displays.
	 */
	public function columns_display( $column, $post_id ) {

		switch ( $column ) {

			case 'wpex_templatera_shortcode' :

				echo '<input type="text" onClick="this.select();" value=\'[templatera id="' . absint( $post_id ) . '"]\' readonly>';

			break;

			case 'wpex_templatera_id' :

				echo absint( $post_id );

			break;

		}

	}

}
Templatera_Admin_Columns::instance();