<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Payment {
    public static function init() {
        add_action( 'wp_ajax_tta_process_payment', [ __CLASS__, 'process_payment' ] );
        add_action( 'wp_ajax_nopriv_tta_process_payment', [ __CLASS__, 'process_payment' ] );
    }

    protected static function get_client_ip() {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''));
        if ( is_string( $ip ) ) {
            $ips = explode( ',', $ip );
            $ip = trim( $ips[0] );
        }
        return sanitize_text_field( $ip );
    }

    public static function process_payment() {
        $raw  = file_get_contents( 'php://input' );
        $data = json_decode( $raw, true );
        if ( ! is_array( $data ) ) {
            wp_send_json_error( [ 'error' => __( 'Invalid request', 'tta' ) ] );
        }
        if ( empty( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'tta_pay_nonce' ) ) {
            wp_send_json_error( [ 'error' => __( 'Security check failed', 'tta' ) ] );
        }

        $amount = isset( $data['amount'] ) ? number_format( (float) $data['amount'], 2, '.', '' ) : '0.00';
        $billing = isset( $data['billing'] ) && is_array( $data['billing'] ) ? $data['billing'] : [];
        $billing_clean = [
            'first_name' => tta_sanitize_text_field( $billing['first_name'] ?? '' ),
            'last_name'  => tta_sanitize_text_field( $billing['last_name'] ?? '' ),
            'email'      => sanitize_email( $billing['email'] ?? '' ),
            'address'    => tta_sanitize_text_field( $billing['address'] ?? '' ),
            'address2'   => tta_sanitize_text_field( $billing['address2'] ?? '' ),
            'city'       => tta_sanitize_text_field( $billing['city'] ?? '' ),
            'state'      => tta_sanitize_text_field( $billing['state'] ?? '' ),
            'zip'        => preg_replace( '/\D/', '', $billing['zip'] ?? '' ),
            'country'    => 'USA',
        ];

        if ( empty( $data['opaqueData'] ) || ! is_array( $data['opaqueData'] ) ) {
            wp_send_json_error( [ 'error' => __( 'Payment token missing', 'tta' ) ] );
        }

        $billing_clean['opaqueData'] = [
            'dataDescriptor' => sanitize_text_field( $data['opaqueData']['dataDescriptor'] ?? '' ),
            'dataValue'      => sanitize_text_field( $data['opaqueData']['dataValue'] ?? '' ),
        ];

        $billing_clean['invoice']     = substr( preg_replace( '/[^A-Za-z0-9]/', '', 'TAR-' . time() ), 0, 20 );
        $billing_clean['description'] = 'Trying to Adult RVA – Order';
        $billing_clean['ip']          = self::get_client_ip();

        $payments_service = new TTA_AuthorizeNet_API();
        $result = $payments_service->charge( $amount, '', '', '', $billing_clean );

        if ( $result['success'] ) {
            wp_send_json_success( [ 'transaction_id' => $result['transaction_id'] ] );
        }

        wp_send_json_error( [ 'error' => $result['error'] ?? __( 'Payment failed', 'tta' ) ] );
    }
}

TTA_Ajax_Payment::init();
