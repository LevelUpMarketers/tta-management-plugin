<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Partners {
    public static function init() {
        add_action( 'wp_ajax_tta_save_partner', [ __CLASS__, 'save_partner' ] );
    }

    public static function save_partner() {
        check_ajax_referer( 'tta_partner_save_action', 'tta_partner_save_nonce' );

        global $wpdb;

        $partners_table = $wpdb->prefix . 'tta_partners';

        $company_name       = tta_sanitize_text_field( wp_unslash( $_POST['company_name'] ?? '' ) );
        $contact_first_name = tta_sanitize_text_field( wp_unslash( $_POST['contact_first_name'] ?? '' ) );
        $contact_last_name  = tta_sanitize_text_field( wp_unslash( $_POST['contact_last_name'] ?? '' ) );
        $contact_phone      = tta_sanitize_text_field( wp_unslash( $_POST['contact_phone'] ?? '' ) );
        $contact_email      = tta_sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
        $licenses           = isset( $_POST['licenses'] ) ? intval( $_POST['licenses'] ) : 0;
        $licenses           = max( 0, min( 9999, $licenses ) );

        if ( empty( $company_name ) || empty( $contact_first_name ) || empty( $contact_last_name ) || empty( $contact_email ) ) {
            wp_send_json_error( [ 'message' => __( 'Company, contact name, and contact email are required.', 'tta' ) ] );
        }

        if ( ! is_email( $contact_email ) ) {
            wp_send_json_error( [ 'message' => __( 'Please provide a valid contact email address.', 'tta' ) ] );
        }

        // Generate a unique identifier for the partner record.
        $unique_identifier = '';
        for ( $attempt = 0; $attempt < 5; $attempt++ ) {
            $candidate = wp_generate_uuid4();
            $exists    = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$partners_table} WHERE uniquecompanyidentifier = %s",
                    $candidate
                )
            );
            if ( ! $exists ) {
                $unique_identifier = $candidate;
                break;
            }
        }

        if ( ! $unique_identifier ) {
            wp_send_json_error( [ 'message' => __( 'Unable to generate a unique identifier for the partner.', 'tta' ) ] );
        }

        // Create the partner record first.
        $inserted = $wpdb->insert(
            $partners_table,
            [
                'company_name'            => $company_name,
                'contact_first_name'      => $contact_first_name,
                'contact_last_name'       => $contact_last_name,
                'contact_phone'           => $contact_phone,
                'contact_email'           => $contact_email,
                'licenses'                => $licenses,
                'uniquecompanyidentifier' => $unique_identifier,
                'adminpageid'             => 0,
                'signuppageid'            => 0,
                'created_at'              => current_time( 'mysql' ),
                'updated_at'              => current_time( 'mysql' ),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
            ]
        );

        $partner_id = $wpdb->insert_id;

        if ( ! $inserted || ! $partner_id ) {
            wp_send_json_error( [ 'message' => __( 'Failed to create partner record.', 'tta' ) ] );
        }

        // Prevent duplicate WordPress accounts for the contact email.
        if ( email_exists( $contact_email ) ) {
            $wpdb->delete( $partners_table, [ 'id' => $partner_id ], [ '%d' ] );
            wp_send_json_error( [ 'message' => __( 'A WordPress user with this contact email already exists.', 'tta' ) ] );
        }

        $username = sanitize_user( $contact_email, true );
        if ( username_exists( $username ) ) {
            $username .= '_' . wp_generate_password( 4, false, false );
        }

        $userdata   = [
            'user_login'         => $username,
            'user_email'         => $contact_email,
            'user_pass'          => wp_generate_password(),
            'first_name'         => $contact_first_name,
            'last_name'          => $contact_last_name,
            'role'               => 'subscriber',
            'show_admin_bar_front' => 'false',
        ];
        $wp_user_id = wp_insert_user( $userdata );

        if ( is_wp_error( $wp_user_id ) ) {
            $wpdb->delete( $partners_table, [ 'id' => $partner_id ], [ '%d' ] );
            wp_send_json_error( [ 'message' => 'WP user creation failed: ' . $wp_user_id->get_error_message() ] );
        }

        update_user_meta( $wp_user_id, 'show_admin_bar_front', 'false' );

        // Create required pages.
        $admin_page_id = wp_insert_post(
            [
                'post_title'  => sprintf( '%s (admin)', $company_name ),
                'post_type'   => 'page',
                'post_status' => 'publish',
                'post_author' => 0,
            ],
            true
        );

        if ( is_wp_error( $admin_page_id ) ) {
            wp_delete_user( $wp_user_id );
            $wpdb->delete( $partners_table, [ 'id' => $partner_id ], [ '%d' ] );
            wp_send_json_error( [ 'message' => 'Failed to create admin page: ' . $admin_page_id->get_error_message() ] );
        }

        $login_page_id = wp_insert_post(
            [
                'post_title'  => sprintf( '%s Login', $company_name ),
                'post_type'   => 'page',
                'post_status' => 'publish',
                'post_author' => 0,
            ],
            true
        );

        if ( is_wp_error( $login_page_id ) ) {
            wp_delete_post( $admin_page_id, true );
            wp_delete_user( $wp_user_id );
            $wpdb->delete( $partners_table, [ 'id' => $partner_id ], [ '%d' ] );
            wp_send_json_error( [ 'message' => 'Failed to create login page: ' . $login_page_id->get_error_message() ] );
        }

        $updated = $wpdb->update(
            $partners_table,
            [
                'adminpageid'  => intval( $admin_page_id ),
                'signuppageid' => intval( $login_page_id ),
            ],
            [ 'id' => $partner_id ],
            [ '%d', '%d' ],
            [ '%d' ]
        );

        if ( false === $updated ) {
            wp_delete_post( $admin_page_id, true );
            wp_delete_post( $login_page_id, true );
            wp_delete_user( $wp_user_id );
            $wpdb->delete( $partners_table, [ 'id' => $partner_id ], [ '%d' ] );
            wp_send_json_error( [ 'message' => __( 'Partner created, but failed to store page references. Please try again.', 'tta' ) ] );
        }

        TTA_Cache::flush();

        wp_send_json_success(
            [
                'message'        => __( 'Partner created successfully!', 'tta' ),
                'partner_id'     => $partner_id,
                'wp_user_id'     => $wp_user_id,
                'admin_page_id'  => $admin_page_id,
                'login_page_id'  => $login_page_id,
            ]
        );
    }
}
