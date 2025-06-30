<?php
/**
 * Customizer theme_mod related functions.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrapper for get_theme_mod with the ability to force the default value.
 *
 * @todo rename to wpex_get_theme_mod() ?
 */
function wpex_get_mod( $id, $default = '', $not_empty = false ) {
	$value = get_theme_mod( $id, $default );
	return ( $not_empty && ! $value ) ? $default : $value;
}

/**
 * Creates a backup of your theme mods.
 */
function wpex_backup_mods() {
	update_option( 'wpex_total_customizer_backup', get_theme_mods(), false );
}
