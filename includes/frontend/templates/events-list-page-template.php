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

$friend_imgs = [];
foreach ( $events as $ev ) {
    $profiles = tta_get_event_attendee_profiles( $ev['id'] );
    foreach ( $profiles as $p ) {
        $friend_imgs[] = intval( $p['img_id'] );
    }
}

$context = tta_get_current_user_context();
$member_events = [];
if ( $context['is_logged_in'] ) {
    $member_events = array_slice( tta_get_member_upcoming_events( $context['wp_user_id'] ), 0, 5 );
}

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
$prev_url = $prev_allowed ? add_query_arg( [ 'cal_year' => $prev_year, 'cal_month' => $prev_month, 'paged' => $paged ], get_permalink() ) : '';
$next_url = $next_allowed ? add_query_arg( [ 'cal_year' => $next_year, 'cal_month' => $next_month, 'paged' => $paged ], get_permalink() ) : '';
?>
<div class="wrap tta-events-list-page">
<div class="tta-events-columns">
    <aside class="tta-events-left">
        <div class="tta-calendar" data-year="<?php echo esc_attr( $year ); ?>" data-month="<?php echo esc_attr( $month ); ?>" data-min-year="<?php echo esc_attr( $min_year ); ?>" data-max-year="<?php echo esc_attr( $max_year ); ?>">
            <div class="tta-cal-nav">
                <?php if ( $prev_allowed ) : ?>
                    <a href="<?php echo esc_url( $prev_url ); ?>" class="tta-cal-prev">&laquo;</a>
                <?php else : ?>
                    <span class="tta-cal-prev tta-cal-disabled">&laquo;</span>
                <?php endif; ?>
                <span class="tta-cal-current"><?php echo esc_html( date_i18n( 'F Y', mktime( 0, 0, 0, $month, 1, $year ) ) ); ?></span>
                <?php if ( $next_allowed ) : ?>
                    <a href="<?php echo esc_url( $next_url ); ?>" class="tta-cal-next">&raquo;</a>
                <?php else : ?>
                    <span class="tta-cal-next tta-cal-disabled">&raquo;</span>
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

        <div class="tta-member-events">
            <h2><?php esc_html_e( 'Your Upcoming Events', 'tta' ); ?></h2>
            <?php if ( $context['is_logged_in'] ) : ?>
                <?php if ( $member_events ) : ?>
                    <ul>
                        <?php foreach ( $member_events as $mev ) : ?>
                            <li><a href="<?php echo esc_url( get_permalink( $mev['page_id'] ) ); ?>"><?php echo esc_html( $mev['name'] ); ?></a> <span class="tta-member-date"><?php echo esc_html( date_i18n( 'M j', strtotime( $mev['date'] ) ) ); ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e( 'You have no upcoming events.', 'tta' ); ?></p>
                <?php endif; ?>
            <?php else : ?>
                <p><?php esc_html_e( 'Log in to see your upcoming events.', 'tta' ); ?></p>
                <div class="login-wrap">
                    <?php wp_login_form( [ 'echo' => true ] ); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $friend_imgs ) : ?>
        <div class="tta-join-friends">
            <h2><?php esc_html_e( 'Join Your Friends', 'tta' ); ?></h2>
            <p class="tta-join-sub"><?php esc_html_e( 'Members attending upcoming events', 'tta' ); ?></p>
            <div class="tta-friend-grid">
                <?php foreach ( $friend_imgs as $img_id ) : ?>
                    <?php if ( $img_id ) : ?>
                        <?php
                        $thumb = wp_get_attachment_image( $img_id, 'thumbnail', false, [
                            'class'     => 'tta-friend-thumb tta-popup-img',
                            'data-full' => wp_get_attachment_image_url( $img_id, 'large' ),
                        ] );
                        echo $thumb; ?>
                    <?php else : ?>
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ); ?>" class="tta-friend-thumb tta-popup-img" data-full="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ); ?>" alt="">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="tta-membership-perks">
            <h2><?php esc_html_e( 'Membership Perks', 'tta' ); ?></h2>
            <?php
            $become_url = home_url( '/become-a-member' );
            if ( ! $context['is_logged_in'] || 'free' === $context['membership_level'] ) :
                ?>
                <a href="<?php echo esc_url( $become_url ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/ads/placeholder1.svg' ); ?>" alt="Become a Member">
                </a>
                <p><?php esc_html_e( 'Become a member today to unlock discounts and exclusive events!', 'tta' ); ?></p>
            <?php elseif ( 'basic' === $context['membership_level'] ) : ?>
                <p><?php esc_html_e( 'Thanks for being a member! Upgrade to Premium for even more perks.', 'tta' ); ?></p>
                <a href="<?php echo esc_url( $become_url ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/ads/placeholder2.svg' ); ?>" alt="Upgrade">
                </a>
            <?php else : ?>
                <p><?php esc_html_e( 'Thanks for being a Premium Member! Don\'t forget about our referral program for extra rewards.', 'tta' ); ?></p>
            <?php endif; ?>
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
        $ts       = strtotime( $ev['date'] );
        $date_str = date_i18n( 'm-d-Y', $ts );
        $parts    = array_pad( explode( '|', $ev['time'] ), 2, '' );
        $start    = $parts[0];
        $end      = $parts[1];
        if ( $ev['all_day_event'] ) {
            $time_str = esc_html__( 'All day', 'tta' );
        } else {
            $start_fmt = $start ? date_i18n( get_option( 'time_format' ), strtotime( $start ) ) : '';
            $end_fmt   = $end ? date_i18n( get_option( 'time_format' ), strtotime( $end ) ) : '';
            $time_str  = trim( $start_fmt . ( $end_fmt ? ' – ' . $end_fmt : '' ) );
        }
        $address   = tta_format_address( $ev['address'] );
        $content   = get_post_field( 'post_content', $ev['page_id'] );
        $excerpt   = wp_trim_words( wp_strip_all_tags( $content ), 25, '…' );
        $remaining = tta_get_remaining_ticket_count( $ev['ute_id'] );
        $cost      = floatval( $ev['baseeventcost'] ?? 0 );
        $cost_str  = $cost ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $cost, 2 ) ) : esc_html__( 'Free', 'tta' );
        $type_map  = [
            'free'       => __( 'Open Event', 'tta' ),
            'paid'       => __( 'Basic Membership Required', 'tta' ),
            'memberonly' => __( 'Premium Membership Required', 'tta' ),
        ];
        $event_type = $type_map[ $ev['type'] ?? '' ] ?? '';
        $maps_url   = 'https://www.google.com/maps/search/?api=1&query=' . urlencode( $address );
    ?>
        <li class="tta-event-list-item">
            <a href="<?php echo esc_url( $page_url ); ?>">
                <div class="tta-event-thumb"><?php echo $img_html; ?></div>
                <div class="tta-event-summary">
                    <h2 class="tta-event-name"><?php echo esc_html( $ev['name'] ); ?></h2>
                    <ul class="tta-event-detail-list">
                        <li>
                            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/calendar.svg' ); ?>" alt="">
                            <div class="tta-event-details-icon-after"><strong><?php esc_html_e( 'Date:', 'tta' ); ?></strong> <?php echo esc_html( $date_str ); ?></div>
                        </li>
                        <li>
                            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/clock.svg' ); ?>" alt="">
                            <div class="tta-event-details-icon-after"><strong><?php esc_html_e( 'Time:', 'tta' ); ?></strong> <?php echo esc_html( $time_str ); ?></div>
                        </li>
                        <li>
                            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/money.svg' ); ?>" alt="">
                            <div class="tta-event-details-icon-after"><strong><?php esc_html_e( 'Cost:', 'tta' ); ?></strong> <?php echo esc_html( $cost_str ); ?></div>
                        </li>
                        <?php if ( $event_type ) : ?>
                        <li>
                            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/memberlevel.svg' ); ?>" alt="">
                            <div class="tta-event-details-icon-after"><strong><?php esc_html_e( 'Event Type:', 'tta' ); ?></strong> <?php echo esc_html( $event_type ); ?></div>
                        </li>
                        <?php endif; ?>
                        <?php if ( $ev['venuename'] ) : ?>
                        <li>
                            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/store.svg' ); ?>" alt="">
                            <div class="tta-event-details-icon-after"><strong><?php esc_html_e( 'Venue:', 'tta' ); ?></strong> <?php echo esc_html( $ev['venuename'] ); ?></div>
                        </li>
                        <?php endif; ?>
                        <li>
                            <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/location.svg' ); ?>" alt="">
                            <div class="tta-event-details-icon-after"><strong><?php esc_html_e( 'Location:', 'tta' ); ?></strong> <a href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $address ); ?></a></div>
                        </li>
                    </ul>
                    <p class="tta-event-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                    <p class="tta-event-remaining"><?php printf( esc_html__( '%d tickets remaining', 'tta' ), $remaining ); ?></p>
                    <span class="tta-event-link"><?php esc_html_e( 'Get Your Tickets', 'tta' ); ?></span>
                </div>
            </a>
        </li>
    <?php endforeach; ?>
    </ul>

    <?php if ( $total_pages > 1 ) : ?>
        <?php
        $links = paginate_links( [
            'current'   => $paged,
            'total'     => $total_pages,
            'mid_size'  => 2,
            'prev_text' => __( 'Previous Events', 'tta' ),
            'next_text' => __( 'More Events', 'tta' ),
            'type'      => 'array',
        ] );

        if ( $links ) :
            $prev  = '';
            $next  = '';
            $pages = [];
            foreach ( $links as $link ) {
                if ( false !== strpos( $link, 'prev page-numbers' ) ) {
                    $prev = $link;
                } elseif ( false !== strpos( $link, 'next page-numbers' ) ) {
                    $next = $link;
                } else {
                    $pages[] = $link;
                }
            }
            ?>
            <div class="tta-events-pagination">
                <?php if ( $prev ) : ?>
                    <div class="tta-pagination-prev"><?php echo $prev; ?></div>
                <?php endif; ?>
                <div class="tta-pagination-pages"><?php echo implode( ' ', $pages ); ?></div>
                <?php if ( $next ) : ?>
                    <div class="tta-pagination-next"><?php echo $next; ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
