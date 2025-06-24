<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ads_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'Ads',
            'Ads',
            'manage_options',
            'tta-ads',
            [ $this, 'render_page' ],
            'dashicons-megaphone',
            11
        );
    }

    public function render_page() {
        $ads = get_option( 'tta_ads', [] );
        if ( isset( $_POST['tta_ads_nonce'] ) && wp_verify_nonce( $_POST['tta_ads_nonce'], 'tta_ads_save' ) ) {
            $new_ads = [];
            if ( isset( $_POST['ads'] ) && is_array( $_POST['ads'] ) ) {
                foreach ( $_POST['ads'] as $ad ) {
                    $id  = intval( $ad['image_id'] ?? 0 );
                    $url = esc_url_raw( $ad['url'] ?? '' );
                    if ( $id ) {
                        $new_ads[] = [ 'image_id' => $id, 'url' => $url ];
                    }
                }
            }
            update_option( 'tta_ads', $new_ads, false );
            TTA_Cache::delete( 'tta_ads_all' );
            $ads = $new_ads;
            echo '<div class="updated"><p>' . esc_html__( 'Ads saved.', 'tta' ) . '</p></div>';
        }
        include TTA_PLUGIN_DIR . 'includes/admin/views/ads-manage.php';
    }
}

TTA_Ads_Admin::get_instance();
