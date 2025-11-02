<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Ajax_Authnet_Test {
    public static function init() {
        add_action( 'wp_ajax_tta_run_authnet_tests', [ __CLASS__, 'run_tests' ] );
    }

    protected static function request( $body, $cookies ) {
        $resp = wp_remote_post( admin_url( 'admin-ajax.php' ), [
            'body'    => $body,
            'timeout' => 20,
            'cookies' => $cookies,
        ] );
        if ( is_wp_error( $resp ) ) {
            return [ 'error' => $resp->get_error_message() ];
        }
        $data = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( null === $data ) {
            return [ 'error' => 'Invalid JSON response' ];
        }
        return $data;
    }

    public static function run_tests() {
        check_ajax_referer( 'tta_authnet_test_action', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied.' ] );
        }

        if ( ! session_id() ) {
            session_start();
        }
        $cookies = [ new WP_Http_Cookie( [
            'name'  => session_name(),
            'value' => session_id(),
        ] ) ];

        if ( function_exists( 'tta_log_payment_event' ) ) {
            tta_log_payment_event( 'Authorize.Net test suite started' );
        }

        $scenarios = [
            'single_ticket'           => [ 'tickets' => 1, 'membership' => false ],
            'multiple_tickets'       => [ 'tickets' => 2, 'membership' => false ],
            'membership_only'        => [ 'tickets' => 0, 'membership' => true ],
            'membership_plus_ticket' => [ 'tickets' => 1, 'membership' => true ],
        ];

        foreach ( $scenarios as $label => $config ) {
            self::run_scenario( $label, $config, $cookies );
            sleep( 2 );
        }

        if ( function_exists( 'tta_log_payment_event' ) ) {
            tta_log_payment_event( 'Authorize.Net test suite finished' );
        }
        wp_send_json_success( [ 'message' => 'Test run complete. Check debug log.' ] );
    }

    protected static function run_scenario( $label, $config, $cookies ) {
        if ( function_exists( 'tta_log_payment_event' ) ) {
            tta_log_payment_event( 'Authorize.Net test scenario started', [ 'scenario' => $label, 'config' => $config ] );
        }

        $event = tta_get_next_event();
        if ( ! $event ) {
            if ( function_exists( 'tta_log_payment_event' ) ) {
                tta_log_payment_event( 'Authorize.Net test scenario aborted: no upcoming events', [ 'scenario' => $label ], 'warning' );
            }
            return;
        }

        global $wpdb;
        $ute  = $wpdb->get_var( $wpdb->prepare( "SELECT ute_id FROM {$wpdb->prefix}tta_events WHERE id = %d", $event['id'] ) );
        $ticket_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tta_tickets WHERE event_ute_id = %s LIMIT 1", $ute ) );
        if ( ! $ticket_id ) {
            if ( function_exists( 'tta_log_payment_event' ) ) {
                tta_log_payment_event( 'Authorize.Net test scenario aborted: no ticket found', [ 'scenario' => $label, 'event_id' => $event['id'] ], 'warning' );
            }
            return;
        }

        if ( $config['tickets'] > 0 ) {
            $res = self::request( [
                'action' => 'tta_add_to_cart',
                'nonce'  => wp_create_nonce( 'tta_frontend_nonce' ),
                'items'  => wp_json_encode( [ [ 'ticket_id' => $ticket_id, 'quantity' => $config['tickets'] ] ] ),
            ], $cookies );
            if ( empty( $res['success'] ) ) {
                $msg = $res['data']['message'] ?? ( $res['error'] ?? 'Add to cart error' );
                if ( function_exists( 'tta_log_payment_event' ) ) {
                    tta_log_payment_event( 'Authorize.Net test scenario add-to-cart failed', [ 'scenario' => $label, 'message' => $msg ], 'error' );
                }
                return;
            }
            if ( function_exists( 'tta_log_payment_event' ) ) {
                tta_log_payment_event( 'Authorize.Net test scenario added tickets', [ 'scenario' => $label, 'tickets' => $config['tickets'] ] );
            }
        }

        if ( $config['membership'] ) {
            $res = self::request( [
                'action' => 'tta_add_membership',
                'nonce'  => wp_create_nonce( 'tta_frontend_nonce' ),
                'level'  => 'basic',
            ], $cookies );
            if ( empty( $res['success'] ) ) {
                $msg = $res['data']['message'] ?? ( $res['error'] ?? 'Membership error' );
                if ( function_exists( 'tta_log_payment_event' ) ) {
                    tta_log_payment_event( 'Authorize.Net test scenario membership add failed', [ 'scenario' => $label, 'message' => $msg ], 'error' );
                }
                return;
            }
            if ( function_exists( 'tta_log_payment_event' ) ) {
                tta_log_payment_event( 'Authorize.Net test scenario added membership', [ 'scenario' => $label ] );
            }
        }

        $member = tta_get_sample_member();
        $res = self::request( [
            'action'              => 'tta_do_checkout',
            'nonce'               => wp_create_nonce( 'tta_checkout_action' ),
            'card_number'         => '4111111111111111',
            'card_exp'            => '12/39',
            'card_cvc'            => '123',
            'billing_first_name'  => $member['first_name'],
            'billing_last_name'   => $member['last_name'],
            'billing_street'      => '123 Main St',
            'billing_city'        => 'Anytown',
            'billing_state'       => 'VA',
            'billing_zip'         => '12345',
        ], $cookies );
        if ( empty( $res['success'] ) ) {
            $msg = $res['data']['message'] ?? ( $res['error'] ?? 'Checkout error' );
            if ( function_exists( 'tta_log_payment_event' ) ) {
                tta_log_payment_event( 'Authorize.Net test scenario checkout failed', [ 'scenario' => $label, 'message' => $msg ], 'error' );
            }
            return;
        }
        if ( function_exists( 'tta_log_payment_event' ) ) {
            tta_log_payment_event( 'Authorize.Net test scenario checkout succeeded', [ 'scenario' => $label ] );
        }
    }
}

TTA_Ajax_Authnet_Test::init();
