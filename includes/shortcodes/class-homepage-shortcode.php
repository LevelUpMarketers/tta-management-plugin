<?php
/**
 * Shortcode to render the Trying to Adult RVA homepage layout.
 *
 * Outputs a two column layout with sidebar and main content areas populated
 * with dynamic data such as upcoming events and members.
 *
 * Usage: [tta_homepage]
 *
 * @package TTA\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the `[tta_homepage]` shortcode.
 */
class TTA_Homepage_Shortcode {

    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function get_instance() {
        static $inst = null;
        if ( null === $inst ) {
            $inst = new self();
        }
        return $inst;
    }

    /**
     * Hook into WordPress.
     */
    private function __construct() {
        add_shortcode( 'tta_homepage', [ $this, 'render' ] );
    }

    /**
     * Render the shortcode output.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public function render( $atts = [] ) {
        wp_enqueue_style( 'tta-homepage-shortcode' );
        wp_enqueue_script( 'tta-homepage-shortcode' );
        wp_enqueue_style( 'tta-popup-css' );
        wp_enqueue_script( 'tta-popup-js' );

        $next_event    = tta_get_next_event();
        $upcoming      = tta_get_upcoming_events( 1, 4 );
        $past_events   = $this->get_recent_past_events( 4 );
        $newest_member = $this->get_newest_member();
        $birthdays        = $this->get_birthdays_this_month();
        $follower_count   = $this->get_follower_count();
        $leadership_count = $this->get_leadership_count();
        $event_count      = $this->get_event_count();
        $current_month = date_i18n( 'F' );

        ob_start();
        ?>
        <div class="tta-home">
            <aside class="tta-home-sidebar">
                <div class="tta-stats">
                    <h2><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/memberlevel.svg' ); ?>" alt=""><?php esc_html_e( 'TTA Stats', 'tta' ); ?></h2>
                    <ul class="tta-stats-list">
                        <li><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/clock.svg' ); ?>" alt=""><span><?php esc_html_e( 'Founded in 2021', 'tta' ); ?></span></li>
                        <li><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/profile.svg' ); ?>" alt="<?php esc_attr_e( 'Followers', 'tta' ); ?>"><span class="tta-counter" aria-live="polite" data-target="<?php echo esc_attr( $follower_count ); ?>" data-suffix="+">0</span> <?php esc_html_e( 'Followers', 'tta' ); ?></li>
                        <li><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/leader.svg' ); ?>" alt="<?php esc_attr_e( 'Leadership team members', 'tta' ); ?>"><span class="tta-counter" aria-live="polite" data-target="<?php echo esc_attr( $leadership_count ); ?>">0</span> <?php esc_html_e( 'Leadership Team Members', 'tta' ); ?></li>
                        <li><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/calendar.svg' ); ?>" alt="<?php esc_attr_e( 'Events', 'tta' ); ?>"><span class="tta-counter" aria-live="polite" data-target="<?php echo esc_attr( $event_count ); ?>">0</span> <?php esc_html_e( 'Events', 'tta' ); ?></li>
                    </ul>
                </div>
                <?php if ( $next_event ) : ?>
                    <?php $img = $next_event['mainimageid'] ? wp_get_attachment_image_url( intval( $next_event['mainimageid'] ), 'large' ) : TTA_PLUGIN_URL . 'assets/images/admin/default-event.png'; ?>
                    <div class="tta-next-event">
                        <h2><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/upcoming.svg' ); ?>" alt=""><?php esc_html_e( 'Our Next Event', 'tta' ); ?></h2>
                        <a class="tta-next-event__link" href="<?php echo esc_url( get_permalink( $next_event['page_id'] ) ); ?>">
                            <?php if ( $img ) : ?><img class="tta-next-event__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $next_event['name'] ); ?>"><?php endif; ?>
                            <p class="tta-next-event__countdown"><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/clock.svg' ); ?>" alt=""><span class="tta-countdown" data-time="<?php echo esc_attr( $next_event['timestamp'] ); ?>"></span></p>
                            <p class="tta-next-event__name"><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/profile.svg' ); ?>" alt=""><?php echo esc_html( $next_event['name'] ); ?></p>
                            <p class="tta-next-event__date"><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/calendar.svg' ); ?>" alt=""><?php echo esc_html( $next_event['date_formatted'] ); ?></p>
                            <p class="tta-next-event__time"><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/clock.svg' ); ?>" alt=""><?php echo esc_html( $next_event['time_formatted'] ); ?></p>
                            <p class="tta-next-event__address"><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/location.svg' ); ?>" alt=""><?php echo esc_html( $next_event['address'] ); ?></p>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ( $newest_member ) : ?>
                    <div class="tta-newest-member">
                        <h2><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/profile.svg' ); ?>" alt=""><?php esc_html_e( 'Our Newest Member', 'tta' ); ?></h2>
                        <?php
                        $img_id   = intval( $newest_member['profileimgid'] );
                        $img_thumb = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/placeholder-profile.svg';
                        $img_full  = $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : $img_thumb;
                        ?>
                        <img class="tta-newest-member__img tta-popup-img" src="<?php echo esc_url( $img_thumb ); ?>" data-full="<?php echo esc_url( $img_full ); ?>" alt="<?php echo esc_attr( $newest_member['first_name'] . ' ' . $newest_member['last_name'] ); ?>">
                        <p class="tta-newest-member__name"><?php echo esc_html( $newest_member['first_name'] . ' ' . $newest_member['last_name'] ); ?></p>
                        <p class="tta-newest-member__level"><?php echo esc_html( ucfirst( $newest_member['membership_level'] ) ); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ( ! empty( $birthdays ) ) : ?>
                    <div class="tta-birthdays">
                        <h2><img class="tta-event-details-icon" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/calendar.svg' ); ?>" alt=""><?php echo esc_html( $current_month . ' ' . __( 'Birthdays', 'tta' ) ); ?></h2>
                        <div class="tta-birthday-grid">
                            <?php foreach ( $birthdays as $b ) :
                                $img_id  = intval( $b['profileimgid'] );
                                $thumb   = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : TTA_PLUGIN_URL . 'assets/images/public/event-page-icons/placeholder-profile.svg';
                                $full    = $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : $thumb;
                                ?>
                                <img class="tta-popup-img" src="<?php echo esc_url( $thumb ); ?>" data-full="<?php echo esc_url( $full ); ?>" alt="">
                            <?php endforeach; ?>
                        </div>
                        <div class="tta-section-button"><button type="button" class="tta-birthday-toggle button"><?php esc_html_e( 'All Birthdays', 'tta' ); ?></button></div>
                    </div>
                <?php endif; ?>
                <div class="tta-partners">
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
            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/ads/placeholder1.svg' ); ?>" alt="">
        <?php endif; ?>
                    </div>
                </div>
            </aside>
            <div class="tta-home-main">
                <section class="tta-section tta-intro">
                    <div class="tta-intro-inner">
                        <div>
                            <h1><?php esc_html_e( 'Welcome to Trying to Adult RVA', 'tta' ); ?></h1>
                            <p><?php esc_html_e( 'Trying to Adult RVA is a community for young adults in Richmond, Virginia who are ready to make real connections and have fun along the way.', 'tta' ); ?></p>
                            <p><?php esc_html_e( 'We host a wide variety of events each month—like trivia nights, brewery hangs, outdoor adventures, cooking classes, and more—designed to help you meet new people and build lasting friendships.', 'tta' ); ?></p>
                            <p><?php esc_html_e( 'Whether you’re looking for a new crew, a best friend, or just a fun night out, you’ll find a welcoming space here. Anyone 21+ is invited to join, and if you “feel” like a young adult, you’ll fit right in.', 'tta' ); ?></p>
                            <p><a class="button" href="<?php echo esc_url( home_url( '/events' ) ); ?>"><?php esc_html_e( 'Browse Events', 'tta' ); ?></a></p>
                        </div>
                        <div class="tta-intro-img">
                            <?php
                            $carousel_images = [
                                '/wp-content/uploads/2025/08/IMG-1351-reduced.jpg',
                                '/wp-content/uploads/2025/08/IMG-4850-reduced.jpg',
                                '/wp-content/uploads/2022/12/IMG-1153.jpg',
                                '/wp-content/uploads/2025/08/41657B74-F47D-451A-A99A-0B95C793FFD4-1-reduced.jpg',
                                '/wp-content/uploads/2025/08/IMG-7075-1-reduced.jpg',
                            ];
                            foreach ( $carousel_images as $i => $src ) :
                                $class = 0 === $i ? ' class="active"' : '';
                                echo '<img src="' . esc_url( $src ) . '" alt=""' . $class . '>';
                            endforeach;
                            ?>
                        </div>
                    </div>
                </section>
                <?php if ( ! empty( $upcoming['events'] ) ) : ?>
                    <section class="tta-section tta-upcoming">
                        <h2><?php esc_html_e( 'Upcoming Events', 'tta' ); ?></h2>
                        <div class="tta-events-grid">
                            <?php foreach ( $upcoming['events'] as $event ) :
                                $img = $event['mainimageid'] ? wp_get_attachment_image_url( intval( $event['mainimageid'] ), 'medium' ) : TTA_PLUGIN_URL . 'assets/images/admin/default-event.png';
                                ?>
                                <a href="<?php echo esc_url( get_permalink( $event['page_id'] ) ); ?>" class="tta-event-card" style="background-image:url('<?php echo esc_url( $img ); ?>');">
                                    <div class="tta-event-card__caption"><?php echo esc_html( $event['name'] ); ?><br><?php echo esc_html( tta_format_event_date( $event['date'] ) ); ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="tta-section-button"><a class="button" href="<?php echo esc_url( home_url( '/events' ) ); ?>"><?php esc_html_e( 'All Upcoming Events', 'tta' ); ?></a></div>
                    </section>
                <?php endif; ?>
                <?php if ( ! empty( $past_events ) ) : ?>
                    <section class="tta-section tta-past">
                        <h2><?php esc_html_e( 'Past Events', 'tta' ); ?></h2>
                        <div class="tta-past-grid">
                            <?php foreach ( $past_events as $event ) :
                                $img = $event['mainimageid'] ? wp_get_attachment_image_url( intval( $event['mainimageid'] ), 'medium' ) : TTA_PLUGIN_URL . 'assets/images/admin/default-event.png';
                                ?>
                                <a href="<?php echo esc_url( get_permalink( $event['page_id'] ) ); ?>" class="tta-event-card" style="background-image:url('<?php echo esc_url( $img ); ?>');">
                                    <div class="tta-event-card__caption"><?php echo esc_html( $event['name'] ); ?><br><?php echo esc_html( tta_format_event_date( $event['date'] ) ); ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="tta-section-button"><a class="button" href="<?php echo esc_url( home_url( '/past-events' ) ); ?>"><?php esc_html_e( 'More Past Events', 'tta' ); ?></a></div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Retrieve most recent past events.
     *
     * @param int $limit Number of events.
     *
     * @return array[]
     */
    private function get_recent_past_events( $limit = 2 ) {
        return TTA_Cache::remember( 'recent_past_events_' . $limit, function() use ( $limit ) {
            global $wpdb;
            $events_table  = $wpdb->prefix . 'tta_events';
            $archive_table = $wpdb->prefix . 'tta_events_archive';
            $today         = current_time( 'Y-m-d' );
            $sql           = $wpdb->prepare(
                "SELECT id, name, date, page_id, mainimageid FROM (
                    SELECT id, name, date, page_id, mainimageid FROM {$events_table} WHERE date < %s
                    UNION ALL
                    SELECT id, name, date, page_id, mainimageid FROM {$archive_table} WHERE date < %s
                ) AS past ORDER BY date DESC LIMIT %d",
                $today,
                $today,
                $limit
            );
            $rows = $wpdb->get_results( $sql, ARRAY_A );
            foreach ( $rows as &$row ) {
                $row['id']          = intval( $row['id'] );
                $row['page_id']     = intval( $row['page_id'] );
                $row['mainimageid'] = intval( $row['mainimageid'] );
                $row['name']        = sanitize_text_field( $row['name'] );
            }
            return $rows;
        }, 300 );
    }

    /**
     * Retrieve the most recently joined member.
     *
     * @return array|null
     */
    private function get_newest_member() {
        return TTA_Cache::remember( 'tta_newest_member', function() {
            global $wpdb;
            $members_table = $wpdb->prefix . 'tta_members';
            $row = $wpdb->get_row( "SELECT id, first_name, last_name, membership_level, profileimgid FROM {$members_table} ORDER BY joined_at DESC LIMIT 1", ARRAY_A );
            if ( ! $row ) {
                return null;
            }
            return [
                'id'              => intval( $row['id'] ),
                'first_name'      => sanitize_text_field( $row['first_name'] ),
                'last_name'       => sanitize_text_field( $row['last_name'] ),
                'membership_level'=> sanitize_text_field( $row['membership_level'] ),
                'profileimgid'    => intval( $row['profileimgid'] ),
            ];
        }, 300 );
    }

    /**
     * Retrieve members with birthdays in the current month.
     *
     * @return array[]
     */
    private function get_birthdays_this_month() {
        return TTA_Cache::remember( 'tta_birthdays_' . date( 'm' ), function() {
            global $wpdb;
            $members_table = $wpdb->prefix . 'tta_members';
            $month = date_i18n( 'm' );
            $rows = $wpdb->get_results( $wpdb->prepare( "SELECT id, profileimgid FROM {$members_table} WHERE MONTH(dob) = %d", $month ), ARRAY_A );
            $members = [];
            foreach ( $rows as $row ) {
                $members[] = [
                    'id'          => intval( $row['id'] ),
                    'profileimgid'=> intval( $row['profileimgid'] ),
                ];
            }
            return $members;
        }, 300 );
    }

    /**
     * Get total number of followers.
     *
     * @return int
     */
    private function get_follower_count() {
        return 65000;
    }

    /**
     * Get total number of leadership team members.
     *
     * @return int
     */
    private function get_leadership_count() {
        return 21;
    }

    /**
     * Get total number of events (current and archived).
     *
     * @return int
     */
    private function get_event_count() {
        return 665;
    }
}

TTA_Homepage_Shortcode::get_instance();
