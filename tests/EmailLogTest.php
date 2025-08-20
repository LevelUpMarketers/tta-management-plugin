<?php
use PHPUnit\Framework\TestCase;
if ( ! defined( 'ARRAY_A' ) ) { define( 'ARRAY_A', 'ARRAY_A' ); }

class EmailLogTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['mails']   = [];
        $GLOBALS['options'] = [];
        if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', sys_get_temp_dir() . '/wp/' ); }
        if ( ! function_exists( 'wp_mail' ) ) {
            function wp_mail( $to, $sub, $body, $headers = [] ) { $GLOBALS['mails'][] = [ $to, $sub ]; return true; }
        }
        if ( ! function_exists( 'get_option' ) ) {
            function get_option( $k, $d = false ) { return $GLOBALS['options'][ $k ] ?? $d; }
        }
        if ( ! function_exists( 'update_option' ) ) {
            function update_option( $k, $v, $autoload = true ) { $GLOBALS['options'][ $k ] = $v; }
        }
        if ( ! function_exists( 'delete_option' ) ) {
            function delete_option( $k ) { unset( $GLOBALS['options'][ $k ] ); }
        }
        $GLOBALS['wpdb'] = new class {
            public $prefix = '';
            public function get_var() { return 'ute'; }
            public function prepare( $q, $a = null, $b = null ) { return $q; }
            public function get_row() { return [ 'id' => 1, 'name' => 'Event', 'date' => '2030-08-15', 'time' => '18:00|20:00' ]; }
            public function get_results() { return []; }
        };
        if ( ! function_exists( 'tta_get_event_for_email' ) ) {
            function tta_get_event_for_email( $ute ) { return [ 'id' => 1, 'name' => 'Event', 'date' => '2030-08-15', 'time' => '18:00|20:00' ]; }
        }
        if ( ! function_exists( 'tta_get_event_attendees_with_status' ) ) {
            function tta_get_event_attendees_with_status( $ute ) { return [ [ 'email' => 'a@test.com', 'first_name' => 'A', 'last_name' => 'One', 'status' => 'checked_in' ] ]; }
        }
        if ( ! function_exists( 'tta_get_comm_templates' ) ) {
            function tta_get_comm_templates() { return [ 'reminder_24hr' => [ 'email_subject' => 'Hi {first_name}', 'email_body' => 'Body {event_name}' ] ]; }
        }
        if ( ! function_exists( 'sanitize_email' ) ) { function sanitize_email( $v ) { return $v; } }
        if ( ! function_exists( 'sanitize_text_field' ) ) { function sanitize_text_field( $v ) { return $v; } }
        if ( ! function_exists( 'esc_url_raw' ) ) { function esc_url_raw( $u ) { return $u; } }
        require_once __DIR__ . '/../includes/email/class-email-handler.php';
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';
    }

    public function test_logs_and_clears() {
        $ref = new ReflectionClass( 'TTA_Email_Reminders' );
        $m   = $ref->getMethod( 'log_email' );
        $m->setAccessible( true );
        $m->invoke( null, 1, 'reminder_24hr', 'a@test.com', true );
        $log = get_option( 'tta_email_log', [] );
        $this->assertCount( 1, $log );
        $this->assertSame( 'a@test.com', $log[0]['recipient'] );
        TTA_Email_Reminders::clear_email_log();
        $this->assertEmpty( get_option( 'tta_email_log', [] ) );
    }
}
?>
