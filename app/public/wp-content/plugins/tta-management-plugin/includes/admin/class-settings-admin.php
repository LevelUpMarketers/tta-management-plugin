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

        if ( isset( $_POST['tta_load_sample_data'] ) && check_admin_referer( 'tta_load_sample_data_action', 'tta_load_sample_data_nonce' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                TTA_Sample_Data::load();
                echo '<div class="updated"><p>Sample data loaded.</p></div>';
            }
        }

        if ( isset( $_POST['tta_delete_sample_data'] ) && check_admin_referer( 'tta_delete_sample_data_action', 'tta_delete_sample_data_nonce' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                TTA_Sample_Data::clear();
                echo '<div class="updated"><p>Sample data deleted.</p></div>';
            }
        }

        echo '<form method="post">';
        wp_nonce_field( 'tta_flush_cache_action', 'tta_flush_cache_nonce' );
        echo '<p><input type="submit" name="tta_flush_cache" class="button button-secondary" value="Clear Cache"></p>';
        echo '</form>';

        echo '<form method="post">';
        wp_nonce_field( 'tta_load_sample_data_action', 'tta_load_sample_data_nonce' );
        echo '<p><input type="submit" name="tta_load_sample_data" class="button button-secondary" value="Load Sample Data"></p>';
        echo '</form>';

        echo '<form method="post">';
        wp_nonce_field( 'tta_delete_sample_data_action', 'tta_delete_sample_data_nonce' );
        echo '<p><input type="submit" name="tta_delete_sample_data" class="button button-secondary" value="Delete Sample Data"></p>';
        echo '</form>';

        echo '<div id="tta-authnet-test-wrapper">';
        echo '<p>';
        echo '<button id="tta-authnet-test-button" class="button button-secondary">Authorize.net testing</button>';
        echo '<span class="tta-admin-progress-spinner-div"><img class="tta-admin-progress-spinner-svg" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ) . '" alt="" style="display:none;"></span>';
        echo '</p>';
        echo '<p class="tta-admin-progress-response-p"></p>';
        echo '</div>';

        if ( isset( $_POST['tta_clear_log'] ) && check_admin_referer( 'tta_clear_log_action', 'tta_clear_log_nonce' ) ) {
            TTA_Debug_Logger::clear();
            echo '<div class="updated"><p>Debug log cleared.</p></div>';
        }

        echo '<h2>' . esc_html__( 'Debug Log', 'tta' ) . '</h2>';
        $log = TTA_Debug_Logger::get_messages();
        if ( $log ) {
            echo '<pre class="tta-debug-log" style="max-height:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:10px;">' . esc_html( implode( "\n", $log ) ) . '</pre>';
            echo '<form method="post">';
            wp_nonce_field( 'tta_clear_log_action', 'tta_clear_log_nonce' );
            echo '<p><input type="submit" name="tta_clear_log" class="button" value="Clear Log"></p>';
            echo '</form>';
        } else {
            echo '<p>' . esc_html__( 'No debug messages logged yet.', 'tta' ) . '</p>';
        }

        echo '</div>';
    }
}

TTA_Settings_Admin::get_instance();
