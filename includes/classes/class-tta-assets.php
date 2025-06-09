<?php
/**
 * File: includes/classes/class-tta-assets.php
 * Purpose: Enqueue CSS/JS for both the backend and frontend.
 *          On our Events and Members admin pages, we explicitly load the full wp_editor
 *          (TinyMCE + Quicktags) so that wp_editor() shows the full toolbar, and also
 *          enable wp_enqueue_media() so that our media‐picker buttons work.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Assets {

    /**
     * Hook into WP to enqueue assets.
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_backend_assets' ] );
        add_action( 'wp_enqueue_scripts',    [ __CLASS__, 'enqueue_frontend_assets' ] );
    }

    /**
     * Enqueue admin‐only CSS/JS.
     *
     * We only load wp_enqueue_editor() (plus our admin.js + CSS) when we're on
     * our plugin’s Events or Members screens (page=tta-events OR page=tta-members).
     *
     * @param string $hook_suffix The current admin page.
     */
    public static function enqueue_backend_assets( $hook_suffix ) {
        // Only run on our plugin pages: “Events” or “Members”
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], [ 'tta-events', 'tta-members' ], true ) ) {

            // 1) Make sure the full TinyMCE / Quicktags / editor CSS are loaded:
            if ( function_exists( 'wp_enqueue_editor' ) ) {
                // WP ≥ 4.8: this loads all scripts/styles needed for wp_editor()
                wp_enqueue_editor();
            } else {
                // Fallback for older WP versions (< 4.8)
                wp_enqueue_script( 'editor' );
                wp_enqueue_script( 'quicktags' );
                wp_enqueue_style( 'editor-buttons' );
            }

            // 2) Enable the media uploader for wp.media() (for image pickers, etc.)
            wp_enqueue_media();

            // 3) Now load our own plugin’s admin CSS & JS

            // Admin CSS (tooltips, layout, etc.)
            wp_enqueue_style(
                'tta-admin-css',
                TTA_PLUGIN_URL . 'assets/css/backend/admin.css',
                [],
                TTA_PLUGIN_VERSION
            );

            // Tooltip CSS (pure‐CSS approach)
            wp_enqueue_style(
                'tta-tooltips-css',
                TTA_PLUGIN_URL . 'assets/css/backend/tooltips.css',
                [],
                TTA_PLUGIN_VERSION
            );

            // Admin JS (our AJAX handlers, inline-row toggles, media pickers, spinner logic, etc.)
            // Declare dependencies so that TinyMCE, Quicktags, and media‐uploader have already loaded.
            wp_enqueue_script(
                'tta-admin-js',
                TTA_PLUGIN_URL . 'assets/js/backend/admin.js',
                [ 'jquery', 'wp-editor', 'wp-tinymce', 'quicktags', 'jquery-ui-core' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script(
                'tta-media-js',
                TTA_PLUGIN_URL . 'assets/js/backend/media-uploader.js',
                [],
                TTA_PLUGIN_VERSION,
                true
            );

            // We localize the AJAX URL and nonces for both Events & Members forms
            wp_localize_script(
                'tta-admin-js',
                'TTA_Ajax',
                [
                    'ajax_url'            => admin_url( 'admin-ajax.php' ),
                    'get_event_nonce'     => wp_create_nonce( 'tta_event_get_action' ),
                    'save_event_nonce'    => wp_create_nonce( 'tta_event_save_action' ),
                    'save_member_nonce'   => wp_create_nonce( 'tta_member_save_action' ),
                    'get_member_nonce'    => wp_create_nonce( 'tta_member_update_action' ),
                    'update_member_nonce' => wp_create_nonce( 'tta_member_update_action' ),
                ]
            );
        }
    }

    /**
     * Enqueue front‐end CSS/JS.
     */
    public static function enqueue_frontend_assets() {
        // 1) Main frontend stylesheet (always)
        wp_enqueue_style(
            'tta-frontend-css',
            TTA_PLUGIN_URL . 'assets/css/frontend/style.css',
            [],
            TTA_PLUGIN_VERSION
        );

        // 2) Only on our “Event Page” template, enqueue event‐page.css
        if ( function_exists( 'is_page_template' ) && is_page_template( 'event-page-template.php' ) ) {
            wp_enqueue_style(
                'tta-eventpage-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/event-page.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );
        }
    }
}

TTA_Assets::init();
