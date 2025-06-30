<?php
/**
 * Recommend/bundled plugins.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns array of recommended plugins.
 *
 * @since 3.3.3
 */
function wpex_recommended_plugins() {

	return apply_filters( 'wpex_recommended_plugins', array(
		'total-theme-core'       => array(
			'name'               => 'Total Theme Core',
			'slug'               => 'total-theme-core',
			'version'            => WPEX_THEME_CORE_PLUGIN_SUPPORTED_VERSION,
			'source'             => 'https://totalwptheme.s3.us-east-1.amazonaws.com/plugins/total-theme-core/version-1-2-5/total-theme-core.zip',
			'required'           => true,
			'force_activation'   => false,
		),
		'js_composer'          => array(
			'name'             => 'WPBakery Page Builder',
			'slug'             => 'js_composer',
			'version'          => WPEX_VC_SUPPORTED_VERSION,
			'source'           => 'https://totalwptheme.s3.us-east-1.amazonaws.com/plugins/wpbakery/version-6-4-1/js_composer.zip',
			'required'         => false,
			'force_activation' => false,
		),
		'templatera'           => array(
			'name'             => 'Templatera',
			'slug'             => 'templatera',
			'source'           => 'https://totalwptheme.s3.us-east-1.amazonaws.com/plugins/templatera/version-2-0-4/templatera.zip',
			'version'          => '2.0.4',
			'required'         => false,
			'force_activation' => false,
		),
		'revslider'            => array(
			'name'             => 'Slider Revolution',
			'slug'             => 'revslider',
			'version'          => '6.2.23',
			'source'           => 'https://totalwptheme.s3.us-east-1.amazonaws.com/plugins/revslider/version-6-2-23/revslider.zip',
			'required'         => false,
			'force_activation' => false,
		),
		'contact-form-7'       => array(
			'name'             => 'Contact Form 7',
			'slug'             => 'contact-form-7',
			'required'         => false,
			'force_activation' => false,
		),
	) );

}

/**
 * Register recommended plugins with the tgmpa script
 *
 * @since 5.0
 */
if ( wpex_is_request( 'admin') && get_theme_mod( 'recommend_plugins_enable', true ) ) {

	if ( ! class_exists( 'TGM_Plugin_Activation' ) ) {
		require_once WPEX_FRAMEWORK_DIR . 'lib/tgmpa/class-tgm-plugin-activation.php';
	}

	function wpex_tgmpa_register() {

		// Get array of recommended plugins
		// See framework/core-functions.php
		$plugins = wpex_recommended_plugins();

		// Dismissable is true by default (lets users dismiss the notice completely)
		$dismissable = true;

		// Prevent dismiss for Visual Composer
		// And remove VC plugin from recommended list if it has a valid license
		// to prevent update issues between TGMPA and VC plugin
		if ( WPEX_VC_ACTIVE ) {
			if ( wpex_vc_theme_mode_check() ) {
				$dismissable = wpex_vc_is_supported() ? true : false;
			} else {
				unset( $plugins['js_composer'] );
			}
		}

		// Register notice
		tgmpa( $plugins, array(
			'id'           => 'wpex_theme',
			'domain'       => 'total',
			'menu'         => 'install-required-plugins',
			'has_notices'  => true,
			'is_automatic' => true,
			'dismissable'  => $dismissable,
		) );

	}

	add_action( 'tgmpa_register', 'wpex_tgmpa_register' );

}