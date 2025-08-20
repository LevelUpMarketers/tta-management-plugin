<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Sample_Data {
    /**
     * Attempt to fetch an existing attachment ID from the media library.
     *
     * @return int Attachment ID or 0 if none found.
     */
    private static function get_random_image_id() {
        if ( function_exists( 'get_post' ) ) {
            for ( $i = 0; $i < 5; $i++ ) {
                $id   = rand( 9637, 22072 );
                $post = get_post( $id );
                if ( $post && 'attachment' === $post->post_type ) {
                    return $id;
                }
            }
        }
        return 0;
    }
    public static function load() {
        $events   = include TTA_PLUGIN_DIR . 'database-testing/sample-events.php';
        $tickets  = include TTA_PLUGIN_DIR . 'database-testing/sample-tickets.php';
        $members  = include TTA_PLUGIN_DIR . 'database-testing/sample-members.php';

        global $wpdb;
        $events_table      = $wpdb->prefix . 'tta_events';
        $tickets_table     = $wpdb->prefix . 'tta_tickets';
        $waitlist_table    = $wpdb->prefix . 'tta_waitlist';
        $members_table     = $wpdb->prefix . 'tta_members';
        $tx_table          = $wpdb->prefix . 'tta_transactions';
        $attendees_table   = $wpdb->prefix . 'tta_attendees';
        $hist_table        = $wpdb->prefix . 'tta_memberhistory';
        $venues_table      = $wpdb->prefix . 'tta_venues';

        foreach ( $members as &$mem ) {
            $user_id = 0;
            if ( function_exists( 'username_exists' ) && function_exists( 'wp_insert_user' ) ) {
                $username = sanitize_user( strstr( $mem['email'], '@', true ) );
                $user_id  = username_exists( $username );
                if ( ! $user_id ) {
                    $userdata = [
                        'user_login' => $username,
                        'user_pass'  => $mem['password'] ?? wp_generate_password( 12 ),
                        'user_email' => sanitize_email( $mem['email'] ),
                        'first_name' => sanitize_text_field( $mem['first_name'] ),
                        'last_name'  => sanitize_text_field( $mem['last_name'] ),
                    ];
                    $maybe = wp_insert_user( $userdata );
                    if ( ! is_wp_error( $maybe ) ) {
                        $user_id = $maybe;
                    }
                }
            }

            $row = [
                'wpuserid'         => intval( $user_id ),
                'first_name'       => sanitize_text_field( $mem['first_name'] ),
                'last_name'        => sanitize_text_field( $mem['last_name'] ),
                'email'            => sanitize_email( $mem['email'] ),
                'profileimgid'     => intval( $mem['profileimgid'] ?? 0 ),
                'joined_at'        => isset( $mem['joined_at'] ) ? sanitize_text_field( $mem['joined_at'] ) : current_time( 'mysql' ),
                'address'          => sanitize_text_field( $mem['address'] ?? '' ),
                'phone'            => sanitize_text_field( $mem['phone'] ?? '' ),
                'dob'              => isset( $mem['dob'] ) ? sanitize_text_field( $mem['dob'] ) : null,
                'member_type'      => sanitize_text_field( $mem['member_type'] ?? 'member' ),
                'membership_level' => sanitize_text_field( $mem['membership_level'] ?? 'basic' ),
                'facebook'         => sanitize_text_field( $mem['facebook'] ?? '' ),
                'linkedin'         => sanitize_text_field( $mem['linkedin'] ?? '' ),
                'instagram'        => sanitize_text_field( $mem['instagram'] ?? '' ),
                'twitter'          => sanitize_text_field( $mem['twitter'] ?? '' ),
                'biography'        => function_exists( 'sanitize_textarea_field' ) ? sanitize_textarea_field( $mem['biography'] ?? '' ) : sanitize_text_field( $mem['biography'] ?? '' ),
                'notes'            => function_exists( 'sanitize_textarea_field' ) ? sanitize_textarea_field( $mem['notes'] ?? '' ) : sanitize_text_field( $mem['notes'] ?? '' ),
                'interests'        => sanitize_text_field( $mem['interests'] ?? '' ),
                'opt_in_marketing_email' => empty( $mem['opt_in_marketing_email'] ) ? 0 : 1,
                'opt_in_marketing_sms'   => empty( $mem['opt_in_marketing_sms'] ) ? 0 : 1,
                'opt_in_event_update_email' => empty( $mem['opt_in_event_email'] ) ? 0 : 1,
                'opt_in_event_update_sms'   => empty( $mem['opt_in_event_sms'] ) ? 0 : 1,
                'hide_event_attendance'  => empty( $mem['hide_event_attendance'] ) ? 0 : 1,
            ];

            $wpdb->insert( $members_table, $row );
            $mem['wpuserid']  = $user_id;
            $mem['member_id'] = $wpdb->insert_id;
        }
        unset( $mem );

        foreach ( $events as $index => $event ) {
            // Ensure a venue entry exists for this event
            $venue_exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$venues_table} WHERE name = %s LIMIT 1",
                $event['venuename']
            ) );
            if ( ! $venue_exists ) {
                $wpdb->insert( $venues_table, [
                    'name'     => sanitize_text_field( $event['venuename'] ),
                    'address'  => sanitize_text_field( $event['address'] ),
                    'venueurl' => sanitize_text_field( $event['venueurl'] ),
                    'url2'     => sanitize_text_field( $event['url2'] ),
                    'url3'     => sanitize_text_field( $event['url3'] ),
                    'url4'     => sanitize_text_field( $event['url4'] ),
                ] );
            }
            $row = [
                'ute_id'               => sanitize_text_field( $event['ute_id'] ),
                'name'                 => sanitize_text_field( $event['name'] ),
                'date'                 => sanitize_text_field( $event['date'] ),
                'baseeventcost'        => floatval( $event['baseeventcost'] ),
                'discountedmembercost' => floatval( $event['discountedmembercost'] ),
                'premiummembercost'    => floatval( $event['premiummembercost'] ),
                'address'              => sanitize_text_field( $event['address'] ),
                'type'                 => sanitize_text_field( $event['type'] ),
                'time'                 => sanitize_text_field( $event['time'] ),
                'venuename'            => sanitize_text_field( $event['venuename'] ),
                'venueurl'             => sanitize_text_field( $event['venueurl'] ),
                'url2'                 => sanitize_text_field( $event['url2'] ),
                'url3'                 => sanitize_text_field( $event['url3'] ),
                'url4'                 => sanitize_text_field( $event['url4'] ),
                'mainimageid'          => $event['mainimageid'] ? intval( $event['mainimageid'] ) : self::get_random_image_id(),
                'otherimageids'        => $event['otherimageids'] ? sanitize_text_field( $event['otherimageids'] ) : '',
                'waitlistavailable'    => intval( $event['waitlistavailable'] ),
                'discountcode'         => sanitize_text_field( $event['discountcode'] ),
                'all_day_event'        => intval( $event['all_day_event'] ),
                'virtual_event'        => intval( $event['virtual_event'] ),
                'refundsavailable'     => intval( $event['refundsavailable'] ),
                'hosts'                => sanitize_text_field( $event['hosts'] ),
                'volunteers'           => sanitize_text_field( $event['volunteers'] ),
                'created_at'           => sanitize_text_field( $event['created_at'] ),
                'updated_at'           => sanitize_text_field( $event['updated_at'] ),
            ];
            $wpdb->insert( $events_table, $row );
            $event_id = $wpdb->insert_id;

            $ticket = $tickets[ $index ] ?? null;
            if ( $ticket ) {
                $ticket_row = [
                    'event_ute_id'         => sanitize_text_field( $ticket['event_ute_id'] ),
                    'event_name'           => sanitize_text_field( $ticket['event_name'] ),
                    'ticket_name'          => sanitize_text_field( $ticket['ticket_name'] ),
                    'waitlist_id'          => intval( $ticket['waitlist_id'] ),
                    'ticketlimit'          => intval( $ticket['ticketlimit'] ),
                    'baseeventcost'        => floatval( $ticket['baseeventcost'] ),
                    'discountedmembercost' => floatval( $ticket['discountedmembercost'] ),
                    'premiummembercost'    => floatval( $ticket['premiummembercost'] ),
                ];
                $wpdb->insert( $tickets_table, $ticket_row );
                $ticket_id = $wpdb->insert_id;
                $wpdb->update( $events_table, [ 'ticket_id' => $ticket_id ], [ 'id' => $event_id ] );
            } else {
                $ticket_id = 0;
            }

            if ( $row['waitlistavailable'] && tta_waitlist_uses_csv() ) {
                $wpdb->insert( $waitlist_table, [
                    'event_ute_id' => $row['ute_id'],
                    'ticket_id'    => $ticket_id,
                    'event_name'   => $row['name'],
                    'ticket_name'  => 'General Admission',
                    'userids'      => '',
                ] );
                $wlid = $wpdb->insert_id;
                $wpdb->update( $tickets_table, [ 'waitlist_id' => $wlid ], [ 'id' => $ticket_id ] );
                $wpdb->update( $events_table, [ 'waitlist_id' => $wlid ], [ 'id' => $event_id ] );
            }

            if ( function_exists( 'wp_insert_post' ) ) {
                $page_id = wp_insert_post( [
                    'post_title'   => $row['name'],
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'meta_input'   => [ '_tta_event_id' => $event_id ],
                ] );
                if ( $page_id && ! is_wp_error( $page_id ) ) {
                    update_post_meta( $page_id, '_wp_page_template', 'event-page-template.php' );
                    $wpdb->update( $events_table, [ 'page_id' => $page_id ], [ 'id' => $event_id ] );
                }
            }

            // Create a sample transaction with attendees and member history
            if ( $ticket_id ) {
                $att_count = rand( 1, 3 );
                $attendees = [];
                for ( $a = 0; $a < $att_count; $a++ ) {
                    $m          = $members[ ( $index + $a ) % count( $members ) ];
                    $attendees[] = [
                        'first_name'   => sanitize_text_field( $m['first_name'] ),
                        'last_name'    => sanitize_text_field( $m['last_name'] ),
                        'email'        => sanitize_email( $m['email'] ),
                        'phone'        => sanitize_text_field( $m['phone'] ?? '' ),
                        'opt_in_sms'   => 1,
                        'opt_in_email' => 1,
                    ];
                }

                if ( class_exists( 'TTA_Transaction_Logger' ) ) {
                    $items = [
                        [
                            'event_ute_id'    => $event['ute_id'],
                            'event_name'      => $event['name'],
                            'ticket_id'       => $ticket_id,
                            'ticket_name'     => $ticket_row['ticket_name'],
                            'quantity'        => $att_count,
                            'price'           => floatval( $ticket['baseeventcost'] ),
                            'final_price'     => floatval( $ticket['baseeventcost'] ),
                            'baseeventcost'   => floatval( $ticket['baseeventcost'] ),
                            'discount_applied'=> false,
                            'discount_used'   => 0,
                            'discount_saved'  => 0,
                            'attendees'       => $attendees,
                        ],
                    ];

                    $amount = floatval( $ticket['baseeventcost'] ) * $att_count;
                    TTA_Transaction_Logger::log(
                        'sample_txn_' . $index,
                        $amount,
                        $items,
                        '',
                        0,
                        intval( $members[ $index % count( $members ) ]['wpuserid'] ),
                        sprintf( '%04d', rand( 1000, 9999 ) )
                    );
                    $txn_id = $wpdb->insert_id;
                } else {
                    $txn_row = [
                        'wpuserid'       => intval( $members[ $index % count( $members ) ]['wpuserid'] ),
                        'member_id'      => 0,
                        'transaction_id' => 'sample_txn_' . $index,
                        'amount'         => floatval( $ticket['baseeventcost'] ) * $att_count,
                        'refunded'       => 0,
                        'card_last4'     => sprintf( '%04d', rand( 1000, 9999 ) ),
                        'discount_code'  => '',
                        'discount_saved' => 0,
                        'details'        => wp_json_encode( $items ),
                    ];
                    $wpdb->insert( $tx_table, $txn_row );
                    $txn_id = $wpdb->insert_id;
                    foreach ( $attendees as $att ) {
                        $wpdb->insert( $attendees_table, [
                            'transaction_id' => $txn_id,
                            'ticket_id'      => $ticket_id,
                            'first_name'     => $att['first_name'],
                            'last_name'      => $att['last_name'],
                            'email'          => $att['email'],
                            'phone'          => $att['phone'],
                            'opt_in_sms'     => 1,
                            'opt_in_email'   => 1,
                            'is_member'      => 1,
                            'status'         => 'pending',
                        ] );
                    }
                }

                if ( $txn_id && $index % 5 === 0 ) {
                    $refund_amount = floatval( $ticket['baseeventcost'] );
                    $wpdb->update(
                        $tx_table,
                        [ 'refunded' => $refund_amount ],
                        [ 'id' => $txn_id ],
                        [ '%f' ],
                        [ '%d' ]
                    );

                    $att_row = null;
                    if ( method_exists( $wpdb, 'get_row' ) ) {
                        $att_row = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT id FROM {$attendees_table} WHERE transaction_id = %d LIMIT 1",
                                $txn_id
                            ),
                            ARRAY_A
                        );
                    }
                    $wpdb->insert(
                        $hist_table,
                        [
                            'member_id'   => intval( $members[ $index % count( $members ) ]['member_id'] ),
                            'wpuserid'    => intval( $members[ $index % count( $members ) ]['wpuserid'] ),
                            'event_id'    => $event_id,
                            'action_type' => 'refund',
                            'action_data' => wp_json_encode([
                                'amount'         => $refund_amount,
                                'transaction_id' => 'sample_txn_' . $index,
                                'attendee_id'    => $att_row['id'] ?? 0,
                                'cancel'         => 0,
                            ]),
                        ],
                        [ '%d','%d','%d','%s','%s' ]
                    );
                }
            }
        }

        $members_table = $wpdb->prefix . 'tta_members';
        $users_table   = $wpdb->users;

        // Populate members table with existing WordPress users
        $wpdb->query( "INSERT IGNORE INTO {$members_table} (wpuserid, first_name, last_name, email, joined_at)
SELECT
  ID AS wpuserid,
  CASE
    WHEN display_name LIKE '% %' THEN SUBSTRING_INDEX(display_name, ' ', 1)
    ELSE display_name
  END AS first_name,
  CASE
    WHEN display_name LIKE '% %' THEN SUBSTRING(display_name, LOCATE(' ', display_name) + 1)
    ELSE ''
  END AS last_name,
  user_email AS email,
  user_registered AS joined_at
FROM {$users_table}" );

        // Update membership levels and types for known accounts
        $wpdb->query( "
UPDATE {$members_table}
SET
  membership_level = CASE email
    WHEN 'tilypoquh@mailinator.com' THEN 'basic'
    WHEN 'sicuzymyt@mailinator.com' THEN 'premium'
    WHEN 'tryingtoadultrva@gmail.com' THEN 'premium'
    WHEN 'eippih@gmail.com' THEN 'premium'
    WHEN 'foreunner1618@gmail.com' THEN 'premium'
    WHEN 'mariah.payne831@gmail.com' THEN 'premium'
    WHEN 'claineryan13@gmail.com' THEN 'premium'
    WHEN 'dana.p.harrell@gmail.com' THEN 'premium'
    ELSE membership_level
  END,
  member_type = CASE email
    WHEN 'tilypoquh@mailinator.com' THEN 'member'
    WHEN 'sicuzymyt@mailinator.com' THEN 'member'
    WHEN 'tryingtoadultrva@gmail.com' THEN 'super_admin'
    WHEN 'eippih@gmail.com' THEN 'super_admin'
    WHEN 'foreunner1618@gmail.com' THEN 'admin'
    WHEN 'mariah.payne831@gmail.com' THEN 'admin'
    WHEN 'claineryan13@gmail.com' THEN 'volunteer'
    WHEN 'dana.p.harrell@gmail.com' THEN 'volunteer'
    ELSE member_type
  END
WHERE email IN (
  'tilypoquh@mailinator.com',
  'sicuzymyt@mailinator.com',
  'tryingtoadultrva@gmail.com',
  'eippih@gmail.com',
  'foreunner1618@gmail.com',
  'mariah.payne831@gmail.com',
  'claineryan13@gmail.com',
  'dana.p.harrell@gmail.com'
)" );

        TTA_Cache::flush();
    }

    public static function clear() {
        global $wpdb;

        $events_table  = $wpdb->prefix . 'tta_events';
        $archive_table = $wpdb->prefix . 'tta_events_archive';

        $page_ids = array_merge(
            $wpdb->get_col( "SELECT page_id FROM {$events_table} WHERE page_id > 0" ),
            $wpdb->get_col( "SELECT page_id FROM {$archive_table} WHERE page_id > 0" )
        );
        foreach ( $page_ids as $pid ) {
            if ( $pid && function_exists( 'wp_delete_post' ) ) {
                wp_delete_post( (int) $pid, true );
            }
        }

        $tables = [
            'events', 'events_archive', 'members', 'tickets', 'tickets_archive',
            'waitlist', 'transactions', 'attendees', 'attendees_archive',
            'memberhistory', 'venues', 'discount_codes', 'carts', 'cart_items'
        ];
        foreach ( $tables as $suffix ) {
            $table = $wpdb->prefix . 'tta_' . $suffix;
            $wpdb->query( "TRUNCATE TABLE {$table}" );
            // Fallback for test doubles that track DELETE statements.
            $wpdb->query( "DELETE FROM {$table}" );
        }

        if ( function_exists( 'get_user_by' ) && function_exists( 'wp_delete_user' ) ) {
            $emails = [
                'tilypoquh@mailinator.com',
                'sicuzymyt@mailinator.com',
                'tryingtoadultrva@gmail.com',
                'eippih@gmail.com',
                'foreunner1618@gmail.com',
                'mariah.payne831@gmail.com',
                'claineryan13@gmail.com',
                'dana.p.harrell@gmail.com',
            ];
            foreach ( $emails as $em ) {
                $u = get_user_by( 'email', $em );
                if ( $u ) {
                    wp_delete_user( $u->ID );
                }
            }

            if ( function_exists( 'get_users' ) ) {
                $users = get_users([
                    'search'         => 'sample_member_%@example.com',
                    'search_columns' => [ 'user_email' ],
                ]);
                foreach ( $users as $u ) {
                    wp_delete_user( $u->ID );
                }
            }
        }

        TTA_Cache::flush();
    }
}
