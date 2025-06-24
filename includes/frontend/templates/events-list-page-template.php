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
?>
<div class="wrap tta-events-list-page">
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
</div>
<?php
get_footer();
