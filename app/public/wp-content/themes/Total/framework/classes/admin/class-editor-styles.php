<?php
/**
 * Enqueues scripts and adds inline CSS for the WordPress editor.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.4
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Editor_Styles {

	/**
	 * Our single Editor_Styles instance.
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
	 * Create or retrieve the instance of Editor_Styles.
	 *
	 * @return Editor_Styles
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Editor_Styles;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 5.0
	 */
	public function init_hooks() {

		// Classic editor
		add_action( 'after_setup_theme', array( $this, 'classic_editor_style' ), 10 );
		add_filter( 'tiny_mce_before_init', array( $this, 'classic_editor_content_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_editor_fonts' ) );

		// Gutenberg scripts
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_assets' ) );

	}

	/**
	 * Add styles for the classic
	 *
	 * @since 5.0
	 */
	public function classic_editor_style() {
		add_editor_style( 'assets/css/wpex-editor-style.css' );
		add_editor_style( wpex_asset_url( 'lib/ticons/css/ticons.min.css' ) );
	}

	/**
	 * Adds inline CSS for the editor to match Customizer settings.
	 *
	 * @since 5.0
	 */
	public function classic_editor_content_style( $settings ) {
		$content_style = ! empty( $settings['content_style'] ) ? $settings['content_style'] : '';
		$content_style .= $this->get_editor_css( 'classic' );
		$settings['content_style'] = $content_style;
        return $settings;
	}

	/**
	 * Enqueues fonts for the tiny mce editor.
	 *
	 * @since 5.0
	 */
	public function enqueue_editor_fonts( $hook ) {
		if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			$this->get_editor_css();
		}
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 5.0
	 */
	public function block_editor_assets() {

		// Enqueue the editor styles.
		wp_enqueue_style(
			'wpex-block-editor-styles',
			get_theme_file_uri( '/assets/css/wpex-gutenberg-editor.css' ),
			array(),
			WPEX_THEME_VERSION,
			'all'
		);

		// Add inline style from the Customizer.
		wp_add_inline_style( 'wpex-block-editor-styles', $this->get_editor_css( 'gutenberg' ) );

	}

	/**
	 * Generate inline CSS for the WP editor based on Customizer settings.
	 *
	 * @since 5.0
	 */
	public function get_editor_css( $editor_type = 'classic' ) {

		ob_start();

		if ( class_exists( 'TotalTheme\Typography' ) ) {

			$typography_settings = array(
				'body_typography' => '',
				'post_content_typography' => '',
				'entry_h2_typography' => 'h2',
				'entry_h3_typography' => 'h3',
				'entry_h4_typography' => 'h4',
			);

			foreach ( $typography_settings as $typography_setting => $target_el ) {

				$value = get_theme_mod( $typography_setting );

				if ( ! empty( $value ) && is_array( $value ) ) {

					foreach ( $value as $property => $value ) {

						if ( is_array( $value ) ) {
							$value = reset( $value );
						}

						switch ( $editor_type ) {
							case 'gutenberg':
								$target = '.editor-styles-wrapper > *';
								break;
							case 'classic':
							default:
								$target = 'body#tinymce.wp-editor';
								break;
						}

						if ( 'color' === $property && $this->is_white( $value ) ) {
							continue;
						}

						if ( $target_el ) {
							$target = $target . ' ' . $target_el;
						}

						echo wpex_parse_css( $value, $property, $target );

						if ( 'font-family' === $property ) {
							if ( 'custom' == wpex_get_font_type( $value ) ) {
								echo wpex_render_custom_font_css( $value );
							} else {
								$font = wpex_enqueue_font( $value );
								if ( 'classic' === $editor_type && $font ) {
									add_editor_style( $font );
								}
							}
						}

					}

				}

			} // End foreach typography_settings

			// Font smoothing for classic editor
			if ( 'classic' == $editor_type && get_theme_mod( 'enable_font_smoothing', false ) ) {
				echo 'body#tinymce.wp-editor{-webkit-font-smoothing: antialiased !important;}';
			}

		}

		return ob_get_clean();

	}

	/**
	 * Check if a value is the color white.
	 *
	 * @since 5.0
	 */
	public function is_white( $value = '' ) {
		if ( '#fff' == $value || '#ffffff' == $value || '#FFF' == $value || '#FFFFFF' == $value ) {
			return true;
		}
		return false;
	}

}
Editor_Styles::instance();