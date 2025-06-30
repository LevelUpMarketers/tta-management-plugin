<?php
/**
 * WPBakery Templatera Configuration
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! WPEX_TEMPLATERA_ACTIVE ) {
	return;
}

function wpex_templatera_remove_notices() {
	remove_action( 'admin_notices', 'templatera_notice' );
}
add_action( 'init', 'wpex_templatera_remove_notices' );
