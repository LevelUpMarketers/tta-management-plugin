<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Settings_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'TTA Settings',
            'TTA Settings',
            'manage_options',
            'tta-settings',
            [ $this, 'render_page' ],
            'dashicons-admin-generic',
            20
        );
    }

    public function render_page() {
        echo '<div class="wrap"><h1>TTA Settings</h1>';

        if ( isset( $_POST['tta_flush_cache'] ) && check_admin_referer( 'tta_flush_cache_action', 'tta_flush_cache_nonce' ) ) {
            TTA_Cache::flush();
            echo '<div class="updated"><p>All caches cleared.</p></div>';
        }

        echo '<form method="post">';
        wp_nonce_field( 'tta_flush_cache_action', 'tta_flush_cache_nonce' );
        echo '<p><input type="submit" name="tta_flush_cache" class="button button-secondary" value="Clear Cache"></p>';
        echo '</form></div>';
    }
}

TTA_Settings_Admin::get_instance();
