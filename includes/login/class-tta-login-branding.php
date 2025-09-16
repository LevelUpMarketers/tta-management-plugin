<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Login_Branding {
    /**
     * Bootstraps the login page branding customizations.
     */
    public static function init() {
        add_action( 'login_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_filter( 'retrieve_password_title', [ __CLASS__, 'filter_password_reset_title' ], 10, 2 );
        add_filter( 'retrieve_password_message', [ __CLASS__, 'filter_password_reset_message' ], 10, 4 );
    }

    /**
     * Enqueue the branded stylesheet on WordPress core login screens.
     */
    public static function enqueue_assets() {
        wp_enqueue_style(
            'tta-wp-login',
            TTA_PLUGIN_URL . 'assets/css/frontend/wp-login.css',
            [],
            TTA_PLUGIN_VERSION
        );
    }

    /**
     * Customize the password reset email subject line.
     *
     * @param string $title      Default email subject.
     * @param string $user_login User login name.
     *
     * @return string
     */
    public static function filter_password_reset_title( $title, $user_login ) {
        unset( $title, $user_login );

        $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        return sprintf( __( 'Reset Your %s Password!', 'tta' ), $blogname );
    }

    /**
     * Customize the password reset email body copy.
     *
     * @param string   $message    Default email message.
     * @param string   $key        Password reset key.
     * @param string   $user_login User login name.
     * @param \WP_User $user_data  User object.
     *
     * @return string
     */
    public static function filter_password_reset_message( $message, $key, $user_login, $user_data ) {
        unset( $message, $user_data );

        $reset_link = network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user_login ), 'login' );

        $lines = [
            __( 'Hi there,', 'tta' ),
            '',
            __( "Looks like you need a password reset! If you didn't request this, you can simply ignore this email.", 'tta' ),
            '',
            __( 'To reset your password, click on the link below:', 'tta' ),
            '',
            $reset_link,
            '',
            __( "We're looking forward to seeing you at the next event!", 'tta' ),
            __( '- The Trying to Adult Team', 'tta' ),
        ];

        return implode( "\n", $lines );
    }
}

TTA_Login_Branding::init();
