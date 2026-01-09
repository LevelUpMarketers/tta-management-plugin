<?php
// includes/ajax/handlers/class-ajax-tickets.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Tickets {

    public static function init() {
        add_action( 'wp_ajax_tta_get_ticket_form', [ __CLASS__, 'get_ticket_form' ] );
        add_action( 'wp_ajax_tta_update_ticket',   [ __CLASS__, 'update_ticket' ] );
        add_action( 'wp_ajax_tta_export_attendees', [ __CLASS__, 'export_attendees' ] );
    }

    /**
     * Return the “edit ticket” form via AJAX
     */
    public static function get_ticket_form() {
        check_ajax_referer( 'tta_ticket_get_action', 'get_ticket_nonce' );

        $ute = tta_sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        if ( ! $ute ) {
            wp_send_json_error( [ 'message' => 'Missing event ID.' ] );
        }

        $GLOBALS['ticket'] = [ 'event_ute_id' => $ute ];

        ob_start();
        include TTA_PLUGIN_DIR . 'includes/admin/views/tickets-edit.php';
        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }

    /**
     * Handle AJAX request to update, delete, and insert tickets & waitlists.
     */
    public static function update_ticket() {
        check_ajax_referer( 'tta_ticket_save_action', 'tta_ticket_save_nonce' );

        if ( empty( $_POST['event_ute_id'] ) ) {
            wp_send_json_error( [ 'message' => 'Missing event identifier.' ] );
        }

        global $wpdb;
        $tickets_table   = $wpdb->prefix . 'tta_tickets';
        $waitlist_table  = $wpdb->prefix . 'tta_waitlist';
        $events_table    = $wpdb->prefix . 'tta_events';
        $ute             = tta_sanitize_text_field( $_POST['event_ute_id'] );

        // 1) Fetch event info (name + waitlist flag)
        $event_row       = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT name, waitlistavailable
                 FROM {$events_table}
                 WHERE ute_id = %s",
                $ute
            ),
            ARRAY_A
        );
        $event_name       = $event_row['name'] ?? '';
        $waitlist_enabled = ( '1' === ( $event_row['waitlistavailable'] ?? '0' ) );

        // 2) Fetch all existing ticket IDs for this event
        $all_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$tickets_table} WHERE event_ute_id = %s",
                $ute
            )
        );

        // 3) Grab submitted arrays for existing tickets
        $names               = $_POST['event_name']           ?? [];
        $limits              = $_POST['ticketlimit']          ?? [];
        $member_limits       = $_POST['memberlimit']          ?? [];
        $base_costs          = $_POST['baseeventcost']        ?? [];
        $member_costs        = $_POST['discountedmembercost'] ?? [];
        $premium_costs       = $_POST['premiummembercost']    ?? [];
        $waitlist_csv_by_tid = $_POST['waitlist_userids']     ?? [];

        // 4) Update existing tickets + waitlists
        foreach ( $names as $tid => $raw_name ) {
            $tid         = intval( $tid );
            $ticket_name = tta_sanitize_text_field( $raw_name );
            $old_limit   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ticketlimit FROM {$tickets_table} WHERE id = %d", $tid ) );
            $new_limit   = intval( $limits[ $tid ] ?? 0 );

            // a) Update ticket row
            $wpdb->update(
                $tickets_table,
                [
                    'ticket_name'          => $ticket_name,
                    'ticketlimit'          => $new_limit,
                    'memberlimit'          => intval( $member_limits[ $tid ] ?? 2 ),
                    'baseeventcost'        => floatval( $base_costs[ $tid ] ?? 0 ),
                    'discountedmembercost' => floatval( $member_costs[ $tid ] ?? 0 ),
                    'premiummembercost'    => floatval( $premium_costs[ $tid ] ?? 0 ),
                ],
                [ 'id' => $tid ],
                [ '%s', '%d', '%d', '%f', '%f', '%f' ],
                [ '%d' ]
            );

            if ( $old_limit <= 0 && $new_limit > 0 ) {
                tta_notify_waitlist_ticket_available( $tid );
            }

            // b) Update or insert waitlist row (with ticket_name)
            $csv    = tta_sanitize_text_field( $waitlist_csv_by_tid[ $tid ] ?? '' );
            if ( tta_waitlist_uses_csv() ) {
                $exists = (bool) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM {$waitlist_table} WHERE ticket_id = %d",
                        $tid
                    )
                );

                if ( '' === $csv ) {
                    // Clear but update ticket_name & event_name
                    $wpdb->update(
                        $waitlist_table,
                        [
                            'userids'     => '',
                            'ticket_name' => $ticket_name,
                            'event_name'  => $event_name,
                        ],
                        [ 'ticket_id' => $tid ],
                        [ '%s', '%s', '%s' ],
                        [ '%d' ]
                    );
                } elseif ( $exists ) {
                    $wpdb->update(
                        $waitlist_table,
                        [
                            'userids'     => $csv,
                            'ticket_name' => $ticket_name,
                            'event_name'  => $event_name,
                        ],
                        [ 'ticket_id' => $tid ],
                        [ '%s', '%s', '%s' ],
                        [ '%d' ]
                    );
                } else {
                    $wpdb->insert(
                        $waitlist_table,
                        [
                            'event_ute_id' => $ute,
                            'ticket_id'    => $tid,
                            'ticket_name'  => $ticket_name,
                            'event_name'   => $event_name,
                            'userids'      => $csv,
                        ],
                        [ '%s', '%d', '%s', '%s', '%s' ]
                    );
                }
            } else {
                if ( '' === $csv ) {
                    $wpdb->delete( $waitlist_table, [ 'ticket_id' => $tid ], [ '%d' ] );
                }
                $wpdb->update(
                    $waitlist_table,
                    [
                        'ticket_name' => $ticket_name,
                        'event_name'  => $event_name,
                    ],
                    [ 'ticket_id' => $tid ],
                    [ '%s', '%s' ],
                    [ '%d' ]
                );
            }
        }

        // 5) Delete tickets (and their waitlists) removed from the form
        $submitted_ids = array_map( 'intval', array_keys( $names ) );
        $to_delete     = array_diff( $all_ids, $submitted_ids );
        if ( $to_delete ) {
            foreach ( $to_delete as $del_id ) {
                $wpdb->delete( $tickets_table,  [ 'id' => $del_id ],        [ '%d' ] );
                $wpdb->delete( $waitlist_table, [ 'ticket_id' => $del_id ], [ '%d' ] );
            }
        }

        // 6) Insert any new tickets (and waitlists if enabled)
        if ( ! empty( $_POST['new_event_name'] ) ) {
            $new_names       = $_POST['new_event_name']            ?? [];
            $new_limits      = $_POST['new_ticketlimit']          ?? [];
            $new_memberlimit = $_POST['new_memberlimit']         ?? [];
            $new_base        = $_POST['new_baseeventcost']         ?? [];
            $new_member      = $_POST['new_discountedmembercost']  ?? [];
            $new_prem        = $_POST['new_premiummembercost']     ?? [];

            foreach ( $new_names as $i => $raw_name ) {
                $ticket_name = tta_sanitize_text_field( $raw_name );
                if ( '' === $ticket_name ) {
                    continue;
                }

                // insert the ticket
                $wpdb->insert(
                    $tickets_table,
                    [
                        'event_ute_id'          => $ute,
                        'event_name'            => $event_name,
                        'ticket_name'           => $ticket_name,
                        'ticketlimit'           => intval( $new_limits[ $i ]    ?? 10000 ),
                        'memberlimit'           => intval( $new_memberlimit[ $i ] ?? 2 ),
                        'baseeventcost'         => floatval( $new_base[ $i ]     ?? 0 ),
                        'discountedmembercost'  => floatval( $new_member[ $i ]   ?? 0 ),
                        'premiummembercost'     => floatval( $new_prem[ $i ]     ?? 0 ),
                        'waitlist_id'           => 0,
                    ],
                    [ '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f', '%d' ]
                );
                $new_tid = $wpdb->insert_id;

                if ( $waitlist_enabled && $new_tid && tta_waitlist_uses_csv() ) {
                    // insert waitlist row for CSV-based tables
                    $wpdb->insert(
                        $waitlist_table,
                        [
                            'event_ute_id' => $ute,
                            'ticket_id'    => $new_tid,
                            'ticket_name'  => $ticket_name,
                            'event_name'   => $event_name,
                            'userids'      => '',
                        ],
                        [ '%s', '%d', '%s', '%s', '%s' ]
                    );
                    $new_wl_id = $wpdb->insert_id;
                    // update ticket with its new waitlist_id
                    $wpdb->update(
                        $tickets_table,
                        [ 'waitlist_id' => $new_wl_id ],
                        [ 'id'           => $new_tid ],
                        [ '%d' ],
                        [ '%d' ]
                    );
                }
            }
        }

        // 7) All done
        TTA_Cache::flush();
        wp_send_json_success( [ 'message' => __( 'Tickets & waitlists saved.', 'tta' ) ] );
    }

    /**
     * Export all attendees for an event as a CSV download.
     */
    public static function export_attendees() {
        check_ajax_referer( 'tta_export_attendees_action', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to export attendees.', 'tta' ) );
        }

        $ute = tta_sanitize_text_field( $_GET['event_ute_id'] ?? '' );
        if ( '' === $ute ) {
            wp_die( esc_html__( 'Missing event identifier.', 'tta' ) );
        }

        global $wpdb;
        $event_name = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT name FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1",
                $ute,
                $ute
            )
        );
        $event_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1",
                $ute,
                $ute
            )
        );
        $filename_base = $event_name ? sanitize_file_name( $event_name ) : $ute;
        $filename      = sprintf( 'tta-attendees-%s.csv', $filename_base );

        $ticket_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}tta_tickets WHERE event_ute_id = %s",
                $ute
            )
        );
        $archive_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}tta_tickets_archive WHERE event_ute_id = %s",
                $ute
            )
        );
        $ticket_ids = array_values( array_unique( array_merge( $ticket_ids, $archive_ids ) ) );

        $rows = [];
        foreach ( $ticket_ids as $ticket_id ) {
            $ticket_id = intval( $ticket_id );
            foreach ( tta_get_ticket_attendees( $ticket_id ) as $attendee ) {
                $rows[] = [
                    'first_name' => $attendee['first_name'] ?? '',
                    'last_name'  => $attendee['last_name'] ?? '',
                    'email'      => $attendee['email'] ?? '',
                    'phone'      => $attendee['phone'] ?? '',
                    'status'     => 'Verified',
                ];
            }

            if ( $event_id ) {
                foreach ( tta_get_ticket_refunded_attendees( $ticket_id, $event_id ) as $attendee ) {
                    $rows[] = [
                        'first_name' => $attendee['first_name'] ?? '',
                        'last_name'  => $attendee['last_name'] ?? '',
                        'email'      => $attendee['email'] ?? '',
                        'phone'      => $attendee['phone'] ?? '',
                        'status'     => 'Refunded',
                    ];
                }

                foreach ( tta_get_ticket_pending_refund_attendees( $ticket_id, $event_id ) as $attendee ) {
                    $rows[] = [
                        'first_name' => $attendee['first_name'] ?? '',
                        'last_name'  => $attendee['last_name'] ?? '',
                        'email'      => $attendee['email'] ?? '',
                        'phone'      => $attendee['phone'] ?? '',
                        'status'     => 'Pending refund Request',
                    ];
                }
            }
        }

        foreach ( tta_get_event_waitlist_entries( $ute ) as $entry ) {
            $rows[] = [
                'first_name' => $entry['first_name'] ?? '',
                'last_name'  => $entry['last_name'] ?? '',
                'email'      => $entry['email'] ?? '',
                'phone'      => $entry['phone'] ?? '',
                'status'     => 'Waitlist',
            ];
        }

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );
        if ( false === $output ) {
            wp_die( esc_html__( 'Unable to generate export file.', 'tta' ) );
        }

        fputcsv( $output, [ 'First Name', 'Last Name', 'Email', 'Phone', 'Status' ] );

        foreach ( $rows as $attendee ) {
            $first_name = sanitize_text_field( $attendee['first_name'] ?? '' );
            $last_name  = sanitize_text_field( $attendee['last_name'] ?? '' );
            $email      = sanitize_email( $attendee['email'] ?? '' );
            $phone      = sanitize_text_field( $attendee['phone'] ?? '' );
            $status     = sanitize_text_field( $attendee['status'] ?? '' );
            $phone_value = '' !== $phone ? $phone : 'N/A';
            fputcsv( $output, [ $first_name, $last_name, $email, $phone_value, $status ] );
        }

        fclose( $output );
        exit;
    }
}

// Initialize this handler
TTA_Ajax_Tickets::init();
