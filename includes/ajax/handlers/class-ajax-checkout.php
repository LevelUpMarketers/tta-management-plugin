<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Checkout {

    public static function init() {
        add_action( 'wp_ajax_tta_checkout', [ __CLASS__, 'checkout' ] );
        add_action( 'wp_ajax_nopriv_tta_checkout', [ __CLASS__, 'checkout' ] );
        add_action( 'wp_ajax_tta_checkout_status', [ __CLASS__, 'status' ] );
        add_action( 'wp_ajax_nopriv_tta_checkout_status', [ __CLASS__, 'status' ] );
    }

    protected static function get_client_ip() {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ( $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
        if ( is_string( $ip ) ) {
            $ips = explode( ',', $ip );
            $ip  = trim( $ips[0] );
        }
        return sanitize_text_field( $ip );
    }

    public static function status() {
        check_ajax_referer( 'tta_checkout_action', 'nonce' );
        $key = sanitize_text_field( $_POST['checkout_key'] ?? '' );
        if ( ! $key ) {
            wp_send_json_error( [ 'message' => __( 'Missing checkout key.', 'tta' ) ] );
        }
        global $wpdb;
        $txn_table = $wpdb->prefix . 'tta_transactions';
        $txn = $wpdb->get_var( $wpdb->prepare( "SELECT transaction_id FROM {$txn_table} WHERE checkout_key = %s LIMIT 1", $key ) );
        if ( $txn ) {
            wp_send_json_success( [ 'transaction_id' => $txn ] );
        }
        wp_send_json_success( [ 'transaction_id' => '' ] );
    }

    public static function checkout() {
        check_ajax_referer( 'tta_checkout_action', 'nonce' );

        $checkout_key = sanitize_text_field( $_POST['checkout_key'] ?? '' );
        if ( ! $checkout_key ) {
            wp_send_json_error( [ 'message' => __( 'Missing checkout key.', 'tta' ) ] );
        }

        global $wpdb;
        $txn_table = $wpdb->prefix . 'tta_transactions';
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$txn_table} (wpuserid, transaction_id, checkout_key, amount)
                 VALUES (%d, '', %s, 0)
                 ON DUPLICATE KEY UPDATE checkout_key = checkout_key",
                get_current_user_id(),
                $checkout_key
            )
        );
        if ( 0 === $wpdb->rows_affected ) {
            $existing = $wpdb->get_var( $wpdb->prepare( "SELECT transaction_id FROM {$txn_table} WHERE checkout_key = %s LIMIT 1", $checkout_key ) );
            if ( $existing ) {
                wp_send_json_success( [ 'transaction_id' => $existing ] );
            }
            wp_send_json_error( [ 'message' => __( 'Checkout already in progress.', 'tta' ) ] );
        }

        $cart            = new TTA_Cart();
        $discount_codes   = $_SESSION['tta_discount_codes'] ?? [];
        $ticket_total     = $cart->get_total( $discount_codes, false );
        $membership_level = $_SESSION['tta_membership_purchase'] ?? '';
        $membership_total = in_array( $membership_level, [ 'basic', 'premium', 'reentry' ], true ) ? tta_get_membership_price( $membership_level ) : 0;
        $amount           = $ticket_total + $membership_total;

        $billing = isset( $_POST['billing'] ) && is_array( $_POST['billing'] ) ? $_POST['billing'] : [];
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

        $opaque = isset( $_POST['opaqueData'] ) && is_array( $_POST['opaqueData'] ) ? $_POST['opaqueData'] : [];
        $has_token = ! empty( $opaque['dataDescriptor'] ) && ! empty( $opaque['dataValue'] );

        if ( $amount > 0 && ! $has_token ) {
            wp_send_json_error( [ 'message' => __( 'Payment token missing.', 'tta' ) ] );
        }

        if ( $has_token ) {
            $billing_clean['opaqueData'] = [
                'dataDescriptor' => sanitize_text_field( $opaque['dataDescriptor'] ),
                'dataValue'      => sanitize_text_field( $opaque['dataValue'] ),
            ];
        }

        $billing_clean['invoice']     = substr( preg_replace( '/[^A-Za-z0-9]/', '', $checkout_key ), 0, 20 );
        $billing_clean['description'] = tta_build_order_description();
        $billing_clean['ip']          = self::get_client_ip();

        $transaction_id = '';
        $last4 = substr( preg_replace( '/\D/', '', $_POST['last4'] ?? '' ), -4 );

        if ( $amount > 0 ) {
            $api = new TTA_AuthorizeNet_API();
            $res = $api->charge( $amount, '', '', '', $billing_clean );
            if ( empty( $res['success'] ) ) {
                $wpdb->delete( $txn_table, [ 'checkout_key' => $checkout_key ], [ '%s' ] );
                wp_send_json_error( [ 'message' => $res['error'] ?? __( 'Payment failed', 'tta' ) ] );
            }
            $transaction_id = $res['transaction_id'];
        }

        $flush_membership_cache = false;

        if ( $membership_total > 0 && $transaction_id ) {
            $membership_key = $ticket_total > 0 ? substr( $checkout_key, 0, 46 ) . '-m' : $checkout_key;
            TTA_Transaction_Logger::log(
                $transaction_id,
                $membership_total,
                [
                    [
                        'membership'  => tta_get_membership_label( $membership_level ),
                        'quantity'    => 1,
                        'price'       => $membership_total,
                        'final_price' => $membership_total,
                    ],
                ],
                '',
                0,
                get_current_user_id(),
                $last4,
                $membership_key
            );

            if ( 'reentry' === $membership_level ) {
                tta_clear_reinstatement_cron( get_current_user_id() );
                tta_unban_user( get_current_user_id() );
                tta_reset_no_show_offset( get_current_user_id() );
                tta_send_banned_reinstatement_email( get_current_user_id() );
                unset( $_SESSION['tta_membership_purchase'] );
                $flush_membership_cache = true;
            } else {
                $api = new TTA_AuthorizeNet_API();
                $existing_sub = tta_get_user_subscription_id( get_current_user_id() );
                if ( $existing_sub ) {
                    $api->cancel_subscription( $existing_sub );
                }
                $sub_name = ( 'premium' === $membership_level ) ? TTA_PREMIUM_SUBSCRIPTION_NAME : TTA_BASIC_SUBSCRIPTION_NAME;
                $sub_desc = ( 'premium' === $membership_level ) ? TTA_PREMIUM_SUBSCRIPTION_DESCRIPTION : TTA_BASIC_SUBSCRIPTION_DESCRIPTION;
                $sub      = $api->create_subscription_from_transaction( $transaction_id, $membership_total, $sub_name, $sub_desc, date( 'Y-m-d', strtotime( '+1 month' ) ) );
                if ( ! $sub['success'] ) {
                    wp_send_json_error( [ 'message' => $sub['error'] ] );
                }
                tta_update_user_membership_level( get_current_user_id(), $membership_level, $sub['subscription_id'], 'active' );
                $_SESSION['tta_checkout_sub'] = [ 'subscription_id' => $sub['subscription_id'] ];
                unset( $_SESSION['tta_membership_purchase'] );
                $flush_membership_cache = true;
            }
        }

        if ( $flush_membership_cache ) {
            TTA_Cache::flush();
        }

        $attendees   = $_POST['attendees'] ?? [];
        $has_tickets = ! empty( $attendees );
        if ( $has_tickets ) {
            $res = $cart->finalize_purchase( $transaction_id, $ticket_total, $attendees, $last4, $checkout_key );
            if ( is_wp_error( $res ) ) {
                wp_send_json_error( [ 'message' => $res->get_error_message() ] );
            }
        } else {
            $cart->empty_cart();
            unset( $_SESSION['tta_cart_session'], $_SESSION['tta_checkout_key'], $_SESSION['tta_discount_codes'] );
        }

        if ( $membership_total > 0 && in_array( $membership_level, [ 'basic', 'premium' ], true ) ) {
            TTA_Email_Handler::get_instance()->send_membership_purchase_email( get_current_user_id(), $membership_level );
        }

        $user   = wp_get_current_user();
        $emails = array_merge( [ $user->user_email ], tta_collect_attendee_emails( $attendees ) );
        $emails = array_filter( array_map( 'sanitize_email', $emails ) );
        $unique = [];
        foreach ( $emails as $email ) {
            $key = strtolower( $email );
            if ( ! isset( $unique[ $key ] ) ) {
                $unique[ $key ] = $email;
            }
        }
        $emails = array_values( $unique );
        $_SESSION['tta_checkout_emails']       = $emails;
        $_SESSION['tta_checkout_membership']   = $membership_total > 0 ? $membership_level : '';
        $_SESSION['tta_checkout_has_tickets']  = $has_tickets;

        $message = __( 'Thank you for your purchase!', 'tta' );
        wp_send_json_success(
            [
                'transaction_id' => $transaction_id,
                'message'        => $message,
                'emails'         => $emails,
                'membership'     => $_SESSION['tta_checkout_membership'],
                'has_tickets'    => $has_tickets,
            ]
        );
    }
}

TTA_Ajax_Checkout::init();
