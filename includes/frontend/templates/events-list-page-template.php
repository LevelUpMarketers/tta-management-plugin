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

$current_year = intval( date_i18n( 'Y' ) );
$year  = isset( $_GET['cal_year'] ) ? intval( $_GET['cal_year'] ) : $current_year;
$month = isset( $_GET['cal_month'] ) ? intval( $_GET['cal_month'] ) : intval( date_i18n( 'n' ) );
$min_year = $current_year - 3;
$max_year = $current_year + 3;
if ( $year < $min_year || $year > $max_year ) {
    $year = $current_year;
}
$month = max( 1, min( 12, $month ) );
$event_days = tta_get_event_days_for_month( $year, $month );
$days_in_month = (int) date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
$first_wday = (int) date( 'w', mktime( 0, 0, 0, $month, 1, $year ) );

$prev_year  = $year;
$prev_month = $month - 1;
if ( $prev_month < 1 ) {
    $prev_month = 12;
    $prev_year--;
}
$next_year  = $year;
$next_month = $month + 1;
if ( $next_month > 12 ) {
    $next_month = 1;
    $next_year++;
}
$prev_allowed = ( $prev_year >= $min_year );
$next_allowed = ( $next_year <= $max_year );
$prev_url = $prev_allowed ? add_query_arg( [ 'cal_year' => $prev_year, 'cal_month' => $prev_month ], get_permalink() ) : '';
$next_url = $next_allowed ? add_query_arg( [ 'cal_year' => $next_year, 'cal_month' => $next_month ], get_permalink() ) : '';
?>
<div class="wrap tta-events-list-page">
<div class="tta-events-columns">
    <aside class="tta-events-left">
        <div class="tta-calendar">
            <div class="tta-cal-nav">
                <?php if ( $prev_allowed ) : ?>
                    <a href="<?php echo esc_url( $prev_url ); ?>">&laquo;</a>
                <?php else : ?>
                    <span class="tta-cal-disabled">&laquo;</span>
                <?php endif; ?>
                <span><?php echo esc_html( date_i18n( 'F Y', mktime( 0, 0, 0, $month, 1, $year ) ) ); ?></span>
                <?php if ( $next_allowed ) : ?>
                    <a href="<?php echo esc_url( $next_url ); ?>">&raquo;</a>
                <?php else : ?>
                    <span class="tta-cal-disabled">&raquo;</span>
                <?php endif; ?>
            </div>
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
                    $has_event = in_array( $d, $event_days, true );
                    $class     = $has_event ? 'tta-cal-day has-event' : 'tta-cal-day';
                    $output    = intval( $d );
                    if ( $has_event ) {
                        $page_id = tta_get_first_event_page_id_for_date( $year, $month, $d );
                        if ( $page_id ) {
                            $url    = get_permalink( $page_id );
                            $output = '<a href="' . esc_url( $url ) . '">' . $output . '</a>';
                        }
                    }
                    echo '<div class="' . esc_attr( $class ) . '">' . $output . '</div>';
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
            <?php $ad = tta_get_random_ad(); ?>
            <?php if ( $ad ) : ?>
                <?php $img = wp_get_attachment_image( intval( $ad['image_id'] ), 'medium' ); ?>
                <?php if ( $ad['url'] ) : ?><a href="<?php echo esc_url( $ad['url'] ); ?>"><?php endif; ?>
                <?php echo $img ? $img : '<img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/ads/placeholder1.svg' ) . '" alt="">'; ?>
                <?php if ( $ad['url'] ) : ?></a><?php endif; ?>
            <?php else : ?>
                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/ads/placeholder1.svg' ); ?>" alt="Ad" />
            <?php endif; ?>
        </div>
    </aside>
</div>
</div>
<?php
get_footer();
