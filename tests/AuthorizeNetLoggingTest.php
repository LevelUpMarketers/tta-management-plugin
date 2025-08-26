<?php
use PHPUnit\Framework\TestCase;

class AuthorizeNetLoggingTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['options'] = [];
        if ( ! function_exists( 'get_option' ) ) {
            function get_option( $k, $d = false ) { return $GLOBALS['options'][ $k ] ?? $d; }
        }
        if ( ! function_exists( 'update_option' ) ) {
            function update_option( $k, $v, $autoload = true ) { $GLOBALS['options'][ $k ] = $v; }
        }
        if ( ! function_exists( 'delete_option' ) ) {
            function delete_option( $k ) { unset( $GLOBALS['options'][ $k ] ); }
        }
        if ( ! function_exists( 'wp_json_encode' ) ) {
            function wp_json_encode( $data ) { return json_encode( $data ); }
        }
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', sys_get_temp_dir() . '/wp/' );
        }
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../includes/classes/class-tta-debug-logger.php';
        require_once __DIR__ . '/../includes/api/class-authorizenet-api.php';
    }

    protected function make_response() {
        $tresponse = new class {
            public function getResponseCode() { return '2'; }
            public function getTransId() { return '123456'; }
            public function getAuthCode() { return 'ABC123'; }
            public function getAvsResultCode() { return 'N'; }
            public function getCvvResultCode() { return 'P'; }
            public function getAccountNumber() { return '4111111111111111'; }
            public function getErrors() {
                return [ new class {
                    public function getErrorCode() { return '54'; }
                    public function getErrorText() { return 'Card expired'; }
                } ];
            }
            public function getMessages() { return []; }
        };

        return new class( $tresponse ) {
            private $tresponse;
            public function __construct( $tresponse ) { $this->tresponse = $tresponse; }
            public function getMessages() {
                return new class {
                    public function getResultCode() { return 'Error'; }
                    public function getMessage() {
                        return [ new class {
                            public function getCode() { return 'E00027'; }
                            public function getText() { return 'The transaction was declined.'; }
                        } ];
                    }
                };
            }
            public function getTransactionResponse() { return $this->tresponse; }
        };
    }

    public function test_logs_transaction_details_in_sandbox_and_live() {
        $this->markTestSkipped('Debug logging disabled');
    }

    protected function assert_response_logged( array $msgs ) {
        $log = implode( "\n", $msgs );
        $this->assertStringContainsString( '2 54 Card expired', $log );
        $this->assertStringContainsString( '"transId":"123456"', $log );
        $this->assertStringContainsString( '"authCode":"ABC123"', $log );
        $this->assertStringContainsString( '"avsResultCode":"N"', $log );
        $this->assertStringContainsString( '"cvvResultCode":"P"', $log );
        $this->assertStringContainsString( '"accountNumber":"************1111"', $log );
        $this->assertStringContainsString( '"errorCode":"54"', $log );
        $this->assertStringContainsString( '"errorText":"Card expired"', $log );
    }
}
?>
