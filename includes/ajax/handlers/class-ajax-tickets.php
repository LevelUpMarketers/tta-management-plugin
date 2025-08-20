<?php
// includes/ajax/handlers/class-ajax-tickets.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Tickets {

    public static function init() {
        add_action( 'wp_ajax_tta_get_ticket_form', [ __CLASS__, 'get_ticket_form' ] );
        add_action( 'wp_ajax_tta_update_ticket',   [ __CLASS__, 'update_ticket' ] );
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
}

// Initialize this handler
TTA_Ajax_Tickets::init();
