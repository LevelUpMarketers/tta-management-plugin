<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Ajax_Email_Logs {
    public static function init() {
        add_action( 'wp_ajax_tta_email_log_recipients', [ __CLASS__, 'get_recipients' ] );
        add_action( 'wp_ajax_tta_email_log_delete', [ __CLASS__, 'delete_job' ] );
        add_action( 'wp_ajax_tta_email_clear_log', [ __CLASS__, 'clear_log' ] );
    }

    public static function get_recipients() {
        check_ajax_referer( 'tta_email_logs_action', 'nonce' );
        $event_id = intval( $_POST['event_id'] ?? 0 );
        $hook     = sanitize_text_field( $_POST['hook'] ?? '' );
        $emails   = TTA_Email_Reminders::get_recipient_emails( $event_id, $hook );
        wp_send_json_success( $emails );
    }

    public static function delete_job() {
        check_ajax_referer( 'tta_email_logs_action', 'nonce' );
        $event_id = intval( $_POST['event_id'] ?? 0 );
        $hook     = sanitize_text_field( $_POST['hook'] ?? '' );
        $template = sanitize_text_field( $_POST['template'] ?? '' );
        $args     = $template ? [ $event_id, $template ] : [ $event_id ];
        wp_clear_scheduled_hook( $hook, $args );
        wp_send_json_success();
    }

    public static function clear_log() {
        check_ajax_referer( 'tta_email_clear_action', 'nonce' );
        TTA_Email_Reminders::clear_email_log();
        wp_send_json_success();
    }
}
