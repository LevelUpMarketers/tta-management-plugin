<?php
/**
 * Simple object caching wrapper based on WordPress transients.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Cache {
    protected static $prefix = 'tta_cache_';

    /**
     * Fetch a cached value by key.
     *
     * @param string $key
     * @return mixed|false
     */
    public static function get( $key ) {
        return get_transient( self::$prefix . $key );
    }

    /**
     * Store a value in the cache.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl    Time to live in seconds. 0 for default (no expiry).
     * @return bool
     */
    public static function set( $key, $value, $ttl = 0 ) {
        return set_transient( self::$prefix . $key, $value, $ttl );
    }

    /**
     * Delete a cached value.
     *
     * @param string $key
     * @return bool
     */
    public static function delete( $key ) {
        return delete_transient( self::$prefix . $key );
    }

    /**
     * Fetch a cached value or compute it using a callback.
     *
     * @param string   $key
     * @param callable $callback
     * @param int      $ttl Time to live in seconds.
     * @return mixed
     */
    public static function remember( $key, callable $callback, $ttl = 0 ) {
        $value = self::get( $key );
        if ( false === $value ) {
            $value = call_user_func( $callback );
            self::set( $key, $value, $ttl );
        }
        return $value;
    }

    /**
     * Flush all plugin caches.
     */
    public static function flush() {
        global $wpdb;
        $pattern = $wpdb->esc_like( self::$prefix ) . '%';

        $option_names = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_%' . $pattern
            )
        );

        foreach ( $option_names as $option_name ) {
            $transient = substr( $option_name, strlen( '_transient_' ) );
            delete_transient( $transient );
        }
    }
}
