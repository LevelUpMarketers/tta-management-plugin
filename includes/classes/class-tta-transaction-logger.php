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

        // Record a single history row for this transaction
        $event_id = 0;
        if ( ! empty( $items[0]['event_ute_id'] ) ) {
            $event_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s LIMIT 1",
                    $items[0]['event_ute_id']
                )
            );
        }

        $wpdb->insert(
            $history_table,
            [
                'member_id'   => $member_id ?: 0,
                'wpuserid'    => $user_id,
                'event_id'    => intval( $event_id ),
                'action_type' => 'purchase',
                'action_data' => wp_json_encode([
                    'items'          => $items,
                    'transaction_id' => $transaction_id,
                    'discount_code'  => $discount_code,
                    'discount_saved' => $discount_saved,
                    'amount'         => $amount,
                ]),
            ],
            [ '%d', '%d', '%d', '%s', '%s' ]
        );

        // Record history for any additional attendees who are members
        foreach ( $items as $it ) {
            foreach ( (array) ( $it['attendees'] ?? [] ) as $att ) {
                $email = sanitize_email( $att['email'] ?? '' );
                $member_row = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT id, wpuserid FROM {$members_table} WHERE email = %s LIMIT 1",
                        $email
                    ),
                    ARRAY_A
                );
                if ( $member_row && intval( $member_row['wpuserid'] ) !== $user_id ) {
                    $wpdb->insert(
                        $history_table,
                        [
                            'member_id'   => intval( $member_row['id'] ),
                            'wpuserid'    => intval( $member_row['wpuserid'] ),
                            'event_id'    => intval( $event_id ),
                            'action_type' => 'purchase',
                            'action_data' => wp_json_encode([
                                'items'          => $items,
                                'transaction_id' => $transaction_id,
                                'discount_code'  => $discount_code,
                                'discount_saved' => $discount_saved,
                                'amount'         => $amount,
                            ]),
                        ],
                        [ '%d', '%d', '%d', '%s', '%s' ]
                    );
                }
            }
        }
    }
}
