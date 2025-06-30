<?php
/**
 * WordPress fallbacks for new core functions used in the theme incase user's site is outdated.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Body open hook.
 */
if ( ! function_exists( 'wp_body_open' ) ) {
    function wp_body_open() {
        do_action( 'wp_body_open' );
    }
}