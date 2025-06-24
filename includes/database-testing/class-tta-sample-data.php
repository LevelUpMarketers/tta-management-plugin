<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Sample_Data {
    public static function load() {
        $events  = include TTA_PLUGIN_DIR . 'database-testing/sample-events.php';
        $tickets = include TTA_PLUGIN_DIR . 'database-testing/sample-tickets.php';

        global $wpdb;
        $events_table  = $wpdb->prefix . 'tta_events';
        $tickets_table = $wpdb->prefix . 'tta_tickets';

        foreach ( $events as $event ) {
            $row = [
                'ute_id'                => sanitize_text_field( $event['ute_id'] ),
                'name'                  => sanitize_text_field( $event['name'] ),
                'date'                  => sanitize_text_field( $event['date'] ),
                'baseeventcost'         => floatval( $event['baseeventcost'] ),
                'discountedmembercost'  => floatval( $event['discountedmembercost'] ),
                'premiummembercost'     => floatval( $event['premiummembercost'] ),
                'address'               => sanitize_text_field( $event['address'] ),
                'type'                  => sanitize_text_field( $event['type'] ),
                'time'                  => sanitize_text_field( $event['time'] ),
                'venuename'             => sanitize_text_field( $event['venuename'] ),
            ];
            $wpdb->insert( $events_table, $row );
        }

        foreach ( $tickets as $ticket ) {
            $row = [
                'event_ute_id'         => sanitize_text_field( $ticket['event_ute_id'] ),
                'event_name'           => sanitize_text_field( $ticket['event_name'] ),
                'ticket_name'          => sanitize_text_field( $ticket['ticket_name'] ),
                'waitlist_id'          => intval( $ticket['waitlist_id'] ),
                'ticketlimit'          => intval( $ticket['ticketlimit'] ),
                'baseeventcost'        => floatval( $ticket['baseeventcost'] ),
                'discountedmembercost' => floatval( $ticket['discountedmembercost'] ),
                'premiummembercost'    => floatval( $ticket['premiummembercost'] ),
            ];
            $wpdb->insert( $tickets_table, $row );
        }

        TTA_Cache::flush();
    }
}
