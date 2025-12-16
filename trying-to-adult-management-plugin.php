<?php
/**
 * Plugin Name: Trying To Adult Management Plugin
 * Plugin URI: https://example.com
 * Description: Custom plugin for Members, Events, Tickets management with waitlist, notifications, and Authorize.Net integration.
 * Version: 1.0.8
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
define( 'TTA_PLUGIN_VERSION', '1.0.8' );
define( 'TTA_DB_VERSION', '1.14.0' );
define( 'TTA_BASIC_MEMBERSHIP_PRICE', 10.00 );
define( 'TTA_PREMIUM_MEMBERSHIP_PRICE', 17.00 );
define( 'TTA_REENTRY_TICKET_PRICE', 25.00 );
define( 'TTA_BASIC_SUBSCRIPTION_NAME', 'Trying to Adult Standard Membership' );
define( 'TTA_PREMIUM_SUBSCRIPTION_NAME', 'Trying to Adult Premium Membership' );
define( 'TTA_BASIC_SUBSCRIPTION_DESCRIPTION', 'Monthly Standard Membership subscription for Trying to Adult.' );
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

// Load Authorize.Net credentials from the database or environment variables.
$tta_authnet_sandbox     = get_option( 'tta_authnet_sandbox', false );
$creds                   = tta_get_authnet_credentials( (bool) $tta_authnet_sandbox );
$tta_authnet_login       = $creds['login_id'];
$tta_authnet_transaction = $creds['transaction_key'];
$tta_authnet_client      = $creds['client_key'];

if ( ! $tta_authnet_login && getenv( 'TTA_AUTHNET_LOGIN_ID' ) ) {
    $tta_authnet_login = getenv( 'TTA_AUTHNET_LOGIN_ID' );
}
if ( ! $tta_authnet_transaction && getenv( 'TTA_AUTHNET_TRANSACTION_KEY' ) ) {
    $tta_authnet_transaction = getenv( 'TTA_AUTHNET_TRANSACTION_KEY' );
}
if ( ! $tta_authnet_client && getenv( 'TTA_AUTHNET_CLIENT_KEY' ) ) {
    $tta_authnet_client = getenv( 'TTA_AUTHNET_CLIENT_KEY' );
}
if ( $tta_authnet_login ) {
    define( 'TTA_AUTHNET_LOGIN_ID', $tta_authnet_login );
}
if ( $tta_authnet_transaction ) {
    define( 'TTA_AUTHNET_TRANSACTION_KEY', $tta_authnet_transaction );
}
if ( $tta_authnet_client ) {
    define( 'TTA_AUTHNET_CLIENT_KEY', $tta_authnet_client );
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
            if ( current_user_can( 'manage_options' ) && ( empty( $c['login_id'] ) || empty( $c['transaction_key'] ) || empty( $c['client_key'] ) ) ) {
                echo '<div class="notice notice-error"><p>' .
                    esc_html__( 'Authorize.Net credentials are not configured. Enter the Login ID, Transaction Key, and Client Key under TTA Settings → API Settings.', 'tta' ) .
                    '</p></div>';
            }
        }
    );
}

// -----------------------------------------------------------------------------
// Twilio Credentials
// -----------------------------------------------------------------------------
if ( false !== get_option( 'tta_sendgrid_api_key', false ) ) {
    delete_option( 'tta_sendgrid_api_key' );
}

$twilio_config = TTA_PLUGIN_DIR . 'twilio-config.php';
if ( file_exists( $twilio_config ) ) {
    include_once $twilio_config;
}

$twilio_user_sid     = get_option( 'tta_twilio_user_sid', '' );
$twilio_api_sid      = get_option( 'tta_twilio_api_sid', '' );
$twilio_api_key      = get_option( 'tta_twilio_api_key', '' );
$twilio_service_sid  = get_option( 'tta_twilio_messaging_service_sid', '' );
$twilio_from_number  = get_option( 'tta_twilio_sending_number', '' );
$twilio_environment  = get_option( 'tta_twilio_environment', 'live' );
$twilio_sandbox_to   = get_option( 'tta_twilio_sandbox_number', '' );

$twilio_environment = 'sandbox' === strtolower( $twilio_environment ) ? 'sandbox' : 'live';

if ( ! $twilio_user_sid && getenv( 'TTA_TWILIO_USER_SID' ) ) {
    $twilio_user_sid = getenv( 'TTA_TWILIO_USER_SID' );
}
if ( ! $twilio_user_sid && getenv( 'TTA_TWILIO_SID' ) ) {
    $twilio_user_sid = getenv( 'TTA_TWILIO_SID' );
}

if ( ! $twilio_api_sid && getenv( 'TTA_TWILIO_API_SID' ) ) {
    $twilio_api_sid = getenv( 'TTA_TWILIO_API_SID' );
}
if ( ! $twilio_api_sid && getenv( 'TTA_TWILIO_SID' ) ) {
    $twilio_api_sid = getenv( 'TTA_TWILIO_SID' );
}

if ( ! $twilio_api_key && getenv( 'TTA_TWILIO_API_KEY' ) ) {
    $twilio_api_key = getenv( 'TTA_TWILIO_API_KEY' );
}
if ( ! $twilio_api_key && getenv( 'TTA_TWILIO_TOKEN' ) ) {
    $twilio_api_key = getenv( 'TTA_TWILIO_TOKEN' );
}

if ( ! $twilio_service_sid && getenv( 'TTA_TWILIO_MESSAGING_SERVICE_SID' ) ) {
    $twilio_service_sid = getenv( 'TTA_TWILIO_MESSAGING_SERVICE_SID' );
}

if ( ! $twilio_from_number && getenv( 'TTA_TWILIO_SENDING_NUMBER' ) ) {
    $twilio_from_number = getenv( 'TTA_TWILIO_SENDING_NUMBER' );
}
if ( ! $twilio_from_number && getenv( 'TTA_TWILIO_FROM' ) ) {
    $twilio_from_number = getenv( 'TTA_TWILIO_FROM' );
}

$env_environment = getenv( 'TTA_TWILIO_ENVIRONMENT' );
if ( $env_environment ) {
    $twilio_environment = 'sandbox' === strtolower( $env_environment ) ? 'sandbox' : 'live';
}

$env_sandbox_to = getenv( 'TTA_TWILIO_SANDBOX_NUMBER' );
if ( $env_sandbox_to ) {
    $twilio_sandbox_to = $env_sandbox_to;
}

if ( $twilio_user_sid && ! defined( 'TTA_TWILIO_USER_SID' ) ) {
    define( 'TTA_TWILIO_USER_SID', $twilio_user_sid );
}
if ( $twilio_api_sid && ! defined( 'TTA_TWILIO_API_SID' ) ) {
    define( 'TTA_TWILIO_API_SID', $twilio_api_sid );
}
if ( $twilio_api_key && ! defined( 'TTA_TWILIO_API_KEY' ) ) {
    define( 'TTA_TWILIO_API_KEY', $twilio_api_key );
}
if ( $twilio_service_sid && ! defined( 'TTA_TWILIO_MESSAGING_SERVICE_SID' ) ) {
    define( 'TTA_TWILIO_MESSAGING_SERVICE_SID', $twilio_service_sid );
}
if ( $twilio_from_number && ! defined( 'TTA_TWILIO_SENDING_NUMBER' ) ) {
    define( 'TTA_TWILIO_SENDING_NUMBER', $twilio_from_number );
}

if ( ! defined( 'TTA_TWILIO_ENVIRONMENT' ) ) {
    define( 'TTA_TWILIO_ENVIRONMENT', $twilio_environment );
}

if ( ! defined( 'TTA_TWILIO_IS_SANDBOX' ) ) {
    $env_value = defined( 'TTA_TWILIO_ENVIRONMENT' ) ? strtolower( TTA_TWILIO_ENVIRONMENT ) : $twilio_environment;
    define( 'TTA_TWILIO_IS_SANDBOX', 'sandbox' === $env_value );
}

if ( $twilio_sandbox_to && ! defined( 'TTA_TWILIO_SANDBOX_NUMBER' ) ) {
    define( 'TTA_TWILIO_SANDBOX_NUMBER', sanitize_text_field( $twilio_sandbox_to ) );
}

if ( defined( 'TTA_TWILIO_USER_SID' ) && ! defined( 'TTA_TWILIO_SID' ) ) {
    define( 'TTA_TWILIO_SID', TTA_TWILIO_USER_SID );
}
if ( defined( 'TTA_TWILIO_API_KEY' ) && ! defined( 'TTA_TWILIO_TOKEN' ) ) {
    define( 'TTA_TWILIO_TOKEN', TTA_TWILIO_API_KEY );
}
if ( defined( 'TTA_TWILIO_SENDING_NUMBER' ) && ! defined( 'TTA_TWILIO_FROM' ) ) {
    define( 'TTA_TWILIO_FROM', TTA_TWILIO_SENDING_NUMBER );
}

if ( is_admin() ) {
    add_action( 'admin_notices', function () {
        $has_account = defined( 'TTA_TWILIO_USER_SID' ) && TTA_TWILIO_USER_SID;
        $has_key     = defined( 'TTA_TWILIO_API_SID' ) && TTA_TWILIO_API_SID && defined( 'TTA_TWILIO_API_KEY' ) && TTA_TWILIO_API_KEY;
        $has_sender  = ( defined( 'TTA_TWILIO_MESSAGING_SERVICE_SID' ) && TTA_TWILIO_MESSAGING_SERVICE_SID ) || ( defined( 'TTA_TWILIO_SENDING_NUMBER' ) && TTA_TWILIO_SENDING_NUMBER );
        $sandbox_ok  = true;

        if ( defined( 'TTA_TWILIO_IS_SANDBOX' ) && TTA_TWILIO_IS_SANDBOX ) {
            $sandbox_ok = defined( 'TTA_TWILIO_SANDBOX_NUMBER' ) && TTA_TWILIO_SANDBOX_NUMBER;
        }

        if ( current_user_can( 'manage_options' ) && ( ! $has_account || ! $has_key || ! $has_sender ) ) {
            echo '<div class="notice notice-error"><p>' .
                esc_html__( 'Twilio credentials are not fully configured. Provide a Twilio User SID, API SID, API Key, and either a Messaging Service SID or Sending Number in TTA Settings → API Settings or via your server environment.', 'tta' ) .
                '</p></div>';
        } elseif ( current_user_can( 'manage_options' ) && ! $sandbox_ok ) {
            echo '<div class="notice notice-warning"><p>' .
                esc_html__( 'Twilio sandbox mode is enabled but no Twilio Sandbox Number is configured. SMS messages will be skipped until a sandbox recipient is provided.', 'tta' ) .
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
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-login-register-page.php';
require_once TTA_PLUGIN_DIR . 'includes/login/class-tta-login-branding.php';
require_once TTA_PLUGIN_DIR . 'includes/api/class-authorizenet-api.php';
require_once TTA_PLUGIN_DIR . 'includes/email/class-email-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/email/class-email-customizer.php';
require_once TTA_PLUGIN_DIR . 'includes/email/class-email-reminders.php';
require_once TTA_PLUGIN_DIR . 'includes/sms/class-sms-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/sms/class-sms-reminders.php';
require_once TTA_PLUGIN_DIR . 'includes/waitlist/class-waitlist.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/class-ajax-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-partners-admin.php';
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
            TTA_Partners_Admin::get_instance();
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
        TTA_Email_Customizer::init();
        TTA_Email_Handler::get_instance();
        TTA_SMS_Handler::get_instance();
        TTA_SMS_Reminders::init();
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