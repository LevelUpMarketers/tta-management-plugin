<?php
/**
 * Disable WPBakery Welcome Screen
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	return;
}

// Remove actions
remove_action( 'vc_activation_hook', 'vc_page_welcome_set_redirect' );
remove_action( 'init', 'vc_page_welcome_redirect' );
remove_action( 'admin_init', 'vc_page_welcome_redirect' );

// Remove menu item
function wpex_vc_remove_welcome_page() {
	remove_submenu_page( 'vc-general', 'vc-welcome' );
}
add_action( 'admin_menu', 'wpex_vc_remove_welcome_page', 999 );