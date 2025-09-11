<?php
use PHPUnit\Framework\TestCase;

class MembershipProbeTest extends TestCase {
    protected function setUp(): void {
        if ( session_status() === PHP_SESSION_NONE ) {
            session_start();
        }
        $_SESSION = [];
        $_POST    = [];
        if ( ! defined( 'ABSPATH' ) ) {
            define( 'ABSPATH', sys_get_temp_dir() . '/wp/' );
        }
        if ( ! function_exists( '__' ) ) {
            function __( $s, $d = null ) { return $s; }
        }
        if ( ! function_exists( 'check_ajax_referer' ) ) {
            function check_ajax_referer( $a, $b ) {}
        }
        if ( ! function_exists( 'sanitize_text_field' ) ) {
            function sanitize_text_field( $v ) { return is_string( $v ) ? $v : ''; }
        }
        if ( ! function_exists( 'sanitize_email' ) ) {
            function sanitize_email( $v ) { return $v; }
        }
        if ( ! function_exists( 'tta_sanitize_text_field' ) ) {
            function tta_sanitize_text_field( $v ) { return sanitize_text_field( $v ); }
        }
        if ( ! function_exists( 'tta_get_membership_price' ) ) {
            function tta_get_membership_price( $level ) { return 10; }
        }
        if ( ! function_exists( 'tta_get_membership_label' ) ) {
            function tta_get_membership_label( $level ) { return ucfirst( $level ); }
        }
        if ( ! function_exists( 'tta_build_order_description' ) ) {
            function tta_build_order_description() { return 'Order'; }
        }
        if ( ! function_exists( 'wp_send_json_error' ) ) {
            function wp_send_json_error( $data = null ) { $GLOBALS['_last_json'] = [ 'success' => false, 'data' => $data ]; throw new Exception( 'json_error' ); }
        }
        if ( ! function_exists( 'wp_send_json_success' ) ) {
            function wp_send_json_success( $data = null ) { $GLOBALS['_last_json'] = [ 'success' => true, 'data' => $data ]; throw new Exception( 'json_success' ); }
        }
        if ( ! defined( 'TTA_PREMIUM_SUBSCRIPTION_NAME' ) ) {
            define( 'TTA_PREMIUM_SUBSCRIPTION_NAME', 'Premium' );
        }
        if ( ! defined( 'TTA_BASIC_SUBSCRIPTION_NAME' ) ) {
            define( 'TTA_BASIC_SUBSCRIPTION_NAME', 'Standard' );
        }
        if ( ! class_exists( 'TTA_Cart' ) ) {
            class TTA_Cart {
                public function get_total( $d, $f ) { return 0; }
                public function empty_cart() {}
            }
        }
        if ( ! class_exists( 'TTA_AuthorizeNet_API' ) ) {
            class TTA_AuthorizeNet_API {
                public static $instance;
                public static $probe_response = [ 'success' => true ];
                public $charge_called = false;
                public function __construct() { self::$instance = $this; }
                public function probe_subscription( $a, $b, $c ) { return self::$probe_response; }
                public function charge( $a, $b, $c, $d, $e ) { $this->charge_called = true; return [ 'success' => true, 'transaction_id' => 't' ]; }
            }
        }
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $rows_affected = 1;
            public $deleted = false;
            public function prepare( $q, ...$a ) { return $q; }
            public function query( $q ) {}
            public function get_var( $q ) { return null; }
            public function delete( $table, $where, $format ) { $this->deleted = true; }
        };
        require_once __DIR__ . '/../includes/ajax/handlers/class-ajax-checkout.php';
    }

    public function test_duplicate_probe_aborts_checkout() {
        TTA_AuthorizeNet_API::$probe_response = [ 'success' => false, 'error_code' => 'E00012', 'error' => 'dup' ];
        $_POST = [
            'nonce' => 'x',
            'checkout_key' => 'k',
            'opaqueData' => [ 'dataDescriptor' => 'd', 'dataValue' => 'v' ],
            'billing' => [ 'first_name' => 'a', 'last_name' => 'b', 'email' => 'e', 'address' => 'a', 'city' => 'c', 'state' => 's', 'zip' => '1' ],
        ];
        $_SESSION['tta_membership_purchase'] = 'premium';
        try { TTA_Ajax_Checkout::checkout(); } catch ( Exception $e ) {}
        $this->assertFalse( TTA_AuthorizeNet_API::$instance->charge_called );
        $this->assertTrue( $GLOBALS['wpdb']->deleted );
        $this->assertSame( 'Duplicate membership detected.', $GLOBALS['_last_json']['data']['message'] );
    }

    public function test_generic_probe_failure_aborts_checkout() {
        TTA_AuthorizeNet_API::$probe_response = [ 'success' => false, 'error_code' => 'E999', 'error' => 'boom' ];
        global $wpdb; $wpdb->deleted = false;
        $_POST = [
            'nonce' => 'x',
            'checkout_key' => 'k2',
            'opaqueData' => [ 'dataDescriptor' => 'd', 'dataValue' => 'v' ],
            'billing' => [ 'first_name' => 'a', 'last_name' => 'b', 'email' => 'e', 'address' => 'a', 'city' => 'c', 'state' => 's', 'zip' => '1' ],
        ];
        $_SESSION['tta_membership_purchase'] = 'premium';
        try { TTA_Ajax_Checkout::checkout(); } catch ( Exception $e ) {}
        $this->assertFalse( TTA_AuthorizeNet_API::$instance->charge_called );
        $this->assertTrue( $wpdb->deleted );
        $this->assertSame( "We're sorry! Looks like there's been some kind of general error with your transaction. Please log out, log back in, and try again, making sure you have a strong Internet connection.", $GLOBALS['_last_json']['data']['message'] );
    }
}
?>
