<?php
/**
 * Template Name: Event Page
 *
 * @package TTA
 */

// Initialize cart early so sessions start before output
$cart = new TTA_Cart();

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
        "SELECT id, ticket_name, ticketlimit, baseeventcost, discountedmembercost, premiummembercost
         FROM {$tickets_table}
         WHERE event_ute_id = %s
         ORDER BY id ASC",
        $event['ute_id']
    ),
    ARRAY_A
);
$ticket_count = count( $tickets );

// Build a map of quantities for this event from the cart
$cart_quantities = [];
foreach ( $cart->get_items() as $it ) {
    if ( isset( $it['event_ute_id'] ) && $it['event_ute_id'] === $event['ute_id'] ) {
        $cart_quantities[ intval( $it['ticket_id'] ) ] = intval( $it['quantity'] );
    }
}

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
} else {
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

  }
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
      </div>

      <a href="#tta-event-buy" class="tta-button tta-button-primary">
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
          <div class="tta-accordion">
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

      

      <section id="tta-event-buy" class="tta-event-buy">
        <h2><?php esc_html_e( 'Get Your Tickets Now', 'tta' ); ?></h2>

        <?php if ( $tickets ) : ?>
          <?php foreach ( $tickets as $ticket ) :
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
                    value="<?php echo esc_attr( $cart_quantities[ $ticket['id'] ] ?? 0 ); ?>"
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
            <?php disabled( empty( $tickets ) || intval( $tickets[0]['ticketlimit'] ) < 1 ); ?>
          >
            <?php esc_html_e( 'Get Tickets', 'tta' ); ?>
          </button>
        </div>
      </section>

      <?php
      // Grab & sanitize gallery IDs
      $other_ids = ! empty( $event['otherimageids'] )
          ? array_filter( array_map( 'intval', explode( ',', $event['otherimageids'] ) ) )
          : [];

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
                      // WP will crop/scale as needed
                      echo wp_get_attachment_image( $img_id, 'medium_large' );
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

        <section class="tta-event-section tta-event-image-gallery-accordion tta-event-attendees-section">
          <div class="tta-accordion">
            <div class="tta-accordion-content">
              <h2><?php esc_html_e( 'Attendees', 'tta' ); ?></h2>
              <div class="tta-gallery-grid">
                <?php foreach ( $other_ids as $img_id ) : ?>
                  <div class="tta-gallery-item">
                    <?php
                      // use a medium-large size for good resolution;
                      // WP will crop/scale as needed
                      echo wp_get_attachment_image( $img_id, 'medium_large' );
                    ?>
                  </div>
                <?php endforeach; ?>
                <?php foreach ( $other_ids as $img_id ) : ?>
                  <div class="tta-gallery-item">
                    <?php
                      // use a medium-large size for good resolution;
                      // WP will crop/scale as needed
                      echo wp_get_attachment_image( $img_id, 'medium_large' );
                    ?>
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

              $host = parse_url( $url, PHP_URL_HOST );
              $service_key = 'external-link.svg'; // default icon

              if ( $host ) {
                  $host = strtolower( $host );
                  foreach ( $icon_map as $domain => $icon_file ) {
                      if ( false !== strpos( $host, $domain ) ) {
                          $service_key = $icon_file;
                          break;
                      }
                  }
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
