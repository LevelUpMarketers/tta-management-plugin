<?php
/**
 * Template Name: Event Page
 *
 * @package TTA
 */

// Initialize cart early so sessions start before output
$cart = new TTA_Cart();
$cart_items = $cart->get_items();

// ───────────────
// 1) Load custom header (without the page-header block)
// ───────────────
$custom_header = plugin_dir_path( __FILE__ ) . 'header-event.php';
if ( file_exists( $custom_header ) ) {
    include $custom_header;
} else {
    get_header();
}

global $wpdb, $post;

// ───────────────
// 2) Fetch the main event record
// ───────────────
$page_id      = $post->ID;
$events_table = $wpdb->prefix . 'tta_events';
$archive_table = $wpdb->prefix . 'tta_events_archive';
$is_archived  = false;
$event        = TTA_Cache::remember( 'event_' . $page_id, function() use ( $wpdb, $events_table, $page_id ) {
    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$events_table} WHERE page_id = %d",
            $page_id
        ),
        ARRAY_A
    );
}, 600 );

if ( ! $event ) {
    $event = TTA_Cache::remember( 'arch_event_' . $page_id, function() use ( $wpdb, $archive_table, $page_id ) {
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$archive_table} WHERE page_id = %d",
                $page_id
            ),
            ARRAY_A
        );
    }, 600 );
    if ( $event ) {
        $is_archived = true;
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Event not found.', 'tta' ) . '</h1>'
           . '<p>' . esc_html__( 'Sorry, this event does not exist.', 'tta' ) . '</p></div>';
        get_footer();
        exit;
    }
}

$has_waitlist = ( '1' === (string) ( $event['waitlistavailable'] ?? '0' ) );

tta_release_refund_tickets( $event['ute_id'] );

// ───────────────
// 3) Fetch this event’s ticket types
// ───────────────
$tickets        = [];
$ticket_count   = 0;
$cart_quantities = [];
$tickets_table  = $wpdb->prefix . ( $is_archived ? 'tta_tickets_archive' : 'tta_tickets' );
$tickets        = TTA_Cache::remember( 'tickets_' . $event['ute_id'], function() use ( $wpdb, $tickets_table, $event ) {
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, ticket_name, ticketlimit, memberlimit, baseeventcost, discountedmembercost, premiummembercost
             FROM {$tickets_table}
             WHERE event_ute_id = %s
             ORDER BY id ASC",
            $event['ute_id']
        ),
        ARRAY_A
    );
}, 600 );
$ticket_count = count( $tickets );

$first_sold_out_ticket = null;
foreach ( $tickets as $t ) {
    if ( intval( $t['ticketlimit'] ) < 1 ) {
        $first_sold_out_ticket = $t;
        break;
    }
}

// Build a map of quantities for this event from the cart
foreach ( $cart_items as $it ) {
    if ( isset( $it['event_ute_id'] ) && $it['event_ute_id'] === $event['ute_id'] ) {
        $cart_quantities[ intval( $it['ticket_id'] ) ] = intval( $it['quantity'] );
    }
}

// Build a map of quantities this user already purchased for each ticket
$purchased_counts = [];
if ( is_user_logged_in() ) {
    $uid = get_current_user_id();
    foreach ( $tickets as $t ) {
        $purchased_counts[ intval( $t['id'] ) ] = tta_get_purchased_ticket_count_for_ticket( $uid, intval( $t['id'] ) );
    }
}

// ───────────────
// 4) Fetch “related” upcoming events
// ───────────────
$related = TTA_Cache::remember( 'related_' . $page_id, function() use ( $wpdb, $events_table, $page_id ) {
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$events_table}
             WHERE page_id != %d
               AND date >= CURDATE()
             ORDER BY date ASC
             LIMIT 4",
            $page_id
        ),
        ARRAY_A
    );
}, 600 );

// ───────────────
// 5) Determine logged-in user context
// ───────────────
$context          = tta_get_current_user_context();
$is_logged_in     = $context['is_logged_in'];
$current_user_id  = $context['wp_user_id'];
$member_row       = $context['member'] ?? [];
$membership_level = $context['membership_level'];
$member_history   = [];

// Load waitlist assets with event/user context
wp_enqueue_script(
    'tta-waitlist-js',
    TTA_PLUGIN_URL . 'assets/js/frontend/waitlist.js',
    [ 'jquery' ],
    TTA_PLUGIN_VERSION,
    true
);
wp_localize_script(
    'tta-waitlist-js',
    'tta_waitlist',
    [
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'tta_frontend_nonce' ),
        'eventUte'  => $event['ute_id'],
        'eventName' => $event['name'],
        'firstName' => $member_row['first_name'] ?? '',
        'lastName'  => $member_row['last_name'] ?? '',
        'email'     => $member_row['email'] ?? '',
        'phone'     => $member_row['phone'] ?? '',
    ]
);

// Map event type to display label and required level
$type_labels = [
    'free'       => __( 'Open Event', 'tta' ),
    'paid'       => __( 'Basic Membership Required', 'tta' ),
    'memberonly' => __( 'Premium Membership Required', 'tta' ),
];
$event_type_label = $type_labels[ $event['type'] ] ?? '';
$event_required   = 'free';
if ( 'paid' === $event['type'] ) {
    $event_required = 'basic';
} elseif ( 'memberonly' === $event['type'] ) {
    $event_required = 'premium';
}

// Build the tickets section message
$tickets_message = '<span class="tta-event-label"><strong>' . esc_html( $event_type_label ) . '</strong></span>';
$qualifies       = (
    'free' === $event_required ||
    ( 'basic' === $event_required && in_array( $membership_level, [ 'basic', 'premium' ], true ) ) ||
    ( 'premium' === $event_required && 'premium' === $membership_level )
);
$tooltip_message = '';
$disable_controls = false;
$waitlist_disabled = ! $is_logged_in;
$waitlist_tooltip  = __( 'You must be logged in to join the waitlist.', 'tta' );
$waitlist_ticket_ids = [];
$show_upgrade_btn  = false;
$upgrade_label     = '';

if ( ! $is_logged_in ) {
    if ( 'free' === $event_required ) {
        $tickets_message .= ' - <a href="#tta-login-message" class="tta-scroll-login">' . esc_html__( 'Log in here', 'tta' ) . '</a> ' . esc_html__( 'for the best experience.', 'tta' );
        $tickets_message .= ' ' . sprintf(
            /* translators: 1: opening link, 2: closing link */
            esc_html__( 'Don\'t have an account? %1$sCreate one here%2$s!', 'tta' ),
            '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
            '</a>'
        );
    } else {
        $tickets_message .= ' - <a href="#tta-login-message" class="tta-scroll-login">' . esc_html__( 'Log in here', 'tta' ) . '</a> ' . esc_html__( 'to purchase tickets.', 'tta' );
        $tickets_message .= ' ' . sprintf(
            /* translators: 1: opening link, 2: closing link */
            esc_html__( 'Don\'t have an account? %1$sCreate one here%2$s!', 'tta' ),
            '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
            '</a>'
        );
        $disable_controls = true;
        $tooltip_message  = 'basic' === $event_required
            ? __( 'You must be logged in and have at least a Basic Membership to attend this event.', 'tta' )
            : __( 'You must be logged in and have a Premium Membership to attend this event.', 'tta' );
        $waitlist_tooltip  = 'basic' === $event_required
            ? __( 'You must be logged in and have at least a Basic Membership to join the waitlist.', 'tta' )
            : __( 'You must be logged in and have a Premium Membership to join the waitlist.', 'tta' );
    }
} else {
    $first = esc_html( $context['first_name'] );
    if ( $qualifies ) {
        if ( 'free' === $event_required ) {
            if ( 'free' === $membership_level ) {
                $tickets_message .= " - " . sprintf( __( 'Thanks for being a Member, %s!', 'tta' ), $first );
                $tickets_message .= ' ' . sprintf(
                    /* translators: 1: opening link, 2: closing link */
                    __( 'Did you know that by upgrading your membership, you\'ll receive discounts and other perks? %1$sUpgrade your membership here%2$s!', 'tta' ),
                    '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                    '</a>'
                );
            } elseif ( 'basic' === $membership_level ) {
                $tickets_message .= " - " . sprintf( __( 'Thanks for being a Basic Member, %s!', 'tta' ), $first );
                $tickets_message .= ' ' . sprintf(
                    /* translators: 1: opening link, 2: closing link */
                    __( 'Did you know that by upgrading your membership to Premium, you\'ll receive even more discounts and perks? %1$sClick here to upgrade%2$s!', 'tta' ),
                    '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                    '</a>'
                );
            } else {
                $tickets_message .= " - " . sprintf( __( 'Thanks for being a Premium Member, %s!', 'tta' ), $first );
                $tickets_message .= ' ' . sprintf(
                    /* translators: 1: opening link, 2: closing link */
                    __( 'Did you know that by referring someone new to Trying to Adult, you can receive a referral bonus, including free events? %1$sClick here for more info%2$s!', 'tta' ),
                    '<a href="' . esc_url( home_url( '/referral-program' ) ) . '">',
                    '</a>'
                );
            }
        } elseif ( 'basic' === $event_required ) {
            if ( 'basic' === $membership_level ) {
                $tickets_message .= " - " . sprintf( __( 'Thanks for being a Basic Member, %s!', 'tta' ), $first );
                $tickets_message .= ' ' . sprintf(
                    /* translators: 1: opening link, 2: closing link */
                    __( 'Did you know that by upgrading your membership to Premium, you\'ll receive even more discounts and perks? %1$sClick here to upgrade%2$s!', 'tta' ),
                    '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                    '</a>'
                );
            } else { // premium
                $tickets_message .= " - " . sprintf( __( 'Thanks for being a Premium Member, %s!', 'tta' ), $first );
                $tickets_message .= ' ' . sprintf(
                    /* translators: 1: opening link, 2: closing link */
                    __( 'Did you know that by referring someone new to Trying to Adult, you can receive a referral bonus, including free events? %1$sClick here for more info%2$s!', 'tta' ),
                    '<a href="' . esc_url( home_url( '/referral-program' ) ) . '">',
                    '</a>'
                );
            }
        } else { // premium event, premium member
            $tickets_message .= " - " . sprintf( __( 'Thanks for being a Premium Member, %s!', 'tta' ), $first );
            $tickets_message .= ' ' . sprintf(
                /* translators: 1: opening link, 2: closing link */
                __( 'Did you know that by referring someone new to Trying to Adult, you can receive a referral bonus, including free events? %1$sClick here for more info%2$s!', 'tta' ),
                '<a href="' . esc_url( home_url( '/referral-program' ) ) . '">',
                '</a>'
            );
        }
    } else {
        if ( 'basic' === $event_required ) {
            $tickets_message .= ' - ' . sprintf(
                __( 'Hey %1$s, you\'ll need to upgrade to at least %2$sBasic Membership%3$s to purchase tickets for this event. %4$sClick here to upgrade%5$s!', 'tta' ),
                $first,
                '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                '</a>',
                '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                '</a>'
            );
            $disable_controls = true;
            $tooltip_message  = __( 'You must have at least a Basic Membership to attend this event.', 'tta' );
            $waitlist_disabled = true;
            $waitlist_tooltip  = __( 'You must have at least a Basic Membership to join the waitlist.', 'tta' );
        } else {
            if ( 'basic' === $membership_level ) {
                $tickets_message .= ' - ' . sprintf(
                    __( 'Hey %1$s, thanks for being a Basic Member! This event is only available to %2$sPremium Members%3$s though. %4$sClick here to upgrade%5$s to attend this event and receive even more discounts and perks!', 'tta' ),
                    $first,
                    '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                    '</a>',
                    '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                    '</a>'
                );
            } else { // free member
                $tickets_message .= ' - ' . sprintf(
                    __( 'Hey %1$s, you\'ll need to upgrade to a %2$sPremium Membership%3$s to purchase tickets for this event. %4$sClick here to upgrade%5$s!', 'tta' ),
                    $first,
                    '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                    '</a>',
                    '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">',
                    '</a>'
                );
            }
            $disable_controls = true;
            $tooltip_message  = __( 'You must have a Premium Membership to attend this event.', 'tta' );
            $waitlist_disabled = true;
            $waitlist_tooltip  = __( 'You must have a Premium Membership to join the waitlist.', 'tta' );
        }
    }
}

if ( ! $qualifies && ! $is_archived ) {
    if ( 'basic' === $event_required ) {
        $show_upgrade_btn = true;
        $upgrade_label    = __( 'Upgrade to Basic', 'tta' );
    } elseif ( 'premium' === $event_required ) {
        $show_upgrade_btn = true;
        $upgrade_label    = __( 'Upgrade to Premium', 'tta' );
    }
}

if ( $is_archived ) {
    $disable_controls = true;
    $tooltip_message  = __( 'Ticket sales are closed for this event.', 'tta' );
    $waitlist_disabled = true;
    $waitlist_tooltip  = __( 'The waitlist is closed for this event.', 'tta' );
}

$has_active_reservations = tta_event_has_active_cart_reservations( $event['ute_id'] );

if ( $is_logged_in ) {

    // b) Check waitlist membership for this event (per ticket)
    $waitlist_ticket_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ticket_id FROM {$wpdb->prefix}tta_waitlist WHERE event_ute_id = %s AND wp_user_id = %d",
            $event['ute_id'],
            $current_user_id
        )
    );
    $waitlist_ticket_ids = array_map( 'intval', $waitlist_ticket_ids );
    if ( $disable_controls && $waitlist_disabled === false ) {
        // Mirror the ticket purchase restriction
        if ( false !== strpos( $tooltip_message, 'attend this event' ) ) {
            $waitlist_tooltip = str_replace( 'attend this event', 'join the waitlist', $tooltip_message );
        } elseif ( false !== strpos( $tooltip_message, 'tickets' ) ) {
            $waitlist_tooltip = str_replace( 'purchase tickets', 'join the waitlist', $tooltip_message );
        }
        $waitlist_disabled = true;
    }

    // c) Fetch this user’s history for this event
    $member_history = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tta_memberhistory
             WHERE wpuserid = %d
               AND event_id = %d",
            $current_user_id,
            intval( $event['id'] )
        ),
        ARRAY_A
    );
}

// ───────────────
// 6) Parse & format the raw address
// ───────────────
$raw_address       = $event['address'];
$parts             = preg_split( '/\s*[-–]\s*/u', $raw_address );
$street            = trim( $parts[0] ?? '' );
$addr2             = trim( $parts[1] ?? '' );
$city              = trim( $parts[2] ?? '' );
$state             = trim( $parts[3] ?? '' );
$zip               = trim( $parts[4] ?? '' );
$street_full       = $street . ( $addr2 ? ' ' . $addr2 : '' );
$city_state_zip    = $city . ( $state || $zip ? ', ' : '' ) . $state . ( $zip ? ' ' . $zip : '' );
$formatted_address = $street_full . ' – ' . $city_state_zip;
$map_query         = rawurlencode( "{$street_full}, {$city_state_zip}" );
$map_url           = "https://www.google.com/maps/search/?api=1&query={$map_query}";

// ───────────────
// 7) Format date & time
// ───────────────
$timestamp = strtotime( $event['date'] );
$date_str = date_i18n( get_option( 'date_format' ), $timestamp );
$parts    = array_pad( explode( '|', $event['time'] ), 2, '' );
$start    = $parts[0];
$end      = $parts[1];
if ( $event['all_day_event'] ) {
    $time_str = esc_html__( 'All day', 'tta' );
} else {
    $start_fmt = $start ? date_i18n( get_option( 'time_format' ), strtotime( $start ) ) : '';
    $end_fmt   = $end ? date_i18n( get_option( 'time_format' ), strtotime( $end ) ) : '';
    $time_str  = trim( $start_fmt . ( $end_fmt ? ' – ' . $end_fmt : '' ) );
}

// ───────────────
// 8) Hero image (fallback)
// ───────────────
if ( ! empty( $event['mainimageid'] ) ) {
    $hero_html = wp_get_attachment_image( intval( $event['mainimageid'] ), 'full', false, [
        'class' => 'tta-event-hero-img',
        'alt'   => esc_attr( $event['name'] ),
    ] );
} else {
    $placeholder = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
    $hero_html   = '<img src="' . $placeholder . '" alt="" class="tta-event-hero-img">';
}

// ───────────────
// 9) Raw page content for description
// ───────────────
$page_post   = get_post( $page_id );
$raw_content = $page_post ? $page_post->post_content : '';

// ───────────────
// 9b) Build a short excerpt for the summary
// ───────────────
$description_excerpt = '';
if ( $raw_content ) {
    $description_excerpt = wp_trim_words(
        wp_strip_all_tags( $raw_content ),
        30,
        '…'
    );
}

// ───────────────
// 10) Determine sidebar cost row
// ───────────────
if ( $ticket_count > 1 ) {
    $bases     = wp_list_pluck( $tickets, 'baseeventcost' );
    $discounts = wp_list_pluck( $tickets, 'discountedmembercost' );
    $premiums  = wp_list_pluck( $tickets, 'premiummembercost' );

    $uniform_base     = ( 1 === count( array_unique( $bases ) ) );
    $uniform_discount = ( 1 === count( array_unique( $discounts ) ) );
    $uniform_premium  = ( 1 === count( array_unique( $premiums ) ) );

    if ( $uniform_base && $uniform_discount && $uniform_premium ) {
        // All tickets share the same prices
        $p_base    = reset( $bases );
        $p_basic   = reset( $discounts );
        $p_premium = reset( $premiums );

        $price_str         = $p_base    ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_base,    2 ) ) : esc_html__( 'Free', 'tta' );
        $price_str_basic   = $p_basic   ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_basic,   2 ) ) : esc_html__( 'Free', 'tta' );
        $price_str_premium = $p_premium ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_premium, 2 ) ) : esc_html__( 'Free', 'tta' );

        $cost_sidebar_row = 
            '<li class="tta-event-costmod-class">'
          . '<img class="tta-event-details-icon" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) . '" alt="Help"> '
          . '<div class="tta-event-details-icon-after"><strong>Cost: </strong>' . esc_html( $price_str )
          . '</div></li>';

        if ( 'basic' === $membership_level ) {
            $cost_sidebar_row =
                '<li class="tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic">'
              . '<img class="tta-event-details-icon" src="' 
                  . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) 
                  . '" alt="Help"> '
              . '<div class="tta-event-details-icon-after"><strong>Cost: </strong><span>' . esc_html( $price_str ) . '</span> ' 
              . esc_html( $price_str_basic )
              . '</div></li>';
        } elseif ( 'premium' === $membership_level ) {
            $cost_sidebar_row =
                '<li class="tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium">'
              . '<img class="tta-event-details-icon" src="' 
                  . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) 
                  . '" alt="Help"> '
              . '<div class="tta-event-details-icon-after"><strong>Cost: </strong><span>' . esc_html( $price_str ) . '</span> ' 
              . esc_html( $price_str_premium )
              . '</div></li>';
        }

    } else {
       // Mixed prices
      $cost_sidebar_row =
          '<li class="tta-event-costmod-class">'
        . '<img class="tta-event-details-icon" src="' 
            . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) 
            . '" alt="Help"> '
        . '<div class="tta-event-details-icon-after"><strong>' 
            . esc_html__( 'Cost:', 'tta' ) 
            . '</strong> ' 
            . esc_html__( 'Various', 'tta' ) 
        . '</div></li>';

    }
} elseif ( 1 === $ticket_count ) {
    // Single ticket
    $single     = reset( $tickets );
    $p_base     = floatval( $single['baseeventcost'] );
    $p_basic    = floatval( $single['discountedmembercost'] );
    $p_premium  = floatval( $single['premiummembercost'] );

    $price_str         = $p_base    ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_base,    2 ) ) : esc_html__( 'Free', 'tta' );
    $price_str_basic   = $p_basic   ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_basic,   2 ) ) : esc_html__( 'Free', 'tta' );
    $price_str_premium = $p_premium ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_premium, 2 ) ) : esc_html__( 'Free', 'tta' );

    $cost_sidebar_row =
        '<li class="tta-event-costmod-class">'
      . '<img class="tta-event-details-icon" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) . '" alt="Help"> '
      . '<div class="tta-event-details-icon-after"><strong>Cost: </strong>' . esc_html( $price_str )
      . '</div></li>';

    if ( 'basic' === $membership_level ) {
        $cost_sidebar_row =
            '<li class="tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic">'
          . '<img class="tta-event-details-icon" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) . '" alt="Help"> '
          . '<div class="tta-event-details-icon-after"><strong>Cost: </strong><span>' . esc_html( $price_str ) . '</span> '
          . esc_html( $price_str_basic )
          . '</div></li>';
    } elseif ( 'premium' === $membership_level ) {
        $cost_sidebar_row =
            '<li class="tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium">'
          . '<img class="tta-event-details-icon" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) . '" alt="Help"> '
          . '<div class="tta-event-details-icon-after"><strong>Cost: </strong><span>' . esc_html( $price_str ) . '</span> '
          . esc_html( $price_str_premium )
          . '</div></li>';
    }

} else {
    // No ticket rows, fall back to base costs on the event record
    $p_base    = floatval( $event['baseeventcost'] );
    $p_basic   = floatval( $event['discountedmembercost'] );
    $p_premium = floatval( $event['premiummembercost'] );

    if ( $p_base || $p_basic || $p_premium ) {
        $price_str         = $p_base    ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_base, 2 ) ) : esc_html__( 'Free', 'tta' );
        $price_str_basic   = $p_basic   ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_basic, 2 ) ) : esc_html__( 'Free', 'tta' );
        $price_str_premium = $p_premium ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_premium, 2 ) ) : esc_html__( 'Free', 'tta' );

        $cost_sidebar_row =
            '<li class="tta-event-costmod-class">'
          . '<img class="tta-event-details-icon" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) . '" alt="Help"> '
          . '<div class="tta-event-details-icon-after"><strong>Cost: </strong>' . esc_html( $price_str )
          . '</div></li>';

        if ( 'basic' === $membership_level ) {
            $cost_sidebar_row =
                '<li class="tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic">'
              . '<img class="tta-event-details-icon" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) . '" alt="Help"> '
              . '<div class="tta-event-details-icon-after"><strong>Cost: </strong><span>' . esc_html( $price_str ) . '</span> '
              . esc_html( $price_str_basic )
              . '</div></li>';
        } elseif ( 'premium' === $membership_level ) {
            $cost_sidebar_row =
                '<li class="tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium">'
              . '<img class="tta-event-details-icon" src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ) . '" alt="Help"> '
              . '<div class="tta-event-details-icon-after"><strong>Cost: </strong><span>' . esc_html( $price_str ) . '</span> '
              . esc_html( $price_str_premium )
              . '</div></li>';
        }
    } else {
        $cost_sidebar_row = '';
    }

}


// ───────────────
// 11) Build Event Schema markup
// ───────────────
$start_ts  = strtotime( $event['date'] . ' ' . ( $start ?? '00:00' ) );
$end_ts    = $event['all_day_event'] ? $start_ts : strtotime( $event['date'] . ' ' . ( $end ?? '00:00' ) );
$schema    = [
    '@context'  => 'https://schema.org',
    '@type'     => 'Event',
    'name'      => $event['name'],
    'description' => wp_strip_all_tags( $raw_content ),
    'startDate' => date( 'c', $start_ts ),
    'endDate'   => date( 'c', $end_ts ),
    'eventStatus' => 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => $event['virtual_event'] ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
    'url'       => get_permalink(),
];

$location = [
    '@type' => 'Place',
    'name'  => $event['venuename'] ?: $event['name'],
    'address' => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $street_full,
        'addressLocality' => $city,
        'addressRegion'   => $state,
        'postalCode'      => $zip,
        'addressCountry'  => 'US',
    ],
];
$schema['location'] = $location;

if ( ! empty( $event['mainimageid'] ) ) {
    $image_url       = wp_get_attachment_image_url( intval( $event['mainimageid'] ), 'full' );
    $schema['image'] = $image_url;
}

if ( $ticket_count > 0 ) {
    $all_sold_out = true;
    foreach ( $tickets as $t ) {
        if ( intval( $t['ticketlimit'] ) > 0 ) {
            $all_sold_out = false;
            break;
        }
    }

    if ( 1 === $ticket_count ) {
        $single  = reset( $tickets );
        $price   = floatval( $single['baseeventcost'] );
        if ( $price > 0 ) {
            $schema['offers'] = [
                '@type'         => 'Offer',
                'price'         => number_format( $price, 2, '.', '' ),
                'priceCurrency' => 'USD',
                'availability'  => $all_sold_out ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
            ];
        }
    }
}

echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
?>

<div class="wrap event-page tta-event-page">

  <!-- HERO -->
<!-- HERO -->
<section class="tta-event-hero">
  <div class="tta-event-hero-inner">

    <div class="tta-event-hero-text">
      <h1 class="tta-event-title">
        <?php echo esc_html( $event['name'] ); ?>
      </h1>
      <div class="tta-event-meta">
        <div class="tta-event-meta-item">
          <img
            class="tta-event-details-icon"
            src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/calendar.svg' ); ?>"
            alt="<?php esc_attr_e( 'Date icon', 'tta' ); ?>"
          />
          <time
            class="tta-event-date"
            datetime="<?php echo esc_attr( date( 'c', $timestamp ) ); ?>"
          >
            <?php echo esc_html( $date_str ); ?>
          </time>
        </div>
        <div class="tta-event-meta-item">
          <img
            class="tta-event-details-icon"
            src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/clock.svg' ); ?>"
            alt="<?php esc_attr_e( 'Date icon', 'tta' ); ?>"
          />
          <span class="tta-event-time">
            <?php echo esc_html( $time_str ); ?>
          </span>
        </div>
        <div class="tta-event-meta-item">
          <img
            class="tta-event-details-icon"
            src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/billing.svg' ); ?>"
            alt="<?php esc_attr_e( 'Member level icon', 'tta' ); ?>"
          />
          <span class="tta-event-type-meta"><?php echo esc_html( $event_type_label ); ?></span>
        </div>
      </div>

      <div class="tta-event-share">
        <span class="tta-share-label"><?php esc_html_e( 'Share this event', 'tta' ); ?></span>
        <a href="#" class="tta-share-link" data-platform="facebook">
          <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/facebook.svg' ); ?>" alt="Facebook">
        </a>
        <a href="#" class="tta-share-link" data-platform="instagram">
          <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/instagram.svg' ); ?>" alt="Instagram">
        </a>
      </div>

      <?php if ( ! $is_archived && ( ! $all_sold_out || $has_waitlist ) ) : ?>
        <a href="<?php echo $is_logged_in ? '#tta-event-buy' : '#tta-login-message'; ?>" class="tta-button tta-button-primary<?php echo $is_logged_in ? '' : ' tta-scroll-login'; ?>">
          <?php
          if ( $all_sold_out && $has_waitlist ) {
            echo $is_logged_in ? esc_html__( 'Join The Waitlist', 'tta' ) : esc_html__( 'Log in to Join The Waitlist', 'tta' );
          } else {
            echo $is_logged_in ? esc_html__( 'Buy Tickets', 'tta' ) : esc_html__( 'Log in to Buy Tickets', 'tta' );
          }
          ?>
        </a>
      <?php endif; ?>
    </div>
    <div class="tta-event-hero-image">
      <?php echo $hero_html; ?>
    </div>

  </div>
</section>


  <!-- MAIN + SIDEBAR -->
  <div class="tta-event-content-wrap">
    <div class="tta-event-columns">
      <!-- MAIN CONTENT -->
      <main class="tta-event-main">

      <?php if ( $raw_content ) : ?>
        <section class="tta-event-section tta-event-description-accordion">
          <div class="tta-accordion">
            <?php if ( $is_archived ) : ?>
              <div class="tta-message-center">
                <p><?php echo esc_html__( "This event has passed, but don't worry! There's tons of other upcoming events.", 'tta' ); ?>
                  <a href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( 'Click here to see all upcoming events', 'tta' ); ?></a>
                </p>
              </div>
            <?php endif; ?>
            <div class="tta-accordion-content">
              <h2><?php esc_html_e( 'About This Event', 'tta' ); ?></h2>
              <?php echo apply_filters( 'the_content', $raw_content ); ?>
            </div>
            <button type="button" class="tta-button tta-button-primary tta-accordion-toggle">
              <?php esc_html_e( 'Read more', 'tta' ); ?>
            </button>
          </div>
        </section>
      <?php endif; ?>

      <?php if ( ! $is_logged_in && ! $is_archived ) : ?>
        <section id="tta-login-message" class="tta-message-center tta-login-accordion">
          <h2><?php esc_html_e( 'Log in or Register Here', 'tta' ); ?></h2>
          <div class="tta-accordion">
            <p>
              <?php
              printf(
                /* translators: 1: opening login button, 2: closing login button, 3: opening registration link, 4: closing registration link */
                esc_html__( 'Ticket discounts may be available! Log in below to check. Don\'t have an account? Create one below or become a Member today!%1$s', 'tta' ),
                '<div><a href="#tta-login-message" class="tta-button tta-button-primary tta-show-register">' . esc_html__( 'Create Account', 'tta' ) . '</a><a href="/become-a-member" class="tta-button tta-button-primary">' . esc_html__( 'Become a Member', 'tta' ) . '</a></div>',
              );
              ?>
            </p>
            <div class="tta-accordion-content expanded">
              <?php
             
// 1. Build the form but DON'T echo it yet
$form_html = wp_login_form( [
    'echo'     => false,                // capture the markup
    'redirect' => get_permalink(),      // where to send the user after login
    'remember' => true,                 // show “Remember Me”
] );

// 2. Build the lost-password link
$lost_pw_url = wp_lostpassword_url( get_permalink() ); // redirect back here after reset
$lost_pw_html = sprintf(
    '<p class="login-lost-password"><a href="%s">%s</a></p>',
    esc_url( $lost_pw_url ),
    __( 'Forgot your password?', 'textdomain' )
);

// 3. Output form + link
echo '<div id="tta-login-wrap">' . $form_html . $lost_pw_html . '</div>';

?>

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
      <?php endif; ?>

      <section id="tta-event-buy" class="tta-event-buy">
        <h2><?php esc_html_e( 'Get Your Tickets Now', 'tta' ); ?></h2>
        <p class="tta-ticket-context">
          <?php echo wp_kses_post( $tickets_message ); ?>
        </p>

        <?php if ( $tickets ) : ?>
          <?php
            $all_sold_out = true;
            foreach ( $tickets as $ticket ) {
                if ( intval( $ticket['ticketlimit'] ) > 0 ) {
                    $all_sold_out = false;
                    break;
                }
            }
            foreach ( $tickets as $ticket ) :
            $limit      = intval( $ticket['ticketlimit'] );
            $available  = $limit > 0 ? $limit : 0;
            $avail_text = $available > 0
                ? sprintf( esc_html__( 'Only %d Left!', 'tta' ), $available )
                : esc_html__( 'Sold Out', 'tta' );
            $avail_text = '<span class="tta-fomo-remaining-styling">' . $avail_text . '</span>';

            // membership‐tier pricing for this row
            $b   = floatval( $ticket['baseeventcost'] );
            $bb  = floatval( $ticket['discountedmembercost'] );
            $bp  = floatval( $ticket['premiummembercost'] );

            $pb   = $b  ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $b, 2 ) )   : esc_html__( 'Free', 'tta' );
            $pbb  = $bb ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $bb,2 ) )   : esc_html__( 'Free', 'tta' );
            $pbp  = $bp ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $bp,2 ) )   : esc_html__( 'Free', 'tta' );

            if ( 'basic' === $membership_level ) {
                $price_row = "<span class='tta-ticket-price tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic'><strong>Cost: </strong><span>{$pb}</span> {$pbb}</span>";
            } elseif ( 'premium' === $membership_level ) {
                $price_row = "<span class='tta-ticket-price tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium'><strong>Cost: </strong><span>{$pb}</span> {$pbp}</span>";
            } else {
                $price_row = "<span class='tta-ticket-price tta-event-costmod-class'><strong>Cost: </strong>{$pb}</span>";
            }
        ?>
            <?php $is_sold_out = $available < 1; ?>
            <?php
              $purchased = intval( $purchased_counts[ intval( $ticket['id'] ) ] ?? 0 );
              $ticket_limit = intval( $ticket['memberlimit'] ?? 2 );
              $remaining_allowance = max( 0, $ticket_limit - $purchased );
              $ticket_limit_reached = ( $remaining_allowance < 1 );
              $ticket_tooltip = $tooltip_message;
              if ( $ticket_limit_reached && ! $disable_controls ) {
                  $ticket_tooltip = sprintf( __( 'You have already purchased the limit of %d for this ticket.', 'tta' ), $ticket_limit );
              }
            ?>
            <div class="tta-top-indiv-wrapper">
              <div class="tta-ticket-item">
                <?php echo $price_row . ' ' . $avail_text; ?>
              </div>
              <div class="tta-ticket-quantity">
                <span class="tta-ticket-name"><?php echo esc_html( $ticket['ticket_name'] ); ?></span>
                <div>
                  <button type="button" class="tta-qty-decrease<?php echo ($is_sold_out || $disable_controls || $ticket_limit_reached) ? ' tta-disabled' : ''; ?><?php echo ($disable_controls || $ticket_limit_reached) ? ' tta-tooltip-trigger' : ''; ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'tta' ); ?>" <?php disabled( $is_sold_out || $disable_controls || $ticket_limit_reached ); ?><?php echo ($disable_controls || $ticket_limit_reached) ? ' data-tooltip="' . esc_attr( $ticket_tooltip ) . '"' : ''; ?>>–</button>
                  <input
                    type="number"
                    name="tta_ticket_qty[<?php echo esc_attr( $ticket['id'] ); ?>]"
                    class="tta-qty-input<?php echo ($is_sold_out || $disable_controls || $ticket_limit_reached) ? ' tta-disabled' : ''; ?><?php echo ($disable_controls || $ticket_limit_reached) ? ' tta-tooltip-trigger' : ''; ?>"
                    value="<?php echo esc_attr( $cart_quantities[ $ticket['id'] ] ?? 0 ); ?>"
                    min="0"
                    <?php if ( $available ): ?>max="<?php echo esc_attr( $available ); ?>"<?php endif; ?>
                    data-limit="<?php echo esc_attr( $ticket_limit ); ?>"
                    data-purchased="<?php echo esc_attr( $purchased ); ?>"
                    <?php disabled( $is_sold_out || $disable_controls || $ticket_limit_reached ); ?><?php echo ($disable_controls || $ticket_limit_reached) ? ' data-tooltip="' . esc_attr( $ticket_tooltip ) . '"' : ''; ?>
                  />
                  <button type="button" class="tta-qty-increase<?php echo ($is_sold_out || $disable_controls || $ticket_limit_reached) ? ' tta-disabled' : ''; ?><?php echo ($disable_controls || $ticket_limit_reached) ? ' tta-tooltip-trigger' : ''; ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'tta' ); ?>" <?php disabled( $is_sold_out || $disable_controls || $ticket_limit_reached ); ?><?php echo ($disable_controls || $ticket_limit_reached) ? ' data-tooltip="' . esc_attr( $ticket_tooltip ) . '"' : ''; ?>>+</button>
                </div>
                <div class="tta-ticket-notice" aria-live="polite"></div>
              </div>
              <?php if ( $is_sold_out && $has_waitlist && ! $has_active_reservations ) : ?>
              <?php
                $ticket_waitlist_disabled = $waitlist_disabled;
                $ticket_waitlist_tooltip  = $waitlist_tooltip;
                if ( in_array( intval( $ticket['id'] ), $waitlist_ticket_ids, true ) ) {
                    $ticket_waitlist_disabled = true;
                    $ticket_waitlist_tooltip  = __( 'You are already on the waitlist for this ticket.', 'tta' );
                }
              ?>
              <div class="tta-ticket-waitlist">
                <button type="button" class="tta-button tta-button-primary tta-join-waitlist<?php echo $ticket_waitlist_disabled ? ' tta-disabled tta-tooltip-trigger' : ''; ?>" data-ticket-id="<?php echo esc_attr( $ticket['id'] ); ?>" data-ticket-name="<?php echo esc_attr( $ticket['ticket_name'] ); ?>"<?php echo $ticket_waitlist_disabled ? ' disabled data-tooltip="' . esc_attr( $ticket_waitlist_tooltip ) . '"' : ''; ?>>
                  <?php esc_html_e( 'Join The Waitlist', 'tta' ); ?>
                </button>
                <?php if ( in_array( intval( $ticket['id'] ), $waitlist_ticket_ids, true ) ) : ?>
                <button type="button" class="tta-button tta-button-secondary tta-leave-waitlist" data-ticket-id="<?php echo esc_attr( $ticket['id'] ); ?>">
                  <?php esc_html_e( 'Leave the Waitlist', 'tta' ); ?>
                </button>
                <?php endif; ?>
                <?php if ( $waitlist_disabled && $show_upgrade_btn ) : ?>
                <a href="<?php echo esc_url( home_url( '/become-a-member/' ) ); ?>" class="tta-button tta-button-primary tta-upgrade-btn">
                  <?php echo esc_html( $upgrade_label ); ?>
                </a>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else : ?>
          <p><?php esc_html_e( 'No tickets available for this event.', 'tta' ); ?></p>
        <?php endif; ?>

        <?php if ( ! $all_sold_out ) : ?>
        <div class="tta-tickets-addtocart-button">
          <button
            type="button"
            id="tta-get-tickets"
            class="tta-button tta-button-primary<?php echo ($all_sold_out || $disable_controls) ? ' tta-disabled' : ''; ?><?php echo $disable_controls ? ' tta-tooltip-trigger' : ''; ?>"
            <?php disabled( empty( $tickets ) || $all_sold_out || $disable_controls ); ?><?php echo $disable_controls ? ' data-tooltip="' . esc_attr( $tooltip_message ) . '"' : ''; ?>
          >
            <?php echo $all_sold_out ? esc_html__( 'Sold Out', 'tta' ) : esc_html__( 'Get Tickets', 'tta' ); ?>
          </button>
          <?php if ( $disable_controls && $show_upgrade_btn ) : ?>
          <a href="<?php echo esc_url( home_url( '/become-a-member/' ) ); ?>" class="tta-button tta-button-primary tta-upgrade-btn">
            <?php echo esc_html( $upgrade_label ); ?>
          </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </section>

      <?php
      // Grab & sanitize gallery IDs
      $other_ids = ! empty( $event['otherimageids'] )
          ? array_filter( array_map( 'intval', explode( ',', $event['otherimageids'] ) ) )
          : [];

      $attendees = tta_get_event_attendee_profiles( $event['id'] );
      $hosts = [];
      $volunteers = [];
      $named = [];
      $hidden = [];
      foreach ( $attendees as $att ) {
        if ( ! empty( $att['first_name'] ) && empty( $att['hide'] ) ) {
          if ( ! empty( $att['is_host'] ) ) {
            $hosts[] = $att;
          } elseif ( ! empty( $att['is_volunteer'] ) ) {
            $volunteers[] = $att;
          } else {
            $named[] = $att;
          }
        } else {
          $hidden[] = $att;
        }
      }
      $sort_cb = function( $a, $b ) {
        $cmp = strcasecmp( $a['first_name'], $b['first_name'] );
        if ( 0 === $cmp ) {
          $cmp = strcasecmp( $a['last_name'], $b['last_name'] );
        }
        return $cmp;
      };
      usort( $hosts, $sort_cb );
      usort( $volunteers, $sort_cb );
      usort( $named, $sort_cb );
      $attendees = array_merge( $hosts, $volunteers, $named, $hidden );
      $placeholder = TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/placeholder-profile.svg';

      if ( ! empty( $other_ids ) ) : ?>
        <section class="tta-event-section tta-event-image-gallery-accordion">
          <div class="tta-accordion">
            <div class="tta-accordion-content">
              <h2><?php esc_html_e( 'Image Gallery', 'tta' ); ?></h2>
              <div class="tta-gallery-grid">
                <?php foreach ( $other_ids as $img_id ) : ?>
                  <div class="tta-gallery-item">
                    <?php
                      // use a medium-large size for good resolution;
                      $full = wp_get_attachment_image_url( $img_id, 'large' );
                      echo wp_get_attachment_image(
                        $img_id,
                        'medium_large',
                        false,
                        [
                          'class'     => 'tta-popup-img',
                          'data-full' => $full ? $full : wp_get_attachment_url( $img_id ),
                        ]
                      );
                    ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <button type="button" class="tta-button tta-button-primary tta-accordion-toggle-image-gallery">
              <?php esc_html_e( 'View Gallery', 'tta' ); ?>
            </button>
          </div>
        </section>

      <?php endif; ?>

      <?php if ( ! empty( $attendees ) ) : ?>
        <section class="tta-event-section tta-event-image-gallery-accordion tta-event-attendees-section">
          <div class="tta-accordion">
            <div class="tta-accordion-content">
              <h2><?php esc_html_e( 'Attendees', 'tta' ); ?></h2>
              <div class="tta-gallery-grid">
                <?php $hidden_i = 1; foreach ( $attendees as $att ) : ?>
                  <div class="tta-gallery-item">
                    <div class="tta-attendee-photo-wrapper">
                      <?php
                        if ( ! empty( $att['img_id'] ) && empty( $att['hide'] ) ) {
                            echo wp_get_attachment_image(
                                $att['img_id'],
                                'medium_large',
                                false,
                                [
                                    'class'     => 'tta-popup-img',
                                    'data-full' => wp_get_attachment_image_url( $att['img_id'], 'large' ),
                                ]
                            );
                        } else {
                            echo '<img src="' . esc_url( $placeholder ) . '" alt="" class="tta-popup-img" data-full="' . esc_url( $placeholder ) . '">';
                        }
                        if ( ! empty( $att['is_host'] ) ) {
                            echo '<span class="tta-attendee-role">' . esc_html__( 'Host', 'tta' ) . '</span>';
                        } elseif ( ! empty( $att['is_volunteer'] ) ) {
                            echo '<span class="tta-attendee-role">' . esc_html__( 'Volunteer', 'tta' ) . '</span>';
                        }
                        if ( empty( $att['hide'] ) && ! empty( $att['first_name'] ) ) {
                            $name  = trim( $att['first_name'] . ' ' . $att['last_name'] );
                            $level = tta_get_membership_label( $att['membership_level'] );
                        } else {
                            $name  = sprintf( __( 'Attendee #%d', 'tta' ), $hidden_i );
                            $level = '';
                            $hidden_i++;
                        }
                      ?>
                    </div>
                    <span class="tta-attendee-name"><?php echo esc_html( $name ); ?></span>
                    <?php if ( $level ) : ?>
                      <span class="tta-attendee-level"><?php echo esc_html( $level ); ?></span>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <button type="button" class="tta-button tta-button-primary tta-accordion-toggle-image-gallery">
              <?php esc_html_e( 'View All Attendees', 'tta' ); ?>
            </button>
          </div>
        </section>
      <?php endif; ?>

    </main>

    <div id="tta-waitlist-overlay" class="tta-waitlist-overlay" style="display:none;">
      <div class="tta-waitlist-modal">
        <button type="button" class="tta-waitlist-close" aria-label="Close">×</button>
        <h2><?php esc_html_e( 'Join The Waitlist', 'tta' ); ?></h2>
        <p class="tta-waitlist-description"><?php esc_html_e( 'We\'ll notify you if a spot opens up.', 'tta' ); ?></p>
        <form id="tta-waitlist-form">
          <input type="hidden" name="ticket_id" value="">
          <input type="hidden" name="ticket_name" value="">
          <label><?php esc_html_e( 'First Name', 'tta' ); ?>
            <input type="text" name="first_name" required>
          </label>
          <label><?php esc_html_e( 'Last Name', 'tta' ); ?>
            <input type="text" name="last_name" required>
          </label>
          <label><?php esc_html_e( 'Email', 'tta' ); ?>
            <input type="email" name="email" required>
          </label>
          <label><?php esc_html_e( 'Phone', 'tta' ); ?>
            <input type="tel" name="phone">
          </label>
          <label><input type="checkbox" name="opt_email" checked> <?php esc_html_e( 'email me when a spot becomes available', 'tta' ); ?></label>
          <label><input type="checkbox" name="opt_sms" checked> <?php esc_html_e( 'text me when a spot becomes available', 'tta' ); ?></label>
          <button type="submit" class="tta-button tta-button-primary"><?php esc_html_e( 'Join Waitlist', 'tta' ); ?></button>
          <span class="tta-progress-spinner">
            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
          </span>
          <span class="tta-admin-progress-response"><p class="tta-admin-progress-response-p"></p></span>
        </form>
      </div>
    </div>

    <!-- SIDEBAR -->
    <aside class="tta-event-sidebar">
      <div class="tta-event-details">
        <h2><?php esc_html_e( 'Event Details', 'tta' ); ?></h2>
        <ul>
          <li>
            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/calendar.svg' ); ?>" alt="Help">
            <div class="tta-event-details-icon-after">
              <strong><?php esc_html_e( 'Date', 'tta' ); ?>:</strong> <?php echo esc_html( $date_str ); ?>
            </div>
          </li>
          <li>
            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/clock.svg' ); ?>" alt="Help">
            <div class="tta-event-details-icon-after">
              <strong><?php esc_html_e( 'Time', 'tta' ); ?>:</strong> <?php echo esc_html( $time_str ); ?>
            </div>
          </li>
          <?php echo $cost_sidebar_row; ?>
          <li>
            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/billing.svg' ); ?>" alt="Help">
            <div class="tta-event-details-icon-after">
              <strong><?php esc_html_e( 'Event Type', 'tta' ); ?>:</strong> <?php
              $etype_text = esc_html( $event_type_label );
              if ( in_array( $event_required, [ 'basic', 'premium' ], true ) ) {
                  $etype_text = '<a href="' . esc_url( home_url( '/become-a-member/' ) ) . '">' . $etype_text . '</a>';
              }
              echo $etype_text; ?>
            </div>
          </li>
          <li>
            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/store.svg' ); ?>" alt="Help">
            <div class="tta-event-details-icon-after">
              <strong><?php esc_html_e( 'Venue', 'tta' ); ?>:</strong> <?php echo '<a href="' . $event['venueurl'] . '">' . esc_html( $event['venuename'] ) . '</a>'; ?>
            </div>
          </li>
          <li>
            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/location.svg' ); ?>" alt="Help">
            <div class="tta-event-details-icon-after">
              <strong><?php esc_html_e( 'Location', 'tta' ); ?>:</strong>
              <a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener">
                <?php echo esc_html( $formatted_address ); ?>
              </a>
            </div>
          </li>
          <li class="tta-event-map-embed">
            <iframe
              width="100%" height="200" frameborder="0" style="border:0"
              loading="lazy" allowfullscreen
              src="https://maps.google.com/maps?q=<?php echo rawurlencode( $formatted_address ); ?>&output=embed">
            </iframe>
          </li>



          <?php
          // 1) Collect your raw URL columns
          $raw_urls = [
              $event['url2'] ?? '',
              $event['url3'] ?? '',
              $event['url4'] ?? '',
          ];

          $additional_links = [];

          // 2) Map hostnames to icon filenames
          $icon_map = [
              'facebook.com'     => 'facebook.svg',
              'instagram.com'    => 'instagram.svg',
              'pinterest.com'    => 'pinterest.svg',
              'reddit.com'       => 'reddit.svg',
              'tripadvisor.com'  => 'tripadvisor.svg',
              'yelp.com'         => 'yelp.svg',
          ];

                    // 4) Add the venue’s own website (if set)
          if ( ! empty( $event['venueurl'] ) ) {
              $additional_links[] = [
                  'href' => esc_url( $event['venueurl'] ),
                  'icon' => esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/website.svg' ),
                  'alt'  => esc_attr__( 'Venue Website', 'tta' ),
              ];
          }

          // 3) Loop and detect service
          foreach ( $raw_urls as $url ) {
              $url = trim( $url );
              if ( empty( $url ) ) {
                  continue;
              }

              $host        = parse_url( $url, PHP_URL_HOST );
              $service_key = '';

              if ( $host ) {
                  $host = strtolower( $host );
                  foreach ( $icon_map as $domain => $icon_file ) {
                      if ( false !== strpos( $host, $domain ) ) {
                          $service_key = $icon_file;
                          break;
                      }
                  }
              }

              // Skip unknown services entirely
              if ( empty( $service_key ) ) {
                  continue;
              }

              $additional_links[] = [
                  'href' => esc_url( $url ),
                  'icon' => esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/' . $service_key ),
                  // alt text based on filename (without extension)
                  'alt'  => esc_attr( ucfirst( pathinfo( $service_key, PATHINFO_FILENAME ) ) ),
              ];
          }




          // 4) Only render if there’s at least one valid link
          if ( ! empty( $additional_links ) ) : ?>
            <li>
              <section class="tta-event-section tta-event-additional-links">
                <h2 class="tta-eventpage-sidebar-heading"><?php esc_html_e( 'Venue Links', 'tta' ); ?></h2>
                <div class="tta-additional-links">
                  <?php foreach ( $additional_links as $link ) : ?>
                    <a href="<?php echo $link['href']; ?>" target="_blank" rel="noopener">
                      <img src="<?php echo $link['icon']; ?>" alt="<?php echo $link['alt']; ?>" />
                    </a>
                  <?php endforeach; ?>
                </div>
          </section>
        </li>
      <?php endif; ?>

          <li>
            <section class="tta-event-section tta-your-events">
              <h2 class="tta-eventpage-sidebar-heading"><?php esc_html_e( 'Your Events', 'tta' ); ?></h2>
              <ul class="tta-your-events-list">
              <?php if ( ! $is_logged_in ) : ?>
                <li>
                  <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/login.svg' ); ?>" alt="<?php esc_attr_e( 'Login', 'tta' ); ?>">
                  <div class="tta-event-details-icon-after">
                    <?php if ( $is_archived ) : ?>
                      <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Login to see info about your events', 'tta' ); ?></a>
                    <?php else : ?>
                      <a href="#tta-login-message" class="tta-scroll-login"><?php esc_html_e( 'Login to see info about your events', 'tta' ); ?></a>
                    <?php endif; ?>
                  </div>
                </li>
              <?php else : ?>
                <li>
                  <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/profile.svg' ); ?>" alt="<?php esc_attr_e( 'Profile', 'tta' ); ?>">
                  <div class="tta-event-details-icon-after"><a href="<?php echo esc_url( home_url( '/member-dashboard/?tab=profile', 'relative' ) ); ?>" class="tta-dashboard-link" data-tab="profile"><?php esc_html_e( 'Your Profile Info', 'tta' ); ?></a></div>
                </li>
                <li>
                  <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/upcoming.svg' ); ?>" alt="<?php esc_attr_e( 'Upcoming', 'tta' ); ?>">
                  <div class="tta-event-details-icon-after"><a href="<?php echo esc_url( home_url( '/member-dashboard/?tab=upcoming', 'relative' ) ); ?>" class="tta-dashboard-link" data-tab="upcoming"><?php esc_html_e( 'Your Upcoming Events', 'tta' ); ?></a></div>
                </li>
                <li>
                  <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/past.svg' ); ?>" alt="<?php esc_attr_e( 'Past', 'tta' ); ?>">
                  <div class="tta-event-details-icon-after"><a href="<?php echo esc_url( home_url( '/member-dashboard/?tab=past', 'relative' ) ); ?>" class="tta-dashboard-link" data-tab="past"><?php esc_html_e( 'Your Past Events', 'tta' ); ?></a></div>
                </li>
                <li>
                  <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/billing.svg' ); ?>" alt="<?php esc_attr_e( 'Billing', 'tta' ); ?>">
                  <div class="tta-event-details-icon-after"><a href="<?php echo esc_url( home_url( '/member-dashboard/?tab=billing', 'relative' ) ); ?>" class="tta-dashboard-link" data-tab="billing"><?php esc_html_e( 'Membership Details', 'tta' ); ?></a></div>
                </li>
                <li>
                  <img class="tta-event-details-icon logout-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/login.svg' ); ?>" alt="<?php esc_attr_e( 'Log out', 'tta' ); ?>">
                  <div class="tta-event-details-icon-after"><a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Log out', 'tta' ); ?></a></div>
                </li>
              <?php endif; ?>
              </ul>
            </section>
          </li>




          <li>
            <section class="tta-event-section tta-event-description-accordion">
              <div class="tta-accordion">
                <div class="tta-accordion-content">
                  <h2 class="tta-eventpage-sidebar-heading"><?php esc_html_e( 'Refund Policy', 'tta' ); ?></h2>
                  <p>PLEASE READ BELOW! Once you claim a ticket via the Trying to Adult RVA website, you are expected to show for that event.</p>
                  <p>If it is a free event, please notify the event host if you are no longer able to attend so that your ticket can be made available to others. Please read the attendance rules for more information.</p>
                  <p>If it is a paid event, you are responsible for finding a replacement if you can no longer attend. The replacement cannot be a banned/removed member. The best way to find a replacement is to post a comment on the specific Meetup event page.
                  <p>You can also message the event host for additional assistance. Once you find a replacement, please send a message to the event host to assist with the exchange.  Please do not use payment apps to exchange tickets. If you cannot find someone to claim your spot, you will forfeit the cost paid.</p>
                </div>
                <button type="button" class="tta-button tta-button-primary tta-accordion-toggle">
                  <?php esc_html_e( 'Read more', 'tta' ); ?>
                </button>
              </div>
            </section>
          </li>











        </ul>
      </div>

      <?php if ( ! empty( $event['mapiframe'] ) ) : ?>
        <div class="tta-event-map">
          <?php echo $event['mapiframe']; ?>
        </div>
      <?php endif; ?>
    </aside>

    <aside class="tta-event-right">
    <div class="tta-events-ad">
        <h2><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/deal.svg' ); ?>" alt=""><?php esc_html_e( 'Meet Our Local Partners', 'tta' ); ?></h2>
        <p class="tta-events-ad__subtitle"><?php esc_html_e( 'We\'re grateful for local partners & businesses that help make Trying to Adult possible. Check out our featured partner below!', 'tta' ); ?></p>
        <?php $ad = tta_get_random_ad(); ?>
        <?php if ( $ad ) : ?>
          <?php $img = wp_get_attachment_image( intval( $ad['image_id'] ), 'medium' ); ?>
          <?php if ( $ad['url'] ) : ?><a href="<?php echo esc_url( $ad['url'] ); ?>" target="_blank" rel="noopener"><?php endif; ?>
          <?php echo $img ? $img : '<img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/ads/placeholder1.svg' ) . '" alt="">'; ?>
          <?php if ( $ad['url'] ) : ?></a><?php endif; ?>
          <div class="tta-events-ad__info">
            <?php if ( ! empty( $ad['business_name'] ) ) : ?>
              <div class="tta-events-ad__info-item">
                <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/store.svg' ); ?>" alt="<?php esc_attr_e( 'Business', 'tta' ); ?>">
                <div class="tta-event-details-icon-after">
                  <?php if ( ! empty( $ad['url'] ) ) : ?>
                    <a href="<?php echo esc_url( $ad['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ad['business_name'] ); ?></a>
                  <?php else : ?>
                    <?php echo esc_html( $ad['business_name'] ); ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>
            <?php if ( ! empty( $ad['business_phone'] ) ) : ?>
              <?php $tel = preg_replace( '/[^0-9+]/', '', $ad['business_phone'] ); ?>
              <div class="tta-events-ad__info-item">
                <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/phone-outline.svg' ); ?>" alt="<?php esc_attr_e( 'Phone', 'tta' ); ?>">
                <div class="tta-event-details-icon-after"><a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $ad['business_phone'] ); ?></a></div>
              </div>
            <?php endif; ?>
            <?php if ( ! empty( $ad['business_address'] ) ) : ?>
              <?php $map = 'https://www.google.com/maps/search/?api=1&query=' . urlencode( $ad['business_address'] ); ?>
              <div class="tta-events-ad__info-item">
                <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/location.svg' ); ?>" alt="<?php esc_attr_e( 'Address', 'tta' ); ?>">
                <div class="tta-event-details-icon-after"><a href="<?php echo esc_url( $map ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ad['business_address'] ); ?></a></div>
              </div>
            <?php endif; ?>
          </div>
        <?php else : ?>
          <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/ads/placeholder1.svg' ); ?>" alt="Ad" />
        <?php endif; ?>
      </div>
    </aside>
    </div><!-- .tta-event-columns -->

  </div><!-- .tta-event-content-wrap -->

  <!-- OTHER UPCOMING EVENTS -->
  <section class="tta-related-events">
    <h2><?php esc_html_e( 'Other Upcoming Events', 'tta' ); ?></h2>
    <div class="tta-related-events-grid">
      <?php if ( $related ) : ?>
        <?php foreach ( $related as $re ) :
          $url = get_permalink( $re['page_id'] );
          if ( ! empty( $re['mainimageid'] ) ) {
            $img_url = wp_get_attachment_image_url( intval( $re['mainimageid'] ), 'full' );
          } else {
            $img_url = TTA_PLUGIN_URL . 'assets/images/admin/default-event.png';
          }
          $time_parts = array_pad( explode( '|', $re['time'] ), 2, '' );
          $rs        = $time_parts[0];
          $re_ts     = strtotime( $re['date'] . ( $rs ? ' ' . $rs : '' ) );
          $dt_iso = date( 'c', $re_ts );
          $dt_disp = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $re_ts );
        ?>
          <a class="tta-related-event" href="<?php echo esc_url( $url ); ?>">
            <div class="tta-event-thumb" style="background-image:url('<?php echo esc_url( $img_url ); ?>');"></div>
            <div class="tta-related-event-info">
              <h3><?php echo esc_html( $re['name'] ); ?></h3>
              <img
                class="tta-event-details-icon"
                src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/calendar.svg' ); ?>"
                alt="<?php esc_attr_e( 'Date icon', 'tta' ); ?>"
              />
              <time datetime="<?php echo esc_attr( $dt_iso ); ?>"><?php echo esc_html( $dt_disp ); ?></time>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else : ?>
        <p><?php esc_html_e( 'No related events found.', 'tta' ); ?></p>
      <?php endif; ?>
    </div>
  </section>

</div><!-- .tta-event-page -->

<?php
get_footer();
