<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Ajax_Attendance {
    public static function init() {
        add_action( 'wp_ajax_tta_get_event_attendance', [ __CLASS__, 'get_event_attendance' ] );
        add_action( 'wp_ajax_tta_set_attendance', [ __CLASS__, 'set_attendance' ] );
        add_action( 'wp_ajax_tta_remove_attendee', [ __CLASS__, 'remove_attendee' ] );
        add_action( 'wp_ajax_tta_refund_attendee', [ __CLASS__, 'refund_attendee' ] );
        add_action( 'wp_ajax_tta_cancel_attendance', [ __CLASS__, 'cancel_attendance' ] );
    }

    public static function get_event_attendance() {
        check_ajax_referer( 'tta_get_attendance_action', 'nonce' );
        $ute = tta_sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        if ( ! $ute ) {
            wp_send_json_error( [ 'message' => 'missing id' ] );
        }
        $event = tta_get_event_for_email( $ute );
        if ( ! $event ) {
            wp_send_json_error( [ 'message' => 'not found' ] );
        }
        $attendees = tta_get_event_attendees_with_status( $ute );
        ob_start();
        $GLOBALS['event'] = $event;
        $GLOBALS['attendees'] = $attendees;
        include TTA_PLUGIN_DIR . 'includes/frontend/views/attendance-list.php';
        $html = ob_get_clean();
        wp_send_json_success( [ 'html' => $html ] );
    }

    public static function set_attendance() {
        check_ajax_referer( 'tta_set_attendance_action', 'nonce' );
        $att_id = intval( $_POST['attendee_id'] ?? 0 );
        $status = sanitize_text_field( $_POST['status'] ?? 'pending' );
        if ( ! $att_id ) {
            wp_send_json_error( [ 'message' => 'missing attendee' ] );
        }
        tta_set_attendance_status( $att_id, $status );
        wp_send_json_success();
    }

    public static function remove_attendee() {
        check_ajax_referer( 'tta_attendee_admin_action', 'nonce' );
        $id = intval( $_POST['attendee_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'missing attendee' ] );
        }
        global $wpdb;
        $table = $wpdb->prefix . 'tta_attendees';
        $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
        TTA_Cache::flush();
        wp_send_json_success( [ 'message' => __( 'Attendee removed.', 'tta' ) ] );
    }

    public static function cancel_attendance() {
        check_ajax_referer( 'tta_attendee_admin_action', 'nonce' );

        $id = intval( $_POST['attendee_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'missing attendee' ] );
        }

        global $wpdb;
        $att_table   = $wpdb->prefix . 'tta_attendees';
        $ticket_table = $wpdb->prefix . 'tta_tickets';
        $tx_table     = $wpdb->prefix . 'tta_transactions';
        $hist_table   = $wpdb->prefix . 'tta_memberhistory';

        $att = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$att_table} WHERE id = %d", $id ), ARRAY_A );
        if ( ! $att ) {
            wp_send_json_error( [ 'message' => 'not found' ] );
        }

        $ticket = $wpdb->get_row( $wpdb->prepare( "SELECT event_ute_id FROM {$ticket_table} WHERE id = %d", intval( $att['ticket_id'] ) ), ARRAY_A );
        $tx     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tx_table} WHERE id = %d", intval( $att['transaction_id'] ) ), ARRAY_A );

        $event_id = 0;
        if ( $ticket && ! empty( $ticket['event_ute_id'] ) ) {
            $event_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1", $ticket['event_ute_id'], $ticket['event_ute_id'] ) );
        }

        if ( $tx ) {
            $wpdb->insert(
                $hist_table,
                [
                    'member_id'   => intval( $tx['member_id'] ),
                    'wpuserid'    => intval( $tx['wpuserid'] ),
                    'event_id'    => $event_id,
                    'action_type' => 'refund',
                    'action_data' => wp_json_encode([
                        'amount'         => 0,
                        'transaction_id' => $tx['transaction_id'],
                        'ticket_id'      => intval( $att['ticket_id'] ),
                        'attendee_id'    => $id,
                        'cancel'         => 1,
                        'attendee'       => [
                            'first_name' => $att['first_name'],
                            'last_name'  => $att['last_name'],
                            'email'      => $att['email'],
                        ],
                    ]),
                ],
                [ '%d','%d','%d','%s','%s' ]
            );
        }

        $wpdb->delete( $att_table, [ 'id' => $id ], [ '%d' ] );
        $should_notify = false;
        if ( $ticket ) {
            $current = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ticketlimit FROM {$ticket_table} WHERE id = %d", intval( $att['ticket_id'] ) ) );
            $after   = $current + 1;
            $should_notify = ( $current <= 0 && $after > 0 );

            $wpdb->query( $wpdb->prepare( "UPDATE {$ticket_table} SET ticketlimit = ticketlimit + 1 WHERE id = %d", intval( $att['ticket_id'] ) ) );
            tta_clear_ticket_cache( $ticket['event_ute_id'] ?? '', intval( $att['ticket_id'] ) );
        }

        TTA_Cache::flush();

        tta_delete_refund_request( $tx['transaction_id'], intval( $att['ticket_id'] ), $id );

        if ( $should_notify ) {
            tta_notify_waitlist_ticket_available( intval( $att['ticket_id'] ) );
        }

        wp_send_json_success( [ 'message' => __( 'Attendance cancelled.', 'tta' ) ] );
    }

    public static function refund_attendee() {
        check_ajax_referer( 'tta_attendee_admin_action', 'nonce' );

        $id   = intval( $_POST['attendee_id'] ?? 0 );
        $mode = sanitize_text_field( $_POST['mode'] ?? 'cancel' );
        $amount = floatval( $_POST['amount'] ?? 0 );

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'missing attendee' ] );
        }

        global $wpdb;
        $att_table   = $wpdb->prefix . 'tta_attendees';
        $ticket_table = $wpdb->prefix . 'tta_tickets';
        $tx_table    = $wpdb->prefix . 'tta_transactions';
        $hist_table  = $wpdb->prefix . 'tta_memberhistory';

        $att = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$att_table} WHERE id = %d", $id ), ARRAY_A );
        if ( ! $att ) {
            wp_send_json_error( [ 'message' => 'not found' ] );
        }

        $tx  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tx_table} WHERE id = %d", intval( $att['transaction_id'] ) ), ARRAY_A );
        if ( ! $tx ) {
            wp_send_json_error( [ 'message' => 'transaction missing' ] );
        }

        if ( $amount <= 0 ) {
            $details = json_decode( $tx['details'], true );
            $amount  = 0;
            if ( is_array( $details ) ) {
                foreach ( $details as $item ) {
                    if ( intval( $item['ticket_id'] ?? 0 ) === intval( $att['ticket_id'] ) ) {
                        $amount = floatval( $item['final_price'] ?? 0 );
                        break;
                    }
                }
            }
            if ( $amount <= 0 ) {
                $amount = floatval( $tx['amount'] );
            }
        }

        $api        = new TTA_AuthorizeNet_API();
        $status_res = $api->get_transaction_status( $tx['transaction_id'] );
        $res        = $api->refund( $amount, $tx['transaction_id'], $tx['card_last4'] );
        if ( ! $res['success'] ) {
            $msg = strtolower( $res['error'] );
            if ( false !== strpos( $msg, 'not meet the criteria' ) || false !== strpos( $msg, 'not settled' ) || false !== strpos( $msg, 'unsuccessful' ) || false !== strpos( strtolower( $status_res['status'] ?? '' ), 'pending' ) ) {
                $ticket = $wpdb->get_row( $wpdb->prepare( "SELECT event_ute_id FROM {$ticket_table} WHERE id = %d", intval( $att['ticket_id'] ) ), ARRAY_A );
                $event_id = 0;
                if ( $ticket && ! empty( $ticket['event_ute_id'] ) ) {
                    $event_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s LIMIT 1", $ticket['event_ute_id'] ) );
                }

                $action_data = [
                    'transaction_id' => $tx['transaction_id'],
                    'ticket_id'      => intval( $att['ticket_id'] ),
                    'reason'         => '',
                    'mode'           => $mode,
                    'pending_reason' => 'settlement',
                    'attendee'       => [
                        'id'         => $id,
                        'first_name' => $att['first_name'],
                        'last_name'  => $att['last_name'],
                        'email'      => $att['email'],
                        'phone'      => $att['phone'],
                        'amount_paid'=> $amount,
                    ],
                ];

                $exists = tta_get_refund_request( $tx['transaction_id'], intval( $att['ticket_id'] ), $id );
                if ( ! $exists ) {
                    $wpdb->insert( $hist_table, [
                        'member_id'   => intval( $tx['member_id'] ),
                        'wpuserid'    => intval( $tx['wpuserid'] ),
                        'event_id'    => $event_id,
                        'action_type' => 'refund_request',
                        'action_data' => wp_json_encode( $action_data ),
                    ], [ '%d','%d','%d','%s','%s' ] );
                    TTA_Cache::delete( 'tta_refund_requests' );
                }

                if ( 'cancel' === $mode ) {
                    tta_cancel_attendance_internal( $id, true, false );
                }
                TTA_Refund_Processor::schedule_unsettled_refund( $tx['transaction_id'], intval( $att['ticket_id'] ), $id, $amount );

                wp_send_json_success( [
                    'message' => __( 'Transaction has not settled yet. Refund will be attempted automatically.', 'tta' ),
                    'pending' => true,
                ] );
            }

            wp_send_json_error( [ 'message' => $res['error'] ] );
        }

        $wpdb->update(
            $tx_table,
            [ 'refunded' => floatval( $tx['refunded'] ) + $amount ],
            [ 'id' => intval( $tx['id'] ) ],
            [ '%f' ],
            [ '%d' ]
        );

        $ticket = $wpdb->get_row( $wpdb->prepare( "SELECT event_ute_id FROM {$ticket_table} WHERE id = %d", intval( $att['ticket_id'] ) ), ARRAY_A );
        $event_id = 0;
        if ( $ticket && ! empty( $ticket['event_ute_id'] ) ) {
            $event_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s LIMIT 1", $ticket['event_ute_id'] ) );
        }

        $wpdb->insert(
            $hist_table,
            [
                'member_id'   => intval( $tx['member_id'] ),
                'wpuserid'    => intval( $tx['wpuserid'] ),
                'event_id'    => $event_id,
                'action_type' => 'refund',
                'action_data' => wp_json_encode([
                    'amount'         => $amount,
                    'transaction_id' => $tx['transaction_id'],
                    'ticket_id'      => intval( $att['ticket_id'] ),
                    'attendee_id'    => $id,
                    'cancel'         => ( 'cancel' === $mode ) ? 1 : 0,
                    'attendee'       => [
                        'first_name' => $att['first_name'],
                        'last_name'  => $att['last_name'],
                        'email'      => $att['email'],
                    ],
                ]),
            ],
            [ '%d','%d','%d','%s','%s' ]
        );

        if ( 'cancel' === $mode ) {
            $wpdb->delete( $att_table, [ 'id' => $id ], [ '%d' ] );
            $should_notify = false;
            if ( $ticket ) {
                $current = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ticketlimit FROM {$ticket_table} WHERE id = %d", intval( $att['ticket_id'] ) ) );
                $after   = $current + 1;
                $should_notify = ( $current <= 0 && $after > 0 );

                $wpdb->query( $wpdb->prepare( "UPDATE {$ticket_table} SET ticketlimit = ticketlimit + 1 WHERE id = %d", intval( $att['ticket_id'] ) ) );
                tta_clear_ticket_cache( $ticket['event_ute_id'] ?? '', intval( $att['ticket_id'] ) );
            }
            if ( $should_notify ) {
                tta_notify_waitlist_ticket_available( intval( $att['ticket_id'] ) );
            }
        }

        TTA_Cache::flush();

        tta_delete_refund_request( $tx['transaction_id'], intval( $att['ticket_id'] ), $id );

        wp_send_json_success( [ 'message' => __( 'Refund processed.', 'tta' ) ] );
    }
}

TTA_Ajax_Attendance::init();
