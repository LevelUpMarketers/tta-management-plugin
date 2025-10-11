<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Ajax_Attendance {
    public static function init() {
        add_action( 'wp_ajax_tta_get_event_attendance', [ __CLASS__, 'get_event_attendance' ] );
        add_action( 'wp_ajax_tta_set_attendance', [ __CLASS__, 'set_attendance' ] );
        add_action( 'wp_ajax_tta_remove_attendee', [ __CLASS__, 'remove_attendee' ] );
        add_action( 'wp_ajax_tta_refund_attendee', [ __CLASS__, 'refund_attendee' ] );
        add_action( 'wp_ajax_tta_cancel_attendance', [ __CLASS__, 'cancel_attendance' ] );
        add_action( 'wp_ajax_tta_mark_pending_no_show', [ __CLASS__, 'mark_pending_no_show' ] );
        add_action( 'wp_ajax_tta_email_event_attendees', [ __CLASS__, 'email_event_attendees' ] );
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
        $event['ute_id'] = $ute;
        $attendees = tta_get_event_attendees_with_status( $ute );
        ob_start();
        $GLOBALS['event'] = $event;
        $GLOBALS['attendees'] = $attendees;
        include TTA_PLUGIN_DIR . 'includes/frontend/views/attendance-list.php';
        $html = ob_get_clean();
        wp_send_json_success( [ 'html' => $html ] );
    }

    public static function email_event_attendees() {
        check_ajax_referer( 'tta_email_attendees_action', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'Please sign in to send emails.', 'tta' ) ] );
        }

        $ute        = tta_sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        $message    = isset( $_POST['message'] ) ? tta_sanitize_checkin_email_message( $_POST['message'] ) : '';
        $min_length = tta_get_checkin_email_min_length();

        if ( '' === $ute ) {
            wp_send_json_error( [ 'message' => __( 'Missing event identifier.', 'tta' ) ] );
        }

        if ( '' === $message ) {
            wp_send_json_error( [ 'message' => __( 'Please provide a message to send.', 'tta' ) ] );
        }

        $normalized = trim( preg_replace( '/\s+/', ' ', $message ) );
        $length     = function_exists( 'mb_strlen' ) ? mb_strlen( $normalized ) : strlen( $normalized );

        if ( $length < $min_length ) {
            wp_send_json_error(
                [
                    'message' => sprintf(
                        /* translators: %d: minimum number of characters required for the message. */
                        __( 'Please enter at least %d characters before sending.', 'tta' ),
                        $min_length
                    ),
                ]
            );
        }

        if ( $length > 2000 ) {
            wp_send_json_error( [ 'message' => __( 'Messages must be 2,000 characters or fewer.', 'tta' ) ] );
        }

        if ( ! class_exists( 'TTA_Email_Handler' ) ) {
            require_once TTA_PLUGIN_DIR . 'includes/email/class-email-handler.php';
        }

        $handler = TTA_Email_Handler::get_instance();
        $sent    = $handler->send_checkin_broadcast( $ute, $message );

        if ( is_wp_error( $sent ) ) {
            wp_send_json_error( [ 'message' => $sent->get_error_message() ] );
        }

        wp_send_json_success( [ 'message' => sprintf( _n( 'Email sent to %d recipient.', 'Email sent to %d recipients.', $sent, 'tta' ), $sent ) ] );
    }

    public static function set_attendance() {
        check_ajax_referer( 'tta_set_attendance_action', 'nonce' );
        $att_id = intval( $_POST['attendee_id'] ?? 0 );
        $status = sanitize_text_field( $_POST['status'] ?? 'pending' );
        if ( ! $att_id ) {
            wp_send_json_error( [ 'message' => 'missing attendee' ] );
        }

        global $wpdb;
        $att_table = $wpdb->prefix . 'tta_attendees';
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT email, status FROM {$att_table} WHERE id = %d", $att_id ), ARRAY_A );
        if ( ! $row ) {
            wp_send_json_error( [ 'message' => 'not found' ] );
        }
        $current = sanitize_text_field( $row['status'] );
        if ( 'pending' !== $current ) {
            wp_send_json_success( [ 'reload' => true ] );
        }

        tta_set_attendance_status( $att_id, $status );

        $email     = strtolower( sanitize_email( $row['email'] ) );
        $attended  = tta_get_attended_event_count_by_email( $email );
        $no_show   = tta_get_no_show_event_count_by_email( $email );

        wp_send_json_success( [
            'attended' => $attended,
            'no_show'  => $no_show,
        ] );
    }

    public static function mark_pending_no_show() {
        check_ajax_referer( 'tta_set_attendance_action', 'nonce' );
        $ute = tta_sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        if ( ! $ute ) {
            wp_send_json_error( [ 'message' => 'missing id' ] );
        }
        $attendees = tta_get_event_attendees_with_status( $ute );
        $count     = 0;
        foreach ( $attendees as $a ) {
            if ( 'pending' === $a['status'] ) {
                tta_set_attendance_status( intval( $a['id'] ), 'no_show' );
                $count++;
            }
        }
        wp_send_json_success( [ 'updated' => $count ] );
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
        $status     = strtolower( $status_res['status'] ?? '' );
        $pending    = false !== strpos( $status, 'pending' );
        $full       = abs( floatval( $tx['amount'] ) - $amount ) < 0.01;

        if ( $pending && $full ) {
            $res = $api->void( $tx['transaction_id'] );
            if ( ! $res['success'] ) {
                $res = $api->refund( $amount, $tx['transaction_id'], $tx['card_last4'] );
            }
        } else {
            $res = $api->refund( $amount, $tx['transaction_id'], $tx['card_last4'] );
        }

        if ( ! $res['success'] ) {
            $msg = strtolower( $res['error'] );
            if ( $pending || false !== strpos( $msg, 'not meet the criteria' ) || false !== strpos( $msg, 'not settled' ) || false !== strpos( $msg, 'unsuccessful' ) ) {
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
