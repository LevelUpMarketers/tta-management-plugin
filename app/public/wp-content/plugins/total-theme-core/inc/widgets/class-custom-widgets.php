<?php
/**
 * Define and load custom widgets
 *
 * @package TotalThemeCore
 * @version 1.2
 *
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class Custom_Widgets {

	/**
	 * Our single Custom_Widgets instance.
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
	 * Create or retrieve the instance of Custom_Widgets.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Custom_Widgets;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'widgets_init', array( $this, 'register_custom_widgets' ) );
		add_action( 'admin_print_scripts-widgets.php', array( $this, 'widget_scripts' ) );
	}

	/**
	 * Register custom widgets.
	 */
	public function register_custom_widgets() {

		$custom_widgets = $this->custom_widgets_list();

		if ( empty( $custom_widgets ) || ! is_array( $custom_widgets ) ) {
			return;
		}

		require_once TTC_PLUGIN_DIR_PATH . 'inc/widgets/class-widget-builder.php';

		foreach ( $custom_widgets as $custom_widget ) {
			$file = TTC_PLUGIN_DIR_PATH . 'inc/widgets/custom-widgets/' . wp_strip_all_tags( $custom_widget ) . '.php';
			if ( file_exists ( $file ) ) {
				require_once $file;
			}
		}

	}

	/**
	 * Return custom widgets list.
	 */
	public function custom_widgets_list() {

		$custom_widgets = array(
			'about'               => 'class-about-widget',
			'advertisement'       => 'class-advertisement-widget',
			'newsletter'          => 'class-newsletter-widget',
			'simple-newsletter'   => 'class-simple-newsletter-widget',
			'info'                => 'class-business-info-widget',
			'social-fontawesome'  => 'class-social-profiles-widget',
			'social',
			'simple-menu'         => 'class-simple-menu-widget',
			'modern-menu'         => 'class-modern-menu-widget',
			'facebook-page'       => 'class-facebook-widget',
			'google-map'          => 'class-google-map-widget',
//			'flickr'              => 'class-flickr-widget', // @deprecated 5.0
			'video'               => 'class-video-widget',
			'posts-thumbnails'    => 'class-posts-thumbnails-widget',
			'posts-grid'          => 'class-posts-grid-widget',
			'posts-icons'         => 'class-posts-with-format-icons',
//			'instagram-grid'      => 'class-instagram-grid-widget', // @deprecated 5.0
			'users-grid'          => 'class-users-grid-widget',
			'taxonomy-terms'      => 'class-widget-taxonomy-terms',
			'comments-avatar'     => 'class-comments-widget',
		);

		if ( function_exists( 'templatera_init' ) ) {
			$custom_widgets['templatera'] = 'class-templarera-widget';
		}

		if ( class_exists( 'bbPress' ) ) {
			$custom_widgets['bbpress-forum-info'] = 'class-bbPress-forum-info';
			$custom_widgets['bbpress-topic-info'] = 'class-bbPress-topic-info-widget';
		}

		return apply_filters( 'wpex_custom_widgets', $custom_widgets );

	}

	/**
	 * Custom Widgets scripts
	 *
	 * @since  1.0
	 * @access public
	 */
	public function widget_scripts() {

		wp_enqueue_style(
			'wpex-custom-widgets-admin',
			TTC_PLUGIN_DIR_URL . 'assets/css/custom-widgets-admin.css',
			array(),
			'1.0'
		);

		wp_enqueue_script(
			'wpex-custom-widgets-admin',
			TTC_PLUGIN_DIR_URL . 'assets/js/custom-widgets-admin.min.js',
			array( 'jquery' ),
			'1.0',
			true
		);

		wp_localize_script( 'wpex-custom-widgets-admin', 'wpexCustomWidgets', array(
			'confirm' => esc_html__( 'Do you really want to delete this item?', 'total-theme-core' ),
		) );

	}

}
Custom_Widgets::instance();