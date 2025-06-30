<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return an array of US states (abbreviation => full name).
 */
function tta_get_us_states() {
    return array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
    );
}

/**
 * Recursively unslash incoming data.
 *
 * @param mixed $value
 * @return mixed
 */
function tta_unslash( $value ) {
    if ( is_array( $value ) ) {
        return array_map( 'tta_unslash', $value );
    }
    return is_string( $value ) ? wp_unslash( $value ) : $value;
}

/**
 * Sanitize a text field allowing apostrophes from user input.
 *
 * @param mixed $value
 * @return string
 */
function tta_sanitize_text_field( $value ) {
    return sanitize_text_field( tta_unslash( $value ) );
}

/**
 * Sanitize textarea input preserving apostrophes.
 *
 * @param mixed $value
 * @return string
 */
function tta_sanitize_textarea_field( $value ) {
    return sanitize_textarea_field( tta_unslash( $value ) );
}

/**
 * Sanitize email input preserving apostrophes.
 *
 * @param mixed $value
 * @return string
 */
function tta_sanitize_email( $value ) {
    return sanitize_email( tta_unslash( $value ) );
}

/**
 * Sanitize a URL input preserving apostrophes.
 *
 * @param mixed $value
 * @return string
 */
function tta_esc_url_raw( $value ) {
    return esc_url_raw( tta_unslash( $value ) );
}

/**
 * Decode a discount string stored in the events table.
 *
 * @param string $raw
 * @return array{code:string,type:string,amount:float}
 */
function tta_parse_discount_data( $raw ) {
    $default = [ 'code' => '', 'type' => 'percent', 'amount' => 0 ];
    if ( ! $raw ) {
        return $default;
    }

    $data = json_decode( $raw, true );
    if ( is_array( $data ) && isset( $data['code'] ) ) {
        $code   = sanitize_text_field( $data['code'] );
        $type   = in_array( $data['type'], [ 'flat', 'percent' ], true ) ? $data['type'] : 'percent';
        $amount = floatval( $data['amount'] ?? 0 );
        return [
            'code'   => $code,
            'type'   => $type,
            'amount' => $amount,
        ];
    }

    // Legacy plain code string defaults to 10% discount
    return [
        'code'   => sanitize_text_field( $raw ),
        'type'   => 'percent',
        'amount' => 10,
    ];
}

/**
 * Encode discount data for storage.
 *
 * @param string $code
 * @param string $type
 * @param float  $amount
 * @return string
 */
function tta_build_discount_data( $code, $type = 'percent', $amount = 0 ) {
    $code   = sanitize_text_field( $code );
    if ( '' === $code ) {
        return '';
    }
    $type   = in_array( $type, [ 'flat', 'percent' ], true ) ? $type : 'percent';
    $amount = floatval( $amount );

    $data = [
        'code'   => $code,
        'type'   => $type,
        'amount' => $amount,
    ];

    return wp_json_encode( $data );
}

/**
 * Store a notice to display on the cart page.
 *
 * @param string $message
 */
function tta_set_cart_notice( $message ) {
    if ( ! session_id() ) {
        session_start();
    }
    $_SESSION['tta_cart_notice'] = sanitize_text_field( $message );
}

/**
 * Fetch and clear any cart notice stored in the session.
 *
 * @return string
 */
function tta_get_cart_notice() {
    if ( ! session_id() ) {
        session_start();
    }
    if ( empty( $_SESSION['tta_cart_notice'] ) ) {
        return '';
    }

    $msg = sanitize_text_field( $_SESSION['tta_cart_notice'] );
    unset( $_SESSION['tta_cart_notice'] );
    return $msg;
}

/**
 * Get count of tickets a user has already purchased for an event.
 *
 * @param int    $user_id
 * @param string $event_ute_id
 * @return int
 */
function tta_get_purchased_ticket_count( $user_id, $event_ute_id ) {
    global $wpdb;
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT action_data FROM {$wpdb->prefix}tta_memberhistory WHERE wpuserid = %d AND action_type = 'purchase'",
            $user_id
        ),
        ARRAY_A
    );
    $total = 0;
    foreach ( $rows as $row ) {
        $data = json_decode( $row['action_data'], true );
        foreach ( (array) ( $data['items'] ?? [] ) as $it ) {
            if ( ( $it['event_ute_id'] ?? '' ) === $event_ute_id ) {
                $total += intval( $it['quantity'] );
            }
        }
    }
    return $total;
}

/**
 * Retrieve attendee records for a given event.
 *
 * @param string $event_ute_id Event ute_id.
 * @return array[] Array of attendees.
 */
function tta_get_event_attendees( $event_ute_id ) {
    global $wpdb;
    $att_table       = $wpdb->prefix . 'tta_attendees';
    $att_archive     = $wpdb->prefix . 'tta_attendees_archive';
    $tickets_table   = $wpdb->prefix . 'tta_tickets';
    $tickets_archive = $wpdb->prefix . 'tta_tickets_archive';

    $sql = "(SELECT a.first_name, a.last_name, a.email, a.ticket_id
              FROM {$att_table} a
              JOIN {$tickets_table} t ON a.ticket_id = t.id
             WHERE t.event_ute_id = %s)
            UNION ALL
            (SELECT a.first_name, a.last_name, a.email, a.ticket_id
              FROM {$att_archive} a
              JOIN {$tickets_archive} t ON a.ticket_id = t.id
             WHERE t.event_ute_id = %s)
            ORDER BY last_name, first_name";

    return $wpdb->get_results(
        $wpdb->prepare( $sql, $event_ute_id, $event_ute_id ),
        ARRAY_A
    );
}

/**
 * Retrieve attendee records with check-in status for a given event.
 *
 * @param string $event_ute_id Event ute_id.
 * @return array[] Array of attendees with status.
 */
function tta_get_event_attendees_with_status( $event_ute_id ) {
    global $wpdb;
    $att_table       = $wpdb->prefix . 'tta_attendees';
    $att_archive     = $wpdb->prefix . 'tta_attendees_archive';
    $tickets_table   = $wpdb->prefix . 'tta_tickets';
    $tickets_archive = $wpdb->prefix . 'tta_tickets_archive';
    $sql = "(SELECT a.id, a.first_name, a.last_name, a.email, a.phone, a.status
               FROM {$att_table} a
               JOIN {$tickets_table} t ON a.ticket_id = t.id
              WHERE t.event_ute_id = %s)
            UNION ALL
            (SELECT a.id, a.first_name, a.last_name, a.email, a.phone, a.status
               FROM {$att_archive} a
               JOIN {$tickets_archive} t ON a.ticket_id = t.id
              WHERE t.event_ute_id = %s)
            ORDER BY last_name, first_name";

    $rows = $wpdb->get_results( $wpdb->prepare( $sql, $event_ute_id, $event_ute_id ), ARRAY_A );
    foreach ( $rows as &$r ) {
        $r['id']         = intval( $r['id'] );
        $r['first_name'] = sanitize_text_field( $r['first_name'] );
        $r['last_name']  = sanitize_text_field( $r['last_name'] );
        $r['email']      = sanitize_email( $r['email'] );
        $r['phone']      = sanitize_text_field( $r['phone'] );
        $r['status']     = sanitize_text_field( $r['status'] );
    }
    return $rows;
}

/**
 * Retrieve attendees for a specific ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @return array[] Array of attendees.
 */
function tta_get_ticket_attendees( $ticket_id ) {
    $ticket_id = intval( $ticket_id );
    if ( ! $ticket_id ) {
        return [];
    }

    $cache_key = 'ticket_attendees_' . $ticket_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $att_table   = $wpdb->prefix . 'tta_attendees';
    $att_archive = $wpdb->prefix . 'tta_attendees_archive';
    $sql = "(SELECT * FROM {$att_table} WHERE ticket_id = %d)
            UNION ALL
            (SELECT * FROM {$att_archive} WHERE ticket_id = %d)
            ORDER BY last_name, first_name";

    $rows = $wpdb->get_results( $wpdb->prepare( $sql, $ticket_id, $ticket_id ), ARRAY_A );

    $txn_ids = [];
    foreach ( $rows as $r ) {
        $txn_ids[] = intval( $r['transaction_id'] );
    }
    $txn_ids = array_unique( array_filter( $txn_ids ) );

    $price_map = [];
    if ( $txn_ids ) {
        $placeholders = implode( ',', array_fill( 0, count( $txn_ids ), '%d' ) );
        $txn_rows     = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, details FROM {$wpdb->prefix}tta_transactions WHERE id IN ($placeholders)",
                $txn_ids
            ),
            ARRAY_A
        );

        foreach ( $txn_rows as $tx ) {
            $details = json_decode( $tx['details'], true );
            if ( ! is_array( $details ) ) {
                continue;
            }
            foreach ( $details as $item ) {
                if ( intval( $item['ticket_id'] ?? 0 ) === $ticket_id ) {
                    $price_map[ intval( $tx['id'] ) ] = floatval( $item['final_price'] ?? 0 );
                    break;
                }
            }
        }
    }

    foreach ( $rows as &$r ) {
        $r['id']         = intval( $r['id'] );
        $r['first_name'] = sanitize_text_field( $r['first_name'] );
        $r['last_name']  = sanitize_text_field( $r['last_name'] );
        $r['email']      = sanitize_email( $r['email'] );
        $r['phone']      = sanitize_text_field( $r['phone'] );
        $txid            = intval( $r['transaction_id'] );
        $r['amount_paid'] = $price_map[ $txid ] ?? 0;
    }

    $ttl = empty( $rows ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $rows, $ttl );
    return $rows;
}

/**
 * Update an attendee's status.
 *
 * @param int    $attendee_id Attendee ID.
 * @param string $status      New status.
 */
function tta_set_attendance_status( $attendee_id, $status ) {
    global $wpdb;
    $att_table = $wpdb->prefix . 'tta_attendees';
    $status = in_array( $status, [ 'checked_in', 'no_show', 'pending' ], true ) ? $status : 'pending';
    $wpdb->update( $att_table, [ 'status' => $status ], [ 'id' => intval( $attendee_id ) ], [ '%s' ], [ '%d' ] );
    TTA_Cache::delete( 'attendance_status_' . intval( $attendee_id ) );
}

/**
 * Get an attendee's current check-in status.
 *
 * @param int $attendee_id Attendee ID.
 * @return string Status string.
 */
function tta_get_attendance_status( $attendee_id ) {
    $attendee_id = intval( $attendee_id );
    $cache_key   = 'attendance_status_' . $attendee_id;
    $cached      = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }
    global $wpdb;
    $att_table   = $wpdb->prefix . 'tta_attendees';
    $att_archive = $wpdb->prefix . 'tta_attendees_archive';
    $status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$att_table} WHERE id = %d", $attendee_id ) );
    if ( ! $status ) {
        $status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$att_archive} WHERE id = %d", $attendee_id ) );
    }
    if ( ! $status ) {
        $status = 'pending';
    }
    TTA_Cache::set( $cache_key, $status, 300 );
    return $status;
}

/**
 * Retrieve profile image IDs for attendees of a given event.
 *
 * @param int $event_id Event ID.
 * @return int[] Array of attachment IDs.
 */
function tta_get_event_attendee_profiles( $event_id ) {
    $event_id = intval( $event_id );
    if ( ! $event_id ) {
        return [];
    }

    $cache_key = 'event_attendee_profiles_' . $event_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table    = $wpdb->prefix . 'tta_events';
    $archive_table   = $wpdb->prefix . 'tta_events_archive';
    $att_table       = $wpdb->prefix . 'tta_attendees';
    $att_archive     = $wpdb->prefix . 'tta_attendees_archive';
    $tickets_table   = $wpdb->prefix . 'tta_tickets';
    $tickets_archive = $wpdb->prefix . 'tta_tickets_archive';
    $members_table   = $wpdb->prefix . 'tta_members';

    $event_row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT ute_id, hosts, volunteers FROM {$events_table} WHERE id = %d",
            $event_id
        ),
        ARRAY_A
    );
    if ( ! $event_row ) {
        $event_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ute_id, hosts, volunteers FROM {$archive_table} WHERE id = %d",
                $event_id
            ),
            ARRAY_A
        );
    }
    $ute_id     = $event_row['ute_id'] ?? null;
    $host_names = array_filter( array_map( 'trim', explode( ',', $event_row['hosts'] ?? '' ) ) );
    $vol_names  = array_filter( array_map( 'trim', explode( ',', $event_row['volunteers'] ?? '' ) ) );
    $host_lower = array_map( 'strtolower', $host_names );
    $vol_lower  = array_map( 'strtolower', $vol_names );

    if ( ! $ute_id ) {
        TTA_Cache::set( $cache_key, [], 60 );
        return [];
    }

    $sql = "(SELECT a.email,
                    COALESCE(m.first_name, a.first_name) AS first_name,
                    COALESCE(m.last_name,  a.last_name)  AS last_name,
                    m.profileimgid,
                    m.hide_event_attendance,
                    m.membership_level
               FROM {$att_table} a
               JOIN {$tickets_table} t ON a.ticket_id = t.id
               LEFT JOIN {$members_table} m ON a.email = m.email
              WHERE t.event_ute_id = %s)
            UNION ALL
            (SELECT a.email,
                    COALESCE(m.first_name, a.first_name) AS first_name,
                    COALESCE(m.last_name,  a.last_name)  AS last_name,
                    m.profileimgid,
                    m.hide_event_attendance,
                    m.membership_level
               FROM {$att_archive} a
               JOIN {$tickets_archive} t ON a.ticket_id = t.id
               LEFT JOIN {$members_table} m ON a.email = m.email
              WHERE t.event_ute_id = %s)";

    $rows = $wpdb->get_results( $wpdb->prepare( $sql, $ute_id, $ute_id ), ARRAY_A );

    $profiles = [];
    foreach ( $rows as $row ) {
        $email = sanitize_email( $row['email'] );
        if ( isset( $profiles[ $email ] ) ) {
            continue;
        }
        $hide   = ! empty( $row['hide_event_attendance'] );
        $fn     = sanitize_text_field( $row['first_name'] ?? '' );
        $ln     = sanitize_text_field( $row['last_name']  ?? '' );
        $name   = trim( $fn . ' ' . $ln );
        $lower  = strtolower( $name );
        $profiles[ $email ] = [
            'first_name'       => $hide ? '' : $fn,
            'last_name'        => $hide ? '' : $ln,
            'img_id'           => $hide ? 0 : intval( $row['profileimgid'] ),
            'hide'             => $hide,
            'membership_level' => $row['membership_level'] ?? 'free',
            'is_host'          => in_array( $lower, $host_lower, true ),
            'is_volunteer'     => in_array( $lower, $vol_lower, true ),
        ];
    }

    $profiles = array_values( $profiles );

    $ttl = empty( $profiles ) ? 60 : 600;
    TTA_Cache::set( $cache_key, $profiles, $ttl );

    return $profiles;
}

function tta_get_event_attendee_image_ids( $event_id ) {
    $profiles = tta_get_event_attendee_profiles( $event_id );
    $ids = array_map( function( $p ) { return intval( $p['img_id'] ); }, $profiles );
    return array_values( array_filter( $ids ) );
}

/**
 * Convert a membership level slug to a human readable label.
 *
 * @param string $level Level slug (free, basic, premium).
 * @return string
 */
function tta_get_membership_label( $level ) {
    switch ( strtolower( $level ) ) {
        case 'basic':
            return __( 'Basic Member', 'tta' );
        case 'premium':
            return __( 'Premium Member', 'tta' );
        default:
            return __( 'Free Member', 'tta' );
    }
}

/**
 * Get the monthly price for a membership level.
 *
 * @param string $level Level slug (basic or premium).
 * @return float
 */
function tta_get_membership_price( $level ) {
    switch ( strtolower( $level ) ) {
        case 'premium':
            return TTA_PREMIUM_MEMBERSHIP_PRICE;
        case 'basic':
            return TTA_BASIC_MEMBERSHIP_PRICE;
        default:
            return 0;
    }
}

/**
 * Update a member's subscription level.
 *
 * @param int    $wp_user_id WordPress user ID.
 * @param string $level      New membership level.
 */
function tta_update_user_membership_level( $wp_user_id, $level, $subscription_id = null, $subscription_status = null ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $level = in_array( $level, [ 'free', 'basic', 'premium' ], true ) ? $level : 'free';
    $data   = [ 'membership_level' => $level ];
    $format = [ '%s' ];
    if ( null !== $subscription_id ) {
        $data['subscription_id'] = sanitize_text_field( $subscription_id );
        $format[] = '%s';
        if ( null === $subscription_status ) {
            $subscription_status = 'active';
        }
    }
    if ( null !== $subscription_status ) {
        $data['subscription_status'] = sanitize_text_field( $subscription_status );
        $format[] = '%s';
    }
    $wpdb->update(
        $members_table,
        $data,
        [ 'wpuserid' => intval( $wp_user_id ) ],
        $format,
        [ '%d' ]
    );
}

/**
 * Get a member's subscription ID.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return string|null
 */
function tta_get_user_subscription_id( $wp_user_id ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $sub = $wpdb->get_var( $wpdb->prepare( "SELECT subscription_id FROM {$members_table} WHERE wpuserid = %d", intval( $wp_user_id ) ) );
    return $sub ?: null;
}

/**
 * Update a member's subscription ID.
 *
 * @param int    $wp_user_id WordPress user ID.
 * @param string $subscription_id Authorize.Net subscription ID.
 */
function tta_update_user_subscription_id( $wp_user_id, $subscription_id ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $wpdb->update(
        $members_table,
        [ 'subscription_id' => sanitize_text_field( $subscription_id ) ],
        [ 'wpuserid' => intval( $wp_user_id ) ],
        [ '%s' ],
        [ '%d' ]
    );
}

/**
 * Get a member's subscription status.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return string|null
 */
function tta_get_user_subscription_status( $wp_user_id ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $status = $wpdb->get_var( $wpdb->prepare( "SELECT subscription_status FROM {$members_table} WHERE wpuserid = %d", intval( $wp_user_id ) ) );
    return $status ?: null;
}

/**
 * Update a member's subscription status.
 *
 * @param int    $wp_user_id WordPress user ID.
 * @param string $status     Status string.
 */
function tta_update_user_subscription_status( $wp_user_id, $status ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $wpdb->update(
        $members_table,
        [ 'subscription_status' => sanitize_text_field( $status ) ],
        [ 'wpuserid' => intval( $wp_user_id ) ],
        [ '%s' ],
        [ '%d' ]
    );
}

/**
 * Get the timestamp until which a user is banned.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return string|null MySQL datetime string or null if not banned.
 */
function tta_get_user_banned_until( $wp_user_id ) {
    $cache_key = 'banned_until_' . intval( $wp_user_id );
    return TTA_Cache::remember( $cache_key, function() use ( $wp_user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tta_members';
        return $wpdb->get_var( $wpdb->prepare( "SELECT banned_until FROM {$table} WHERE wpuserid = %d", intval( $wp_user_id ) ) );
    }, 300 );
}

/**
 * Determine if a user is currently banned.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return bool
 */
function tta_user_is_banned( $wp_user_id ) {
    $until = tta_get_user_banned_until( $wp_user_id );
    if ( ! $until ) {
        return false;
    }
    $timestamp = strtotime( $until );
    return $timestamp && $timestamp > time();
}

/**
 * Retrieve the last four digits of a subscription's credit card.
 *
 * The data is cached for ten minutes to limit API calls.
 *
 * @param string $subscription_id Authorize.Net subscription ID.
 * @return string Empty string on failure.
 */
function tta_get_subscription_card_last4( $subscription_id ) {
    if ( ! $subscription_id ) {
        return '';
    }
    $cache_key = 'sub_last4_' . $subscription_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }
    $api  = new TTA_AuthorizeNet_API();
    $info = $api->get_subscription_details( $subscription_id );
    if ( $info['success'] ) {
        TTA_Cache::set( $cache_key, $info['card_last4'], 600 );
        return $info['card_last4'];
    }
    return '';
}

/**
 * Retrieve recent transactions for a subscription.
 *
 * @param string $subscription_id Authorize.Net subscription ID.
 * @return array[] { id:string, date:string, amount:float }
 */
function tta_get_subscription_transactions( $subscription_id ) {
    if ( ! $subscription_id ) {
        return [];
    }
    $cache_key = 'sub_tx_' . $subscription_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    $api  = new TTA_AuthorizeNet_API();
    $info = $api->get_subscription_details( $subscription_id, true );
    if ( ! $info['success'] ) {
        return [];
    }
    $txns = $info['transactions'] ?? [];
    TTA_Cache::set( $cache_key, $txns, 600 );
    return $txns;
}

/**
 * Format a raw address string from the events table.
 *
 * @param string $raw Raw address ("street - addr2 - city - state - zip").
 * @return string Formatted address.
 */
function tta_format_address( $raw ) {
    $parts  = preg_split( '/\s*[-–]\s*/u', $raw );
    $street = trim( $parts[0] ?? '' );
    $addr2  = trim( $parts[1] ?? '' );
    $city   = trim( $parts[2] ?? '' );
    $state  = trim( $parts[3] ?? '' );
    $zip    = trim( $parts[4] ?? '' );

    $street_full    = $street . ( $addr2 ? ' ' . $addr2 : '' );
    $city_state_zip = $city . ( $state || $zip ? ', ' : '' ) . $state . ( $zip ? ' ' . $zip : '' );

    return trim( $street_full . ( $street_full && $city_state_zip ? ' ' : '' ) . $city_state_zip );
}

/**
 * Parse a raw address string into components.
 *
 * The database stores addresses separated by either a hyphen or an en dash.
 * This helper splits on both characters so older rows continue to load
 * correctly.
 *
 * @param string $raw Raw address string.
 * @return array{street:string,address2:string,city:string,state:string,zip:string}
 */
function tta_parse_address( $raw ) {
    $parts = preg_split( '/\s*[-–]\s*/u', $raw );
    return [
        'street'   => trim( $parts[0] ?? '' ),
        'address2' => trim( $parts[1] ?? '' ),
        'city'     => trim( $parts[2] ?? '' ),
        'state'    => trim( $parts[3] ?? '' ),
        'zip'      => trim( $parts[4] ?? '' ),
    ];
}

/**
 * Format an event date for display in communications.
 *
 * @param string $date Date in YYYY-MM-DD format.
 * @return string Human readable date.
 */
function tta_format_event_date( $date ) {
    $ts = strtotime( $date );
    if ( ! $ts ) {
        return '';
    }
    return date_i18n( 'F jS, Y', $ts );
}

/**
 * Format an event time range for display in communications.
 *
 * @param string $range Time range in "HH:MM|HH:MM" format.
 * @return string Formatted time range.
 */
function tta_format_event_time( $range ) {
    $parts = explode( '|', $range );
    $start = trim( $parts[0] ?? '' );
    $end   = trim( $parts[1] ?? '' );

    $out = '';
    if ( $start ) {
        $ts  = strtotime( $start );
        $out = $ts ? date_i18n( 'g:i a', $ts ) : $start;
    }
    if ( $end ) {
        $ts2 = strtotime( $end );
        $out .= $out ? ' - ' : '';
        $out .= $ts2 ? date_i18n( 'g:i a', $ts2 ) : $end;
    }
    return trim( $out );
}

/**
 * Retrieve upcoming events purchased by a user.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array[] List of events with transaction details.
 */
function tta_get_member_upcoming_events( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return [];
    }

    $cache_key = 'upcoming_events_' . $wp_user_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $hist_table    = $wpdb->prefix . 'tta_memberhistory';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT mh.action_data, mh.event_id, e.name, e.page_id, e.mainimageid, e.date, e.time, e.address, e.type, e.refundsavailable
               FROM {$hist_table} mh
               JOIN {$events_table} e ON mh.event_id = e.id
              WHERE mh.wpuserid = %d
                AND mh.action_type = 'purchase'
                AND e.date >= %s
              ORDER BY e.date ASC",
            $wp_user_id,
            current_time( 'Y-m-d' )
        ),
        ARRAY_A
    );

    $events = [];
    foreach ( $rows as $row ) {
        $data = json_decode( $row['action_data'], true );
        if ( ! is_array( $data ) ) {
            continue;
        }
        $events[] = [
            'event_id'       => intval( $row['event_id'] ),
            'name'           => sanitize_text_field( $row['name'] ),
            'page_id'        => intval( $row['page_id'] ),
            'image_id'       => intval( $row['mainimageid'] ),
            'date'           => $row['date'],
            'time'           => $row['time'],
            'address'        => sanitize_text_field( $row['address'] ),
            'event_type'     => sanitize_text_field( $row['type'] ),
            'refunds'        => intval( $row['refundsavailable'] ),
            'transaction_id' => $data['transaction_id'] ?? '',
            'amount'         => floatval( $data['amount'] ?? 0 ),
            'items'          => $data['items'] ?? [],
        ];
    }

    $ttl = empty( $events ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $events, $ttl );

    return $events;
}

/**
 * Retrieve past events purchased by a user.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array[] List of events with transaction details.
 */
function tta_get_member_past_events( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return [];
    }

    $cache_key = 'past_events_' . $wp_user_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';
    $hist_table    = $wpdb->prefix . 'tta_memberhistory';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT mh.action_data, mh.event_id,
                    COALESCE(e.name, a.name) AS name,
                    COALESCE(e.page_id, a.page_id) AS page_id,
                    COALESCE(e.mainimageid, a.mainimageid) AS mainimageid,
                    COALESCE(e.date, a.date) AS date,
                    COALESCE(e.time, a.time) AS time,
                    COALESCE(e.address, a.address) AS address,
                    COALESCE(e.type, a.type) AS type,
                    COALESCE(e.refundsavailable, a.refundsavailable) AS refunds
               FROM {$hist_table} mh
               LEFT JOIN {$events_table} e ON mh.event_id = e.id
               LEFT JOIN {$archive_table} a ON mh.event_id = a.id
              WHERE mh.wpuserid = %d
                AND mh.action_type = 'purchase'
                AND COALESCE(e.date, a.date) < %s
              ORDER BY COALESCE(e.date, a.date) DESC",
            $wp_user_id,
            current_time( 'Y-m-d' )
        ),
        ARRAY_A
    );

    $events = [];
    foreach ( $rows as $row ) {
        $data = json_decode( $row['action_data'], true );
        if ( ! is_array( $data ) ) {
            continue;
        }
        $events[] = [
            'event_id'       => intval( $row['event_id'] ),
            'name'           => sanitize_text_field( $row['name'] ),
            'page_id'        => intval( $row['page_id'] ),
            'image_id'       => intval( $row['mainimageid'] ),
            'date'           => $row['date'],
            'time'           => $row['time'],
            'address'        => sanitize_text_field( $row['address'] ),
            'event_type'     => sanitize_text_field( $row['type'] ),
            'refunds'        => intval( $row['refunds'] ),
            'transaction_id' => $data['transaction_id'] ?? '',
            'amount'         => floatval( $data['amount'] ?? 0 ),
            'items'          => $data['items'] ?? [],
        ];
    }

    $ttl = empty( $events ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $events, $ttl );

    return $events;
}

/**
 * Summarize a member's purchase and attendance history.
 *
 * @param int $member_id Member ID.
 * @return array Summary data.
 */
function tta_get_member_history_summary( $member_id ) {
    $member_id = intval( $member_id );
    if ( ! $member_id ) {
        return [
            'total_spent'   => 0,
            'events'        => 0,
            'attended'      => 0,
            'no_show'       => 0,
            'refunds'       => 0,
            'cancellations' => 0,
            'transactions'  => [],
        ];
    }

    $cache_key = 'member_hist_sum_' . $member_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $hist_table    = $wpdb->prefix . 'tta_memberhistory';
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';
    $tx_table      = $wpdb->prefix . 'tta_transactions';
    $att_table     = $wpdb->prefix . 'tta_attendees';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT mh.action_data, mh.event_id,
                    COALESCE(e.name, a.name)   AS name,
                    COALESCE(e.date, a.date)   AS date,
                    COALESCE(e.time, a.time)   AS time,
                    COALESCE(e.address, a.address) AS address
               FROM {$hist_table} mh
               LEFT JOIN {$events_table} e ON mh.event_id = e.id
               LEFT JOIN {$archive_table} a ON mh.event_id = a.id
              WHERE mh.member_id = %d
                AND mh.action_type = 'purchase'
              ORDER BY COALESCE(e.date, a.date) DESC",
            $member_id
        ),
        ARRAY_A
    );

    $summary = [
        'total_spent'   => 0,
        'events'        => 0,
        'attended'      => 0,
        'no_show'       => 0,
        'refunds'       => 0,
        'cancellations' => 0,
        'transactions'  => [],
    ];

    $event_ids = [];
    foreach ( $rows as $row ) {
        $data   = json_decode( $row['action_data'], true );
        $amount = floatval( $data['amount'] ?? 0 );
        $summary['total_spent'] += $amount;
        $event_ids[] = intval( $row['event_id'] );
        $summary['transactions'][] = [
            'event_id'       => intval( $row['event_id'] ),
            'name'           => sanitize_text_field( $row['name'] ),
            'date'           => $row['date'],
            'time'           => $row['time'],
            'address'        => sanitize_text_field( $row['address'] ),
            'amount'         => $amount,
            'transaction_id' => $data['transaction_id'] ?? '',
        ];
    }

    $summary['events'] = count( array_unique( $event_ids ) );

    $status_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT a.status FROM {$att_table} a
             JOIN {$tx_table} t ON a.transaction_id = t.id
            WHERE t.member_id = %d",
            $member_id
        ),
        ARRAY_A
    );
    foreach ( $status_rows as $r ) {
        if ( 'checked_in' === $r['status'] ) {
            $summary['attended']++;
        } elseif ( 'no_show' === $r['status'] ) {
            $summary['no_show']++;
        }
    }

    $summary['refunds'] = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$hist_table} WHERE member_id = %d AND action_type = 'refund_request'",
        $member_id
    ) );

    $summary['cancellations'] = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$hist_table} WHERE member_id = %d AND action_type = 'cancel_request'",
        $member_id
    ) );

    TTA_Cache::set( $cache_key, $summary, 300 );
    return $summary;
}

/**
 * Retrieve a member's full billing history including subscription charges.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array[] { date:string, description:string, amount:float, url?:string }
 */
function tta_get_member_billing_history( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return [];
    }

    $cache_key = 'billing_hist_' . $wp_user_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $tx_table = $wpdb->prefix . 'tta_transactions';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT transaction_id, amount, details, created_at FROM {$tx_table} WHERE wpuserid = %d ORDER BY created_at DESC",
            $wp_user_id
        ),
        ARRAY_A
    );

    $history = [];
    foreach ( $rows as $row ) {
        $items = json_decode( $row['details'], true );
        if ( ! is_array( $items ) ) {
            continue;
        }
        foreach ( $items as $it ) {
            $name    = $it['event_name'] ?? ( $it['membership'] ?? '' );
            $price   = floatval( $it['final_price'] ?? 0 ) * intval( $it['quantity'] ?? 1 );
            $page_id = intval( $it['page_id'] ?? 0 );
            if ( ! $page_id && ! empty( $it['event_ute_id'] ) ) {
                $events_table  = $wpdb->prefix . 'tta_events';
                $archive_table = $wpdb->prefix . 'tta_events_archive';
                $page_id       = (int) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT page_id FROM {$events_table} WHERE ute_id = %s UNION SELECT page_id FROM {$archive_table} WHERE ute_id = %s LIMIT 1",
                        $it['event_ute_id'],
                        $it['event_ute_id']
                    )
                );
            }
            $url = '';
            if ( $page_id && function_exists( 'get_permalink' ) ) {
                $url = get_permalink( $page_id );
            }

            $history[] = [
                'date'        => $row['created_at'],
                'description' => sanitize_text_field( $name ),
                'amount'      => $price,
                'url'         => $url,
            ];
        }
    }

    $sub_id = tta_get_user_subscription_id( $wp_user_id );
    if ( $sub_id ) {
        foreach ( tta_get_subscription_transactions( $sub_id ) as $sub_tx ) {
            $label = __( 'Membership Charge', 'tta' );
            $history[] = [
                'date'        => $sub_tx['date'],
                'description' => $label,
                'amount'      => floatval( $sub_tx['amount'] ),
            ];
        }
    }

    usort( $history, function ( $a, $b ) {
        return strtotime( $b['date'] ) - strtotime( $a['date'] );
    } );

    TTA_Cache::set( $cache_key, $history, 300 );
    return $history;
}

/**
 * Retrieve the next upcoming event.
 *
 * @return array|null {
 *     @type int    $id      Event ID.
 *     @type string $name    Event name.
 *     @type string $date    Event date (Y-m-d).
 *     @type string $time    Event time string.
 *     @type string $address Formatted address string.
 *     @type int    $page_id Page ID for event page.
 * }
 */
function tta_get_next_event() {
    $cache_key = 'tta_next_event';
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table = $wpdb->prefix . 'tta_events';

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, name, date, time, address, page_id, type, venuename, venueurl, baseeventcost, discountedmembercost, premiummembercost FROM {$events_table} WHERE date >= %s ORDER BY date ASC, time ASC LIMIT 1",
            current_time( 'Y-m-d' )
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        TTA_Cache::set( $cache_key, null, 60 );
        return null;
    }

    $event = [
        'id'                 => intval( $row['id'] ),
        'name'               => sanitize_text_field( $row['name'] ),
        'date'               => $row['date'],
        'time'               => $row['time'],
        'address'            => tta_format_address( $row['address'] ),
        'page_id'            => intval( $row['page_id'] ),
        'type'               => sanitize_text_field( $row['type'] ),
        'venue_name'         => sanitize_text_field( $row['venuename'] ),
        'venue_url'          => esc_url_raw( $row['venueurl'] ),
        'base_cost'          => floatval( $row['baseeventcost'] ),
        'member_cost'        => floatval( $row['discountedmembercost'] ),
        'premium_cost'       => floatval( $row['premiummembercost'] ),
        'date_formatted'     => tta_format_event_date( $row['date'] ),
        'time_formatted'     => tta_format_event_time( $row['time'] ),
    ];

    TTA_Cache::set( $cache_key, $event, 300 );
    return $event;
}

/**
 * Retrieve event details for email templates.
 *
 * @param string $event_ute_id Event ute_id.
 * @return array Event data including page_url and costs.
 */
function tta_get_event_for_email( $event_ute_id ) {
    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return [];
    }

    $cache_key = 'email_event_' . $event_ute_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, name, date, time, address, page_id, type, venuename, venueurl, baseeventcost, discountedmembercost, premiummembercost FROM {$events_table} WHERE ute_id = %s",
            $event_ute_id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, name, date, time, address, page_id, type, venuename, venueurl, baseeventcost, discountedmembercost, premiummembercost FROM {$archive_table} WHERE ute_id = %s",
                $event_ute_id
            ),
            ARRAY_A
        );
    }

    if ( ! $row ) {
        TTA_Cache::set( $cache_key, [], 60 );
        return [];
    }

    $event = [
        'id'           => intval( $row['id'] ),
        'name'         => sanitize_text_field( $row['name'] ),
        'date'         => $row['date'],
        'time'         => $row['time'],
        'address'      => tta_format_address( $row['address'] ),
        'page_id'      => intval( $row['page_id'] ),
        'page_url'     => get_permalink( intval( $row['page_id'] ) ),
        'type'         => sanitize_text_field( $row['type'] ),
        'venue_name'   => sanitize_text_field( $row['venuename'] ),
        'venue_url'    => esc_url_raw( $row['venueurl'] ),
        'base_cost'    => floatval( $row['baseeventcost'] ),
        'member_cost'  => floatval( $row['discountedmembercost'] ),
        'premium_cost' => floatval( $row['premiummembercost'] ),
    ];

    TTA_Cache::set( $cache_key, $event, 300 );
    return $event;
}

/**
 * Get the remaining ticket count for an upcoming event.
 *
 * @param string $event_ute_id Event ute_id.
 * @return int Remaining tickets.
 */
function tta_get_remaining_ticket_count( $event_ute_id ) {
    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return 0;
    }

    $cache_key = 'tickets_remaining_' . $event_ute_id;
    $remaining = TTA_Cache::get( $cache_key );
    if ( false !== $remaining ) {
        return intval( $remaining );
    }

    global $wpdb;
    $tickets_table = $wpdb->prefix . 'tta_tickets';
    $remaining     = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT SUM(ticketlimit) FROM {$tickets_table} WHERE event_ute_id = %s",
        $event_ute_id
    ) );

    $remaining = max( 0, $remaining );
    TTA_Cache::set( $cache_key, $remaining, 60 );
    return $remaining;
}

/**
 * Retrieve upcoming events with pagination.
 *
 * @param int $paged    Page number (1-indexed).
 * @param int $per_page Events per page.
 * @return array{events:array[],total:int}
 */
function tta_get_upcoming_events( $paged = 1, $per_page = 5 ) {
    $paged    = max( 1, intval( $paged ) );
    $per_page = max( 1, intval( $per_page ) );

    $cache_key = 'upcoming_events_' . $paged . '_' . $per_page;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table = $wpdb->prefix . 'tta_events';
    $offset       = ( $paged - 1 ) * $per_page;

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS * FROM {$events_table} WHERE date >= %s ORDER BY date ASC LIMIT %d, %d",
            current_time( 'Y-m-d' ),
            $offset,
            $per_page
        ),
        ARRAY_A
    );

    $total = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );

    $events = [];
    foreach ( $rows as $row ) {
        $events[] = [
            'id'            => intval( $row['id'] ),
            'ute_id'        => sanitize_text_field( $row['ute_id'] ),
            'name'          => sanitize_text_field( $row['name'] ),
            'date'          => $row['date'],
            'time'          => $row['time'],
            'all_day_event' => ! empty( $row['all_day_event'] ),
            'venuename'     => sanitize_text_field( $row['venuename'] ),
            'address'       => sanitize_text_field( $row['address'] ),
            'page_id'       => intval( $row['page_id'] ),
            'mainimageid'   => intval( $row['mainimageid'] ),
        ];
    }

    $result = [ 'events' => $events, 'total' => $total ];
    TTA_Cache::set( $cache_key, $result, 300 );
    return $result;
}

/**
 * Get a list of days in a month that have events scheduled.
 *
 * @param int $year 4-digit year.
 * @param int $month Numeric month (1-12).
 * @return int[] Array of day numbers with events.
 */
function tta_get_event_days_for_month( $year, $month ) {
    $year  = max( 1970, intval( $year ) );
    $month = max( 1, min( 12, intval( $month ) ) );

    $cache_key = 'event_days_' . $year . '_' . $month;
    $days      = TTA_Cache::get( $cache_key );
    if ( false !== $days ) {
        return (array) $days;
    }

    global $wpdb;
    $events_table = $wpdb->prefix . 'tta_events';
    $start = sprintf( '%04d-%02d-01', $year, $month );
    $end   = date( 'Y-m-t', strtotime( $start ) );

    $results = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DAY(date) FROM {$events_table} WHERE date BETWEEN %s AND %s",
            $start,
            $end
        )
    );

    $days = array_map( 'intval', $results );
    TTA_Cache::set( $cache_key, $days, 300 );
    return $days;
}

/**
 * Retrieve a sample member record for previews.
 *
 * @return array{
 *     first_name:string,
 *     last_name:string,
 *     email:string,
 *     phone:string,
 *     membership_level:string,
 *     member_type:string
 * }
 */
function tta_get_sample_member() {
    $cache_key = 'tta_sample_member';
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $row = $wpdb->get_row(
        "SELECT first_name, last_name, email, phone, membership_level, member_type FROM {$members_table} ORDER BY id ASC LIMIT 1",
        ARRAY_A
    );

    if ( ! $row ) {
        $row = [
            'first_name'       => 'First',
            'last_name'        => 'Last',
            'email'            => 'member@example.com',
            'phone'            => '555-555-5555',
            'membership_level' => 'basic',
            'member_type'      => 'member',
        ];
        TTA_Cache::set( $cache_key, $row, 60 );
        return $row;
    }

    $member = [
        'first_name'       => sanitize_text_field( $row['first_name'] ?? '' ),
        'last_name'        => sanitize_text_field( $row['last_name'] ?? '' ),
        'email'            => sanitize_email( $row['email'] ?? '' ),
        'phone'            => sanitize_text_field( $row['phone'] ?? '' ),
        'membership_level' => sanitize_text_field( $row['membership_level'] ?? 'basic' ),
        'member_type'      => sanitize_text_field( $row['member_type'] ?? 'member' ),
    ];

    TTA_Cache::set( $cache_key, $member, 300 );
    return $member;
}

/**
 * Retrieve information about the current visitor and any associated member
 * record.
 *
 * @return array{
 *     is_logged_in:bool,
 *     wp_user_id:int,
 *     user_email:string,
 *     user_login:string,
 *     first_name:string,
 *     last_name:string,
 *     member:?array,
 *     membership_level:string,
 *     subscription_id:?string,
 *     subscription_status:?string
 * }
 */
function tta_get_current_user_context() {
    $context = [
        'is_logged_in'     => is_user_logged_in(),
        'wp_user_id'       => 0,
        'user_email'       => '',
        'user_login'       => '',
        'first_name'       => '',
        'last_name'        => '',
        'member'           => null,
        'membership_level' => 'free',
        'subscription_id'  => null,
        'subscription_status' => null,
        'banned_until'      => null,
    ];

    if ( ! $context['is_logged_in'] ) {
        return $context;
    }

    $user = wp_get_current_user();
    $context['wp_user_id'] = intval( $user->ID );
    $context['user_email'] = sanitize_email( $user->user_email );
    $context['user_login'] = sanitize_user( $user->user_login );
    $context['first_name'] = sanitize_text_field( $user->first_name );
    $context['last_name']  = sanitize_text_field( $user->last_name );

    $cache_key = 'member_row_' . $context['wp_user_id'];
    $member    = TTA_Cache::remember( $cache_key, function() use ( $context ) {
        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$members_table} WHERE wpuserid = %d",
                $context['wp_user_id']
            ),
            ARRAY_A
        );
    }, 300 );

    if ( is_array( $member ) ) {
        $context['member']           = $member;
        $context['membership_level']  = $member['membership_level'] ?? 'free';
        $context['subscription_id']   = $member['subscription_id'] ?? null;
        $context['subscription_status'] = $member['subscription_status'] ?? null;
        $context['banned_until']      = $member['banned_until'] ?? null;
    }

    return $context;
}

/**
 * Render the cart table HTML for the given cart.
 *
 * @param TTA_Cart $cart
 * @param array    $discount_codes
 * @param array    $notices        Optional per-ticket messages keyed by ticket ID.
 *
 * @return string HTML markup
*/
function tta_render_cart_contents( TTA_Cart $cart, $discount_codes = [], array $notices = [] ) {
    ob_start();
    $items            = $cart->get_items_with_discounts( $discount_codes );
    $total            = $cart->get_total( $discount_codes );
    $membership_level = $_SESSION['tta_membership_purchase'] ?? '';
    $has_membership   = in_array( $membership_level, [ 'basic', 'premium' ], true );
    $has_tickets      = ! empty( $items );
    $code_events = [];
    foreach ( $items as $row ) {
        $info = tta_parse_discount_data( $row['discountcode'] );
        if ( $info['code'] && ! isset( $code_events[ $info['code'] ] ) ) {
            $code_events[ $info['code'] ] = $row['event_name'];
        }
    }
    if ( $items || $has_membership ) {
        ?>
        <table class="tta-cart-table">
            <thead>
                <tr>
                    <th>
                        <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( 'The name of the Event or Membership you\'re purchasing.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Event or Item', 'tta' ); ?>
                    </th>
                    <?php if ( $has_tickets ) : ?>
                    <th>
                            <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'We reserve your ticket for 5 minutes so events don\'t oversell. After 5 minutes it becomes available to others.' ); ?>">
                                <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Ticket Reserved for…', 'tta' ); ?>
                    </th>
                    <?php endif; ?>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Limit of two tickets per event in total.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Quantity', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Base cost before member discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Price', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Price for this row after all discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Subtotal', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon tta-tooltip-left" data-tooltip="<?php echo esc_attr( 'Remove this item from your cart.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Remove Item', 'tta' ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $it ) : ?>
                    <?php $sub = $it['quantity'] * $it['final_price']; ?>
                    <?php $expire_at = strtotime( $it['expires_at'] ); ?>
                    <tr data-expire-at="<?php echo esc_attr( $expire_at ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>">
                        <td data-label="<?php echo esc_attr( 'Event or Item' ); ?>">
                            <?php
                            $desc = '';
                            if ( $it['page_id'] && function_exists( 'get_post_field' ) ) {
                                $desc = get_post_field( 'post_excerpt', intval( $it['page_id'] ) );
                                if ( '' === $desc ) {
                                    $desc = wp_strip_all_tags( wp_trim_words( get_post_field( 'post_content', intval( $it['page_id'] ) ), 30 ) );
                                }
                            }
                            ?>
                            <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( $desc ); ?>">
                                <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                            </span>
                            <a href="<?php echo esc_url( get_permalink( $it['page_id'] ) ); ?>">
                                <?php echo esc_html( $it['event_name'] ); ?>
                            </a><br>
                            <?php echo esc_html( $it['ticket_name'] ); ?>
                        </td>
                        <?php if ( $has_tickets ) : ?>
                        <td data-label="<?php echo esc_attr( 'Ticket Reserved for…' ); ?>" class="tta-countdown-cell"><span class="tta-countdown"></span></td>
                        <?php endif; ?>
                        <td data-label="<?php echo esc_attr( 'Quantity' ); ?>">
                            <input type="number" name="cart_qty[<?php echo esc_attr( $it['ticket_id'] ); ?>]" value="<?php echo esc_attr( $it['quantity'] ); ?>" min="0" class="tta-cart-qty">
                            <?php $ntext = $notices[ $it['ticket_id'] ] ?? ''; ?>
                            <div class="tta-ticket-notice <?php echo $ntext ? 'tt-show' : ''; ?>" aria-live="polite"><?php echo esc_html( $ntext ); ?></div>
                        </td>
                        <td data-label="<?php echo esc_attr( 'Price' ); ?>">
                            <?php
                            $base = '$' . number_format( $it['baseeventcost'], 2 );
                            echo esc_html( $base );
                            if ( intval( $it['quantity'] ) > 1 ) {
                                echo ' x ' . intval( $it['quantity'] );
                            }
                            ?>
                        </td>
                        <td data-label="<?php echo esc_attr( 'Subtotal' ); ?>">
                            <?php
                            $orig = '$' . number_format( $it['baseeventcost'] * $it['quantity'], 2 );
                            $final = '$' . number_format( $sub, 2 );
                            if ( floatval( $it['final_price'] ) !== floatval( $it['baseeventcost'] ) ) {
                                echo '<span class="tta-price-strike">' . esc_html( $orig ) . '</span> ' . esc_html( $final );
                            } else {
                                echo esc_html( $final );
                            }
                            ?>
                        </td>
                        <td><button type="button" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>" class="tta-remove-item" aria-label="Remove"></button></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ( $has_membership ) : ?>
                    <?php $m_price = tta_get_membership_price( $membership_level ); ?>
                    <tr class="tta-membership-row" data-ticket="0">
                        <td><?php echo esc_html( ucfirst( $membership_level ) . ' Membership' ); ?></td>
                        <?php if ( $has_tickets ) : ?><td></td><?php endif; ?>
                        <td>1</td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?> <?php esc_html_e( 'Per Month', 'tta' ); ?></td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?> <?php esc_html_e( 'Per Month', 'tta' ); ?></td>
                        <td><button type="button" id="tta-remove-membership" class="tta-remove-item" aria-label="Remove"></button></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="<?php echo $has_tickets ? 4 : 3; ?>"><?php esc_html_e( 'Total', 'tta' ); ?></th>
                    <?php
                    $m_total = $has_membership ? tta_get_membership_price( $membership_level ) : 0;
                    ?>
                    <td colspan="2" class="tta-cart-total">
                        $<?php echo esc_html( number_format( $total, 2 ) ); ?>
                        <?php
                        if ( $has_membership ) {
                            if ( $has_tickets ) {
                                echo ' ' . esc_html__( 'today,', 'tta' ) . ' $' . number_format( $m_total, 2 ) . ' ' . esc_html__( 'Per Month', 'tta' );
                            } else {
                                echo ' ' . esc_html__( 'Per Month', 'tta' );
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php if ( $discount_codes ) : ?>
                <tr class="tta-active-discounts">
                    <td colspan="6">
                        <?php esc_html_e( 'Active Discount Codes:', 'tta' ); ?>
                        <?php foreach ( $discount_codes as $code ) : ?>
                            <?php $ev = $code_events[ $code ] ?? ''; ?>
                            <span class="tta-discount-tag"><?php echo esc_html( $code . ( $ev ? " ($ev)" : '' ) ); ?> <button type="button" class="tta-remove-discount tta-remove-item" data-code="<?php echo esc_attr( $code ); ?>" aria-label="Remove"></button></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        <p class="tta-cart-discount">
            <label><?php esc_html_e( 'Discount Code', 'tta' ); ?>
                <input type="text" id="tta-discount-code" name="discount_code">
            </label>
            <button type="button" id="tta-apply-discount" disabled><?php esc_html_e( 'Apply Discount', 'tta' ); ?></button>
            <span class="tta-discount-feedback"></span>
        </p>
        <?php
    } else {
        echo '<p>' . esc_html__( 'Your cart is empty.', 'tta' ) . '</p>';
    }
    return trim( ob_get_clean() );
}

/**
 * Render a read-only summary of the cart for the checkout page.
 *
 * @param TTA_Cart $cart
 * @param array    $discount_codes Optional active discount codes.
 *
 * @return string
 */
function tta_render_checkout_summary( TTA_Cart $cart, $discount_codes = [] ) {
    ob_start();
    $items            = $cart->get_items_with_discounts( $discount_codes );
    $total            = $cart->get_total( $discount_codes );
    $membership_level = $_SESSION['tta_membership_purchase'] ?? '';
    $has_membership   = in_array( $membership_level, [ 'basic', 'premium' ], true );
    $has_tickets      = ! empty( $items );
    $code_events = [];
    foreach ( $items as $row ) {
        $info = tta_parse_discount_data( $row['discountcode'] );
        if ( $info['code'] && ! isset( $code_events[ $info['code'] ] ) ) {
            $code_events[ $info['code'] ] = $row['event_name'];
        }
    }
    if ( $items || $has_membership ) {
        ?>
        <div id="tta-checkout-container">
        <table class="tta-checkout-summary">
            <thead>
                <tr>
                    <th>
                        <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( 'The name of the Event or Membership you\'re purchasing.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Event or Item', 'tta' ); ?>
                    </th>
                    <?php if ( $has_tickets ) : ?>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( "We reserve your ticket for 5 minutes so events don't oversell. After 5 minutes it becomes available to others." ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Ticket Reserved for…', 'tta' ); ?>
                    </th>
                    <?php endif; ?>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Limit of two tickets per event in total.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Qty', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Base cost before member discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Price', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Price for this row after all discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Subtotal', 'tta' ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $it ) : ?>
                    <?php $sub = $it['quantity'] * $it['final_price']; ?>
                    <?php $expire_at = strtotime( $it['expires_at'] ); ?>
                    <tr data-expire-at="<?php echo esc_attr( $expire_at ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>">
                        <td data-label="<?php echo esc_attr( 'Event or Item' ); ?>">
                            <?php
                            $desc = '';
                            if ( $it['page_id'] && function_exists( 'get_post_field' ) ) {
                                $desc = get_post_field( 'post_excerpt', intval( $it['page_id'] ) );
                                if ( '' === $desc ) {
                                    $desc = wp_strip_all_tags( wp_trim_words( get_post_field( 'post_content', intval( $it['page_id'] ) ), 30 ) );
                                }
                            }
                            ?>
                            <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( $desc ); ?>">
                                <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                            </span>
                            <a href="<?php echo esc_url( get_permalink( $it['page_id'] ) ); ?>">
                                <?php echo esc_html( $it['event_name'] ); ?>
                            </a><br>
                            <?php echo esc_html( $it['ticket_name'] ); ?>
                        </td>
                        <?php if ( $has_tickets ) : ?>
                        <td class="tta-countdown-cell" data-label="<?php echo esc_attr( 'Ticket Reserved for…' ); ?>"><span class="tta-countdown"></span></td>
                        <?php endif; ?>
                        <td data-label="<?php echo esc_attr( 'Qty' ); ?>"><?php echo intval( $it['quantity'] ); ?></td>
                        <td data-label="<?php echo esc_attr( 'Price' ); ?>">
                            <?php
                            $base = '$' . number_format( $it['baseeventcost'], 2 );
                            echo esc_html( $base );
                            if ( intval( $it['quantity'] ) > 1 ) {
                                echo ' x ' . intval( $it['quantity'] );
                            }
                            ?>
                        </td>
                        <td data-label="<?php echo esc_attr( 'Subtotal' ); ?>">
                            <?php
                            $orig = '$' . number_format( $it['baseeventcost'] * $it['quantity'], 2 );
                            $final_sub = '$' . number_format( $sub, 2 );
                            if ( floatval( $it['final_price'] ) !== floatval( $it['baseeventcost'] ) ) {
                                echo '<span class="tta-price-strike">' . esc_html( $orig ) . '</span> ' . esc_html( $final_sub );
                            } else {
                                echo esc_html( $final_sub );
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ( $has_membership ) : ?>
                    <?php $m_price = tta_get_membership_price( $membership_level ); ?>
                    <tr class="tta-membership-row" data-ticket="0">
                        <td><?php echo esc_html( ucfirst( $membership_level ) . ' Membership' ); ?></td>
                        <?php if ( $has_tickets ) : ?><td></td><?php endif; ?>
                        <td>1</td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?> <?php esc_html_e( 'Per Month', 'tta' ); ?></td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?> <?php esc_html_e( 'Per Month', 'tta' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="<?php echo $has_tickets ? 4 : 3; ?>"><?php esc_html_e( 'Total', 'tta' ); ?></th>
                    <?php
                    $m_total = $has_membership ? tta_get_membership_price( $membership_level ) : 0;
                    ?>
                    <td>
                        $<?php echo esc_html( number_format( $total, 2 ) ); ?>
                        <?php
                        if ( $has_membership ) {
                            if ( $has_tickets ) {
                                echo ' ' . esc_html__( 'today,', 'tta' ) . ' $' . number_format( $m_total, 2 ) . ' ' . esc_html__( 'Per Month', 'tta' );
                            } else {
                                echo ' ' . esc_html__( 'Per Month', 'tta' );
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php if ( $discount_codes ) : ?>
                <tr class="tta-active-discounts">
                    <td colspan="5">
                        <?php esc_html_e( 'Active Discount Codes:', 'tta' ); ?>
                        <?php foreach ( $discount_codes as $code ) : ?>
                            <?php $ev = $code_events[ $code ] ?? ''; ?>
                            <span class="tta-discount-tag"><?php echo esc_html( $code . ( $ev ? " ($ev)" : '' ) ); ?></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        </div>
        <?php
    } else {
        echo '<p>' . esc_html__( 'Your cart is empty.', 'tta' ) . '</p>';
    }
    return trim( ob_get_clean() );
}

/**
 * Render attendee input fields grouped by event for checkout.
 *
 * @param TTA_Cart $cart Cart instance.
 * @return string        HTML markup with attendee fields.
 */
function tta_render_attendee_fields( TTA_Cart $cart, $disabled = false ) {
    $items = $cart->get_items();
    if ( ! $items ) {
        return '';
    }

    $groups = [];
    foreach ( $items as $row ) {
        $eid = $row['event_ute_id'];
        if ( ! isset( $groups[ $eid ] ) ) {
            $groups[ $eid ] = [
                'event_name' => $row['event_name'],
                'page_id'    => $row['page_id'],
                'tickets'    => [],
            ];
        }
        $groups[ $eid ]['tickets'][] = $row;
    }

    ob_start();
    echo '<div class="tta-attendee-fields">';
    $context      = tta_get_current_user_context();
    $used_default = false;
    $d_attr = $disabled ? ' disabled' : '';
    foreach ( $groups as $grp ) {
        echo '<div class="tta-event-group">';
        echo '<h4><a href="' . esc_url( get_permalink( $grp['page_id'] ) ) . '">' . esc_html( $grp['event_name'] ) . '</a></h4>';
        echo '<p class="tta-attendee-note">' . esc_html__( 'Complete the information below for each attendee. This information will be used for checking attendees in when arriving at the event.', 'tta' ) . '</p>';
        foreach ( $grp['tickets'] as $t ) {
            $qty = intval( $t['quantity'] );
            for ( $i = 0; $i < $qty; $i++ ) {
                echo '<div class="tta-attendee-row">';
                echo '<strong>' . esc_html( $t['ticket_name'] ) . ' #' . ( $i + 1 ) . '</strong><br />';
                $base    = 'attendees[' . intval( $t['ticket_id'] ) . '][' . $i . ']';
                $fn_val  = '';
                $ln_val  = '';
                $em_val  = '';
                $ph_val  = '';
                $sms_chk = 'checked';
                $em_chk  = 'checked';
                if ( ! $used_default && $context['member'] ) {
                    $fn_val  = esc_attr( $context['member']['first_name'] );
                    $ln_val  = esc_attr( $context['member']['last_name'] );
                    $em_val  = esc_attr( $context['member']['email'] );
                    $ph_val  = esc_attr( $context['member']['phone'] ?? '' );
                    $used_default = true;
                }
                $img = esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' );
                echo '<label><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'First name for event check-in.', 'tta' ) . '"><img src="' . $img . '" alt="?"></span>' . esc_html__( 'First Name', 'tta' ) . '<span class="tta-required">*</span><br />';
                echo '<input type="text" name="' . esc_attr( $base . '[first_name]' ) . '" value="' . $fn_val . '" required' . $d_attr . '></label> ';
                echo '<label><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Last name for event check-in.', 'tta' ) . '"><img src="' . $img . '" alt="?"></span>' . esc_html__( 'Last Name', 'tta' ) . '<br />';
                echo '<input type="text" name="' . esc_attr( $base . '[last_name]' ) . '" value="' . $ln_val . '" required' . $d_attr . '></label> ';
                echo '<label><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Email used for ticket confirmation.', 'tta' ) . '"><img src="' . $img . '" alt="?"></span>' . esc_html__( 'Email', 'tta' ) . '<span class="tta-required">*</span><br />';
                echo '<input type="email" name="' . esc_attr( $base . '[email]' ) . '" value="' . $em_val . '" required' . $d_attr . '></label> ';
                echo '<label><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Phone used for event updates or issues.', 'tta' ) . '"><img src="' . $img . '" alt="?"></span>' . esc_html__( 'Phone', 'tta' ) . '<br />';
                echo '<input type="tel" name="' . esc_attr( $base . '[phone]' ) . '" value="' . $ph_val . '"' . $d_attr . '></label>';
                echo '<div class="optin-container"><label class="tta-ticket-optin"><input type="checkbox" name="' . esc_attr( $base . '[opt_in_sms]' ) . '" ' . $sms_chk . $d_attr . '> <span class="tta-ticket-opt-text">' . esc_html__( 'text me updates about this event', 'tta' ) . '</span></label>';
                echo '<label class="tta-ticket-optin"><input type="checkbox" name="' . esc_attr( $base . '[opt_in_email]' ) . '" ' . $em_chk . $d_attr . '><span class="tta-ticket-opt-text">' . esc_html__( 'email me updates about this event', 'tta' ) . '</span></label></div>';
                echo '</div>';
            }
        }
        echo '</div>';
    }
    echo '</div>';
    return trim( ob_get_clean() );
}

/**
 * Render an image preview for admin screens that works even when
 * WordPress cannot generate intermediate sizes (e.g. unsupported
 * MIME types).
 *
 * @param int   $attachment_id Attachment ID.
 * @param array $size          [width, height] to display.
 * @param array $attrs         Optional attributes for the img tag.
 * @return string              HTML <img> markup or empty string.
 */
function tta_admin_preview_image( $attachment_id, array $size, array $attrs = [] ) {
    $attachment_id = intval( $attachment_id );
    if ( ! $attachment_id ) {
        return '';
    }

    $url = wp_get_attachment_image_url( $attachment_id, $size );
    if ( ! $url ) {
        $url = wp_get_attachment_url( $attachment_id );
    }

    if ( ! $url ) {
        return '';
    }

    $full = wp_get_attachment_image_url( $attachment_id, 'large' );
    if ( ! $full ) {
        $full = $url;
    }

    $attrs['class'] = isset( $attrs['class'] ) ? $attrs['class'] . ' tta-popup-img' : 'tta-popup-img';
    $attrs['data-full'] = $full;

    $attr_str = '';
    foreach ( $attrs as $key => $val ) {
        $attr_str .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $val ) );
    }

    $width  = intval( $size[0] );
    $height = intval( $size[1] );

    return sprintf(
        '<img src="%s" width="%d" height="%d"%s />',
        esc_url( $url ),
        $width,
        $height,
        $attr_str
    );
}

/**
 * Retrieve Email & SMS templates with defaults.
 *
 * @return array
 */
function tta_get_comm_templates() {
    if ( ! class_exists( 'TTA_Comms_Admin' ) ) {
        require_once TTA_PLUGIN_DIR . 'includes/admin/class-comms-admin.php';
    }
    $defaults = TTA_Comms_Admin::get_default_templates();
    $saved    = tta_unslash( get_option( 'tta_comms_templates', [] ) );

    if ( is_array( $saved ) ) {
        foreach ( $saved as $k => $vals ) {
            if ( isset( $defaults[ $k ] ) && is_array( $vals ) ) {
                $defaults[ $k ] = array_merge( $defaults[ $k ], $vals );
            }
        }
    }

    return $defaults;
}

/**
 * Retrieve all saved ads.
 *
 * @return array[] List of ads with image_id and url.
 */
function tta_get_ads() {
    $cache_key = 'tta_ads_all';
    $ads = TTA_Cache::get( $cache_key );
    if ( false !== $ads ) {
        return $ads;
    }
    $ads = get_option( 'tta_ads', [] );
    if ( ! is_array( $ads ) ) {
        $ads = [];
    }
    TTA_Cache::set( $cache_key, $ads, 300 );
    return $ads;
}

/**
 * Retrieve one random ad record.
 *
 * @return array|null
 */
function tta_get_random_ad() {
    $ads = tta_get_ads();
    if ( empty( $ads ) ) {
        return null;
    }
    $ads = array_values( $ads );
    shuffle( $ads );
    return $ads[0];
}

/**
 * Get the page ID of the first event scheduled for a given date.
 *
 * @param int $year  Year in YYYY format.
 * @param int $month Month number (1-12).
 * @param int $day   Day of month (1-31).
 * @return int       WordPress page ID or 0 if none found.
 */
function tta_get_first_event_page_id_for_date( $year, $month, $day ) {
    $year  = max( 1970, intval( $year ) );
    $month = max( 1, min( 12, intval( $month ) ) );
    $day   = max( 1, min( 31, intval( $day ) ) );

    $cache_key = sprintf( 'event_page_%04d_%02d_%02d', $year, $month, $day );
    $page_id   = TTA_Cache::get( $cache_key );
    if ( false !== $page_id ) {
        return intval( $page_id );
    }

    global $wpdb;
    $events_table = $wpdb->prefix . 'tta_events';
    $date         = sprintf( '%04d-%02d-%02d', $year, $month, $day );

    $page_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT page_id FROM {$events_table} WHERE date = %s ORDER BY id ASC LIMIT 1",
            $date
        )
    );

    TTA_Cache::set( $cache_key, $page_id, 300 );
    return $page_id;
}
