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
     * Delete expired cart items and release their ticket inventory.
     */
    public static function clean_expired_items() {
        global $wpdb;

        $items_table  = $wpdb->prefix . 'tta_cart_items';
        $tickets_table = $wpdb->prefix . 'tta_tickets';

        $expired = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ticket_id, quantity FROM {$items_table} WHERE expires_at < %s",
                current_time( 'mysql' )
            ),
            ARRAY_A
        );

        foreach ( $expired as $row ) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$tickets_table} SET ticketlimit = ticketlimit + %d WHERE id = %d",
                    intval( $row['quantity'] ),
                    intval( $row['ticket_id'] )
                )
            );
        }

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$items_table} WHERE expires_at < %s",
                current_time( 'mysql' )
            )
        );
    }

    /**
     * Schedule cleanup events if not already scheduled.
     */
    public static function schedule_event() {
        if ( ! wp_next_scheduled( 'tta_cart_cleanup_event' ) ) {
            wp_schedule_event( time(), 'hourly', 'tta_cart_cleanup_event' );
        }
        if ( ! wp_next_scheduled( 'tta_cart_item_cleanup_event' ) ) {
            wp_schedule_event( time(), 'tta_ten_minutes', 'tta_cart_item_cleanup_event' );
        }
    }

    /**
     * Clear the scheduled cleanup on plugin deactivation.
     */
    public static function clear_event() {
        wp_clear_scheduled_hook( 'tta_cart_cleanup_event' );
        wp_clear_scheduled_hook( 'tta_cart_item_cleanup_event' );
    }

    /**
     * Init hooks.
     */
    public static function init() {
        add_action( 'tta_checkout_complete', [ __CLASS__, 'clean_expired_carts' ] );
        add_action( 'tta_cart_cleanup_event', [ __CLASS__, 'clean_expired_carts' ] );
        add_action( 'tta_cart_item_cleanup_event', [ __CLASS__, 'clean_expired_items' ] );
        add_filter( 'cron_schedules', [ __CLASS__, 'add_schedule' ] );
        self::schedule_event();
    }

    /**
     * Register a 10 minute schedule for WP cron.
     */
    public static function add_schedule( $schedules ) {
        if ( ! isset( $schedules['tta_ten_minutes'] ) ) {
            $schedules['tta_ten_minutes'] = [
                'interval' => 600,
                'display'  => 'Every Ten Minutes',
            ];
        }
        return $schedules;
    }
}
