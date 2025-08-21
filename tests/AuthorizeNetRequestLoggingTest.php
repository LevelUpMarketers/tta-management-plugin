<?php
use PHPUnit\Framework\TestCase;

class AuthorizeNetRequestLoggingTest extends TestCase {
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
        if ( ! function_exists( 'sanitize_text_field' ) ) {
            function sanitize_text_field( $str ) { return is_string( $str ) ? trim( $str ) : $str; }
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

    public function test_request_logging_masks_sensitive_data() {
        $response = $this->make_response();
        $api = new class( 'LOGINID123456', 'TRANSKEY12345678' ) extends TTA_AuthorizeNet_API {
            public $fake_response;
            protected function send_request( $controller ) {
                return $this->fake_response;
            }
        };
        $api->fake_response = $response;

        TTA_Debug_Logger::clear();
        $api->charge( 10.0, '4111111111111111', '2025-12', '999', [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'address'    => '123 St',
            'city'       => 'Richmond',
            'state'      => 'VA',
            'zip'        => '23220',
        ] );

        $log = implode( "\n", TTA_Debug_Logger::get_messages() );
        $this->assertStringContainsString( 'charge request', $log );
        $this->assertStringContainsString( 'LOGI', $log );
        $this->assertStringContainsString( '5678', $log );
        $this->assertStringNotContainsString( 'LOGINID123456', $log );
        $this->assertStringNotContainsString( 'TRANSKEY12345678', $log );
        $this->assertStringContainsString( '************1111', $log );
        $this->assertStringNotContainsString( '4111111111111111', $log );
        $this->assertStringContainsString( '"amount":"10.00"', $log );
        $this->assertStringContainsString( '"country":"USA"', $log );
        $this->assertStringContainsString( '[omitted]', $log );
        $this->assertStringNotContainsString( '999', $log );
        $this->assertStringContainsString( 'John', $log );
        $this->assertStringContainsString( 'Card expired', $log );
    }
}
