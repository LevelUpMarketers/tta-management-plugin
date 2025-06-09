<?php
/**
 * Template Name: Event Page
 */

get_header();

global $wpdb, $post;

// 1) Grab the page’s ID and lookup the event
$page_id = $post->ID;

$event = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}tta_events WHERE page_id = %d",
        $page_id
    ),
    ARRAY_A
);

// 2) If no event found, bail with a message
if ( ! $event ) {
    echo '<div class="wrap"><h1>Event not found.</h1><p>Sorry, this event does not exist.</p></div>';
    get_footer();
    exit;
}

// 3) Otherwise render your event details
?>
<div class="wrap event-page">
    <h1><?php echo esc_html( $event['name'] ); ?></h1>

    <p><strong>Description:</strong>
        <?php
        // Using get_post() and accessing post_content directly
        $post = get_post( $event['page_id'] );
        if ( $post ) {
            // If you want the raw content:
            $raw_content = $post->post_content;
            // If you want it filtered just like in the loop (shortcodes, embeds, etc.):
            $content = apply_filters( 'the_content', $raw_content );
            echo $content;
        }
        ?>
    </p>

    <p><strong>Date:</strong>
        <?php echo esc_html( date_i18n( 'F j, Y', strtotime( $event['date'] ) ) ); ?>
    </p>

    <p><strong>Time:</strong>
        <?php
        list( $start, $end ) = explode( '|', $event['time'] );
        if ( $event['all_day_event'] ) {
            echo 'All day';
        } else {
            echo esc_html( $start ) . ' – ' . esc_html( $end );
        }
        ?>
    </p>

    <p><strong>Location:</strong>
        <?php echo esc_html( $event['address'] ); ?>
    </p>

    <?php if ( $event['venueurl'] ) : ?>
    <p><strong>Venue Link:</strong>
        <a href="<?php echo esc_url( $event['venueurl'] ); ?>" target="_blank">
            Visit Venue Site
        </a>
    </p>
    <?php endif; ?>

    <p><strong>Cost:</strong>
        <?php
        if ( 'free' === $event['type'] ) {
            echo 'Free';
        } else {
            echo '$' . number_format_i18n( $event['baseeventcost'], 2 );
            if ( 'memberonly' === $event['type'] ) {
                echo ' <em>(Members only)</em>';
            }
        }
        ?>
    </p>

    <?php if ( $event['discountedmembercost'] > 0 ) : ?>
    <p>
        <strong>Member Discounted Cost:</strong>
        $<?php echo number_format_i18n( $event['discountedmembercost'], 2 ); ?>
    </p>
    <?php endif; ?>

    <?php if ( $event['discountcode'] ) : ?>
    <p>
        <strong>Discount Code:</strong>
        <?php echo esc_html( $event['discountcode'] ); ?>
    </p>
    <?php endif; ?>

    <!-- Add any extra URLs -->
    <?php for ( $i = 2; $i <= 4; $i++ ) :
        if ( $url = $event["url{$i}"] ) : ?>
        <p>
            <strong>More Info <?php echo $i - 1; ?>:</strong>
            <a href="<?php echo esc_url( $url ); ?>" target="_blank">
                <?php echo esc_html( $url ); ?>
            </a>
        </p>
    <?php
        endif;
    endfor; ?>

    <?php
    // 4) You can now insert your “Buy Tickets” shortcode or button here,
    //    using the $event['ticket_id'], $event['waitlist_id'], etc.
    ?>

</div>

<?php
get_footer();
