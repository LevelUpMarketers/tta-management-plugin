<?php
// includes/ajax/handlers/class-ajax-comms.php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Ajax_Comms {
    public static function init() {
        add_action( 'wp_ajax_tta_save_comm_template', [ __CLASS__, 'save_template' ] );
        add_action( 'wp_ajax_tta_mass_emails', [ __CLASS__, 'mass_emails' ] );
        add_action( 'wp_ajax_tta_mass_send', [ __CLASS__, 'mass_send' ] );
    }

    public static function save_template() {
        check_ajax_referer( 'tta_comms_save_action', 'tta_comms_save_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );
        }

        $key = sanitize_key( $_POST['template_key'] ?? '' );
        if ( ! $key ) {
            wp_send_json_error( [ 'message' => 'Invalid template key.' ] );
        }

        $templates = tta_get_comm_templates();
        if ( ! isset( $templates[ $key ] ) ) {
            wp_send_json_error( [ 'message' => 'Template not found.' ] );
        }

        $templates[ $key ]['email_subject'] = tta_sanitize_text_field( $_POST['email_subject'] ?? $templates[ $key ]['email_subject'] );
        if ( 'checkin_broadcast' === $key ) {
            $templates[ $key ]['email_opening'] = tta_sanitize_text_field( $_POST['email_opening'] ?? ( $templates[ $key ]['email_opening'] ?? '' ) );
            $templates[ $key ]['email_closing'] = tta_sanitize_text_field( $_POST['email_closing'] ?? ( $templates[ $key ]['email_closing'] ?? '' ) );
            $templates[ $key ]['email_body']    = '';
        } else {
            $templates[ $key ]['email_body']    = tta_sanitize_textarea_field( $_POST['email_body'] ?? $templates[ $key ]['email_body'] );
        }
        $templates[ $key ]['sms_text']      = tta_sanitize_textarea_field( $_POST['sms_text'] ?? $templates[ $key ]['sms_text'] );

        update_option( 'tta_comms_templates', $templates, false );

        wp_send_json_success( [ 'message' => 'Template saved.' ] );
    }

    public static function mass_emails() {
        check_ajax_referer( 'tta_mass_email_action', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );
        }
        $ute = sanitize_text_field( $_POST['event'] ?? '' );
        if ( '' === $ute ) {
            wp_send_json_error( [ 'message' => 'Missing event.' ] );
        }
        $attendees = tta_get_event_attendees( $ute );
        $emails    = [];
        foreach ( (array) $attendees as $att ) {
            $email = sanitize_email( $att['email'] ?? '' );
            if ( $email ) {
                $emails[ strtolower( $email ) ] = $email;
            }
        }
        wp_send_json_success( [ 'emails' => array_values( $emails ) ] );
    }

    public static function mass_send() {
        check_ajax_referer( 'tta_mass_email_action', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );
        }
        $ute     = sanitize_text_field( $_POST['event'] ?? '' );
        $subject = tta_sanitize_text_field( $_POST['email_subject'] ?? '' );
        $body    = tta_sanitize_textarea_field( $_POST['email_body'] ?? '' );
        $emails  = array_filter( array_map( 'sanitize_email', preg_split( '/[\r\n,]+/', $_POST['emails'] ?? '' ) ) );
        if ( '' === $ute || '' === $subject || '' === $body || empty( $emails ) ) {
            wp_send_json_error( [ 'message' => 'Missing data.' ] );
        }
        $sent = TTA_Email_Handler::get_instance()->send_mass_email( $ute, $emails, $subject, $body );
        wp_send_json_success( [ 'message' => sprintf( __( 'Email sent to %d recipients.', 'tta' ), $sent ) ] );
    }
}
