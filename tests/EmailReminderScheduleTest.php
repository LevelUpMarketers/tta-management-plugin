<?php
use PHPUnit\Framework\TestCase;
if ( ! defined( 'ARRAY_A' ) ) { define( 'ARRAY_A', 'ARRAY_A' ); }

class EmailReminderScheduleTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['scheduled'] = [];
        $GLOBALS['cleared']   = [];
        if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', sys_get_temp_dir() . '/wp/' ); }
        if ( ! defined( 'DAY_IN_SECONDS' ) ) { define( 'DAY_IN_SECONDS', 86400 ); }
        if ( ! defined( 'HOUR_IN_SECONDS' ) ) { define( 'HOUR_IN_SECONDS', 3600 ); }
        if ( ! function_exists( 'wp_schedule_single_event' ) ) {
            function wp_schedule_single_event( $ts, $hook, $args = [] ) {
                $GLOBALS['scheduled'][] = [ $ts, $hook, $args ];
            }
        }
        if ( ! function_exists( 'wp_next_scheduled' ) ) {
            function wp_next_scheduled( $hook, $args = [] ) {
                return false;
            }
        }
        if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
            function wp_clear_scheduled_hook( $hook, $args = [] ) {
                $GLOBALS['cleared'][] = [ $hook, $args ];
                foreach ( $GLOBALS['scheduled'] as $i => $event ) {
                    if ( $event[1] === $hook && $event[2] === $args ) {
                        unset( $GLOBALS['scheduled'][ $i ] );
                    }
                }
                $GLOBALS['scheduled'] = array_values( $GLOBALS['scheduled'] );
            }
        }
        $GLOBALS['wpdb'] = new class {
            public $prefix = '';
            public function get_var() { return 'ute_1'; }
            public function get_row() {
                return [
                    'id' => 1,
                    'name' => 'Test',
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
            public function prepare( $query, ...$args ) { return $query; }
        };
        if ( ! function_exists( 'sanitize_text_field' ) ) {
            function sanitize_text_field( $v ) { return $v; }
        }
        if ( ! function_exists( 'sanitize_textarea_field' ) ) {
            function sanitize_textarea_field( $v ) { return $v; }
        }
        if ( ! function_exists( 'esc_url_raw' ) ) {
            function esc_url_raw( $v ) { return $v; }
        }
        if ( ! function_exists( 'get_permalink' ) ) {
            function get_permalink( $id ) { return ''; }
        }
        if ( ! function_exists( 'wp_timezone' ) ) {
            function wp_timezone() { return new DateTimeZone( 'America/New_York' ); }
        }
        if ( ! function_exists( 'get_transient' ) ) {
            function get_transient( $key ) {
                return $GLOBALS['transients'][ $key ] ?? false;
            }
        }
        if ( ! function_exists( 'set_transient' ) ) {
            function set_transient( $key, $value, $expiration ) {
                $GLOBALS['transients'][ $key ] = $value;
            }
        }
        $GLOBALS['transients'] = [ 'tta_time_offset' => 0 ];
    }

    protected function tearDown(): void {
        $GLOBALS['scheduled'] = [];
        $GLOBALS['cleared']   = [];
    }

    public function test_schedule_event_emails_creates_six_events() {
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';
        TTA_Email_Reminders::schedule_event_emails( 1 );
        $this->assertCount( 6, $GLOBALS['scheduled'] );
        $hooks = array_column( $GLOBALS['scheduled'], 1 );
        $this->assertContains( 'tta_attendee_reminder_email', $hooks );
        $this->assertContains( 'tta_host_reminder_email', $hooks );
        $this->assertContains( 'tta_volunteer_reminder_email', $hooks );
    }

    public function test_reminders_use_site_timezone() {
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';
        TTA_Email_Reminders::schedule_event_emails( 1 );
        $timestamp = null;
        foreach ( $GLOBALS['scheduled'] as $event ) {
            if ( 'tta_attendee_reminder_email' === $event[1] && 'reminder_24hr' === $event[2][1] ) {
                $timestamp = $event[0];
                break;
            }
        }
        $this->assertNotNull( $timestamp );
        $expected = ( new DateTime( '2030-08-14 18:00', wp_timezone() ) )->setTimezone( new DateTimeZone( 'UTC' ) )->getTimestamp();
        $this->assertSame( $expected, $timestamp );
    }

    public function test_schedule_ignores_time_offset_transient() {
        // Simulate an offset transient which should now be ignored.
        $GLOBALS['transients'] = [ 'tta_time_offset' => -4 * HOUR_IN_SECONDS ];
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';
        TTA_Email_Reminders::schedule_event_emails( 1 );
        $timestamp = null;
        foreach ( $GLOBALS['scheduled'] as $event ) {
            if ( 'tta_attendee_reminder_email' === $event[1] && 'reminder_24hr' === $event[2][1] ) {
                $timestamp = $event[0];
                break;
            }
        }
        $this->assertNotNull( $timestamp );
        $expected = ( new DateTime( '2030-08-14 18:00', wp_timezone() ) )->setTimezone( new DateTimeZone( 'UTC' ) )->getTimestamp();
        $this->assertSame( $expected, $timestamp );
    }

    public function test_schedule_post_event_thanks_creates_event() {
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';
        TTA_Email_Reminders::schedule_post_event_thanks( 1 );
        $this->assertCount( 1, $GLOBALS['scheduled'] );
        $this->assertSame( 'tta_post_event_thanks_email', $GLOBALS['scheduled'][0][1] );
    }

    public function test_clear_event_emails_removes_events() {
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';
        TTA_Email_Reminders::schedule_event_emails( 1 );
        TTA_Email_Reminders::schedule_post_event_thanks( 1 );
        TTA_Email_Reminders::clear_event_emails( 1 );
        $hooks = [];
        foreach ( $GLOBALS['cleared'] as $entry ) {
            $hooks[] = is_array( $entry ) ? $entry[0] : $entry;
        }
        $this->assertContains( 'tta_attendee_reminder_email', $hooks );
        $this->assertContains( 'tta_host_reminder_email', $hooks );
        $this->assertContains( 'tta_volunteer_reminder_email', $hooks );
        $this->assertContains( 'tta_post_event_thanks_email', $hooks );
    }
}
