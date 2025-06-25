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
    }

    public static function ajax_add_membership() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        $level = isset( $_POST['level'] ) ? sanitize_text_field( $_POST['level'] ) : '';
        if ( ! in_array( $level, [ 'basic', 'premium' ], true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid membership level.' ] );
        }
        $context = tta_get_current_user_context();
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

        $api   = new TTA_AuthorizeNet_API();
        $res   = $api->cancel_subscription( $sub_id );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        tta_update_user_membership_level( $user_id, 'free', null, 'cancelled' );
        tta_update_user_subscription_status( $user_id, 'cancelled' );
        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Subscription cancelled.', 'tta' ), 'status' => 'cancelled' ] );
    }
}

TTA_Ajax_Membership::init();

