<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Calendar {
    public static function init() {
        add_action( 'wp_ajax_tta_get_calendar_month', [ __CLASS__, 'get_month' ] );
        add_action( 'wp_ajax_nopriv_tta_get_calendar_month', [ __CLASS__, 'get_month' ] );
    }

    public static function get_month() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );

        $year  = intval( $_POST['year']  ?? 0 );
        $month = intval( $_POST['month'] ?? 0 );

        $current_year = intval( date_i18n( 'Y' ) );
        $min_year = $current_year - 3;
        $max_year = $current_year + 3;

        if ( $year < $min_year ) { $year = $min_year; }
        if ( $year > $max_year ) { $year = $max_year; }
        $month = max( 1, min( 12, $month ) );

        $event_days   = tta_get_event_days_for_month( $year, $month );
        $days_in_month = intval( date( 't', mktime( 0, 0, 0, $month, 1, $year ) ) );
        $first_wday    = intval( date( 'w', mktime( 0, 0, 0, $month, 1, $year ) ) );

        $prev_year  = $year;
        $prev_month = $month - 1;
        if ( $prev_month < 1 ) { $prev_month = 12; $prev_year--; }
        $next_year  = $year;
        $next_month = $month + 1;
        if ( $next_month > 12 ) { $next_month = 1; $next_year++; }
        $prev_allowed = $prev_year >= $min_year;
        $next_allowed = $next_year <= $max_year;

        $permalinks = [];
        foreach ( $event_days as $d ) {
            $pid = tta_get_first_event_page_id_for_date( $year, $month, $d );
            if ( $pid ) {
                $permalinks[ $d ] = get_permalink( $pid );
            }
        }

        wp_send_json_success( [
            'year'         => $year,
            'month'        => $month,
            'month_name'   => date_i18n( 'F', mktime( 0, 0, 0, $month, 1, $year ) ),
            'event_days'   => $event_days,
            'days_in_month'=> $days_in_month,
            'first_wday'   => $first_wday,
            'prev_allowed' => $prev_allowed,
            'next_allowed' => $next_allowed,
            'permalinks'   => $permalinks,
        ] );
    }
}

TTA_Ajax_Calendar::init();
