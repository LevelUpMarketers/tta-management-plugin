<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Capture PHP errors, warnings, notices, and exceptions for debugging.
 */
class TTA_Debug_Logger {
    /** @var array Cached log messages */
    protected static $messages = [];

    /**
     * Initialize error, exception, and shutdown handlers.
     */
    public static function init() {
        set_error_handler( [ __CLASS__, 'handle_error' ] );
        set_exception_handler( [ __CLASS__, 'handle_exception' ] );
        register_shutdown_function( [ __CLASS__, 'handle_shutdown' ] );
    }

    /**
     * Handle PHP errors.
     */
    public static function handle_error( $errno, $errstr, $errfile, $errline ) {
        self::log( sprintf( 'PHP %s: %s in %s on line %d', self::friendly_severity( $errno ), $errstr, $errfile, $errline ) );
        return false; // let PHP continue normal handling
    }

    /**
     * Handle uncaught exceptions.
     */
    public static function handle_exception( $exception ) {
        self::log( sprintf( 'Uncaught Exception: %s in %s on line %d', $exception->getMessage(), $exception->getFile(), $exception->getLine() ) );
    }

    /**
     * Capture fatal errors on shutdown.
     */
    public static function handle_shutdown() {
        $error = error_get_last();
        if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ], true ) ) {
            self::log( sprintf( 'Fatal Error: %s in %s on line %d', $error['message'], $error['file'], $error['line'] ) );
        }
        self::persist();
    }

    /**
     * Convert PHP error severity to a readable label.
     */
    protected static function friendly_severity( $errno ) {
        $map = [
            E_ERROR             => 'Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
        ];
        return $map[ $errno ] ?? 'Unknown';
    }

    /**
     * Log a message to the option store.
     */
    public static function log( $message ) {
        $msgs   = get_option( 'tta_debug_log', [] );
        $msgs[] = '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message;
        if ( count( $msgs ) > 200 ) {
            $msgs = array_slice( $msgs, -200 );
        }
        update_option( 'tta_debug_log', $msgs, false );
        self::$messages = $msgs;
    }

    /**
     * Clear stored log messages.
     */
    public static function clear() {
        delete_option( 'tta_debug_log' );
        self::$messages = [];
    }

    /**
     * Retrieve stored log messages.
     *
     * @return array
     */
    public static function get_messages() {
        if ( empty( self::$messages ) ) {
            self::$messages = get_option( 'tta_debug_log', [] );
        }
        return self::$messages;
    }

    /**
     * Persist messages when shutting down.
     */
    protected static function persist() {
        if ( ! empty( self::$messages ) ) {
            update_option( 'tta_debug_log', self::$messages, false );
        }
    }
}
