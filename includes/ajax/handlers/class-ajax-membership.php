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
        add_action( 'wp_ajax_tta_change_membership_level', [ __CLASS__, 'ajax_change_membership_level' ] );
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
            wp_send_json_error( [ 'message' => __( 'Whoops! Looks like you already have a Premium Membership!', 'tta' ) ] );
        }
        if ( 'basic' === $current_level && 'basic' === $level ) {
            wp_send_json_error( [ 'message' => __( 'Whoops! Looks like you already have a Basic Membership!', 'tta' ) ] );
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
        TTA_Email_Handler::get_instance()->send_membership_cancellation_email( $user_id, $level );
        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Subscription cancelled.', 'tta' ), 'status' => 'cancelled' ] );
    }

    public static function ajax_change_membership_level() {
        check_ajax_referer( 'tta_member_front_update', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'tta' ) ] );
        }

        $user_id = get_current_user_id();
        $context = tta_get_current_user_context();
        $current = strtolower( $context['membership_level'] ?? 'free' );
        $status  = strtolower( $context['subscription_status'] ?? '' );
        $sub_id  = $context['subscription_id'] ?? '';

        if ( 'active' !== $status || ! $sub_id ) {
            wp_send_json_error( [ 'message' => __( 'No active subscription found.', 'tta' ) ] );
        }

        $level = isset( $_POST['level'] ) ? sanitize_text_field( $_POST['level'] ) : '';
        if ( ! in_array( $level, [ 'basic', 'premium' ], true ) || $level === $current ) {
            wp_send_json_error( [ 'message' => __( 'Invalid membership level.', 'tta' ) ] );
        }

        $amount   = tta_get_membership_price( $level );
        $sub_name = ( 'premium' === $level ) ? TTA_PREMIUM_SUBSCRIPTION_NAME : TTA_BASIC_SUBSCRIPTION_NAME;
        $sub_desc = ( 'premium' === $level ) ? TTA_PREMIUM_SUBSCRIPTION_DESCRIPTION : TTA_BASIC_SUBSCRIPTION_DESCRIPTION;

        $api = new TTA_AuthorizeNet_API();
        $res = $api->update_subscription_amount( $sub_id, $amount, $sub_name, $sub_desc );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        tta_update_user_membership_level( $user_id, $level );
        TTA_Cache::flush();
        TTA_Email_Handler::get_instance()->send_membership_change_email( $user_id, $level );

        wp_send_json_success( [
            'message' => __( 'Membership updated.', 'tta' ),
            'level'   => $level,
            'label'   => tta_get_membership_label( $level ),
            'price'   => number_format( $amount, 2 ),
        ] );
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

        $opaque = [
            'dataDescriptor' => isset( $_POST['opaqueData']['dataDescriptor'] ) ? preg_replace( '/[^A-Za-z0-9._-]/', '', wp_unslash( $_POST['opaqueData']['dataDescriptor'] ) ) : '',
            'dataValue'      => isset( $_POST['opaqueData']['dataValue'] ) ? preg_replace( '/[^A-Za-z0-9+=\/._-]/', '', wp_unslash( $_POST['opaqueData']['dataValue'] ) ) : '',
        ];
        if ( empty( $opaque['dataDescriptor'] ) || empty( $opaque['dataValue'] ) ) {
            wp_send_json_error( [
                'message' => __( "Encryption of your payment information failed! Please try again later. If you're still having trouble, please contact us using the form on our Contact Page.", 'tta' ),
            ] );
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
        $billing['opaqueData'] = $opaque;

        $api  = new TTA_AuthorizeNet_API();
        $details = $api->get_subscription_details( $sub_id );
        if ( ! $details['success'] ) {
            wp_send_json_error( [ 'message' => $details['error'] ?? __( 'Unable to load your payment profile.', 'tta' ) ] );
        }

        $profile_id         = $details['profile_id'] ?? '';
        $payment_profile_id = $details['payment_profile_id'] ?? '';

        if ( ! $profile_id || ! $payment_profile_id ) {
            wp_send_json_error( [ 'message' => __( 'Unable to locate your payment profile. Please contact support.', 'tta' ) ] );
        }

        $res = $api->update_customer_payment_profile( $profile_id, $payment_profile_id, $billing );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        self::clear_subscription_cache( $sub_id, $user_id );

        $last4      = isset( $_POST['last4'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['last4'] ) ) : '';
        $last4_form = $last4 ? '**** ' . substr( $last4, -4 ) : '';

        $context = tta_get_current_user_context();
        $status  = strtolower( $context['subscription_status'] ?? '' );

        // Only attempt to charge again if the subscription is currently flagged for payment problems.
        if ( 'paymentproblem' === $status ) {
            $retry = $api->retry_subscription_charge( $sub_id );
            if ( $retry['success'] ) {
                $prev = get_user_meta( $user_id, 'tta_prev_level', true );
                if ( ! in_array( $prev, [ 'basic', 'premium' ], true ) ) {
                    $prev = 'basic';
                }
                tta_update_user_membership_level( $user_id, $prev, null, 'active' );
                delete_user_meta( $user_id, 'tta_prev_level' );
                tta_log_subscription_status_change( $user_id, 'active' );
                self::clear_subscription_cache( $sub_id, $user_id );
                wp_send_json_success( [
                    'message' => __( 'Payment method updated and charge successful.', 'tta' ),
                    'status'  => 'active',
                    'last4'   => $last4_form,
                ] );
            }

            tta_log_subscription_status_change( $user_id, 'paymentproblem' );
            wp_send_json_error( [ 'message' => sprintf( __( 'Payment profile updated but charge failed: %s', 'tta' ), $retry['error'] ) ] );
        }

        wp_send_json_success( [
            'message' => __( 'Your payment method has been successfully updated. Thanks for being proactive and keeping your membership current!', 'tta' ),
            'status'  => $status ?: 'active',
            'last4'   => $last4_form,
        ] );
    }

    /**
     * Clear cached subscription and member context entries after payment updates.
     *
     * @param string $subscription_id Authorize.Net subscription identifier.
     * @param int    $user_id         Related WordPress user ID.
     */
    private static function clear_subscription_cache( $subscription_id, $user_id = 0 ) {
        if ( $subscription_id ) {
            TTA_Cache::delete( 'sub_last4_' . $subscription_id );
            TTA_Cache::delete( 'sub_status_' . $subscription_id );
            TTA_Cache::delete( 'sub_tx_' . $subscription_id );
        }

        if ( $user_id ) {
            TTA_Cache::delete( 'member_row_' . intval( $user_id ) );
        }
    }
}

TTA_Ajax_Membership::init();

