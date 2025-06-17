<?php
// includes/ajax/handlers/class-ajax-cart.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Cart {

    public static function init() {
        add_action( 'wp_ajax_tta_add_to_cart',      [ __CLASS__, 'ajax_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_tta_add_to_cart',[ __CLASS__, 'ajax_add_to_cart' ] );
        add_action( 'wp_ajax_tta_update_cart',      [ __CLASS__, 'ajax_update_cart' ] );
        add_action( 'wp_ajax_nopriv_tta_update_cart',[ __CLASS__, 'ajax_update_cart' ] );
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

        $existing = [];
        foreach ( $cart->get_items() as $row ) {
            $e = $row['event_ute_id'];
            $existing[ $e ] = ( $existing[ $e ] ?? 0 ) + intval( $row['quantity'] );
        }

        foreach ( $items as $it ) {
            $ticket_id = intval( $it['ticket_id'] );
            $qty       = intval( $it['quantity'] );

            $ticket = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT event_ute_id, baseeventcost, discountedmembercost, premiummembercost
                     FROM {$wpdb->prefix}tta_tickets
                     WHERE id = %d",
                    $ticket_id
                ),
                ARRAY_A
            );
            if ( ! $ticket ) {
                continue;
            }

            $event_ute = $ticket['event_ute_id'];
            $purchased = is_user_logged_in() ? tta_get_purchased_ticket_count( get_current_user_id(), $event_ute ) : 0;
            $allowed   = max( 0, 2 - $purchased - ( $existing[ $event_ute ] ?? 0 ) );
            if ( $qty > $allowed ) {
                $qty = $allowed;
            }

            if ( 'basic' === $membership_level ) {
                $price = floatval( $ticket['discountedmembercost'] );
            } elseif ( 'premium' === $membership_level ) {
                $price = floatval( $ticket['premiummembercost'] );
            } else {
                $price = floatval( $ticket['baseeventcost'] );
            }

            if ( $qty <= 0 ) {
                $cart->remove_item( $ticket_id );
            } else {
                $cart->add_item( $ticket_id, $qty, $price );
                $existing[ $event_ute ] = ( $existing[ $event_ute ] ?? 0 ) + $qty;
            }
        }

        // Always send users to the dedicated cart page
        $cart_url = home_url( '/cart' );

        wp_send_json_success( [ 'cart_url' => $cart_url ] );
    }

    public static function ajax_update_cart() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );

        $cart = new TTA_Cart();

        foreach ( (array) ( $_POST['cart_qty'] ?? [] ) as $ticket_id => $qty ) {
            $cart->update_quantity( intval( $ticket_id ), intval( $qty ) );
        }

        $_SESSION['tta_discount_code'] = sanitize_text_field( $_POST['discount_code'] ?? '' );

        $html = tta_render_cart_contents( $cart, $_SESSION['tta_discount_code'] );
        wp_send_json_success( [ 'html' => $html ] );
    }
}

// Initialize
TTA_Ajax_Cart::init();
