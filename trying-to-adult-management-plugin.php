<?php
/**
 * Plugin Name: Trying To Adult Management Plugin
 * Plugin URI: https://example.com
 * Description: Custom plugin for Members, Events, Tickets management with waitlist, notifications, and Authorize.Net integration.
 * Version: 0.4.5
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: trying-to-adult-management
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'TTA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TTA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TTA_PLUGIN_VERSION', '0.4.5' );
define( 'TTA_DB_VERSION', '1.11.0' );
define( 'TTA_BASIC_MEMBERSHIP_PRICE', 5.00 );
define( 'TTA_PREMIUM_MEMBERSHIP_PRICE', 10.00 );
define( 'TTA_REENTRY_TICKET_PRICE', 25.00 );
define( 'TTA_BASIC_SUBSCRIPTION_NAME', 'Trying to Adult Basic Membership' );
define( 'TTA_PREMIUM_SUBSCRIPTION_NAME', 'Trying to Adult Premium Membership' );
define( 'TTA_BASIC_SUBSCRIPTION_DESCRIPTION', 'Monthly Basic Membership subscription for Trying to Adult.' );
define( 'TTA_PREMIUM_SUBSCRIPTION_DESCRIPTION', 'Monthly Premium Membership subscription for Trying to Adult.' );
// Ban sentinel datetimes
define( 'TTA_BAN_UNTIL_INDEFINITE', '9999-12-31 23:59:59' );
define( 'TTA_BAN_UNTIL_REENTRY', '9998-12-31 23:59:59' );

if ( ! defined( 'TTA_AUTHNET_IMPORT_LOOKBACK_DAYS' ) ) {
    define( 'TTA_AUTHNET_IMPORT_LOOKBACK_DAYS', 93 );
}
if ( ! defined( 'TTA_AUTHNET_IMPORT_MAX_TRANSACTIONS' ) ) {
    define( 'TTA_AUTHNET_IMPORT_MAX_TRANSACTIONS', 20 );
}
if ( ! defined( 'TTA_AUTHNET_IMPORT_MAX_REQUESTS' ) ) {
    define( 'TTA_AUTHNET_IMPORT_MAX_REQUESTS', 200 );
}

require_once TTA_PLUGIN_DIR . 'includes/helpers.php';
require_once TTA_PLUGIN_DIR . 'includes/classes/class-tta-debug-logger.php';
TTA_Debug_Logger::init();
require_once TTA_PLUGIN_DIR . 'includes/classes/class-tta-tooltips.php';
require_once TTA_PLUGIN_DIR . 'includes/admin-bar.php';

// Load Authorize.Net and SendGrid credentials from the database or environment variables.
$tta_authnet_sandbox     = get_option( 'tta_authnet_sandbox', false );
$creds                   = tta_get_authnet_credentials( (bool) $tta_authnet_sandbox );
$tta_authnet_login       = $creds['login_id'];
$tta_authnet_transaction = $creds['transaction_key'];
$tta_sendgrid_key        = get_option( 'tta_sendgrid_api_key' );

if ( ! $tta_authnet_login && getenv( 'TTA_AUTHNET_LOGIN_ID' ) ) {
    $tta_authnet_login = getenv( 'TTA_AUTHNET_LOGIN_ID' );
}
if ( ! $tta_authnet_transaction && getenv( 'TTA_AUTHNET_TRANSACTION_KEY' ) ) {
    $tta_authnet_transaction = getenv( 'TTA_AUTHNET_TRANSACTION_KEY' );
}
if ( ! $tta_sendgrid_key && getenv( 'TTA_SENDGRID_API_KEY' ) ) {
    $tta_sendgrid_key = getenv( 'TTA_SENDGRID_API_KEY' );
}

if ( $tta_authnet_login ) {
    define( 'TTA_AUTHNET_LOGIN_ID', $tta_authnet_login );
}
if ( $tta_authnet_transaction ) {
    define( 'TTA_AUTHNET_TRANSACTION_KEY', $tta_authnet_transaction );
}
if ( $tta_sendgrid_key && ! defined( 'TTA_SENDGRID_API_KEY' ) ) {
    define( 'TTA_SENDGRID_API_KEY', $tta_sendgrid_key );
}
if ( ! defined( 'TTA_AUTHNET_SANDBOX' ) ) {
    define( 'TTA_AUTHNET_SANDBOX', (bool) $tta_authnet_sandbox );
}

// Warn administrators if Authorize.Net credentials are missing.
if ( is_admin() ) {
    add_action(
        'admin_notices',
        function () {
            $c = tta_get_authnet_credentials();
            if ( current_user_can( 'manage_options' ) && ( empty( $c['login_id'] ) || empty( $c['transaction_key'] ) ) ) {
                echo '<div class="notice notice-error"><p>' .
                    esc_html__( 'Authorize.Net credentials are not configured. Enter them under TTA Settings → API Settings.', 'tta' ) .
                    '</p></div>';
            }
        }
    );
}

// -----------------------------------------------------------------------------
// Twilio Credentials
// -----------------------------------------------------------------------------
$twilio_config = TTA_PLUGIN_DIR . 'twilio-config.php';
if ( file_exists( $twilio_config ) ) {
    include_once $twilio_config;
}

if ( getenv( 'TTA_TWILIO_SID' ) && ! defined( 'TTA_TWILIO_SID' ) ) {
    define( 'TTA_TWILIO_SID', getenv( 'TTA_TWILIO_SID' ) );
}
if ( getenv( 'TTA_TWILIO_TOKEN' ) && ! defined( 'TTA_TWILIO_TOKEN' ) ) {
    define( 'TTA_TWILIO_TOKEN', getenv( 'TTA_TWILIO_TOKEN' ) );
}
if ( getenv( 'TTA_TWILIO_FROM' ) && ! defined( 'TTA_TWILIO_FROM' ) ) {
    define( 'TTA_TWILIO_FROM', getenv( 'TTA_TWILIO_FROM' ) );
}

if ( is_admin() ) {
    add_action( 'admin_notices', function () {
        if ( current_user_can( 'manage_options' ) && ( ! defined( 'TTA_TWILIO_SID' ) || ! defined( 'TTA_TWILIO_TOKEN' ) || ! defined( 'TTA_TWILIO_FROM' ) ) ) {
            echo '<div class="notice notice-error"><p>' .
                esc_html__( 'Twilio credentials are not configured. Define TTA_TWILIO_SID, TTA_TWILIO_TOKEN and TTA_TWILIO_FROM in twilio-config.php or your server environment.', 'tta' ) .
                '</p></div>';
        }
    } );
}



// Load Composer autoloader if present within the plugin directory.
if ( file_exists( TTA_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once TTA_PLUGIN_DIR . 'vendor/autoload.php';
}


// Autoload TTA_ classes
spl_autoload_register( function ( $class ) {
    if ( 0 !== strpos( $class, 'TTA_' ) ) {
        return;
    }
    $file = TTA_PLUGIN_DIR . 'includes/classes/class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
    if ( file_exists( $file ) ) {
        include $file;
    }
} );


// ──────────────────────────────────────────────────────────────────────────
// 1) Load our helper functions (including tta_get_us_states())
// ──────────────────────────────────────────────────────────────────────────
// Core includes
require_once TTA_PLUGIN_DIR . 'includes/class-db-setup.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-event-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-cart-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-checkout-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-events-list-page.php';
require_once TTA_PLUGIN_DIR . 'includes/api/class-authorizenet-api.php';
require_once TTA_PLUGIN_DIR . 'includes/email/class-email-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/email/class-email-reminders.php';
require_once TTA_PLUGIN_DIR . 'includes/sms/class-sms-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/waitlist/class-waitlist.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/class-ajax-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-members-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-events-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-venues-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-tickets-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-settings-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-comms-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-ads-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-discount-codes-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-bi-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-refund-requests-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/shortcodes/class-events-shortcode.php';
require_once TTA_PLUGIN_DIR . 'includes/shortcodes/class-members-shortcode.php';
require_once TTA_PLUGIN_DIR . 'includes/shortcodes/class-homepage-shortcode.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-tta-member-dashboard.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-tta-checkin-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-become-member-page.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-tta-alert-bar.php';
require_once TTA_PLUGIN_DIR . 'includes/cart/class-cart.php';
require_once TTA_PLUGIN_DIR . 'includes/cart/class-cart-cleanup.php';
require_once TTA_PLUGIN_DIR . 'includes/classes/class-tta-event-archiver.php';
require_once TTA_PLUGIN_DIR . 'includes/classes/class-tta-refund-processor.php';
require_once TTA_PLUGIN_DIR . 'includes/database-testing/class-tta-sample-data.php';



// Activation & Deactivation
register_activation_hook( __FILE__, array( 'TTA_DB_Setup', 'install' ) );
register_activation_hook( __FILE__, array( 'TTA_Cart_Cleanup', 'schedule_event' ) );
register_activation_hook( __FILE__, array( 'TTA_Event_Archiver', 'schedule_event' ) );
register_activation_hook( __FILE__, array( 'TTA_Refund_Processor', 'schedule_event' ) );
register_deactivation_hook( __FILE__, array( 'TTA_DB_Setup', 'uninstall' ) );
register_deactivation_hook( __FILE__, array( 'TTA_Cart_Cleanup', 'clear_event' ) );
register_deactivation_hook( __FILE__, array( 'TTA_Event_Archiver', 'clear_event' ) );
register_deactivation_hook( __FILE__, array( 'TTA_Refund_Processor', 'clear_event' ) );

// Initialize plugin
add_action( 'plugins_loaded', array( 'TTA_Plugin', 'init' ) );
add_action( 'wp_login', 'tta_check_subscription_on_login', 10, 2 );
add_action( 'init', 'tta_check_subscription_on_init' );

class TTA_Plugin {
    public static function init() {

        // Ensure database schema is up to date
        TTA_DB_Setup::maybe_upgrade();

        // Load assets hooks:
        TTA_Assets::init();

        // Admin pages
        if ( is_admin() ) {
            TTA_Members_Admin::get_instance();
            TTA_Events_Admin::get_instance();
            TTA_Tickets_Admin::get_instance();
            TTA_Settings_Admin::get_instance();
            TTA_Comms_Admin::get_instance();
            TTA_Ads_Admin::get_instance();
            TTA_Refund_Requests_Admin::get_instance();
        } else {
            // Frontend shortcodes
            TTA_Events_Shortcode::get_instance();
            TTA_Members_Shortcode::get_instance();
        }

        // Notification handlers
        TTA_Email_Handler::get_instance();
        TTA_SMS_Handler::get_instance();
        TTA_Email_Reminders::init();

        // Expired cart cleanup
        TTA_Cart_Cleanup::init();
        TTA_Event_Archiver::init();
        TTA_Refund_Processor::init();

        // Frontend alert bar
        TTA_Alert_Bar::init();

        // Clear plugin caches after a successful checkout
        add_action( 'tta_checkout_complete', [ 'TTA_Cache', 'flush' ] );
    }
}
?>
