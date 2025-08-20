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
        $response = $this->make_response();

        update_option( 'tta_authnet_sandbox', 1 );
        $api  = new class extends TTA_AuthorizeNet_API {
            public function log_response_public( $context, $response ) { $this->log_response( $context, $response ); }
        };
        TTA_Debug_Logger::clear();
        $api->log_response_public( 'charge', $response );
        $msgs = TTA_Debug_Logger::get_messages();
        $this->assertTrue( $this->contains_decline_detail( $msgs ) );

        update_option( 'tta_authnet_sandbox', 0 );
        $apiLive = new class extends TTA_AuthorizeNet_API {
            public function log_response_public( $context, $response ) { $this->log_response( $context, $response ); }
        };
        TTA_Debug_Logger::clear();
        $apiLive->log_response_public( 'charge', $response );
        $msgsLive = TTA_Debug_Logger::get_messages();
        $this->assertTrue( $this->contains_decline_detail( $msgsLive ) );
    }

    protected function contains_decline_detail( array $msgs ) {
        foreach ( $msgs as $m ) {
            if ( strpos( $m, '2 54 Card expired' ) !== false ) {
                return true;
            }
        }
        return false;
    }
}
?>
