<?php
/**
 * Enqueue admin scripts
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue theme icons CSS in the WP admin
 *
 * @since 4.0
 */
function wpex_register_admin_scripts() {

	// Theme Icons
	wp_register_style(
		'ticons',
		wpex_asset_url( 'lib/ticons/css/ticons.min.css' ),
		array(),
		WPEX_THEME_VERSION
	);

	// Chosen select
	wp_register_style(
		'wpex-chosen',
		wpex_asset_url( 'lib/chosen/chosen.min.css' ),
		false,
		'1.4.1'
	);

	wp_register_script(
		'wpex-chosen',
		wpex_asset_url( 'lib/chosen/chosen.jquery.min.js' ),
		array( 'jquery' ),
		'1.4.1'
	);

	// Chosen Icons
	wp_register_script(
		'wpex-chosen-icon',
		wpex_asset_url( 'js/dynamic/admin/wpex-chosen-icon.min.js' ),
		array( 'jquery', 'wpex-chosen' ),
		WPEX_THEME_VERSION
	);

	// Theme Panel
	wp_register_style(
		'wpex-admin-pages',
		wpex_asset_url( 'css/wpex-theme-panel.css' ),
		array(),
		WPEX_THEME_VERSION
	);

	wp_register_script(
		'wpex-admin-pages',
		wpex_asset_url( 'js/dynamic/admin/wpex-theme-panel.min.js' ),
		array( 'jquery' ),
		WPEX_THEME_VERSION,
		true
	);

	wp_localize_script( 'wpex-admin-pages', 'wpextp', array(
		'confirmReset'  => esc_html__( 'Confirm Reset', 'total' ),
		'importOptions' => esc_html__( 'Import Options', 'total' ),
	) );

}
add_action( 'admin_enqueue_scripts', 'wpex_register_admin_scripts', 5 );

/**
 * Enqueue theme icons CSS in the WP admin
 *
 * @since 4.0
 */
function wpex_ticons_admin_enqueue( $hook ) {

	// Array of places to load font awesome
	$hooks = array(
		'edit.php',
		'post.php',
		'post-new.php',
		'widgets.php',
	);

	// Only needed on these admin screens
	if ( ! in_array( $hook, $hooks ) ) {
		return;
	}

	// Load font awesome script for VC icons and other
	wp_enqueue_style( 'ticons' );

}
add_action( 'admin_enqueue_scripts', 'wpex_ticons_admin_enqueue' );