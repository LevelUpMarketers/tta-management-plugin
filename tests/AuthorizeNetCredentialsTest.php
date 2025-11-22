<?php
use PHPUnit\Framework\TestCase;

class AuthorizeNetCredentialsTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['options'] = [];
        if ( ! function_exists( 'get_option' ) ) {
            function get_option( $k, $d = false ) { return $GLOBALS['options'][ $k ] ?? $d; }
        }
        if ( ! function_exists( 'update_option' ) ) {
            function update_option( $k, $v, $autoload = true ) { $GLOBALS['options'][ $k ] = $v; }
        }
        if ( ! function_exists( 'add_action' ) ) {
            function add_action( $hook, $func ) { /* no-op for tests */ }
        }
        if ( ! function_exists( 'add_filter' ) ) {
            function add_filter( $hook, $func ) { /* no-op for tests */ }
        }
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', sys_get_temp_dir() . '/wp/' );
        }
        require_once __DIR__ . '/../includes/helpers.php';
    }

    public function test_fetches_credentials_for_each_environment() {
        update_option( 'tta_authnet_login_id_live', 'liveLogin' );
        update_option( 'tta_authnet_transaction_key_live', 'liveKey' );
        update_option( 'tta_authnet_public_client_key_live', 'liveClient' );
        update_option( 'tta_authnet_login_id_sandbox', 'sandLogin' );
        update_option( 'tta_authnet_transaction_key_sandbox', 'sandKey' );
        update_option( 'tta_authnet_public_client_key_sandbox', 'sandClient' );

        update_option( 'tta_authnet_sandbox', 0 );
        $creds = tta_get_authnet_credentials();
        $this->assertSame( 'liveLogin', $creds['login_id'] );
        $this->assertSame( 'liveKey', $creds['transaction_key'] );
        $this->assertSame( 'liveClient', $creds['client_key'] );

        update_option( 'tta_authnet_sandbox', 1 );
        $creds = tta_get_authnet_credentials();
        $this->assertSame( 'sandLogin', $creds['login_id'] );
        $this->assertSame( 'sandKey', $creds['transaction_key'] );
        $this->assertSame( 'sandClient', $creds['client_key'] );

        update_option( 'tta_authnet_use_sandbox', 1 );
        update_option( 'tta_authnet_sandbox', 0 );
        $creds = tta_get_authnet_credentials();
        $this->assertSame( 'sandLogin', $creds['login_id'] );

        update_option( 'tta_authnet_use_sandbox', 0 );
        $creds = tta_get_authnet_credentials();
        $this->assertSame( 'liveLogin', $creds['login_id'] );

        // Explicit parameter overrides option
        $creds = tta_get_authnet_credentials( false );
        $this->assertSame( 'liveLogin', $creds['login_id'] );
        $this->assertSame( 'liveClient', $creds['client_key'] );
    }

    public function test_uses_environment_variables_when_options_are_missing() {
        putenv( 'TTA_AUTHNET_LOGIN_ID=envLogin' );
        putenv( 'TTA_AUTHNET_TRANSACTION_KEY=envKey' );
        putenv( 'TTA_AUTHNET_CLIENT_KEY=envClient' );

        $creds = tta_get_authnet_credentials( false );

        $this->assertSame( 'envLogin', $creds['login_id'] );
        $this->assertSame( 'envKey', $creds['transaction_key'] );
        $this->assertSame( 'envClient', $creds['client_key'] );

        // Clear environment variables to avoid cross-test pollution.
        putenv( 'TTA_AUTHNET_LOGIN_ID' );
        putenv( 'TTA_AUTHNET_TRANSACTION_KEY' );
        putenv( 'TTA_AUTHNET_CLIENT_KEY' );
    }

    public function test_does_not_mix_partial_option_credentials_with_constants() {
        // Only provide login ID in options; other values must not be filled from constants.
        update_option( 'tta_authnet_login_id_live', 'liveOnlyLogin' );
        putenv( 'TTA_AUTHNET_TRANSACTION_KEY=envKey' );
        putenv( 'TTA_AUTHNET_CLIENT_KEY=envClient' );

        $creds = tta_get_authnet_credentials( false );

        $this->assertSame( 'liveOnlyLogin', $creds['login_id'] );
        $this->assertSame( '', $creds['transaction_key'], 'Partial option values should not be combined with constants' );
        $this->assertSame( '', $creds['client_key'], 'Partial option values should not be combined with constants' );

        putenv( 'TTA_AUTHNET_TRANSACTION_KEY' );
        putenv( 'TTA_AUTHNET_CLIENT_KEY' );
    }
}
?>
