<?php
/**
 * Plugin Name: Trying To Adult Management Plugin
 * Plugin URI: https://example.com
 * Description: Custom plugin for Members, Events, Tickets management with waitlist, notifications, and Authorize.Net integration.
 * Version: 0.1.0
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
define( 'TTA_PLUGIN_VERSION', '0.4.1' );
define( 'TTA_DB_VERSION', '1.5.0' );
define( 'TTA_BASIC_MEMBERSHIP_PRICE', 5.00 );
define( 'TTA_PREMIUM_MEMBERSHIP_PRICE', 10.00 );
define( 'TTA_BASIC_SUBSCRIPTION_NAME', 'Trying to Adult Basic Membership' );
define( 'TTA_PREMIUM_SUBSCRIPTION_NAME', 'Trying to Adult Premium Membership' );
define( 'TTA_BASIC_SUBSCRIPTION_DESCRIPTION', 'Monthly Basic Membership subscription for Trying to Adult.' );
define( 'TTA_PREMIUM_SUBSCRIPTION_DESCRIPTION', 'Monthly Premium Membership subscription for Trying to Adult.' );

require_once TTA_PLUGIN_DIR . 'includes/classes/class-tta-debug-logger.php';
TTA_Debug_Logger::init();
require_once TTA_PLUGIN_DIR . 'includes/classes/class-tta-tooltips.php';

// Load Authorize.Net credentials from a config file if present
$config_file = TTA_PLUGIN_DIR . 'authnet-config.php';
if ( file_exists( $config_file ) ) {
    include_once $config_file;
}


// Attempt to load Authorize.Net credentials from environment variables
if ( getenv( 'TTA_AUTHNET_LOGIN_ID' ) && ! defined( 'TTA_AUTHNET_LOGIN_ID' ) ) {
    define( 'TTA_AUTHNET_LOGIN_ID', getenv( 'TTA_AUTHNET_LOGIN_ID' ) );
}
if ( getenv( 'TTA_AUTHNET_TRANSACTION_KEY' ) && ! defined( 'TTA_AUTHNET_TRANSACTION_KEY' ) ) {
    define( 'TTA_AUTHNET_TRANSACTION_KEY', getenv( 'TTA_AUTHNET_TRANSACTION_KEY' ) );
}
if ( ! defined( 'TTA_AUTHNET_SANDBOX' ) ) {
    $sandbox = getenv( 'TTA_AUTHNET_SANDBOX' );
    define( 'TTA_AUTHNET_SANDBOX', $sandbox ? ( 'true' === strtolower( $sandbox ) ) : true );
}

// Warn administrators if Authorize.Net credentials are missing
if ( is_admin() ) {
    add_action( 'admin_notices', function () {
        if ( current_user_can( 'manage_options' ) && ( ! defined( 'TTA_AUTHNET_LOGIN_ID' ) || ! defined( 'TTA_AUTHNET_TRANSACTION_KEY' ) ) ) {
            echo '<div class="notice notice-error"><p>' .
                esc_html__( 'Authorize.Net credentials are not configured. Define TTA_AUTHNET_LOGIN_ID and TTA_AUTHNET_TRANSACTION_KEY in authnet-config.php or your server environment.', 'tta' ) .
                '</p></div>';
        }
    } );
}

// Load Composer autoloader if present
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
require_once TTA_PLUGIN_DIR . 'includes/helpers.php';

// Core includes
require_once TTA_PLUGIN_DIR . 'includes/class-db-setup.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-event-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-cart-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-checkout-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-events-list-page.php';
require_once TTA_PLUGIN_DIR . 'includes/api/class-authorizenet-api.php';
require_once TTA_PLUGIN_DIR . 'includes/email/class-email-handler.php';
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
require_once TTA_PLUGIN_DIR . 'includes/shortcodes/class-events-shortcode.php';
require_once TTA_PLUGIN_DIR . 'includes/shortcodes/class-members-shortcode.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-tta-member-dashboard.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-tta-checkin-page-manager.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-become-member-page.php';
require_once TTA_PLUGIN_DIR . 'includes/cart/class-cart.php';
require_once TTA_PLUGIN_DIR . 'includes/cart/class-cart-cleanup.php';
require_once TTA_PLUGIN_DIR . 'includes/classes/class-tta-event-archiver.php';
require_once TTA_PLUGIN_DIR . 'includes/database-testing/class-tta-sample-data.php';



// Activation & Deactivation
register_activation_hook( __FILE__, array( 'TTA_DB_Setup', 'install' ) );
register_activation_hook( __FILE__, array( 'TTA_Cart_Cleanup', 'schedule_event' ) );
register_activation_hook( __FILE__, array( 'TTA_Event_Archiver', 'schedule_event' ) );
register_deactivation_hook( __FILE__, array( 'TTA_DB_Setup', 'uninstall' ) );
register_deactivation_hook( __FILE__, array( 'TTA_Cart_Cleanup', 'clear_event' ) );
register_deactivation_hook( __FILE__, array( 'TTA_Event_Archiver', 'clear_event' ) );

// Initialize plugin
add_action( 'plugins_loaded', array( 'TTA_Plugin', 'init' ) );

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
        } else {
            // Frontend shortcodes
            TTA_Events_Shortcode::get_instance();
            TTA_Members_Shortcode::get_instance();
        }

        // Notification handlers
        TTA_Email_Handler::get_instance();
        TTA_SMS_Handler::get_instance();

        // Expired cart cleanup
        TTA_Cart_Cleanup::init();
        TTA_Event_Archiver::init();
    }
}
?>
