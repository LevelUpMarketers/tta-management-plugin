<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Event_Archiver {
    /**
     * Archive and remove past events older than three days.
     */
    public static function archive_past_events() {
        global $wpdb;
        $events_table  = $wpdb->prefix . 'tta_events';
        $archive_table = $wpdb->prefix . 'tta_events_archive';
        $waitlist_table   = $wpdb->prefix . 'tta_waitlist';
        $tickets_table    = $wpdb->prefix . 'tta_tickets';
        $tickets_archive  = $wpdb->prefix . 'tta_tickets_archive';
        $att_table        = $wpdb->prefix . 'tta_attendees';
        $att_archive      = $wpdb->prefix . 'tta_attendees_archive';

        $cutoff = date( 'Y-m-d', strtotime( '-3 days', current_time( 'timestamp' ) ) );
        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$events_table} WHERE date < %s", $cutoff ), ARRAY_A );

        foreach ( $rows as $row ) {
            $id     = intval( $row['id'] );
            $ute_id = $row['ute_id'];
            TTA_Email_Reminders::clear_event_emails( $id );
            TTA_Email_Reminders::schedule_post_event_thanks( $id );
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$archive_table} WHERE id = %d", $id ) );
            if ( ! $exists ) {
                $wpdb->insert( $archive_table, $row );
            }

            // Archive tickets and attendees
            $tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tickets_table} WHERE event_ute_id = %s", $ute_id ), ARRAY_A );
            foreach ( $tickets as $t ) {
                $t_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tickets_archive} WHERE id = %d", $t['id'] ) );
                if ( ! $t_exists ) {
                    $wpdb->insert( $tickets_archive, $t );
                }
                $attendees = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$att_table} WHERE ticket_id = %d", $t['id'] ), ARRAY_A );
                foreach ( $attendees as $a ) {
                    $a_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$att_archive} WHERE id = %d", $a['id'] ) );
                    if ( ! $a_exists ) {
                        $wpdb->insert( $att_archive, $a );
                    }
                }
            }

            $wpdb->delete( $waitlist_table, [ 'event_ute_id' => $ute_id ], [ '%s' ] );
            $wpdb->delete( $tickets_table,  [ 'event_ute_id' => $ute_id ], [ '%s' ] );
            $wpdb->delete( $events_table,  [ 'id' => $id ], [ '%d' ] );
        }

        if ( ! empty( $rows ) ) {
            TTA_Cache::flush();
        }
    }

    /** Schedule the cron event. */
    public static function schedule_event() {
        if ( ! wp_next_scheduled( 'tta_event_archive_event' ) ) {
            wp_schedule_event( time(), 'daily', 'tta_event_archive_event' );
        }
    }

    /** Clear the scheduled cron event. */
    public static function clear_event() {
        wp_clear_scheduled_hook( 'tta_event_archive_event' );
    }

    /** Initialize hooks. */
    public static function init() {
        add_action( 'tta_event_archive_event', [ __CLASS__, 'archive_past_events' ] );
        self::schedule_event();
    }
}
