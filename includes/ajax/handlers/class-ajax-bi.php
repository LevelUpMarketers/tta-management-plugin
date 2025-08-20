<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_BI {
    public static function init() {
        add_action( 'wp_ajax_tta_bi_data', [ __CLASS__, 'bi_data' ] );
    }

    public static function bi_data() {
        global $wpdb;
        $members = $wpdb->prefix . 'tta_members';
        $tx      = $wpdb->prefix . 'tta_transactions';
        $att     = $wpdb->prefix . 'tta_attendees';
        $events  = $wpdb->prefix . 'tta_events';
        $hist    = $wpdb->prefix . 'tta_memberhistory';

        $months = isset( $_GET['months'] ) ? max( 1, min( 24, absint( $_GET['months'] ) ) ) : 6;
        $chart  = isset( $_GET['chart'] ) ? sanitize_key( $_GET['chart'] ) : 'all';
        $compare = ! empty( $_GET['compare'] );
        $start  = gmdate( 'Y-m-01 00:00:00', strtotime( "-$months months" ) );
        $prev_start = gmdate( 'Y-m-01 00:00:00', strtotime( "-$months months", strtotime( $start ) ) );

        $labels = [];
        $ts = strtotime( $start );
        for ( $i = 0; $i < $months; $i++ ) {
            $labels[] = gmdate( 'Y-m', strtotime( "+$i month", $ts ) );
        }
        $prev_labels = [];
        $pts = strtotime( $prev_start );
        for ( $i = 0; $i < $months; $i++ ) {
            $prev_labels[] = gmdate( 'Y-m', strtotime( "+$i month", $pts ) );
        }

        $data = [];

        if ( 'all' === $chart || 'subs' === $chart ) {
            $active    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$members} WHERE subscription_status = 'active'" );
            $cancelled = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$members} WHERE subscription_status = 'cancelled'" );
            $problem   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$members} WHERE subscription_status = 'paymentproblem'" );
            $data['subs'] = [
                [ 'label' => 'Active', 'count' => $active ],
                [ 'label' => 'Cancelled', 'count' => $cancelled ],
                [ 'label' => 'Payment Issues', 'count' => $problem ],
            ];
        }

        if ( 'all' === $chart || 'signups' === $chart ) {
            $signup_rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE_FORMAT(joined_at,'%Y-%m') m, COUNT(*) c FROM {$members} WHERE joined_at >= %s GROUP BY m", $start ), ARRAY_A );
            $map = wp_list_pluck( $signup_rows, 'c', 'm' );
            $data['signups'] = [];
            foreach ( $labels as $m ) {
                $data['signups'][] = [ 'label' => $m, 'count' => isset( $map[ $m ] ) ? (int) $map[ $m ] : 0 ];
            }
            if ( $compare ) {
                $prev_rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE_FORMAT(joined_at,'%Y-%m') m, COUNT(*) c FROM {$members} WHERE joined_at >= %s AND joined_at < %s GROUP BY m", $prev_start, $start ), ARRAY_A );
                $pmap = wp_list_pluck( $prev_rows, 'c', 'm' );
                $data['signups_prev'] = [];
                foreach ( $prev_labels as $m ) {
                    $data['signups_prev'][] = [ 'label' => $m, 'count' => isset( $pmap[ $m ] ) ? (int) $pmap[ $m ] : 0 ];
                }
            }
        }

        if ( 'all' === $chart || 'revenue' === $chart || 'prediction' === $chart || 'cumulative' === $chart ) {
            $rev_rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE_FORMAT(created_at,'%Y-%m') as m, SUM(amount - refunded) as total FROM {$tx} WHERE created_at >= %s GROUP BY m", $start ), ARRAY_A );
            $map = wp_list_pluck( $rev_rows, 'total', 'm' );
            $data['revenue'] = [];
            foreach ( $labels as $m ) {
                $data['revenue'][] = [ 'label' => $m, 'amount' => isset( $map[ $m ] ) ? (float) $map[ $m ] : 0 ];
            }
            if ( $compare ) {
                $prev_rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE_FORMAT(created_at,'%Y-%m') as m, SUM(amount - refunded) as total FROM {$tx} WHERE created_at >= %s AND created_at < %s GROUP BY m", $prev_start, $start ), ARRAY_A );
                $pmap = wp_list_pluck( $prev_rows, 'total', 'm' );
                $data['revenue_prev'] = [];
                foreach ( $prev_labels as $m ) {
                    $data['revenue_prev'][] = [ 'label' => $m, 'amount' => isset( $pmap[ $m ] ) ? (float) $pmap[ $m ] : 0 ];
                }
            }
        }

        if ( ( 'all' === $chart || 'cumulative' === $chart ) && ! empty( $data['revenue'] ) ) {
            $sum = 0;
            $data['cumulative'] = array_map( function( $r ) use ( &$sum ) { $sum += $r['amount']; return [ 'label' => $r['label'], 'amount' => $sum ]; }, $data['revenue'] );
            if ( $compare && ! empty( $data['revenue_prev'] ) ) {
                $sum = 0;
                $data['cumulative_prev'] = array_map( function( $r ) use ( &$sum ){ $sum += $r['amount']; return [ 'label'=>$r['label'], 'amount'=>$sum ]; }, $data['revenue_prev'] );
            }
        }

        if ( 'all' === $chart || 'ticket_sales' === $chart ) {
            $ticket_rows = $wpdb->get_results( "SELECT YEAR(created_at) as y, SUM(amount-refunded) as total FROM {$tx} GROUP BY y ORDER BY y", ARRAY_A );
            $data['ticket_sales'] = array_map( function( $r ){ return [ 'label'=>$r['y'], 'amount'=>(float)$r['total'] ]; }, $ticket_rows );
        }

        if ( 'all' === $chart || 'avg_tickets' === $chart ) {
            $year = gmdate('Y');
            $avg_rows = $wpdb->get_results( $wpdb->prepare("SELECT MONTH(e.date) m, AVG(a.count) avg_tix FROM {$events} e LEFT JOIN (SELECT ticket_id, COUNT(*) count FROM {$att} GROUP BY ticket_id) a ON a.ticket_id=e.id WHERE YEAR(e.date)=%d GROUP BY MONTH(e.date)", $year), ARRAY_A );
            $data['avg_tickets'] = array_map(function($r){ return ['label'=>sprintf('%02d',$r['m']), 'count'=>round($r['avg_tix'],2)]; }, $avg_rows );
        }

        if ( 'all' === $chart || 'by_level' === $chart ) {
            $level_rows = $wpdb->get_results( "SELECT membership_level, COUNT(*) c FROM {$members} GROUP BY membership_level", ARRAY_A );
            $data['by_level'] = array_map(function($r){ return ['label'=>$r['membership_level'], 'count'=>(int)$r['c']]; }, $level_rows );
        }

        if ( 'all' === $chart || 'churn' === $chart ) {
            $cancel_rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE_FORMAT(action_date,'%Y-%m') m, COUNT(*) c FROM {$hist} WHERE action_type='membership_cancel' AND action_date >= %s GROUP BY m", $start ), ARRAY_A );
            $total_members = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$members}" );
            $map = wp_list_pluck( $cancel_rows, 'c', 'm' );
            $data['churn'] = [];
            foreach ( $labels as $m ) {
                $count = isset( $map[ $m ] ) ? $map[ $m ] : 0;
                $rate = $total_members ? ( $count / $total_members ) * 100 : 0;
                $data['churn'][] = [ 'label' => $m, 'rate' => round( $rate, 2 ) ];
            }
            if ( $compare ) {
                $prev_rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE_FORMAT(action_date,'%Y-%m') m, COUNT(*) c FROM {$hist} WHERE action_type='membership_cancel' AND action_date >= %s AND action_date < %s GROUP BY m", $prev_start, $start ), ARRAY_A );
                $pmap = wp_list_pluck( $prev_rows, 'c', 'm' );
                $data['churn_prev'] = [];
                foreach ( $prev_labels as $m ) {
                    $count = isset( $pmap[ $m ] ) ? $pmap[ $m ] : 0;
                    $rate = $total_members ? ( $count / $total_members ) * 100 : 0;
                    $data['churn_prev'][] = [ 'label' => $m, 'rate' => round( $rate, 2 ) ];
                }
            }
        }

        if ( 'all' === $chart || 'prediction' === $chart ) {
            $rev_vals = isset( $data['revenue'] ) ? wp_list_pluck( $data['revenue'], 'amount' ) : [];
            $pred = 0;
            if ( $rev_vals ) {
                $pred = array_sum( array_slice( $rev_vals, -3 ) ) / min( 3, count( $rev_vals ) );
            }
            $future = $months < 1 ? strtotime( '+' . round( $months * 30 ) . ' days' ) : strtotime( '+' . $months . ' months' );
            $label = $months < 1 ? gmdate( 'Y-m-d', $future ) : gmdate( 'Y-m', $future );
            $data['prediction'] = [ 'label' => $label, 'amount' => round( $pred * $months, 2 ) ];
        }

        wp_send_json( $data );
    }
}
TTA_Ajax_BI::init();
