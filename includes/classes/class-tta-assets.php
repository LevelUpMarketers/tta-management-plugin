<?php
/**
 * File: includes/classes/class-tta-assets.php
 * Purpose: Enqueue CSS/JS for both the backend and frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Assets {

    /**
     * Hook into WP to enqueue assets.
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_backend_assets' ] );
        add_action( 'wp_enqueue_scripts',    [ __CLASS__, 'enqueue_frontend_assets' ] );
    }

    /**
     * Enqueue admin‐only CSS/JS.
     *
     * We only load wp_enqueue_editor() (plus our admin.js + CSS) when we're on
     * our plugin’s Events, Members, or Tickets screens.
     *
     * @param string $hook_suffix The current admin page.
     */
    public static function enqueue_backend_assets( $hook_suffix ) {
        $page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
        if ( in_array( $page, [ 'tta-events','tta-members','tta-tickets','tta-comms','tta-ads','tta-venues','tta-refund-requests','tta-settings' ], true ) ) {

            // 1) Make sure the full TinyMCE / Quicktags / editor CSS are loaded:
            if ( function_exists( 'wp_enqueue_editor' ) ) {
                // WP ≥ 4.8: this loads all scripts/styles needed for wp_editor()
                wp_enqueue_editor();
            } else {
                // Fallback for older WP versions (< 4.8)
                wp_enqueue_script( 'editor' );
                wp_enqueue_script( 'quicktags' );
                wp_enqueue_style( 'editor-buttons' );
            }

            // 2) Enable the media uploader for wp.media()
            wp_enqueue_media();

            // 3) Now load our plugin’s admin CSS & JS

            // Admin CSS (tooltips, layout, etc.)
            wp_enqueue_style(
                'tta-admin-css',
                TTA_PLUGIN_URL . 'assets/css/backend/admin.css',
                [],
                TTA_PLUGIN_VERSION
            );

            // Tooltip CSS (pure‐CSS approach)
            wp_enqueue_style(
                'tta-tooltips-css',
                TTA_PLUGIN_URL . 'assets/css/backend/tooltips.css',
                [],
                TTA_PLUGIN_VERSION
            );

            // Admin JS (our AJAX handlers, inline-row toggles, media pickers, spinner logic, etc.)
            wp_enqueue_script(
                'tta-admin-js',
                TTA_PLUGIN_URL . 'assets/js/backend/admin.js',
                [ 'jquery', 'wp-editor', 'wp-tinymce', 'quicktags', 'jquery-ui-core' ],
                TTA_PLUGIN_VERSION,
                true
            );


            // Media uploader helper JS
            wp_enqueue_script(
                'tta-media-js',
                TTA_PLUGIN_URL . 'assets/js/backend/media-uploader.js',
                [],
                TTA_PLUGIN_VERSION,
                true
            );

            // Image popup used for event images
            wp_enqueue_style(
                'tta-popup-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/profile-popup.css',
                [],
                TTA_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'tta-popup-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/profile-popup.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script(
                'tta-checkout-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/checkout-expiration-mask.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            // 4) Localize nonces & AJAX URL for our admin JS
            wp_localize_script(
                'tta-admin-js',
                'TTA_Ajax',
                [
                    'ajax_url'            => admin_url( 'admin-ajax.php' ),
                    'get_event_nonce'     => wp_create_nonce( 'tta_event_get_action' ),
                    'save_event_nonce'    => wp_create_nonce( 'tta_event_save_action' ),
                    'save_member_nonce'   => wp_create_nonce( 'tta_member_save_action' ),
                    'get_member_nonce'    => wp_create_nonce( 'tta_member_update_action' ),
                    'update_member_nonce' => wp_create_nonce( 'tta_member_update_action' ),
                    'get_ticket_nonce'    => wp_create_nonce( 'tta_ticket_get_action' ),
                    'save_ticket_nonce'   => wp_create_nonce( 'tta_ticket_save_action' ),
                    'get_venue_nonce'     => wp_create_nonce( 'tta_venue_get_action' ),
                    'save_venue_nonce'    => wp_create_nonce( 'tta_venue_save_action' ),
                    'get_ad_nonce'        => wp_create_nonce( 'tta_ad_get_action' ),
                    'save_ad_nonce'       => wp_create_nonce( 'tta_ad_save_action' ),
                    'save_comm_nonce'     => wp_create_nonce( 'tta_comms_save_action' ),
                    'membership_admin_nonce' => wp_create_nonce( 'tta_membership_admin_action' ),
                    'attendee_admin_nonce' => wp_create_nonce( 'tta_attendee_admin_action' ),
                    'waitlist_admin_nonce' => wp_create_nonce( 'tta_waitlist_admin_action' ),
                    'authnet_test_nonce'   => wp_create_nonce( 'tta_authnet_test_action' ),
                    'email_logs_nonce'    => wp_create_nonce( 'tta_email_logs_action' ),
                    'email_log_clear_nonce' => wp_create_nonce( 'tta_email_clear_action' ),
                    'banned_members_nonce' => wp_create_nonce( 'tta_banned_members_action' ),
                    'sample_event'        => ( function() {
                        $e = tta_get_next_event();
                        if ( ! $e ) {
                            return null;
                        }
                        $e['page_url']              = get_permalink( $e['page_id'] );
                        $e['dashboard_profile_url'] = home_url( '/member-dashboard/?tab=profile' );
                        $e['dashboard_upcoming_url'] = home_url( '/member-dashboard/?tab=upcoming' );
                        $e['dashboard_waitlist_url'] = home_url( '/member-dashboard/?tab=waitlist' );
                        $e['dashboard_past_url']    = home_url( '/member-dashboard/?tab=past' );
                        $e['dashboard_billing_url'] = home_url( '/member-dashboard/?tab=billing' );
                        $e['address_link']          = $e['address'] ? esc_url( 'https://maps.google.com/?q=' . rawurlencode( $e['address'] ) ) : '';
                        $e['date']                  = $e['date_formatted'];
                        $e['time']                  = $e['time_formatted'];
                        return $e;
                    } )(),
                    'sample_member'       => tta_get_sample_member(),
                ]
            );

            if ( 'tta-settings' === $page ) {
                wp_localize_script(
                    'tta-admin-js',
                    'TTA_Authnet',
                    [
                        'live_login'    => get_option( 'tta_authnet_login_id_live', '' ),
                        'live_key'      => get_option( 'tta_authnet_transaction_key_live', '' ),
                        'sandbox_login' => get_option( 'tta_authnet_login_id_sandbox', '' ),
                        'sandbox_key'   => get_option( 'tta_authnet_transaction_key_sandbox', '' ),
                    ]
                );
            }
        }
    }

    /**
     * Enqueue front‐end CSS/JS.
     */
    public static function enqueue_frontend_assets() {
        // 1) Main frontend stylesheet (always)
        wp_enqueue_style(
            'tta-frontend-css',
            TTA_PLUGIN_URL . 'assets/css/frontend/style.css',
            [],
            TTA_PLUGIN_VERSION
        );

        // Register assets used by shortcodes.
        wp_register_style(
            'tta-homepage-shortcode',
            TTA_PLUGIN_URL . 'assets/css/frontend/homepage-shortcode.css',
            [ 'tta-frontend-css' ],
            TTA_PLUGIN_VERSION
        );
        wp_register_script(
            'tta-homepage-shortcode',
            TTA_PLUGIN_URL . 'assets/js/frontend/homepage-shortcode.js',
            [ 'jquery' ],
            TTA_PLUGIN_VERSION,
            true
        );
        wp_register_style(
            'tta-popup-css',
            TTA_PLUGIN_URL . 'assets/css/frontend/profile-popup.css',
            [ 'tta-frontend-css' ],
            TTA_PLUGIN_VERSION
        );
        wp_register_script(
            'tta-popup-js',
            TTA_PLUGIN_URL . 'assets/js/frontend/profile-popup.js',
            [ 'jquery' ],
            TTA_PLUGIN_VERSION,
            true
        );


        // 2) Only on our “Event Page” template, enqueue event-page.css and cart + event JS
        if ( function_exists( 'is_page_template' ) && is_page_template( 'event-page-template.php' ) ) {
            // Event page CSS
            wp_enqueue_style(
                'tta-eventpage-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/event-page.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );

            // Cart & quantity JS
            wp_enqueue_script(
                'tta-cart-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/tta-cart.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            // Event page specific JS
            wp_enqueue_script(
                'tta-eventpage-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/event-page.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_enqueue_script(
                'tta-share-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/share/event-share.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_enqueue_style(
                'tta-popup-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/profile-popup.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'tta-popup-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/profile-popup.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_enqueue_script(
                'sticky-sidebar',
                TTA_PLUGIN_URL . 'assets/js/frontend/sticky-sidebar.min.js',
                [],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_enqueue_script(
                'tta-sticky-ad',
                TTA_PLUGIN_URL . 'assets/js/frontend/sticky-events-ad.js',
                [ 'jquery', 'sticky-sidebar' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_localize_script(
                'tta-cart-js',
                'tta_ajax',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'tta_frontend_nonce' ),
                ]
            );
            wp_localize_script(
                'tta-eventpage-js',
                'tta_event',
                [
                    'limit_msg'      => __( "We're sorry, there's a limit of %d per ticket.", 'tta' ),
                    'prev_limit_msg' => __( "We're sorry, there's a limit of %d per ticket. You've already purchased tickets in a previous transaction.", 'tta' ),
                    'sold_out_msg'   => __( "We're sorry, but someone just purchased the last ticket. It's currently reserved in another member's cart.", 'tta' ),
                    'email_mismatch_msg' => __( 'Email addresses do not match.', 'tta' ),
                    'password_mismatch_msg' => __( 'Passwords do not match.', 'tta' ),
                    'password_requirements_msg' => __( 'Password must be at least 8 characters and include upper and lower case letters and a number.', 'tta' ),
                    'request_failed_msg' => __( 'Request failed.', 'tta' ),
                    'account_created_msg' => __( 'Account created! Reloading in %d…', 'tta' ),
                ]
            );
        }

        // 3) Events List Page template assets
        if ( function_exists( 'is_page_template' ) && is_page_template( 'events-list-page-template.php' ) ) {
            wp_enqueue_style(
                'tta-eventslist-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/events-list.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );
            wp_enqueue_style(
                'tta-popup-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/profile-popup.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );
            wp_enqueue_script(
                'tta-popup-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/profile-popup.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_enqueue_script(
                'tta-calendar-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/calendar-ajax.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_enqueue_script(
                'tta-eventslist-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/events-list-page.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_enqueue_script(
                'sticky-sidebar',
                TTA_PLUGIN_URL . 'assets/js/frontend/sticky-sidebar.min.js',
                [],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_enqueue_script(
                'tta-sticky-ad',
                TTA_PLUGIN_URL . 'assets/js/frontend/sticky-events-ad.js',
                [ 'jquery', 'sticky-sidebar' ],
                TTA_PLUGIN_VERSION,
                true
            );
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
            $permalinks = [];
            foreach ( $event_days as $d ) {
                $pid = tta_get_first_event_page_id_for_date( $year, $month, $d );
                if ( $pid ) {
                    $permalinks[ $d ] = get_permalink( $pid );
                }
            }
            wp_localize_script(
                'tta-calendar-js',
                'ttaCal',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'tta_frontend_nonce' ),
                    'permalinks' => $permalinks,
                ]
            );
        }

        // 4) Become a Member page assets
        if ( function_exists( 'is_page_template' ) && is_page_template( 'become-member-page-template.php' ) ) {
            wp_enqueue_style(
                'tta-become-member-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/become-member.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'tta-cart-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/tta-cart.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_localize_script(
                'tta-cart-js',
                'tta_ajax',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'tta_frontend_nonce' ),
                    'password_requirements_msg' => __( 'Password must be at least 8 characters and include upper and lower case letters and a number.', 'tta' ),
                ]
            );
        }

        // 4) Cart Page template assets
        if ( function_exists( 'is_page_template' ) && is_page_template( 'cart-page-template.php' ) ) {
            wp_enqueue_style(
                'tta-cartpage-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/cart-page.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'tta-cart-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/tta-cart.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_localize_script(
                'tta-cart-js',
                'tta_ajax',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'tta_frontend_nonce' ),
                ]
            );
        }

        // 4) Checkout Page template assets
        if ( function_exists( 'is_page_template' ) && is_page_template( 'checkout-page-template.php' ) ) {
            wp_enqueue_style(
                'tta-cartpage-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/cart-page.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'tta-cart-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/tta-cart.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_localize_script(
                'tta-cart-js',
                'tta_ajax',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'tta_frontend_nonce' ),
                ]
            );

            wp_enqueue_script(
                'tta-checkout-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/checkout-expiration-mask.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script(
                'tta-checkout-process',
                TTA_PLUGIN_URL . 'assets/js/frontend/checkout-page.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script(
                'tta-checkout-auth',
                TTA_PLUGIN_URL . 'assets/js/frontend/checkout-auth.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_localize_script(
                'tta-checkout-auth',
                'tta_ajax',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'tta_frontend_nonce' ),
                    'password_requirements_msg' => __( 'Password must be at least 8 characters and include upper and lower case letters and a number.', 'tta' ),
                ]
            );

            wp_localize_script(
                'tta-checkout-process',
                'tta_checkout',
                [
                    'ajax_url'   => admin_url( 'admin-ajax.php' ),
                    'nonce'      => wp_create_nonce( 'tta_checkout_action' ),
                    'user_email' => is_user_logged_in() ? wp_get_current_user()->user_email : '',
                ]
            );
        }

        // 5) Host Check-In template assets
        if ( function_exists( 'is_page_template' ) && is_page_template( 'host-checkin-template.php' ) ) {
            wp_enqueue_script(
                'tta-checkin-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/event-checkin.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );
            wp_localize_script(
                'tta-checkin-js',
                'TTA_Checkin',
                [
                    'ajax_url'  => admin_url( 'admin-ajax.php' ),
                    'get_nonce' => wp_create_nonce( 'tta_get_attendance_action' ),
                    'set_nonce' => wp_create_nonce( 'tta_set_attendance_action' ),
                ]
            );
        }
    }

}

TTA_Assets::init();
