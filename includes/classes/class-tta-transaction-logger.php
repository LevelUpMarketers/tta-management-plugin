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
     * @param int    $user_id        Optional WordPress user ID
     */
    public static function log( $transaction_id, $amount, array $items, $user_id = 0 ) {
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
                'details'        => wp_json_encode( $items ),
            ],
            [ '%d', '%d', '%s', '%f', '%s' ]
        );

        // Record a history row per item for quick lookup by event
        foreach ( $items as $item ) {
            $event_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s",
                    $item['event_ute_id']
                )
            );

            $wpdb->insert(
                $history_table,
                [
                    'member_id'   => $member_id ?: 0,
                    'wpuserid'    => $user_id,
                    'event_id'    => intval( $event_id ),
                    'action_type' => 'purchase',
                    'action_data' => wp_json_encode([
                        'ticket_id'      => intval( $item['ticket_id'] ),
                        'ticket_name'    => $item['ticket_name'],
                        'quantity'       => intval( $item['quantity'] ),
                        'price'          => floatval( $item['price'] ),
                        'transaction_id' => $transaction_id,
                    ]),
                ],
                [ '%d', '%d', '%d', '%s', '%s' ]
            );
        }
    }
}
