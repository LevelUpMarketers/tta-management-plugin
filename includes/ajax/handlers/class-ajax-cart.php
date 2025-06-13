<?php
// includes/ajax/handlers/class-ajax-cart.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Cart {

    public static function init() {
        add_action( 'wp_ajax_tta_add_to_cart',      [ __CLASS__, 'ajax_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_tta_add_to_cart',[ __CLASS__, 'ajax_add_to_cart' ] );
    }

    public static function ajax_add_to_cart() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        global $wpdb;

        $items = json_decode( stripslashes( $_POST['items'] ?? '[]' ), true );
        if ( ! is_array( $items ) || empty( $items ) ) {
            wp_send_json_error( [ 'message' => 'No items to add.' ] );
        }

        // Determine membership level
        $membership_level = 'free';
        if ( is_user_logged_in() ) {
            $lvl = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT membership_level FROM {$wpdb->prefix}tta_members WHERE wpuserid = %d",
                    get_current_user_id()
                )
            );
            if ( in_array( $lvl, [ 'basic', 'premium' ], true ) ) {
                $membership_level = $lvl;
            }
        }

        $cart = new TTA_Cart();

        foreach ( $items as $it ) {
            $ticket_id = intval( $it['ticket_id'] );
            $qty       = max( 1, intval( $it['quantity'] ) );

            $ticket = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT baseeventcost, discountedmembercost, premiummembercost
                     FROM {$wpdb->prefix}tta_tickets
                     WHERE id = %d",
                    $ticket_id
                ),
                ARRAY_A
            );
            if ( ! $ticket ) {
                continue;
            }

            if ( 'basic' === $membership_level ) {
                $price = floatval( $ticket['discountedmembercost'] );
            } elseif ( 'premium' === $membership_level ) {
                $price = floatval( $ticket['premiummembercost'] );
            } else {
                $price = floatval( $ticket['baseeventcost'] );
            }

            $cart->add_item( $ticket_id, $qty, $price );
        }

        // Always send users to the dedicated cart page
        $cart_url = home_url( '/cart' );

        wp_send_json_success( [ 'cart_url' => $cart_url ] );
    }
}

// Initialize
TTA_Ajax_Cart::init();
