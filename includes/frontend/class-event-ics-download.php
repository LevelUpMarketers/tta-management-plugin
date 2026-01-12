<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Event_ICS_Download {
    public static function init() {
        add_action( 'template_redirect', [ __CLASS__, 'maybe_download' ] );
    }

    public static function maybe_download() {
        $download_flag = tta_sanitize_text_field( $_GET['tta_event_ics'] ?? '' );
        if ( '1' !== $download_flag ) {
            return;
        }

        $event_ute_id = tta_sanitize_text_field( $_GET['event_ute_id'] ?? '' );
        if ( '' === $event_ute_id ) {
            wp_die( esc_html__( 'Missing event identifier.', 'tta' ) );
        }

        global $wpdb;
        $events_table  = $wpdb->prefix . 'tta_events';
        $archive_table = $wpdb->prefix . 'tta_events_archive';
        $event         = TTA_Cache::remember( 'event_ics_' . $event_ute_id, function() use ( $wpdb, $events_table, $archive_table, $event_ute_id ) {
            return $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$events_table} WHERE ute_id = %s UNION SELECT * FROM {$archive_table} WHERE ute_id = %s LIMIT 1",
                    $event_ute_id,
                    $event_ute_id
                ),
                ARRAY_A
            );
        }, 600 );

        if ( ! $event ) {
            wp_die( esc_html__( 'Event not found.', 'tta' ) );
        }

        $raw_address       = $event['address'] ?? '';
        $parts             = preg_split( '/\s*[-–]\s*/u', $raw_address );
        $street            = trim( $parts[0] ?? '' );
        $addr2             = trim( $parts[1] ?? '' );
        $city              = trim( $parts[2] ?? '' );
        $state             = trim( $parts[3] ?? '' );
        $zip               = trim( $parts[4] ?? '' );
        $street_full       = $street . ( $addr2 ? ' ' . $addr2 : '' );
        $city_state_zip    = $city . ( $state || $zip ? ', ' : '' ) . $state . ( $zip ? ' ' . $zip : '' );
        $formatted_address = trim( $street_full . ( $city_state_zip ? ' – ' . $city_state_zip : '' ) );

        $tz       = wp_timezone();
        $parts    = array_pad( explode( '|', $event['time'] ?? '' ), 2, '' );
        $start    = $parts[0] ?? '';
        $end      = $parts[1] ?? '';
        $start_dt = date_create_from_format( 'Y-m-d H:i', $event['date'] . ' ' . ( $start ?: '00:00' ), $tz );
        $start_ts = $start_dt ? $start_dt->getTimestamp() : strtotime( $event['date'] . ' ' . ( $start ?: '00:00' ) );
        $end_dt   = ( $event['all_day_event'] ?? false )
            ? $start_dt
            : date_create_from_format( 'Y-m-d H:i', $event['date'] . ' ' . ( $end ?: '00:00' ), $tz );
        $end_ts = $end_dt ? $end_dt->getTimestamp() : strtotime( $event['date'] . ' ' . ( $end ?: '00:00' ) );
        if ( empty( $event['all_day_event'] ) && ( ! $end || $end_ts <= $start_ts ) ) {
            $end_ts = $start_ts + HOUR_IN_SECONDS;
        }

        $event_permalink = $event['page_id'] ? get_permalink( intval( $event['page_id'] ) ) : '';
        $description     = '';
        if ( $event['page_id'] ) {
            $description = TTA_Cache::remember( 'event_ics_desc_' . $event['page_id'], function() use ( $event ) {
                $page_post = get_post( intval( $event['page_id'] ) );
                if ( ! $page_post ) {
                    return '';
                }
                return wp_trim_words( wp_strip_all_tags( $page_post->post_content ), 30, '…' );
            }, 600 );
        }

        $details_parts = [];
        if ( $description ) {
            $details_parts[] = $description;
        }
        if ( $event_permalink ) {
            $details_parts[] = $event_permalink;
        }

        $uid = sprintf( '%s@tryingtoadult', sanitize_key( $event['ute_id'] ?? uniqid( 'tta', true ) ) );
        $now = gmdate( 'Ymd\THis\Z' );

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Trying To Adult RVA//Event Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . self::escape_ics_text( $uid ),
            'DTSTAMP:' . $now,
        ];

        if ( ! empty( $event['all_day_event'] ) ) {
            $lines[] = 'DTSTART;VALUE=DATE:' . gmdate( 'Ymd', $start_ts );
            $lines[] = 'DTEND;VALUE=DATE:' . gmdate( 'Ymd', strtotime( '+1 day', $start_ts ) );
        } else {
            $lines[] = 'DTSTART:' . gmdate( 'Ymd\THis\Z', $start_ts );
            $lines[] = 'DTEND:' . gmdate( 'Ymd\THis\Z', $end_ts );
        }

        $lines[] = 'SUMMARY:' . self::escape_ics_text( $event['name'] ?? '' );
        if ( $formatted_address ) {
            $lines[] = 'LOCATION:' . self::escape_ics_text( $formatted_address );
        }
        if ( $details_parts ) {
            $lines[] = 'DESCRIPTION:' . self::escape_ics_text( implode( "\n\n", $details_parts ) );
        }
        if ( $event_permalink ) {
            $lines[] = 'URL:' . self::escape_ics_text( $event_permalink );
        }
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        $content  = implode( "\r\n", $lines ) . "\r\n";
        $filename = sprintf( 'tta-event-%s.ics', sanitize_file_name( $event['name'] ?? $event['ute_id'] ?? 'event' ) );

        nocache_headers();
        header( 'Content-Type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $content ) );

        echo $content;
        exit;
    }

    private static function escape_ics_text( $text ) {
        $text = (string) $text;
        $text = str_replace( "\\", "\\\\", $text );
        $text = str_replace( ";", "\\;", $text );
        $text = str_replace( ",", "\\,", $text );
        $text = str_replace( [ "\r\n", "\n", "\r" ], "\\n", $text );
        return $text;
    }
}

TTA_Event_ICS_Download::init();
