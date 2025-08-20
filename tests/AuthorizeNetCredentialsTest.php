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
        update_option( 'tta_authnet_login_id_sandbox', 'sandLogin' );
        update_option( 'tta_authnet_transaction_key_sandbox', 'sandKey' );

        update_option( 'tta_authnet_sandbox', 0 );
        $creds = tta_get_authnet_credentials();
        $this->assertSame( 'liveLogin', $creds['login_id'] );
        $this->assertSame( 'liveKey', $creds['transaction_key'] );

        update_option( 'tta_authnet_sandbox', 1 );
        $creds = tta_get_authnet_credentials();
        $this->assertSame( 'sandLogin', $creds['login_id'] );
        $this->assertSame( 'sandKey', $creds['transaction_key'] );

        // Explicit parameter overrides option
        $creds = tta_get_authnet_credentials( false );
        $this->assertSame( 'liveLogin', $creds['login_id'] );
    }
}
?>
