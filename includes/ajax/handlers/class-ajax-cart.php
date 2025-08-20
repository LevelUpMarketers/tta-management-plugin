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
        add_action( 'wp_ajax_tta_check_stock',      [ __CLASS__, 'ajax_check_stock' ] );
        add_action( 'wp_ajax_nopriv_tta_check_stock',[ __CLASS__, 'ajax_check_stock' ] );
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
                $ban = tta_get_ban_message( get_current_user_id() );
                $msg = $ban['message'];
                if ( ! empty( $ban['button'] ) ) {
                    $url = add_query_arg( 'auto', 'reentry', home_url( '/checkout' ) );
                    $msg .= ' <a class="tta-alert-button" href="' . esc_url( $url ) . '">' . esc_html__( 'Purchase Re-entry Ticket', 'tta' ) . '</a>';
                }
                wp_send_json_error( [ 'message' => $msg ] );
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

        $added   = false;
        $message = '';
        foreach ( $items as $it ) {
            $ticket_id = intval( $it['ticket_id'] );
            $qty       = intval( $it['quantity'] );

            $ticket = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT event_ute_id, ticket_name, baseeventcost, discountedmembercost, premiummembercost, memberlimit
                     FROM {$wpdb->prefix}tta_tickets
                     WHERE id = %d",
                    $ticket_id
                ),
                ARRAY_A
            );
            if ( ! $ticket ) {
                continue;
            }

            $event_ute    = $ticket['event_ute_id'];
            $limit        = intval( $ticket['memberlimit'] );
            if ( $limit < 1 ) {
                $limit = 2;
            }
            $event_limit = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(memberlimit) FROM {$wpdb->prefix}tta_tickets WHERE event_ute_id = %s",
                    $event_ute
                )
            );
            if ( $event_limit < 1 ) {
                $event_limit = $limit;
            }

            $existing_qty   = $existing_tickets[ $ticket_id ] ?? 0;
            $event_existing = $existing_events[ $event_ute ] ?? 0;
            $purchased_ticket = is_user_logged_in() ? tta_get_purchased_ticket_count_for_ticket( get_current_user_id(), $ticket_id ) : 0;
            $purchased_event  = is_user_logged_in() ? tta_get_purchased_ticket_count( get_current_user_id(), $event_ute ) : 0;

            $ticket_allowed = max( 0, $limit - $purchased_ticket );
            $event_allowed  = max( 0, $event_limit - $purchased_event - ( $event_existing - $existing_qty ) );
            $allowed_total  = min( $ticket_allowed, $event_allowed );

            $diff = $qty - $existing_qty;
            if ( $diff > 0 && $qty > $allowed_total ) {
                $qty  = min( $allowed_total, $qty );
                $diff = $qty - $existing_qty;
                if ( $purchased_ticket >= $limit ) {
                    $message = sprintf( __( "We're sorry, there's a limit of %d per ticket. You've already purchased tickets in a previous transaction.", 'tta' ), $limit );
                } else {
                    $message = sprintf( __( "We're sorry, there's a limit of %d per ticket.", 'tta' ), $limit );
                }
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
                $new_qty = $cart->add_item( $ticket_id, $qty, $price );
                if ( $diff > 0 ) {
                    $added = true;
                    if ( $new_qty <= $existing_qty ) {
                        $event = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT name, waitlistavailable, page_id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s",
                                $event_ute
                            ),
                            ARRAY_A
                        );
                        if ( $event && ! empty( $event['waitlistavailable'] ) ) {
                            $ctx = tta_get_current_user_context();
                            tta_set_waitlist_context([
                                'event_ute_id' => $event_ute,
                                'event_name'   => $event['name'],
                                'page_id'      => intval( $event['page_id'] ),
                                'ticket_id'    => $ticket_id,
                                'ticket_name'  => $ticket['ticket_name'],
                                'first_name'   => $ctx['first_name'] ?? '',
                                'last_name'    => $ctx['last_name'] ?? '',
                                'email'        => $ctx['user_email'] ?? '',
                                'phone'        => $ctx['member']['phone'] ?? '',
                            ]);
                            $message = __( "We're sorry, but someone just purchased the last ticket. It's currently reserved in another member's cart.", 'tta' );
                        }
                    }
                }
                $existing_events[ $event_ute ] = ( $existing_events[ $event_ute ] ?? 0 ) + max( 0, $diff );
                $existing_tickets[ $ticket_id ] = $new_qty;
            }
        }

        $data = [];
        if ( $added ) {
            $data['cart_url'] = home_url( '/cart' );
        }
        if ( $message ) {
            $data['message'] = $message;
        }

        wp_send_json_success( $data );
    }

    /**
     * Return current ticket availability.
     */
    public static function ajax_check_stock() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        $ticket_id = intval( $_POST['ticket_id'] ?? 0 );
        if ( ! $ticket_id ) {
            wp_send_json_error( [ 'message' => 'missing_id' ] );
        }
        global $wpdb;
        $available = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ticketlimit FROM {$wpdb->prefix}tta_tickets WHERE id = %d",
                $ticket_id
            )
        );
        wp_send_json_success( [ 'available' => $available ] );
    }

    public static function ajax_update_cart() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );

        global $wpdb;

        $cart = new TTA_Cart();

        $notices = [];
        foreach ( (array) ( $_POST['cart_qty'] ?? [] ) as $ticket_id => $qty ) {
            $posted = intval( $qty );
            $final  = $cart->update_quantity( intval( $ticket_id ), $posted );
            if ( $final < $posted ) {
                $limit = (int) $wpdb->get_var( $wpdb->prepare( "SELECT memberlimit FROM {$wpdb->prefix}tta_tickets WHERE id = %d", $ticket_id ) );
                if ( $limit < 1 ) { $limit = 2; }
                $notices[ intval( $ticket_id ) ] = sprintf( __( "We're sorry, there's a limit of %d per ticket.", 'tta' ), $limit );
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
            $globals = tta_get_global_discount_codes();
            foreach ( $globals as $g ) {
                if ( $g['code'] && strcasecmp( $g['code'], $code_to_add ) === 0 ) {
                    $valid = true;
                    break;
                }
            }
            if ( ! $valid ) {
                foreach ( $cart->get_items() as $it ) {
                    $info = tta_parse_discount_data( $it['discountcode'] );
                    if ( $info['code'] && strcasecmp( $info['code'], $code_to_add ) === 0 ) {
                        $valid = true;
                        break;
                    }
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
