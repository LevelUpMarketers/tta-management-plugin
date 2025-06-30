<?php
/**
 * Plugin Name: Total Theme Core
 * Plugin URI: https://wpexplorer-themes.com/total/docs/total-theme-core-plugin/
 * Description: Adds core functionality to the Total WordPress theme including post types, shortcodes, builder modules meta options and more. This is a required plugin for the Total theme and can only be disabled via a child theme or by switching themes.
 * Version: 1.2.5
 * Author: WPExplorer
 * Author URI: https://www.wpexplorer.com/
 * License: Custom license
 * License URI: http://themeforest.net/licenses/terms/regular
 * Text Domain: total-theme-core
 * Domain Path: /languages
 *
 * @author  WPExplorer
 * @package TotalThemeCore
 * @version 1.2.5
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Total_Theme_Core' ) ) {

	final class Total_Theme_Core {

		/**
		 * Our single Total_Theme_Core instance.
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
		 * Create or retrieve the instance of Total_Theme_Core.
		 */
		public static function instance() {
			if ( is_null( static::$instance ) ) {
				static::$instance = new Total_Theme_Core;
				static::$instance->define_constants();
				static::$instance->includes();
				static::$instance->init_hooks();
			}

			return static::$instance;
		}

		/**
		 * Define constants.
		 */
		public function define_constants() {
			define( 'TTC_VERSION', '1.2.5' );
			define( 'TTC_MAIN_FILE_PATH', __FILE__ );
			define( 'TTC_PLUGIN_DIR_PATH', plugin_dir_path( TTC_MAIN_FILE_PATH ) );
			define( 'TTC_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Include files.
		 */
		public function includes() {
			require_once TTC_PLUGIN_DIR_PATH . 'inc/helper-functions.php';

			if ( is_admin() ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/lib/wpex-meta-factory/class-wpex-meta-factory.php';
			}
		}

		/**
		 * Hook into actions and filters (Theme Setup & Theme Action Hooks).
		 */
		public function init_hooks() {

			// Add text domain.
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			// Flush Rewrites when de-activating the plugin.
			register_deactivation_hook( TTC_MAIN_FILE_PATH, 'flush_rewrite_rules' );

			// Do stuff when we activate the plugin for the first time.
			register_activation_hook( TTC_MAIN_FILE_PATH, array( $this, 'on_activation' ) );

			// Load all plugin features.
			add_action( 'after_setup_theme', array( $this, 'load' ) );

		}

		/**
		 * Load Text Domain.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'total-theme-core', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Flush Re-write rules.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function on_activation() {

			if ( get_theme_mod( 'portfolio_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/post-types/class-portfolio.php';
				$instance = TotalThemeCore\Portfolio::instance();
				$instance->on_activation();
			}

			if ( get_theme_mod( 'staff_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/post-types/class-staff.php';
				$instance = TotalThemeCore\Staff::instance();
				$instance->on_activation();
			}

			if ( get_theme_mod( 'testimonials_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/post-types/class-testimonials.php';
				$instance = TotalThemeCore\Testimonials::instance();
				$instance->on_activation();
			}

			flush_rewrite_rules();

		}

		/**
		 * Start things up.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function load() {

			// Don't load on older versions of Total to prevent issues with customers potentially downgrading
			if ( defined( 'WPEX_THEME_VERSION' ) && version_compare( '4.9', WPEX_THEME_VERSION, '>' ) ) {
				return;
			}

			// Custom shortcodes
			require_once TTC_PLUGIN_DIR_PATH . 'inc/shortcodes.php';

			// Demo importer
			if ( get_theme_mod( 'demo_importer_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/demo-importer/demo-importer.php';
			}

			// Custom Widgets
			if ( get_theme_mod( 'custom_widgets_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/widgets/class-custom-widgets.php';
			}

			// Widget Areas
			if ( get_theme_mod( 'widget_areas_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/widgets/class-widget-areas.php';
			}

			// Font Manager
			if ( get_theme_mod( 'font_manager_enable', true ) && defined( 'TOTAL_THEME_ACTIVE' ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/lib/wpex-font-manager/class-wpex-font-manager.php';
			}

			// WPBakery Shortcodes
			if ( get_theme_mod( 'extend_visual_composer', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/class-wpbakery-shortcodes.php';
			}

			// Admin only Meta classes
			if ( is_admin() ) {

				// Main theme metabox class
				require_once TTC_PLUGIN_DIR_PATH . 'inc/meta/class-meta-boxes.php';

				// Cards metabox class
				if ( apply_filters( 'wpex_card_metabox', true ) ) {
					require_once TTC_PLUGIN_DIR_PATH . 'inc/meta/class-wpex-card-meta.php';
				}

				// Custom term meta options
				require_once TTC_PLUGIN_DIR_PATH . 'inc/meta/class-term-meta.php';

				// Register custom user contact methods
				if ( apply_filters( 'wpex_add_user_social_options', true ) ) {
					require_once TTC_PLUGIN_DIR_PATH . 'inc/meta/class-user-contactmethods.php';
				}

			}

			// Custom metabox for adding post galleries
			if ( get_theme_mod( 'gallery_metabox_enable', true ) && is_admin() ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/meta/class-gallery-metabox.php';
			}

			// Term Thumbnails
			if ( get_theme_mod( 'term_thumbnails_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/meta/class-term-thumbnails.php';
			}

			// Custom category settings
			if ( get_theme_mod( 'category_settings_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/meta/class-category-settings.php';
			}

			// Portfolio post type
			if ( get_theme_mod( 'portfolio_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/post-types/class-portfolio.php';
			}

			// Staff post type
			if ( get_theme_mod( 'staff_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/post-types/class-staff.php';
			}

			// Testimonials post type
			if ( get_theme_mod( 'testimonials_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/post-types/class-testimonials.php';
			}

			// Post series
			if ( get_theme_mod( 'post_series_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/addons/class-post-series.php';
			}

			// Custom CSS panel
			if ( defined( 'TOTAL_THEME_ACTIVE' ) && get_theme_mod( 'custom_css_enable', true ) ) {
				require_once TTC_PLUGIN_DIR_PATH . 'inc/addons/class-custom-css-panel.php';
			}

		}

	}

	Total_Theme_Core::instance();

}