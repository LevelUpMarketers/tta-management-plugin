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
        if ( in_array( $page, [ 'tta-events', 'tta-members', 'tta-tickets', 'tta-comms', 'tta-ads', 'tta-venues', 'tta-refund-requests', 'tta-settings', 'tta-partners', 'tta-bi-dashboard' ], true ) ) {

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
                    'save_partner_nonce'  => wp_create_nonce( 'tta_partner_save_action' ),
                    'get_partner_nonce'   => wp_create_nonce( 'tta_partner_manage_action' ),
                    'update_partner_nonce'=> wp_create_nonce( 'tta_partner_manage_action' ),
                    'get_member_nonce'    => wp_create_nonce( 'tta_member_update_action' ),
                    'update_member_nonce' => wp_create_nonce( 'tta_member_update_action' ),
                    'get_ticket_nonce'    => wp_create_nonce( 'tta_ticket_get_action' ),
                    'save_ticket_nonce'   => wp_create_nonce( 'tta_ticket_save_action' ),
                    'export_attendees_nonce' => wp_create_nonce( 'tta_export_attendees_action' ),
                    'get_venue_nonce'     => wp_create_nonce( 'tta_venue_get_action' ),
                    'save_venue_nonce'    => wp_create_nonce( 'tta_venue_save_action' ),
                    'get_ad_nonce'        => wp_create_nonce( 'tta_ad_get_action' ),
                    'save_ad_nonce'       => wp_create_nonce( 'tta_ad_save_action' ),
                    'save_comm_nonce'     => wp_create_nonce( 'tta_comms_save_action' ),
                    'membership_admin_nonce' => wp_create_nonce( 'tta_membership_admin_action' ),
                    'attendee_admin_nonce' => wp_create_nonce( 'tta_attendee_admin_action' ),
                    'waitlist_admin_nonce' => wp_create_nonce( 'tta_waitlist_admin_action' ),
                    'authnet_test_nonce'   => wp_create_nonce( 'tta_authnet_test_action' ),
                    'bi_monthly_overview_nonce' => wp_create_nonce( 'tta_bi_monthly_overview_action' ),
                    'email_logs_nonce'    => wp_create_nonce( 'tta_email_logs_action' ),
                    'email_log_clear_nonce' => wp_create_nonce( 'tta_email_clear_action' ),
                    'banned_members_nonce' => wp_create_nonce( 'tta_banned_members_action' ),
                    'checkinPreviewPlaceholder' => __( '[Message typed on the Event Check-In page]', 'tta' ),
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
                        $calendar_links             = tta_build_event_calendar_links( $e );
                        $e['google_calendar_url']   = $calendar_links['google_calendar_url'];
                        $e['ics_download_url']      = $calendar_links['ics_download_url'];
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

            if ( 'tta-members' === $page ) {
                $use_sandbox = (bool) get_option( 'tta_authnet_use_sandbox', get_option( 'tta_authnet_sandbox', false ) );
                $login       = $use_sandbox ? get_option( 'tta_authnet_login_id_sandbox', '' ) : get_option( 'tta_authnet_login_id_live', '' );
                $client_key  = $use_sandbox ? get_option( 'tta_authnet_public_client_key_sandbox', '' ) : get_option( 'tta_authnet_public_client_key_live', '' );
                if ( ! $client_key && defined( 'TTA_AUTHNET_CLIENT_KEY' ) ) {
                    $client_key = TTA_AUTHNET_CLIENT_KEY;
                }
                $accept_url  = $use_sandbox ? 'https://jstest.authorize.net/v1/Accept.js' : 'https://js.authorize.net/v1/Accept.js';

                wp_enqueue_script(
                    'tta-acceptjs',
                    $accept_url,
                    [],
                    null,
                    true
                );

                wp_enqueue_script(
                    'tta-admin-membership-payments',
                    TTA_PLUGIN_URL . 'assets/js/backend/membership-payments.js',
                    [ 'tta-admin-js', 'tta-acceptjs', 'jquery' ],
                    TTA_PLUGIN_VERSION,
                    true
                );

                wp_localize_script(
                    'tta-admin-membership-payments',
                    'TTA_ACCEPT_ADMIN',
                    [
                        'loginId'        => $login,
                        'clientKey'      => $client_key,
                        'failureMessage' => wp_kses_post( __( "Encryption of your payment information failed! Please try again later. If you're still having trouble, please contact us using the form on our <a href=\"/contact\">Contact Page</a>.", 'tta' ) ),
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

        // Logout link handler for top bar.
        wp_enqueue_script(
            'tta-logout-link',
            TTA_PLUGIN_URL . 'assets/js/frontend/logout.js',
            [],
            TTA_PLUGIN_VERSION,
            true
        );
        $first_name = '';
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $first_name = $user->user_firstname;
            if ( empty( $first_name ) ) {
                $first_name = $user->display_name;
            }
            $first_name = sanitize_text_field( $first_name );
        }

        wp_localize_script(
            'tta-logout-link',
            'TTALogout',
            [
                'url'        => wp_logout_url( home_url( '/' ) ),
                'name'       => $first_name,
                'loggedIn'   => is_user_logged_in(),
                'loginUrl'   => home_url( '/login-or-create-an-account/' ),
                'loginLabel' => esc_html__( 'Log In', 'tta-management-plugin' ),
            ]
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

            if ( ! session_id() ) {
                session_start();
            }
            if ( empty( $_SESSION['tta_checkout_key'] ) && ! empty( $_SESSION['tta_cart_session'] ) ) {
                $_SESSION['tta_checkout_key'] = $_SESSION['tta_cart_session'];
            }
            wp_localize_script(
                'tta-checkout-process',
                'tta_checkout',
                [
                    'ajax_url'       => admin_url( 'admin-ajax.php' ),
                    'nonce'          => wp_create_nonce( 'tta_checkout_action' ),
                    'user_email'     => is_user_logged_in() ? wp_get_current_user()->user_email : '',
                    'dashboard_url'  => home_url( '/member-dashboard/?tab=billing' ),
                    'basic_price'    => TTA_BASIC_MEMBERSHIP_PRICE,
                    'premium_price'  => TTA_PREMIUM_MEMBERSHIP_PRICE,
                    'checkout_key'   => sanitize_text_field( $_SESSION['tta_checkout_key'] ?? '' ),
                    'encryption_failed_html' => wp_kses_post( __( "Encryption of your payment information failed! Please try again later. If you're still having trouble, please contact us using the form on our <a href=\"/contact\">Contact Page</a>.", 'tta' ) ),
                    'debug'          => tta_payment_debug_enabled(),
                ]
            );

            $use_sandbox = (bool) get_option( 'tta_authnet_use_sandbox', get_option( 'tta_authnet_sandbox', false ) );
            $login       = $use_sandbox ? get_option( 'tta_authnet_login_id_sandbox', '' ) : get_option( 'tta_authnet_login_id_live', '' );
            $client_key  = $use_sandbox ? get_option( 'tta_authnet_public_client_key_sandbox', '' ) : get_option( 'tta_authnet_public_client_key_live', '' );
            if ( ! $client_key && defined( 'TTA_AUTHNET_CLIENT_KEY' ) ) {
                $client_key = TTA_AUTHNET_CLIENT_KEY;
            }
            $mode        = $use_sandbox ? 'sandbox' : 'live';
            $accept_url  = $use_sandbox ? 'https://jstest.authorize.net/v1/Accept.js' : 'https://js.authorize.net/v1/Accept.js';

            wp_enqueue_script(
                'tta-acceptjs',
                $accept_url,
                [],
                null,
                true
            );

            wp_enqueue_script(
                'tta-accept-checkout',
                TTA_PLUGIN_URL . 'assets/js/frontend/tta-accept-checkout.js',
                [ 'tta-acceptjs', 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            wp_localize_script(
                'tta-accept-checkout',
                'TTA_ACCEPT',
                [
                    'loginId'   => $login,
                    'clientKey' => $client_key,
                    'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                    'nonce'     => wp_create_nonce( 'tta_pay_nonce' ),
                    'mode'      => $mode,
                    'debug'     => tta_payment_debug_enabled(),
                ]
            );
        }

        // Login/Register and Partner Admin page template assets
        if (
            function_exists( 'is_page_template' )
            && (
                is_page_template( 'login-register-page-template.php' )
                || is_page_template( 'partner-admin-page-template.php' )
                || is_page_template( 'partner-login-page-template.php' )
            )
        ) {
            wp_enqueue_style(
                'tta-login-register-css',
                TTA_PLUGIN_URL . 'assets/css/frontend/login-register.css',
                [ 'tta-frontend-css' ],
                TTA_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'tta-login-register-js',
                TTA_PLUGIN_URL . 'assets/js/frontend/login-register-page.js',
                [ 'jquery' ],
                TTA_PLUGIN_VERSION,
                true
            );

            $redirect_url = apply_filters( 'tta_login_register_redirect_url', home_url( '/events' ) );

            $login_register_localize = [
                'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
                'nonce'                => wp_create_nonce( 'tta_frontend_nonce' ),
                'redirectUrl'          => esc_url_raw( $redirect_url ),
                'emailMismatch'        => __( 'Email addresses do not match.', 'tta' ),
                'passwordMismatch'     => __( 'Passwords do not match.', 'tta' ),
                'passwordRequirements' => __( 'Password must be at least 8 characters and include upper and lower case letters and a number.', 'tta' ),
                'requestFailed'        => __( 'Request failed.', 'tta' ),
                'successMessage'       => __( 'Account created! Redirecting…', 'tta' ),
                'showPassword'         => __( 'Show password', 'tta' ),
                'hidePassword'         => __( 'Hide password', 'tta' ),
            ];

            if ( function_exists( 'is_page_template' ) && is_page_template( 'partner-login-page-template.php' ) ) {
                global $wpdb;

                $page_id      = get_queried_object_id();
                $partner_name = TTA_Cache::remember(
                    'partner_login_assets_' . $page_id,
                    static function () use ( $wpdb, $page_id ) {
                        if ( ! $page_id ) {
                            return '';
                        }

                        $table = $wpdb->prefix . 'tta_partners';

                        return (string) $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT company_name FROM {$table} WHERE signuppageid = %d LIMIT 1",
                                $page_id
                            )
                        );
                    },
                    MINUTE_IN_SECONDS * 5
                );

                $partner_name = $partner_name ?: __( 'our partner', 'tta' );
                $contact_link = '<a href="/contact">' . esc_html__( 'on our Contact page', 'tta' ) . '</a>';

                $login_register_localize['isPartnerLogin'] = true;
                $login_register_localize['partnerPageId']  = $page_id;
                $login_register_localize['partnerName']    = $partner_name;
                $login_register_localize['requestFailed']  = sprintf(
                    /* translators: 1: partner name, 2: contact link */
                    __( 'We don\'t seem to have this email address associated with %1$s! Are you sure you\'re using the email address that %1$s would have provided to us? If you\'re still having trouble, contact us %2$s.', 'tta' ),
                    esc_html( $partner_name ),
                    wp_kses( $contact_link, [ 'a' => [ 'href' => [] ] ] )
                );
            }

            wp_localize_script( 'tta-login-register-js', 'ttaLoginRegister', $login_register_localize );

            if ( is_page_template( 'partner-admin-page-template.php' ) ) {
                wp_enqueue_style(
                    'tta-partner-dashboard-css',
                    TTA_PLUGIN_URL . 'assets/css/frontend/member-dashboard.css',
                    [ 'tta-login-register-css' ],
                    TTA_PLUGIN_VERSION
                );
            }
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
            $min_length = tta_get_checkin_email_min_length();

            wp_localize_script(
                'tta-checkin-js',
                'TTA_Checkin',
                [
                    'ajax_url'  => admin_url( 'admin-ajax.php' ),
                    'get_nonce' => wp_create_nonce( 'tta_get_attendance_action' ),
                    'set_nonce' => wp_create_nonce( 'tta_set_attendance_action' ),
                    'email_nonce' => wp_create_nonce( 'tta_email_attendees_action' ),
                    'attendance_label' => __( 'Event Attendance & No-Shows:', 'tta' ),
                    'attended_label'   => __( 'Events Attended', 'tta' ),
                    'noshow_label'     => __( 'No-Shows', 'tta' ),
                    'email_required'   => __( 'Please type a message before sending.', 'tta' ),
                    'email_success'    => __( 'Email sent to all attendees.', 'tta' ),
                    'email_failed'     => __( 'Unable to send the email. Please try again.', 'tta' ),
                    'email_min_length' => $min_length,
                    'email_too_short'  => sprintf(
                        /* translators: %d: minimum number of characters required for the message. */
                        __( 'Please enter at least %d characters before sending.', 'tta' ),
                        $min_length
                    ),
                ]
            );
        }
    }

}

TTA_Assets::init();
