<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Store and retrieve Authorize.Net gateway diagnostics.
 */
class TTA_Gateway_Diagnostics {
    const OPTION_ENABLED = 'tta_gateway_logging_enabled';
    const OPTION_LOG     = 'tta_gateway_diagnostic_log';
    const MAX_ENTRIES    = 200;

    /**
     * Check if diagnostics logging is enabled.
     *
     * @return bool
     */
    public static function is_enabled() {
        return (bool) get_option( self::OPTION_ENABLED, false );
    }

    /**
     * Persist logging state.
     *
     * @param bool $enabled Whether logging should be enabled.
     * @return void
     */
    public static function set_enabled( $enabled ) {
        update_option( self::OPTION_ENABLED, $enabled ? 1 : 0, false );
    }

    /**
     * Record a diagnostic entry.
     *
     * @param array $entry Diagnostic data to store.
     * @return void
     */
    public static function record( array $entry ) {
        if ( ! self::is_enabled() ) {
            return;
        }

        $entry['timestamp'] = $entry['timestamp'] ?? gmdate( 'Y-m-d H:i:s' );
        $log                = get_option( self::OPTION_LOG, [] );
        if ( ! is_array( $log ) ) {
            $log = [];
        }

        $log[] = self::sanitize_entry( $entry );

        if ( count( $log ) > self::MAX_ENTRIES ) {
            $log = array_slice( $log, -self::MAX_ENTRIES );
        }

        update_option( self::OPTION_LOG, $log, false );
    }

    /**
     * Retrieve stored diagnostics.
     *
     * @param int $limit Optional number of entries to return.
     * @return array
     */
    public static function get_entries( $limit = 0 ) {
        $log = get_option( self::OPTION_LOG, [] );
        if ( ! is_array( $log ) ) {
            return [];
        }

        $log = array_values( $log );
        if ( $limit > 0 ) {
            $log = array_slice( $log, -absint( $limit ) );
        }

        return array_reverse( $log );
    }

    /**
     * Clear all stored diagnostics.
     *
     * @return void
     */
    public static function clear() {
        delete_option( self::OPTION_LOG );
    }

    /**
     * Ensure the entry data only contains serializable values.
     *
     * @param mixed $value Entry value.
     * @return mixed
     */
    protected static function sanitize_entry( $value ) {
        if ( is_array( $value ) ) {
            $clean = [];
            foreach ( $value as $key => $item ) {
                $clean[ $key ] = self::sanitize_entry( $item );
            }
            return $clean;
        }

        if ( is_object( $value ) ) {
            if ( $value instanceof \DateTimeInterface ) {
                return $value->format( 'c' );
            }
            return self::sanitize_entry( json_decode( wp_json_encode( $value ), true ) );
        }

        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_scalar( $value ) || null === $value ) {
            return $value;
        }

        return (string) maybe_serialize( $value );
    }
}
