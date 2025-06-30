<?php
// includes/ajax/handlers/class-ajax-comms.php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Ajax_Comms {
    public static function init() {
        add_action( 'wp_ajax_tta_save_comm_template', [ __CLASS__, 'save_template' ] );
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
        $templates[ $key ]['email_body']    = tta_sanitize_textarea_field( $_POST['email_body'] ?? $templates[ $key ]['email_body'] );
        $templates[ $key ]['sms_text']      = tta_sanitize_textarea_field( $_POST['sms_text'] ?? $templates[ $key ]['sms_text'] );

        update_option( 'tta_comms_templates', $templates, false );

        wp_send_json_success( [ 'message' => 'Template saved.' ] );
    }
}
