<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Ajax_Attendance {
    public static function init() {
        add_action( 'wp_ajax_tta_get_event_attendance', [ __CLASS__, 'get_event_attendance' ] );
        add_action( 'wp_ajax_tta_set_attendance', [ __CLASS__, 'set_attendance' ] );
    }

    public static function get_event_attendance() {
        check_ajax_referer( 'tta_get_attendance_action', 'nonce' );
        $ute = tta_sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        if ( ! $ute ) {
            wp_send_json_error( [ 'message' => 'missing id' ] );
        }
        global $wpdb;
        $events_table = $wpdb->prefix . 'tta_events';
        $event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$events_table} WHERE ute_id = %s", $ute ), ARRAY_A );
        if ( ! $event ) {
            wp_send_json_error( [ 'message' => 'not found' ] );
        }
        $attendees = tta_get_event_attendees_with_status( $ute );
        ob_start();
        $GLOBALS['event'] = $event;
        $GLOBALS['attendees'] = $attendees;
        include TTA_PLUGIN_DIR . 'includes/frontend/views/attendance-list.php';
        $html = ob_get_clean();
        wp_send_json_success( [ 'html' => $html ] );
    }

    public static function set_attendance() {
        check_ajax_referer( 'tta_set_attendance_action', 'nonce' );
        $att_id = intval( $_POST['attendee_id'] ?? 0 );
        $status = sanitize_text_field( $_POST['status'] ?? 'pending' );
        if ( ! $att_id ) {
            wp_send_json_error( [ 'message' => 'missing attendee' ] );
        }
        tta_set_attendance_status( $att_id, $status );
        wp_send_json_success();
    }
}

TTA_Ajax_Attendance::init();
