<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A', 'ARRAY_A'); }

class DummyWpdbHelpers {
    public $prefix = 'wp_';
    public $var_calls = 0;
    public $results_calls = 0;
    public $row_calls = 0;
    public $last_query = '';
    public $results_data = [];
    public $event_row_data = [ 'ute_id' => 'ute1', 'hosts' => '', 'volunteers' => '' ];

    public function get_var($query) {
        $this->var_calls++;
        return 'ute1';
    }

    public function get_results($query, $output = ARRAY_A) {
        $this->results_calls++;
        $this->last_query = $query;
        if ($this->results_data) {
            return $this->results_data;
        }
        return [
            [
                'email' => 'a@example.com',
                'first_name' => 'Ann',
                'last_name' => 'Bee',
                'profileimgid' => 5,
                'hide_event_attendance' => 0,
                'membership_level' => 'premium',
            ],
        ];
    }

    public function get_row($query, $output = ARRAY_A) {
        $this->row_calls++;
        if ( strpos( $query, 'FROM wp_tta_events' ) !== false ) {
            return $this->event_row_data;
        }
        return [
            'wpuserid' => 1,
            'membership_level' => 'premium',
        ];
    }

    public function prepare($query, ...$args) {
        foreach ($args as $a) {
            $query = preg_replace('/%d/', intval($a), $query, 1);
            $query = preg_replace('/%s/', $a, $query, 1);
        }
        return $query;
    }
}

class HelpersTest extends TestCase {
    private $wpdb;

    protected function setUp(): void {
        if (!defined('ABSPATH')) {
            define('ABSPATH', sys_get_temp_dir() . '/wp/');
        }
        session_start();

        if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_textarea_field')) { function sanitize_textarea_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_email')) { function sanitize_email($v){ return trim($v); } }
        if (!function_exists('sanitize_user')) { function sanitize_user($v){ return preg_replace('/[^A-Za-z0-9]/','',$v); } }
        if (!function_exists('wp_unslash')) { function wp_unslash($v){ return is_array($v)?array_map('wp_unslash',$v):str_replace('\\','',$v); } }
        if (!function_exists('esc_url')) { function esc_url($v){ return $v; } }
        if (!function_exists('esc_attr')) { function esc_attr($v){ return $v; } }
        if (!function_exists('is_user_logged_in')) { function is_user_logged_in(){ return true; } }
        if (!function_exists('wp_get_current_user')) { function wp_get_current_user(){ return (object)['ID'=>1,'user_email'=>'u@e.com','user_login'=>'user','first_name'=>'First','last_name'=>'Last']; } }
        if (!function_exists('wp_get_attachment_image_url')) { function wp_get_attachment_image_url($id,$size){ return $id===1?false:'img'.$id.'.jpg'; } }
        if (!function_exists('wp_get_attachment_url')) { function wp_get_attachment_url($id){ return 'file'.$id.'.jpg'; } }

        $GLOBALS['transients'] = [];
        if (!function_exists('get_transient')) { function get_transient($k){ return $GLOBALS['transients'][$k] ?? false; } }
        if (!function_exists('set_transient')) { function set_transient($k,$v,$t=0){ $GLOBALS['transients'][$k]=$v; } }
        if (!function_exists('delete_transient')) { function delete_transient($k){ unset($GLOBALS['transients'][$k]); } }

        global $wpdb;
        $this->wpdb = $wpdb = new DummyWpdbHelpers();

        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/helpers.php';
    }

    protected function tearDown(): void {
        $_SESSION = [];
        $GLOBALS['transients'] = [];
    }

    public function test_parse_discount_data_handles_legacy_string() {
        $res = tta_parse_discount_data('CODE');
        $this->assertSame('CODE', $res['code']);
        $this->assertSame('percent', $res['type']);
        $this->assertSame(10, $res['amount']);
    }

    public function test_parse_discount_data_handles_json() {
        $json = json_encode(['code'=>'X','type'=>'flat','amount'=>5]);
        $res = tta_parse_discount_data($json);
        $this->assertSame('X', $res['code']);
        $this->assertSame('flat', $res['type']);
        $this->assertSame(5.0, $res['amount']);
    }

    public function test_build_discount_data_round_trip() {
        $str = tta_build_discount_data('Z','flat',2);
        $res = tta_parse_discount_data($str);
        $this->assertSame('Z', $res['code']);
        $this->assertSame('flat', $res['type']);
        $this->assertSame(2.0, $res['amount']);
    }

    public function test_set_and_get_cart_notice() {
        tta_set_cart_notice('hi');
        $msg = tta_get_cart_notice();
        $this->assertSame('hi', $msg);
        $this->assertSame('', tta_get_cart_notice());
    }

    public function test_get_event_attendee_profiles_caches_results() {
        global $wpdb;
        $this->wpdb->event_row_data = [ 'ute_id' => 'ute1', 'hosts' => 'Ann Bee', 'volunteers' => '' ];
        $profiles1 = tta_get_event_attendee_profiles(5);
        $this->assertCount(1, $profiles1);
        $this->assertSame(1, $wpdb->row_calls);
        $this->assertSame(1, $wpdb->results_calls);
        $this->assertTrue($profiles1[0]['is_host']);
        $this->assertSame('premium', $profiles1[0]['membership_level']);
        $wpdb->results_data = [];
        $profiles2 = tta_get_event_attendee_profiles(5);
        $this->assertCount(1, $profiles2);
        $this->assertSame(1, $wpdb->results_calls);
    }

    public function test_get_event_attendee_image_ids_filters_zeroes() {
        global $wpdb;
        $wpdb->results_data = [
            ['email'=>'a@e.com','first_name'=>'A','last_name'=>'B','profileimgid'=>5,'hide_event_attendance'=>0,'membership_level'=>'free'],
            ['email'=>'b@e.com','first_name'=>'B','last_name'=>'C','profileimgid'=>0,'hide_event_attendance'=>0,'membership_level'=>'basic'],
            ['email'=>'c@e.com','first_name'=>'C','last_name'=>'D','profileimgid'=>3,'hide_event_attendance'=>1,'membership_level'=>'premium'],
        ];
        TTA_Cache::delete('event_attendee_profiles_99');
        $ids = tta_get_event_attendee_image_ids(99);
        sort($ids);
        $this->assertSame([5], $ids);
    }

    public function test_get_current_user_context_cached_member_lookup() {
        global $wpdb;
        $context1 = tta_get_current_user_context();
        $context2 = tta_get_current_user_context();
        $this->assertSame($context1, $context2);
    }

    public function test_admin_preview_image_uses_fallback() {
        $html = tta_admin_preview_image(1, [50,50], ['class'=>'x']);
        $this->assertStringContainsString('file1.jpg', $html);
        $this->assertStringContainsString('class="x"', $html);
    }

    public function test_get_member_upcoming_events_queries_tables() {
        global $wpdb;
        $wpdb->results_data = [
            [
                'action_data' => json_encode([
                    'transaction_id' => 'TX1',
                    'amount' => 20,
                    'items' => [ [ 'ticket_name'=>'General', 'quantity'=>1, 'attendees'=>[] ] ]
                ]),
                'event_id'    => 5,
                'name'        => 'Test Event',
                'page_id'     => 1,
                'mainimageid' => 2,
                'date'        => '2030-01-01',
                'time'        => '10:00|12:00',
                'address'     => '123 St -  - Town - ST - 12345',
                'type'        => 'paid',
                'refundsavailable' => '1'
            ]
        ];
        $events = tta_get_member_upcoming_events(1);
        $this->assertCount(1, $events);
        $this->assertSame('Test Event', $events[0]['name']);
        $this->assertSame('123 St -  - Town - ST - 12345', $events[0]['address']);
        $this->assertStringContainsString('wp_tta_memberhistory', $wpdb->last_query);
    }

    public function test_get_next_event_returns_cached_row() {
        global $wpdb;
        $this->wpdb->event_row_data = [
            'id' => 7,
            'name' => 'Soon Event',
            'date' => '2030-02-01',
            'time' => '20:00|22:00',
            'address' => '1 St -  - City - ST - 00000',
            'page_id' => 9,
        ];
        TTA_Cache::delete('tta_next_event');
        $ev1 = tta_get_next_event();
        $ev2 = tta_get_next_event();
        $this->assertSame($ev1, $ev2);
        $this->assertSame('Soon Event', $ev1['name']);
    }
}
