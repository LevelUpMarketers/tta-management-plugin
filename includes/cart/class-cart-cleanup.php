<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper for removing expired carts from the database.
 */
class TTA_Cart_Cleanup {

    /**
     * Delete cart rows whose expires_at is in the past.
     */
    public static function clean_expired_carts() {
        global $wpdb;
        $table = $wpdb->prefix . 'tta_carts';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE expires_at < %s",
                current_time( 'mysql' )
            )
        );
    }

    /**
     * Schedule the hourly cleanup event if not already scheduled.
     */
    public static function schedule_event() {
        if ( ! wp_next_scheduled( 'tta_cart_cleanup_event' ) ) {
            wp_schedule_event( time(), 'hourly', 'tta_cart_cleanup_event' );
        }
    }

    /**
     * Clear the scheduled cleanup on plugin deactivation.
     */
    public static function clear_event() {
        wp_clear_scheduled_hook( 'tta_cart_cleanup_event' );
    }

    /**
     * Init hooks.
     */
    public static function init() {
        add_action( 'tta_checkout_complete', [ __CLASS__, 'clean_expired_carts' ] );
        add_action( 'tta_cart_cleanup_event', [ __CLASS__, 'clean_expired_carts' ] );
        self::schedule_event();
    }
}
