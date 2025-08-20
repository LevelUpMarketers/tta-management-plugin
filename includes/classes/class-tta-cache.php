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
     * Determine if caching should be bypassed.
     *
     * @return bool
     */
    protected static function disabled() {
        return function_exists( 'is_admin' ) && is_admin() &&
            function_exists( 'current_user_can' ) && current_user_can( 'manage_options' );
    }

    /**
     * Fetch a cached value by key.
     *
     * @param string $key
     * @return mixed|false
     */
    public static function get( $key ) {
        if ( self::disabled() ) {
            return false;
        }
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
        if ( self::disabled() ) {
            return true;
        }
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
     * Delete all cached values beginning with a given prefix.
     *
     * @param string $prefix Cache key prefix.
     */
    public static function delete_group( $prefix ) {
        global $wpdb;
        $base    = self::$prefix . $prefix;
        $like    = method_exists( $wpdb, 'esc_like' ) ? $wpdb->esc_like( $base ) : addcslashes( $base, '_%' );
        $pattern = $like . '%';
        $option_names = [];
        if ( method_exists( $wpdb, 'get_col' ) ) {
            $option_names = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_%' . $pattern
                )
            );
        }
        foreach ( $option_names as $option_name ) {
            $transient = substr( $option_name, strlen( '_transient_' ) );
            delete_transient( $transient );
        }
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
        if ( self::disabled() ) {
            return call_user_func( $callback );
        }
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
