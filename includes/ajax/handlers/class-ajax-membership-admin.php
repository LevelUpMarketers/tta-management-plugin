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
        add_action( 'wp_ajax_tta_admin_assign_membership', [ __CLASS__, 'assign_membership' ] );
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

        $opaque_descriptor = sanitize_text_field( $_POST['opaqueData']['dataDescriptor'] ?? '' );
        $opaque_value      = sanitize_text_field( $_POST['opaqueData']['dataValue'] ?? '' );
        if ( ! $opaque_descriptor || ! $opaque_value ) {
            wp_send_json_error( [ 'message' => __( 'Encryption of the payment details failed. Please refresh and try again.', 'tta' ) ] );
        }

        $billing = [
            'first_name' => sanitize_text_field( $_POST['bill_first'] ?? '' ),
            'last_name'  => sanitize_text_field( $_POST['bill_last'] ?? '' ),
            'address'    => sanitize_text_field( $_POST['bill_address'] ?? '' ),
            'address2'   => sanitize_text_field( $_POST['bill_address2'] ?? '' ),
            'city'       => sanitize_text_field( $_POST['bill_city'] ?? '' ),
            'state'      => sanitize_text_field( $_POST['bill_state'] ?? '' ),
            'zip'        => sanitize_text_field( $_POST['bill_zip'] ?? '' ),
            'opaqueData' => [
                'dataDescriptor' => $opaque_descriptor,
                'dataValue'      => $opaque_value,
            ],
        ];

        $api = new TTA_AuthorizeNet_API();
        $profile_ids = self::get_payment_profile_ids( $api, $member['subscription_id'] );
        if ( is_wp_error( $profile_ids ) ) {
            wp_send_json_error( [ 'message' => $profile_ids->get_error_message() ] );
        }

        list( $profile_id, $payment_profile_id ) = $profile_ids;

        $res = $api->update_customer_payment_profile( $profile_id, $payment_profile_id, $billing );
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

        $level = strtolower( $member['membership_level'] );

        $api = new TTA_AuthorizeNet_API();
        $res = $api->cancel_subscription( $member['subscription_id'] );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        tta_update_user_membership_level( $member['wpuserid'], 'free', null, 'cancelled' );
        tta_update_user_subscription_status( $member['wpuserid'], 'cancelled' );
        tta_log_membership_cancellation( $member['wpuserid'], $level, 'admin' );
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

        $level       = sanitize_text_field( $_POST['level'] ?? $member['membership_level'] );
        $status      = strtolower( $member['subscription_status'] );
        $sub_id      = $member['subscription_id'];
        $use_current = ! empty( $_POST['use_current'] );
        $create_new  = ! empty( $_POST['create_new'] );
        $amount      = floatval( $_POST['amount'] ?? tta_get_membership_price( $level ) );
        $opaque_descriptor = sanitize_text_field( $_POST['opaqueData']['dataDescriptor'] ?? '' );
        $opaque_value      = sanitize_text_field( $_POST['opaqueData']['dataValue'] ?? '' );
        $last4             = substr( preg_replace( '/\D/', '', $_POST['last4'] ?? '' ), -4 );
        $has_token         = ( ! empty( $opaque_descriptor ) && ! empty( $opaque_value ) );

        $billing = $use_current ? [] : [
            'first_name' => sanitize_text_field( $_POST['bill_first'] ?? '' ),
            'last_name'  => sanitize_text_field( $_POST['bill_last'] ?? '' ),
            'address'    => sanitize_text_field( $_POST['bill_address'] ?? '' ),
            'address2'   => sanitize_text_field( $_POST['bill_address2'] ?? '' ),
            'city'       => sanitize_text_field( $_POST['bill_city'] ?? '' ),
            'state'      => sanitize_text_field( $_POST['bill_state'] ?? '' ),
            'zip'        => sanitize_text_field( $_POST['bill_zip'] ?? '' ),
        ];
        if ( ! $use_current && $has_token ) {
            $billing['opaqueData'] = [
                'dataDescriptor' => $opaque_descriptor,
                'dataValue'      => $opaque_value,
            ];
        }

        $api    = new TTA_AuthorizeNet_API();
        $new_id = $sub_id;

        if ( $use_current ) {
            if ( $sub_id && 'cancelled' !== $status ) {
                $res = $api->update_subscription_payment( $sub_id );
                if ( ! $res['success'] ) {
                    wp_send_json_error( [ 'message' => $res['error'] ] );
                }
            } else {
                wp_send_json_error( [ 'message' => __( 'Subscription cancelled or missing. Provide payment details to create a new subscription.', 'tta' ) ] );
            }
        } elseif ( $create_new ) {
            if ( ! $has_token ) {
                wp_send_json_error( [ 'message' => __( 'Payment details required.', 'tta' ) ] );
            }
            $charge = $api->charge( $amount, '', '', '', $billing );
            if ( ! $charge['success'] ) {
                wp_send_json_error( [ 'message' => $charge['error'] ] );
            }
            TTA_Transaction_Logger::log(
                $charge['transaction_id'],
                $amount,
                [
                    [
                        'membership'  => ucfirst( $level ) . ' Membership',
                        'quantity'    => 1,
                        'price'       => $amount,
                        'final_price' => $amount,
                    ],
                ],
                '',
                0,
                intval( $member['wpuserid'] ),
                $last4,
                ''
            );

            $sub = $api->create_subscription( $amount, '', '', '', $billing, ucfirst( $level ) . ' Membership', '', date( 'Y-m-d', strtotime( '+1 month' ) ) );
            if ( ! $sub['success'] ) {
                wp_send_json_error( [ 'message' => $sub['error'] ] );
            }
            $new_id = $sub['subscription_id'];
        } elseif ( $sub_id && 'cancelled' !== $status ) {
            if ( ! $has_token ) {
                wp_send_json_error( [ 'message' => __( 'Payment details required.', 'tta' ) ] );
            }
            $profile_ids = self::get_payment_profile_ids( $api, $sub_id );
            if ( is_wp_error( $profile_ids ) ) {
                wp_send_json_error( [ 'message' => $profile_ids->get_error_message() ] );
            }

            list( $profile_id, $payment_profile_id ) = $profile_ids;

            $res = $api->update_customer_payment_profile( $profile_id, $payment_profile_id, $billing );
            if ( ! $res['success'] ) {
                wp_send_json_error( [ 'message' => $res['error'] ] );
            }
        } else {
            if ( ! $has_token ) {
                wp_send_json_error( [ 'message' => __( 'Payment details required.', 'tta' ) ] );
            }
            $sub = $api->create_subscription( $amount, '', '', '', $billing, ucfirst( $level ) . ' Membership' );
            if ( ! $sub['success'] ) {
                wp_send_json_error( [ 'message' => $sub['error'] ] );
            }
            $new_id = $sub['subscription_id'];
        }

        tta_update_user_membership_level( $member['wpuserid'], $level, $new_id, 'active' );
        TTA_Cache::flush();

        if ( $create_new ) {
            $msg = __( 'Existing subscription cancelled and new subscription created.', 'tta' );
        } elseif ( $use_current ) {
            $msg = __( 'Reactivation attempted using stored info.', 'tta' );
        } else {
            $msg = __( 'Subscription reactivated.', 'tta' );
        }
        wp_send_json_success( [
            'message'        => $msg,
            'subscriptionId' => $new_id,
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

        $api      = new TTA_AuthorizeNet_API();
        $sub_name = ( 'premium' === $level ) ? TTA_PREMIUM_SUBSCRIPTION_NAME : TTA_BASIC_SUBSCRIPTION_NAME;
        $res      = $api->update_subscription_amount( $member['subscription_id'], $amount, $sub_name );
        if ( ! $res['success'] ) {
            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        tta_update_user_membership_level( $member['wpuserid'], $level );
        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Membership updated.', 'tta' ) ] );
    }

    public static function assign_membership() {
        self::verify_nonce();
        $member_id = intval( $_POST['member_id'] ?? 0 );
        $member    = self::get_member( $member_id );
        if ( ! $member ) {
            wp_send_json_error( [ 'message' => __( 'Member not found.', 'tta' ) ] );
        }

        $level  = sanitize_text_field( $_POST['level'] ?? '' );
        $amount = floatval( $_POST['amount'] ?? 0 );
        $opaque_descriptor = sanitize_text_field( $_POST['opaqueData']['dataDescriptor'] ?? '' );
        $opaque_value      = sanitize_text_field( $_POST['opaqueData']['dataValue'] ?? '' );
        $last4             = substr( preg_replace( '/\D/', '', $_POST['last4'] ?? '' ), -4 );

        if ( ! in_array( $level, [ 'basic', 'premium' ], true ) || $amount <= 0 || ! $opaque_descriptor || ! $opaque_value ) {
            wp_send_json_error( [ 'message' => __( 'Invalid details.', 'tta' ) ] );
        }

        $billing = [
            'first_name' => sanitize_text_field( $_POST['bill_first'] ?? '' ),
            'last_name'  => sanitize_text_field( $_POST['bill_last'] ?? '' ),
            'address'    => sanitize_text_field( $_POST['bill_address'] ?? '' ),
            'address2'   => sanitize_text_field( $_POST['bill_address2'] ?? '' ),
            'city'       => sanitize_text_field( $_POST['bill_city'] ?? '' ),
            'state'      => sanitize_text_field( $_POST['bill_state'] ?? '' ),
            'zip'        => sanitize_text_field( $_POST['bill_zip'] ?? '' ),
            'opaqueData' => [
                'dataDescriptor' => $opaque_descriptor,
                'dataValue'      => $opaque_value,
            ],
        ];

        $api = new TTA_AuthorizeNet_API();
        $charge = $api->charge( $amount, '', '', '', $billing );
        if ( ! $charge['success'] ) {
            wp_send_json_error( [ 'message' => $charge['error'] ] );
        }

        TTA_Transaction_Logger::log(
            $charge['transaction_id'],
            $amount,
            [
                [
                    'membership'  => ucfirst( $level ) . ' Membership',
                    'quantity'    => 1,
                    'price'       => $amount,
                    'final_price' => $amount,
                ],
            ],
            '',
            0,
            intval( $member['wpuserid'] ),
            $last4,
            ''
        );

        $sub = $api->create_subscription( $amount, '', '', '', $billing, ucfirst( $level ) . ' Membership', '', date( 'Y-m-d', strtotime( '+1 month' ) ) );
        if ( ! $sub['success'] ) {
            wp_send_json_error( [ 'message' => $sub['error'] ] );
        }

        tta_update_user_membership_level( $member['wpuserid'], $level, $sub['subscription_id'], 'active' );
        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Membership assigned.', 'tta' ) ] );
    }

    /**
     * Resolve the customer profile and payment profile IDs for an ARB subscription.
     *
     * @param TTA_AuthorizeNet_API $api             API client instance.
     * @param string               $subscription_id Authorize.Net subscription ID.
     * @return array|WP_Error { profile_id, payment_profile_id } or WP_Error on failure.
     */
    private static function get_payment_profile_ids( TTA_AuthorizeNet_API $api, $subscription_id ) {
        $details = $api->get_subscription_details( $subscription_id );
        if ( ! $details['success'] ) {
            return new WP_Error( 'tta_profile_lookup_failed', $details['error'] ?? __( 'Unable to load subscription details.', 'tta' ) );
        }

        $profile_id         = $details['profile_id'] ?? '';
        $payment_profile_id = $details['payment_profile_id'] ?? '';

        if ( ! $profile_id || ! $payment_profile_id ) {
            return new WP_Error( 'tta_profile_missing', __( 'Unable to locate the payment profile for this subscription.', 'tta' ) );
        }

        return [ $profile_id, $payment_profile_id ];
    }
}

TTA_Ajax_Membership_Admin::init();
