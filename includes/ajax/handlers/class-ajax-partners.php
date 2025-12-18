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
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
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
        } elseif ( 'xlsx' === $extension ) {
            $rows = self::parse_xlsx_rows( $uploaded_path );
        } else {
            self::cleanup_upload( $uploaded_path );
            wp_send_json_error( [ 'message' => __( 'Unsupported file type. Please upload CSV or Excel (.xlsx) files.', 'tta' ) ] );
        }

        if ( empty( $rows ) ) {
            self::cleanup_upload( $uploaded_path );
            wp_send_json_error( [ 'message' => __( 'The uploaded file did not contain any rows.', 'tta' ) ] );
        }

        $inserted = 0;
        $skipped  = 0;
        $now      = current_time( 'mysql' );

        foreach ( $rows as $row ) {
            $first_name = tta_sanitize_text_field( $row['first_name'] ?? '' );
            $last_name  = tta_sanitize_text_field( $row['last_name'] ?? '' );
            $email      = tta_sanitize_email( $row['email'] ?? '' );

            if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || ! is_email( $email ) ) {
                $skipped++;
                continue;
            }

            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$members_table} WHERE email = %s LIMIT 1",
                    $email
                )
            );
            if ( $existing ) {
                $skipped++;
                continue;
            }

            $inserted_row = $wpdb->insert(
                $members_table,
                [
                    'wpuserid'          => 0,
                    'first_name'        => $first_name,
                    'last_name'         => $last_name,
                    'email'             => $email,
                    'partner'           => $partner['uniquecompanyidentifier'],
                    'profileimgid'      => 0,
                    'joined_at'         => $now,
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

            if ( $inserted_row ) {
                $inserted++;
            } else {
                $skipped++;
            }
        }

        TTA_Cache::flush();
        self::cleanup_upload( $uploaded_path );

        wp_send_json_success(
            [
                'message'  => sprintf(
                    /* translators: 1: inserted count, 2: skipped count */
                    __( 'Licenses processed. Added: %1$d, Skipped: %2$d', 'tta' ),
                    $inserted,
                    $skipped
                ),
                'added'    => $inserted,
                'skipped'  => $skipped,
            ]
        );
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
                    $headers = array_map( 'strtolower', array_map( 'trim', $data ) );
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
        if ( ! class_exists( 'ZipArchive' ) ) {
            return $rows;
        }

        $zip = new ZipArchive();
        if ( true !== $zip->open( $path ) ) {
            return $rows;
        }

        $sheet_xml = $zip->getFromName( 'xl/worksheets/sheet1.xml' );
        if ( ! $sheet_xml ) {
            $zip->close();
            return $rows;
        }

        $shared_strings = [];
        $shared_xml     = $zip->getFromName( 'xl/sharedStrings.xml' );
        if ( $shared_xml ) {
            $shared = simplexml_load_string( $shared_xml );
            if ( $shared && isset( $shared->si ) ) {
                foreach ( $shared->si as $index => $si ) {
                    $shared_strings[ intval( $index ) ] = (string) $si->t;
                }
            }
        }

        $sheet = simplexml_load_string( $sheet_xml );
        $zip->close();
        if ( ! $sheet || ! isset( $sheet->sheetData->row ) ) {
            return $rows;
        }

        $headers = [];
        foreach ( $sheet->sheetData->row as $row ) {
            $columns = [];
            foreach ( $row->c as $c ) {
                $ref   = (string) $c['r']; // e.g. A1
                $col   = preg_replace( '/\\d+/', '', $ref );
                $value = (string) $c->v;
                $type  = (string) $c['t'];
                if ( 's' === $type ) {
                    $value = $shared_strings[ intval( $value ) ] ?? '';
                }
                $columns[ $col ] = $value;
            }

            $row_values = array_values( $columns );
            if ( empty( $headers ) ) {
                $headers = array_map( 'strtolower', array_map( 'trim', $row_values ) );
                continue;
            }

            $mapped = self::map_row_by_headers( $headers, $row_values );
            if ( $mapped ) {
                $rows[] = $mapped;
            }
        }

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
}
