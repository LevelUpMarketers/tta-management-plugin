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
            if ( tta_user_is_banned( get_current_user_id() ) ) {
                wp_send_json_error( [ 'message' => __( 'You are currently banned from purchasing tickets.', 'tta' ) ] );
            }
        }

        $cart = new TTA_Cart();
        // Ensure a cart row exists before calculating existing quantities
        $cart->ensure_cart_exists();

        $existing_events  = [];
        $existing_tickets = [];
        foreach ( $cart->get_items() as $row ) {
            $e = $row['event_ute_id'];
            $existing_events[ $e ] = ( $existing_events[ $e ] ?? 0 ) + intval( $row['quantity'] );
            $existing_tickets[ intval( $row['ticket_id'] ) ] = intval( $row['quantity'] );
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

            $event_ute     = $ticket['event_ute_id'];
            $existing_qty  = $existing_tickets[ $ticket_id ] ?? 0;
            $event_total   = $existing_events[ $event_ute ] ?? 0;
            $purchased     = is_user_logged_in() ? tta_get_purchased_ticket_count( get_current_user_id(), $event_ute ) : 0;
            $allowed_total = max( 0, 2 - $purchased - $event_total );
            $diff          = $qty - $existing_qty;
            if ( $diff > 0 && $diff > $allowed_total ) {
                $qty  = $existing_qty + $allowed_total;
                $diff = $allowed_total;
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
                $existing_events[ $event_ute ] = ( $existing_events[ $event_ute ] ?? 0 ) + max( 0, $diff );
                $existing_tickets[ $ticket_id ] = $qty;
            }
        }

        // Always send users to the dedicated cart page
        $cart_url = home_url( '/cart' );

        wp_send_json_success( [ 'cart_url' => $cart_url ] );
    }

    public static function ajax_update_cart() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );

        $cart = new TTA_Cart();

        $notices = [];
        foreach ( (array) ( $_POST['cart_qty'] ?? [] ) as $ticket_id => $qty ) {
            $posted = intval( $qty );
            $final  = $cart->update_quantity( intval( $ticket_id ), $posted );
            if ( $final < $posted ) {
                $notices[ intval( $ticket_id ) ] = __( "We're sorry, there's a limit of two tickets total per event.", 'tta' );
            }
        }

        if ( ! isset( $_SESSION['tta_discount_codes'] ) ) {
            $_SESSION['tta_discount_codes'] = [];
        }

        $code_to_add    = tta_sanitize_text_field( $_POST['discount_code'] ?? '' );
        $code_to_remove = tta_sanitize_text_field( $_POST['remove_code'] ?? '' );

        if ( $code_to_remove ) {
            $_SESSION['tta_discount_codes'] = array_values( array_filter(
                $_SESSION['tta_discount_codes'],
                function ( $c ) use ( $code_to_remove ) {
                    return strcasecmp( $c, $code_to_remove ) !== 0;
                }
            ) );
            $message = __( 'Discount removed.', 'tta' );
        }

        $valid   = false;
        $message = '';
        if ( $code_to_add ) {
            foreach ( $cart->get_items() as $it ) {
                $info = tta_parse_discount_data( $it['discountcode'] );
                if ( $info['code'] && strcasecmp( $info['code'], $code_to_add ) === 0 ) {
                    $valid = true;
                    break;
                }
            }
            if ( $valid ) {
                $_SESSION['tta_discount_codes'][] = $code_to_add;
                $_SESSION['tta_discount_codes']   = array_unique( $_SESSION['tta_discount_codes'] );
                $message = __( 'Discount applied!', 'tta' );
            } else {
                $message = __( 'Invalid discount code.', 'tta' );
            }
        }

        $html     = tta_render_cart_contents( $cart, $_SESSION['tta_discount_codes'], $notices );
        $summary  = tta_render_checkout_summary( $cart, $_SESSION['tta_discount_codes'] );
        wp_send_json_success( [
            'html'    => $html,
            'summary' => $summary,
            'message' => $message,
        ] );
    }

}

// Initialize
TTA_Ajax_Cart::init();
