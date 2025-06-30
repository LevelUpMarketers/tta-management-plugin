<?php
/**
 * Custom WPBakery Shortcodes
 *
 * @package TotalThemeCore
 * @version 1.2.5
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class WPBakeryShortcodes {

	/**
	 * Is wpbakery plugin is active?
	 *
	 * @var bool
	 */
	protected $wpbakery_is_active = false;

	/**
	 * Are we currently in the front-end editor?
	 *
	 * @var bool
	 */
	protected $vc_is_inline = false;

	/**
	 * Our single WPBakeryShortcodes instance.
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
	 * Create or retrieve the instance of WPBakeryShortcodes.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new WPBakeryShortcodes;
			static::$instance->set_vars();
			static::$instance->include_files();
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Set class vars.
	 */
	public function set_vars() {
		$this->wpbakery_is_active = function_exists( 'vc_lean_map' );
		$this->vc_is_inline       = function_exists( 'vc_is_inline' ) && vc_is_inline();
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// VC only functions
		if ( $this->wpbakery_is_active ) {
			add_action( 'vc_before_mapping', array( $this, 'vc_before_mapping' ) );
		}

		// Add shortcodes to tinymce
		add_filter( 'wpex_shortcodes_tinymce_json', array( $this, 'shortcodes_tinymce' ) );

		// Backend WPBakery editor scripts
		if ( $this->wpbakery_is_active && ( is_request( 'admin' ) || $this->vc_is_inline ) ) {
			add_action( 'vc_inline_editor_page_view', array( $this, 'frontend_editor_scripts' ), PHP_INT_MAX );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}

		// Fronend scripts used for element design
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), PHP_INT_MAX );

	}

	/**
	 * Includes files.
	 */
	public function include_files() {

		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/deprecated.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/shortcodes-list.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/core-functions.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/sanitization.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/arrays.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/mapper.php';

		// Allow frontend editor support for templatera
		if ( $this->wpbakery_is_active && ( is_request( 'admin' ) || $this->vc_is_inline ) ) {
			require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/templatera/class-templatera-frontend-support.php';
		}

		// Classes
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/classes/class-vcex-query-builder.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/classes/class-vcex-inline-style.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/classes/class-vcex-source-value.php';

		// Frontend functions
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/frontend/helpers.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/frontend/entry-classes.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/frontend/scripts.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/frontend/loadmore.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/frontend/grid-filter.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/frontend/font-icons.php';

		// Load shortcode files
		$this->require_shortcode_classes();

	}

	/**
	 * Run functions before/needed for VC mapping.
	 */
	public function vc_before_mapping() {
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/edit-form-fields.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/iconpicker.php';
		require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/functions/autocomplete.php';
		if ( function_exists( 'vc_add_shortcode_param' ) ) {
			require_once TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/shortcode-params/load.php';
		}
	}

	/**
	 * Load shortcode classes.
	 */
	public function require_shortcode_classes() {

		$modules = vcex_builder_modules();

		if ( ! empty( $modules ) ) {

			foreach ( $modules as $key => $val ) {

				$file = '';

				if ( is_array( $val ) ) {

					$condition = isset( $val['condition'] ) ? $val['condition'] : true;

					if ( $condition ) {

						$file = isset( $val['file'] ) ? $val['file'] : TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/shortcodes/' . wp_strip_all_tags( $key ) . '.php';

					}

				} else {

					$file = TTC_PLUGIN_DIR_PATH . 'inc/wpbakery/shortcodes/' . wp_strip_all_tags( $val ) . '.php';

				}

				if ( $file && file_exists( $file ) ) {
					require_once $file;
				}

			}

		}

	}

	/**
	 * Add shortcodes to tinymce.
	 */
	public function shortcodes_tinymce( $data ) {

		if ( ! apply_filters( 'vcex_wpex_shortcodes_tinymce', true ) ) {
			return $data;
		}

		$data['shortcodes']['vcex_button'] = array(
			'text' => esc_html__( 'Button', 'total-theme-core' ),
			'insert' => '[vcex_button url="#" title="Visit Site" style="flat" align="left" color="black" size="small" target="self" rel="none"]Button Text[/vcex_button]',
		);

		$data['shortcodes']['vcex_divider'] = array(
			'text' => esc_html__( 'Divider', 'total-theme-core' ),
			'insert' => '[vcex_divider color="#dddddd" width="100%" height="1px" margin_top="20" margin_bottom="20"]',
		);

		$data['shortcodes']['vcex_divider_dots'] = array(
			'text' => esc_html__( 'Divider Dots', 'total-theme-core' ),
			'insert' => '[vcex_divider_dots color="#dd3333" margin_top="10" margin_bottom="10"]',
		);

		$data['shortcodes']['vcex_spacing'] = array(
			'text' => esc_html__( 'Spacing', 'total-theme-core' ),
			'insert' => '[vcex_spacing size="20px"]',
		);

		$data['shortcodes']['vcex_spacing'] = array(
			'text' => esc_html__( 'Spacing', 'total-theme-core' ),
			'insert' => '[vcex_spacing size="30px"]',
		);

		return $data;

	}

	/**
	 * Editor Scripts.
	 */
	public function frontend_editor_scripts() {

		wp_enqueue_script(
			'vcex-vc_reload',
			vcex_asset_url( 'js/frontend-editor/vcex-vc_reload.min.js' ),
			array( 'jquery' ),
			TTC_VERSION,
			true
		);

	}

	/**
	 * Admin Scripts.
	 */
	public function admin_scripts( $hook ) {

		$hooks = array(
			'edit.php',
			'post.php',
			'post-new.php',
			'widgets.php',
			'toolset_page_ct-editor', // Support VC widget plugin
		);

		if ( ! in_array( $hook, $hooks ) ) {
			return;
		}

		wp_enqueue_style(
			'vcex-shortcodes-params',
			vcex_asset_url( 'css/vcex-shortcodes-params.css' ),
			array(),
			TTC_VERSION
		);

	}

	/**
	 * Frontend Scripts.
	 */
	public function frontend_scripts() {

		if ( ! apply_filters( 'vcex_enqueue_frontend_js', true ) ) {
			return;
		}

		$deps = array( 'jquery' );

		if ( defined( 'WPEX_THEME_JS_HANDLE' ) ) {
			$deps[] = WPEX_THEME_JS_HANDLE;
		}

		wp_enqueue_script(
			'vcex-shortcodes',
			vcex_asset_url( 'js/vcex-shortcodes.min.js' ),
			$deps,
			TTC_VERSION,
			true
		);

	}

}
WPBakeryShortcodes::instance();