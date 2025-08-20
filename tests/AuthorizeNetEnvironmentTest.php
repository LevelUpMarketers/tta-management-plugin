<?php
use PHPUnit\Framework\TestCase;

class AuthorizeNetEnvironmentTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['options'] = [];
        if ( ! function_exists( 'get_option' ) ) {
            function get_option( $k, $d = false ) { return $GLOBALS['options'][ $k ] ?? $d; }
        }
        if ( ! function_exists( 'update_option' ) ) {
            function update_option( $k, $v, $autoload = true ) { $GLOBALS['options'][ $k ] = $v; }
        }
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', sys_get_temp_dir() . '/wp/' );
        }
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../includes/api/class-authorizenet-api.php';
    }

    public function test_uses_sandbox_when_option_set() {
        update_option( 'tta_authnet_sandbox', 1 );
        $api  = new TTA_AuthorizeNet_API();
        $ref  = new ReflectionClass( $api );
        $prop = $ref->getProperty( 'environment' );
        $prop->setAccessible( true );
        $this->assertSame( \net\authorize\api\constants\ANetEnvironment::SANDBOX, $prop->getValue( $api ) );
    }

    public function test_defaults_to_live_environment() {
        update_option( 'tta_authnet_sandbox', 0 );
        $api  = new TTA_AuthorizeNet_API();
        $ref  = new ReflectionClass( $api );
        $prop = $ref->getProperty( 'environment' );
        $prop->setAccessible( true );
        $this->assertSame( \net\authorize\api\constants\ANetEnvironment::PRODUCTION, $prop->getValue( $api ) );
    }
}
?>
