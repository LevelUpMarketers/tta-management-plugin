<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Sample_Data {
    public static function load() {
        $events  = include TTA_PLUGIN_DIR . 'database-testing/sample-events.php';
        $tickets = include TTA_PLUGIN_DIR . 'database-testing/sample-tickets.php';

        global $wpdb;
        $events_table   = $wpdb->prefix . 'tta_events';
        $tickets_table  = $wpdb->prefix . 'tta_tickets';
        $waitlist_table = $wpdb->prefix . 'tta_waitlist';

        foreach ( $events as $index => $event ) {
            $row = [
                'ute_id'               => sanitize_text_field( $event['ute_id'] ),
                'name'                 => sanitize_text_field( $event['name'] ),
                'date'                 => sanitize_text_field( $event['date'] ),
                'baseeventcost'        => floatval( $event['baseeventcost'] ),
                'discountedmembercost' => floatval( $event['discountedmembercost'] ),
                'premiummembercost'    => floatval( $event['premiummembercost'] ),
                'address'              => sanitize_text_field( $event['address'] ),
                'type'                 => sanitize_text_field( $event['type'] ),
                'time'                 => sanitize_text_field( $event['time'] ),
                'venuename'            => sanitize_text_field( $event['venuename'] ),
                'venueurl'             => sanitize_text_field( $event['venueurl'] ),
                'url2'                 => sanitize_text_field( $event['url2'] ),
                'url3'                 => sanitize_text_field( $event['url3'] ),
                'url4'                 => sanitize_text_field( $event['url4'] ),
                'mainimageid'          => intval( $event['mainimageid'] ),
                'otherimageids'        => sanitize_text_field( $event['otherimageids'] ),
                'waitlistavailable'    => intval( $event['waitlistavailable'] ),
                'discountcode'         => sanitize_text_field( $event['discountcode'] ),
                'all_day_event'        => intval( $event['all_day_event'] ),
                'virtual_event'        => intval( $event['virtual_event'] ),
                'refundsavailable'     => intval( $event['refundsavailable'] ),
                'hosts'                => sanitize_text_field( $event['hosts'] ),
                'volunteers'           => sanitize_text_field( $event['volunteers'] ),
                'created_at'           => sanitize_text_field( $event['created_at'] ),
                'updated_at'           => sanitize_text_field( $event['updated_at'] ),
            ];
            $wpdb->insert( $events_table, $row );
            $event_id = $wpdb->insert_id;

            $ticket = $tickets[ $index ] ?? null;
            if ( $ticket ) {
                $ticket_row = [
                    'event_ute_id'         => sanitize_text_field( $ticket['event_ute_id'] ),
                    'event_name'           => sanitize_text_field( $ticket['event_name'] ),
                    'ticket_name'          => sanitize_text_field( $ticket['ticket_name'] ),
                    'waitlist_id'          => intval( $ticket['waitlist_id'] ),
                    'ticketlimit'          => intval( $ticket['ticketlimit'] ),
                    'baseeventcost'        => floatval( $ticket['baseeventcost'] ),
                    'discountedmembercost' => floatval( $ticket['discountedmembercost'] ),
                    'premiummembercost'    => floatval( $ticket['premiummembercost'] ),
                ];
                $wpdb->insert( $tickets_table, $ticket_row );
                $ticket_id = $wpdb->insert_id;
                $wpdb->update( $events_table, [ 'ticket_id' => $ticket_id ], [ 'id' => $event_id ] );
            } else {
                $ticket_id = 0;
            }

            if ( $row['waitlistavailable'] ) {
                $wpdb->insert( $waitlist_table, [
                    'event_ute_id' => $row['ute_id'],
                    'ticket_id'    => $ticket_id,
                    'event_name'   => $row['name'],
                    'ticket_name'  => 'General Admission',
                    'userids'      => '',
                ] );
                $wlid = $wpdb->insert_id;
                $wpdb->update( $tickets_table, [ 'waitlist_id' => $wlid ], [ 'id' => $ticket_id ] );
                $wpdb->update( $events_table, [ 'waitlist_id' => $wlid ], [ 'id' => $event_id ] );
            }

            if ( function_exists( 'wp_insert_post' ) ) {
                $page_id = wp_insert_post( [
                    'post_title'   => $row['name'],
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'meta_input'   => [ '_tta_event_id' => $event_id ],
                ] );
                if ( $page_id && ! is_wp_error( $page_id ) ) {
                    update_post_meta( $page_id, '_wp_page_template', 'event-page-template.php' );
                    $wpdb->update( $events_table, [ 'page_id' => $page_id ], [ 'id' => $event_id ] );
                }
            }
        }

        TTA_Cache::flush();
    }

    public static function clear() {
        global $wpdb;
        $events_table   = $wpdb->prefix . 'tta_events';
        $tickets_table  = $wpdb->prefix . 'tta_tickets';
        $waitlist_table = $wpdb->prefix . 'tta_waitlist';

        $page_ids = $wpdb->get_col( "SELECT page_id FROM {$events_table} WHERE ute_id LIKE 'sample_event_%'" );
        foreach ( $page_ids as $pid ) {
            if ( $pid && function_exists( 'wp_delete_post' ) ) {
                wp_delete_post( (int) $pid, true );
            }
        }

        $wpdb->query( "DELETE FROM {$events_table} WHERE ute_id LIKE 'sample_event_%'" );
        $wpdb->query( "DELETE FROM {$tickets_table} WHERE event_ute_id LIKE 'sample_event_%'" );
        $wpdb->query( "DELETE FROM {$waitlist_table} WHERE event_ute_id LIKE 'sample_event_%'" );

        TTA_Cache::flush();
    }
}
