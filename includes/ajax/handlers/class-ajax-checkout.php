<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Checkout {

    public static function init() {
        add_action( 'wp_ajax_tta_do_checkout', [ __CLASS__, 'do_checkout' ] );
        add_action( 'wp_ajax_nopriv_tta_do_checkout', [ __CLASS__, 'do_checkout' ] );
    }

    public static function do_checkout() {
        check_ajax_referer( 'tta_checkout_action', 'nonce' );

        $cart = new TTA_Cart();
        $discount_codes = $_SESSION['tta_discount_codes'] ?? [];

        $ticket_total     = $cart->get_total( $discount_codes, false );
        $membership_level = $_SESSION['tta_membership_purchase'] ?? '';
        $membership_total = in_array( $membership_level, [ 'basic', 'premium', 'reentry' ], true ) ? tta_get_membership_price( $membership_level ) : 0;
        $amount           = $ticket_total + $membership_total;

        $exp_input  = tta_sanitize_text_field( $_POST['card_exp'] ?? '' );
        $digits     = preg_replace( '/\D/', '', $exp_input );
        if ( strlen( $digits ) !== 4 ) {
            wp_send_json_error( [ 'message' => __( 'Invalid expiration date format.', 'tta' ) ] );
        }
        $month = substr( $digits, 0, 2 );
        $year  = substr( $digits, 2, 2 );
        if ( (int) $month < 1 || (int) $month > 12 ) {
            wp_send_json_error( [ 'message' => __( 'Invalid expiration month.', 'tta' ) ] );
        }
        $exp_date = '20' . $year . '-' . $month;

        $billing = [
            'first_name' => tta_sanitize_text_field( $_POST['billing_first_name'] ?? '' ),
            'last_name'  => tta_sanitize_text_field( $_POST['billing_last_name'] ?? '' ),
            'address'    => tta_sanitize_text_field( $_POST['billing_street'] ?? '' ),
            'address2'   => tta_sanitize_text_field( $_POST['billing_street_2'] ?? '' ),
            'city'       => tta_sanitize_text_field( $_POST['billing_city'] ?? '' ),
            'state'      => tta_sanitize_text_field( $_POST['billing_state'] ?? '' ),
            'zip'        => tta_sanitize_text_field( $_POST['billing_zip'] ?? '' ),
        ];

        if ( empty( $billing['address'] ) || empty( $billing['city'] ) || empty( $billing['state'] ) || empty( $billing['zip'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Please complete all required billing address fields.', 'tta' ) ] );
        }

        $api = new TTA_AuthorizeNet_API();
        $transaction_id = '';
        if ( $membership_total > 0 ) {
            $charge = $api->charge(
                $membership_total,
                preg_replace( '/\D/', '', $_POST['card_number'] ?? '' ),
                $exp_date,
                tta_sanitize_text_field( $_POST['card_cvc'] ?? '' ),
                $billing
            );
            if ( ! $charge['success'] ) {
                wp_send_json_error( [ 'message' => $charge['error'] ] );
            }

            TTA_Transaction_Logger::log(
                $charge['transaction_id'],
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
                substr( preg_replace( '/\D/', '', $_POST['card_number'] ?? '' ), -4 )
            );

            if ( 'reentry' === $membership_level ) {
                tta_clear_reinstatement_cron( get_current_user_id() );
                tta_unban_user( get_current_user_id() );
                tta_send_banned_reinstatement_email( get_current_user_id() );
                unset( $_SESSION['tta_membership_purchase'] );
            } else {
                $existing_sub = tta_get_user_subscription_id( get_current_user_id() );
                if ( $existing_sub ) {
                    $api->cancel_subscription( $existing_sub );
                }
                $sub_name = ( 'premium' === $membership_level ) ? TTA_PREMIUM_SUBSCRIPTION_NAME : TTA_BASIC_SUBSCRIPTION_NAME;
                $sub_desc = ( 'premium' === $membership_level ) ? TTA_PREMIUM_SUBSCRIPTION_DESCRIPTION : TTA_BASIC_SUBSCRIPTION_DESCRIPTION;
                $sub      = $api->create_subscription(
                    $membership_total,
                    preg_replace( '/\D/', '', $_POST['card_number'] ?? '' ),
                    $exp_date,
                    tta_sanitize_text_field( $_POST['card_cvc'] ?? '' ),
                    $billing,
                    $sub_name,
                    $sub_desc,
                    date( 'Y-m-d', strtotime( '+1 month' ) )
                );
                if ( ! $sub['success'] ) {
                    wp_send_json_error( [ 'message' => $sub['error'] ] );
                }
                tta_update_user_membership_level( get_current_user_id(), $membership_level, $sub['subscription_id'], 'active' );
                $_SESSION['tta_checkout_sub'] = [
                    'subscription_id' => $sub['subscription_id'],
                    'result_code'     => $sub['result_code'] ?? '',
                    'message_code'    => $sub['message_code'] ?? '',
                    'message_text'    => $sub['message_text'] ?? '',
                ];
                unset( $_SESSION['tta_membership_purchase'] );
            }
        }

        if ( $ticket_total > 0 ) {
            $result = $api->charge(
                $ticket_total,
                preg_replace( '/\D/', '', $_POST['card_number'] ?? '' ),
                $exp_date,
                tta_sanitize_text_field( $_POST['card_cvc'] ?? '' ),
                $billing
            );
            if ( ! $result['success'] ) {
                wp_send_json_error( [ 'message' => $result['error'] ] );
            }
            $transaction_id = $result['transaction_id'];
        }

        $attendees   = $_POST['attendees'] ?? [];
        $last4       = substr( preg_replace( '/\D/', '', $_POST['card_number'] ?? '' ), -4 );
        $has_tickets = ! empty( $attendees );
        $res         = $cart->finalize_purchase( $transaction_id, $ticket_total, $attendees, $last4 );
        if ( is_wp_error( $res ) ) {
            wp_send_json_error( [ 'message' => $res->get_error_message() ] );
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
                'message'     => $message,
                'emails'      => $emails,
                'membership'  => $_SESSION['tta_checkout_membership'],
                'has_tickets' => $has_tickets,
            ]
        );
    }
}

TTA_Ajax_Checkout::init();
