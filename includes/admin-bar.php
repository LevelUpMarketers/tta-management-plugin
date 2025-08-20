<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hide the admin bar for logged-in users without administrator capabilities.
 *
 * The toolbar is fully suppressed via the `show_admin_bar` filter. A CSS
 * fallback ensures the bar remains hidden if the filter is bypassed.
 *
 * @return void
 */
function tta_maybe_hide_admin_bar() {
    if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
        add_filter( 'show_admin_bar', '__return_false', 100 );
        add_action(
            'wp_print_styles',
            function () {
                echo '<style>#wpadminbar{display:none !important;}html{margin-top:0 !important;}</style>';
            },
            100
        );
    }
}

add_action( 'after_setup_theme', 'tta_maybe_hide_admin_bar' );
