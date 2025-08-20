<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Auth {
    public static function init() {
        add_action( 'wp_ajax_tta_login', [ __CLASS__, 'login' ] );
        add_action( 'wp_ajax_nopriv_tta_login', [ __CLASS__, 'login' ] );
        add_action( 'wp_ajax_tta_register', [ __CLASS__, 'register' ] );
        add_action( 'wp_ajax_nopriv_tta_register', [ __CLASS__, 'register' ] );
    }

    public static function login() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );

        $creds = [
            'user_login'    => sanitize_user( $_POST['username'] ?? '' ),
            'user_password' => $_POST['password'] ?? '',
            'remember'      => true,
        ];
        $user = wp_signon( $creds, is_ssl() );
        if ( is_wp_error( $user ) ) {
            wp_send_json_error( [ 'message' => $user->get_error_message() ] );
        }
        wp_send_json_success();
    }

    public static function register() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );

        $first        = tta_sanitize_text_field( $_POST['first_name']    ?? '' );
        $last         = tta_sanitize_text_field( $_POST['last_name']     ?? '' );
        $email        = sanitize_email( $_POST['email']                ?? '' );
        $email_verify = sanitize_email( $_POST['email_verify']         ?? '' );
        $pass         = $_POST['password'] ?? '';
        $pass_verify  = $_POST['password_verify'] ?? '';
        if ( ! $first || ! $last || ! $email || ! $email_verify || ! $pass || ! $pass_verify ) {
            wp_send_json_error( [ 'message' => __( 'All fields are required.', 'tta' ) ] );
        }
        if ( $email !== $email_verify ) {
            wp_send_json_error( [ 'message' => __( 'Emails do not match.', 'tta' ) ] );
        }
        if ( $pass !== $pass_verify ) {
            wp_send_json_error( [ 'message' => __( 'Passwords do not match.', 'tta' ) ] );
        }
        if ( ! preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $pass ) ) {
            wp_send_json_error( [ 'message' => __( 'Password must be at least 8 characters and include upper and lower case letters and a number.', 'tta' ) ] );
        }
        if ( email_exists( $email ) || tta_get_member_row_by_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Email already in use.', 'tta' ) ] );
        }
        $username = sanitize_user( strstr( $email, '@', true ) );
        if ( username_exists( $username ) ) {
            $username .= '_' . wp_generate_password( 4, false, false );
        }
        $uid = wp_insert_user( [
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $pass,
            'first_name' => $first,
            'last_name'  => $last,
            'role'       => 'subscriber',
        ] );
        if ( is_wp_error( $uid ) ) {
            wp_send_json_error( [ 'message' => $uid->get_error_message() ] );
        }

        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';
        $inserted      = $wpdb->insert(
            $members_table,
            [
                'wpuserid'         => intval( $uid ),
                'first_name'       => $first,
                'last_name'        => $last,
                'email'            => $email,
                'joined_at'        => current_time( 'mysql' ),
                'member_type'      => 'member',
                'membership_level' => 'free',
                'subscription_status' => null,
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        if ( false === $inserted ) {
            wp_delete_user( $uid );
            wp_send_json_error( [ 'message' => __( 'Failed to create member.', 'tta' ) ] );
        }

        wp_set_current_user( $uid );
        wp_set_auth_cookie( $uid, true );
        TTA_Cache::flush();

        wp_send_json_success();
    }
}

TTA_Ajax_Auth::init();
