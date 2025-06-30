<?php
/**
 * Used to insert content to the top/bottom of the mobile menu.
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<?php if ( has_action( 'wpex_mobile_menu_top' ) || has_action( 'wpex_hook_mobile_menu_top' ) ) { ?>

	<div class="wpex-mobile-menu-top wpex-hidden"><?php wpex_hook_mobile_menu_top(); ?></div>

<?php } ?>

<?php if ( has_action( 'wpex_mobile_menu_bottom' ) || has_action( 'wpex_hook_mobile_menu_bottom' ) ) { ?>

	<div class="wpex-mobile-menu-bottom wpex-hidden"><?php wpex_hook_mobile_menu_bottom(); ?></div>

<?php } ?>