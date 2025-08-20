<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Utility class to log completed transactions to the database
 * and record member history entries.
 */
class TTA_Transaction_Logger {

    /**
     * Record a transaction along with its purchased items.
     *
     * @param string $transaction_id Authorize.Net transaction ID
     * @param float  $amount         Total charged amount
     * @param array  $items          Cart items from TTA_Cart::get_items()
     * @param string $discount_code  Discount code used at checkout
     * @param float  $discount_saved Total savings from discounts
     * @param int    $user_id        Optional WordPress user ID
     */
    public static function log( $transaction_id, $amount, array $items, $discount_code = '', $discount_saved = 0, $user_id = 0, $card_last4 = '' ) {
        global $wpdb;

        $user_id = $user_id ?: get_current_user_id();
        $members_table = $wpdb->prefix . 'tta_members';
        $history_table = $wpdb->prefix . 'tta_memberhistory';
        $txn_table     = $wpdb->prefix . 'tta_transactions';

        $member_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$members_table} WHERE wpuserid = %d LIMIT 1",
                $user_id
            )
        );

        // Save summary transaction row
        $wpdb->insert(
            $txn_table,
            [
                'wpuserid'       => $user_id,
                'member_id'      => $member_id,
                'transaction_id' => $transaction_id,
                'amount'         => $amount,
                'refunded'       => 0,
                'card_last4'     => sanitize_text_field( $card_last4 ),
                'discount_code'  => $discount_code,
                'discount_saved' => $discount_saved,
                'details'        => wp_json_encode( $items ),
            ],
            [ '%d', '%d', '%s', '%f', '%f', '%s', '%s', '%f', '%s' ]
        );

        $txn_id   = $wpdb->insert_id;
        $att_table = $wpdb->prefix . 'tta_attendees';
        foreach ( $items as $it ) {
            foreach ( (array) ( $it['attendees'] ?? [] ) as $att ) {
                $email      = sanitize_email( $att['email'] ?? '' );
                $is_member  = $email ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$members_table} WHERE email = %s", $email ) ) : 0;
                $wpdb->insert(
                    $att_table,
                    [
                        'transaction_id' => $txn_id,
                        'ticket_id'      => intval( $it['ticket_id'] ),
                        'first_name'     => sanitize_text_field( $att['first_name'] ?? '' ),
                        'last_name'      => sanitize_text_field( $att['last_name'] ?? '' ),
                        'email'          => $email,
                        'phone'          => sanitize_text_field( $att['phone'] ?? '' ),
                        'opt_in_sms'     => empty( $att['opt_in_sms'] ) ? 0 : 1,
                        'opt_in_email'   => empty( $att['opt_in_email'] ) ? 0 : 1,
                        'is_member'      => $is_member ? 1 : 0,
                    ],
                    [ '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d' ]
                );
            }
        }

        // Group items by event so each event gets its own history entry
        $by_event = [];
        foreach ( $items as $item ) {
            if ( empty( $item['event_ute_id'] ) ) {
                continue;
            }
            $by_event[ $item['event_ute_id'] ][] = $item;
        }

        foreach ( $by_event as $ute_id => $event_items ) {
            $event_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s LIMIT 1",
                    $ute_id
                )
            );

            // Calculate the total amount for these items
            $event_amount = 0;
            foreach ( $event_items as $it ) {
                $price  = isset( $it['final_price'] ) ? floatval( $it['final_price'] ) : floatval( $it['price'] );
                $qty    = intval( $it['quantity'] ?? 1 );
                $event_amount += $price * $qty;
            }

            $history_data = [
                'items'          => $event_items,
                'transaction_id' => $transaction_id,
                'discount_code'  => $discount_code,
                'discount_saved' => $discount_saved,
                'amount'         => $event_amount,
            ];

            // Record purchase for the logged in member
            $wpdb->insert(
                $history_table,
                [
                    'member_id'   => $member_id ?: 0,
                    'wpuserid'    => $user_id,
                    'event_id'    => intval( $event_id ),
                    'action_type' => 'purchase',
                    'action_data' => wp_json_encode( $history_data ),
                ],
                [ '%d', '%d', '%d', '%s', '%s' ]
            );
            // clear upcoming events cache for the purchaser
            TTA_Cache::delete( 'upcoming_events_' . $user_id );

            // Record history for any additional attendees who are members
            $unique_members = [];
            foreach ( $event_items as $it ) {
                foreach ( (array) ( $it['attendees'] ?? [] ) as $att ) {
                    $email = sanitize_email( $att['email'] ?? '' );
                    if ( ! $email ) {
                        continue;
                    }
                    $member_row = tta_get_member_row_by_email( $email );
                    if ( $member_row && intval( $member_row['wpuserid'] ) !== $user_id ) {
                        $unique_members[ $member_row['wpuserid'] ] = $member_row;
                    }
                }
            }

        foreach ( $unique_members as $m ) {
            $wpdb->insert(
                $history_table,
                [
                    'member_id'   => intval( $m['id'] ),
                    'wpuserid'    => intval( $m['wpuserid'] ),
                    'event_id'    => intval( $event_id ),
                    'action_type' => 'purchase',
                    'action_data' => wp_json_encode( $history_data ),
                ],
                [ '%d', '%d', '%d', '%s', '%s' ]
            );
            // clear upcoming events cache for this attendee member
            TTA_Cache::delete( 'upcoming_events_' . intval( $m['wpuserid'] ) );
        }
        }

        do_action( 'tta_after_purchase_logged', $items, $user_id );
    }
}
