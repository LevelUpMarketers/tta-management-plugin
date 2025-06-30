<?php
/**
 * Topbar menu displays inside the topbar "content" area
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$menu_class = array(
	'top-bar-menu',
	'wpex-inline-block',
	'wpex-m-0',
	'wpex-list-none',
	'wpex-last-mr-0',
);

if ( wpex_topbar_content() ) {
	$menu_class[] = 'wpex-mr-20';
}

wp_nav_menu( array(
	'theme_location' => 'topbar_menu',
	'fallback_cb'    => false,
	'link_before'    => '<span class="link-inner">',
	'link_after'     => '</span>',
	'container'      => false,
	'menu_class'     => implode( ' ', $menu_class ),
) );