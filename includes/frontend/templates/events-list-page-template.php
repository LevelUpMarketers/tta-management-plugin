<?php
/**
 * Template Name: Events List Page
 *
 * @package TTA
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$paged    = max( 1, get_query_var( 'paged', 1 ) );
$per_page = 5;
$data     = tta_get_upcoming_events( $paged, $per_page );
$events   = $data['events'];
$total    = $data['total'];
$total_pages = $per_page > 0 ? ceil( $total / $per_page ) : 1;

$year  = intval( date_i18n( 'Y' ) );
$month = intval( date_i18n( 'n' ) );
$event_days = tta_get_event_days_for_month( $year, $month );
$days_in_month = (int) date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
$first_wday = (int) date( 'w', mktime( 0, 0, 0, $month, 1, $year ) );
?>
<div class="wrap tta-events-list-page">
<div class="tta-events-columns">
    <aside class="tta-events-left">
        <div class="tta-calendar">
            <div class="tta-cal-header"><?php echo esc_html( date_i18n( 'F Y', mktime( 0, 0, 0, $month, 1, $year ) ) ); ?></div>
            <div class="tta-cal-grid">
                <?php
                $labels = [ 'Su','Mo','Tu','We','Th','Fr','Sa' ];
                foreach ( $labels as $lab ) {
                    echo '<div class="tta-cal-label">' . esc_html( $lab ) . '</div>';
                }
                for ( $i = 0; $i < $first_wday; $i++ ) {
                    echo '<div class="tta-cal-day empty"></div>';
                }
                for ( $d = 1; $d <= $days_in_month; $d++ ) {
                    $class = in_array( $d, $event_days, true ) ? 'tta-cal-day has-event' : 'tta-cal-day';
                    echo '<div class="' . esc_attr( $class ) . '">' . intval( $d ) . '</div>';
                }
                ?>
            </div>
        </div>
    </aside>
    <main class="tta-events-center">
<?php if ( $events ) : ?>
    <ul class="tta-events-list">
    <?php foreach ( $events as $ev ) :
        $page_url = get_permalink( $ev['page_id'] );
        if ( ! empty( $ev['mainimageid'] ) ) {
            $img_html = wp_get_attachment_image( intval( $ev['mainimageid'] ), 'medium' );
        } else {
            $default  = esc_url( TTA_PLUGIN_URL . 'assets/images/admin/default-event.png' );
            $img_html = '<img src="' . $default . '" alt="" class="attachment-medium size-medium" />';
        }
        $ts        = strtotime( $ev['date'] );
        $date_str  = date_i18n( 'm-d-Y', $ts );
        list( $start, $end ) = explode( '|', $ev['time'] );
        $time_str = $ev['all_day_event'] ? esc_html__( 'All day', 'tta' ) : date_i18n( get_option( 'time_format' ), strtotime( $start ) ) . ' – ' . date_i18n( get_option( 'time_format' ), strtotime( $end ) );
        $address   = tta_format_address( $ev['address'] );
        $content   = get_post_field( 'post_content', $ev['page_id'] );
        $excerpt   = wp_trim_words( wp_strip_all_tags( $content ), 25, '…' );
        $remaining = tta_get_remaining_ticket_count( $ev['ute_id'] );
    ?>
        <li class="tta-event-list-item">
            <a href="<?php echo esc_url( $page_url ); ?>">
                <div class="tta-event-thumb"><?php echo $img_html; ?></div>
                <div class="tta-event-summary">
                    <h2 class="tta-event-name"><?php echo esc_html( $ev['name'] ); ?></h2>
                    <p class="tta-event-datetime"><?php echo esc_html( $date_str . ' ' . $time_str ); ?></p>
                    <?php if ( $ev['venuename'] ) : ?>
                        <p class="tta-event-venue"><?php echo esc_html( $ev['venuename'] ); ?></p>
                    <?php endif; ?>
                    <p class="tta-event-address"><?php echo esc_html( $address ); ?></p>
                    <p class="tta-event-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                    <p class="tta-event-remaining">
                        <?php printf( esc_html__( '%d tickets remaining', 'tta' ), $remaining ); ?>
                    </p>
                    <span class="tta-event-link"><?php esc_html_e( 'Get Your Tickets', 'tta' ); ?></span>
                </div>
            </a>
        </li>
    <?php endforeach; ?>
    </ul>

    <?php if ( $total_pages > 1 ) : ?>
    <div class="tta-events-pagination">
        <?php
        echo paginate_links( [
            'current' => $paged,
            'total'   => $total_pages,
        ] );
        ?>
    </div>
    <?php endif; ?>
<?php else : ?>
    <p><?php esc_html_e( 'No upcoming events found.', 'tta' ); ?></p>
<?php endif; ?>
    </main>
    <aside class="tta-events-right">
        <div class="tta-events-ad">
            <img src="https://via.placeholder.com/300x250?text=Ad" alt="Advertisement" />
        </div>
    </aside>
</div>
</div>
<?php
get_footer();
