<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Partners {
    public static function init() {
        add_action( 'wp_ajax_tta_save_partner', [ __CLASS__, 'save_partner' ] );
        add_action( 'wp_ajax_tta_get_partner_form', [ __CLASS__, 'get_partner_form' ] );
        add_action( 'wp_ajax_tta_update_partner', [ __CLASS__, 'update_partner' ] );
        add_action( 'wp_ajax_tta_upload_partner_licenses', [ __CLASS__, 'upload_partner_licenses' ] );
        add_action( 'wp_ajax_tta_fetch_partner_members', [ __CLASS__, 'fetch_partner_members' ] );
        add_action( 'wp_ajax_tta_add_partner_member', [ __CLASS__, 'add_partner_member' ] );
        add_action( 'wp_ajax_tta_partner_end_employment', [ __CLASS__, 'end_member_employment' ] );
        add_action( 'wp_ajax_tta_partner_import_status', [ __CLASS__, 'partner_import_status' ] );
        add_action( 'wp_ajax_tta_partner_register', [ __CLASS__, 'partner_register' ] );
        add_action( 'wp_ajax_nopriv_tta_partner_register', [ __CLASS__, 'partner_register' ] );
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
                'wpuserid'                => 0,
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

        $partner_password = 'd3v50$VdMICfo^s4AWIbJhG5';

        $userdata   = [
            'user_login'         => $username,
            'user_email'         => $contact_email,
            'user_pass'          => $partner_password,
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
        update_post_meta( $admin_page_id, '_wp_page_template', 'partner-admin-page-template.php' );

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
        update_post_meta( $login_page_id, '_wp_page_template', 'partner-login-page-template.php' );

        $updated = $wpdb->update(
            $partners_table,
            [
                'adminpageid'  => intval( $admin_page_id ),
                'signuppageid' => intval( $login_page_id ),
                'wpuserid'     => intval( $wp_user_id ),
            ],
            [ 'id' => $partner_id ],
            [ '%d', '%d', '%d' ],
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
                'message'        => sprintf(
                    __( "Partner created successfully! This is the Partner's password: %s", 'tta' ),
                    $partner_password
                ),
                'partner_id'     => $partner_id,
                'wp_user_id'     => $wp_user_id,
                'admin_page_id'  => $admin_page_id,
                'login_page_id'  => $login_page_id,
            ]
        );
    }

    public static function get_partner_form() {
        check_ajax_referer( 'tta_partner_manage_action', 'get_partner_nonce' );

        $partner_id = isset( $_POST['partner_id'] ) ? intval( $_POST['partner_id'] ) : 0;

        if ( ! $partner_id ) {
            wp_send_json_error( [ 'message' => __( 'Missing partner ID.', 'tta' ) ] );
        }

        $_GET['partner_id'] = $partner_id;

        ob_start();
        include TTA_PLUGIN_DIR . 'includes/admin/views/partners-edit.php';
        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }

    public static function update_partner() {
        check_ajax_referer( 'tta_partner_manage_action', 'tta_partner_update_nonce' );

        $partner_id = isset( $_POST['partner_id'] ) ? intval( $_POST['partner_id'] ) : 0;

        if ( ! $partner_id ) {
            wp_send_json_error( [ 'message' => __( 'Missing partner ID.', 'tta' ) ] );
        }

        global $wpdb;

        $partners_table = $wpdb->prefix . 'tta_partners';

        $partner = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$partners_table} WHERE id = %d", $partner_id ),
            ARRAY_A
        );

        if ( ! $partner ) {
            wp_send_json_error( [ 'message' => __( 'Partner not found.', 'tta' ) ] );
        }

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

        $existing_user = get_user_by( 'email', $partner['contact_email'] );
        $new_email_user = get_user_by( 'email', $contact_email );

        if ( $new_email_user && ( ! $existing_user || $existing_user->ID !== $new_email_user->ID ) ) {
            wp_send_json_error( [ 'message' => __( 'A WordPress user with this contact email already exists.', 'tta' ) ] );
        }

        $wp_user_id = $existing_user ? intval( $existing_user->ID ) : 0;

        if ( ! empty( $partner['adminpageid'] ) ) {
            $admin_page_id     = intval( $partner['adminpageid'] );
            $current_template  = get_post_meta( $admin_page_id, '_wp_page_template', true );
            if ( 'partner-admin-page-template.php' !== $current_template ) {
                update_post_meta( $admin_page_id, '_wp_page_template', 'partner-admin-page-template.php' );
            }
        }

        if ( ! empty( $partner['signuppageid'] ) ) {
            $login_page_id    = intval( $partner['signuppageid'] );
            $current_template = get_post_meta( $login_page_id, '_wp_page_template', true );
            if ( 'partner-login-page-template.php' !== $current_template ) {
                update_post_meta( $login_page_id, '_wp_page_template', 'partner-login-page-template.php' );
            }
        }

        if ( $existing_user ) {
            $user_update = wp_update_user(
                [
                    'ID'           => $existing_user->ID,
                    'user_email'   => $contact_email,
                    'first_name'   => $contact_first_name,
                    'last_name'    => $contact_last_name,
                    'display_name' => trim( $contact_first_name . ' ' . $contact_last_name ),
                ]
            );

            if ( is_wp_error( $user_update ) ) {
                wp_send_json_error( [ 'message' => 'WP user update failed: ' . $user_update->get_error_message() ] );
            }

            $wp_user_id = intval( $existing_user->ID );
        } else {
            $username = sanitize_user( $contact_email, true );
            if ( username_exists( $username ) ) {
                $username .= '_' . wp_generate_password( 4, false, false );
            }

            $partner_password = 'd3v50$VdMICfo^s4AWIbJhG5';

            $wp_user_id = wp_insert_user(
                [
                    'user_login'           => $username,
                    'user_email'           => $contact_email,
                    'user_pass'            => $partner_password,
                    'first_name'           => $contact_first_name,
                    'last_name'            => $contact_last_name,
                    'role'                 => 'subscriber',
                    'show_admin_bar_front' => 'false',
                ]
            );

            if ( is_wp_error( $wp_user_id ) ) {
                wp_send_json_error( [ 'message' => 'WP user creation failed: ' . $wp_user_id->get_error_message() ] );
            }

            update_user_meta( $wp_user_id, 'show_admin_bar_front', 'false' );
        }

        $partner_updated = $wpdb->update(
            $partners_table,
            [
                'company_name'       => $company_name,
                'contact_first_name' => $contact_first_name,
                'contact_last_name'  => $contact_last_name,
                'contact_phone'      => $contact_phone,
                'contact_email'      => $contact_email,
                'licenses'           => $licenses,
                'wpuserid'           => $wp_user_id,
                'updated_at'         => current_time( 'mysql' ),
            ],
            [ 'id' => $partner_id ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
            ],
            [ '%d' ]
        );

        if ( false === $partner_updated ) {
            wp_send_json_error( [ 'message' => __( 'Failed to update partner.', 'tta' ) ] );
        }

        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Partner updated successfully.', 'tta' ) ] );
    }

    /**
     * Bulk create members for a partner from an uploaded CSV.
     */
    public static function upload_partner_licenses() {
        check_ajax_referer( 'tta_partner_upload_action', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in to upload licenses.', 'tta' ) ] );
        }

        $page_id = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : 0;
        if ( ! $page_id ) {
            wp_send_json_error( [ 'message' => __( 'Missing partner page.', 'tta' ) ] );
        }

        global $wpdb;
        $partners_table = $wpdb->prefix . 'tta_partners';
        $members_table  = $wpdb->prefix . 'tta_members';

        $partner = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$partners_table} WHERE adminpageid = %d LIMIT 1",
                $page_id
            ),
            ARRAY_A
        );

        if ( ! $partner ) {
            wp_send_json_error( [ 'message' => __( 'Partner not found for this page.', 'tta' ) ] );
        }

        $current_user_id = get_current_user_id();
        $can_manage      = current_user_can( 'manage_options' );
        $is_partner_user = intval( $partner['wpuserid'] ) === $current_user_id;
        if ( ! $can_manage && ! $is_partner_user ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to upload licenses for this partner.', 'tta' ) ] );
        }

        if ( empty( $_FILES['license_file'] ) || ! is_array( $_FILES['license_file'] ) ) {
            wp_send_json_error( [ 'message' => __( 'No file uploaded.', 'tta' ) ] );
        }

        $file      = $_FILES['license_file'];
        $allowed_mimes = [
            'csv'  => 'text/csv',
            'txt'  => 'text/plain',
        ];

        $upload = wp_handle_upload(
            $file,
            [
                'test_form' => false,
                'mimes'     => $allowed_mimes,
            ]
        );

        if ( isset( $upload['error'] ) ) {
            wp_send_json_error( [ 'message' => $upload['error'] ] );
        }

        $uploaded_path = $upload['file'];
        $extension     = strtolower( pathinfo( $uploaded_path, PATHINFO_EXTENSION ) );

        $rows = [];
        if ( 'csv' === $extension || 'txt' === $extension ) {
            $rows = self::parse_csv_rows( $uploaded_path );
        } else {
            self::cleanup_upload( $uploaded_path );
            wp_send_json_error( [ 'message' => __( 'Unsupported file type. Please upload a CSV file.', 'tta' ) ] );
        }

        if ( empty( $rows ) ) {
            self::cleanup_upload( $uploaded_path );
            wp_send_json_error( [ 'message' => __( 'The uploaded file did not contain any rows.', 'tta' ) ] );
        }

        $current_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$members_table} WHERE partner = %s",
                $partner['uniquecompanyidentifier']
            )
        );
        $license_limit = max( 0, intval( $partner['licenses'] ) );
        if ( $license_limit > 0 && $current_count >= $license_limit ) {
            self::cleanup_upload( $uploaded_path );
            wp_send_json_error( [ 'message' => __( 'License limit reached for this partner.', 'tta' ) ] );
        }

        $total_rows = count( $rows );
        if ( $license_limit > 0 ) {
            $total_rows = min( $total_rows, max( 0, $license_limit - $current_count ) );
        }

        $job_id = TTA_Partner_Import_Job::create_job(
            [
                'partner_id'    => intval( $partner['id'] ),
                'page_id'       => $page_id,
                'partner_uid'   => $partner['uniquecompanyidentifier'],
                'license_limit' => $license_limit,
                'file'          => $uploaded_path,
                'total_rows'    => $total_rows,
                'offset'        => 0,
            ]
        );

        wp_send_json_success(
            [
                'message'  => __( 'Import started. Please keep this page open while we process the file.', 'tta' ),
                'job_id'   => $job_id,
                'total'    => $total_rows,
            ]
        );
    }

    /**
     * Add a single partner member via form.
     */
    public static function add_partner_member() {
        check_ajax_referer( 'tta_partner_upload_action', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in to add a member.', 'tta' ) ] );
        }

        $page_id = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : 0;
        if ( ! $page_id ) {
            wp_send_json_error( [ 'message' => __( 'Missing partner page.', 'tta' ) ] );
        }

        global $wpdb;
        $partners_table = $wpdb->prefix . 'tta_partners';
        $members_table  = $wpdb->prefix . 'tta_members';

        $partner = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$partners_table} WHERE adminpageid = %d LIMIT 1",
                $page_id
            ),
            ARRAY_A
        );

        if ( ! $partner ) {
            wp_send_json_error( [ 'message' => __( 'Partner not found for this page.', 'tta' ) ] );
        }

        $current_user_id = get_current_user_id();
        $can_manage      = current_user_can( 'manage_options' );
        $is_partner_user = intval( $partner['wpuserid'] ) === $current_user_id;

        if ( ! $can_manage && ! $is_partner_user ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to add members for this partner.', 'tta' ) ] );
        }

        $first_name = tta_sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
        $last_name  = tta_sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
        $email      = tta_sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

        if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Please provide a valid first name, last name, and email.', 'tta' ) ] );
        }

        $license_limit = max( 0, intval( $partner['licenses'] ) );
        if ( $license_limit > 0 ) {
            $current_count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$members_table} WHERE partner = %s",
                    $partner['uniquecompanyidentifier']
                )
            );
            if ( $current_count >= $license_limit ) {
                wp_send_json_error( [ 'message' => __( 'License limit reached for this partner.', 'tta' ) ] );
            }
        }

        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$members_table} WHERE email = %s LIMIT 1",
                $email
            )
        );
        if ( $existing ) {
            wp_send_json_error( [ 'message' => __( 'A member with this email already exists.', 'tta' ) ] );
        }

        $inserted = $wpdb->insert(
            $members_table,
            [
                'wpuserid'          => 0,
                'first_name'        => $first_name,
                'last_name'         => $last_name,
                'email'             => $email,
                'partner'           => $partner['uniquecompanyidentifier'],
                'profileimgid'      => 0,
                'joined_at'         => current_time( 'mysql' ),
                'address'           => '',
                'phone'             => null,
                'dob'               => null,
                'member_type'       => 'member',
                'membership_level'  => 'free',
                'subscription_id'   => null,
                'subscription_status' => null,
                'facebook'          => null,
                'linkedin'          => null,
                'instagram'         => null,
                'twitter'           => null,
                'biography'         => null,
                'notes'             => null,
                'interests'         => null,
                'opt_in_marketing_email'    => 0,
                'opt_in_marketing_sms'      => 0,
                'opt_in_event_update_email' => 0,
                'opt_in_event_update_sms'   => 0,
                'hide_event_attendance'     => 0,
                'no_show_offset'            => 0,
                'banned_until'              => null,
            ],
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%s',
            ]
        );

        if ( ! $inserted ) {
            wp_send_json_error( [ 'message' => __( 'Failed to add member.', 'tta' ) ] );
        }

        TTA_Cache::flush();

        wp_send_json_success(
            [
                'message'   => __( 'Member added successfully.', 'tta' ),
                'remaining' => $license_limit > 0 ? max( 0, $license_limit - $current_count - 1 ) : null,
            ]
        );
    }

    /**
     * Register a partner-linked member into WordPress from the partner login page.
     */
    public static function partner_register() {
        check_ajax_referer( 'tta_frontend_nonce', 'nonce' );

        $first        = tta_sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
        $last         = tta_sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
        $email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $email_verify = sanitize_email( wp_unslash( $_POST['email_verify'] ?? '' ) );
        $pass         = $_POST['password'] ?? '';
        $pass_verify  = $_POST['password_verify'] ?? '';
        $page_id      = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : 0;

        if ( ! $first || ! $last || ! $email || ! $email_verify || ! $pass || ! $pass_verify ) {
            wp_send_json_error( [ 'message' => __( 'All fields are required.', 'tta' ) ] );
        }

        if ( ! $page_id ) {
            wp_send_json_error( [ 'message' => __( 'Signup page is missing partner context.', 'tta' ) ] );
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

        $format_name = static function ( $value ) {
            $value = trim( $value );
            if ( function_exists( 'mb_convert_case' ) ) {
                return mb_convert_case( $value, MB_CASE_TITLE, 'UTF-8' );
            }

            return ucwords( strtolower( $value ) );
        };

        $first = $format_name( $first );
        $last  = $format_name( $last );

        global $wpdb;
        $partners_table = $wpdb->prefix . 'tta_partners';
        $members_table  = $wpdb->prefix . 'tta_members';

        $partner = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, company_name, uniquecompanyidentifier FROM {$partners_table} WHERE signuppageid = %d LIMIT 1",
                $page_id
            ),
            ARRAY_A
        );

        if ( ! $partner ) {
            wp_send_json_error( [ 'message' => __( 'Partner information could not be found for this page.', 'tta' ) ] );
        }

        $member = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, partner, wpuserid FROM {$members_table} WHERE email = %s LIMIT 1",
                $email
            ),
            ARRAY_A
        );

        $contact_link = '<a href="/contact">' . esc_html__( 'on our Contact page', 'tta' ) . '</a>';

        if ( ! $member ) {
            wp_send_json_error(
                [
                    'message' => sprintf(
                        /* translators: 1: partner name, 2: contact link */
                        __( "Whoops - it doesn't look like %1$s gave us that email address! Is there another email address associated with you that %1$s would have given us? Please try another email address, and if you're still having trouble, reach out using the form %2$s.", 'tta' ),
                        esc_html( $partner['company_name'] ),
                        wp_kses( $contact_link, [ 'a' => [ 'href' => [] ] ] )
                    ),
                ]
            );
        }

        if ( empty( $member['partner'] ) ) {
            wp_send_json_error(
                [
                    'message' => sprintf(
                        /* translators: %s: contact link */
                        __( 'This email is not linked to a partner invite. Please reach out using the form %s.', 'tta' ),
                        wp_kses( $contact_link, [ 'a' => [ 'href' => [] ] ] )
                    ),
                ]
            );
        }

        if ( $member['partner'] !== $partner['uniquecompanyidentifier'] ) {
            wp_send_json_error(
                [
                    'message' => sprintf(
                        /* translators: 1: partner name, 2: contact link */
                        __( "We don't seem to have this email address associated with %1$s! Are you sure you're using the email address that %1$s would have provided to us? If you're still having trouble, contact us %2$s.", 'tta' ),
                        esc_html( $partner['company_name'] ),
                        wp_kses( $contact_link, [ 'a' => [ 'href' => [] ] ] )
                    ),
                ]
            );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error(
                [
                    'message' => sprintf(
                        /* translators: 1: partner name, 2: contact link */
                        __( 'Whoops - looks like an account with this email address already exists! Would %1$s have given us a different email address for you? Please try a different email address, and if you\'re still having trouble, reach out using the form %2$s.', 'tta' ),
                        esc_html( $partner['company_name'] ),
                        wp_kses( $contact_link, [ 'a' => [ 'href' => [] ] ] )
                    ),
                ]
            );
        }

        $username = sanitize_user( strstr( $email, '@', true ) );
        if ( username_exists( $username ) ) {
            $username .= '_' . wp_generate_password( 4, false, false );
        }

        $user_id = wp_insert_user(
            [
                'user_login'         => $username,
                'user_email'         => $email,
                'user_pass'          => $pass,
                'first_name'         => $first,
                'last_name'          => $last,
                'role'               => 'subscriber',
                'show_admin_bar_front' => 'false',
            ]
        );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( [ 'message' => $user_id->get_error_message() ] );
        }

        update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

        $updated = $wpdb->update(
            $members_table,
            [
                'wpuserid'            => intval( $user_id ),
                'first_name'          => $first,
                'last_name'           => $last,
                'membership_level'    => 'premium',
                'subscription_status' => 'active',
                'joined_at'           => current_time( 'mysql' ),
            ],
            [ 'id' => intval( $member['id'] ) ],
            [ '%d', '%s', '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        );

        if ( false === $updated ) {
            wp_delete_user( $user_id );
            wp_send_json_error( [ 'message' => __( 'Unable to update member record.', 'tta' ) ] );
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );
        $user_obj = get_user_by( 'id', $user_id );
        if ( $user_obj ) {
            do_action( 'wp_login', $user_obj->user_login, $user_obj );
        }

        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Account created! Redirecting…', 'tta' ) ] );
    }

    /**
     * Fetch paginated members for a partner with optional search.
     */
    public static function fetch_partner_members() {
        check_ajax_referer( 'tta_partner_fetch_action', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'tta' ) ] );
        }

        $page_id = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : 0;
        if ( ! $page_id ) {
            wp_send_json_error( [ 'message' => __( 'Missing partner page.', 'tta' ) ] );
        }

        global $wpdb;
        $partners_table = $wpdb->prefix . 'tta_partners';
        $members_table  = $wpdb->prefix . 'tta_members';

        $partner = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$partners_table} WHERE adminpageid = %d LIMIT 1",
                $page_id
            ),
            ARRAY_A
        );

        if ( ! $partner ) {
            wp_send_json_error( [ 'message' => __( 'Partner not found for this page.', 'tta' ) ] );
        }

        $current_user_id = get_current_user_id();
        $can_manage      = current_user_can( 'manage_options' );
        $is_partner_user = intval( $partner['wpuserid'] ) === $current_user_id;

        if ( ! $can_manage && ! $is_partner_user ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to view these members.', 'tta' ) ] );
        }

        $per_page = isset( $_POST['per_page'] ) ? max( 1, min( 100, intval( $_POST['per_page'] ) ) ) : 20;
        $page     = isset( $_POST['page'] ) ? max( 1, intval( $_POST['page'] ) ) : 1;
        $offset   = ( $page - 1 ) * $per_page;

        $first_name = tta_sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
        $last_name  = tta_sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
        $email      = tta_sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

        $wheres = [ 'partner = %s' ];
        $params = [ $partner['uniquecompanyidentifier'] ];

        if ( $first_name ) {
            $wheres[] = 'first_name LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $first_name ) . '%';
        }
        if ( $last_name ) {
            $wheres[] = 'last_name LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $last_name ) . '%';
        }
        if ( $email ) {
            $wheres[] = 'email LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $email ) . '%';
        }

        $where_sql = implode( ' AND ', $wheres );

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$members_table} WHERE {$where_sql}",
                $params
            )
        );

        $members = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, first_name, last_name, email, joined_at, wpuserid FROM {$members_table} WHERE {$where_sql} ORDER BY first_name ASC, last_name ASC, id ASC LIMIT %d OFFSET %d",
                array_merge( $params, [ $per_page, $offset ] )
            ),
            ARRAY_A
        );

        wp_send_json_success(
            [
                'members'   => $members,
                'total'     => intval( $total ),
                'page'      => $page,
                'per_page'  => $per_page,
                'pages'     => $per_page ? ceil( $total / $per_page ) : 0,
            ]
        );
    }

    /**
     * Mark a partner member as no longer employed.
     */
    public static function end_member_employment() {
        check_ajax_referer( 'tta_partner_member_action', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'tta' ) ] );
        }

        $page_id   = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : 0;
        $member_id = isset( $_POST['member_id'] ) ? intval( $_POST['member_id'] ) : 0;

        if ( ! $page_id || ! $member_id ) {
            wp_send_json_error( [ 'message' => __( 'Missing member details.', 'tta' ) ] );
        }

        global $wpdb;
        $partners_table = $wpdb->prefix . 'tta_partners';
        $members_table  = $wpdb->prefix . 'tta_members';

        $partner = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$partners_table} WHERE adminpageid = %d LIMIT 1",
                $page_id
            ),
            ARRAY_A
        );

        if ( ! $partner ) {
            wp_send_json_error( [ 'message' => __( 'Partner not found for this page.', 'tta' ) ] );
        }

        $current_user_id = get_current_user_id();
        $can_manage      = current_user_can( 'manage_options' );
        $is_partner_user = intval( $partner['wpuserid'] ) === $current_user_id;

        if ( ! $can_manage && ! $is_partner_user ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to update this member.', 'tta' ) ] );
        }

        $member = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, partner FROM {$members_table} WHERE id = %d AND partner = %s LIMIT 1",
                $member_id,
                $partner['uniquecompanyidentifier']
            ),
            ARRAY_A
        );

        if ( ! $member || empty( $member['partner'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Member not found for this partner.', 'tta' ) ] );
        }

        $max_length = 191;
        $prefix     = 'notemployed-';
        if ( strlen( $member['partner'] ) + strlen( $prefix ) > $max_length ) {
            $prefix = 'nle-';
        }
        $new_partner = $prefix . $member['partner'];

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$members_table} SET membership_level = %s, subscription_status = NULL, partner = %s WHERE id = %d",
                'free',
                $new_partner,
                intval( $member['id'] )
            )
        );

        if ( false === $updated ) {
            wp_send_json_error( [ 'message' => __( 'Unable to update the member record.', 'tta' ) ] );
        }

        if ( 0 === $updated ) {
            wp_send_json_error( [ 'message' => __( 'Member not found for this partner.', 'tta' ) ] );
        }

        TTA_Cache::flush();

        wp_send_json_success( [ 'message' => __( 'Member marked as no longer employed.', 'tta' ) ] );
    }

    /**
     * Parse CSV rows into an array.
     *
     * @param string $path
     * @return array
     */
    protected static function parse_csv_rows( $path ) {
        $rows    = [];
        $headers = [];

        if ( ( $handle = fopen( $path, 'r' ) ) !== false ) {
            while ( ( $data = fgetcsv( $handle ) ) !== false ) {
                if ( empty( $headers ) ) {
                    $headers = self::normalize_headers( $data );
                    if ( empty( $headers ) ) {
                        break;
                    }
                    continue;
                }
                if ( empty( array_filter( $data, 'strlen' ) ) ) {
                    continue;
                }
                $row = self::map_row_by_headers( $headers, $data );
                if ( $row ) {
                    $rows[] = $row;
                }
            }
            fclose( $handle );
        }

        return $rows;
    }

    /**
     * Parse XLSX rows (first worksheet) into an array.
     *
     * @param string $path
     * @return array
     */
    protected static function parse_xlsx_rows( $path ) {
        $rows = [];
        return $rows;
    }

    /**
     * Map a row array to first/last/email by header names.
     *
     * @param array $headers
     * @param array $values
     * @return array|null
     */
    protected static function map_row_by_headers( $headers, $values ) {
        $first_idx = array_search( 'first name', $headers, true );
        $last_idx  = array_search( 'last name', $headers, true );
        $email_idx = array_search( 'email', $headers, true );

        if ( false === $first_idx || false === $last_idx || false === $email_idx ) {
            return null;
        }

        return [
            'first_name' => $values[ $first_idx ] ?? '',
            'last_name'  => $values[ $last_idx ] ?? '',
            'email'      => $values[ $email_idx ] ?? '',
        ];
    }

    /**
     * Normalize headers (strip BOM, trim, lowercase).
     *
     * @param array $raw_headers
     * @return array
     */
    protected static function normalize_headers( $raw_headers ) {
        $normalized = [];
        foreach ( $raw_headers as $header ) {
            $header        = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $header );
            $normalized[] = strtolower( trim( $header ) );
        }
        return $normalized;
    }

    /**
     * Remove uploaded temp files.
     *
     * @param string $path
     * @return void
     */
    protected static function cleanup_upload( $path ) {
        if ( $path && file_exists( $path ) ) {
            unlink( $path );
        }
    }

    /**
     * Get import job status.
     */
    public static function partner_import_status() {
        check_ajax_referer( 'tta_partner_fetch_action', 'nonce' );

        $job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
        if ( ! $job_id ) {
            wp_send_json_error( [ 'message' => __( 'Missing job ID.', 'tta' ) ] );
        }

        $job = TTA_Partner_Import_Job::status( $job_id );
        if ( ! $job ) {
            wp_send_json_error( [ 'message' => __( 'Job not found.', 'tta' ) ] );
        }

        wp_send_json_success(
            [
                'status'    => $job['status'],
                'added'     => $job['added'],
                'skipped'   => $job['skipped'],
                'total'     => $job['total_rows'],
                'message'   => $job['message'],
                'error'     => $job['error'],
            ]
        );
    }
}
