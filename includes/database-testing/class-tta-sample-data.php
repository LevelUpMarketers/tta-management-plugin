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

        foreach ( $members as $mem ) {
            $row = [];
            foreach ( $mem as $k => $v ) {
                $row[ $k ] = is_string( $v ) ? sanitize_text_field( $v ) : $v;
            }
            $wpdb->insert( $members_table, $row );
        }

        foreach ( $events as $index => $event ) {
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

            if ( $row['waitlistavailable'] ) {
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
                        'phone'        => sanitize_text_field( $m['phone'] ),
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
                        'details'        => '',
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
            }
        }

        TTA_Cache::flush();
    }

    public static function clear() {
        global $wpdb;
        $events_table    = $wpdb->prefix . 'tta_events';
        $tickets_table   = $wpdb->prefix . 'tta_tickets';
        $waitlist_table  = $wpdb->prefix . 'tta_waitlist';
        $members_table   = $wpdb->prefix . 'tta_members';
        $tx_table        = $wpdb->prefix . 'tta_transactions';
        $attendees_table = $wpdb->prefix . 'tta_attendees';
        $history_table   = $wpdb->prefix . 'tta_memberhistory';

        $page_ids = $wpdb->get_col( "SELECT page_id FROM {$events_table} WHERE ute_id LIKE 'sample_event_%'" );
        foreach ( $page_ids as $pid ) {
            if ( $pid && function_exists( 'wp_delete_post' ) ) {
                wp_delete_post( (int) $pid, true );
            }
        }

        $wpdb->query( "DELETE FROM {$events_table} WHERE ute_id LIKE 'sample_event_%'" );
        $wpdb->query( "DELETE FROM {$tickets_table} WHERE event_ute_id LIKE 'sample_event_%'" );
        $wpdb->query( "DELETE FROM {$waitlist_table} WHERE event_ute_id LIKE 'sample_event_%'" );
        $wpdb->query( "DELETE FROM {$attendees_table} WHERE transaction_id IN (SELECT id FROM {$tx_table} WHERE transaction_id LIKE 'sample_txn_%')" );
        $wpdb->query( "DELETE FROM {$tx_table} WHERE transaction_id LIKE 'sample_txn_%'" );
        $wpdb->query( "DELETE FROM {$history_table} WHERE action_data LIKE '%sample_txn_%'" );
        $wpdb->query( "DELETE FROM {$members_table} WHERE email LIKE 'sample_member_%@example.com'" );

        TTA_Cache::flush();
    }
}
