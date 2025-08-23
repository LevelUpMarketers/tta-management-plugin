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
        $discount_codes   = $_SESSION['tta_discount_codes'] ?? [];

        $ticket_total     = $cart->get_total( $discount_codes, false );
        $membership_level = $_SESSION['tta_membership_purchase'] ?? '';
        $membership_total = in_array( $membership_level, [ 'basic', 'premium', 'reentry' ], true ) ? tta_get_membership_price( $membership_level ) : 0;
        $amount           = $ticket_total + $membership_total;

        $transaction_id = tta_sanitize_text_field( $_POST['transaction_id'] ?? '' );
        $last4          = substr( preg_replace( '/\D/', '', $_POST['last4'] ?? '' ), -4 );

        if ( $amount > 0 && empty( $transaction_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Missing transaction ID.', 'tta' ) ] );
        }

        if ( $membership_total > 0 && $transaction_id ) {
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
                $last4
            );

            if ( 'reentry' === $membership_level ) {
                tta_clear_reinstatement_cron( get_current_user_id() );
                tta_unban_user( get_current_user_id() );
                tta_send_banned_reinstatement_email( get_current_user_id() );
                unset( $_SESSION['tta_membership_purchase'] );
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
                $_SESSION['tta_checkout_sub'] = [
                    'subscription_id' => $sub['subscription_id'],
                ];
                unset( $_SESSION['tta_membership_purchase'] );
            }
        }

        $attendees   = $_POST['attendees'] ?? [];
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
