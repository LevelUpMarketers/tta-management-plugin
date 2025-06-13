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
define( 'TTA_PLUGIN_VERSION', '0.2.0' );

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
require_once TTA_PLUGIN_DIR . 'includes/api/class-authorizenet-api.php';
require_once TTA_PLUGIN_DIR . 'includes/email/class-email-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/sms/class-sms-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/waitlist/class-waitlist.php';
require_once TTA_PLUGIN_DIR . 'includes/ajax/class-ajax-handler.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-members-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-events-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/admin/class-tickets-admin.php';
require_once TTA_PLUGIN_DIR . 'includes/shortcodes/class-events-shortcode.php';
require_once TTA_PLUGIN_DIR . 'includes/shortcodes/class-members-shortcode.php';
require_once TTA_PLUGIN_DIR . 'includes/frontend/class-tta-member-dashboard.php';
require_once TTA_PLUGIN_DIR . 'includes/cart/class-cart.php';



// Activation & Deactivation
register_activation_hook( __FILE__, array( 'TTA_DB_Setup', 'install' ) );
register_deactivation_hook( __FILE__, array( 'TTA_DB_Setup', 'uninstall' ) );

// Initialize plugin
add_action( 'plugins_loaded', array( 'TTA_Plugin', 'init' ) );

class TTA_Plugin {
    public static function init() {

        // Load assets hooks:
        TTA_Assets::init();

        // Admin pages
        if ( is_admin() ) {
            TTA_Members_Admin::get_instance();
            TTA_Events_Admin::get_instance();
            TTA_Tickets_Admin::get_instance();
        } else {
            // Frontend shortcodes
            TTA_Events_Shortcode::get_instance();
            TTA_Members_Shortcode::get_instance();
        }

        // Notification handlers
        TTA_Email_Handler::get_instance();
        TTA_SMS_Handler::get_instance();
    }
}
?>