<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Refund_Processor {
    /** Schedule cron event. */
    public static function schedule_event() {
        if ( ! wp_next_scheduled( 'tta_refund_request_cron' ) ) {
            wp_schedule_event( time(), 'tta_ten_minutes', 'tta_refund_request_cron' );
        }
    }

    /** Clear cron event. */
    public static function clear_event() {
        wp_clear_scheduled_hook( 'tta_refund_request_cron' );
    }

    /**
     * Calculate the next expected settlement time for Authorize.Net batches.
     * Authorize.Net batches typically settle once daily around 3:00 AM in the
     * merchant's timezone. We schedule retries shortly after this time.
     *
     * @return int Timestamp for the next settlement window.
     */
    public static function get_next_settlement_time() {
        $now  = current_time( 'timestamp' );
        $next = strtotime( '3:15am', $now );
        if ( $next <= $now ) {
            $next = strtotime( 'tomorrow 3:15am', $now );
        }
        return $next;
    }

    /**
     * Schedule a refund retry for an unsettled transaction.
     *
     * @param string $gateway_tx_id Gateway transaction ID.
     * @param int    $ticket_id     Ticket ID.
     * @param int    $attendee_id   Attendee ID.
     * @param float  $amount        Refund amount.
     */
    public static function schedule_unsettled_refund( $gateway_tx_id, $ticket_id, $attendee_id, $amount ) {
        $time = self::get_next_settlement_time();
        wp_schedule_single_event( $time, 'tta_process_unsettled_refund', [ $gateway_tx_id, intval( $ticket_id ), intval( $attendee_id ), floatval( $amount ) ] );
    }

    /**
     * Attempt to process a refund that previously failed due to an unsettled transaction.
     * Reschedules itself if the transaction is still unsettled.
     *
     * @param string $gateway_tx_id Gateway transaction ID.
     * @param int    $ticket_id     Ticket ID.
     * @param int    $attendee_id   Attendee ID.
     * @param float  $amount        Refund amount.
     */
    public static function process_unsettled_refund( $gateway_tx_id, $ticket_id, $attendee_id, $amount ) {
        $req = tta_get_refund_request( $gateway_tx_id, $ticket_id, $attendee_id );
        if ( ! $req ) {
            return;
        }

        self::process_refund_request( $req, $amount );

        if ( tta_get_refund_request( $gateway_tx_id, $ticket_id, $attendee_id ) ) {
            // Still pending; try again after next settlement window.
            self::schedule_unsettled_refund( $gateway_tx_id, $ticket_id, $attendee_id, $amount );
        }
    }

    /** Initialize hooks. */
    public static function init() {
        add_action( 'tta_after_purchase_logged', [ __CLASS__, 'handle_purchase' ], 10, 2 );
        add_action( 'tta_refund_request_cron', [ __CLASS__, 'expire_requests' ] );
        add_action( 'tta_process_unsettled_refund', [ __CLASS__, 'process_unsettled_refund' ], 10, 4 );
        // Clear legacy retry hooks replaced by per-request scheduling.
        wp_clear_scheduled_hook( 'tta_refund_retry_morning' );
        wp_clear_scheduled_hook( 'tta_refund_retry_night' );
        self::schedule_event();
    }

    /**
     * Process pending refund requests when tickets are purchased.
     *
     * @param array $items   Items purchased.
     * @param int   $user_id Buyer user ID.
     */
    public static function handle_purchase( array $items, $user_id ) {
        $events = [];

        foreach ( $items as $it ) {
            $event_ute = $it['event_ute_id'] ?? '';
            if ( $event_ute ) {
                $events[ $event_ute ] = true;
            }
        }

        // First process any refund requests that can now be issued. This must
        // happen before releasing additional refund tickets so counts stay
        // accurate and purchased refund tickets are removed from the pool.
        self::retry_pending_requests();

        foreach ( array_keys( $events ) as $ute ) {
            tta_release_refund_tickets( $ute );
        }
    }

    /** Attempt to process pending refund requests. */
    public static function retry_pending_requests() {
        global $wpdb;
        $requests = tta_get_refund_requests();
        if ( ! $requests ) {
            return;
        }

        $grouped = [];
        foreach ( $requests as $req ) {
            $tid = intval( $req['ticket_id'] );
            $grouped[ $tid ][] = $req;
        }

        foreach ( $grouped as $tid => $list ) {
            $released = tta_get_released_refund_count( $tid );
            if ( $released <= 0 ) {
                continue;
            }

            $stock = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT ticketlimit FROM {$wpdb->prefix}tta_tickets WHERE id = %d",
                $tid
            ) );

            $sold_from_pool = max( 0, $released - $stock );

            $eligible = array_values( array_filter( $list, function( $req ) {
                return 'settlement' !== ( $req['pending_reason'] ?? '' );
            } ) );

            $to_refund = min( count( $eligible ), $sold_from_pool );
            for ( $i = 0; $i < $to_refund; $i++ ) {
                self::process_refund_request( $eligible[ $i ] );
            }
        }
    }

    /**
     * Process a single refund request.
     *
     * @param array $req Refund request data.
     */
    public static function process_refund_request( array $req, $amount_override = null ) {
        $tx = tta_get_transaction_by_gateway_id( $req['transaction_id'] );
        if ( ! $tx ) {
            tta_delete_refund_request( $req['transaction_id'], $req['ticket_id'], $req['attendee']['id'] ?? 0 );
            return;
        }

        $amount = null === $amount_override ? tta_get_ticket_price_from_transaction( $tx, $req['ticket_id'] ) : floatval( $amount_override );
        if ( $amount <= 0 ) {
            $amount = floatval( $tx['amount'] );
        }

        $api         = new TTA_AuthorizeNet_API();
        $status_res = $api->get_transaction_status( $tx['transaction_id'] );
        $res        = $api->refund( $amount, $tx['transaction_id'], $tx['card_last4'] );
        if ( ! $res['success'] ) {
            $msg = strtolower( $res['error'] );
            if ( false !== strpos( $msg, 'not meet the criteria' ) || false !== strpos( $msg, 'not settled' ) || false !== strpos( $msg, 'unsuccessful' ) || false !== strpos( strtolower( $status_res['status'] ?? '' ), 'pending' ) ) {
                global $wpdb;
                $hist_table = $wpdb->prefix . 'tta_memberhistory';
                if ( ! empty( $req['history_id'] ) ) {
                    $data = [
                        'transaction_id' => $req['transaction_id'],
                        'ticket_id'      => intval( $req['ticket_id'] ),
                        'reason'         => $req['reason'] ?? '',
                        'mode'           => $req['mode'] ?? 'cancel',
                        'pending_reason' => 'settlement',
                        'attendee'       => $req['attendee'],
                    ];
                    $wpdb->update(
                        $hist_table,
                        [ 'action_data' => wp_json_encode( $data ) ],
                        [ 'id' => intval( $req['history_id'] ) ],
                        [ '%s' ],
                        [ '%d' ]
                    );
                    TTA_Cache::delete( 'tta_refund_requests' );
                    tta_clear_pending_refund_cache( intval( $req['ticket_id'] ), intval( $req['event_id'] ) );
                }
                self::schedule_unsettled_refund( $req['transaction_id'], intval( $req['ticket_id'] ), intval( $req['attendee']['id'] ?? 0 ), $amount );
                tta_decrement_released_refund_count( intval( $req['ticket_id'] ) );
                return;
            }
        }

        if ( ! $res['success'] ) {
            return;
        }

        global $wpdb;
        $txn_table  = $wpdb->prefix . 'tta_transactions';
        $hist_table = $wpdb->prefix . 'tta_memberhistory';

        $wpdb->update(
            $txn_table,
            [ 'refunded' => floatval( $tx['refunded'] ) + $amount ],
            [ 'id' => intval( $tx['id'] ) ],
            [ '%f' ],
            [ '%d' ]
        );

        $attendee = $req['attendee'] ?? [];
        $reason   = sanitize_text_field( $req['reason'] ?? '' );
        $wpdb->insert(
            $hist_table,
            [
                'member_id'   => intval( $tx['member_id'] ),
                'wpuserid'    => intval( $tx['wpuserid'] ),
                'event_id'    => intval( $req['event_id'] ),
                'action_type' => 'refund',
                'action_data' => wp_json_encode([
                    'amount'         => $amount,
                    'transaction_id' => $tx['transaction_id'],
                    'ticket_id'      => intval( $req['ticket_id'] ),
                    'attendee_id'    => 0,
                    'cancel'         => 1,
                    'attendee'       => $attendee,
                    'reason'         => $reason,
                ]),
            ],
            [ '%d','%d','%d','%s','%s' ]
        );

        $ticket_info = tta_get_ticket_basic_info( $req['ticket_id'] );
        $refund_data = [
            'event_id'    => intval( $req['event_id'] ),
            'ticket_id'   => intval( $req['ticket_id'] ),
            'ticket_name' => $ticket_info['ticket_name'] ?? '',
            'attendee'    => $attendee,
            'amount'      => $amount,
            'first_name'  => $attendee['first_name'] ?? '',
            'last_name'   => $attendee['last_name'] ?? '',
            'email'       => $attendee['email'] ?? '',
        ];
        TTA_Email_Handler::get_instance()->send_refund_emails( $tx, $refund_data );
        TTA_SMS_Handler::get_instance()->send_refund_texts( $tx, $refund_data );

        tta_delete_refund_request( $req['transaction_id'], $req['ticket_id'], $req['attendee']['id'] ?? 0 );
        tta_decrement_released_refund_count( $req['ticket_id'] );
        TTA_Cache::flush();
    }

    /** Expire refund requests less than two hours before the event. */
    public static function expire_requests() {
        $requests = tta_get_refund_requests();
        $now = current_time( 'timestamp' );
        foreach ( $requests as $req ) {
            $ts = tta_get_event_start_timestamp( $req['event_id'] );
            if ( $ts && ( $ts - 7200 ) <= $now ) {
                tta_delete_refund_request( $req['transaction_id'], $req['ticket_id'] );
            }
        }
    }
}
