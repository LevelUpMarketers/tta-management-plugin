<?php
/**
 * Template Name: Event Page
 *
 * @package TTA
 */

get_header();

global $wpdb, $post;

// 1) Grab the page’s ID and lookup the event
$page_id = $post->ID;
$table   = $wpdb->prefix . 'tta_events';

$event = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$table} WHERE page_id = %d",
        $page_id
    ),
    ARRAY_A
);

// 2) If no event found, bail
if ( ! $event ) {
    echo '<div class="wrap"><h1>' . esc_html__( 'Event not found.', 'tta' ) . '</h1>'
       . '<p>' . esc_html__( 'Sorry, this event does not exist.', 'tta' ) . '</p></div>';
    get_footer();
    exit;
}

// 2a) Parse & format the raw DB address (splitting on hyphen or en‐dash)
$raw_address = $event['address'];
$parts = preg_split( '/\s*[-–]\s*/u', $raw_address );
$street = trim( $parts[0] ?? '' );
$addr2  = trim( $parts[1] ?? '' );
$city   = trim( $parts[2] ?? '' );
$state  = trim( $parts[3] ?? '' );
$zip    = trim( $parts[4] ?? '' );

// If there’s an “address 2”, stitch it onto the street line
$street_full = $street . ( $addr2 ? ' ' . $addr2 : '' );

// City, State ZIP
$city_state_zip = $city . ( $state || $zip ? ', ' : '' ) 
                . $state 
                . ( $zip ? ' ' . $zip : '' );

// Final formatted string: “Street [Suite A] – City, State ZIP”
$formatted_address = $street_full 
                   . ' – ' 
                   . $city_state_zip;

// Build Google Maps link
$map_query = rawurlencode( "{$street_full}, {$city_state_zip}" );
$map_url   = "https://www.google.com/maps/search/?api=1&query={$map_query}";

// 3) Format date/time and price
$timestamp   = strtotime( $event['date'] );
$date_str    = date_i18n( get_option( 'date_format' ), $timestamp );

list( $start, $end ) = explode( '|', $event['time'] );
if ( $event['all_day_event'] ) {
    $time_str = esc_html__( 'All day', 'tta' );
    $end_time = $timestamp + DAY_IN_SECONDS;
} else {
    $start_str = date_i18n( get_option( 'time_format' ), strtotime( $start ) );
    $end_str   = date_i18n( get_option( 'time_format' ), strtotime( $end ) );
    $time_str  = esc_html( "{$start_str} – {$end_str}" );
    $end_time  = strtotime( $event['date'] . ' ' . $end );
}

$price_float = floatval( $event['baseeventcost'] );
$price_str   = $price_float
    ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $price_float, 2 ) )
    : esc_html__( 'Free', 'tta' );

$price_float_basic = floatval( $event['discountedmembercost'] );
$price_str_basic   = $price_float_basic
    ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $price_float_basic, 2 ) )
    : esc_html__( 'Free', 'tta' );

$price_float_premium = floatval( $event['premiummembercost'] );
$price_str_premium   = $price_float_premium
    ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $price_float_premium, 2 ) )
    : esc_html__( 'Free', 'tta' );

// 4) Hero image (fallback)
if ( ! empty( $event['mainimageid'] ) ) {
    $hero_html = wp_get_attachment_image( intval( $event['mainimageid'] ), 'full', false, [
        'class' => 'tta-event-hero-img',
        'alt'   => esc_attr( $event['name'] ),
    ] );
    $image_url = wp_get_attachment_url( intval( $event['mainimageid'] ) );
} else {
    $placeholder = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
    $hero_html   = '<img src="' . $placeholder . '" alt="" class="tta-event-hero-img">';
    $image_url   = $placeholder;
}

// 5) Raw page content for description
$page_post   = get_post( $page_id );
$raw_content = $page_post ? $page_post->post_content : '';




// 1) Figure out current user’s membership level (default to “free”)
$current_user_id = get_current_user_id();
if ( $current_user_id ) {
    $member = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT membership_level FROM {$wpdb->prefix}tta_members WHERE wpuserid = %d",
            $current_user_id
        ),
        ARRAY_A
    );
    $membership_level = $member['membership_level'] ?? 'free';
} else {
    $membership_level = 'free';
}

// 2) Start with the default cost row
$cost_sidebar_row = "<li class='tta-event-costmod-class'><strong>Cost: </strong>" .  $price_str . "</li>";

// 3) Modify for basic vs. premium members
if ( 'basic' === $membership_level ) {
    $cost_sidebar_row = "<li class='tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic'><strong>Cost: </strong><span>" .  $price_str . "</span> " . $price_str_basic . "</li>";
}
elseif ( 'premium' === $membership_level ) {
    $cost_sidebar_row = "<li class='tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium'><strong>Cost: </strong><span>" .  $price_str . "</span> " . $price_str_premium . "</li>";
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
          <span class="tta-event-time"><?php echo $time_str; ?></span>
          <span class="tta-event-price"><?php echo esc_html( $price_str ); ?></span>
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

      <?php
      // —————————————————————————————————————————————————————————
      // Real ticket availability & pricing
      // —————————————————————————————————————————————————————————

      // 1) Fetch this event’s ticket record
      $tickets_table = $wpdb->prefix . 'tta_tickets';
      $ticket = $wpdb->get_row(
          $wpdb->prepare(
              "SELECT attendancelimit FROM {$tickets_table} WHERE event_ute_id = %s",
              $event['ute_id']
          ),
          ARRAY_A
      );

      // 2) Determine availability
      $limit      = isset( $ticket['attendancelimit'] ) ? intval( $ticket['attendancelimit'] ) : 0;
      $available  = $limit > 0 ? $limit : 0;
      $avail_text = $available > 0
          ? sprintf( esc_html__( 'Only %d Available', 'tta' ), $available )
          : esc_html__( 'Sold Out', 'tta' );

      // 3) Choose price based on membership level
      if ( 'basic' === $membership_level ) {
          $ticket_price = $price_str_basic;
      } elseif ( 'premium' === $membership_level ) {
          $ticket_price = $price_str_premium;
      } else {
          $ticket_price = $price_str;
      }

      // 2) Start with the default cost row
      $cost_bottomticket_row = "<span class='tta-ticket-price tta-event-costmod-class'><strong>Cost: </strong>" .  $price_str . "</span>";

      // 3) Modify for basic vs. premium members
      if ( 'basic' === $membership_level ) {
          $cost_bottomticket_row = "<span class='tta-ticket-price tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic'><strong>Cost: </strong><span>" .  $price_str . "</span> " . $price_str_basic . "</span>";
      }
      elseif ( 'premium' === $membership_level ) {
          $cost_bottomticket_row = "<span class='tta-ticket-price tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium'><strong>Cost: </strong><span>" .  $price_str . "</span> " . $price_str_premium . "</span>";
      }
      ?>

      <section class="tta-event-buy">
        <h2><?php esc_html_e( 'Get Your Tickets Now', 'tta' ); ?></h2>
        <div class="tta-ticket-item">
          <span class="tta-ticket-name"><?php echo esc_html( $event['name'] ); ?></span>
          <span class="tta-ticket-price"><?php echo $cost_bottomticket_row; echo esc_html( $avail_text ); ?></span>
        </div>
        <div class="tta-ticket-quantity">
          <button type="button" class="tta-qty-decrease" aria-label="<?php esc_attr_e( 'Decrease quantity', 'tta' ); ?>">–</button>
          <input
            type="number"
            name="tta_ticket_qty"
            class="tta-qty-input"
            value="0"
            min="0"
            <?php if ( $available ): ?>max="<?php echo $available; ?>"<?php endif; ?>
          />
          <button type="button" class="tta-qty-increase" aria-label="<?php esc_attr_e( 'Increase quantity', 'tta' ); ?>">+</button>
        </div>
        <button
          type="button"
          id="tta-get-tickets"
          class="tta-button tta-button-primary"
          <?php disabled( $available === 0 ); ?>
        >
          <?php esc_html_e( 'Get Tickets', 'tta' ); ?>
        </button>
      </section>

    </main>

    <!-- SIDEBAR -->
    <aside class="tta-event-sidebar">
      <div class="tta-event-details">
        <h2><?php esc_html_e( 'Event Details', 'tta' ); ?></h2>
        <ul>
          <li>
            <strong><?php esc_html_e( 'Date', 'tta' ); ?>:</strong>
            <?php echo esc_html( $date_str ); ?>
          </li>
          <li>
            <strong><?php esc_html_e( 'Time', 'tta' ); ?>:</strong>
            <?php echo $time_str; ?>
          </li>
          <?php echo $cost_sidebar_row; ?>
          <li>
            <strong><?php esc_html_e( 'Venue', 'tta' ); ?>:</strong>
            <?php echo esc_html( $event['venuename'] ); ?>
          </li>
          <li>
            <strong><?php esc_html_e( 'Location', 'tta' ); ?>:</strong>
            <a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener">
              <?php echo esc_html( $formatted_address ); ?>
            </a>
          </li>
          <?php if ( $formatted_address ) : ?>
          <li class="tta-event-map-embed">
            <iframe
              width="100%"
              height="200"
              frameborder="0"
              style="border:0"
              loading="lazy"
              allowfullscreen
              src="https://maps.google.com/maps?q=<?php echo rawurlencode( $formatted_address ); ?>&output=embed">
            </iframe>
          </li>
        <?php endif; ?>
        </ul>
      </div>

      <?php if ( ! empty( $event['mapiframe'] ) ) : ?>
        <div class="tta-event-map">
          <?php echo $event['mapiframe']; ?>
        </div>
      <?php endif; ?>
    </aside>

  </div><!-- .tta-event-content-wrap -->

  <!-- TICKETS -->
  <section id="tta-tickets" class="tta-event-tickets-wrap">

  </section>

  <!-- OTHER UPCOMING EVENTS -->
  <section class="tta-related-events">
    <h2><?php esc_html_e( 'Other Upcoming Events', 'tta' ); ?></h2>
    <div class="tta-related-events-grid">
      <?php
      $related = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT * FROM {$table}
           WHERE page_id != %d
             AND date >= CURDATE()
           ORDER BY date ASC
           LIMIT 4",
          $page_id
        ),
        ARRAY_A
      );

      if ( $related ) :
        foreach ( $related as $re ) :
          $url = get_permalink( $re['page_id'] );

          // full-size image or default fallback
          if ( ! empty( $re['mainimageid'] ) ) {
            $img = wp_get_attachment_image(
              intval( $re['mainimageid'] ),
              'full',
              false,
              [ 'class' => 'tta-related-event-img', 'alt' => esc_attr( $re['name'] ) ]
            );
          } else {
            $default = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
            $img     = '<img src="' . $default . '" alt="' . esc_attr( $re['name'] ) . '" class="tta-related-event-img">';
          }

          list( $rs, ) = explode( '|', $re['time'] );
          $re_ts   = strtotime( $re['date'] . ' ' . $rs );
          $dt_iso  = date( 'c', $re_ts );
          $dt_disp = date_i18n(
            get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
            $re_ts
          );
      ?>
        <a class="tta-related-event" href="<?php echo esc_url( $url ); ?>">
          <div class="thumb"><?php echo $img; ?></div>
          <div class="tta-related-event-info">
            <h3><?php echo esc_html( $re['name'] ); ?></h3>
            <time datetime="<?php echo esc_attr( $dt_iso ); ?>">
              <?php echo esc_html( $dt_disp ); ?>
            </time>
          </div>
        </a>
      <?php
        endforeach;
      else :
        echo '<p>' . esc_html__( 'No related events found.', 'tta' ) . '</p>';
      endif;
      ?>
    </div>
  </section>


</div><!-- .tta-event-page -->

<?php
get_footer();
