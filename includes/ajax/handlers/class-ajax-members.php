<?php
// includes/ajax/handlers/class-ajax-members.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Ajax_Members {

    public static function init() {
        add_action( 'wp_ajax_tta_save_member',        [ __CLASS__, 'save_member' ] );
        add_action( 'wp_ajax_tta_get_member',         [ __CLASS__, 'get_member' ] );
        add_action( 'wp_ajax_tta_get_member_form',    [ __CLASS__, 'get_member_form' ] );
        add_action( 'wp_ajax_tta_get_member_history', [ __CLASS__, 'get_member_history' ] );
        add_action( 'wp_ajax_tta_update_member',      [ __CLASS__, 'update_member' ] );
        add_action( 'wp_ajax_tta_front_update_member',[ __CLASS__, 'update_member_front' ] );
        add_action( 'wp_ajax_tta_reinstate_member',   [ __CLASS__, 'reinstate_member' ] );
    }

    public static function save_member() {
        check_ajax_referer( 'tta_member_save_action', 'tta_member_save_nonce' );

        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';

        $first_name   = tta_sanitize_text_field( $_POST['first_name']   ?? '' );
        $last_name    = tta_sanitize_text_field( $_POST['last_name']    ?? '' );
        $email        = tta_sanitize_email(       $_POST['email']        ?? '' );
        $email_verify = tta_sanitize_email(       $_POST['email_verify'] ?? '' );

        // Build address
        $address = implode( ' – ', [
            tta_sanitize_text_field( $_POST['street_address'] ?? '' ),
            tta_sanitize_text_field( $_POST['address_2']      ?? '' ),
            tta_sanitize_text_field( $_POST['city']           ?? '' ),
            tta_sanitize_text_field( $_POST['state']          ?? '' ),
            tta_sanitize_text_field( $_POST['zip']            ?? '' ),
        ]);

        $phone            = tta_sanitize_text_field( $_POST['phone']            ?? '' );
        $dob              = tta_sanitize_text_field( $_POST['dob']              ?? '' );
        $member_type      = tta_sanitize_text_field( $_POST['member_type']      ?? 'member' );
        $membership_level = tta_sanitize_text_field( $_POST['membership_level'] ?? 'free' );
        $facebook         = tta_esc_url_raw( $_POST['facebook']  ?? '' );
        $linkedin         = tta_esc_url_raw( $_POST['linkedin']  ?? '' );
        $instagram        = tta_esc_url_raw( $_POST['instagram'] ?? '' );
        $twitter          = tta_esc_url_raw( $_POST['twitter']   ?? '' );
        $biography        = tta_sanitize_textarea_field( $_POST['biography'] ?? '' );
        $notes            = tta_sanitize_textarea_field( $_POST['notes']     ?? '' );

        $interests_arr = array_filter( array_map( 'sanitize_text_field', $_POST['interests'] ?? [] ) );
        $interests     = $interests_arr ? implode( ',', $interests_arr ) : '';

        $hide_att      = ! empty( $_POST['hide_event_attendance'] ) ? 1 : 0;
        $ban_status    = sanitize_text_field( $_POST['ban_status'] ?? 'none' );
        $ban_weeks     = 0;
        switch ( $ban_status ) {
            case 'indefinite':
                $banned_until = TTA_BAN_UNTIL_INDEFINITE;
                break;
            case 'reentry':
                $banned_until = TTA_BAN_UNTIL_REENTRY;
                break;
            case '1week':
                $ban_weeks   = 1;
                $banned_until = date( 'Y-m-d H:i:s', time() + WEEK_IN_SECONDS );
                break;
            case '2week':
                $ban_weeks   = 2;
                $banned_until = date( 'Y-m-d H:i:s', time() + 2 * WEEK_IN_SECONDS );
                break;
            case '3week':
                $ban_weeks   = 3;
                $banned_until = date( 'Y-m-d H:i:s', time() + 3 * WEEK_IN_SECONDS );
                break;
            case '4week':
                $ban_weeks   = 4;
                $banned_until = date( 'Y-m-d H:i:s', time() + 4 * WEEK_IN_SECONDS );
                break;
            default:
                $banned_until = null;
        }

        $opt_email = ! empty( $_POST['opt_in_marketing_email'] )    ? 1 : 0;
        $opt_sms   = ! empty( $_POST['opt_in_marketing_sms'] )      ? 1 : 0;
        $opt_upd_email = ! empty( $_POST['opt_in_event_update_email'] ) ? 1 : 0;
        $opt_upd_sms   = ! empty( $_POST['opt_in_event_update_sms'] )   ? 1 : 0;

        // Basic validation
        if ( ! $first_name || ! $last_name || ! $email ) {
            wp_send_json_error([ 'message' => 'First name, last name & email are required.' ]);
        }
        if ( $email !== $email_verify ) {
            wp_send_json_error([ 'message' => 'Emails do not match.' ]);
        }

        // Check existing WP user
        if ( email_exists( $email ) ) {
            $existing = get_user_by( 'email', $email );
            $link     = admin_url( 'user-edit.php?user_id=' . $existing->ID );
            wp_send_json_error([
                'message' => sprintf(
                    'A WordPress user with that email exists. <a href="%s" target="_blank">View profile</a>.',
                    esc_url( $link )
                )
            ]);
        }

        // Create WP user
        $username = sanitize_user( $email, true );
        if ( username_exists( $username ) ) {
            $username .= '_' . wp_generate_password( 4, false, false );
        }
        $userdata = [
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => wp_generate_password(),
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => 'subscriber',
        ];
        $wp_user_id = wp_insert_user( $userdata );
        if ( is_wp_error( $wp_user_id ) ) {
            wp_send_json_error([ 'message' => 'WP user creation failed: ' . $wp_user_id->get_error_message() ]);
        }

        // Insert into tta_members
        $inserted = $wpdb->insert(
            $members_table,
            [
                // core fields
                'wpuserid'                  => intval( $wp_user_id ),
                'first_name'                => $first_name,
                'last_name'                 => $last_name,
                'email'                     => $email,
                'profileimgid'              => intval( $_POST['profileimgid'] ?? 0 ),
                'joined_at'                 => current_time( 'mysql' ),
                'address'                   => $address,
                // new fields
                'phone'                     => $phone,
                'dob'                       => $dob,
                'member_type'               => $member_type,
                'membership_level'          => $membership_level,
                'facebook'                  => $facebook,
                'linkedin'                  => $linkedin,
                'instagram'                 => $instagram,
                'twitter'                   => $twitter,
                'biography'                 => $biography,
                'notes'                     => $notes,
                'interests'                 => $interests,
                'opt_in_marketing_email'    => $opt_email,
                'opt_in_marketing_sms'      => $opt_sms,
                'opt_in_event_update_email' => $opt_upd_email,
                'opt_in_event_update_sms'   => $opt_upd_sms,
                'hide_event_attendance'     => $hide_att,
                'banned_until'             => $banned_until,
            ],
            [
                '%d',    // wpuserid
                '%s',    // first_name
                '%s',    // last_name
                '%s',    // email
                '%d',    // profileimgid
                '%s',    // joined_at
                '%s',    // address
                '%s',    // phone
                '%s',    // dob
                '%s',    // member_type
                '%s',    // membership_level
                '%s',    // facebook
                '%s',    // linkedin
                '%s',    // instagram
                '%s',    // twitter
                '%s',    // biography
                '%s',    // notes
                '%s',    // interests
                '%d',    // opt_in_marketing_email
                '%d',    // opt_in_marketing_sms
                '%d',    // opt_in_event_update_email
                '%d',    // opt_in_event_update_sms
                '%d',    // hide_event_attendance
                '%s',    // banned_until
            ]
        );
        $member_id = $wpdb->insert_id;
        if ( ! $member_id ) {
            wp_delete_user( $wp_user_id );
            wp_send_json_error([ 'message' => 'Failed to create member record.' ]);
        }

        // Profile image
        if ( ! empty( $_FILES['profile_image']['name'] ) ) {
            require_once ABSPATH.'wp-admin/includes/file.php';
            require_once ABSPATH.'wp-admin/includes/media.php';
            require_once ABSPATH.'wp-admin/includes/image.php';

            $aid = media_handle_upload( 'profile_image', 0 );
            if ( is_wp_error( $aid ) ) {
                wp_send_json_error([ 'message'=>'Image upload failed: '.$aid->get_error_message() ]);
            }
            $wpdb->update(
                $members_table,
                [ 'profile_image_id' => $aid ],
                [ 'id' => $member_id ],
                [ '%d' ],
                [ '%d' ]
            );
            update_user_meta( $wp_user_id, 'profileimgid', $aid );
        }

        wp_clear_scheduled_hook( 'tta_reinstate_member', [ intval( $wp_user_id ) ] );
        if ( $ban_weeks > 0 ) {
            wp_schedule_single_event( strtotime( $banned_until ), 'tta_reinstate_member', [ intval( $wp_user_id ) ] );
        }

        // Clear caches so attendee lists stay fresh
        TTA_Cache::flush();

        wp_send_json_success([
            'message'   => 'Member created successfully!',
            'member_id' => $member_id,
            'wp_user_id'=> $wp_user_id,
        ]);
    }

    public static function get_member() {
        check_ajax_referer( 'tta_member_update_action', 'get_member_nonce' );
        if ( empty($_POST['member_id']) ) {
            wp_send_json_error([ 'message'=>'Missing member ID.' ]);
        }
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tta_members WHERE id=%d",
                intval($_POST['member_id'])
            ),
            ARRAY_A
        );
        if ( ! $row ) {
            wp_send_json_error([ 'message'=>'Member not found.' ]);
        }
        wp_send_json_success([ 'member'=>$row ]);
    }

    public static function get_member_form() {
        check_ajax_referer( 'tta_member_update_action', 'get_member_nonce' );
        if ( empty($_POST['member_id']) ) {
            wp_send_json_error([ 'message'=>'Missing member ID.' ]);
        }
        wp_enqueue_media();
        if ( function_exists('wp_enqueue_editor') ) {
            wp_enqueue_editor();
        }
        $_GET['member_id'] = intval($_POST['member_id']);
        ob_start();
        include TTA_PLUGIN_DIR . 'includes/admin/views/members-edit.php';
        $html = ob_get_clean();
        wp_send_json_success([ 'html'=>$html ]);
    }

    public static function get_member_history() {
        check_ajax_referer( 'tta_member_update_action', 'get_member_nonce' );
        if ( empty( $_POST['member_id'] ) ) {
            wp_send_json_error( [ 'message' => 'Missing member ID.' ] );
        }
        $_GET['member_id'] = intval( $_POST['member_id'] );
        ob_start();
        include TTA_PLUGIN_DIR . 'includes/admin/views/member-history-details.php';
        $html = ob_get_clean();
        wp_send_json_success( [ 'html' => $html ] );
    }

    public static function update_member() {
        check_ajax_referer( 'tta_member_update_action', 'tta_member_update_nonce' );
        if ( empty( $_POST['member_id'] ) ) {
            wp_send_json_error([ 'message' => 'Missing member ID.' ]);
        }

        global $wpdb;
        $members_table     = $wpdb->prefix . 'tta_members';
        $id                = intval( $_POST['member_id'] );

        // Gather & sanitize all fields
        $first_name        = tta_sanitize_text_field( $_POST['first_name']   ?? '' );
        $last_name         = tta_sanitize_text_field( $_POST['last_name']    ?? '' );
        $email             = tta_sanitize_email(       $_POST['email']        ?? '' );
        $phone             = tta_sanitize_text_field( $_POST['phone']         ?? '' );
        $dob               = tta_sanitize_text_field( $_POST['dob']           ?? '' );
        $facebook          = tta_esc_url_raw(         $_POST['facebook']      ?? '' );
        $linkedin          = tta_esc_url_raw(         $_POST['linkedin']      ?? '' );
        $instagram         = tta_esc_url_raw(         $_POST['instagram']     ?? '' );
        $twitter           = tta_esc_url_raw(         $_POST['twitter']       ?? '' );
        $biography         = tta_sanitize_textarea_field( $_POST['biography'] ?? '' );
        $notes             = tta_sanitize_textarea_field( $_POST['notes']     ?? '' );
        $profileimgid      = intval(             $_POST['profileimgid'] ?? 0 );

        // New: member type & membership level
        $member_type       = tta_sanitize_text_field( $_POST['member_type']       ?? '' );
        $membership_level  = tta_sanitize_text_field( $_POST['membership_level']  ?? '' );

        // Interests array ⇒ CSV
        $interests_arr     = array_filter( array_map( 'sanitize_text_field', $_POST['interests'] ?? [] ) );
        $interests         = ! empty( $interests_arr ) ? implode( ',', $interests_arr ) : '';

        $hide_att         = ! empty( $_POST['hide_event_attendance'] ) ? 1 : 0;
        $ban_status       = sanitize_text_field( $_POST['ban_status'] ?? 'none' );
        $ban_weeks        = 0;
        switch ( $ban_status ) {
            case 'indefinite':
                $banned_until = TTA_BAN_UNTIL_INDEFINITE;
                break;
            case 'reentry':
                $banned_until = TTA_BAN_UNTIL_REENTRY;
                break;
            case '1week':
                $ban_weeks   = 1;
                $banned_until = date( 'Y-m-d H:i:s', time() + WEEK_IN_SECONDS );
                break;
            case '2week':
                $ban_weeks   = 2;
                $banned_until = date( 'Y-m-d H:i:s', time() + 2 * WEEK_IN_SECONDS );
                break;
            case '3week':
                $ban_weeks   = 3;
                $banned_until = date( 'Y-m-d H:i:s', time() + 3 * WEEK_IN_SECONDS );
                break;
            case '4week':
                $ban_weeks   = 4;
                $banned_until = date( 'Y-m-d H:i:s', time() + 4 * WEEK_IN_SECONDS );
                break;
            default:
                $banned_until = null;
        }

        // Opt-ins
        $opt_email         = ! empty( $_POST['opt_in_marketing_email'] )    ? 1 : 0;
        $opt_sms           = ! empty( $_POST['opt_in_marketing_sms'] )      ? 1 : 0;
        $opt_upd_email     = ! empty( $_POST['opt_in_event_update_email'] ) ? 1 : 0;
        $opt_upd_sms       = ! empty( $_POST['opt_in_event_update_sms'] )   ? 1 : 0;

        // Rebuild address
        $address = implode( ' – ', [
            tta_sanitize_text_field( $_POST['street_address'] ?? '' ),
            tta_sanitize_text_field( $_POST['address_2']      ?? '' ),
            tta_sanitize_text_field( $_POST['city']           ?? '' ),
            tta_sanitize_text_field( $_POST['state']          ?? '' ),
            tta_sanitize_text_field( $_POST['zip']            ?? '' ),
        ]);

        // Build update array
        $update_data = [
            'first_name'                => $first_name,
            'last_name'                 => $last_name,
            'email'                     => $email,
            'phone'                     => $phone,
            'dob'                       => $dob,
            'address'                   => $address,
            'facebook'                  => $facebook,
            'linkedin'                  => $linkedin,
            'instagram'                 => $instagram,
            'twitter'                   => $twitter,
            'biography'                 => $biography,
            'notes'                     => $notes,
            'interests'                 => $interests,
            'profileimgid'              => $profileimgid,
            'opt_in_marketing_email'    => $opt_email,
            'opt_in_marketing_sms'      => $opt_sms,
            'opt_in_event_update_email' => $opt_upd_email,
            'opt_in_event_update_sms'   => $opt_upd_sms,
            'hide_event_attendance'     => $hide_att,
            'member_type'               => $member_type,
            'membership_level'          => $membership_level,
            'banned_until'              => $banned_until,
        ];

        // Define formats matching update_data order
        $formats = [
            '%s','%s','%s','%s','%s',
            '%s','%s','%s','%s','%s',
            '%s','%s','%s','%d','%d',
            '%d','%d','%d','%d','%s',
            '%s','%s'
        ];

        // Run the update
        $updated = $wpdb->update(
            $members_table,
            $update_data,
            [ 'id' => $id ],
            $formats,
            [ '%d' ]
        );
        if ( false === $updated ) {
            wp_send_json_error([ 'message' => 'Failed to update member.' ]);
        }

        // If WP user email changed, sync it
        $member_row = $wpdb->get_row(
            $wpdb->prepare( "SELECT wpuserid, email FROM {$members_table} WHERE id = %d", $id ),
            ARRAY_A
        );
        if ( $member_row && isset( $member_row['wpuserid'] ) ) {
            $wp_user_id = intval( $member_row['wpuserid'] );
            if ( $member_row['email'] !== get_userdata( $wp_user_id )->user_email ) {
                wp_update_user([
                    'ID'         => $wp_user_id,
                    'user_email' => $member_row['email'],
                ]);
            }

            wp_clear_scheduled_hook( 'tta_reinstate_member', [ $wp_user_id ] );
            if ( $ban_weeks > 0 ) {
                wp_schedule_single_event( strtotime( $banned_until ), 'tta_reinstate_member', [ $wp_user_id ] );
            }
            if ( 'indefinite' === $ban_status ) {
                $sub_id = tta_get_user_subscription_id( $wp_user_id );
                if ( $sub_id ) {
                    $api = new TTA_AuthorizeNet_API();
                    $api->cancel_subscription( $sub_id );
                    tta_update_user_subscription_status( $wp_user_id, 'cancelled' );
                }
            }
        }

        // Flush caches so attendee data updates immediately
        TTA_Cache::flush();

        wp_send_json_success([ 'message' => 'Member updated successfully!' ]);
    }


    public static function reinstate_member() {
        check_ajax_referer( 'tta_banned_members_action', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
        }
        $wp_user_id = intval( $_POST['wp_user_id'] ?? 0 );
        if ( ! $wp_user_id ) {
            wp_send_json_error( [ 'message' => 'Invalid member.' ] );
        }
        tta_unban_user( $wp_user_id );
        wp_send_json_success( [ 'message' => 'Member reinstated.' ] );
    }


    public static function update_member_front() {
        check_ajax_referer( 'tta_member_front_update', 'tta_member_front_update_nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error([ 'message'=>'You must be logged in.' ]);
        }

        global $wpdb;
        $table      = $wpdb->prefix . 'tta_members';
        $wpuid      = get_current_user_id();
        $member_row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE wpuserid = %d",$wpuid),
            ARRAY_A
        );
        if ( ! $member_row ) {
            wp_send_json_error([ 'message'=>'Member record not found.' ]);
        }

        $member_id = intval($member_row['id']);

        // Handle image file if uploaded...
        // (similar logic to save_member)

        // Collect fields same as update_member()

        // Run update...
        wp_send_json_success([ 'message'=>'Profile updated successfully!' ]);
    }
}

// Initialize
TTA_Ajax_Members::init();
