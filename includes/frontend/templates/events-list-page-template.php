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

$header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">EVENTS</p>[/vc_column_text][/vc_column][/vc_row]';
echo do_shortcode( $header_shortcode );

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
$seen        = [];
foreach ( $events as $ev ) {
    $profiles = tta_get_event_attendee_profiles( $ev['id'] );
    foreach ( $profiles as $p ) {
        $key = strtolower( trim( $p['first_name'] . ' ' . $p['last_name'] ) . '|' . $p['img_id'] );
        if ( isset( $seen[ $key ] ) ) {
            continue;
        }
        $seen[ $key ] = true;
        $friend_imgs[] = [
            'img_id' => intval( $p['img_id'] ),
            'name'   => trim( $p['first_name'] . ' ' . $p['last_name'] ),
        ];
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
                             <li><a href="<?php echo esc_url( get_permalink( $mev['page_id'] ) ); ?>"><?php echo esc_html( $mev['name'] ); ?></a> <span class="tta-member-date">&nbsp;- <?php echo esc_html( date_i18n( 'F j', strtotime( $mev['date'] ) ) ); ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e( 'You have no upcoming events.', 'tta' ); ?></p>
                <?php endif; ?>
            <?php else : ?>
                <p><?php echo wp_kses_post( sprintf( __( 'Log in to see your upcoming events. Not a member yet? <a href="%s">Click here to join now!</a>', 'tta' ), esc_url( home_url( '/become-a-member' ) ) ) ); ?></p>
                <div class="login-wrap">
                    <?php wp_login_form( [ 'echo' => true ] ); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $friend_imgs ) : ?>
        <div class="tta-join-friends tta-event-image-gallery-accordion">
            <div class="tta-accordion">
                <?php $friend_count = count( $friend_imgs ); ?>
                <div class="tta-accordion-content<?php echo ( $friend_count <= 12 ) ? ' tta-auto-height' : ''; ?>">
                    <h2><?php esc_html_e( 'Join Your Friends', 'tta' ); ?></h2>
                    <p class="tta-join-sub"><?php esc_html_e( 'Members attending upcoming events', 'tta' ); ?></p>
                    <div class="tta-friend-grid">
                        <?php $fi = 0; foreach ( $friend_imgs as $f ) : ?>
                            <?php $extra = $fi >= 12 ? ' tta-extra-friend' : ''; ?>
                            <?php if ( $f['img_id'] ) : ?>
                                <?php
                                $thumb = wp_get_attachment_image( $f['img_id'], 'thumbnail', false, [
                                    'class'     => 'tta-friend-thumb tta-popup-img' . $extra,
                                    'data-full' => wp_get_attachment_image_url( $f['img_id'], 'large' ),
                                    'alt'       => esc_attr( $f['name'] )
                                ] );
                                echo $thumb; ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ); ?>" class="tta-friend-thumb tta-popup-img<?php echo esc_attr( $extra ); ?>" data-full="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ); ?>" alt="<?php echo esc_attr( $f['name'] ); ?>">
                            <?php endif; ?>
                        <?php $fi++; endforeach; ?>
                    </div>
                </div>
                <?php if ( $friend_count > 12 ) : ?>
                    <button type="button" class="tta-button tta-button-primary tta-accordion-toggle-image-gallery"><?php esc_html_e( 'View All Attendees', 'tta' ); ?></button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <section class="tta-event-section tta-your-events">
            <h2 class="tta-eventpage-sidebar-heading"><?php esc_html_e( 'Your Profile', 'tta' ); ?></h2>
            <ul class="tta-your-events-list">
            <?php if ( ! $context['is_logged_in'] ) : ?>
                <li>
                    <img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/login.svg' ); ?>" alt="<?php esc_attr_e( 'Login', 'tta' ); ?>">
                    <div class="tta-event-details-icon-after"><a href="#loginform" class="tta-scroll-login"><?php esc_html_e( 'Login to see info about your events', 'tta' ); ?></a></div>
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

        <div class="tta-membership-perks">
            <h2><?php esc_html_e( 'Membership Perks', 'tta' ); ?></h2>
            <?php
            $become_url = home_url( '/become-a-member' );
            if ( ! $context['is_logged_in'] || 'free' === $context['membership_level'] ) :
                ?>
                <a href="<?php echo esc_url( $become_url ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/ads/NotLoggedInMembershipAdvert.png' ); ?>" alt="Become a Member">
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
            $img_url = wp_get_attachment_image_url( intval( $ev['mainimageid'] ), 'medium' );
        } else {
            $img_url = TTA_PLUGIN_URL . 'assets/images/admin/default-event.png';
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
        $has_waitlist = ( '1' === (string) ( $ev['waitlistavailable'] ?? '0' ) );

        $cost_range = tta_get_ticket_cost_range( $ev['ute_id'] );
        $base_min   = floatval( $cost_range['base_min'] );
        $base_max   = floatval( $cost_range['base_max'] );
        $basic_min  = floatval( $cost_range['basic_min'] );
        $basic_max  = floatval( $cost_range['basic_max'] );
        $premium_min = floatval( $cost_range['premium_min'] );
        $premium_max = floatval( $cost_range['premium_max'] );

        $format_cost = static function( $min, $max ) {
            if ( 0 === $min && 0 === $max ) {
                return __( 'Free', 'tta' );
            }
            $min_str = sprintf( __( '$%s', 'tta' ), number_format_i18n( $min, 2 ) );
            if ( $min === $max ) {
                return $min_str;
            }
            $max_str = sprintf( __( '$%s', 'tta' ), number_format_i18n( $max, 2 ) );
            return $min_str . ' - ' . $max_str;
        };

        $base_str    = $format_cost( $base_min, $base_max );
        $basic_str   = $format_cost( $basic_min, $basic_max );
        $premium_str = $format_cost( $premium_min, $premium_max );

        if ( $context['is_logged_in'] ) {
            if ( 'basic' === $context['membership_level'] && ( $basic_min !== $base_min || $basic_max !== $base_max ) ) {
                $cost_html = sprintf(
                    "<span class='tta-ticket-price tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-basic'><strong>%s</strong> <span class='tta-price-strike'>%s</span> %s</span>",
                    esc_html__( 'Cost:', 'tta' ),
                    esc_html( $base_str ),
                    esc_html( $basic_str )
                );
            } elseif ( 'premium' === $context['membership_level'] && ( $premium_min !== $base_min || $premium_max !== $base_max ) ) {
                $cost_html = sprintf(
                    "<span class='tta-ticket-price tta-event-costmod-class tta-event-costmod-class-strikethrough tta-event-costmod-class-premium'><strong>%s</strong> <span class='tta-price-strike'>%s</span> %s</span>",
                    esc_html__( 'Cost:', 'tta' ),
                    esc_html( $base_str ),
                    esc_html( $premium_str )
                );
            } else {
                $cost_html = sprintf(
                    "<span class='tta-ticket-price tta-event-costmod-class'><strong>%s</strong> %s</span>",
                    esc_html__( 'Cost:', 'tta' ),
                    esc_html( $base_str )
                );
            }
        } else {
            $cost_html = sprintf(
                "<span class='tta-ticket-price tta-event-costmod-class'><strong>%s</strong> %s</span>",
                esc_html__( 'Cost:', 'tta' ),
                esc_html( $base_str )
            );
        }

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
                <div class="tta-event-thumb" style="background-image:url('<?php echo esc_url( $img_url ); ?>');"></div>
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
                            <div class="tta-event-details-icon-after"><?php echo wp_kses_post( $cost_html ); ?></div>
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
                            <div class="tta-event-details-icon-after"><strong><?php esc_html_e( 'Location:', 'tta' ); ?></strong> <a href="<?php echo esc_url( $page_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $address ); ?></a></div>
                        </li>
                    </ul>
                    <p class="tta-event-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                    <?php if ( $remaining > 0 ) : ?>
                    <p class="tta-event-remaining"><?php printf( esc_html__( '%d tickets remaining', 'tta' ), $remaining ); ?></p>
                    <?php else : ?>
                    <p class="tta-event-remaining tta-fomo-remaining-styling"><?php esc_html_e( 'Sold Out!', 'tta' ); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $page_url ); ?>"><span class="tta-event-link">
                    <?php
                    if ( $remaining <= 0 ) {
                        echo $has_waitlist ? esc_html__( 'Join The Waitlist', 'tta' ) : esc_html__( 'Sold Out', 'tta' );
                    } else {
                        echo esc_html__( 'Get Your Tickets', 'tta' );
                    }
                    ?></span></a>
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
</div>
</div>
<?php
get_footer();
