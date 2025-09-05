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
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''));
        if ( is_string( $ip ) ) {
            $ips = explode( ',', $ip );
            $ip = trim( $ips[0] );
        }
        return sanitize_text_field( $ip );
    }

    public static function process_payment() {
        // Debugging disabled
        // $debug = [
        //     'stage'        => 'ajax_entry',
        //     'server_time'  => gmdate('c'),
        //     'php_version'  => PHP_VERSION,
        //     'wp_version'   => ( function_exists('get_bloginfo') ? get_bloginfo('version') : 'n/a' ),
        //     'server'       => [
        //         'REMOTE_ADDR'   => $_SERVER['REMOTE_ADDR'] ?? null,
        //         'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
         //         'HTTP_CF_CONNECTING_IP' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
        //         'REQUEST_URI'   => $_SERVER['REQUEST_URI'] ?? null,
        //         'HTTPS'         => isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null,
        //     ],
        // ];
        // error_log('in "process_payment"');

        $raw  = file_get_contents( 'php://input' );
        // $debug['raw_body'] = $raw;

        $data = json_decode( $raw, true );
        // $debug['decoded_json'] = $data;

        if ( ! is_array( $data ) ) {
            // error_log('invalid json in body');
            wp_send_json_error( [
                'error' => __( 'Invalid request', 'tta' ),
                // 'debug' => $debug,
            ], 400 );
        }

        $nonce_ok = ! empty( $data['_wpnonce'] ) && wp_verify_nonce( $data['_wpnonce'], 'tta_pay_nonce' );
        // $debug['nonce_ok'] = $nonce_ok;
        if ( ! $nonce_ok ) {
            // error_log('nonce check failed');
            wp_send_json_error( [
                'error' => __( 'Security check failed', 'tta' ),
                // 'debug' => $debug,
            ], 403 );
        }

        $amount  = isset( $data['amount'] ) ? number_format( (float) $data['amount'], 2, '.', '' ) : '0.00';
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

        // $debug['amount']          = $amount;
        // $debug['billing_received'] = $billing;
        // $debug['billing_clean']    = $billing_clean;

        if ( empty( $data['opaqueData'] ) || ! is_array( $data['opaqueData'] ) ) {
            // error_log('payment token missing');
            // $debug['opaque_present'] = false;
            wp_send_json_error( [
                'error' => __( 'Payment token missing', 'tta' ),
                // 'debug' => $debug,
            ], 422 );
        }
        // $debug['opaque_present'] = true;

        $billing_clean['opaqueData'] = [
            'dataDescriptor' => sanitize_text_field( $data['opaqueData']['dataDescriptor'] ?? '' ),
            'dataValue'      => sanitize_text_field( $data['opaqueData']['dataValue'] ?? '' ),
        ];

        $billing_clean['invoice']     = substr( preg_replace( '/[^A-Za-z0-9]/', '', 'TAR-' . time() ), 0, 20 );
        $billing_clean['description'] = tta_build_order_description();
        $billing_clean['ip']          = self::get_client_ip();

        // $debug['final_payload_to_charge'] = [
        //     'amount'  => $amount,
        //     'billing' => $billing_clean,
        // ];

        // Call gateway
        $payments_service = new TTA_AuthorizeNet_API();
        $result = $payments_service->charge( $amount, '', '', '', $billing_clean );

        // $debug['charge_result_raw'] = $result;

        if ( ! empty( $result['success'] ) ) {
            // error_log('charge success');
            wp_send_json_success( [
                'transaction_id' => $result['transaction_id'],
                // 'debug'          => $debug,
                // 'gateway'        => $result['debug'] ?? null, // pass through gateway debug if provided
            ] );
        }

        // error_log('charge failed');
        wp_send_json_error( [
            'error'   => $result['error'] ?? __( 'Payment failed', 'tta' ),
            // 'debug'   => $debug,
            // 'gateway' => $result['debug'] ?? null,
        ] );
    }
}

TTA_Ajax_Payment::init();
