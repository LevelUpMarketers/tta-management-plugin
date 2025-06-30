<?php
/**
 * WPBakery disable updater
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Delete design options
if ( get_option( 'wpb_js_use_custom' ) ) {
	delete_option( 'wpb_js_use_custom' );
}

// Set correct filter for VC
add_filter( 'vc_settings_page_show_design_tabs', '__return_false' );

// Remove custom style
function wpex_vc_remove_design_panel_css() {
	wp_deregister_style( 'js_composer_custom_css' );
}
add_action( 'wp_enqueue_scripts', 'wpex_vc_remove_design_panel_css' );