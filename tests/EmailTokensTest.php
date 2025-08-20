<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A', 'ARRAY_A'); }

class EmailTokensTest extends TestCase {
    protected function setUp(): void {
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field($v){ return is_string($v)?trim($v):$v; }
        }
        if (!function_exists('sanitize_email')) {
            function sanitize_email($v){ return trim($v); }
        }
        if (!function_exists('home_url')) {
            function home_url($p='', $t='relative'){ return '/'.ltrim($p,'/'); }
        }
        if (!function_exists('date_i18n')) {
            function date_i18n($f,$ts){ return date($f,$ts); }
        }
        if (!function_exists('esc_url')) {
            function esc_url($v){ return $v; }
        }
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/email/class-email-handler.php';
    }

    public function test_build_tokens_includes_refund_event_name() {
        $handler = TTA_Email_Handler::get_instance();
        $reflect = new \ReflectionClass($handler);
        $method = $reflect->getMethod('build_tokens');
        $method->setAccessible(true);

        $event = [ 'name' => 'Sample Event', 'date' => '2025-06-30', 'time' => '18:00|20:00' ];
        $member = [ 'first_name' => 'Bob', 'last_name' => 'Smith', 'user_email' => 'bob@example.com',
                    'member' => [ 'phone' => '', 'member_type' => '' ], 'membership_level' => '' ];
        $attendees = [];
        $refund = [ 'ticket_name' => 'General', 'amount' => '10.00' ];

        $tokens = $method->invoke($handler, $event, $member, $attendees, $refund);

        $this->assertArrayHasKey('{refund_event_name}', $tokens);
        $this->assertSame('Sample Event', $tokens['{refund_event_name}']);
    }

    public function test_tokens_change_with_attendee_order() {
        $handler = TTA_Email_Handler::get_instance();
        $ref = new \ReflectionClass($handler);
        $method = $ref->getMethod('build_tokens');
        $method->setAccessible(true);

        $event = [ 'name' => 'Event', 'date' => '2025-06-30', 'time' => '18:00|20:00' ];
        $member = [ 'first_name' => 'Buyer', 'last_name' => 'Name', 'user_email' => 'buyer@example.com',
                    'member' => [ 'phone' => '' ], 'membership_level' => '' ];
        $attendees = [
            [ 'first_name' => 'Tucker', 'last_name' => '', 'email' => 'tucker@example.com', 'phone' => '' ],
            [ 'first_name' => 'Jill', 'last_name' => '', 'email' => 'jill@example.com', 'phone' => '' ],
        ];

        $tokens1 = $method->invoke($handler, $event, $member, $attendees);
        $ordered = array_reverse($attendees);
        $tokens2 = $method->invoke($handler, $event, $member, $ordered);

        $this->assertSame('Tucker', $tokens1['{attendee_first_name}']);
        $this->assertSame('Jill', $tokens2['{attendee_first_name}']);
    }

    public function test_build_tokens_includes_event_address_link() {
        $handler = TTA_Email_Handler::get_instance();
        $ref = new \ReflectionClass($handler);
        $method = $ref->getMethod('build_tokens');
        $method->setAccessible(true);

        $event = [
            'name'    => 'Event',
            'date'    => '2025-06-30',
            'time'    => '18:00|20:00',
            'address' => '500 Sample St, Richmond VA',
        ];
        $member = [
            'first_name' => 'Bob',
            'last_name'  => 'Smith',
            'user_email' => 'bob@example.com',
            'member'     => [],
            'membership_level' => '',
        ];
        $attendees = [];

        $tokens = $method->invoke($handler, $event, $member, $attendees);

        $this->assertArrayHasKey('{event_address_link}', $tokens);
        $this->assertSame(
            'https://maps.google.com/?q=500%20Sample%20St%2C%20Richmond%20VA',
            $tokens['{event_address_link}']
        );
    }

    public function test_build_tokens_include_host_and_volunteer_tokens() {
        $handler = TTA_Email_Handler::get_instance();
        $ref = new \ReflectionClass($handler);
        $method = $ref->getMethod('build_tokens');
        $method->setAccessible(true);

        $event = [
            'id'         => 0,
            'name'       => 'Event',
            'date'       => '2025-06-30',
            'time'       => '18:00|20:00',
            'host_notes' => 'Don\'t forget snacks',
        ];
        $member = [
            'first_name' => 'Bob',
            'last_name'  => 'Smith',
            'user_email' => 'bob@example.com',
            'member'     => [],
            'membership_level' => '',
        ];
        $attendees = [];

        $tokens = $method->invoke($handler, $event, $member, $attendees);

        $this->assertArrayHasKey('{event_host}', $tokens);
        $this->assertArrayHasKey('{event_volunteer}', $tokens);
        $this->assertArrayHasKey('{host_notes}', $tokens);
        $this->assertSame('TBD', $tokens['{event_host}']);
        $this->assertSame('TBD', $tokens['{event_volunteer}']);
        $this->assertSame("Don't forget snacks", $tokens['{host_notes}']);
    }
}
