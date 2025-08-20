<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class TTA_Ajax_Assistance {
    public static function init() {
        add_action( 'wp_ajax_tta_send_assistance_note', [ __CLASS__, 'send_note' ] );
    }

    public static function send_note() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'tta' ) ] );
        }
        $ute  = tta_sanitize_text_field( $_POST['event_ute_id'] ?? '' );
        $note = tta_sanitize_textarea_field( $_POST['message'] ?? '' );
        if ( '' === $ute || '' === $note ) {
            wp_send_json_error( [ 'message' => __( 'Missing data.', 'tta' ) ] );
        }
        $saved = tta_save_assistance_note( get_current_user_id(), $ute, $note );
        if ( ! $saved ) {
            wp_send_json_error( [ 'message' => __( 'Unable to save note.', 'tta' ) ] );
        }
        tta_send_assistance_note_email( $ute, get_current_user_id(), $note );
        wp_send_json_success( [ 'message' => __( 'Message sent!', 'tta' ) ] );
    }
}
TTA_Ajax_Assistance::init();
