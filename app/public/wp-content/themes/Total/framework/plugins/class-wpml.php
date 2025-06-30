<?php
/**
 * WPML Configuration Class
 *
 * @package Total WordPress Theme
 * @subpackage WPML
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class WPML {

	/**
	 * Our single WPML instance.
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
	 * Create or retrieve the instance of WPML.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new WPML;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		add_filter( 'upload_dir', array( $this, 'upload_dir' ) );

		if ( wpex_is_request( 'admin' ) ) {
			add_action( 'admin_init', array( $this, 'register_strings' ) );
			add_filter( 'wpex_shortcodes_tinymce_json', array( $this, 'tinymce_shortcode' ) );
		}

		if ( wpex_is_request( 'frontend' ) ) {
			add_filter( 'body_class', array( $this, 'body_class' ) );
		}

	}

	/**
	 * Registers theme_mod strings into WPML.
	 *
	 * @since 4.6.5
	 */
	public function register_strings() {
		if ( function_exists( 'icl_register_string' ) && $strings = wpex_register_theme_mod_strings() ) {
			foreach( $strings as $string => $default ) {
				icl_register_string( 'Theme Settings', $string, get_theme_mod( $string, $default ) );
			}
		}
	}

	/**
	 * Registers theme_mod strings into WPML
	 *
	 * @since 3.0.0
	 */
	public function body_class( $classes ) {
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$classes[] = 'wpml-language-' . sanitize_html_class( ICL_LANGUAGE_CODE );
		}
		return $classes;
	}

	/**
	 * Fix for when users have the Language URL Option on "different domains"
	 * which causes cropped images to fail.
	 *
	 * @since 4.6.5
	 */
	public function upload_dir( $upload ) {

		// Check if WPML language_negociation type
		$language_negociation = apply_filters( 'wpml_setting', false, 'language_negotiation_type' );
		if ( $language_negociation !== false && $language_negociation == 2 ) {
			$upload['baseurl'] = apply_filters( 'wpml_permalink', $upload['baseurl'] );
		}

		// Return $upload var
		return $upload;

	}

	/**
	 * Add shortcodes to the tiny MCE.
	 *
	 * @since 4.6.5
	 */
	public function tinymce_shortcode( $data ) {
		if ( shortcode_exists( 'wpml_translate' ) ) {
			$data['shortcodes']['wpml_lang_selector'] = array(
				'text' => esc_html__( 'WPML Switcher', 'total' ),
				'insert' => '[wpml_lang_selector]',
			);
		}
		return $data;
	}

}
WPML::instance();