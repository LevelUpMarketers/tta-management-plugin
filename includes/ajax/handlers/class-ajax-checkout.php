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
        $changed = $cart->sync_with_inventory();
        if ( $changed ) {
            wp_send_json_error( [ 'message' => __( 'Some tickets in your cart were no longer available and have been removed. Please review the updated cart and try again.', 'tta' ) ] );
        }

        $amount = $cart->get_total( $discount_codes );

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
            'city'       => tta_sanitize_text_field( $_POST['billing_city'] ?? '' ),
            'state'      => tta_sanitize_text_field( $_POST['billing_state'] ?? '' ),
            'zip'        => tta_sanitize_text_field( $_POST['billing_zip'] ?? '' ),
        ];

        $api    = new TTA_AuthorizeNet_API();
        $result = $api->charge(
            $amount,
            preg_replace( '/\D/', '', $_POST['card_number'] ?? '' ),
            $exp_date,
            tta_sanitize_text_field( $_POST['card_cvc'] ?? '' ),
            $billing
        );

        if ( ! $result['success'] ) {
            wp_send_json_error( [ 'message' => $result['error'] ] );
        }

        $attendees = $_POST['attendees'] ?? [];
        $last4    = substr( preg_replace( '/\D/', '', $_POST['card_number'] ?? '' ), -4 );
        $res = $cart->finalize_purchase( $result['transaction_id'], $amount, $attendees, $last4 );
        if ( is_wp_error( $res ) ) {
            wp_send_json_error( [ 'message' => $res->get_error_message() ] );
        }

        wp_send_json_success( [ 'message' => __( 'Thank you for your purchase!', 'tta' ) ] );
    }
}

TTA_Ajax_Checkout::init();
