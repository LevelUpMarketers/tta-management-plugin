<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Customizes default WordPress email headers for the plugin.
 */
class TTA_Email_Customizer {
    /**
     * Register hooks.
     */
    public static function init() {
        add_filter( 'wp_mail_from_name', [ __CLASS__, 'from_name' ] );
        add_filter( 'wp_mail_from', [ __CLASS__, 'from_email' ] );
        add_filter( 'wp_mail', [ __CLASS__, 'add_bcc' ] );
    }

    /**
     * Set the default sender name.
     *
     * @return string Sender name.
     */
    public static function from_name() {
        return 'Trying To Adult';
    }

    /**
     * Set the default sender email address.
     *
     * @param string $email Original email address.
     * @return string Sender email address.
     */
    public static function from_email( $email ) {
        return sanitize_email( 'noreply@tryingtoadultrva.com' );
    }

    /**
     * Add a Bcc header to every outgoing email.
     *
     * @param array $args Mail arguments.
     * @return array Modified mail arguments.
     */
    public static function add_bcc( $args ) {
        $bcc     = sanitize_email( 'onlineservices@leveluprichmond.com' );
        $headers = $args['headers'] ?? [];

        if ( is_string( $headers ) && '' !== $headers ) {
            $headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        } elseif ( ! is_array( $headers ) ) {
            $headers = [];
        }

        $headers[]       = 'Bcc: ' . $bcc;
        $args['headers'] = $headers;

        return $args;
    }
}
