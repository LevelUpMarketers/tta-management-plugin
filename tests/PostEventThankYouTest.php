<?php
use PHPUnit\Framework\TestCase;
if ( ! defined( 'ARRAY_A' ) ) { define( 'ARRAY_A', 'ARRAY_A' ); }

class PostEventThankYouTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['mails'] = [];
        if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', sys_get_temp_dir() . '/wp/' ); }
        if ( ! function_exists( 'wp_mail' ) ) {
            function wp_mail( $to, $sub, $body, $headers = [] ) {
                $GLOBALS['mails'][] = [ $to, $sub ];
            }
        }
        if ( ! function_exists( 'tta_get_event_ute_id' ) ) {
            function tta_get_event_ute_id( $event_id ) { return 'ute'; }
        }
        if ( ! function_exists( 'tta_get_event_for_email' ) ) {
            function tta_get_event_for_email( $ute ) {
                return [ 'id' => 1, 'name' => 'Event', 'date' => '2030-08-15', 'time' => '18:00|20:00' ];
            }
        }
        if ( ! function_exists( 'tta_get_event_attendees_with_status' ) ) {
            function tta_get_event_attendees_with_status( $ute ) {
                return [
                    [ 'email' => 'a@test.com', 'first_name' => 'A', 'last_name' => 'One', 'status' => 'checked_in' ],
                    [ 'email' => 'b@test.com', 'first_name' => 'B', 'last_name' => 'Two', 'status' => 'pending' ],
                ];
            }
        }
        if ( ! function_exists( 'tta_get_comm_templates' ) ) {
            function tta_get_comm_templates() {
                return [ 'post_event_review' => [ 'email_subject' => 'Thanks {first_name}', 'email_body' => 'Review {event_name}' ] ];
            }
        }
        if ( ! function_exists( 'sanitize_email' ) ) {
            function sanitize_email( $v ) { return $v; }
        }
        if ( ! function_exists( 'sanitize_text_field' ) ) {
            function sanitize_text_field( $v ) { return $v; }
        }
        if ( ! function_exists( 'home_url' ) ) {
            function home_url( $p = '' ) { return ''; }
        }
        if ( ! function_exists( 'esc_url' ) ) {
            function esc_url( $v ) { return $v; }
        }
        if ( ! function_exists( 'tta_get_event_host_volunteer_names' ) ) {
            function tta_get_event_host_volunteer_names( $id ) { return [ 'hosts' => [], 'volunteers' => [] ]; }
        }
        if ( ! function_exists( 'tta_unslash' ) ) {
            function tta_unslash( $v ) { return $v; }
        }
        if ( ! function_exists( 'tta_format_event_date' ) ) {
            function tta_format_event_date( $d ) { return $d; }
        }
        if ( ! function_exists( 'tta_format_event_time' ) ) {
            function tta_format_event_time( $t ) { return $t; }
        }
        $GLOBALS['wpdb'] = new class {
            public $prefix = '';
            public function get_var() { return 'ute'; }
            public function get_row() {
                return [
                    'id' => 1,
                    'name' => 'Event',
                    'date' => '2030-08-15',
                    'time' => '18:00|20:00',
                    'address' => '',
                    'page_id' => 0,
                    'type' => '',
                    'venuename' => '',
                    'venueurl' => '',
                    'baseeventcost' => 0,
                    'discountedmembercost' => 0,
                    'premiummembercost' => 0,
                    'host_notes' => '',
                ];
            }
            public function get_results( $q, $fmt = ARRAY_A ) {
                return [
                    [ 'id' => 1, 'ticket_id' => 1, 'first_name' => 'A', 'last_name' => 'One', 'email' => 'a@test.com', 'phone' => '', 'assistance_note' => '', 'status' => 'checked_in' ],
                    [ 'id' => 2, 'ticket_id' => 1, 'first_name' => 'B', 'last_name' => 'Two', 'email' => 'b@test.com', 'phone' => '', 'assistance_note' => '', 'status' => 'pending' ],
                ];
            }
            public function prepare( $q, ...$a ) { return $q; }
        };
        if ( ! function_exists( 'tta_expand_anchor_tokens' ) ) {
            function tta_expand_anchor_tokens( $text, $tokens ) { return $text; }
        }
        if ( ! function_exists( 'tta_strip_bold' ) ) {
            function tta_strip_bold( $text ) { return $text; }
        }
        if ( ! function_exists( 'tta_convert_bold' ) ) {
            function tta_convert_bold( $text ) { return $text; }
        }
        if ( ! function_exists( 'tta_convert_links' ) ) {
            function tta_convert_links( $text ) { return $text; }
        }
        if ( ! class_exists( 'TTA_Email_Handler' ) ) {
            require_once __DIR__ . '/../includes/email/class-email-handler.php';
        }
    }

    protected function tearDown(): void {
        $GLOBALS['mails'] = [];
    }

    public function test_send_post_event_thanks_only_checked_in() {
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';
        TTA_Email_Reminders::send_post_event_thanks( 1 );
        $this->assertCount( 1, $GLOBALS['mails'] );
        $this->assertSame( 'a@test.com', $GLOBALS['mails'][0][0] );
    }
}
