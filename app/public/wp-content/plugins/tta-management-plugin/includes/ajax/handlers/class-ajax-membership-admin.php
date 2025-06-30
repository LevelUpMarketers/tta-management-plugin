<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Membership_Admin {
    public static function init() {
        add_action( 'wp_ajax_tta_admin_update_payment', [ __CLASS__, 'update_payment' ] );
        add_action( 'wp_ajax_tta_admin_cancel_subscription', [ __CLASS__, 'cancel_subscription' ] );
        add_action( 'wp_ajax_tta_admin_reactivate_subscription', [ __CLASS__, 'reactivate_subscription' ] );
        add_action( 'wp_ajax_tta_admin_change_level', [ __CLASS__, 'change_level' ] );
    }

    protected static function verify_nonce() {
        if ( ! current_user_can( 'manage_options' ) ||
             ! wp_verify_nonce( $_POST['nonce'] ?? '', 'tta_membership_admin_action' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized request.', 'tta' ) ] );
        }
    }

    protected static function get_member( $member_id ) {
        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$members_table} WHERE id=%d", $member_id ), ARRAY_A );
    }

    public static function update_payment() {
        self::verify_nonce();
        $member_id = intval( $_POST['member_id'] ?? 0 );
        $member    = self::get_member( $member_id );
        if ( ! $member || ! $member['subscription_id'] ) {
            wp_send_json_error( [ 'message' => __( 'No active subscription found.', 'tta' ) ] );
        }

        $card_number = preg_replace( '/\D/', '', $_POST['card_number'] ?? '' );
        $exp         = sanitize_text_field( $_POST['exp_date'] ?? '' );
        $cvc         = sanitize_text_field( $_POST['card_cvc'] ?? '' );
        if ( ! $card_number || ! $exp ) {
            wp_send_json_error( [ 'message' => __( 'Payment details incomplete.', 'tta' ) ] );
        }

        $billing = [
            'first_name' => sanitize_text_field( $_POST['bill_first'] ?? '' ),
            'last_name'  => sanitize_text_field( $_POST['bill_last'] ?? '' ),
            'address'    => sanitize_text_field( $_POST['bill_address'] ?? '' ),
            'city'       => sanitize_text_field( $_POST['bill_city'] ?? '' ),
            'state'      => sanitize_text_field( $_POST['bill_state'] ?? '' ),
            'zip'        => sanitize_text_field( $_POST['bill_zip'] ?? '' ),
        ];

        $api = new TTA_AuthorizeNet_API();
        $res = $api->update_subscription_payment( $member['subscription_id'], $card_number, $exp, $cvc, $billing );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        TTA_Cache::delete( 'sub_last4_' . $member['subscription_id'] );
        wp_send_json_success( [ 'message' => __( 'Payment method updated.', 'tta' ) ] );
    }

    public static function cancel_subscription() {
        self::verify_nonce();
        $member_id = intval( $_POST['member_id'] ?? 0 );
        $member    = self::get_member( $member_id );
        if ( ! $member || ! $member['subscription_id'] ) {
            wp_send_json_error( [ 'message' => __( 'No active subscription found.', 'tta' ) ] );
        }

        $api = new TTA_AuthorizeNet_API();
        $res = $api->cancel_subscription( $member['subscription_id'] );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        tta_update_user_membership_level( $member['wpuserid'], 'free', null, 'cancelled' );
        tta_update_user_subscription_status( $member['wpuserid'], 'cancelled' );
        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Subscription cancelled.', 'tta' ) ] );
    }

    public static function reactivate_subscription() {
        self::verify_nonce();
        $member_id = intval( $_POST['member_id'] ?? 0 );
        $member    = self::get_member( $member_id );
        if ( ! $member ) {
            wp_send_json_error( [ 'message' => __( 'Member not found.', 'tta' ) ] );
        }

        $level  = $member['membership_level'];
        $amount = floatval( $_POST['amount'] ?? tta_get_membership_price( $level ) );
        $card   = preg_replace( '/\D/', '', $_POST['card_number'] ?? '' );
        $exp    = sanitize_text_field( $_POST['exp_date'] ?? '' );
        $cvc    = sanitize_text_field( $_POST['card_cvc'] ?? '' );
        if ( ! $card || ! $exp ) {
            wp_send_json_error( [ 'message' => __( 'Payment details required.', 'tta' ) ] );
        }

        $billing = [
            'first_name' => sanitize_text_field( $_POST['bill_first'] ?? '' ),
            'last_name'  => sanitize_text_field( $_POST['bill_last'] ?? '' ),
            'address'    => sanitize_text_field( $_POST['bill_address'] ?? '' ),
            'city'       => sanitize_text_field( $_POST['bill_city'] ?? '' ),
            'state'      => sanitize_text_field( $_POST['bill_state'] ?? '' ),
            'zip'        => sanitize_text_field( $_POST['bill_zip'] ?? '' ),
        ];

        $api = new TTA_AuthorizeNet_API();
        $sub = $api->create_subscription( $amount, $card, $exp, $cvc, $billing, ucfirst( $level ) . ' Membership' );
        if ( ! $sub['success'] ) {
            wp_send_json_error( [ 'message' => $sub['error'] ] );
        }

        tta_update_user_membership_level( $member['wpuserid'], $level, $sub['subscription_id'], 'active' );
        TTA_Cache::flush();

        wp_send_json_success( [
            'message'        => __( 'Subscription reactivated.', 'tta' ),
            'subscriptionId' => $sub['subscription_id'],
            'resultCode'     => $sub['result_code'] ?? '',
            'messageCode'    => $sub['message_code'] ?? '',
            'messageText'    => $sub['message_text'] ?? '',
        ] );
    }

    public static function change_level() {
        self::verify_nonce();
        $member_id = intval( $_POST['member_id'] ?? 0 );
        $member    = self::get_member( $member_id );
        if ( ! $member || ! $member['subscription_id'] ) {
            wp_send_json_error( [ 'message' => __( 'Active subscription not found.', 'tta' ) ] );
        }

        $level  = sanitize_text_field( $_POST['level'] ?? '' );
        $amount = floatval( $_POST['price'] ?? 0 );
        if ( ! in_array( $level, [ 'basic', 'premium' ], true ) || $amount <= 0 ) {
            wp_send_json_error( [ 'message' => __( 'Invalid level or price.', 'tta' ) ] );
        }

        $api = new TTA_AuthorizeNet_API();
        $res = $api->update_subscription_amount( $member['subscription_id'], $amount );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        tta_update_user_membership_level( $member['wpuserid'], $level );
        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Membership updated.', 'tta' ) ] );
    }
}

TTA_Ajax_Membership_Admin::init();
