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
 * Retrieve stored Authorize.Net credentials for live or sandbox mode.
 *
 * @param bool|null $sandbox Whether to fetch sandbox credentials. Null uses current option.
 * @return array{login_id:string, transaction_key:string}
 */
function tta_get_authnet_credentials( $sandbox = null ) {
    if ( null === $sandbox ) {
        $sandbox = (bool) get_option( 'tta_authnet_sandbox', false );
    }
    $login_option = $sandbox ? 'tta_authnet_login_id_sandbox' : 'tta_authnet_login_id_live';
    $key_option   = $sandbox ? 'tta_authnet_transaction_key_sandbox' : 'tta_authnet_transaction_key_live';
    return [
        'login_id'        => get_option( $login_option, '' ),
        'transaction_key' => get_option( $key_option, '' ),
    ];
}

/**
 * Collect attendee emails from the nested attendee array structure.
 *
 * The returned list preserves the submitted order and may contain
 * duplicates. Consumers are expected to de-duplicate as needed.
 *
 * @param array $attendees Attendee data posted from checkout.
 * @return array           Sanitized list of emails.
 */
function tta_collect_attendee_emails( array $attendees ) {
    $emails = [];
    foreach ( $attendees as $rows ) {
        foreach ( (array) $rows as $row ) {
            $email = tta_sanitize_email( $row['email'] ?? '' );
            if ( $email ) {
                $emails[] = $email;
            }
        }
    }
    return $emails;
}

/**
 * Get a member's current membership level by email.
 *
 * @param string $email Email address.
 * @return string       free, basic or premium.
 */
function tta_get_membership_level_by_email( $email ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $email = sanitize_email( $email );
    if ( ! $email ) {
        return 'free';
    }
    $level = $wpdb->get_var( $wpdb->prepare( "SELECT membership_level FROM {$members_table} WHERE email = %s LIMIT 1", $email ) );
    $level = $level ? strtolower( $level ) : 'free';
    return in_array( $level, [ 'free', 'basic', 'premium' ], true ) ? $level : 'free';
}

/**
 * Get a member row by email address.
 *
 * @param string $email Email to search.
 * @return array|null   Array with id and wpuserid or null if not found.
 */
function tta_get_member_row_by_email( $email ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $email         = sanitize_email( $email );
    if ( ! $email ) {
        return null;
    }

    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, wpuserid FROM {$members_table} WHERE LOWER(email) = %s LIMIT 1",
            strtolower( $email )
        ),
        ARRAY_A
    );
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
 * Convert Markdown-style links to HTML anchors for emails.
 *
 * Supports the pattern `[text](url)` and escapes both the URL and link text.
 *
 * @param string $text Raw message content.
 * @return string Text with `<a href>` tags.
 */
function tta_convert_links( $text ) {
    return preg_replace_callback(
        '/\[([^\]]+)\]\(([^\)]+)\)/',
        static function ( $m ) {
            $url   = esc_url( $m[2] );
            $label = esc_html( $m[1] );
            return '<a href="' . $url . '">' . $label . '</a>';
        },
        $text
    );
}

/**
 * Convert basic Markdown-style formatting to HTML tags.
 *
 * Supports the following sequences and escapes the enclosed text:
 *
 * - `***text***` → `<strong><em>text</em></strong>`
 * - `**text**`   → `<strong>text</strong>`
 * - `*text*`     → `<em>text</em>`
 *
 * @param string $text Raw message content.
 * @return string Text with `<strong>`/`<em>` tags.
 */
function tta_convert_bold( $text ) {
    $text = preg_replace_callback(
        '/\*\*\*(.*?)\*\*\*/',
        static function ( $m ) {
            return '<strong><em>' . esc_html( $m[1] ) . '</em></strong>';
        },
        $text
    );

    $text = preg_replace_callback(
        '/\*\*(.*?)\*\*/',
        static function ( $m ) {
            return '<strong>' . esc_html( $m[1] ) . '</strong>';
        },
        $text
    );

    $text = preg_replace_callback(
        '/\*(.*?)\*/',
        static function ( $m ) {
            return '<em>' . esc_html( $m[1] ) . '</em>';
        },
        $text
    );

    return $text;
}

/**
 * Strip Markdown-style bold and italic markers, returning plain text.
 *
 * @param string $text Raw message content.
 * @return string Text without formatting markers.
 */
function tta_strip_bold( $text ) {
    $text = preg_replace( '/\*\*\*(.*?)\*\*\*/', '$1', $text );
    $text = preg_replace( '/\*\*(.*?)\*\*/', '$1', $text );
    return preg_replace( '/\*(.*?)\*/', '$1', $text );
}

/**
 * Expand dashboard URL tokens with optional anchor text.
 *
 * Replaces patterns like `{dashboard_upcoming_url anchor="View"}` with either
 * a Markdown-style link or the raw URL if no anchor is specified.
 *
 * @param string $text   Raw template text.
 * @param array  $tokens Token map from build_tokens().
 * @return string Text with anchor tokens expanded.
 */
function tta_expand_anchor_tokens( $text, array $tokens ) {
    return preg_replace_callback(
        '/\{(dashboard_(?:profile|upcoming|waitlist|past|billing)_url)\s+anchor="([^"]*)"\}/',
        static function ( $m ) use ( $tokens ) {
            $key  = '{' . $m[1] . '}';
            $url  = isset( $tokens[ $key ] ) ? esc_url( $tokens[ $key ] ) : '';
            $text = $m[2];
            if ( '' === $text ) {
                return $url;
            }
            $text = esc_html( $text );
            return '[' . $text . '](' . $url . ')';
        },
        $text
    );
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
 * Convert stored discount data into a human-readable string.
 *
 * @param string $raw Discount data from the database.
 * @return string Formatted description or empty string.
 */
function tta_format_discount_display( $raw ) {
    $info = tta_parse_discount_data( $raw );
    if ( '' === $info['code'] ) {
        return '';
    }

    $amount = number_format( floatval( $info['amount'] ), 2 );
    if ( 'flat' === $info['type'] ) {
        return sprintf( '%s - %s %s%s', $info['code'], __( 'Flat Discount of', 'tta' ), '$', $amount );
    }

    return sprintf( '%s - %s%% %s', $info['code'], $amount, __( 'Discount', 'tta' ) );
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
 * Store context data for the waitlist join modal.
 *
 * @param array $context
 */
function tta_set_waitlist_context( array $context ) {
    if ( ! session_id() ) {
        session_start();
    }
    $_SESSION['tta_waitlist_context'] = $context;
}

/**
 * Retrieve and clear any stored waitlist context.
 *
 * @return array
 */
function tta_get_waitlist_context() {
    if ( ! session_id() ) {
        session_start();
    }
    $ctx = $_SESSION['tta_waitlist_context'] ?? [];
    unset( $_SESSION['tta_waitlist_context'] );
    return is_array( $ctx ) ? $ctx : [];
}

/**
 * Determine if the waitlist table still uses a CSV column.
 *
 * @return bool True if the table includes a `userids` column.
 */
function tta_waitlist_uses_csv() {
    static $uses_csv = null;
    if ( null === $uses_csv ) {
        global $wpdb;
        $table     = $wpdb->prefix . 'tta_waitlist';
        $uses_csv = (bool) $wpdb->get_var( "SHOW COLUMNS FROM {$table} LIKE 'userids'" );
    }
    return $uses_csv;
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

    $user_id      = intval( $user_id );
    $event_ute_id = sanitize_text_field( $event_ute_id );

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

    $event_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1",
            $event_ute_id,
            $event_ute_id
        )
    );

    if ( $event_id ) {
        $refunds = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT action_data FROM {$wpdb->prefix}tta_memberhistory WHERE wpuserid = %d AND event_id = %d AND action_type = 'refund'",
                $user_id,
                $event_id
            ),
            ARRAY_A
        );
        foreach ( $refunds as $row ) {
            $data = json_decode( $row['action_data'], true );
            if ( ! empty( $data['cancel'] ) ) {
                $total -= 1;
            }
        }
    }

    return max( 0, $total );
}

/**
 * Get how many of a specific ticket a user has purchased.
 *
 * @param int $user_id
 * @param int $ticket_id
 * @return int
 */
function tta_get_purchased_ticket_count_for_ticket( $user_id, $ticket_id ) {
    global $wpdb;

    $user_id  = intval( $user_id );
    $ticket_id = intval( $ticket_id );

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
            if ( intval( $it['ticket_id'] ?? 0 ) === $ticket_id ) {
                $total += intval( $it['quantity'] );
            }
        }
    }

    $event_ute_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT event_ute_id FROM {$wpdb->prefix}tta_tickets WHERE id = %d UNION SELECT event_ute_id FROM {$wpdb->prefix}tta_tickets_archive WHERE id = %d LIMIT 1",
            $ticket_id,
            $ticket_id
        )
    );

    if ( $event_ute_id ) {
        $event_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1",
                $event_ute_id,
                $event_ute_id
            )
        );

        if ( $event_id ) {
            $refunds = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT action_data FROM {$wpdb->prefix}tta_memberhistory WHERE wpuserid = %d AND event_id = %d AND action_type = 'refund'",
                    $user_id,
                    $event_id
                ),
                ARRAY_A
            );
            foreach ( $refunds as $row ) {
                $data = json_decode( $row['action_data'], true );
                if ( ! empty( $data['cancel'] ) ) {
                    $total -= 1;
                }
            }
        }
    }

    return max( 0, $total );
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
            ORDER BY first_name, last_name";

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

    $sql = "(SELECT a.id, a.ticket_id, a.first_name, a.last_name, a.email, a.phone, a.assistance_note, a.status
               FROM {$att_table} a
               JOIN {$tickets_table} t ON a.ticket_id = t.id
              WHERE t.event_ute_id = %s)
           UNION ALL
            (SELECT a.id, a.ticket_id, a.first_name, a.last_name, a.email, a.phone, a.assistance_note, a.status
               FROM {$att_archive} a
               JOIN {$tickets_archive} t ON a.ticket_id = t.id
              WHERE t.event_ute_id = %s)
            ORDER BY first_name, last_name";

    $rows = $wpdb->get_results( $wpdb->prepare( $sql, $event_ute_id, $event_ute_id ), ARRAY_A );

    $event_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1",
            $event_ute_id,
            $event_ute_id
        )
    );

    $exclude_emails = [];
    if ( $event_id ) {
        $ticket_ids = [];
        foreach ( $rows as $r ) {
            $ticket_ids[ intval( $r['ticket_id'] ) ] = true;
        }
        foreach ( array_keys( $ticket_ids ) as $tid ) {
            foreach ( tta_get_ticket_pending_refund_attendees( $tid, $event_id ) as $req ) {
                $exclude_emails[] = strtolower( $req['email'] );
            }
            foreach ( tta_get_ticket_refunded_attendees( $tid, $event_id ) as $ref ) {
                $exclude_emails[] = strtolower( $ref['email'] );
            }
        }
        $exclude_emails = array_unique( $exclude_emails );
    }

    $out = [];
    foreach ( $rows as $r ) {
        if ( in_array( strtolower( $r['email'] ), $exclude_emails, true ) ) {
            continue;
        }
        $r['id']            = intval( $r['id'] );
        $r['first_name']    = sanitize_text_field( $r['first_name'] );
        $r['last_name']     = sanitize_text_field( $r['last_name'] );
        $r['email']         = sanitize_email( $r['email'] );
        $r['phone']         = sanitize_text_field( $r['phone'] );
        $r['status']        = sanitize_text_field( $r['status'] );
        $r['attended_count'] = tta_get_attended_event_count_by_email( $r['email'] );
        $r['no_show_count']  = tta_get_no_show_event_count_by_email( $r['email'] );
        $note = trim( $r['assistance_note'] ?? '' );
        $r['assistance_note'] = $note !== '' ? sanitize_textarea_field( $note ) : '-';
        $out[] = $r;
    }

    return $out;
}

/**
 * Get the number of expected attendees for an event.
 *
 * @param string $event_ute_id Event ute_id.
 * @return int Count of attendees.
 */
function tta_get_expected_attendee_count( $event_ute_id ) {
    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return 0;
    }

    $cache_key = 'expected_count_' . $event_ute_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return intval( $cached );
    }

    $attendees = tta_get_event_attendees_with_status( $event_ute_id );
    $count     = count( $attendees );

    TTA_Cache::set( $cache_key, $count, 60 );
    return $count;
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
    $txn_map   = [];
    if ( $txn_ids ) {
        $placeholders = implode( ',', array_fill( 0, count( $txn_ids ), '%d' ) );
        $txn_rows     = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, transaction_id, created_at, wpuserid, details FROM {$wpdb->prefix}tta_transactions WHERE id IN ($placeholders)",
                $txn_ids
            ),
            ARRAY_A
        );

        foreach ( $txn_rows as $tx ) {
            $txn_id = intval( $tx['id'] );
            $email = '';
            if ( ! empty( $tx['wpuserid'] ) ) {
                $user  = get_userdata( intval( $tx['wpuserid'] ) );
                $email = $user ? strtolower( $user->user_email ) : '';
            }
            $txn_map[ $txn_id ] = [
                'gateway_id'      => sanitize_text_field( $tx['transaction_id'] ),
                'created_at'      => $tx['created_at'],
                'purchaser_email' => $email,
            ];

            $details = json_decode( $tx['details'], true );
            if ( ! is_array( $details ) ) {
                continue;
            }
            foreach ( $details as $item ) {
                if ( intval( $item['ticket_id'] ?? 0 ) === $ticket_id ) {
                    $price_map[ $txn_id ] = floatval( $item['final_price'] ?? 0 );
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
        $txid             = intval( $r['transaction_id'] );
        $r['amount_paid']  = $price_map[ $txid ] ?? 0;
        $r['gateway_id']   = $txn_map[ $txid ]['gateway_id'] ?? '';
        $r['created_at']   = $txn_map[ $txid ]['created_at'] ?? '';
        $purch_email       = $txn_map[ $txid ]['purchaser_email'] ?? '';
        $r['is_purchaser'] = $purch_email && strtolower( $r['email'] ) === $purch_email;
    }

    $ttl = empty( $rows ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $rows, $ttl );
    return $rows;
}

/**
 * Get the WordPress user ID for an attendee.
 *
 * @param int $attendee_id Attendee ID.
 * @return int WP user ID or 0 if not found.
 */
function tta_get_attendee_user_id( $attendee_id ) {
    global $wpdb;
    $att_table = $wpdb->prefix . 'tta_attendees';
    $tx_table  = $wpdb->prefix . 'tta_transactions';
    if ( ! method_exists( $wpdb, 'get_var' ) ) {
        return 0;
    }
    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT t.wpuserid FROM {$att_table} a JOIN {$tx_table} t ON a.transaction_id = t.id WHERE a.id = %d",
            intval( $attendee_id )
        )
    );
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
    $status    = in_array( $status, [ 'checked_in', 'no_show', 'pending' ], true ) ? $status : 'pending';

    $row   = $wpdb->get_row( $wpdb->prepare( "SELECT email FROM {$att_table} WHERE id = %d", $attendee_id ), ARRAY_A );
    $email = $row ? strtolower( sanitize_email( $row['email'] ) ) : '';

    $current = tta_get_attendance_status( $attendee_id );
    if ( $current === $status ) {
        return;
    }

    $wpdb->update( $att_table, [ 'status' => $status ], [ 'id' => intval( $attendee_id ) ], [ '%s' ], [ '%d' ] );
    TTA_Cache::delete( 'attendance_status_' . intval( $attendee_id ) );
    if ( $email ) {
        TTA_Cache::delete( 'attended_count_' . md5( $email ) );
        TTA_Cache::delete( 'no_show_count_' . md5( $email ) );
    }

    $user_id = tta_get_attendee_user_id( $attendee_id );
    if ( $user_id ) {
        TTA_Cache::delete( 'attendance_summary_' . $user_id );
        TTA_Cache::delete( 'past_events_' . $user_id );
    }

    if ( 'no_show' === $status && 'no_show' !== $current && $user_id ) {
        $no_shows        = tta_get_no_show_event_count_by_email( $email );
        $previous_no_show = max( 0, $no_shows - 1 );
        if ( $no_shows >= 3 && $previous_no_show < 3 ) {
            $members_table = $wpdb->prefix . 'tta_members';
            $wpdb->update( $members_table, [ 'banned_until' => TTA_BAN_UNTIL_REENTRY ], [ 'wpuserid' => $user_id ], [ '%s' ], [ '%d' ] );
            TTA_Cache::delete( 'banned_until_' . $user_id );
            TTA_Cache::delete( 'banned_members' );
            tta_clear_reinstatement_cron( $user_id );
            tta_send_no_show_ban_email( $user_id );
        }
    }
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
    if ( ! method_exists( $wpdb, 'get_var' ) ) {
        return 'pending';
    }
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
    $parse_roles = static function( $list ) {
        $ids = [];
        $names = [];
        foreach ( array_filter( array_map( 'trim', explode( ',', (string) $list ) ) ) as $item ) {
            if ( ctype_digit( $item ) ) {
                $ids[] = intval( $item );
            } else {
                $names[] = strtolower( $item );
            }
        }
        return [ $ids, $names ];
    };

    list( $host_ids, $host_lower ) = $parse_roles( $event_row['hosts'] ?? '' );
    list( $vol_ids,  $vol_lower  ) = $parse_roles( $event_row['volunteers'] ?? '' );

    if ( ! $ute_id ) {
        TTA_Cache::set( $cache_key, [], 60 );
        return [];
    }

    $sql = "(SELECT a.email,
                    COALESCE(m.first_name, a.first_name) AS first_name,
                    COALESCE(m.last_name,  a.last_name)  AS last_name,
                    m.profileimgid,
                    m.hide_event_attendance,
                    m.membership_level,
                    m.wpuserid
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
                    m.membership_level,
                    m.wpuserid
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
        $fn      = sanitize_text_field( $row['first_name'] ?? '' );
        $ln      = sanitize_text_field( $row['last_name']  ?? '' );
        $name    = trim( $fn . ' ' . $ln );
        $lower   = strtolower( $name );
        $wp_id   = intval( $row['wpuserid'] ?? 0 );
        $profiles[ $email ] = [
            'first_name'       => $hide ? '' : $fn,
            'last_name'        => $hide ? '' : $ln,
            'img_id'           => $hide ? 0 : intval( $row['profileimgid'] ),
            'hide'             => $hide,
            'membership_level' => tta_get_membership_level_by_email( $email ),
            'is_host'          => ( $wp_id && in_array( $wp_id, $host_ids, true ) ) || in_array( $lower, $host_lower, true ),
            'is_volunteer'     => ( $wp_id && in_array( $wp_id, $vol_ids, true ) ) || in_array( $lower, $vol_lower, true ),
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
 * Retrieve email addresses for hosts and/or volunteers of an event.
 *
 * Supports both numeric user IDs and legacy name-based values.
 *
 * @param int         $event_id Event ID.
 * @param string|null $role     Optional role filter: 'host' or 'volunteer'.
 * @return string[] List of unique emails.
 */
function tta_get_event_host_volunteer_emails( $event_id, $role = null ) {
    $event_id = intval( $event_id );
    if ( ! $event_id ) {
        return [];
    }

    $role      = $role ? strtolower( $role ) : null;
    $cache_key = 'event_host_vol_emails_' . $event_id . '_' . ( $role ?: 'all' );
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';
    $members_table = $wpdb->prefix . 'tta_members';

    $row = $wpdb->get_row(
        $wpdb->prepare( "SELECT hosts, volunteers FROM {$events_table} WHERE id = %d", $event_id ),
        ARRAY_A
    );
    if ( ! $row ) {
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT hosts, volunteers FROM {$archive_table} WHERE id = %d", $event_id ),
            ARRAY_A
        );
    }
    if ( ! $row ) {
        TTA_Cache::set( $cache_key, [], 60 );
        return [];
    }

    $parse = static function( $list ) {
        $ids = [];
        $names = [];
        foreach ( array_filter( array_map( 'trim', explode( ',', (string) $list ) ) ) as $val ) {
            if ( ctype_digit( $val ) ) {
                $ids[] = intval( $val );
            } else {
                $names[] = strtolower( $val );
            }
        }
        return [ $ids, $names ];
    };

    list( $host_ids, $host_names ) = $parse( $row['hosts'] ?? '' );
    list( $vol_ids,  $vol_names  ) = $parse( $row['volunteers'] ?? '' );

    $emails    = [];
    $id_list   = [];
    $name_list = [];

    if ( 'host' === $role ) {
        $id_list   = $host_ids;
        $name_list = $host_names;
    } elseif ( 'volunteer' === $role ) {
        $id_list   = $vol_ids;
        $name_list = $vol_names;
    } else {
        $id_list   = array_unique( array_merge( $host_ids, $vol_ids ) );
        $name_list = array_unique( array_merge( $host_names, $vol_names ) );
    }

    if ( $id_list ) {
        $placeholders = implode( ',', array_fill( 0, count( $id_list ), '%d' ) );
        $rows         = $wpdb->get_col( $wpdb->prepare( "SELECT email FROM {$members_table} WHERE wpuserid IN ($placeholders)", $id_list ) );
        foreach ( $rows as $em ) {
            $emails[] = sanitize_email( $em );
        }
    }

    foreach ( $name_list as $nm ) {
        $rows = $wpdb->get_col( $wpdb->prepare( "SELECT email FROM {$members_table} WHERE LOWER(CONCAT(first_name,' ',last_name)) = %s", $nm ) );
        foreach ( $rows as $em ) {
            $emails[] = sanitize_email( $em );
        }
    }

    $emails = array_values( array_unique( array_filter( $emails ) ) );
    $ttl = empty( $emails ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $emails, $ttl );
    return $emails;
}

/**
 * Retrieve host and volunteer names for an event.
 *
 * Supports numeric user IDs and legacy name-based values.
 *
 * @param int $event_id Event ID.
 * @return array {
 *     @type string[] $hosts      Host names.
 *     @type string[] $volunteers Volunteer names.
 * }
 */
function tta_get_event_host_volunteer_names( $event_id ) {
    $event_id = intval( $event_id );
    if ( ! $event_id ) {
        return [ 'hosts' => [], 'volunteers' => [] ];
    }

    $cache_key = 'event_host_vol_names_' . $event_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';

    $row = $wpdb->get_row(
        $wpdb->prepare( "SELECT hosts, volunteers FROM {$events_table} WHERE id = %d", $event_id ),
        ARRAY_A
    );
    if ( ! $row ) {
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT hosts, volunteers FROM {$archive_table} WHERE id = %d", $event_id ),
            ARRAY_A
        );
    }
    if ( ! $row ) {
        TTA_Cache::set( $cache_key, [ 'hosts' => [], 'volunteers' => [] ], 60 );
        return [ 'hosts' => [], 'volunteers' => [] ];
    }

    $parse = static function( $list ) {
        $ids = [];
        $names = [];
        foreach ( array_filter( array_map( 'trim', explode( ',', (string) $list ) ) ) as $val ) {
            if ( ctype_digit( $val ) ) {
                $ids[] = intval( $val );
            } else {
                $names[] = sanitize_text_field( $val );
            }
        }
        return [ $ids, $names ];
    };

    list( $host_ids, $host_names ) = $parse( $row['hosts'] ?? '' );
    list( $vol_ids,  $vol_names  ) = $parse( $row['volunteers'] ?? '' );

    $host_list = array_merge( tta_get_member_names_by_ids( $host_ids ), $host_names );
    $vol_list  = array_merge( tta_get_member_names_by_ids( $vol_ids ), $vol_names );

    $data = [
        'hosts'      => array_values( array_unique( array_filter( $host_list ) ) ),
        'volunteers' => array_values( array_unique( array_filter( $vol_list ) ) ),
    ];

    $ttl = ( empty( $data['hosts'] ) && empty( $data['volunteers'] ) ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $data, $ttl );
    return $data;
}

/**
 * Convert an array of member names to WordPress user IDs.
 *
 * Matching is case-insensitive on the concatenated first and last name.
 *
 * @param string[] $names Full names.
 * @return int[]  User IDs.
 */
function tta_get_member_ids_by_names( array $names ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $ids = [];
    foreach ( array_filter( $names ) as $name ) {
        $name = strtolower( trim( $name ) );
        if ( '' === $name ) {
            continue;
        }
        $rows = $wpdb->get_col( $wpdb->prepare( "SELECT wpuserid FROM {$members_table} WHERE LOWER(CONCAT(first_name,' ',last_name)) = %s", $name ) );
        foreach ( $rows as $id ) {
            $ids[] = intval( $id );
        }
    }
    return array_values( array_unique( $ids ) );
}

/**
 * Convert an array of WordPress user IDs to full member names.
 *
 * @param int[] $user_ids User IDs.
 * @return string[] Names in the same order as provided IDs.
 */
function tta_get_member_names_by_ids( array $user_ids ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $user_ids = array_filter( array_map( 'intval', $user_ids ) );
    if ( empty( $user_ids ) ) {
        return [];
    }
    $placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
    $rows = $wpdb->get_results( $wpdb->prepare( "SELECT wpuserid, first_name, last_name FROM {$members_table} WHERE wpuserid IN ($placeholders)", $user_ids ), ARRAY_A );
    $map = [];
    foreach ( $rows as $r ) {
        $map[ intval( $r['wpuserid'] ) ] = trim( sanitize_text_field( $r['first_name'] ) . ' ' . sanitize_text_field( $r['last_name'] ) );
    }
    $names = [];
    foreach ( $user_ids as $id ) {
        if ( isset( $map[ $id ] ) ) {
            $names[] = $map[ $id ];
        }
    }
    return $names;
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
        case 'reentry':
            return __( 'Re-Entry Ticket', 'tta' );
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
        case 'reentry':
            return TTA_REENTRY_TICKET_PRICE;
        default:
            return 0;
    }
}

/**
 * Fetch a member's current membership level by user ID.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return string free, basic or premium.
 */
function tta_get_user_membership_level( $wp_user_id ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $level = $wpdb->get_var( $wpdb->prepare( "SELECT membership_level FROM {$members_table} WHERE wpuserid = %d LIMIT 1", intval( $wp_user_id ) ) );
    $level = $level ? strtolower( $level ) : 'free';
    return in_array( $level, [ 'free', 'basic', 'premium' ], true ) ? $level : 'free';
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
 * Verify a member's subscription status at login.
 *
 * @param string  $user_login Username.
 * @param WP_User $user       Logged in user object.
 */
function tta_check_subscription_on_login( $user_login, $user ) {
    $wp_user_id = intval( $user->ID );
    if ( ! $wp_user_id ) {
        return;
    }

    $cache_key = 'login_sub_check_' . $wp_user_id;
    if ( TTA_Cache::get( $cache_key ) ) {
        return;
    }
    TTA_Cache::set( $cache_key, 1, HOUR_IN_SECONDS );

    tta_sync_subscription_status( $wp_user_id );
}

/**
 * Ensure a user's subscription status matches Authorize.Net.
 *
 * @param int $wp_user_id WordPress user ID.
 */
function tta_sync_subscription_status( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return;
    }

    $level  = tta_get_user_membership_level( $wp_user_id );
    $sub_id = tta_get_user_subscription_id( $wp_user_id );
    if ( ! $sub_id ) {
        return;
    }

    $info           = tta_get_subscription_status_info( $sub_id );
    $gateway_status = strtolower( $info['status'] ?? '' );
    if ( ! $gateway_status ) {
        return;
    }

    if ( in_array( $gateway_status, [ 'cancelled', 'canceled', 'terminated' ], true ) ) {
        $status = 'cancelled';
    } elseif ( 'active' === $gateway_status ) {
        $status = 'active';
    } else {
        $status = 'paymentproblem';
    }

    $current = tta_get_user_subscription_status( $wp_user_id );

    if ( 'active' !== $status ) {
        if ( 'free' !== $level ) {
            update_user_meta( $wp_user_id, 'tta_prev_level', $level );
        }
        tta_update_user_membership_level( $wp_user_id, 'free', null, $status );
        if ( $status !== $current ) {
            tta_log_subscription_status_change( $wp_user_id, $status );
        }
    } else {
        if ( 'free' === $level ) {
            $prev = get_user_meta( $wp_user_id, 'tta_prev_level', true );
            if ( ! in_array( $prev, [ 'basic', 'premium' ], true ) ) {
                $prev = 'basic';
            }
            tta_update_user_membership_level( $wp_user_id, $prev, null, 'active' );
            delete_user_meta( $wp_user_id, 'tta_prev_level' );
            if ( 'active' !== $current ) {
                tta_log_subscription_status_change( $wp_user_id, 'active' );
            }
        } elseif ( 'active' !== $current ) {
            tta_update_user_subscription_status( $wp_user_id, 'active' );
            tta_log_subscription_status_change( $wp_user_id, 'active' );
        }
    }
}

/**
 * Periodic front-end check to keep subscription statuses in sync.
 */
function tta_check_subscription_on_init() {
    if ( ! is_user_logged_in() ) {
        return;
    }
    $wp_user_id = get_current_user_id();
    $cache_key  = 'init_sub_check_' . $wp_user_id;
    if ( TTA_Cache::get( $cache_key ) ) {
        return;
    }
    TTA_Cache::set( $cache_key, 1, DAY_IN_SECONDS );
    tta_sync_subscription_status( $wp_user_id );
}

/**
 * Record a membership cancellation in the member history table.
 *
 * @param int    $wp_user_id WordPress user ID.
 * @param string $level      Membership level that was cancelled.
 * @param string $actor      Who cancelled (member or admin).
 */
function tta_log_membership_cancellation( $wp_user_id, $level, $actor = 'member' ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $hist_table    = $wpdb->prefix . 'tta_memberhistory';

    $member_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$members_table} WHERE wpuserid = %d LIMIT 1",
            intval( $wp_user_id )
        )
    );
    if ( ! $member_id ) {
        return;
    }

    $sub_id = tta_get_user_subscription_id( $wp_user_id );
    $last4  = $sub_id ? tta_get_subscription_card_last4( $sub_id ) : '';

    $data = [
        'by'            => sanitize_text_field( $actor ),
        'previous_level'=> sanitize_text_field( $level ),
        'card_last4'    => sanitize_text_field( $last4 ),
        'subscription_id' => sanitize_text_field( $sub_id ),
    ];

    $wpdb->insert(
        $hist_table,
        [
            'member_id'   => intval( $member_id ),
            'wpuserid'    => intval( $wp_user_id ),
            'event_id'    => 0,
            'action_type' => 'membership_cancel',
            'action_data' => wp_json_encode( $data ),
        ],
        [ '%d', '%d', '%d', '%s', '%s' ]
    );

    TTA_Cache::delete( 'billing_hist_' . $wp_user_id );
    TTA_Cache::delete( 'mem_cancel_' . $wp_user_id );
}

/**
 * Record a subscription status change in member history.
 *
 * @param int    $wp_user_id WordPress user ID.
 * @param string $status     New status value.
 */
function tta_log_subscription_status_change( $wp_user_id, $status ) {
    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $hist_table    = $wpdb->prefix . 'tta_memberhistory';

    $member_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$members_table} WHERE wpuserid = %d LIMIT 1",
            intval( $wp_user_id )
        )
    );
    if ( ! $member_id ) {
        return;
    }

    $wpdb->insert(
        $hist_table,
        [
            'member_id'   => intval( $member_id ),
            'wpuserid'    => intval( $wp_user_id ),
            'action_type' => 'subscription_status',
            'action_data' => wp_json_encode( [ 'status' => sanitize_text_field( $status ) ] ),
        ],
        [ '%d', '%d', '%s', '%s' ]
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
 * Get ban status info for a user.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array { type:string, weeks:int }
 */
function tta_get_user_ban_status( $wp_user_id ) {
    $until = tta_get_user_banned_until( $wp_user_id );
    if ( ! $until ) {
        return [ 'type' => 'none', 'weeks' => 0 ];
    }
    if ( TTA_BAN_UNTIL_INDEFINITE === $until ) {
        return [ 'type' => 'indefinite', 'weeks' => 0 ];
    }
    if ( TTA_BAN_UNTIL_REENTRY === $until ) {
        return [ 'type' => 'reentry', 'weeks' => 0 ];
    }
    $ts = strtotime( $until );
    if ( ! $ts || $ts <= time() ) {
        return [ 'type' => 'none', 'weeks' => 0 ];
    }
    $weeks = (int) ceil( ( $ts - time() ) / WEEK_IN_SECONDS );
    return [ 'type' => 'timed', 'weeks' => max( 1, $weeks ) ];
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
 * Clear any scheduled reinstatement cron for a user.
 *
 * @param int $wp_user_id WordPress user ID.
 */
function tta_clear_reinstatement_cron( $wp_user_id ) {
    $ts = wp_next_scheduled( 'tta_reinstate_member', [ intval( $wp_user_id ) ] );
    if ( false !== $ts ) {
        wp_unschedule_event( $ts, 'tta_reinstate_member', [ intval( $wp_user_id ) ] );
    }
}

/**
 * Remove a user's banned status.
 *
 * @param int $wp_user_id WordPress user ID.
 */
function tta_unban_user( $wp_user_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'tta_members';
    $wpdb->update( $table, [ 'banned_until' => null ], [ 'wpuserid' => intval( $wp_user_id ) ], [ '%s' ], [ '%d' ] );
    TTA_Cache::delete( 'banned_until_' . intval( $wp_user_id ) );
    TTA_Cache::delete( 'banned_members' );
    tta_clear_reinstatement_cron( $wp_user_id );
}

add_action( 'tta_reinstate_member', 'tta_unban_user', 10, 1 );

/**
 * Retrieve all currently banned members.
 *
 * @return array[] List of members with keys wpuserid, first_name, last_name, banned_until.
 */
function tta_get_banned_members() {
    $cache_key = 'banned_members';
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tta_members';
    $rows  = $wpdb->get_results( "SELECT wpuserid, first_name, last_name, banned_until FROM {$table} WHERE banned_until IS NOT NULL", ARRAY_A );

    TTA_Cache::set( $cache_key, $rows, $rows ? 60 : 30 );

    return $rows ?: [];
}

/**
 * Build a ban message and button flag for a user.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array { message:string, button:bool }
 */
function tta_get_ban_message( $wp_user_id ) {
    $status = tta_get_user_ban_status( $wp_user_id );
    switch ( $status['type'] ) {
        case 'indefinite':
            return [
                'message' => __( 'You are currently banned from purchasing tickets. Please use the <a href="/contact">contact form on our contact page</a> if you feel you\'ve been accidentally banned or would like to contest this.', 'tta' ),
                'button'  => false,
            ];
        case 'reentry':
            return [
                'message' => __( 'You are banned due to excessive event no-shows or other reasons and must purchase a re-entry ticket to attend events again.', 'tta' ),
                'button'  => true,
            ];
        case 'timed':
            $w = intval( $status['weeks'] );
            return [
                'message' => sprintf( __( 'You are banned for %d week(s) due to excessive event no-shows or other reasons. After %d week(s), you will be automatically reinstated.  You may purchase a Re-entry Ticket to become reinstated early.', 'tta' ), $w, $w ),
                'button'  => true,
            ];
    }
    return [ 'message' => '', 'button' => false ];
}

/**
 * Send the banned reinstatement email to a user.
 *
 * @param int $wp_user_id WordPress user ID.
 */
function tta_send_banned_reinstatement_email( $wp_user_id ) {
    $templates = tta_get_comm_templates();
    if ( empty( $templates['banned_reinstatement'] ) ) {
        return;
    }
    $tpl     = $templates['banned_reinstatement'];
    $context = tta_get_user_context_by_id( $wp_user_id );
    $tokens  = [
        '{first_name}' => $context['first_name'] ?? '',
        '{email}'      => $context['user_email'] ?? '',
    ];
    $sub_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
    $subject = tta_strip_bold( strtr( $sub_raw, $tokens ) );
    $body_raw = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
    $body_txt = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
    $body     = nl2br( $body_txt );
    $to       = sanitize_email( $context['user_email'] );
    if ( $to ) {
        wp_mail( $to, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
    }
}

/**
 * Send the no-show limit ban notification email to a user.
 *
 * @param int $wp_user_id WordPress user ID.
 */
function tta_send_no_show_ban_email( $wp_user_id ) {
    $templates = tta_get_comm_templates();
    if ( empty( $templates['no_show_limit'] ) ) {
        return;
    }
    $tpl     = $templates['no_show_limit'];
    $context = tta_get_user_context_by_id( $wp_user_id );
    $tokens  = [
        '{first_name}'   => $context['first_name'] ?? '',
        '{reentry_link}' => esc_url( home_url( '/checkout?auto=reentry' ) ),
    ];
    $sub_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
    $subject = tta_strip_bold( strtr( $sub_raw, $tokens ) );
    $body_raw = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
    $body_txt = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
    $body     = nl2br( $body_txt );
    $to       = sanitize_email( $context['user_email'] );
    if ( $to ) {
        wp_mail( $to, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
    }
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

    $info = tta_get_subscription_status_info( $subscription_id );
    if ( $info ) {
        TTA_Cache::set( $cache_key, $info['last4'], 600 );
        return $info['last4'];
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
 * Retrieve the status and last four digits for a subscription.
 *
 * @param string $subscription_id Authorize.Net subscription ID.
 * @return array{status:string,last4:string}|array
 */
function tta_get_subscription_status_info( $subscription_id ) {
    if ( ! $subscription_id ) {
        return [];
    }

    $cache_key = 'sub_status_' . $subscription_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    $api  = new TTA_AuthorizeNet_API();
    $info = $api->get_subscription_details( $subscription_id );
    if ( $info['success'] ) {
        $data = [
            'status'  => strtolower( $info['status'] ?? '' ),
            'last4'   => $info['card_last4'] ?? '',
            'amount'  => isset( $info['amount'] ) ? floatval( $info['amount'] ) : 0,
            'exp_date'=> $info['exp_date'] ?? '',
            'billing' => $info['billing'] ?? [],
        ];
        TTA_Cache::set( $cache_key, $data, 600 );
        return $data;
    }

    return [];
}

/**
 * Fetch the most recent membership cancellation record for a user.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array|null { date:string, by:string, level:string, card_last4:string }
 */
function tta_get_last_membership_cancellation( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return null;
    }
    $cache_key = 'mem_cancel_' . $wp_user_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $hist_table = $wpdb->prefix . 'tta_memberhistory';
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT action_data, action_date FROM {$hist_table} WHERE wpuserid = %d AND action_type = 'membership_cancel' ORDER BY action_date DESC LIMIT 1",
            $wp_user_id
        ),
        ARRAY_A
    );
    if ( ! $row ) {
        TTA_Cache::set( $cache_key, null, 300 );
        return null;
    }

    $data = json_decode( $row['action_data'], true );
    $info = [
        'date'       => $row['action_date'],
        'by'         => sanitize_text_field( $data['by'] ?? '' ),
        'level'      => sanitize_text_field( $data['previous_level'] ?? '' ),
        'card_last4' => sanitize_text_field( $data['card_last4'] ?? '' ),
    ];

    TTA_Cache::set( $cache_key, $info, 300 );
    return $info;
}

/**
 * Determine if a user has ever purchased a membership.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return bool
 */
function tta_user_had_membership( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return false;
    }

    $cache_key = 'mem_had_' . $wp_user_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return (bool) $cached;
    }

    global $wpdb;
    $members_table = $wpdb->prefix . 'tta_members';
    $hist_table    = $wpdb->prefix . 'tta_memberhistory';

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT membership_level, subscription_id FROM {$members_table} WHERE wpuserid = %d", $wp_user_id ), ARRAY_A );

    $has = false;
    if ( $row ) {
        $level = strtolower( $row['membership_level'] );
        $has   = in_array( $level, array( 'basic', 'premium' ), true ) || ! empty( $row['subscription_id'] );
    }

    if ( ! $has ) {
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$hist_table} WHERE wpuserid = %d AND action_type = 'membership_cancel'", $wp_user_id ) );
        $has   = $count > 0;
    }

    TTA_Cache::set( $cache_key, $has ? 1 : 0, 300 );
    return $has;
}

/**
 * Format a raw address string from the events table.
 *
 * @param string $raw Raw address ("street - addr2 - city - state - zip").
 * @return string Formatted address.
 */
function tta_format_address( $raw ) {
    $raw    = trim( $raw );
    $parts  = preg_split( '/\s[-–]\s/u', $raw );
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
    $raw   = trim( $raw );
    $parts = preg_split( '/\s[-–]\s/u', $raw );
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
 * Format event date and time together for display.
 *
 * @param string $date  Event date in YYYY-MM-DD format.
 * @param string $range Time range in "HH:MM|HH:MM" format.
 * @return string Human readable date and time.
 */
function tta_format_event_datetime( $date, $range ) {
    $date_ts  = strtotime( $date );
    $date_str = $date_ts ? date_i18n( 'l F j, Y', $date_ts ) : '';

    $parts = explode( '|', $range );
    $start = trim( $parts[0] ?? '' );
    $end   = trim( $parts[1] ?? '' );

    $time = '';
    if ( $start ) {
        $ts = strtotime( $start );
        $time = $ts ? date_i18n( 'g:i A', $ts ) : $start;
    }
    if ( $end ) {
        $ts2  = strtotime( $end );
        $time .= $time ? ' to ' : '';
        $time .= $ts2 ? date_i18n( 'g:i A', $ts2 ) : $end;
    }

    return trim( $date_str . ( $time ? ' - ' . $time : '' ) );
}

/**
 * Build a Google Maps URL for an address string.
 *
 * @param string $raw Raw address.
 * @return string URL.
 */
function tta_get_google_maps_url( $raw ) {
    $address = tta_format_address( $raw );
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $address );
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

    $events  = [];
    $txn_map = [];
    $refunds = [];
    $approved = [];
    $kept     = [];
    foreach ( $rows as $row ) {
        $data = json_decode( $row['action_data'], true );
        if ( ! is_array( $data ) ) {
            continue;
        }
        $txn_map[ $data['transaction_id'] ?? '' ] = 0;
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

    // Load refund requests for this member and merge counts
    $member_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tta_members WHERE wpuserid = %d", $wp_user_id ) );
    if ( $member_id ) {
        foreach ( tta_get_refund_requests() as $req ) {
            if ( intval( $req['member_id'] ) !== $member_id ) {
                continue;
            }
            $tx  = $req['transaction_id'];
            $tid = intval( $req['ticket_id'] );
            $refunds[ $tx ][ $tid ][] = $req;
            $txn_map[ $tx ] = isset( $txn_map[ $tx ] ) ? $txn_map[ $tx ] + 1 : 1;
        }

        if ( $events ) {
            $ids = array_column( $events, 'event_id' );
            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $refund_rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT event_id, action_data FROM {$hist_table} WHERE wpuserid = %d AND action_type = 'refund' AND event_id IN ($placeholders)",
                    $wp_user_id,
                    ...$ids
                ),
                ARRAY_A
            );
            foreach ( $refund_rows as $r ) {
                $data = json_decode( $r['action_data'], true );
                if ( floatval( $data['amount'] ?? 0 ) <= 0 ) {
                    continue;
                }
                $tx    = $data['transaction_id'] ?? '';
                $tid   = intval( $data['ticket_id'] ?? 0 );
                $email = sanitize_email( $data['attendee']['email'] ?? '' );
                if ( ! $tx || ! $tid || ! $email ) {
                    continue;
                }
                $entry = [
                    'first_name' => $data['attendee']['first_name'] ?? '',
                    'last_name'  => $data['attendee']['last_name'] ?? '',
                    'email'      => $email,
                    'amount'     => floatval( $data['amount'] ?? 0 ),
                ];
                if ( ! empty( $data['cancel'] ) ) {
                    if ( ! isset( $approved[ $tx ][ $tid ][ $email ] ) ) {
                        $txn_map[ $tx ] = isset( $txn_map[ $tx ] ) ? $txn_map[ $tx ] + 1 : 1;
                    }
                    $approved[ $tx ][ $tid ][ $email ] = $entry;
                } else {
                    $kept[ $tx ][ $tid ][ $email ] = $entry;
                }
            }
            // Flatten arrays for easier consumption later
            foreach ( $approved as $tx_key => &$tids ) {
                foreach ( $tids as $tid_key => &$ap ) {
                    $ap = array_values( $ap );
                }
                unset( $ap );
            }
            unset( $tids );
            foreach ( $kept as $tx_key => &$tids ) {
                foreach ( $tids as $tid_key => &$kp ) {
                    $kp = array_values( $kp );
                }
                unset( $kp );
            }
            unset( $tids );
        }
    }

    if ( $txn_map && ! property_exists( $wpdb, 'results_data' ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $txn_map ), '%s' ) );
        $tx_rows      = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, transaction_id, wpuserid FROM {$wpdb->prefix}tta_transactions WHERE transaction_id IN ($placeholders)",
                ...array_keys( $txn_map )
            ),
            ARRAY_A
        );

        $tx_ids   = [];
        $tx_users = [];
        foreach ( $tx_rows as $tr ) {
            $tx_ids[ $tr['transaction_id'] ] = intval( $tr['id'] );
            $tx_users[ $tr['transaction_id'] ] = intval( $tr['wpuserid'] );
        }

        if ( $tx_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $tx_ids ), '%d' ) );
            $counts       = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT transaction_id, COUNT(*) AS cnt FROM {$wpdb->prefix}tta_attendees WHERE transaction_id IN ($placeholders) GROUP BY transaction_id",
                    ...array_values( $tx_ids )
                ),
                ARRAY_A
            );

            foreach ( $counts as $c ) {
                $tid = array_search( intval( $c['transaction_id'] ), $tx_ids, true );
                if ( $tid ) {
                    $txn_map[ $tid ] = intval( $c['cnt'] );
                }
            }
        }

        if ( $txn_map ) {
            $events = array_filter(
                $events,
                static function ( $ev ) use ( $txn_map ) {
                    $tx = $ev['transaction_id'] ?? '';
                    return isset( $txn_map[ $tx ] ) && $txn_map[ $tx ] > 0;
                }
            );
            $events = array_values( $events );

            // Refresh attendee lists using the current database state
            foreach ( $events as &$ev ) {
                $gateway_tx = $ev['transaction_id'] ?? '';
                if ( ! isset( $tx_ids[ $gateway_tx ] ) ) {
                    continue;
                }
                $internal_tx = $tx_ids[ $gateway_tx ];
                $purchaser   = $tx_users[ $gateway_tx ] ?? 0;
                $new_items   = [];
                foreach ( $ev['items'] as $item ) {
                    $tid = intval( $item['ticket_id'] ?? 0 );
                    if ( ! $tid ) {
                        continue;
                    }
                    $attendees = array_filter(
                        tta_get_ticket_attendees( $tid ),
                        static function ( $a ) use ( $internal_tx ) {
                            return intval( $a['transaction_id'] ) === $internal_tx;
                        }
                    );
                    $item['attendees']    = array_values( $attendees );
                    $item['quantity']     = count( $item['attendees'] );
                    $item['purchaser_id'] = $purchaser;
                    $item['refund_pending'] = false;
                    if ( isset( $kept[ $gateway_tx ][ $tid ] ) ) {
                        foreach ( $kept[ $gateway_tx ][ $tid ] as $kp ) {
                            $clone = $item;
                            $clone['refund_keep']   = true;
                            $clone['refund_amount'] = $kp['amount'];
                            $clone['refund_attendee'] = [
                                'first_name' => $kp['first_name'],
                                'last_name'  => $kp['last_name'],
                                'email'      => $kp['email'],
                            ];
                            $clone['quantity']  = 1;
                            $clone['attendees'] = [];
                            $new_items[]        = $clone;
                            $attendees = array_filter(
                                $attendees,
                                static function ( $a ) use ( $kp ) {
                                    return strtolower( $a['email'] ) !== strtolower( $kp['email'] );
                                }
                            );
                        }
                        $item['attendees'] = array_values( $attendees );
                        $item['quantity']  = count( $item['attendees'] );
                    }
                    if ( isset( $approved[ $gateway_tx ][ $tid ] ) ) {
                        foreach ( $approved[ $gateway_tx ][ $tid ] as $ap ) {
                            $clone = $item;
                            $clone['refund_approved'] = true;
                            $clone['refund_amount']   = $ap['amount'];
                            $clone['refund_attendee'] = [
                                'first_name' => $ap['first_name'],
                                'last_name'  => $ap['last_name'],
                                'email'      => $ap['email'],
                            ];
                            $clone['quantity'] = 1;
                            $clone['attendees'] = [];
                            $new_items[] = $clone;
                        }
                    }
                    if ( isset( $refunds[ $gateway_tx ][ $tid ] ) ) {
                        foreach ( $refunds[ $gateway_tx ][ $tid ] as $req ) {
                            $clone = $item;
                            $clone['refund_pending'] = true;
                            $clone['refund_attendee'] = [
                                'first_name' => $req['first_name'],
                                'last_name'  => $req['last_name'],
                                'email'      => $req['email'],
                            ];
                            $clone['quantity'] = 1;
                            $clone['attendees'] = [];
                            $new_items[] = $clone;
                            if ( 'keep' === ( $req['mode'] ?? '' ) ) {
                                $attendees = array_filter(
                                    $attendees,
                                    static function ( $a ) use ( $req ) {
                                        return strtolower( $a['email'] ) !== strtolower( $req['email'] );
                                    }
                                );
                            }
                        }
                        $item['attendees'] = array_values( $attendees );
                        $item['quantity']  = count( $item['attendees'] );
                    }
                    if ( $item['quantity'] > 0 ) {
                        $new_items[] = $item;
                    }
                }
                $ev['items'] = $new_items;
            }
            unset( $ev );
            $events = array_filter( $events, static function ( $e ) {
                return ! empty( $e['items'] );
            } );
            $events = array_values( $events );
        }
    }

    // Split items so each attendee gets an individual entry
    foreach ( $events as &$ev ) {
        $split_items = [];
        foreach ( $ev['items'] as $item ) {
            $attendees = $item['attendees'] ?? [];
            if ( ! empty( $item['refund_pending'] ) ) {
                // keep a row for the pending refund attendee
                $pending            = $item;
                $pending['quantity'] = 1;
                $pending['attendees'] = [];
                $split_items[]      = $pending;

                // show remaining attendees separately with refund links
                foreach ( $attendees as $att ) {
                    $clone                     = $item;
                    $clone['refund_pending']   = false;
                    unset( $clone['refund_attendee'] );
                    $clone['attendees']        = [ $att ];
                    $clone['quantity']         = 1;
                    $split_items[]             = $clone;
                }
                continue;
            }

            if ( ! empty( $item['refund_approved'] ) ) {
                $item['quantity'] = 1;
                $split_items[] = $item;
                continue;
            }

            $qty = intval( $item['quantity'] ?? count( $attendees ) );
            if ( $attendees ) {
                foreach ( $attendees as $att ) {
                    $clone              = $item;
                    $clone['attendees'] = [ $att ];
                    $clone['quantity']  = 1;
                    $split_items[]      = $clone;
                }
            } elseif ( $qty > 1 ) {
                for ( $i = 0; $i < $qty; $i++ ) {
                    $clone              = $item;
                    $clone['quantity']  = 1;
                    $split_items[]      = $clone;
                }
            } else {
                $split_items[] = $item;
            }
        }
        $ev['items'] = $split_items;
    }
    unset( $ev );

    $ttl = empty( $events ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $events, $ttl );

    return $events;
}

/**
 * Get a summary of a member's attendance and total savings.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array {
 *     @type int   $attended Number of events attended.
 *     @type int   $no_show  Number of no-shows.
 *     @type float $savings  Total amount saved from discounts.
 * }
 */
function tta_get_member_attendance_summary( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return [ 'attended' => 0, 'no_show' => 0, 'savings' => 0 ];
    }

    $cache_key = 'attendance_summary_' . $wp_user_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    $events   = tta_get_member_past_events( $wp_user_id );
    $user     = get_userdata( $wp_user_id );
    $email    = $user ? strtolower( $user->user_email ) : '';
    $attended = 0;
    $no_show  = 0;
    foreach ( $events as $ev ) {
        $found = '';
        foreach ( $ev['items'] as $item ) {
            foreach ( (array) ( $item['attendees'] ?? [] ) as $att ) {
                if ( $email && strtolower( $att['email'] ) === $email ) {
                    $found = $att['status'] ?? '';
                    break 2;
                }
            }
        }
        if ( 'checked_in' === $found ) {
            $attended++;
        } elseif ( 'no_show' === $found ) {
            $no_show++;
        }
    }

    global $wpdb;
    $tx_table = $wpdb->prefix . 'tta_transactions';
    $savings  = (float) $wpdb->get_var( $wpdb->prepare(
        "SELECT SUM(discount_saved) FROM {$tx_table} WHERE wpuserid = %d",
        $wp_user_id
    ) );

    $summary = [
        'attended' => $attended,
        'no_show'  => $no_show,
        'savings'  => $savings,
    ];

    TTA_Cache::set( $cache_key, $summary, 300 );
    return $summary;
}

/**
 * Retrieve waitlist events for a user.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array[] List of waitlist entries.
 */
function tta_get_member_waitlist_events( $wp_user_id ) {
    $wp_user_id = intval( $wp_user_id );
    if ( ! $wp_user_id ) {
        return [];
    }

    $cache_key = 'waitlist_events_' . $wp_user_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $waitlist_table = $wpdb->prefix . 'tta_waitlist';
    $events_table   = $wpdb->prefix . 'tta_events';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT w.ticket_id, w.ticket_name, w.event_name, w.event_ute_id, w.added_at, e.id AS event_id, e.page_id, e.mainimageid, e.date, e.time, e.address FROM {$waitlist_table} w JOIN {$events_table} e ON w.event_ute_id = e.ute_id WHERE w.wp_user_id = %d ORDER BY w.added_at ASC",
            $wp_user_id
        ),
        ARRAY_A
    );

    $events = [];
    foreach ( $rows as $r ) {
        $events[] = [
            'event_id'     => intval( $r['event_id'] ),
            'event_ute_id' => sanitize_text_field( $r['event_ute_id'] ),
            'name'         => sanitize_text_field( $r['event_name'] ),
            'page_id'      => intval( $r['page_id'] ),
            'image_id'     => intval( $r['mainimageid'] ),
            'date'         => $r['date'],
            'time'         => $r['time'],
            'address'      => sanitize_text_field( $r['address'] ),
            'ticket_id'    => intval( $r['ticket_id'] ),
            'ticket_name'  => sanitize_text_field( $r['ticket_name'] ),
            'added_at'     => $r['added_at'],
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

    $events  = [];
    $txn_map = [];
    $refunds = [];
    $approved = [];
    foreach ( $rows as $row ) {
        $data = json_decode( $row['action_data'], true );
        if ( ! is_array( $data ) ) {
            continue;
        }
        $txn_map[ $data['transaction_id'] ?? '' ] = 0;
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

    $member_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tta_members WHERE wpuserid = %d", $wp_user_id ) );
    if ( $member_id ) {
        foreach ( tta_get_refund_requests() as $req ) {
            if ( intval( $req['member_id'] ) !== $member_id ) {
                continue;
            }
            $tx  = $req['transaction_id'];
            $tid = intval( $req['ticket_id'] );
            $refunds[ $tx ][ $tid ] = $req;
            if ( isset( $txn_map[ $tx ] ) ) {
                $txn_map[ $tx ] += 1;
            } else {
                $txn_map[ $tx ] = 1;
            }
        }

        if ( $events ) {
            $ids = array_column( $events, 'event_id' );
            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $refund_rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT event_id, action_data FROM {$hist_table} WHERE wpuserid = %d AND action_type = 'refund' AND event_id IN ($placeholders)",
                    $wp_user_id,
                    ...$ids
                ),
                ARRAY_A
            );
            foreach ( $refund_rows as $r ) {
                $data = json_decode( $r['action_data'], true );
                if ( empty( $data['cancel'] ) || floatval( $data['amount'] ?? 0 ) <= 0 ) {
                    continue;
                }
                $tx  = $data['transaction_id'] ?? '';
                $tid = intval( $data['ticket_id'] ?? 0 );
                $email = sanitize_email( $data['attendee']['email'] ?? '' );
                if ( ! $tx || ! $tid || ! $email ) {
                    continue;
                }
                if ( ! isset( $approved[ $tx ][ $tid ][ $email ] ) ) {
                    $txn_map[ $tx ] = isset( $txn_map[ $tx ] ) ? $txn_map[ $tx ] + 1 : 1;
                }
                $approved[ $tx ][ $tid ][ $email ] = [
                    'first_name' => $data['attendee']['first_name'] ?? '',
                    'last_name'  => $data['attendee']['last_name'] ?? '',
                    'email'      => $email,
                    'amount'     => floatval( $data['amount'] ?? 0 ),
                ];
            }
            foreach ( $approved as $tx_key => &$tids ) {
                foreach ( $tids as $tid_key => &$ap ) {
                    $ap = array_values( $ap );
                }
                unset( $ap );
            }
            unset( $tids );
        }
    }

    if ( $txn_map && ! property_exists( $wpdb, 'results_data' ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $txn_map ), '%s' ) );
        $tx_rows      = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, transaction_id FROM {$wpdb->prefix}tta_transactions WHERE transaction_id IN ($placeholders)",
                ...array_keys( $txn_map )
            ),
            ARRAY_A
        );

        $tx_ids = [];
        foreach ( $tx_rows as $tr ) {
            $tx_ids[ $tr['transaction_id'] ] = intval( $tr['id'] );
        }

        if ( $tx_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $tx_ids ), '%d' ) );
            $counts       = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT transaction_id, COUNT(*) AS cnt FROM {$wpdb->prefix}tta_attendees WHERE transaction_id IN ($placeholders) GROUP BY transaction_id",
                    ...array_values( $tx_ids )
                ),
                ARRAY_A
            );

            foreach ( $counts as $c ) {
                $tid = array_search( intval( $c['transaction_id'] ), $tx_ids, true );
                if ( $tid ) {
                    $txn_map[ $tid ] = intval( $c['cnt'] );
                }
            }
        }

        if ( $txn_map ) {
            $events = array_filter(
                $events,
                static function ( $ev ) use ( $txn_map ) {
                    $tx = $ev['transaction_id'] ?? '';
                    return isset( $txn_map[ $tx ] ) && $txn_map[ $tx ] > 0;
                }
            );
            $events = array_values( $events );

            foreach ( $events as &$ev ) {
                $gateway_tx = $ev['transaction_id'] ?? '';
                if ( ! isset( $tx_ids[ $gateway_tx ] ) ) {
                    continue;
                }
                $internal_tx = $tx_ids[ $gateway_tx ];
                $new_items   = [];
                foreach ( $ev['items'] as $item ) {
                    $tid = intval( $item['ticket_id'] ?? 0 );
                    if ( ! $tid ) {
                        continue;
                    }
                    $attendees = array_filter(
                        tta_get_ticket_attendees( $tid ),
                        static function ( $a ) use ( $internal_tx ) {
                            return intval( $a['transaction_id'] ) === $internal_tx;
                        }
                    );
                    $item['attendees'] = array_values( $attendees );
                    $item['quantity']  = count( $item['attendees'] );
                    $item['refund_pending'] = false;
                    if ( isset( $approved[ $gateway_tx ][ $tid ] ) ) {
                        foreach ( $approved[ $gateway_tx ][ $tid ] as $ap ) {
                            $clone = $item;
                            $clone['refund_approved'] = true;
                            $clone['refund_amount']   = $ap['amount'];
                            $clone['refund_attendee'] = [
                                'first_name' => $ap['first_name'],
                                'last_name'  => $ap['last_name'],
                                'email'      => $ap['email'],
                            ];
                            $clone['quantity'] = 1;
                            $clone['attendees'] = [];
                            $new_items[] = $clone;
                        }
                    }
                    if ( isset( $refunds[ $gateway_tx ][ $tid ] ) ) {
                        $req  = $refunds[ $gateway_tx ][ $tid ];
                        $item['refund_pending'] = true;
                        $item['refund_attendee'] = [
                            'first_name' => $req['first_name'],
                            'last_name'  => $req['last_name'],
                            'email'      => $req['email'],
                        ];
                        if ( 0 === $item['quantity'] ) {
                            $item['quantity'] = 1;
                        }
                        $new_items[] = $item;
                    } elseif ( $item['quantity'] > 0 ) {
                        $new_items[] = $item;
                    }
                }
                $ev['items'] = $new_items;
            }
            unset( $ev );
            $events = array_filter( $events, static function ( $e ) {
                return ! empty( $e['items'] );
            } );
            $events = array_values( $events );
        }
    }

    foreach ( $events as &$ev ) {
        $split_items = [];
        foreach ( $ev['items'] as $item ) {
            $attendees = $item['attendees'] ?? [];
            if ( ! empty( $item['refund_pending'] ) ) {
                $pending            = $item;
                $pending['quantity'] = 1;
                $pending['attendees'] = [];
                $split_items[]      = $pending;

                foreach ( $attendees as $att ) {
                    $clone                     = $item;
                    $clone['refund_pending']   = false;
                    unset( $clone['refund_attendee'] );
                    $clone['attendees']        = [ $att ];
                    $clone['quantity']         = 1;
                    $split_items[]             = $clone;
                }
                continue;
            }

            if ( ! empty( $item['refund_approved'] ) ) {
                $item['quantity'] = 1;
                $split_items[] = $item;
                continue;
            }

            $qty = intval( $item['quantity'] ?? count( $attendees ) );
            if ( $attendees ) {
                foreach ( $attendees as $att ) {
                    $clone              = $item;
                    $clone['attendees'] = [ $att ];
                    $clone['quantity']  = 1;
                    $split_items[]      = $clone;
                }
            } elseif ( $qty > 1 ) {
                for ( $i = 0; $i < $qty; $i++ ) {
                    $clone              = $item;
                    $clone['quantity']  = 1;
                    $split_items[]      = $clone;
                }
            } else {
                $split_items[] = $item;
            }
        }
        $ev['items'] = $split_items;
    }
    unset( $ev );

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
    $members_table = $wpdb->prefix . 'tta_members';

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

    // Subtract any refunds issued to the member.
    $refund_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT action_data FROM {$hist_table} WHERE member_id = %d AND action_type = 'refund'",
            $member_id
        ),
        ARRAY_A
    );
    foreach ( $refund_rows as $row ) {
        $data   = json_decode( $row['action_data'], true );
        $amount = floatval( $data['amount'] ?? 0 );
        $summary['total_spent'] -= $amount;
    }

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
        "SELECT COUNT(*) FROM {$hist_table} WHERE member_id = %d AND action_type = 'refund'",
        $member_id
    ) );

    $summary['cancellations'] = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$hist_table} WHERE member_id = %d AND action_type = 'cancel_request'",
        $member_id
    ) );

    // Include membership purchases in the total spent calculation
    $wp_user_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wpuserid FROM {$members_table} WHERE id = %d LIMIT 1",
            $member_id
        )
    );
    if ( $wp_user_id ) {
        $tx_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT details FROM {$tx_table} WHERE wpuserid = %d",
                $wp_user_id
            ),
            ARRAY_A
        );
        $membership_total = 0;
        foreach ( $tx_rows as $tx_row ) {
            $items = json_decode( $tx_row['details'], true );
            if ( ! is_array( $items ) ) {
                continue;
            }
            foreach ( $items as $it ) {
                if ( empty( $it['membership'] ) ) {
                    continue;
                }
                $price = isset( $it['final_price'] ) ? floatval( $it['final_price'] ) : floatval( $it['price'] );
                $qty   = intval( $it['quantity'] ?? 1 );
                $membership_total += $price * $qty;
            }
        }

        // Include recurring subscription charges from Authorize.Net
        $sub_id = tta_get_user_subscription_id( $wp_user_id );
        if ( $sub_id ) {
            foreach ( tta_get_subscription_transactions( $sub_id ) as $sub_tx ) {
                $membership_total += floatval( $sub_tx['amount'] );
            }
        }

        $summary['total_spent'] += $membership_total;
    }

    TTA_Cache::set( $cache_key, $summary, 300 );
    return $summary;
}

/**
 * Retrieve a member's full billing history including subscription charges.
 *
 * @param int $wp_user_id WordPress user ID.
 * @return array[] {
 *     @type string $date   Transaction date.
 *     @type string $description Description of the item.
 *     @type float  $amount Amount charged or refunded.
 *     @type string $url    Optional link to the item.
 *     @type string $type   Transaction type.
 *     @type string $method Payment method description.
 * }
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
    $tx_table   = $wpdb->prefix . 'tta_transactions';
    $hist_table = $wpdb->prefix . 'tta_memberhistory';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT transaction_id, amount, card_last4, details, created_at FROM {$tx_table} WHERE wpuserid = %d ORDER BY created_at DESC",
            $wp_user_id
        ),
        ARRAY_A
    );

    $refund_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT event_id, action_data, action_date FROM {$hist_table} WHERE wpuserid = %d AND action_type = 'refund' ORDER BY action_date DESC",
            $wp_user_id
        ),
        ARRAY_A
    );

    $history    = [];
    $event_map  = [];
    $refund_ids = [];
    foreach ( $refund_rows as $r ) {
        $eid = intval( $r['event_id'] );
        if ( $eid ) {
            $refund_ids[] = $eid;
        }
    }

    if ( $refund_ids ) {
        $placeholders = implode( ',', array_fill( 0, count( $refund_ids ), '%d' ) );
        $events_table  = $wpdb->prefix . 'tta_events';
        $archive_table = $wpdb->prefix . 'tta_events_archive';
        $ev_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name, page_id FROM {$events_table} WHERE id IN ($placeholders) UNION SELECT id, name, page_id FROM {$archive_table} WHERE id IN ($placeholders)",
                [...$refund_ids, ...$refund_ids]
            ),
            ARRAY_A
        );
        foreach ( $ev_rows as $er ) {
            $event_map[ intval( $er['id'] ) ] = [
                'name'    => sanitize_text_field( $er['name'] ),
                'page_id' => intval( $er['page_id'] ),
            ];
        }
    }
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

            $last4  = sanitize_text_field( $row['card_last4'] );
            $method = $last4 ? sprintf( __( 'Credit Card (**** **** **** %s)', 'tta' ), $last4 ) : __( 'Credit Card', 'tta' );

            $type = empty( $it['membership'] ) ? 'purchase' : 'membership subscription';
            $history[] = [
                'date'           => $row['created_at'],
                'description'    => sanitize_text_field( $name ),
                'amount'         => $price,
                'url'            => $url,
                'type'           => $type,
                'method'         => $method,
                'transaction_id' => sanitize_text_field( $row['transaction_id'] ),
            ];
        }
    }

    foreach ( $refund_rows as $row ) {
        $data = json_decode( $row['action_data'], true );
        if ( ! is_array( $data ) ) {
            continue;
        }
        if ( empty( $data['amount'] ) && ! empty( $data['cancel'] ) ) {
            continue; // skip cancel without refund
        }
        $amount  = -floatval( $data['amount'] ?? 0 );
        $eid     = intval( $row['event_id'] );
        $name    = $event_map[ $eid ]['name'] ?? __( 'Refund', 'tta' );
        $page_id = $event_map[ $eid ]['page_id'] ?? 0;
        $url     = '';
        if ( $page_id && function_exists( 'get_permalink' ) ) {
            $url = get_permalink( $page_id );
        }
        $tx_id  = sanitize_text_field( $data['transaction_id'] ?? '' );
        $last4  = '';
        if ( $tx_id ) {
            $last4 = (string) $wpdb->get_var( $wpdb->prepare( "SELECT card_last4 FROM {$tx_table} WHERE transaction_id = %s LIMIT 1", $tx_id ) );
        }
        $method = $last4 ? sprintf( __( 'Credit Card (**** **** **** %s)', 'tta' ), $last4 ) : __( 'Credit Card', 'tta' );

        $history[] = [
            'date'           => $row['action_date'],
            'description'    => sanitize_text_field( $name ),
            'amount'         => $amount,
            'url'            => $url,
            'type'           => 'refund',
            'method'         => $method,
            'transaction_id' => $tx_id,
        ];
    }

    $sub_id = tta_get_user_subscription_id( $wp_user_id );
    if ( $sub_id ) {
        $last4  = tta_get_subscription_card_last4( $sub_id );
        $method = $last4 ? sprintf( __( 'Credit Card (**** **** **** %s)', 'tta' ), $last4 ) : __( 'Credit Card', 'tta' );
        foreach ( tta_get_subscription_transactions( $sub_id ) as $sub_tx ) {
            $label = __( 'Membership Charge', 'tta' );
            $history[] = [
                'date'           => $sub_tx['date'],
                'description'    => $label,
                'amount'         => floatval( $sub_tx['amount'] ),
                'type'           => 'membership subscription',
                'method'         => $method,
                'transaction_id' => $sub_tx['id'],
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
 * Retrieve all refund requests submitted by members.
 *
 * @return array[] List of refund requests.
 */
function tta_get_refund_requests() {
    $cache_key = 'tta_refund_requests';
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $hist_table   = $wpdb->prefix . 'tta_memberhistory';
    $members_table= $wpdb->prefix . 'tta_members';
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';

    $rows = $wpdb->get_results(
        "SELECT mh.id, mh.member_id, mh.action_date, mh.action_data, mh.event_id, m.first_name, m.last_name,
                COALESCE(e.name, ea.name) AS event_name,
                COALESCE(e.page_id, ea.page_id) AS page_id
           FROM {$hist_table} mh
           JOIN {$members_table} m ON mh.member_id = m.id
      LEFT JOIN {$events_table} e ON mh.event_id = e.id
      LEFT JOIN {$archive_table} ea ON mh.event_id = ea.id
         WHERE mh.action_type = 'refund_request'
      ORDER BY mh.action_date ASC",
        ARRAY_A
    );

    $out = [];
    foreach ( $rows as $r ) {
        $data = json_decode( $r['action_data'], true );
        $att = $data['attendee'] ?? [];
        $attendee = [
            'id'          => intval( $att['id'] ?? 0 ),
            'first_name'  => sanitize_text_field( $att['first_name'] ?? '' ),
            'last_name'   => sanitize_text_field( $att['last_name'] ?? '' ),
            'email'       => sanitize_email( $att['email'] ?? '' ),
            'phone'       => sanitize_text_field( $att['phone'] ?? '' ),
            'amount_paid' => isset( $att['amount_paid'] ) ? floatval( $att['amount_paid'] ) : 0,
        ];

        $out[] = [
            'history_id'    => intval( $r['id'] ),
            'date'          => $r['action_date'],
            'member_id'     => intval( $r['member_id'] ),
            'member_name'   => trim( $r['first_name'] . ' ' . $r['last_name'] ),
            'event_id'      => intval( $r['event_id'] ),
            'event_name'    => sanitize_text_field( $r['event_name'] ),
            'event_url'     => $r['page_id'] ? get_permalink( $r['page_id'] ) : '',
            'transaction_id'=> sanitize_text_field( $data['transaction_id'] ?? '' ),
            'ticket_id'     => intval( $data['ticket_id'] ?? 0 ),
            'reason'        => sanitize_text_field( $data['reason'] ?? '' ),
            'mode'          => sanitize_text_field( $data['mode'] ?? '' ),
            'pending_reason'=> sanitize_text_field( $data['pending_reason'] ?? '' ),
            'attendee_id'   => $attendee['id'],
            'first_name'    => $attendee['first_name'],
            'last_name'     => $attendee['last_name'],
            'email'         => $attendee['email'],
            'phone'         => $attendee['phone'],
            'amount_paid'   => $attendee['amount_paid'],
            'attendee'      => $attendee,
        ];
    }

    TTA_Cache::set( $cache_key, $out, 300 );
    return $out;
}

/**
 * Retrieve attendee details for a specific refund request.
 *
 * @param string $gateway_tx_id Gateway transaction ID.
 * @param int    $event_id      Event ID.
 * @return array[]
 */
function tta_get_refund_request_attendees( $gateway_tx_id, $event_id, $ticket_id = 0 ) {
    $gateway_tx_id = sanitize_text_field( $gateway_tx_id );
    $event_id      = intval( $event_id );
    $ticket_id     = intval( $ticket_id );
    if ( '' === $gateway_tx_id || ! $event_id ) {
        return [];
    }

    $cache_key = 'refund_attendees_' . md5( $gateway_tx_id . '_' . $event_id . '_' . $ticket_id );
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $tx_table       = $wpdb->prefix . 'tta_transactions';
    $att_table      = $wpdb->prefix . 'tta_attendees';
    $att_archive    = $wpdb->prefix . 'tta_attendees_archive';
    $ticket_table   = $wpdb->prefix . 'tta_tickets';
    $ticket_archive = $wpdb->prefix . 'tta_tickets_archive';
    $events_table   = $wpdb->prefix . 'tta_events';
    $archive_table  = $wpdb->prefix . 'tta_events_archive';

    $tx = $wpdb->get_row( $wpdb->prepare( "SELECT id, details, created_at FROM {$tx_table} WHERE transaction_id = %s", $gateway_tx_id ), ARRAY_A );
    if ( ! $tx ) {
        TTA_Cache::set( $cache_key, [], 60 );
        return [];
    }

    $ute_id = $wpdb->get_var( $wpdb->prepare( "SELECT ute_id FROM {$events_table} WHERE id = %d UNION SELECT ute_id FROM {$archive_table} WHERE id = %d LIMIT 1", $event_id, $event_id ) );
    if ( ! $ute_id ) {
        TTA_Cache::set( $cache_key, [], 60 );
        return [];
    }

    $ticket_sql = $ticket_id ? ' AND a.ticket_id = %d' : '';
    $sql = "(SELECT a.id, a.ticket_id, a.first_name, a.last_name, a.email, a.phone, a.status FROM {$att_table} a JOIN {$ticket_table} t ON a.ticket_id = t.id WHERE a.transaction_id = %d AND t.event_ute_id = %s{$ticket_sql}) UNION ALL (SELECT a.id, a.ticket_id, a.first_name, a.last_name, a.email, a.phone, a.status FROM {$att_archive} a JOIN {$ticket_archive} t ON a.ticket_id = t.id WHERE a.transaction_id = %d AND t.event_ute_id = %s{$ticket_sql}) ORDER BY last_name, first_name";
    $params = $ticket_id ? [ $tx['id'], $ute_id, $ticket_id, $tx['id'], $ute_id, $ticket_id ] : [ $tx['id'], $ute_id, $tx['id'], $ute_id ];
    $rows = $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );

    $details = json_decode( $tx['details'], true );
    $price_map = [];
    if ( is_array( $details ) ) {
        foreach ( $details as $item ) {
            if ( ( $item['event_ute_id'] ?? '' ) !== $ute_id ) {
                continue;
            }
            $tid   = intval( $item['ticket_id'] ?? 0 );
            $price = floatval( $item['final_price'] ?? ( $item['price'] ?? 0 ) );
            $price_map[ $tid ] = $price;
        }
    }

    $attendees = [];
    foreach ( $rows as $r ) {
        $tid = intval( $r['ticket_id'] );
        $attendees[] = [
            'id'          => intval( $r['id'] ),
            'first_name'  => sanitize_text_field( $r['first_name'] ),
            'last_name'   => sanitize_text_field( $r['last_name'] ),
            'email'       => sanitize_email( $r['email'] ),
            'phone'       => sanitize_text_field( $r['phone'] ),
            'status'      => sanitize_text_field( $r['status'] ?? 'pending' ),
            'amount_paid' => $price_map[ $tid ] ?? 0,
            'gateway_id'  => $gateway_tx_id,
            'created_at'  => $tx['created_at'],
        ];
    }

    $ttl = empty( $attendees ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $attendees, $ttl );

    return $attendees;
}

/**
 * Delete a pending refund request once processed.
 *
 * @param string $gateway_tx_id Gateway transaction ID.
 * @param int    $ticket_id     Ticket ID.
 */
function tta_delete_refund_request( $gateway_tx_id, $ticket_id = 0, $attendee_id = 0 ) {
    global $wpdb;
    $hist_table = $wpdb->prefix . 'tta_memberhistory';
    $gateway_tx_id = sanitize_text_field( $gateway_tx_id );
    $ticket_id     = intval( $ticket_id );
    $attendee_id   = intval( $attendee_id );

    if ( '' === $gateway_tx_id ) {
        return;
    }

    $sql    = "DELETE FROM {$hist_table} WHERE action_type = 'refund_request' AND action_data LIKE %s";
    $params = [ '%' . $wpdb->esc_like( '"transaction_id":"' . $gateway_tx_id . '"' ) . '%' ];

    if ( $ticket_id ) {
        $sql   .= ' AND action_data LIKE %s';
        $params[] = '%' . $wpdb->esc_like( '"ticket_id":' . $ticket_id ) . '%';
    }
    if ( $attendee_id ) {
        $sql   .= ' AND action_data LIKE %s';
        $params[] = '%' . $wpdb->esc_like( '"attendee":{"id":' . $attendee_id ) . '%';
    }

    $wpdb->query( $wpdb->prepare( $sql, ...$params ) );
    TTA_Cache::delete( 'tta_refund_requests' );
    TTA_Cache::flush();
}

/**
 * Fetch the next pending refund request for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @return array|null Request row or null.
 */
function tta_get_next_refund_request_for_ticket( $ticket_id ) {
    global $wpdb;
    $hist_table = $wpdb->prefix . 'tta_memberhistory';
    $ticket_id  = intval( $ticket_id );
    if ( ! $ticket_id ) {
        return null;
    }

    $like = '%' . $wpdb->esc_like( '"ticket_id":' . $ticket_id ) . '%';
    $row  = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$hist_table} WHERE action_type='refund_request' AND action_data LIKE %s ORDER BY action_date ASC LIMIT 1",
            $like
        ),
        ARRAY_A
    );
    if ( ! $row ) {
        return null;
    }

    $data = json_decode( $row['action_data'], true );
    return [
        'member_id'     => intval( $row['member_id'] ),
        'wpuserid'      => intval( $row['wpuserid'] ),
        'event_id'      => intval( $row['event_id'] ),
        'transaction_id'=> sanitize_text_field( $data['transaction_id'] ?? '' ),
        'ticket_id'     => intval( $data['ticket_id'] ?? 0 ),
        'reason'        => sanitize_text_field( $data['reason'] ?? '' ),
        'attendee'      => $data['attendee'] ?? [],
    ];
}

/**
 * Fetch a specific refund request by transaction and ticket.
 *
 * @param string $gateway_tx_id Gateway ID.
 * @param int    $ticket_id     Ticket ID.
 * @return array|null Request row or null.
 */
function tta_get_refund_request( $gateway_tx_id, $ticket_id, $attendee_id = 0 ) {
    $gateway_tx_id = sanitize_text_field( $gateway_tx_id );
    $ticket_id     = intval( $ticket_id );
    $attendee_id   = intval( $attendee_id );
    if ( '' === $gateway_tx_id ) {
        return null;
    }

    foreach ( tta_get_refund_requests() as $req ) {
        if ( $req['transaction_id'] !== $gateway_tx_id ) {
            continue;
        }
        if ( $ticket_id && intval( $req['ticket_id'] ) !== $ticket_id ) {
            continue;
        }
        if ( $attendee_id && intval( $req['attendee_id'] ?? 0 ) !== $attendee_id ) {
            continue;
        }
        return $req;
    }
    return null;
}

/**
 * Retrieve a transaction row by gateway transaction ID.
 *
 * @param string $gateway_tx_id Gateway ID.
 * @return array|null Transaction row.
 */
function tta_get_transaction_by_gateway_id( $gateway_tx_id ) {
    global $wpdb;
    $tx_table = $wpdb->prefix . 'tta_transactions';
    $gateway_tx_id = sanitize_text_field( $gateway_tx_id );
    if ( '' === $gateway_tx_id ) {
        return null;
    }

    $row = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM {$tx_table} WHERE transaction_id = %s", $gateway_tx_id ),
        ARRAY_A
    );
    return $row ?: null;
}

/**
 * Get the price paid for a ticket within a transaction.
 *
 * @param array $tx        Transaction row.
 * @param int   $ticket_id Ticket ID.
 * @return float Amount paid.
 */
function tta_get_ticket_price_from_transaction( array $tx, $ticket_id ) {
    $ticket_id = intval( $ticket_id );
    $details   = json_decode( $tx['details'] ?? '', true );
    if ( is_array( $details ) ) {
        foreach ( $details as $it ) {
            if ( intval( $it['ticket_id'] ?? 0 ) === $ticket_id ) {
                return floatval( $it['final_price'] ?? ( $it['price'] ?? 0 ) );
            }
        }
    }
    return 0.0;
}

/**
 * Get the start timestamp for an event ID.
 *
 * @param int $event_id Event ID.
 * @return int Timestamp or 0.
 */
function tta_get_event_start_timestamp( $event_id ) {
    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';
    $event_id = intval( $event_id );
    if ( ! $event_id ) {
        return 0;
    }

    $row = $wpdb->get_row(
        $wpdb->prepare( "SELECT date, time FROM {$events_table} WHERE id = %d", $event_id ),
        ARRAY_A
    );
    if ( ! $row ) {
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT date, time FROM {$archive_table} WHERE id = %d", $event_id ),
            ARRAY_A
        );
    }
    if ( ! $row ) {
        return 0;
    }

    $time = $row['time'] ? explode( '|', $row['time'] )[0] : '00:00';
    return strtotime( $row['date'] . ' ' . $time );
}

/**
 * Retrieve attendees with pending refund requests for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @param int $event_id  Event ID.
 * @return array[]
 */
function tta_get_ticket_pending_refund_attendees( $ticket_id, $event_id ) {
    $ticket_id = intval( $ticket_id );
    $event_id  = intval( $event_id );
    if ( ! $ticket_id || ! $event_id ) {
        return [];
    }

    $cache_key = 'pending_refund_attendees_' . $ticket_id . '_' . $event_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    $requests  = tta_get_refund_requests();
    $attendees = [];
    foreach ( $requests as $req ) {
        if ( intval( $req['ticket_id'] ) !== $ticket_id || intval( $req['event_id'] ) !== $event_id ) {
            continue;
        }
        $tx      = tta_get_transaction_by_gateway_id( $req['transaction_id'] );
        $p_email = '';
        if ( $tx && ! empty( $tx['wpuserid'] ) ) {
            $u       = get_userdata( intval( $tx['wpuserid'] ) );
            $p_email = $u ? strtolower( $u->user_email ) : '';
        }
        $attendees[] = [
            'first_name'  => sanitize_text_field( $req['first_name'] ),
            'last_name'   => sanitize_text_field( $req['last_name'] ),
            'email'       => sanitize_email( $req['email'] ),
            'phone'       => sanitize_text_field( $req['phone'] ),
            'reason'      => sanitize_text_field( $req['reason'] ),
            'mode'        => sanitize_text_field( $req['mode'] ?? '' ),
            'pending_reason' => sanitize_text_field( $req['pending_reason'] ?? '' ),
            'amount_paid' => floatval( $req['amount_paid'] ),
            'gateway_id'  => sanitize_text_field( $req['transaction_id'] ),
            'created_at'  => $tx['created_at'] ?? '',
            'is_purchaser'=> $p_email && strtolower( $req['email'] ) === $p_email,
        ];
    }

    $ttl = empty( $attendees ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $attendees, $ttl );

    return $attendees;
}

/**
 * Retrieve attendees who have been refunded for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @param int $event_id  Event ID.
 * @return array[]
 */
function tta_get_ticket_refunded_attendees( $ticket_id, $event_id ) {
    $ticket_id = intval( $ticket_id );
    $event_id  = intval( $event_id );
    if ( ! $ticket_id || ! $event_id ) {
        return [];
    }

    $cache_key = 'refunded_attendees_' . $ticket_id . '_' . $event_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $hist_table = $wpdb->prefix . 'tta_memberhistory';
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT action_data, action_date FROM {$hist_table} WHERE event_id = %d AND action_type = 'refund' ORDER BY action_date DESC",
            $event_id
        ),
        ARRAY_A
    );

    $attendees = [];
    foreach ( $rows as $r ) {
        $data = json_decode( $r['action_data'], true );
        if ( intval( $data['ticket_id'] ?? 0 ) !== $ticket_id ) {
            continue;
        }
        $amount = floatval( $data['amount'] ?? 0 );
        if ( $amount <= 0 ) {
            continue;
        }
        $att  = $data['attendee'] ?? [];
        $tx   = tta_get_transaction_by_gateway_id( $data['transaction_id'] ?? '' );
        $p_em = '';
        if ( $tx && ! empty( $tx['wpuserid'] ) ) {
            $u    = get_userdata( intval( $tx['wpuserid'] ) );
            $p_em = $u ? strtolower( $u->user_email ) : '';
        }
        $email = sanitize_email( $att['email'] ?? '' );
        $attendees[] = [
            'first_name'  => sanitize_text_field( $att['first_name'] ?? '' ),
            'last_name'   => sanitize_text_field( $att['last_name'] ?? '' ),
            'email'       => $email,
            'phone'       => sanitize_text_field( $att['phone'] ?? '' ),
            'reason'      => sanitize_text_field( $data['reason'] ?? '' ),
            'amount_paid' => $amount,
            'gateway_id'  => sanitize_text_field( $data['transaction_id'] ?? '' ),
            'created_at'  => $r['action_date'],
            'is_purchaser'=> $p_em && strtolower( $email ) === $p_em,
        ];
    }

    $ttl = empty( $attendees ) ? 60 : 300;
    TTA_Cache::set( $cache_key, $attendees, $ttl );

    return $attendees;
}

/**
 * Get the count of pending refund requests for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @param int $event_id  Event ID.
 * @return int Number of refund requests eligible to release. Requests with a
 *             pending reason of 'settlement' are excluded.
 */
function tta_get_ticket_refund_pool_count( $ticket_id, $event_id ) {
    $attendees = tta_get_ticket_pending_refund_attendees( $ticket_id, $event_id );
    $count     = 0;
    foreach ( $attendees as $att ) {
        if ( 'settlement' === ( $att['pending_reason'] ?? '' ) ) {
            continue;
        }
        $count++;
    }
    return $count;
}

/**
 * Lookup an attendee row by gateway transaction ID and ticket ID.
 *
 * @param string $gateway_tx_id Gateway transaction ID.
 * @param int    $ticket_id     Ticket ID.
 * @param int    $attendee_id   Optional attendee ID for transactions with multiple of the same ticket.
 * @return array|null Attendee row.
*/
function tta_get_attendee_by_tx_ticket( $gateway_tx_id, $ticket_id, $attendee_id = 0 ) {
    global $wpdb;
    $att_table = $wpdb->prefix . 'tta_attendees';
    $tx_table  = $wpdb->prefix . 'tta_transactions';
    $gateway_tx_id = sanitize_text_field( $gateway_tx_id );
    $ticket_id = intval( $ticket_id );
    if ( '' === $gateway_tx_id || ! $ticket_id ) {
        return null;
    }
    $tx_row = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$tx_table} WHERE transaction_id = %s", $gateway_tx_id ), ARRAY_A );
    if ( ! $tx_row ) {
        return null;
    }
    if ( $attendee_id ) {
        $att = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$att_table} WHERE id = %d AND transaction_id = %d AND ticket_id = %d LIMIT 1", $attendee_id, intval( $tx_row['id'] ), $ticket_id ), ARRAY_A );
    } else {
        $att = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$att_table} WHERE transaction_id = %d AND ticket_id = %d LIMIT 1", intval( $tx_row['id'] ), $ticket_id ), ARRAY_A );
    }
    return $att ?: null;
}

/**
 * Get how many events an attendee has checked into.
 *
 * @param string $email Attendee email address.
 * @return int Number of events attended.
 */
function tta_get_attended_event_count_by_email( $email ) {
    $email = strtolower( sanitize_email( $email ) );
    if ( '' === $email ) {
        return 0;
    }

    $cache_key = 'attended_count_' . md5( $email );
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return intval( $cached );
    }

    global $wpdb;
    $att_table   = $wpdb->prefix . 'tta_attendees';
    $archive     = $wpdb->prefix . 'tta_attendees_archive';

    $count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$att_table} WHERE LOWER(email) = %s AND status = 'checked_in'",
        $email
    ) );

    $count += (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$archive} WHERE LOWER(email) = %s AND status = 'checked_in'",
        $email
    ) );

    TTA_Cache::set( $cache_key, $count, 300 );
    return $count;
}

/**
 * Get how many events an attendee has been marked as a no-show for.
 *
 * @param string $email Attendee email address.
 * @return int Number of no-shows.
 */
function tta_get_no_show_event_count_by_email( $email ) {
    $email = strtolower( sanitize_email( $email ) );
    if ( '' === $email ) {
        return 0;
    }

    $cache_key = 'no_show_count_' . md5( $email );
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return intval( $cached );
    }

    global $wpdb;
    $att_table = $wpdb->prefix . 'tta_attendees';
    $archive   = $wpdb->prefix . 'tta_attendees_archive';

    $count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$att_table} WHERE LOWER(email) = %s AND status = 'no_show'",
        $email
    ) );

    $count += (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$archive} WHERE LOWER(email) = %s AND status = 'no_show'",
        $email
    ) );

    TTA_Cache::set( $cache_key, $count, 300 );
    return $count;
}

/**
 * Cancel an attendee without Ajax context.
 *
 * @param int  $attendee_id     Attendee ID.
 * @param bool $update_inventory Whether to increment ticket inventory.
 * @param bool $log_history      Whether to record a refund entry in member history.
 */
function tta_cancel_attendance_internal( $attendee_id, $update_inventory = true, $log_history = true ) {
    global $wpdb;
    $att_table   = $wpdb->prefix . 'tta_attendees';
    $ticket_table = $wpdb->prefix . 'tta_tickets';
    $tx_table     = $wpdb->prefix . 'tta_transactions';
    $hist_table   = $wpdb->prefix . 'tta_memberhistory';

    $att = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$att_table} WHERE id = %d", $attendee_id ), ARRAY_A );
    if ( ! $att ) {
        return;
    }

    $ticket = $wpdb->get_row( $wpdb->prepare( "SELECT event_ute_id FROM {$ticket_table} WHERE id = %d", intval( $att['ticket_id'] ) ), ARRAY_A );
    $tx     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tx_table} WHERE id = %d", intval( $att['transaction_id'] ) ), ARRAY_A );

    $event_id = 0;
    if ( $ticket && ! empty( $ticket['event_ute_id'] ) ) {
        $event_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1", $ticket['event_ute_id'], $ticket['event_ute_id'] ) );
    }

    if ( $tx && $log_history ) {
        $wpdb->insert(
            $hist_table,
            [
                'member_id'   => intval( $tx['member_id'] ),
                'wpuserid'    => intval( $tx['wpuserid'] ),
                'event_id'    => $event_id,
                'action_type' => 'refund',
                'action_data' => wp_json_encode([
                    'amount'         => 0,
                    'transaction_id' => $tx['transaction_id'],
                    'ticket_id'      => intval( $att['ticket_id'] ),
                    'attendee_id'    => $attendee_id,
                    'cancel'         => 1,
                    'attendee'       => [
                        'first_name' => $att['first_name'],
                        'last_name'  => $att['last_name'],
                        'email'      => $att['email'],
                    ],
                ]),
            ],
            [ '%d','%d','%d','%s','%s' ]
        );
    }

    $wpdb->delete( $att_table, [ 'id' => $attendee_id ], [ '%d' ] );
    $should_notify = false;
    if ( $ticket ) {
        $current = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ticketlimit FROM {$ticket_table} WHERE id = %d", intval( $att['ticket_id'] ) ) );
        $after   = $current + 1;
        $should_notify = ( $current <= 0 && $after > 0 );

        if ( $update_inventory ) {
            $wpdb->query( $wpdb->prepare( "UPDATE {$ticket_table} SET ticketlimit = ticketlimit + 1 WHERE id = %d", intval( $att['ticket_id'] ) ) );
        }
        tta_clear_ticket_cache( $ticket['event_ute_id'] ?? '', intval( $att['ticket_id'] ) );
    }

    TTA_Cache::flush();

    if ( $update_inventory && $should_notify ) {
        tta_notify_waitlist_ticket_available( intval( $att['ticket_id'] ) );
    }
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
            "SELECT id, name, date, time, address, page_id, type, venuename, venueurl, baseeventcost, discountedmembercost, premiummembercost, hosts, volunteers, host_notes, mainimageid FROM {$events_table} WHERE date >= %s ORDER BY date ASC, time ASC LIMIT 1",
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
        'mainimageid'        => intval( $row['mainimageid'] ),
        'timestamp'          => ( function() use ( $row ) {
            $tz  = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
            $time = explode( '|', $row['time'] )[0];
            $dt   = new DateTime( $row['date'] . ' ' . $time, $tz );
            return $dt->getTimestamp();
        } )(),
    ];

    $names = tta_get_event_host_volunteer_names( $event['id'] );
    $event['host_names']       = implode( ', ', $names['hosts'] );
    $event['volunteer_names']  = implode( ', ', $names['volunteers'] );
    $event['host_notes']       = tta_sanitize_textarea_field( $row['host_notes'] ?? '' );

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
            "SELECT id, name, date, time, address, page_id, type, venuename, venueurl, baseeventcost, discountedmembercost, premiummembercost, host_notes FROM {$events_table} WHERE ute_id = %s",
            $event_ute_id
        ),
        ARRAY_A
    );

    if ( ! $row ) {
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, name, date, time, address, page_id, type, venuename, venueurl, baseeventcost, discountedmembercost, premiummembercost, host_notes FROM {$archive_table} WHERE ute_id = %s",
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
        'host_notes'   => tta_sanitize_textarea_field( $row['host_notes'] ),
    ];

    TTA_Cache::set( $cache_key, $event, 300 );
    return $event;
}

/**
 * Retrieve an event ute_id from an event ID.
 *
 * @param int $event_id Event ID.
 * @return string Event ute_id or empty string.
 */
function tta_get_event_ute_id( $event_id ) {
    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';
    $event_id = intval( $event_id );
    if ( ! $event_id ) {
        return '';
    }
    $ute = $wpdb->get_var( $wpdb->prepare( "SELECT ute_id FROM {$events_table} WHERE id = %d UNION SELECT ute_id FROM {$archive_table} WHERE id = %d LIMIT 1", $event_id, $event_id ) );
    return $ute ? sanitize_text_field( $ute ) : '';
}

/**
 * Retrieve an event ID from a ute_id.
 *
 * @param string $event_ute_id Event ute_id.
 * @return int Event ID or 0.
 */
function tta_get_event_id_by_ute( $event_ute_id ) {
    global $wpdb;
    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';
    $event_ute_id  = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return 0;
    }
    $id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$events_table} WHERE ute_id = %s UNION SELECT id FROM {$archive_table} WHERE ute_id = %s LIMIT 1", $event_ute_id, $event_ute_id ) );
    return $id ? intval( $id ) : 0;
}

/**
 * Fetch ticket name and event ute_id.
 *
 * @param int $ticket_id Ticket ID.
 * @return array|null
 */
function tta_get_ticket_basic_info( $ticket_id ) {
    global $wpdb;
    $tickets_table   = $wpdb->prefix . 'tta_tickets';
    $tickets_archive = $wpdb->prefix . 'tta_tickets_archive';
    $ticket_id = intval( $ticket_id );
    if ( ! $ticket_id ) {
        return null;
    }
    $row = $wpdb->get_row( $wpdb->prepare( "SELECT event_ute_id, ticket_name FROM {$tickets_table} WHERE id = %d", $ticket_id ), ARRAY_A );
    if ( ! $row ) {
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT event_ute_id, ticket_name FROM {$tickets_archive} WHERE id = %d", $ticket_id ), ARRAY_A );
    }
    if ( ! $row ) {
        return null;
    }
    return [
        'event_ute_id' => sanitize_text_field( $row['event_ute_id'] ),
        'ticket_name'  => sanitize_text_field( $row['ticket_name'] ),
    ];
}

/**
 * Get attendees for a transaction and event.
 *
 * @param string $gateway_tx_id Gateway transaction ID.
 * @param int    $event_id      Event ID.
 * @return array[]
 */
function tta_get_transaction_event_attendees( $gateway_tx_id, $event_id ) {
    return tta_get_refund_request_attendees( $gateway_tx_id, $event_id );
}

/**
 * Build a user context array for a specific user ID.
 *
 * @param int $user_id WordPress user ID.
 * @return array
 */
function tta_get_user_context_by_id( $user_id ) {
    $context = [
        'wp_user_id'       => intval( $user_id ),
        'user_email'       => '',
        'first_name'       => '',
        'last_name'        => '',
        'member'           => null,
        'membership_level' => 'free',
    ];

    $user = get_user_by( 'ID', $user_id );
    if ( $user ) {
        $context['user_email'] = sanitize_email( $user->user_email );
        $context['first_name'] = sanitize_text_field( $user->first_name );
        $context['last_name']  = sanitize_text_field( $user->last_name );
    }

    $cache_key = 'member_row_' . $user_id;
    $member    = TTA_Cache::remember( $cache_key, function() use ( $user_id ) {
        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$members_table} WHERE wpuserid = %d", $user_id ), ARRAY_A );
    }, 300 );

    if ( is_array( $member ) ) {
        $context['member']           = $member;
        $context['membership_level'] = $member['membership_level'] ?? 'free';
    }

    return $context;
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
 * Get min and max ticket costs for an event.
 *
 * Returns an array with base, basic and premium cost ranges. Each key
 * has `_min` and `_max` variants. Results are cached for five minutes.
 *
 * @param string $event_ute_id Event ute_id.
 * @return array{
 *     base_min:float, base_max:float,
 *     basic_min:float, basic_max:float,
 *     premium_min:float, premium_max:float
 * }
 */
function tta_get_ticket_cost_range( $event_ute_id ) {
    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return [
            'base_min'    => 0.0,
            'base_max'    => 0.0,
            'basic_min'   => 0.0,
            'basic_max'   => 0.0,
            'premium_min' => 0.0,
            'premium_max' => 0.0,
        ];
    }

    $cache_key = 'ticket_cost_range_' . $event_ute_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    global $wpdb;
    $tickets_table   = $wpdb->prefix . 'tta_tickets';
    $tickets_archive = $wpdb->prefix . 'tta_tickets_archive';

    $sql = "(SELECT baseeventcost, discountedmembercost, premiummembercost FROM {$tickets_table} WHERE event_ute_id = %s) UNION ALL (SELECT baseeventcost, discountedmembercost, premiummembercost FROM {$tickets_archive} WHERE event_ute_id = %s)";
    $rows = $wpdb->get_results( $wpdb->prepare( $sql, $event_ute_id, $event_ute_id ), ARRAY_A );

    $base  = [];
    $basic = [];
    $prem  = [];
    foreach ( $rows as $r ) {
        $base[]  = floatval( $r['baseeventcost'] );
        $basic[] = floatval( $r['discountedmembercost'] );
        $prem[]  = floatval( $r['premiummembercost'] );
    }

    if ( empty( $base ) ) {
        $result = [
            'base_min'    => 0.0,
            'base_max'    => 0.0,
            'basic_min'   => 0.0,
            'basic_max'   => 0.0,
            'premium_min' => 0.0,
            'premium_max' => 0.0,
        ];
    } else {
        $result = [
            'base_min'    => min( $base ),
            'base_max'    => max( $base ),
            'basic_min'   => min( $basic ),
            'basic_max'   => max( $basic ),
            'premium_min' => min( $prem ),
            'premium_max' => max( $prem ),
        ];
    }

    TTA_Cache::set( $cache_key, $result, 300 );
    return $result;
}

/**
 * Get the remaining stock for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @return int Remaining count.
 */
function tta_get_ticket_stock( $ticket_id ) {
    $ticket_id = intval( $ticket_id );
    if ( ! $ticket_id ) {
        return 0;
    }

    $cache_key = 'ticket_stock_' . $ticket_id;
    $cached    = TTA_Cache::get( $cache_key );
    if ( false !== $cached ) {
        return intval( $cached );
    }

    global $wpdb;
    $tickets_table   = $wpdb->prefix . 'tta_tickets';
    $tickets_archive = $wpdb->prefix . 'tta_tickets_archive';
    $stock = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ticketlimit FROM {$tickets_table} WHERE id = %d", $ticket_id ) );
    if ( null === $stock ) {
        $stock = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ticketlimit FROM {$tickets_archive} WHERE id = %d", $ticket_id ) );
    }
    TTA_Cache::set( $cache_key, $stock, 60 );
    return $stock;
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
            'id'                 => intval( $row['id'] ),
            'ute_id'             => sanitize_text_field( $row['ute_id'] ),
            'name'               => sanitize_text_field( $row['name'] ),
            'date'               => $row['date'],
            'time'               => $row['time'],
            'all_day_event'      => ! empty( $row['all_day_event'] ),
            'venuename'          => sanitize_text_field( $row['venuename'] ),
            'address'            => sanitize_text_field( $row['address'] ),
            'waitlistavailable'  => ! empty( $row['waitlistavailable'] ),
            'page_id'            => intval( $row['page_id'] ),
            'mainimageid'        => intval( $row['mainimageid'] ),
            'baseeventcost'      => floatval( $row['baseeventcost'] ?? 0 ),
            'discountedmembercost' => floatval( $row['discountedmembercost'] ?? 0 ),
            'premiummembercost'  => floatval( $row['premiummembercost'] ?? 0 ),
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
    $has_membership   = in_array( $membership_level, [ 'basic', 'premium', 'reentry' ], true );
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
        <div class="tta-cart-table-wrapper">
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
                            <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'We reserve your ticket for 10 minutes so events don\'t oversell. After 10 minutes it becomes available to others.' ); ?>">
                                <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Ticket Reserved for…', 'tta' ); ?>
                    </th>
                    <?php endif; ?>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Maximum quantity each member can purchase for this ticket.' ); ?>">
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
                        <td><?php echo esc_html( tta_get_membership_label( $membership_level ) ); ?></td>
                        <?php if ( $has_tickets ) : ?><td></td><?php endif; ?>
                        <td>1</td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?><?php if ( 'reentry' !== $membership_level ) echo ' ' . esc_html__( 'Per Month', 'tta' ); ?></td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?><?php if ( 'reentry' !== $membership_level ) echo ' ' . esc_html__( 'Per Month', 'tta' ); ?></td>
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
                            if ( 'reentry' === $membership_level ) {
                                // no extra text
                            } elseif ( $has_tickets ) {
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
                        <span class="tta-discount-code-bold-title"><?php esc_html_e( 'Active Discount Codes:', 'tta' ); ?></span>
                        <?php foreach ( $discount_codes as $code ) : ?>
                            <?php $ev = $code_events[ $code ] ?? ''; ?>
                            <span class="tta-discount-tag"><?php echo esc_html( $code . ( $ev ? " ($ev)" : '' ) ); ?> <button type="button" class="tta-remove-discount tta-remove-item" data-code="<?php echo esc_attr( $code ); ?>" aria-label="Remove"></button></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        </div>
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
    $has_membership   = in_array( $membership_level, [ 'basic', 'premium', 'reentry' ], true );
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
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( "We reserve your ticket for 10 minutes so events don't oversell. After 10 minutes it becomes available to others." ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Ticket Reserved for…', 'tta' ); ?>
                    </th>
                    <?php endif; ?>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Maximum quantity each member can purchase for this ticket.' ); ?>">
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
                        <td><?php echo esc_html( tta_get_membership_label( $membership_level ) ); ?></td>
                        <?php if ( $has_tickets ) : ?><td></td><?php endif; ?>
                        <td>1</td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?><?php if ( 'reentry' !== $membership_level ) echo ' ' . esc_html__( 'Per Month', 'tta' ); ?></td>
                        <td>$<?php echo esc_html( number_format( $m_price, 2 ) ); ?><?php if ( 'reentry' !== $membership_level ) echo ' ' . esc_html__( 'Per Month', 'tta' ); ?></td>
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
                            if ( 'reentry' === $membership_level ) {
                                // no extra text
                            } elseif ( $has_tickets ) {
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
                        <span class="tta-discount-code-bold-title"><?php esc_html_e( 'Active Discount Codes:', 'tta' ); ?></span>
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
    $d_attr = $disabled ? ' disabled' : '';
    foreach ( $groups as $grp ) {
        $used_default = false;
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
                $locked = '';
                if ( ! $used_default && $context['member'] ) {
                    $fn_val  = esc_attr( $context['member']['first_name'] );
                    $ln_val  = esc_attr( $context['member']['last_name'] );
                    $em_val  = esc_attr( $context['member']['email'] );
                    $ph_val  = esc_attr( $context['member']['phone'] ?? '' );
                    $locked  = ' disabled';
                    $used_default = true;
                }
                $img = esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' );
                echo '<label><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'First name for event check-in.', 'tta' ) . '"><img src="' . $img . '" alt="?"></span>' . esc_html__( 'First Name', 'tta' ) . '<span class="tta-required">*</span><br />';
                if ( $locked ) {
                    echo '<input type="hidden" name="' . esc_attr( $base . '[first_name]' ) . '" value="' . $fn_val . '">';
                    echo '<input type="text" value="' . $fn_val . '" required disabled></label> ';
                } else {
                    echo '<input type="text" name="' . esc_attr( $base . '[first_name]' ) . '" value="' . $fn_val . '" required' . $d_attr . '></label> ';
                }
                echo '<label><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Last name for event check-in.', 'tta' ) . '"><img src="' . $img . '" alt="?"></span>' . esc_html__( 'Last Name', 'tta' ) . '<br />';
                if ( $locked ) {
                    echo '<input type="hidden" name="' . esc_attr( $base . '[last_name]' ) . '" value="' . $ln_val . '">';
                    echo '<input type="text" value="' . $ln_val . '" required disabled></label> ';
                } else {
                    echo '<input type="text" name="' . esc_attr( $base . '[last_name]' ) . '" value="' . $ln_val . '" required' . $d_attr . '></label> ';
                }
                echo '<label><span class="tta-tooltip-icon" data-tooltip="' . esc_attr__( 'Email used for ticket confirmation.', 'tta' ) . '"><img src="' . $img . '" alt="?"></span>' . esc_html__( 'Email', 'tta' ) . '<span class="tta-required">*</span><br />';
                if ( $locked ) {
                    echo '<input type="hidden" name="' . esc_attr( $base . '[email]' ) . '" value="' . $em_val . '">';
                    echo '<input type="email" value="' . $em_val . '" required disabled></label> ';
                } else {
                    echo '<input type="email" name="' . esc_attr( $base . '[email]' ) . '" value="' . $em_val . '" required' . $d_attr . '></label> ';
                }
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
 * Render the login/register accordion used on public pages when a member
 * must authenticate before continuing.
 *
 * @param string $redirect URL to redirect to after a successful login.
 * @return string         HTML markup for the login/register section.
 */
function tta_render_login_register_section( $redirect ) {
    $form_html = wp_login_form(
        [
            'echo'     => false,
            'redirect' => esc_url_raw( $redirect ),
        ]
    );

    $lost_pw_url = wp_lostpassword_url( $redirect );

    ob_start();
    ?>
    <section id="tta-login-message" class="tta-message-center tta-login-accordion">
      <h2><?php esc_html_e( 'Log in or Register Here', 'tta' ); ?></h2>
      <div class="tta-accordion">
        <p>
          <?php
            printf(
                /* translators: 1: action buttons */
                esc_html__( 'Ticket discounts may be available! Log in below to check. Don\'t have an account? Create one below or become a Member today!%1$s', 'tta' ),
                '<div><a href="#tta-login-message" class="tta-button tta-button-primary tta-show-register">' . esc_html__( 'Create Account', 'tta' ) . '</a><a href="' . esc_url( home_url( '/become-a-member' ) ) . '" class="tta-button tta-button-primary">' . esc_html__( 'Become a Member', 'tta' ) . '</a></div>'
            );
          ?>
        </p>
        <div class="tta-accordion-content expanded">
          <div id="tta-login-wrap">
            <?php echo $form_html; ?>
            <p class="login-lost-password"><a href="<?php echo esc_url( $lost_pw_url ); ?>"><?php esc_html_e( 'Forgot your password?', 'tta' ); ?></a></p>
          </div>
          <form id="tta-register-form" style="display:none;">
            <p>
              <label><?php esc_html_e( 'First Name', 'tta' ); ?><br />
                <input type="text" name="first_name" required />
              </label>
            </p>
            <p>
              <label><?php esc_html_e( 'Last Name', 'tta' ); ?><br />
                <input type="text" name="last_name" required />
              </label>
            </p>
            <p>
              <label><?php esc_html_e( 'Email', 'tta' ); ?><br />
                <input type="email" name="email" required />
              </label>
            </p>
            <p>
              <label><?php esc_html_e( 'Verify Email', 'tta' ); ?><br />
                <input type="email" name="email_verify" required />
              </label>
            </p>
            <p>
              <label><?php esc_html_e( 'Password', 'tta' ); ?><br />
                <input type="password" name="password" required />
              </label>
            </p>
            <p>
              <label><?php esc_html_e( 'Verify Password', 'tta' ); ?><br />
                <input type="password" name="password_verify" required />
              </label>
            </p>
            <p>
              <button type="submit" class="tta-button tta-button-primary"><?php esc_html_e( 'Create Account', 'tta' ); ?></button>
              <a href="#tta-login-message" class="tta-button-link tta-cancel-register"><?php esc_html_e( 'Cancel Account Creation', 'tta' ); ?></a>
              <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
            </p>
            <span id="tta-register-response" class="tta-admin-progress-response-p"></span>
          </form>
        </div>
      </div>
    </section>
    <?php
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
 * Track the number of refund pool tickets currently released for sale.
 *
 * Stored as an option mapping ticket ID to a count. Helper functions keep the
 * array sanitized.
 *
 * @return array
 */
function tta_get_released_refund_map() {
    $map = get_option( 'tta_refund_pool_released', [] );
    return is_array( $map ) ? array_map( 'intval', $map ) : [];
}

/**
 * Track which tickets have completely sold out at least once.
 *
 * Stored as an option mapping ticket ID to a boolean-like flag. This allows
 * refund requests submitted after the initial sell out to be released
 * immediately.
 *
 * @return array
 */
function tta_get_sold_out_map() {
    $map = get_option( 'tta_ticket_sold_out_once', [] );
    return is_array( $map ) ? array_map( 'intval', $map ) : [];
}

/**
 * Determine if a ticket has sold out at least once.
 *
 * @param int $ticket_id Ticket ID.
 * @return bool
 */
function tta_has_ticket_sold_out( $ticket_id ) {
    $ticket_id = intval( $ticket_id );
    $map       = tta_get_sold_out_map();
    return ! empty( $map[ $ticket_id ] );
}

/**
 * Mark a ticket as having sold out at least once.
 *
 * @param int $ticket_id Ticket ID.
 */
function tta_mark_ticket_sold_out( $ticket_id ) {
    $ticket_id = intval( $ticket_id );
    if ( $ticket_id <= 0 ) {
        return;
    }
    $map = tta_get_sold_out_map();
    if ( empty( $map[ $ticket_id ] ) ) {
        $map[ $ticket_id ] = 1;
        update_option( 'tta_ticket_sold_out_once', $map, false );
    }
}

/**
 * Get released count for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @return int
 */
function tta_get_released_refund_count( $ticket_id ) {
    $map = tta_get_released_refund_map();
    $ticket_id = intval( $ticket_id );
    return isset( $map[ $ticket_id ] ) ? intval( $map[ $ticket_id ] ) : 0;
}

/**
 * Persist released count for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @param int $count     Number of released tickets remaining.
 */
function tta_set_released_refund_count( $ticket_id, $count ) {
    $ticket_id = intval( $ticket_id );
    $count     = intval( $count );
    $map       = tta_get_released_refund_map();
    if ( $count <= 0 ) {
        unset( $map[ $ticket_id ] );
    } else {
        $map[ $ticket_id ] = $count;
    }
    update_option( 'tta_refund_pool_released', $map, false );
}

/**
 * Decrease released refund count for a ticket.
 *
 * @param int $ticket_id Ticket ID.
 * @param int $diff      Amount to subtract.
 */
function tta_decrement_released_refund_count( $ticket_id, $diff = 1 ) {
    $ticket_id = intval( $ticket_id );
    $diff      = intval( $diff );
    if ( $diff <= 0 ) {
        return;
    }
    $current = tta_get_released_refund_count( $ticket_id );
    $new     = max( 0, $current - $diff );
    tta_set_released_refund_count( $ticket_id, $new );
}

/**
 * Clear cached ticket and event availability data.
 *
 * @param string $event_ute_id Event ute_id.
 * @param int    $ticket_id    Optional ticket ID.
 */
function tta_clear_ticket_cache( $event_ute_id, $ticket_id = 0 ) {
    $event_ute_id = sanitize_text_field( $event_ute_id );
    $ticket_id    = intval( $ticket_id );

    if ( $event_ute_id ) {
        TTA_Cache::delete( 'tickets_' . $event_ute_id );
        TTA_Cache::delete( 'tickets_remaining_' . $event_ute_id );
    }

    if ( $ticket_id > 0 ) {
        TTA_Cache::delete( 'ticket_stock_' . $ticket_id );
    }

    // Bust higher level caches so events list counts refresh immediately.
    TTA_Cache::delete_group( 'upcoming_events_' );
    TTA_Cache::delete_group( 'event_days_' );
}

/**
 * Clear cached pending refund attendee data for a ticket/event combo.
 *
 * @param int $ticket_id Ticket ID.
 * @param int $event_id  Event ID.
 */
function tta_clear_pending_refund_cache( $ticket_id, $event_id ) {
    $ticket_id = intval( $ticket_id );
    $event_id  = intval( $event_id );
    if ( $ticket_id && $event_id ) {
        TTA_Cache::delete( 'pending_refund_attendees_' . $ticket_id . '_' . $event_id );
    }
}

/**
 * Determine if an event has any active cart reservations.
 *
 * @param string $event_ute_id Event ute_id.
 * @return bool True when unexpired cart items exist.
 */
function tta_event_has_active_cart_reservations( $event_ute_id ) {
    global $wpdb;
    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return false;
    }
    $cart_table    = $wpdb->prefix . 'tta_cart_items';
    $tickets_table = $wpdb->prefix . 'tta_tickets';

    $count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$cart_table} ci
             JOIN {$tickets_table} t ON ci.ticket_id = t.id
             WHERE t.event_ute_id = %s AND ci.expires_at > %s",
            $event_ute_id,
            current_time( 'mysql', true )
        )
    );

    return $count > 0;
}

/**
 * Release tickets from the refund pool when an event sells out.
 *
 * @param string $event_ute_id Event ute_id.
 */
function tta_release_refund_tickets( $event_ute_id ) {
    global $wpdb;
    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return;
    }
    if ( ! is_object( $wpdb ) || ! method_exists( $wpdb, 'get_results' ) ) {
        return;
    }
    $events_table  = $wpdb->prefix . 'tta_events';
    $tickets_table = $wpdb->prefix . 'tta_tickets';

    if ( tta_event_has_active_cart_reservations( $event_ute_id ) ) {
        return;
    }

    $event_id = tta_get_event_id_by_ute( $event_ute_id );
    if ( ! $event_id ) {
        return;
    }

    $tickets = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, ticketlimit FROM {$tickets_table} WHERE event_ute_id = %s",
            $event_ute_id
        ),
        ARRAY_A
    );

    foreach ( $tickets as $t ) {
        $tid   = intval( $t['id'] );
        $limit = intval( $t['ticketlimit'] );
        if ( $limit <= 0 ) {
            tta_mark_ticket_sold_out( $tid );
        }

        if ( ! tta_has_ticket_sold_out( $tid ) ) {
            continue; // do not release refund tickets until initial sellout.
        }

        $pool = tta_get_ticket_refund_pool_count( $tid, $event_id );
        if ( $pool <= $limit ) {
            continue;
        }
        $diff = $pool - $limit;
        $wpdb->query( $wpdb->prepare( "UPDATE {$tickets_table} SET ticketlimit = ticketlimit + %d WHERE id = %d", $diff, $tid ) );
        tta_clear_ticket_cache( $event_ute_id, $tid );
        tta_set_released_refund_count( $tid, $pool );
        tta_notify_waitlist_ticket_available( $tid );
    }
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
 * Remove purchased users from any related waitlists.
 *
 * @param array $items   Cart items with attendee info.
 * @param int   $user_id Purchaser WordPress user ID.
 */
function tta_remove_purchased_from_waitlists( array $items, $user_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'tta_waitlist';
    $buyer_email = '';
    if ( $user_id ) {
        $u = get_user_by( 'ID', $user_id );
        if ( $u ) {
            $buyer_email = sanitize_email( $u->user_email );
        }
    }
    foreach ( $items as $it ) {
        $tid = intval( $it['ticket_id'] );
        if ( $user_id ) {
            $wpdb->delete( $table, [ 'ticket_id' => $tid, 'wp_user_id' => $user_id ], [ '%d', '%d' ] );
        }
        if ( $buyer_email ) {
            $wpdb->delete( $table, [ 'ticket_id' => $tid, 'email' => $buyer_email ], [ '%d', '%s' ] );
        }
        foreach ( (array) ( $it['attendees'] ?? [] ) as $a ) {
            $email = sanitize_email( $a['email'] ?? '' );
            if ( $email ) {
                $wpdb->delete( $table, [ 'ticket_id' => $tid, 'email' => $email ], [ '%d', '%s' ] );
            }
        }
    }
    TTA_Cache::flush();
}

/**
 * Check if a ticket currently has any waitlist entries.
 *
 * @param int $ticket_id Ticket ID.
 * @return bool True when the waitlist table has rows for the ticket.
 */
function tta_ticket_has_waitlist_entries( $ticket_id ) {
    global $wpdb;
    $ticket_id     = intval( $ticket_id );
    if ( $ticket_id <= 0 ) {
        return false;
    }
    $waitlist_table = $wpdb->prefix . 'tta_waitlist';
    $count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$waitlist_table} WHERE ticket_id = %d",
            $ticket_id
        )
    );
    return $count > 0;
}

/**
 * Notify waitlist members when stock becomes available.
 *
 * @param int $ticket_id Ticket ID.
 */
function tta_notify_waitlist_ticket_available( $ticket_id ) {
    global $wpdb;
    $tickets_table = $wpdb->prefix . 'tta_tickets';
    $waitlist_table = $wpdb->prefix . 'tta_waitlist';
    $events_table = $wpdb->prefix . 'tta_events';

    $ticket = $wpdb->get_row(
        $wpdb->prepare("SELECT event_ute_id, ticket_name FROM {$tickets_table} WHERE id = %d", $ticket_id),
        ARRAY_A
    );
    if ( ! $ticket ) {
        return;
    }

    $event = $wpdb->get_row(
        $wpdb->prepare("SELECT name, waitlistavailable, page_id FROM {$events_table} WHERE ute_id = %s", $ticket['event_ute_id']),
        ARRAY_A
    );
    if ( empty( $event ) || empty( $event['waitlistavailable'] ) ) {
        return;
    }

    $entries = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$waitlist_table} WHERE ticket_id = %d ORDER BY added_at ASC", $ticket_id),
        ARRAY_A
    );
    if ( empty( $entries ) ) {
        return;
    }

    $grouped = [ 'premium' => [], 'basic' => [], 'free' => [] ];
    foreach ( $entries as $e ) {
        $level = tta_get_user_membership_level( intval( $e['wp_user_id'] ) );
        $e['membership_level'] = $level;
        $grouped[ $level ][] = $e;
    }

    $has_premium = ! empty( $grouped['premium'] );
    $has_basic   = ! empty( $grouped['basic'] );

    $offsets = [ 'premium' => 0, 'basic' => 600, 'free' => 900 ];
    if ( ! $has_premium ) {
        $offsets['basic'] = 0;
        $offsets['free']  = $has_basic ? 600 : 0;
    }

    foreach ( $grouped as $level => $rows ) {
        foreach ( $rows as $row ) {
            $delay = $offsets[ $level ];
            wp_schedule_single_event( time() + $delay, 'tta_send_waitlist_notification', [ $row, $event ] );
        }
    }
}

/**
 * Send a waitlist email notification.
 *
 * @param array $entry Waitlist row.
 * @param array $event Event row.
 */
function tta_send_waitlist_notification( $entry, $event ) {
    $templates = tta_get_comm_templates();
    if ( empty( $templates['waitlist_available'] ) ) {
        return;
    }
    $tpl = $templates['waitlist_available'];
    $tokens = [
        '{event_name}' => $event['name'] ?? '',
        '{event_link}' => get_permalink( intval( $event['page_id'] ) ),
        '{first_name}' => $entry['first_name'] ?? '',
    ];
    $sub_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
    $subject = strtr( $sub_raw, $tokens );
    $body_raw = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
    $body_txt = tta_convert_links( strtr( $body_raw, $tokens ) );
    $body    = nl2br( $body_txt );
    $to      = sanitize_email( $entry['email'] ?? '' );
    if ( $to ) {
        wp_mail( $to, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
    }
    TTA_SMS_Handler::get_instance()->send_waitlist_text( $entry, $event );
}
add_action( 'tta_send_waitlist_notification', 'tta_send_waitlist_notification', 10, 2 );

/**
 * Store an assistance note for the logged in member's attendee record.
 *
 * @param int    $wp_user_id    WordPress user ID.
 * @param string $event_ute_id  Event ute ID.
 * @param string $note          Message text.
 * @return bool True on success.
 */
function tta_save_assistance_note( $wp_user_id, $event_ute_id, $note ) {
    global $wpdb;
    $wp_user_id   = intval( $wp_user_id );
    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( ! $wp_user_id || '' === $event_ute_id ) {
        return false;
    }

    $user = get_userdata( $wp_user_id );
    if ( ! $user ) {
        return false;
    }

    $att_table     = $wpdb->prefix . 'tta_attendees';
    $tx_table      = $wpdb->prefix . 'tta_transactions';
    $tickets_table = $wpdb->prefix . 'tta_tickets';

    $ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT a.id FROM {$att_table} a JOIN {$tx_table} tx ON a.transaction_id = tx.id JOIN {$tickets_table} t ON a.ticket_id = t.id WHERE tx.wpuserid = %d AND t.event_ute_id = %s AND a.email = %s",
        $wp_user_id,
        $event_ute_id,
        $user->user_email
    ) );

    if ( empty( $ids ) ) {
        return false;
    }

    $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
    $sql = $wpdb->prepare(
        "UPDATE {$att_table} SET assistance_note = %s WHERE id IN ($placeholders)",
        tta_sanitize_textarea_field( $note ),
        ...$ids
    );
    $wpdb->query( $sql );
    TTA_Cache::flush();
    return true;
}

/**
 * Email event hosts and volunteers when a member asks for assistance.
 *
 * @param string $event_ute_id Event ute ID.
 * @param int    $wp_user_id   WordPress user ID submitting the note.
 * @param string $note         Message text.
 */
function tta_send_assistance_note_email( $event_ute_id, $wp_user_id, $note ) {
    $templates = tta_get_comm_templates();
    if ( empty( $templates['assistance_request'] ) ) {
        return;
    }
    $tpl   = $templates['assistance_request'];
    $event = tta_get_event_for_email( $event_ute_id );
    if ( empty( $event ) ) {
        return;
    }

    $context = tta_get_user_context_by_id( $wp_user_id );
    $tokens = [
        '{event_name}'           => $event['name'] ?? '',
        '{event_address}'        => $event['address'] ?? '',
        '{event_address_link}'   => isset( $event['address'] ) && $event['address'] !== ''
            ? esc_url( 'https://maps.google.com/?q=' . rawurlencode( $event['address'] ) )
            : '',
        '{event_link}'           => $event['page_url'] ?? '',
        '{dashboard_profile_url}'  => home_url( '/member-dashboard/?tab=profile' ),
        '{dashboard_upcoming_url}' => home_url( '/member-dashboard/?tab=upcoming' ),
        '{dashboard_past_url}'       => home_url( '/member-dashboard/?tab=past' ),
        '{dashboard_billing_url}'    => home_url( '/member-dashboard/?tab=billing' ),
        '{dashboard_waitlist_url}'   => home_url( '/member-dashboard/?tab=waitlist' ),
        '{event_date}'           => isset( $event['date'] ) ? tta_format_event_date( $event['date'] ) : '',
        '{event_time}'           => isset( $event['time'] ) ? tta_format_event_time( $event['time'] ) : '',
        '{event_type}'           => $event['type'] ?? '',
        '{venue_name}'           => $event['venue_name'] ?? '',
        '{venue_url}'            => $event['venue_url'] ?? '',
        '{base_cost}'            => isset( $event['base_cost'] ) ? number_format( (float) $event['base_cost'], 2 ) : '',
        '{member_cost}'          => isset( $event['member_cost'] ) ? number_format( (float) $event['member_cost'], 2 ) : '',
        '{premium_cost}'         => isset( $event['premium_cost'] ) ? number_format( (float) $event['premium_cost'], 2 ) : '',
        '{first_name}'            => $context['first_name'] ?? '',
        '{last_name}'             => $context['last_name'] ?? '',
        '{email}'                 => $context['user_email'] ?? '',
        '{phone}'                 => $context['member']['phone'] ?? '',
        '{membership_level}'      => $context['membership_level'] ?? '',
        '{member_type}'           => $context['member']['member_type'] ?? '',
        '{assistance_note}'       => $note !== '' ? sanitize_textarea_field( $note ) : 'N/A',
        '{assistance_message}'    => $note !== '' ? sanitize_textarea_field( $note ) : 'N/A',
        '{assistance_first_name}' => ! empty( $context['first_name'] ) ? $context['first_name'] : 'N/A',
        '{assistance_last_name}'  => ! empty( $context['last_name'] ) ? $context['last_name'] : 'N/A',
        '{assistance_email}'      => ! empty( $context['user_email'] ) ? $context['user_email'] : 'N/A',
        '{assistance_phone}'      => ! empty( $context['member']['phone'] ) ? $context['member']['phone'] : 'N/A',
    ];

    $names = tta_get_event_host_volunteer_names( $event['id'] );
    $tokens['{event_host}']       = $names['hosts'] ? implode( ', ', $names['hosts'] ) : 'TBD';
    $tokens['{event_hosts}']      = $tokens['{event_host}'];
    $tokens['{event_volunteer}']  = $names['volunteers'] ? implode( ', ', $names['volunteers'] ) : 'TBD';
    $tokens['{event_volunteers}'] = $tokens['{event_volunteer}'];

    $subject_raw = tta_expand_anchor_tokens( $tpl['email_subject'], $tokens );
    $subject     = tta_strip_bold( strtr( $subject_raw, $tokens ) );
    $body_raw    = tta_expand_anchor_tokens( $tpl['email_body'], $tokens );
    $body_txt    = tta_convert_bold( tta_convert_links( strtr( $body_raw, $tokens ) ) );
    $body        = nl2br( $body_txt );

    $emails = tta_get_event_host_volunteer_emails( $event['id'] );
    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
    foreach ( $emails as $to ) {
        wp_mail( $to, $subject, $body, $headers );
    }
}

/**
 * Retrieve all saved ads.
 *
 * @return array[] List of ads with image_id, url, business_name, business_phone, and business_address.
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
 * @return array|null Ad array with image_id, url, business_name, business_phone, and business_address or null if none exist.
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
 * Retrieve all non-event discount codes.
 *
 * @return array[]
 */
function tta_get_global_discount_codes() {
    $cache_key = 'tta_global_discounts';
    $codes = TTA_Cache::get( $cache_key );
    if ( false !== $codes ) {
        return $codes;
    }
    $codes = get_option( 'tta_global_discount_codes', [] );
    if ( ! is_array( $codes ) ) {
        $codes = [];
    }
    TTA_Cache::set( $cache_key, $codes, 300 );
    return $codes;
}

/**
 * Save global discount codes and clear the cache.
 *
 * @param array[] $codes
 */
function tta_save_global_discount_codes( $codes ) {
    if ( ! is_array( $codes ) ) {
        $codes = [];
    }
    update_option( 'tta_global_discount_codes', array_values( $codes ), false );
    TTA_Cache::delete( 'tta_global_discounts' );
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

/**
 * Redirect non-admin users away from the dashboard.
 */
function tta_block_dashboard_access() {
    if ( is_admin() && ! wp_doing_ajax() && ! current_user_can( 'manage_options' ) ) {
        $referer = wp_get_referer();
        wp_safe_redirect( $referer ? $referer : home_url( '/' ) );
        exit;
    }
}
add_action( 'admin_init', 'tta_block_dashboard_access' );

/**
 * Keep non-admins on the same page after logging in.
 *
 * @param string       $redirect_to           URL to redirect to.
 * @param string       $requested_redirect_to Requested redirect.
 * @param WP_User|WP_Error $user             User object or error.
 * @return string
 */
function tta_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( $user instanceof WP_User && ! user_can( $user, 'manage_options' ) ) {
        $referer = wp_get_referer();
        return $referer ? $referer : home_url( '/' );
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'tta_login_redirect', 10, 3 );

/**
 * Get aggregated metrics for an event.
 *
 * @param string $event_ute_id Event ute_id.
 * @return array{
 *     expected_attendees:int,
 *     checked_in:int,
 *     no_show:int,
 *     refund_requests:int,
 *     refunded_amount:float,
 *     revenue:float,
 *     waitlist_count:int,
 *     sold_out:bool
 * }
 */
function tta_get_event_metrics( $event_ute_id ) {
    global $wpdb;

    $event_ute_id = sanitize_text_field( $event_ute_id );
    if ( '' === $event_ute_id ) {
        return [
            'expected_attendees' => 0,
            'checked_in'        => 0,
            'no_show'           => 0,
            'refund_requests'   => 0,
            'refunded_amount'   => 0,
            'revenue'           => 0,
            'waitlist_count'    => 0,
            'sold_out'          => false,
        ];
    }

    $attendees = tta_get_event_attendees_with_status( $event_ute_id );
    $metrics = [
        'expected_attendees' => count( $attendees ),
        'checked_in'        => 0,
        'no_show'           => 0,
        'refund_requests'   => 0,
        'refunded_amount'   => 0,
        'revenue'           => 0,
        'waitlist_count'    => 0,
        'sold_out'          => false,
    ];
    foreach ( $attendees as $a ) {
        if ( 'checked_in' === $a['status'] ) {
            $metrics['checked_in']++;
        } elseif ( 'no_show' === $a['status'] ) {
            $metrics['no_show']++;
        }
    }

    $event_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}tta_events WHERE ute_id = %s UNION SELECT id FROM {$wpdb->prefix}tta_events_archive WHERE ute_id = %s LIMIT 1",
            $event_ute_id,
            $event_ute_id
        )
    );

    if ( $event_id ) {
        $ticket_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}tta_tickets WHERE event_ute_id = %s UNION ALL SELECT id FROM {$wpdb->prefix}tta_tickets_archive WHERE event_ute_id = %s",
                $event_ute_id,
                $event_ute_id
            ),
            ARRAY_A
        );
        foreach ( $ticket_rows as $tr ) {
            $tid = intval( $tr['id'] );
            $metrics['refund_requests'] += count( tta_get_ticket_pending_refund_attendees( $tid, $event_id ) );
            foreach ( tta_get_ticket_refunded_attendees( $tid, $event_id ) as $ref ) {
                $metrics['refunded_amount'] += floatval( $ref['amount_paid'] );
            }
        }
    }

    // Revenue from transactions
    $like = '%' . $wpdb->esc_like( $event_ute_id ) . '%';
    $tx_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT details FROM {$wpdb->prefix}tta_transactions WHERE details LIKE %s",
            $like
        ),
        ARRAY_A
    );
    foreach ( $tx_rows as $tx ) {
        $items = json_decode( $tx['details'], true );
        if ( ! is_array( $items ) ) {
            continue;
        }
        foreach ( $items as $it ) {
            if ( ( $it['event_ute_id'] ?? '' ) === $event_ute_id ) {
                $qty   = intval( $it['quantity'] ?? 1 );
                $price = floatval( $it['final_price'] ?? ( $it['price'] ?? 0 ) );
                $metrics['revenue'] += $price * $qty;
            }
        }
    }

    $metrics['waitlist_count'] = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}tta_waitlist WHERE event_ute_id = %s",
            $event_ute_id
        )
    );

    $open_tickets = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}tta_tickets WHERE event_ute_id = %s AND ticketlimit > 0",
            $event_ute_id
        )
    );
    $open_tickets += (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}tta_tickets_archive WHERE event_ute_id = %s AND ticketlimit > 0",
            $event_ute_id
        )
    );
    $metrics['sold_out'] = ( $open_tickets === 0 );
    return $metrics;
}

/**
 * Export event metrics to an Excel spreadsheet.
 *
 * @param string $start_date Optional start date Y-m-d.
 * @param string $end_date   Optional end date Y-m-d.
 */
function tta_export_event_metrics_report( $start_date = '', $end_date = '' ) {
    global $wpdb;

    if ( ! class_exists( '\\PhpOffice\\PhpSpreadsheet\\Spreadsheet' ) ) {
        if ( function_exists( 'is_admin' ) && is_admin() ) {
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'PhpSpreadsheet library missing. Run composer install inside the tta-management-plugin directory.', 'tta' ) . '</p></div>';
            } );
        }
        return;
    }

    $events_table  = $wpdb->prefix . 'tta_events';
    $archive_table = $wpdb->prefix . 'tta_events_archive';
    $where  = '1=1';
    $params = [];
    if ( $start_date ) {
        $where  .= ' AND date >= %s';
        $params[] = $start_date;
    }
    if ( $end_date ) {
        $where  .= ' AND date <= %s';
        $params[] = $end_date;
    }

    $sql = "SELECT * FROM {$events_table} WHERE {$where} UNION ALL SELECT * FROM {$archive_table} WHERE {$where} ORDER BY date DESC";
    $events = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $params, $params ) ), ARRAY_A );
    if ( empty( $events ) ) {
        wp_die( esc_html__( 'No events found for that range.', 'tta' ) );
    }

    $remove_cols = [ 'id', 'page_id', 'ticket_id', 'refundsavailable', 'created_at', 'updated_at', 'ute_id', 'otherimageids', 'waitlist_id' ];
    $bool_cols   = [ 'waitlistavailable', 'all_day_event', 'virtual_event' ];

    $header_labels = [
        'id'                    => 'ID',
        'name'                  => 'Event Name',
        'time'                  => 'Time',
        'date'                  => 'Date',
        'baseeventcost'         => 'Base Event Cost',
        'discountedmembercost'  => 'Discounted Member Cost',
        'premiummembercost'     => 'Premium Member Cost',
        'address'               => 'Address',
        'type'                  => 'Event Type',
        'venuename'             => 'Venue Name',
        'venueurl'              => 'Venue URL',
        'url2'                  => 'URL 2',
        'url3'                  => 'URL 3',
        'url4'                  => 'URL 4',
        'mainimageid'           => 'Featured Image',
        'waitlistavailable'     => 'Waitlist Available',
        'discountcode'          => 'Discount',
        'all_day_event'         => 'All Day Event',
        'virtual_event'         => 'Virtual Event',
        'hosts'                 => 'Hosts',
        'volunteers'            => 'Volunteers',
        'host_notes'            => 'Host Notes',
    ];

    $metric_headers = [
        'expected_attendees'   => 'Tickets Sold',
        'sold_out'             => 'Sold Out',
        'checked_in'           => 'Checked In',
        'no_show'              => 'No Shows',
        'refund_requests'      => 'Refund Requests',
        'refunded_amount'      => 'Refunded Amount',
        'revenue'              => 'Total Revenue',
        'revenue_minus_refunds'=> 'Revenue Minus Refunds',
    ];

    // Prepare header list after removing unwanted columns.
    $first_event = $events[0];
    foreach ( $remove_cols as $rc ) {
        unset( $first_event[ $rc ] );
    }
    $headers = array_keys( $first_event );
    // Move "time" directly before "date" if both exist.
    $time_index = array_search( 'time', $headers, true );
    if ( false !== $time_index ) {
        unset( $headers[ $time_index ] );
    }
    $date_index = array_search( 'date', $headers, true );
    if ( false !== $date_index ) {
        array_splice( $headers, $date_index, 0, 'time' );
    } elseif ( false !== $time_index ) {
        // If date is missing but time exists, append time.
        $headers[] = 'time';
    }
    $headers = array_values( $headers );
    $display_headers = [];
    foreach ( $headers as $h ) {
        $display_headers[] = $header_labels[ $h ] ?? $h;
    }
    foreach ( $metric_headers as $h => $label ) {
        $display_headers[] = $label;
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray( $display_headers, null, 'A1' );
    foreach ( range( 1, count( $display_headers ) ) as $col ) {
        $sheet->getColumnDimensionByColumn( $col )->setAutoSize( true );
    }

    $row = 2;
    foreach ( $events as $ev ) {
        $metrics = tta_get_event_metrics( $ev['ute_id'] );
        foreach ( $remove_cols as $c ) {
            unset( $ev[ $c ] );
        }

        foreach ( $bool_cols as $c ) {
            if ( isset( $ev[ $c ] ) ) {
                $ev[ $c ] = $ev[ $c ] ? 'Yes' : 'No';
            }
        }

        $ev['address']              = tta_format_address( $ev['address'] );
        $ev['date']                 = tta_format_event_date( $ev['date'] );
        $ev['time']                 = tta_format_event_time( $ev['time'] );
        $ev['type']                 = ucfirst( strtolower( $ev['type'] ) );
        if ( ! empty( $ev['mainimageid'] ) ) {
            $url = wp_get_attachment_url( intval( $ev['mainimageid'] ) );
            $ev['mainimageid'] = $url ? $url : '';
        } else {
            $ev['mainimageid'] = '';
        }
        $ev['baseeventcost']        = '$' . number_format( floatval( $ev['baseeventcost'] ), 2 );
        $ev['discountedmembercost'] = '$' . number_format( floatval( $ev['discountedmembercost'] ), 2 );
        $ev['premiummembercost']    = '$' . number_format( floatval( $ev['premiummembercost'] ), 2 );
        $ev['discountcode']         = tta_format_discount_display( $ev['discountcode'] );

        $metrics['revenue_minus_refunds'] = max( 0, $metrics['revenue'] - $metrics['refunded_amount'] );
        $metrics['refunded_amount']       = '$' . number_format( $metrics['refunded_amount'], 2 );
        $metrics['revenue']               = '$' . number_format( $metrics['revenue'], 2 );
        $metrics['revenue_minus_refunds'] = '$' . number_format( $metrics['revenue_minus_refunds'], 2 );
        $metrics['sold_out']              = $metrics['sold_out'] ? 'Yes' : 'No';

        $ordered_metrics = [];
        foreach ( array_keys( $metric_headers ) as $mk ) {
            $ordered_metrics[] = $metrics[ $mk ] ?? '';
        }

        $row_values = [];
        foreach ( $headers as $col ) {
            $row_values[] = $ev[ $col ] ?? '';
        }

        $sheet->fromArray( array_merge( $row_values, $ordered_metrics ), null, 'A' . $row );
        $row++;
    }

    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
    $tmp_file = wp_tempnam();
    $writer->save( $tmp_file );

    nocache_headers();
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
    header( 'Content-Disposition: attachment; filename="event-report.xlsx"' );
    readfile( $tmp_file );
    unlink( $tmp_file );
    exit;
}

/**
 * Handle admin-post request for exporting event metrics.
 */
function tta_handle_event_metrics_export() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Permission denied.', 'tta' ) );
    }

    check_admin_referer( 'tta_export_events_nonce' );

    $start = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
    $end   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';

    tta_export_event_metrics_report( $start, $end );
}
add_action( 'admin_post_tta_export_event_metrics', 'tta_handle_event_metrics_export' );

/**
 * Export member metrics to an Excel spreadsheet.
 *
 * @param string $start_date Optional join start date Y-m-d.
 * @param string $end_date   Optional join end date Y-m-d.
 */
function tta_export_member_metrics_report( $start_date = '', $end_date = '' ) {
    global $wpdb;

    if ( ! class_exists( '\\PhpOffice\\PhpSpreadsheet\\Spreadsheet' ) ) {
        if ( function_exists( 'is_admin' ) && is_admin() ) {
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'PhpSpreadsheet library missing. Run composer install inside the tta-management-plugin directory.', 'tta' ) . '</p></div>';
            } );
        }
        return;
    }

    $table  = $wpdb->prefix . 'tta_members';
    $where  = '1=1';
    $params = [];
    if ( $start_date ) {
        $where  .= ' AND joined_at >= %s';
        $params[] = $start_date;
    }
    if ( $end_date ) {
        $where  .= ' AND joined_at <= %s';
        $params[] = $end_date;
    }

    $sql     = $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY joined_at DESC", $params );
    $members = $wpdb->get_results( $sql, ARRAY_A );
    if ( empty( $members ) ) {
        wp_die( esc_html__( 'No members found for that range.', 'tta' ) );
    }

    $remove_cols = [ 'id', 'password', 'profileimgid', 'notes', 'biography', 'subscription_id', 'facebook', 'linkedin', 'instagram', 'twitter', 'interests' ];
    $bool_cols   = [ 'opt_in_marketing_email', 'opt_in_marketing_sms', 'opt_in_event_email', 'opt_in_event_sms', 'hide_event_attendance' ];

    $header_labels = [
        'first_name'            => 'First Name',
        'last_name'             => 'Last Name',
        'email'                 => 'Email',
        'member_type'           => 'Member Type',
        'membership_level'      => 'Membership Level',
        'joined_at'             => 'Joined',
        'membership_length'     => 'Membership Length (days)',
        'address'               => 'Address',
        'phone'                 => 'Phone',
        'dob'                   => 'Date of Birth',
        'subscription_status'   => 'Subscription Status',
        'opt_in_marketing_email'=> 'Marketing Emails',
        'opt_in_marketing_sms'  => 'Marketing SMS',
        'opt_in_event_email'    => 'Event Emails',
        'opt_in_event_sms'      => 'Event SMS',
        'hide_event_attendance' => 'Hide Attendance',
    ];

    $metric_headers = [
        'events'       => 'Events Purchased',
        'attended'     => 'Events Attended',
        'no_show'      => 'No Shows',
        'refunds'      => 'Refund Requests',
        'cancellations'=> 'Cancellation Requests',
        'total_spent'  => 'Total Spent',
    ];

    $first = $members[0];
    foreach ( $remove_cols as $rc ) {
        unset( $first[ $rc ] );
    }
    $headers = array_keys( $first );
    $joined_index = array_search( 'joined_at', $headers, true );
    if ( false !== $joined_index ) {
        array_splice( $headers, $joined_index + 1, 0, 'membership_length' );
    } else {
        $headers[] = 'membership_length';
    }

    $display_headers = [];
    foreach ( $headers as $h ) {
        $display_headers[] = $header_labels[ $h ] ?? $h;
    }
    foreach ( $metric_headers as $h => $label ) {
        $display_headers[] = $label;
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray( $display_headers, null, 'A1' );
    foreach ( range( 1, count( $display_headers ) ) as $col ) {
        $sheet->getColumnDimensionByColumn( $col )->setAutoSize( true );
    }

    $row = 2;
    foreach ( $members as $m ) {
        $summary = tta_get_member_history_summary( $m['id'] );

        foreach ( $remove_cols as $c ) {
            unset( $m[ $c ] );
        }

        foreach ( $bool_cols as $c ) {
            if ( isset( $m[ $c ] ) ) {
                $m[ $c ] = $m[ $c ] ? 'Yes' : 'No';
            }
        }

        $m['address']   = tta_format_address( $m['address'] );
        $join_ts = strtotime( $m['joined_at'] );
        $m['membership_length'] = floor( ( time() - $join_ts ) / DAY_IN_SECONDS );
        $m['joined_at'] = date_i18n( 'n-j-Y', $join_ts );
        $m['dob']       = $m['dob'] ? date_i18n( 'n-j-Y', strtotime( $m['dob'] ) ) : '';
        $summary['total_spent'] = '$' . number_format( $summary['total_spent'], 2 );

        $ordered_metrics = [];
        foreach ( array_keys( $metric_headers ) as $mk ) {
            $ordered_metrics[] = $summary[ $mk ] ?? '';
        }

        $row_values = [];
        foreach ( $headers as $col ) {
            $row_values[] = $m[ $col ] ?? '';
        }

        $sheet->fromArray( array_merge( $row_values, $ordered_metrics ), null, 'A' . $row );
        $row++;
    }

    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
    $tmp_file = wp_tempnam();
    $writer->save( $tmp_file );

    nocache_headers();
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
    header( 'Content-Disposition: attachment; filename="member-report.xlsx"' );
    readfile( $tmp_file );
    unlink( $tmp_file );
    exit;
}

/**
 * Handle admin-post request for exporting member metrics.
 */
function tta_handle_member_metrics_export() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Permission denied.', 'tta' ) );
    }

    check_admin_referer( 'tta_export_members_nonce' );

    $start = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
    $end   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';

    tta_export_member_metrics_report( $start, $end );
}
add_action( 'admin_post_tta_export_member_metrics', 'tta_handle_member_metrics_export' );
