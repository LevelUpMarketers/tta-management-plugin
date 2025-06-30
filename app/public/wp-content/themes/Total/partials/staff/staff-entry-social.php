<?php
/**
 * Staff entry social profile links
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'staff_entry_social', true ) ) {
	return;
}

echo wpex_get_staff_social();