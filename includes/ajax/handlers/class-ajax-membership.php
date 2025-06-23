<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Membership {
    public static function init() {
        add_action( 'wp_ajax_tta_add_membership', [ __CLASS__, 'ajax_add_membership' ] );
        add_action( 'wp_ajax_nopriv_tta_add_membership', [ __CLASS__, 'ajax_add_membership' ] );
    }

    public static function ajax_add_membership() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        $level = isset( $_POST['level'] ) ? sanitize_text_field( $_POST['level'] ) : '';
        if ( ! in_array( $level, [ 'basic', 'premium' ], true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid membership level.' ] );
        }
        if ( ! session_id() ) {
            session_start();
        }
        $_SESSION['tta_membership_purchase'] = $level;
        wp_send_json_success( [ 'cart_url' => home_url( '/cart' ) ] );
    }
}

TTA_Ajax_Membership::init();

