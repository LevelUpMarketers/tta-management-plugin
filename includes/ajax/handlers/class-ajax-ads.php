<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Ads {
    public static function init() {
        add_action( 'wp_ajax_tta_get_ad_form', [ __CLASS__, 'get_ad_form' ] );
        add_action( 'wp_ajax_tta_update_ad', [ __CLASS__, 'update_ad' ] );
    }

    public static function get_ad_form() {
        check_ajax_referer( 'tta_ad_get_action', 'get_ad_nonce' );
        if ( ! isset( $_POST['ad_id'] ) || '' === $_POST['ad_id'] ) {
            wp_send_json_error( [ 'message' => 'Missing ID' ] );
        }
        $_GET['ad_id'] = intval( $_POST['ad_id'] );
        ob_start();
        include TTA_PLUGIN_DIR . 'includes/admin/views/ads-edit.php';
        $html = ob_get_clean();
        wp_send_json_success( [ 'html' => $html ] );
    }

    public static function update_ad() {
        check_ajax_referer( 'tta_ad_save_action', 'tta_ad_save_nonce' );
        if ( ! isset( $_POST['ad_id'] ) || '' === $_POST['ad_id'] ) {
            wp_send_json_error( [ 'message' => 'Missing ID' ] );
        }

        $ads = get_option( 'tta_ads', [] );
        $idx = intval( $_POST['ad_id'] );
        if ( ! isset( $ads[ $idx ] ) ) {
            wp_send_json_error( [ 'message' => 'Ad not found' ] );
        }

        $url = sanitize_text_field( $_POST['url'] ?? '' );
        if ( $url && ! preg_match( '#^https?://#i', $url ) ) {
            $url = 'https://' . $url;
        }
        $host = parse_url( $url, PHP_URL_HOST );
        if ( ! $host || ! preg_match( '/\.[a-z]{2,}$/i', $host ) ) {
            wp_send_json_error( [ 'message' => 'Invalid URL' ] );
        }

        $ads[ $idx ] = [
            'image_id'        => intval( $_POST['image_id'] ?? 0 ),
            'url'             => esc_url_raw( $url ),
            'business_name'   => sanitize_text_field( $_POST['business_name'] ?? '' ),
            'business_phone'  => sanitize_text_field( $_POST['business_phone'] ?? '' ),
            'business_address'=> sanitize_text_field( $_POST['business_address'] ?? '' ),
        ];
        update_option( 'tta_ads', array_values( $ads ), false );
        TTA_Cache::delete( 'tta_ads_all' );
        wp_send_json_success( [ 'message' => 'Ad updated!' ] );
    }
}

TTA_Ajax_Ads::init();

