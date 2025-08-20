<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Membership {
    public static function init() {
        add_action( 'wp_ajax_tta_add_membership', [ __CLASS__, 'ajax_add_membership' ] );
        add_action( 'wp_ajax_nopriv_tta_add_membership', [ __CLASS__, 'ajax_add_membership' ] );
        add_action( 'wp_ajax_tta_remove_membership', [ __CLASS__, 'ajax_remove_membership' ] );
        add_action( 'wp_ajax_nopriv_tta_remove_membership', [ __CLASS__, 'ajax_remove_membership' ] );
        add_action( 'wp_ajax_tta_cancel_membership', [ __CLASS__, 'ajax_cancel_membership' ] );
        add_action( 'wp_ajax_tta_update_payment', [ __CLASS__, 'ajax_update_payment' ] );
    }

    public static function ajax_add_membership() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        $level = isset( $_POST['level'] ) ? sanitize_text_field( $_POST['level'] ) : '';
        if ( ! in_array( $level, [ 'basic', 'premium' ], true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid membership level.' ] );
        }
        $context = tta_get_current_user_context();
        if ( $context['member'] && tta_user_is_banned( $context['wp_user_id'] ) ) {
            $ban = tta_get_ban_message( $context['wp_user_id'] );
            $msg = $ban['message'];
            if ( ! empty( $ban['button'] ) ) {
                $url = add_query_arg( 'auto', 'reentry', home_url( '/checkout' ) );
                $msg .= ' <a class="tta-alert-button" href="' . esc_url( $url ) . '">' . esc_html__( 'Purchase Re-entry Ticket', 'tta' ) . '</a>';
            }
            wp_send_json_error( [ 'message' => $msg ] );
        }
        $current_level = strtolower( $context['membership_level'] );
        if ( 'premium' === $current_level ) {
            wp_send_json_error( [ 'message' => __( 'You already have a Premium Membership.', 'tta' ) ] );
        }
        if ( 'basic' === $current_level && 'basic' === $level ) {
            wp_send_json_error( [ 'message' => __( 'You already have a Basic Membership.', 'tta' ) ] );
        }
        if ( ! session_id() ) {
            session_start();
        }
        $_SESSION['tta_membership_purchase'] = $level;
        wp_send_json_success( [ 'cart_url' => home_url( '/cart' ) ] );
    }

    public static function ajax_remove_membership() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        if ( ! session_id() ) {
            session_start();
        }
        unset( $_SESSION['tta_membership_purchase'] );
        wp_send_json_success();
    }

    public static function ajax_cancel_membership() {
        check_ajax_referer( 'tta_member_front_update', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'tta' ) ] );
        }

        $user_id = get_current_user_id();
        $sub_id  = tta_get_user_subscription_id( $user_id );
        if ( ! $sub_id ) {
            wp_send_json_error( [ 'message' => __( 'No active subscription found.', 'tta' ) ] );
        }

        $context = tta_get_current_user_context();
        $level   = strtolower( $context['membership_level'] ?? 'free' );

        $api   = new TTA_AuthorizeNet_API();
        $res   = $api->cancel_subscription( $sub_id );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        tta_update_user_membership_level( $user_id, 'free', null, 'cancelled' );
        tta_update_user_subscription_status( $user_id, 'cancelled' );
        tta_log_membership_cancellation( $user_id, $level, 'member' );
        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Subscription cancelled.', 'tta' ), 'status' => 'cancelled' ] );
    }

    public static function ajax_update_payment() {
        check_ajax_referer( 'tta_member_front_update', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'tta' ) ] );
        }

        $user_id = get_current_user_id();
        $sub_id  = tta_get_user_subscription_id( $user_id );
        if ( ! $sub_id ) {
            wp_send_json_error( [ 'message' => __( 'No active subscription found.', 'tta' ) ] );
        }

        $card_number = isset( $_POST['card_number'] ) ? preg_replace( '/\D/', '', $_POST['card_number'] ) : '';
        $exp         = isset( $_POST['exp_date'] ) ? sanitize_text_field( $_POST['exp_date'] ) : '';
        $cvc         = isset( $_POST['card_cvc'] ) ? sanitize_text_field( $_POST['card_cvc'] ) : '';
        if ( ! $card_number || ! $exp ) {
            wp_send_json_error( [ 'message' => __( 'Payment details incomplete.', 'tta' ) ] );
        }

        $billing = [
            'first_name' => sanitize_text_field( $_POST['bill_first'] ?? '' ),
            'last_name'  => sanitize_text_field( $_POST['bill_last'] ?? '' ),
            'address'    => sanitize_text_field( $_POST['bill_address'] ?? '' ),
            'address2'   => sanitize_text_field( $_POST['bill_address2'] ?? '' ),
            'city'       => sanitize_text_field( $_POST['bill_city'] ?? '' ),
            'state'      => sanitize_text_field( $_POST['bill_state'] ?? '' ),
            'zip'        => sanitize_text_field( $_POST['bill_zip'] ?? '' ),
        ];

        $api  = new TTA_AuthorizeNet_API();
        $res  = $api->update_subscription_payment( $sub_id, $card_number, $exp, $cvc, $billing );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        TTA_Cache::delete( 'sub_last4_' . $sub_id );

        // Attempt to retry the failed charge immediately.
        $retry = $api->retry_subscription_charge( $sub_id );
        if ( $retry['success'] ) {
            $prev = get_user_meta( $user_id, 'tta_prev_level', true );
            if ( ! in_array( $prev, [ 'basic', 'premium' ], true ) ) {
                $prev = 'basic';
            }
            tta_update_user_membership_level( $user_id, $prev, null, 'active' );
            delete_user_meta( $user_id, 'tta_prev_level' );
            tta_log_subscription_status_change( $user_id, 'active' );
            wp_send_json_success( [
                'message' => __( 'Payment method updated and charge successful.', 'tta' ),
                'status'  => 'active',
            ] );
        }

        tta_log_subscription_status_change( $user_id, 'paymentproblem' );
        wp_send_json_error( [ 'message' => sprintf( __( 'Payment profile updated but charge failed: %s', 'tta' ), $retry['error'] ) ] );
    }
}

TTA_Ajax_Membership::init();

