<?php
// includes/ajax/handlers/class-ajax-events.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Events {

    public static function init() {
        add_action( 'wp_ajax_tta_save_event',     [ __CLASS__, 'save_event' ] );
        add_action( 'wp_ajax_tta_update_event',   [ __CLASS__, 'update_event' ] );
        add_action( 'wp_ajax_tta_get_event',      [ __CLASS__, 'get_event' ] );
        add_action( 'wp_ajax_tta_get_event_form', [ __CLASS__, 'get_event_form' ] );
    }

    public static function save_event() {
        // 0) Verify nonce
        check_ajax_referer( 'tta_event_save_action', 'tta_event_save_nonce' );
        global $wpdb;
        $events_table   = $wpdb->prefix . 'tta_events';
        $tickets_table  = $wpdb->prefix . 'tta_tickets';
        $waitlist_table = $wpdb->prefix . 'tta_waitlist';

        // 1) Gather & sanitize the incoming event data
        $ute_id             = uniqid( 'tte_', true );
        $address_parts = [
            tta_sanitize_text_field( $_POST['street_address'] ?? '' ),
            tta_sanitize_text_field( $_POST['address_2']      ?? '' ),
            tta_sanitize_text_field( $_POST['city']           ?? '' ),
            tta_sanitize_text_field( $_POST['state']          ?? '' ),
            tta_sanitize_text_field( $_POST['zip']            ?? '' ),
        ];
        $address              = implode( ' - ', $address_parts );
        $start                = tta_sanitize_text_field( $_POST['start_time']   ?? '' );
        $end                  = tta_sanitize_text_field( $_POST['end_time']     ?? '' );
        $time                 = $start . '|' . $end;
        $waitlist_available   = tta_sanitize_text_field( $_POST['waitlistavailable'] ?? '0' );

        $event_data = [
            'ute_id'               => $ute_id,
            'name'                 => tta_sanitize_text_field( $_POST['name']                 ?? '' ),
            'date'                 => tta_sanitize_text_field( $_POST['date']                 ?? '' ),
            'all_day_event'        => tta_sanitize_text_field( $_POST['all_day_event']        ?? '0' ),
            'time'                 => $time,
            'virtual_event'        => tta_sanitize_text_field( $_POST['virtual_event']        ?? '0' ),
            'address'              => $address,
            'venuename'            => tta_sanitize_text_field( $_POST['venuename']            ?? '' ),
            'venueurl'             => tta_esc_url_raw( $_POST['venueurl']            ?? '' ),
            'type'                 => tta_sanitize_text_field( $_POST['type']                 ?? '' ),
            'baseeventcost'        => floatval( $_POST['baseeventcost']        ?? 0 ),
            'discountedmembercost' => floatval( $_POST['discountedmembercost'] ?? 0 ),
            'premiummembercost'    => floatval( $_POST['premiummembercost']   ?? 0 ),
            'waitlistavailable'    => $waitlist_available,
            'refundsavailable'     => tta_sanitize_text_field( $_POST['refundsavailable']    ?? '0' ),
            'discountcode'         => tta_build_discount_data(
                $_POST['discountcode'] ?? '',
                $_POST['discount_type'] ?? 'percent',
                $_POST['discount_amount'] ?? 0
            ),
            'url2'                 => tta_esc_url_raw( $_POST['url2']                ?? '' ),
            'url3'                 => tta_esc_url_raw( $_POST['url3']                ?? '' ),
            'url4'                 => tta_esc_url_raw( $_POST['url4']                ?? '' ),
            'mainimageid'          => intval( $_POST['mainimageid']         ?? 0 ),
            'otherimageids'        => tta_sanitize_text_field( $_POST['otherimageids']       ?? '' ),
            'hosts'                => implode( ',', tta_get_member_ids_by_names( $_POST['hosts'] ?? [] ) ),
            'volunteers'           => implode( ',', tta_get_member_ids_by_names( $_POST['volunteers'] ?? [] ) ),
            'host_notes'           => tta_sanitize_textarea_field( $_POST['host_notes'] ?? '' ),
        ];

        $required = [
            'name'       => $event_data['name'],
            'date'       => $event_data['date'],
            'start_time' => $start,
            'venuename'  => $event_data['venuename'],
        ];
        $missing = [];
        foreach ( $required as $key => $val ) {
            if ( '' === $val ) {
                $missing[] = $key;
            }
        }
        if ( $missing ) {
            wp_send_json_error( [ 'message' => 'Missing required fields: ' . implode( ', ', $missing ) ] );
            return;
        }

        // Save venue if new when updating
        $venue_table = $wpdb->prefix . 'tta_venues';
        $venue_name  = tta_sanitize_text_field( $_POST['venuename'] ?? '' );
        $venue_data  = [
            'name'     => $venue_name,
            'address'  => $address,
            'venueurl' => tta_esc_url_raw( $_POST['venueurl'] ?? '' ),
            'url2'     => tta_esc_url_raw( $_POST['url2'] ?? '' ),
            'url3'     => tta_esc_url_raw( $_POST['url3'] ?? '' ),
            'url4'     => tta_esc_url_raw( $_POST['url4'] ?? '' ),
        ];
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$venue_table} WHERE name = %s", $venue_name ) );
        if ( ! $exists ) {
            $wpdb->insert( $venue_table, $venue_data );
        }

        // 2) Insert the event record
        $wpdb->insert( $events_table, $event_data );
        $event_id = $wpdb->insert_id;
        if ( ! $event_id ) {
            wp_send_json_error( [ 'message' => 'Failed to create event.' ] );
        }

        // 3) Always create a ticket record
        $ticket_data = [
            'event_ute_id'         => $ute_id,
            'event_name'           => $event_data['name'],
            'ticket_name'          => 'General Admission',
            'ticketlimit'          => 10000,
            'waitlist_id'          => 0,
            'baseeventcost'        => $event_data['baseeventcost'],
            'discountedmembercost' => $event_data['discountedmembercost'],
            'premiummembercost'    => $event_data['premiummembercost'],
        ];
        $wpdb->insert( $tickets_table, $ticket_data );
        $ticket_id = $wpdb->insert_id;
        if ( ! $ticket_id ) {
            wp_send_json_error( [ 'message' => 'Failed to create ticket.' ] );
        }

        // 4) If waitlists are enabled, create one now
        $waitlist_id = 0;
        if ( '1' === $waitlist_available && tta_waitlist_uses_csv() ) {
            $waitlist_data = [
                'event_ute_id' => $ute_id,
                'ticket_id'    => $ticket_id,
                'event_name'   => $event_data['name'],
                'ticket_name'  => 'General Admission',
                'userids'      => '',
            ];
            $wpdb->insert( $waitlist_table, $waitlist_data );
            $waitlist_id = $wpdb->insert_id;
            if ( ! $waitlist_id ) {
                wp_send_json_error( [ 'message' => 'Failed to create waitlist.' ] );
            }
            $wpdb->update(
                $tickets_table,
                [ 'waitlist_id' => $waitlist_id ],
                [ 'id'           => $ticket_id ]
            );
        }

        // 5) Update event with ticket & waitlist IDs
        $wpdb->update(
            $events_table,
            [
                'ticket_id'   => $ticket_id,
                'waitlist_id' => $waitlist_id,
            ],
            [ 'id' => $event_id ]
        );

        // 6) Auto-create a WordPress Page for this Event
        $page_data = [
            'post_title'   => $event_data['name'],
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'meta_input'   => [ '_tta_event_id' => $event_id ],
        ];
        $page_id = wp_insert_post( $page_data );
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', 'event-page-template.php' );
            $wpdb->update(
                $events_table,
                [ 'page_id' => $page_id ],
                [ 'id'      => $event_id ]
            );
        }

        // 7) Save the TinyMCE description if provided
        if ( isset( $_POST['description'] ) && $page_id ) {
            wp_update_post( [
                'ID'           => $page_id,
                'post_content' => wp_kses_post( $_POST['description'] ),
            ] );
        }

        // Schedule reminder emails for the new event
        TTA_Email_Reminders::schedule_event_emails( $event_id );

        // 8) Return success
        $page_url = $page_id ? get_permalink( $page_id ) : '';
        TTA_Cache::flush();
        wp_send_json_success( [
            'message'  => 'Your Event was created successfully! <a href="' . esc_url( $page_url ) . '" target="_blank">View the Event Page here</a>. Make sure to now visit the <a href="/wp-admin/admin.php?page=tta-tickets">Tickets tab</a> to create additional Tickets and set attendance limits (if applicable). If this event has no attendance limit and needs no additional Tickets, no action is needed.',
            'id'       => $event_id,
            'ticket'   => $ticket_id,
            'waitlist' => $waitlist_id,
            'page_id'  => $page_id,
            'page_url' => $page_url,
        ] );
    }

    public static function update_event() {
        check_ajax_referer( 'tta_event_save_action', 'tta_event_save_nonce' );
        if ( empty( $_POST['tta_event_id'] ) ) {
            wp_send_json_error([ 'message' => 'Missing event ID.' ]);
        }

        global $wpdb;
        $events_table   = $wpdb->prefix . 'tta_events';
        $tickets_table  = $wpdb->prefix . 'tta_tickets';
        $waitlist_table = $wpdb->prefix . 'tta_waitlist';

        $id        = intval( $_POST['tta_event_id'] );
        $existing  = $wpdb->get_row( $wpdb->prepare( "SELECT date, time FROM {$events_table} WHERE id = %d", $id ), ARRAY_A );
        $old_date  = $existing['date'] ?? '';
        $old_start = explode( '|', $existing['time'] ?? '' )[0] ?? '';
        $address_parts = [
            tta_sanitize_text_field( $_POST['street_address'] ?? '' ),
            tta_sanitize_text_field( $_POST['address_2']      ?? '' ),
            tta_sanitize_text_field( $_POST['city']           ?? '' ),
            tta_sanitize_text_field( $_POST['state']          ?? '' ),
            tta_sanitize_text_field( $_POST['zip']            ?? '' ),
        ];
        $address   = implode( ' - ', $address_parts );
        $start     = tta_sanitize_text_field( $_POST['start_time'] ?? '' );
        $end       = tta_sanitize_text_field( $_POST['end_time']   ?? '' );
        $time      = $start . '|' . $end;

        $event_data = [
            'name'                 => tta_sanitize_text_field( $_POST['name']                 ?? '' ),
            'date'                 => tta_sanitize_text_field( $_POST['date']                 ?? '' ),
            'all_day_event'        => tta_sanitize_text_field( $_POST['all_day_event']        ?? '0' ),
            'time'                 => $time,
            'virtual_event'        => tta_sanitize_text_field( $_POST['virtual_event']        ?? '0' ),
            'address'              => $address,
            'venuename'            => tta_sanitize_text_field( $_POST['venuename']            ?? '' ),
            'venueurl'             => tta_esc_url_raw( $_POST['venueurl']            ?? '' ),
            'type'                 => tta_sanitize_text_field( $_POST['type']                 ?? '' ),
            'baseeventcost'        => floatval( $_POST['baseeventcost']        ?? 0 ),
            'discountedmembercost' => floatval( $_POST['discountedmembercost'] ?? 0 ),
            'premiummembercost'    => floatval( $_POST['premiummembercost']    ?? 0 ),
            'waitlistavailable'    => tta_sanitize_text_field( $_POST['waitlistavailable']   ?? '0' ),
            'refundsavailable'     => tta_sanitize_text_field( $_POST['refundsavailable']    ?? '0' ),
            'discountcode'         => tta_build_discount_data(
                $_POST['discountcode'] ?? '',
                $_POST['discount_type'] ?? 'percent',
                $_POST['discount_amount'] ?? 0
            ),
            'url2'                 => tta_esc_url_raw( $_POST['url2']                ?? '' ),
            'url3'                 => tta_esc_url_raw( $_POST['url3']                ?? '' ),
            'url4'                 => tta_esc_url_raw( $_POST['url4']                ?? '' ),
            'mainimageid'          => intval( $_POST['mainimageid']         ?? 0 ),
            'otherimageids'        => tta_sanitize_text_field( $_POST['otherimageids']       ?? '' ),
            'hosts'                => implode( ',', tta_get_member_ids_by_names( $_POST['hosts'] ?? [] ) ),
            'volunteers'           => implode( ',', tta_get_member_ids_by_names( $_POST['volunteers'] ?? [] ) ),
            'host_notes'           => tta_sanitize_textarea_field( $_POST['host_notes'] ?? '' ),
        ];

        $reschedule = ( $old_date !== $event_data['date'] ) || ( $old_start !== $start );

        $updated = $wpdb->update( $events_table, $event_data, [ 'id' => $id ] );
        if ( false === $updated ) {
            wp_send_json_error([ 'message' => 'Failed to update event.' ]);
        }

        $event       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$events_table} WHERE id = %d", $id ), ARRAY_A );
        $ute_id      = $event['ute_id'];
        $ticket_id   = intval( $event['ticket_id'] );
        $waitlist_id = intval( $event['waitlist_id'] );
        $page_id     = intval( $event['page_id'] );

        // Update ticket
        $ticket_update = [
            'event_name'           => $event_data['name'],
            'baseeventcost'        => $event_data['baseeventcost'],
            'discountedmembercost' => $event_data['discountedmembercost'],
            'premiummembercost'    => $event_data['premiummembercost'],
        ];
        $wpdb->update( $tickets_table, $ticket_update, [ 'event_ute_id' => $ute_id ] );

        
        // 4) If waitlists are enabled, ensure each ticket has a waitlist row
        if ( '1' === $event_data['waitlistavailable'] && tta_waitlist_uses_csv() ) {
            $tickets = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, waitlist_id, ticket_name FROM {$tickets_table} WHERE event_ute_id = %d",
                    $ute_id
                ),
                ARRAY_A
            );

            foreach ( $tickets as $ticket ) {
                $tid          = intval( $ticket['id'] );
                $existing_wl  = intval( $ticket['waitlist_id'] );

                if ( ! $existing_wl ) {
                    // Double-check no existing waitlist row
                    $row = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT id FROM {$waitlist_table} WHERE ticket_id = %d",
                            $tid
                        )
                    );

                    if ( ! $row ) {
                        // Insert full waitlist record
                        $wpdb->insert(
                            $waitlist_table,
                            [
                                'ticket_id'     => $tid,
                                'event_ute_id'  => $ute_id,
                                'ticket_name'   => tta_sanitize_text_field( $ticket['ticket_name'] ),
                                'event_name'    => tta_sanitize_text_field( $event_data['name'] ),
                                'userids'       => '',
                            ],
                            [ '%d', '%s', '%s', '%s', '%s' ]
                        );
                        $row = $wpdb->insert_id;
                    }

                    // Link ticket → waitlist
                    $wpdb->update(
                        $tickets_table,
                        [ 'waitlist_id' => $row ],
                        [ 'id' => $tid ],
                        [ '%d' ],
                        [ '%d' ]
                    );

                    // If the event itself has no waitlist_id, set it once
                    if ( ! $waitlist_id ) {
                        $wpdb->update(
                            $events_table,
                            [ 'waitlist_id' => $row ],
                            [ 'id' => $id ],
                            [ '%d' ],
                            [ '%d' ]
                        );
                        $waitlist_id = $row;
                    }
                }
            }
        }

        // Save description
        if ( isset( $_POST['description'] ) && $page_id ) {
            wp_update_post( [
                'ID'           => $page_id,
                'post_content' => wp_kses_post( $_POST['description'] ),
            ] );
        }

        $page_url = $page_id ? get_permalink( $page_id ) : '';
        if ( $reschedule ) {
            TTA_Email_Reminders::clear_event_emails( $id );
            TTA_Email_Reminders::schedule_event_emails( $id );
        }
        TTA_Cache::flush();
        wp_send_json_success([ 'message'=>'Event updated!','page_url'=>$page_url ]);
    }

    public static function get_event() {
        check_ajax_referer( 'tta_event_get_action', 'get_event_nonce' );
        if ( empty( $_POST['event_id'] ) ) {
            wp_send_json_error([ 'message'=>'Missing event ID' ]);
        }
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tta_events WHERE id=%d", intval($_POST['event_id']) ),
            ARRAY_A
        );
        if ( ! $row ) {
            wp_send_json_error([ 'message'=>'Event not found' ]);
        }
        wp_send_json_success([ 'event'=>$row ]);
    }

    public static function get_event_form() {
        check_ajax_referer( 'tta_event_get_action', 'get_event_nonce' );
        if ( empty( $_POST['event_id'] ) ) {
            wp_send_json_error([ 'message'=>'Missing event ID' ]);
        }
        wp_enqueue_media();
        $src = sanitize_key( $_POST['source'] ?? 'events' );
        if ( 'archive' !== $src ) {
            wp_enqueue_editor();
        }

        $_GET['event_id'] = intval( $_POST['event_id'] );
        if ( 'archive' === $src ) {
            $_GET['archive'] = 1;
        }
        ob_start();
        include TTA_PLUGIN_DIR . 'includes/admin/views/events-edit.php';
        $html = ob_get_clean();
        wp_send_json_success([ 'html'=>$html ]);
    }
}
