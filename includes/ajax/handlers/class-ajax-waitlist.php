<?php
// includes/ajax/handlers/class-ajax-waitlist.php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class TTA_Ajax_Waitlist {
    public static function init() {
        add_action( 'wp_ajax_tta_join_waitlist', [ __CLASS__, 'join_waitlist' ] );
        add_action( 'wp_ajax_nopriv_tta_join_waitlist', [ __CLASS__, 'join_waitlist' ] );
        add_action( 'wp_ajax_tta_leave_waitlist', [ __CLASS__, 'leave_waitlist' ] );
        add_action( 'wp_ajax_nopriv_tta_leave_waitlist', [ __CLASS__, 'leave_waitlist' ] );
        add_action( 'wp_ajax_tta_remove_waitlist_entry', [ __CLASS__, 'remove_entry' ] );
    }

    public static function join_waitlist() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        global $wpdb;
        $table = $wpdb->prefix . 'tta_waitlist';

        $event_ute = sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        $ticket_id = intval( $_POST['ticket_id'] ?? 0 );
        $ticket_name = sanitize_text_field( $_POST['ticket_name'] ?? '' );
        $event_name = sanitize_text_field( $_POST['event_name'] ?? '' );
        $first = sanitize_text_field( $_POST['first_name'] ?? '' );
        $last  = sanitize_text_field( $_POST['last_name'] ?? '' );
        $email = sanitize_email( $_POST['email'] ?? '' );
        $phone = sanitize_text_field( $_POST['phone'] ?? '' );
        $opt_email = empty( $_POST['opt_email'] ) ? 0 : 1;
        $opt_sms   = empty( $_POST['opt_sms'] ) ? 0 : 1;
        $user_id   = is_user_logged_in() ? get_current_user_id() : 0;

        if ( '' === $email ) {
            wp_send_json_error( [ 'message' => 'missing_email' ] );
        }

        $wpdb->insert( $table, [
            'event_ute_id' => $event_ute,
            'ticket_id'    => $ticket_id,
            'ticket_name'  => $ticket_name,
            'event_name'   => $event_name,
            'wp_user_id'   => $user_id,
            'first_name'   => $first,
            'last_name'    => $last,
            'email'        => $email,
            'phone'        => $phone,
            'opt_in_email' => $opt_email,
            'opt_in_sms'   => $opt_sms,
        ], [ '%s','%d','%s','%s','%d','%s','%s','%s','%s','%d','%d' ] );

        if ( ! $wpdb->insert_id ) {
            wp_send_json_error( [ 'message' => 'db_error' ] );
        }
        TTA_Cache::flush();
        wp_send_json_success();
    }

    public static function leave_waitlist() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'not_logged_in' ] );
        }
        $event_ute = sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        $ticket_id = intval( $_POST['ticket_id'] ?? 0 );
        if ( '' === $event_ute || ! $ticket_id ) {
            wp_send_json_error( [ 'message' => 'missing_event' ] );
        }
        global $wpdb;
        $table = $wpdb->prefix . 'tta_waitlist';
        $deleted = $wpdb->delete(
            $table,
            [
                'event_ute_id' => $event_ute,
                'ticket_id'    => $ticket_id,
                'wp_user_id'   => get_current_user_id(),
            ],
            [ '%s', '%d', '%d' ]
        );
        if ( ! $deleted ) {
            wp_send_json_error( [ 'message' => 'not_found' ] );
        }
        TTA_Cache::flush();
        wp_send_json_success();
    }

    /**
     * Remove a waitlist entry via admin.
     */
    public static function remove_entry() {
        check_ajax_referer( 'tta_waitlist_admin_action', 'nonce' );
        $id = intval( $_POST['waitlist_id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'missing_id' ] );
        }
        global $wpdb;
        $table = $wpdb->prefix . 'tta_waitlist';
        $deleted = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
        if ( ! $deleted ) {
            wp_send_json_error( [ 'message' => 'not_found' ] );
        }
        TTA_Cache::flush();
        wp_send_json_success( [ 'message' => __( 'Waitlist entry removed.', 'tta' ) ] );
    }
}
TTA_Ajax_Waitlist::init();
