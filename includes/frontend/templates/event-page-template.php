<?php
/**
 * Template Name: Event Page
 *
 * @package TTA
 */

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
$event        = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$events_table} WHERE page_id = %d",
        $page_id
    ),
    ARRAY_A
);
if ( ! $event ) {
    echo '<div class="wrap"><h1>' . esc_html__( 'Event not found.', 'tta' ) . '</h1>'
       . '<p>' . esc_html__( 'Sorry, this event does not exist.', 'tta' ) . '</p></div>';
    get_footer();
    exit;
}

// ───────────────
// 3) Fetch this event’s ticket types
// ───────────────
$tickets_table = $wpdb->prefix . 'tta_tickets';
$tickets       = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT id, ticket_name, attendancelimit, baseeventcost, discountedmembercost, premiummembercost
         FROM {$tickets_table}
         WHERE event_ute_id = %s
         ORDER BY id ASC",
        $event['ute_id']
    ),
    ARRAY_A
);
$ticket_count = count( $tickets );

// ───────────────
// 4) Fetch “related” upcoming events
// ───────────────
$related = $wpdb->get_results(
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

// ───────────────
// 5) Determine logged-in user context
// ───────────────
$is_logged_in     = is_user_logged_in();
$current_user_id  = $is_logged_in ? get_current_user_id() : 0;
$member_row       = [];
$membership_level = 'free';
$is_on_waitlist   = false;
$member_history   = [];

if ( $is_logged_in ) {
    // a) Fetch member info
    $member_row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tta_members WHERE wpuserid = %d",
            $current_user_id
        ),
        ARRAY_A
    ) ?: [];
    $membership_level = $member_row['membership_level'] ?? 'free';

    // b) Check waitlist membership for this event
    $waitlists = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT userids FROM {$wpdb->prefix}tta_waitlist WHERE event_ute_id = %s",
            $event['ute_id']
        ),
        ARRAY_A
    );
    foreach ( $waitlists as $wl ) {
        $uids = array_filter( array_map( 'intval', explode( ',', $wl['userids'] ) ) );
        if ( in_array( $current_user_id, $uids, true ) ) {
            $is_on_waitlist = true;
            break;
        }
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
$date_str  = date_i18n( get_option( 'date_format' ), $timestamp );
list( $start, $end ) = explode( '|', $event['time'] );
if ( $event['all_day_event'] ) {
    $time_str = esc_html__( 'All day', 'tta' );
} else {
    $time_str = date_i18n( get_option( 'time_format' ), strtotime( $start ) )
              . ' – '
              . date_i18n( get_option( 'time_format' ), strtotime( $end ) );
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

        $cost_sidebar_row = "<li class='tta-event-costmod-class'><strong>Cost: </strong>{$price_str}</li>";
        if ( 'basic' === $membership_level ) {
            $cost_sidebar_row = "<li class='tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic'><strong>Cost: </strong><span>{$price_str}</span> {$price_str_basic}</li>";
        } elseif ( 'premium' === $membership_level ) {
            $cost_sidebar_row = "<li class='tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium'><strong>Cost: </strong><span>{$price_str}</span> {$price_str_premium}</li>";
        }
    } else {
        // Mixed prices
        $cost_sidebar_row = "<li class='tta-event-costmod-class'><strong>Cost: </strong>Various</li>";
    }
} else {
    // Single ticket
    $single     = reset( $tickets );
    $p_base     = floatval( $single['baseeventcost'] );
    $p_basic    = floatval( $single['discountedmembercost'] );
    $p_premium  = floatval( $single['premiummembercost'] );

    $price_str         = $p_base    ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_base,    2 ) ) : esc_html__( 'Free', 'tta' );
    $price_str_basic   = $p_basic   ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_basic,   2 ) ) : esc_html__( 'Free', 'tta' );
    $price_str_premium = $p_premium ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $p_premium, 2 ) ) : esc_html__( 'Free', 'tta' );

    $cost_sidebar_row = "<li class='tta-event-costmod-class'><strong>Cost: </strong>{$price_str}</li>";
    if ( 'basic' === $membership_level ) {
        $cost_sidebar_row = "<li class='tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic'><strong>Cost: </strong><span>{$price_str}</span> {$price_str_basic}</li>";
    } elseif ( 'premium' === $membership_level ) {
        $cost_sidebar_row = "<li class='tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium'><strong>Cost: </strong><span>{$price_str}</span> {$price_str_premium}</li>";
    }
}
?>

<div class="wrap event-page tta-event-page">

  <!-- HERO -->
  <section class="tta-event-hero">
    <div class="tta-event-hero-inner">
      <div class="tta-event-hero-text">
        <h1 class="tta-event-title"><?php echo esc_html( $event['name'] ); ?></h1>
        <div class="tta-event-meta">
          <time class="tta-event-date" datetime="<?php echo esc_attr( date( 'c', $timestamp ) ); ?>">
            <?php echo esc_html( $date_str ); ?>
          </time>
          <span class="tta-event-time"><?php echo esc_html( $time_str ); ?></span>
        </div>
        <a href="#tta-tickets" class="tta-button tta-button-primary">
          <?php esc_html_e( 'Buy Tickets', 'tta' ); ?>
        </a>
      </div>
      <div class="tta-event-hero-image">
        <?php echo $hero_html; ?>
      </div>
    </div>
  </section>

  <!-- MAIN + SIDEBAR -->
  <div class="tta-event-content-wrap">

    <!-- MAIN CONTENT -->
    <main class="tta-event-main">

      <?php if ( $raw_content ) : ?>
        <section class="tta-event-section tta-event-description-accordion">
          <details>
            <summary><?php esc_html_e( 'Event Description', 'tta' ); ?></summary>
            <?php echo apply_filters( 'the_content', $raw_content ); ?>
          </details>
        </section>
      <?php endif; ?>

      <?php if ( ! empty( $event['howitworks'] ) ) : ?>
        <section class="tta-event-section">
          <h2><?php esc_html_e( 'How It Works', 'tta' ); ?></h2>
          <?php echo wpautop( wp_kses_post( $event['howitworks'] ) ); ?>
        </section>
      <?php endif; ?>

      <?php if ( ! empty( $event['whatoexpect'] ) ) : ?>
        <section class="tta-event-section">
          <h2><?php esc_html_e( 'What to Expect', 'tta' ); ?></h2>
          <?php echo wpautop( wp_kses_post( $event['whatoexpect'] ) ); ?>
        </section>
      <?php endif; ?>

      <section class="tta-event-buy">
        <h2><?php esc_html_e( 'Get Your Tickets Now', 'tta' ); ?></h2>

        <?php if ( $tickets ) : ?>
          <?php foreach ( $tickets as $ticket ) : 
            $limit      = intval( $ticket['attendancelimit'] );
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
            <div class="tta-top-indiv-wrapper">
              <div class="tta-ticket-item">
                <?php echo $price_row . ' ' . $avail_text; ?>
              </div>
              <div class="tta-ticket-quantity">
                <span class="tta-ticket-name"><?php echo esc_html( $ticket['ticket_name'] ); ?></span>
                <div>
                  <button type="button" class="tta-qty-decrease" aria-label="<?php esc_attr_e( 'Decrease quantity', 'tta' ); ?>">–</button>
                  <input
                    type="number"
                    name="tta_ticket_qty[<?php echo esc_attr( $ticket['id'] ); ?>]"
                    class="tta-qty-input"
                    value="0"
                    min="0"
                    <?php if ( $available ): ?>max="<?php echo esc_attr( $available ); ?>"<?php endif; ?>
                  />
                  <button type="button" class="tta-qty-increase" aria-label="<?php esc_attr_e( 'Increase quantity', 'tta' ); ?>">+</button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else : ?>
          <p><?php esc_html_e( 'No tickets available for this event.', 'tta' ); ?></p>
        <?php endif; ?>

        <div class="tta-tickets-addtocart-button">
          <button
            type="button"
            id="tta-get-tickets"
            class="tta-button tta-button-primary"
            <?php disabled( empty( $tickets ) || intval( $tickets[0]['attendancelimit'] ) < 1 ); ?>
          >
            <?php esc_html_e( 'Get Tickets', 'tta' ); ?>
          </button>
        </div>
      </section>

    </main>

    <!-- SIDEBAR -->
    <aside class="tta-event-sidebar">
      <div class="tta-event-details">
        <h2><?php esc_html_e( 'Event Details', 'tta' ); ?></h2>
        <ul>
          <li><strong><?php esc_html_e( 'Date', 'tta' ); ?>:</strong> <?php echo esc_html( $date_str ); ?></li>
          <li><strong><?php esc_html_e( 'Time', 'tta' ); ?>:</strong> <?php echo esc_html( $time_str ); ?></li>
          <?php echo $cost_sidebar_row; ?>
          <li><strong><?php esc_html_e( 'Venue', 'tta' ); ?>:</strong> <?php echo esc_html( $event['venuename'] ); ?></li>
          <li>
            <strong><?php esc_html_e( 'Location', 'tta' ); ?>:</strong>
            <a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener">
              <?php echo esc_html( $formatted_address ); ?>
            </a>
          </li>
          <li class="tta-event-map-embed">
            <iframe
              width="100%" height="200" frameborder="0" style="border:0"
              loading="lazy" allowfullscreen
              src="https://maps.google.com/maps?q=<?php echo rawurlencode( $formatted_address ); ?>&output=embed">
            </iframe>
          </li>
        </ul>
      </div>

      <?php if ( ! empty( $event['mapiframe'] ) ) : ?>
        <div class="tta-event-map">
          <?php echo $event['mapiframe']; ?>
        </div>
      <?php endif; ?>
    </aside>

  </div><!-- .tta-event-content-wrap -->

  <!-- OTHER UPCOMING EVENTS -->
  <section class="tta-related-events">
    <h2><?php esc_html_e( 'Other Upcoming Events', 'tta' ); ?></h2>
    <div class="tta-related-events-grid">
      <?php if ( $related ) : ?>
        <?php foreach ( $related as $re ) : 
          $url = get_permalink( $re['page_id'] );
          if ( ! empty( $re['mainimageid'] ) ) {
            $img = wp_get_attachment_image( intval( $re['mainimageid'] ), 'full', false, [
              'class' => 'tta-related-event-img',
              'alt'   => esc_attr( $re['name'] )
            ] );
          } else {
            $default = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
            $img     = '<img src="' . $default . '" alt="' . esc_attr( $re['name'] ) . '" class="tta-related-event-img">';
          }
          list( $rs, ) = explode( '|', $re['time'] );
          $re_ts = strtotime( $re['date'] . ' ' . $rs );
          $dt_iso = date( 'c', $re_ts );
          $dt_disp = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $re_ts );
        ?>
          <a class="tta-related-event" href="<?php echo esc_url( $url ); ?>">
            <div class="thumb"><?php echo $img; ?></div>
            <div class="tta-related-event-info">
              <h3><?php echo esc_html( $re['name'] ); ?></h3>
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
