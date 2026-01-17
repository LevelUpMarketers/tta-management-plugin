<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_BI_Dashboard {
    public static function init() {
        add_action( 'wp_ajax_tta_bi_monthly_overview', [ __CLASS__, 'monthly_overview' ] );
        add_action( 'wp_ajax_tta_bi_comparison_overview', [ __CLASS__, 'comparison_overview' ] );
        add_action( 'wp_ajax_tta_bi_members_monthly_overview', [ __CLASS__, 'members_monthly_overview' ] );
    }

    public static function monthly_overview() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'tta' ) ], 403 );
        }

        check_ajax_referer( 'tta_bi_monthly_overview_action', 'nonce' );

        $month = sanitize_text_field( $_POST['month'] ?? '' );
        if ( ! preg_match( '/^\d{4}-\d{2}$/', $month ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid month.', 'tta' ) ], 400 );
        }

        global $wpdb;
        $table   = $wpdb->prefix . 'tta_events_archive';
        $metrics = tta_get_bi_monthly_overview_metrics( $table, $month );
        $display = tta_format_bi_monthly_overview_metrics( $metrics );

        wp_send_json_success( [ 'metrics' => $display ] );
    }

    public static function comparison_overview() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'tta' ) ], 403 );
        }

        check_ajax_referer( 'tta_bi_comparison_overview_action', 'nonce' );

        $comparison = sanitize_text_field( $_POST['comparison'] ?? '' );
        if ( ! in_array( $comparison, [ 'last_month', 'last_quarter', 'last_year', 'last_30_days', 'last_90_days', 'last_365_days' ], true ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid comparison.', 'tta' ) ], 400 );
        }

        global $wpdb;
        $table   = $wpdb->prefix . 'tta_events_archive';
        $metrics = tta_get_bi_comparison_metrics( $table, $comparison );

        wp_send_json_success( $metrics );
    }

    public static function members_monthly_overview() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'tta' ) ], 403 );
        }

        check_ajax_referer( 'tta_bi_members_monthly_overview_action', 'nonce' );

        $month = sanitize_text_field( $_POST['month'] ?? '' );
        if ( ! preg_match( '/^\d{4}-\d{2}$/', $month ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid month.', 'tta' ) ], 400 );
        }

        $metrics = tta_get_bi_membership_monthly_overview_metrics( $month );
        $display = tta_format_bi_membership_overview_metrics( $metrics );

        wp_send_json_success( [ 'metrics' => $display ] );
    }
}
