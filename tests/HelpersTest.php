<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A', 'ARRAY_A'); }

class DummyWpdbHelpers {
    public $prefix = 'wp_';
    public $var_calls = 0;
    public $var_value = 'ute1';
    public $results_calls = 0;
    public $row_calls = 0;
    public $last_query = '';
    public $results_data = [];
    public $event_row_data = [ 'ute_id' => 'ute1', 'hosts' => '', 'volunteers' => '' ];
    public $row_data = null;

    public function esc_like( $text ) {
        return $text;
    }

    public function get_var($query) {
        $this->var_calls++;
        return $this->var_value;
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
        if ( strpos( $query, 'FROM wp_tta_transactions' ) !== false ) {
            return [ 'id' => 99, 'details' => json_encode([['event_ute_id'=>'ute1','ticket_id'=>3,'final_price'=>5]]), 'created_at' => '2025-07-01 10:00:00' ];
        }
        if ( $this->row_data !== null ) {
            $row = $this->row_data;
            $this->row_data = null;
            return $row;
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

require_once __DIR__ . '/../includes/classes/class-tta-refund-processor.php';

class TTA_Refund_Processor_Test extends TTA_Refund_Processor {
    public static $processed = [];

    public static function process_refund_request( array $req, $amount_override = null ) {
        self::$processed[] = $req['transaction_id'];
    }

    public static function retry_pending_requests() {
        global $wpdb;
        $requests = tta_get_refund_requests();
        if ( ! $requests ) {
            return;
        }

        $grouped = [];
        foreach ( $requests as $req ) {
            $tid = intval( $req['ticket_id'] );
            $grouped[ $tid ][] = $req;
        }

        foreach ( $grouped as $tid => $list ) {
            $released = tta_get_released_refund_count( $tid );
            if ( $released <= 0 ) {
                continue;
            }

            $stock = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT ticketlimit FROM {$wpdb->prefix}tta_tickets WHERE id = %d",
                $tid
            ) );

            $sold_from_pool = max( 0, $released - $stock );

            $eligible = array_values( array_filter( $list, function( $req ) {
                return 'settlement' !== ( $req['pending_reason'] ?? '' );
            } ) );

            $to_refund = min( count( $eligible ), $sold_from_pool );
            for ( $i = 0; $i < $to_refund; $i++ ) {
                static::process_refund_request( $eligible[ $i ] );
            }
        }
    }
}

class HelpersTest extends TestCase {
    private $wpdb;

    protected function setUp(): void {
        if (!defined('ABSPATH')) {
            define('ABSPATH', sys_get_temp_dir() . '/wp/');
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_textarea_field')) { function sanitize_textarea_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_email')) { function sanitize_email($v){ return trim($v); } }
        if (!function_exists('sanitize_user')) { function sanitize_user($v){ return preg_replace('/[^A-Za-z0-9]/','',$v); } }
        if (!function_exists('wp_unslash')) { function wp_unslash($v){ return is_array($v)?array_map('wp_unslash',$v):str_replace('\\','',$v); } }
        if (!function_exists('esc_url')) { function esc_url($v){ return $v; } }
        if (!function_exists('esc_url_raw')) { function esc_url_raw($v){ return $v; } }
        if (!function_exists('esc_attr')) { function esc_attr($v){ return $v; } }
        if (!function_exists('esc_html')) { function esc_html($v){ return $v; } }
        if (!function_exists('esc_html_e')) { function esc_html_e($s,$d=null){ echo $s; } }
        if (!function_exists('esc_html__')) { function esc_html__($s,$d=null){ return $s; } }
        if (!function_exists('esc_like')) { function esc_like($v){ return $v; } }
        if (!function_exists('is_user_logged_in')) { function is_user_logged_in(){ return true; } }
        if (!function_exists('wp_get_current_user')) { function wp_get_current_user(){ return (object)['ID'=>1,'user_email'=>'u@e.com','user_login'=>'user','first_name'=>'First','last_name'=>'Last']; } }
        if (!function_exists('get_userdata')) { function get_userdata($id){ return (object)['ID'=>$id,'user_email'=>'member'.$id.'@example.com']; } }
        if (!function_exists('wp_get_attachment_image_url')) { function wp_get_attachment_image_url($id,$size){ return $id===1?false:'img'.$id.'.jpg'; } }
        if (!function_exists('wp_get_attachment_url')) { function wp_get_attachment_url($id){ return 'file'.$id.'.jpg'; } }
        if (!function_exists('get_permalink')) { function get_permalink($id){ return 'post/'.$id; } }
        if (!function_exists('date_i18n')) { function date_i18n($format,$ts){ return date($format,$ts); } }
        if (!function_exists('wp_json_encode')) { function wp_json_encode($data, $options = 0, $depth = 512){ return json_encode($data, $options, $depth); } }
        if (!function_exists('current_time')) { function current_time($type = 'mysql', $gmt = false){ return 'timestamp' === $type ? time() : date('Y-m-d H:i:s'); } }
        if (!function_exists('get_option')) { function get_option($k,$d=null){ return $GLOBALS['options'][$k] ?? $d; } }
        if (!function_exists('update_option')) { function update_option($k,$v,$autoload=true){ $GLOBALS['options'][$k]=$v; } }
        if (!function_exists('add_action')) { function add_action($t,$c,$p=10,$a=1){} }
        if (!function_exists('add_filter')) { function add_filter($t,$c,$p=10,$a=1){} }

        $GLOBALS['transients'] = [];
        $GLOBALS['options'] = [];
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
        $GLOBALS['options'] = [];
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
        $this->wpdb->var_value = 'premium';
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
        $this->assertStringContainsString('class="x tta-popup-img"', $html);
        $this->assertStringContainsString('data-full="file1.jpg"', $html);
    }

    public function test_user_banned_helpers() {
        global $wpdb;
        $wpdb->var_calls = 0;
        $wpdb->var_value = '2099-12-31 23:59:59';
        $until = tta_get_user_banned_until(1);
        $this->assertSame('2099-12-31 23:59:59', $until);
        $this->assertTrue( tta_user_is_banned(1) );
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

    public function test_get_member_upcoming_events_splits_multi_attendees() {
        global $wpdb;
        $wpdb->results_data = [
            [
                'action_data' => json_encode([
                    'transaction_id' => 'TXS',
                    'amount' => 40,
                    'items' => [
                        [
                            'ticket_id' => 7,
                            'ticket_name' => 'General',
                            'quantity' => 2,
                            'attendees' => [
                                [ 'first_name' => 'Ann',  'last_name' => 'A', 'email' => 'a@e.com' ],
                                [ 'first_name' => 'Bob',  'last_name' => 'B', 'email' => 'b@e.com' ],
                            ]
                        ]
                    ]
                ]),
                'event_id'    => 9,
                'name'        => 'Split Event',
                'page_id'     => 1,
                'mainimageid' => 0,
                'date'        => '2030-01-02',
                'time'        => '08:00|10:00',
                'address'     => '1 St -  - City - ST - 00000',
                'type'        => 'paid',
                'refundsavailable' => '1'
            ]
        ];
        $events = tta_get_member_upcoming_events(1);
        $this->assertCount(1, $events);
        $this->assertCount(2, $events[0]['items']);
        $this->assertSame('Ann', $events[0]['items'][0]['attendees'][0]['first_name']);
        $this->assertSame('Bob', $events[0]['items'][1]['attendees'][0]['first_name']);
    }

    public function test_get_member_upcoming_events_handles_refund_pending_split() {
        global $wpdb;
        $wpdb->results_data = [
            [
                'action_data' => json_encode([
                    'transaction_id' => 'TXR',
                    'amount' => 40,
                    'items' => [
                        [
                            'ticket_id' => 7,
                            'ticket_name' => 'General',
                            'quantity' => 2,
                            'refund_pending' => true,
                            'refund_attendee' => [
                                'first_name' => 'Ann',
                                'last_name'  => 'A',
                                'email'      => 'a@e.com'
                            ],
                            'attendees' => [
                                [ 'first_name' => 'Bob', 'last_name' => 'B', 'email' => 'b@e.com' ]
                            ]
                        ]
                    ]
                ]),
                'event_id'    => 9,
                'name'        => 'Refund Event',
                'page_id'     => 1,
                'mainimageid' => 0,
                'date'        => '2030-01-02',
                'time'        => '08:00|10:00',
                'address'     => '1 St -  - City - ST - 00000',
                'type'        => 'paid',
                'refundsavailable' => '1'
            ]
        ];

        $events = tta_get_member_upcoming_events(1);
        $this->assertCount(1, $events);
        $items = $events[0]['items'];
        $this->assertCount(2, $items);
        $pending = array_values(array_filter($items, function($i){ return !empty($i['refund_pending']); }));
        $this->assertCount(1, $pending);
        $normal = array_values(array_filter($items, function($i){ return empty($i['refund_pending']); }));
        $this->assertCount(1, $normal);
        $this->assertSame('Bob', $normal[0]['attendees'][0]['first_name']);
    }

    public function test_get_member_past_events_queries_archive() {
        global $wpdb;
        $wpdb->results_data = [
            [
                'action_data' => json_encode([
                    'transaction_id' => 'TX2',
                    'amount' => 10,
                    'items' => [ [ 'ticket_name'=>'General', 'quantity'=>1, 'attendees'=>[] ] ]
                ]),
                'event_id'    => 5,
                'name'        => 'Past Event',
                'page_id'     => 1,
                'mainimageid' => 2,
                'date'        => '2020-01-01',
                'time'        => '10:00|12:00',
                'address'     => '1 St -  - Town - ST - 00000',
                'type'        => 'paid',
                'refunds'     => '0'
            ]
        ];
        $events = tta_get_member_past_events(1);
        $this->assertCount(1, $events);
        $this->assertSame('Past Event', $events[0]['name']);
        $this->assertStringContainsString('wp_tta_events_archive', $wpdb->last_query);
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
            'type' => 'paid',
            'venuename' => 'Main Hall',
            'venueurl' => 'https://example.com',
            'baseeventcost' => 10,
            'discountedmembercost' => 8,
            'premiummembercost' => 7,
            'hosts' => 'Ann Bee',
            'volunteers' => 'Ben Dee',
            'host_notes' => 'Bring ID',
        ];
        TTA_Cache::delete('tta_next_event');
        $ev1 = tta_get_next_event();
        $ev2 = tta_get_next_event();
        $this->assertSame($ev1, $ev2);
        $this->assertSame('Soon Event', $ev1['name']);
        $this->assertSame('February 1st, 2030', $ev1['date_formatted']);
        $this->assertSame('8:00 pm - 10:00 pm', $ev1['time_formatted']);
        $this->assertSame('Ann Bee', $ev1['host_names']);
        $this->assertSame('Ben Dee', $ev1['volunteer_names']);
        $this->assertSame('Bring ID', $ev1['host_notes']);
    }

    public function test_set_attendance_status_updates_db() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $updated = [];
            public function update($table, $data, $where, $formats, $where_f) {
                $this->updated = [$table, $data, $where];
            }
            public function get_row($query, $output = ARRAY_A) {
                return ['email' => 'test@example.com'];
            }
            public function prepare($query, ...$args) {
                return $query;
            }
        };
        require_once __DIR__ . '/../includes/helpers.php';
        tta_set_attendance_status(5, 'checked_in');
        $this->assertSame('wp_tta_attendees', $wpdb->updated[0]);
        $this->assertSame('checked_in', $wpdb->updated[1]['status']);
    }

    public function test_save_assistance_note_updates_db() {
        global $wpdb;
        $wpdb = new class extends DummyWpdbHelpers {
            public $queries = [];
            public function get_col( $q ) { $this->queries[] = $q; return [1,2]; }
            public function query( $q ) { $this->queries[] = $q; }
        };
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        tta_save_assistance_note( 5, 'ute1', 'Help' );
        $this->assertStringContainsString( 'a.email', $wpdb->queries[0] );
        $this->assertStringContainsString( 'UPDATE wp_tta_attendees', $wpdb->queries[1] );
    }

    public function test_get_event_attendees_with_status_queries_table() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $last_query = '';
            public function get_results($q,$o=ARRAY_A){ $this->last_query = $q; return [ ['id'=>1,'ticket_id'=>2,'first_name'=>'A','last_name'=>'B','email'=>'e','phone'=>'p','status'=>'pending'] ]; }
            public function get_var($q){ return 5; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%s/',$v,$q,1); $q=preg_replace('/%d/',$v,$q,1); } return $q; }
        };
        require_once __DIR__ . '/../includes/helpers.php';
        $rows = tta_get_event_attendees_with_status('ev1');
        $this->assertCount(1, $rows);
    }

    public function test_get_remaining_ticket_count_queries_table() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $last_query = '';
            public function get_var($q){ $this->last_query = $q; return 7; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%s/',$v,$q,1); $q=preg_replace('/%d/',$v,$q,1); } return $q; }
        };
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $count = tta_get_remaining_ticket_count('ute1');
        $this->assertSame(7, $count);
        $this->assertStringContainsString('wp_tta_tickets', $wpdb->last_query);
    }

    public function test_ticket_cost_range_returns_min_max() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $rows = [];
            public $last_query = '';
            public function get_results( $q, $o = ARRAY_A ) { $this->last_query = $q; return $this->rows; }
            public function prepare( $q, ...$a ) { foreach ( $a as $v ) { $q = preg_replace( '/%s/', $v, $q, 1 ); } return $q; }
        };
        $wpdb->rows = [
            [ 'baseeventcost' => 20, 'discountedmembercost' => 10, 'premiummembercost' => 9 ],
            [ 'baseeventcost' => 90, 'discountedmembercost' => 70, 'premiummembercost' => 65 ],
        ];

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';

        $range = tta_get_ticket_cost_range( 'ev1' );
        $this->assertSame( 20.0, $range['base_min'] );
        $this->assertSame( 90.0, $range['base_max'] );
        $this->assertSame( 10.0, $range['basic_min'] );
        $this->assertSame( 70.0, $range['basic_max'] );
        $this->assertSame( 9.0, $range['premium_min'] );
        $this->assertSame( 65.0, $range['premium_max'] );

        // Confirm cached result is returned
        $wpdb->rows = [];
        $range2 = tta_get_ticket_cost_range( 'ev1' );
        $this->assertSame( $range, $range2 );
    }

    public function test_get_upcoming_events_returns_rows() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $last_query = '';
            public $rows = [];
            public function get_results($q,$o=ARRAY_A){ $this->last_query = $q; return $this->rows; }
            public function get_var($q){ return 1; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%s/',$v,$q,1); $q=preg_replace('/%d/',$v,$q,1); } return $q; }
        };
        $wpdb->rows = [ [
            'id'=>1,
            'ute_id'=>'ute1',
            'name'=>'Soon',
            'date'=>'2030-01-01',
            'time'=>'10:00|12:00',
            'all_day_event'=>0,
            'venuename'=>'Venue',
            'address'=>'1 St -  - Town - ST - 00000',
            'page_id'=>5,
            'mainimageid'=>0
        ] ];
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $res = tta_get_upcoming_events(1,5);
        $this->assertCount(1, $res['events']);
        $this->assertSame('Soon', $res['events'][0]['name']);
        $this->assertStringContainsString('wp_tta_events', $wpdb->last_query);
    }

    public function test_get_upcoming_events_includes_waitlist_flag() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $last_query = '';
            public $rows = [];
            public function get_results($q, $o = ARRAY_A) { $this->last_query = $q; return $this->rows; }
            public function get_var($q) { return 1; }
            public function prepare($q, ...$a) { foreach ($a as $v) { $q = preg_replace('/%s/', $v, $q, 1); $q = preg_replace('/%d/', $v, $q, 1); } return $q; }
        };
        $wpdb->rows = [ [
            'id' => 1,
            'ute_id' => 'ute1',
            'name' => 'Soon',
            'date' => '2030-01-01',
            'time' => '10:00|12:00',
            'all_day_event' => 0,
            'venuename' => 'Venue',
            'address' => '1 St -  - Town - ST - 00000',
            'page_id' => 5,
            'mainimageid' => 0,
            'waitlistavailable' => 1,
        ] ];
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $res = tta_get_upcoming_events(1, 5);
        $this->assertTrue($res['events'][0]['waitlistavailable']);
    }

    public function test_get_event_days_for_month() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $get_col_query = '';
            public function get_col($q){ $this->get_col_query = $q; return [1,15,28]; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%s/',$v,$q,1); } return $q; }
        };

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $days = tta_get_event_days_for_month(2025,7);
        $this->assertSame([1,15,28], $days);
        $this->assertStringContainsString('wp_tta_events', $wpdb->get_col_query);
    }

    public function test_get_ads_functions() {
        update_option('tta_ads', [
            [
                'image_id'        => 1,
                'url'             => 'https://example.com/a',
                'business_name'   => 'Biz A',
                'business_phone'  => '111-222-3333',
                'business_address'=> '1 Main St',
            ],
            [
                'image_id'        => 2,
                'url'             => 'https://example.com/b',
                'business_name'   => 'Biz B',
                'business_phone'  => '444-555-6666',
                'business_address'=> '2 Main St',
            ],
        ], false);

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';

        $ads = tta_get_ads();
        $this->assertCount(2, $ads);
        $this->assertSame('Biz A', $ads[0]['business_name']);

        $ad = tta_get_random_ad();
        $this->assertArrayHasKey('business_phone', $ad);
    }

    public function test_global_discount_code_helpers() {
        update_option('tta_global_discount_codes', [
            ['code' => 'SAVE', 'type' => 'percent', 'amount' => 5],
        ], false);
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $codes = tta_get_global_discount_codes();
        $this->assertCount(1, $codes);
        $this->assertSame('SAVE', $codes[0]['code']);
        tta_save_global_discount_codes([]);
        $codes2 = tta_get_global_discount_codes();
        $this->assertSame([], $codes2);
    }

    public function test_get_first_event_page_id_for_date() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public function get_var($q) { return 42; }
            public function prepare($q, ...$a) { foreach ($a as $v) { $q = preg_replace('/%s/', $v, $q, 1); } return $q; }
        };

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';

        $page_id = tta_get_first_event_page_id_for_date(2025, 7, 15);
        $this->assertSame(42, $page_id);
    }

    public function test_format_event_date_outputs_readable_string() {
        require_once __DIR__ . '/../includes/helpers.php';
        $this->assertSame('June 28th, 2025', tta_format_event_date('2025-06-28'));
    }

    public function test_format_event_time_formats_range() {
        require_once __DIR__ . '/../includes/helpers.php';
        $this->assertSame('6:00 pm - 8:00 pm', tta_format_event_time('18:00|20:00'));
    }

    public function test_format_event_datetime_handles_range() {
        require_once __DIR__ . '/../includes/helpers.php';
        $out = tta_format_event_datetime('2025-07-19', '18:00|20:00');
        $this->assertSame('Saturday July 19, 2025 - 6:00 PM to 8:00 PM', $out);
    }

    public function test_format_event_datetime_handles_single_time() {
        require_once __DIR__ . '/../includes/helpers.php';
        $out = tta_format_event_datetime('2025-07-19', '18:00|');
        $this->assertSame('Saturday July 19, 2025 - 6:00 PM', $out);
    }

    public function test_parse_address_preserves_hyphenated_street() {
        require_once __DIR__ . '/../includes/helpers.php';
        $raw = '1234 E-State St - Apt A - Richmond - VA - 23231';
        $parts = tta_parse_address($raw);
        $this->assertSame('1234 E-State St', $parts['street']);
        $this->assertSame('Apt A', $parts['address2']);
        $this->assertSame('Richmond', $parts['city']);
        $this->assertSame('VA', $parts['state']);
        $this->assertSame('23231', $parts['zip']);
    }

    public function test_format_address_preserves_hyphenated_street() {
        require_once __DIR__ . '/../includes/helpers.php';
        $raw = '1234 E-State St -  - Richmond - VA - 23231';
        $formatted = tta_format_address($raw);
        $this->assertSame('1234 E-State St Richmond, VA 23231', $formatted);
    }
    public function test_get_member_waitlist_events_returns_entries() {
        global $wpdb;
        $this->wpdb->results_calls = 0;
        $this->wpdb->results_data = [
            [
                'ticket_id' => 1,
                'ticket_name' => 'General',
                'event_name' => 'Big Event',
                'event_ute_id' => 'ute1',
                'added_at' => '2024-01-01 00:00:00',
                'event_id' => 5,
                'page_id' => 10,
                'mainimageid' => 3,
                'date' => '2030-01-01',
                'time' => '18:00|20:00',
                'address' => '1 St -  - City - ST - 12345'
            ]
        ];
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $events = tta_get_member_waitlist_events(1);
        $this->assertCount(1, $events);
        $this->assertSame('Big Event', $events[0]['name']);
        $this->assertSame(1, $wpdb->results_calls);
        $cached = tta_get_member_waitlist_events(1);
        $this->assertSame(1, $wpdb->results_calls);
    }

    public function test_get_refund_requests_returns_rows() {
        global $wpdb;
        $this->wpdb->results_calls = 0;
        $this->wpdb->results_data = [
            [
                'member_id' => 7,
                'action_date' => '2025-07-01 10:00:00',
                'action_data' => json_encode([
                    'transaction_id' => 'tx99',
                    'ticket_id' => 3,
                    'reason' => 'Changed plans',
                    'attendee' => [
                        'id'         => 55,
                        'first_name' => 'Ann',
                        'last_name'  => 'Bee',
                        'email'      => 'a@example.com',
                        'phone'      => '123',
                        'amount_paid' => 10.00,
                    ],
                ]),
                'event_id' => 5,
                'first_name' => 'Ann',
                'last_name' => 'Bee',
                'event_name' => 'Fun Event',
                'page_id' => 2
            ]
        ];
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $rows = tta_get_refund_requests();
        $this->assertCount(1, $rows);
        $this->assertSame('Ann Bee', $rows[0]['member_name']);
        $this->assertSame('Ann', $rows[0]['first_name']);
        $this->assertSame('a@example.com', $rows[0]['email']);
        $this->assertSame(10.0, $rows[0]['amount_paid']);
        $this->assertSame(55, $rows[0]['attendee_id']);
        $this->assertArrayHasKey('attendee', $rows[0]);
        $this->assertSame('Ann', $rows[0]['attendee']['first_name']);
        $this->assertSame(1, $wpdb->results_calls);
        $cached = tta_get_refund_requests();
        $this->assertSame(1, $wpdb->results_calls);
    }

    public function test_get_refund_request_attendees_parses_rows() {
        global $wpdb;
        $this->wpdb->results_calls = 0;
        // first query returns transaction row
        $this->wpdb->row_calls = 0;
        $this->wpdb->results_data = [
            [
                'id' => 55,
                'ticket_id' => 3,
                'first_name' => 'Ann',
                'last_name' => 'Bee',
                'email' => 'a@example.com',
                'phone' => '123',
            ],
            [
                'id' => 56,
                'ticket_id' => 3,
                'first_name' => 'Carl',
                'last_name' => 'Dee',
                'email' => 'c@example.com',
                'phone' => '555',
            ]
        ];
        $this->wpdb->event_row_data = [ 'ute_id' => 'ute1', 'hosts' => '', 'volunteers' => '' ];
        $this->wpdb->last_query = '';
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $attendees = tta_get_refund_request_attendees('tx1', 5);
        $this->assertCount(2, $attendees);
        $this->assertSame('Ann', $attendees[0]['first_name']);
        $this->assertSame('tx1', $attendees[0]['gateway_id']);
    }

    public function test_get_ticket_refunded_attendees_returns_reason() {
        global $wpdb;
        $this->wpdb->results_calls = 0;
        $this->wpdb->results_data = [
            [
                'action_data' => json_encode([
                    'amount'         => 70,
                    'transaction_id' => 'tx2',
                    'ticket_id'      => 3,
                    'attendee_id'    => 0,
                    'cancel'         => 1,
                    'attendee'       => [
                        'first_name' => 'Ann',
                        'last_name'  => 'Bee',
                        'email'      => 'a@example.com',
                        'phone'      => '123',
                    ],
                    'reason'        => 'No longer attending',
                ]),
                'action_date' => '2025-07-14 12:00:00',
            ]
        ];

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        $rows = tta_get_ticket_refunded_attendees( 3, 5 );
        $this->assertCount( 1, $rows );
        $this->assertSame( 'No longer attending', $rows[0]['reason'] );
        $this->assertSame( 'tx2', $rows[0]['gateway_id'] );
        $this->assertSame( 1, $wpdb->results_calls );
        $cached = tta_get_ticket_refunded_attendees( 3, 5 );
        $this->assertSame( 1, $wpdb->results_calls );
    }

    public function test_get_next_refund_request_for_ticket_includes_reason() {
        global $wpdb;
        $this->wpdb->row_calls = 0;
        $this->wpdb->row_data  = [
            'member_id' => 7,
            'wpuserid'  => 3,
            'event_id'  => 5,
            'action_data' => json_encode([
                'transaction_id' => 'tx9',
                'ticket_id'      => 3,
                'reason'         => 'Need refund',
                'attendee'       => []
            ])
        ];

        require_once __DIR__ . '/../includes/helpers.php';
        $req = tta_get_next_refund_request_for_ticket( 3 );
        $this->assertSame( 'Need refund', $req['reason'] );
        $this->assertSame( 'tx9', $req['transaction_id'] );
        $this->assertSame( 1, $this->wpdb->row_calls );
    }

    public function test_refund_pool_count_excludes_settlement_requests() {
        global $wpdb;
        TTA_Cache::delete( 'tta_refund_requests' );
        TTA_Cache::delete( 'pending_refund_attendees_9_20' );
        $att1 = [
            'id'         => 1,
            'first_name' => 'Ann',
            'last_name'  => 'Bee',
            'email'      => 'a@example.com',
            'phone'      => '123',
            'amount_paid'=> 10.0,
        ];
        $att2 = [
            'id'         => 2,
            'first_name' => 'Carl',
            'last_name'  => 'Dee',
            'email'      => 'c@example.com',
            'phone'      => '555',
            'amount_paid'=> 10.0,
        ];
        $wpdb->results_data = [
            [
                'member_id'   => 7,
                'action_date' => '2025-07-01 10:00:00',
                'action_data' => json_encode([
                    'transaction_id' => 'tx1',
                    'ticket_id'      => 9,
                    'pending_reason' => 'waitlist',
                    'attendee'       => $att1,
                ]),
                'event_id'   => 20,
                'first_name' => 'Ann',
                'last_name'  => 'Bee',
                'event_name' => 'Fun Event',
                'page_id'    => 2,
            ],
            [
                'member_id'   => 8,
                'action_date' => '2025-07-02 10:00:00',
                'action_data' => json_encode([
                    'transaction_id' => 'tx2',
                    'ticket_id'      => 9,
                    'pending_reason' => 'settlement',
                    'attendee'       => $att2,
                ]),
                'event_id'   => 20,
                'first_name' => 'Carl',
                'last_name'  => 'Dee',
                'event_name' => 'Fun Event',
                'page_id'    => 2,
            ],
        ];

        require_once __DIR__ . '/../includes/helpers.php';
        $this->assertCount( 2, tta_get_ticket_pending_refund_attendees( 9, 20 ) );
        $this->assertSame( 1, tta_get_ticket_refund_pool_count( 9, 20 ) );
    }

    public function test_get_refund_requests_returns_oldest_first() {
        global $wpdb;
        TTA_Cache::delete( 'tta_refund_requests' );
        $wpdb->results_data = [
            [
                'id'           => 1,
                'member_id'    => 7,
                'action_date'  => '2025-07-01 10:00:00',
                'action_data'  => json_encode([
                    'transaction_id' => 'tx1',
                    'ticket_id'      => 9,
                    'attendee'       => []
                ]),
                'event_id'     => 20,
                'first_name'   => 'Ann',
                'last_name'    => 'Bee',
                'event_name'   => 'Fun Event',
                'page_id'      => 2,
            ],
            [
                'id'           => 2,
                'member_id'    => 8,
                'action_date'  => '2025-07-02 10:00:00',
                'action_data'  => json_encode([
                    'transaction_id' => 'tx2',
                    'ticket_id'      => 9,
                    'attendee'       => []
                ]),
                'event_id'     => 20,
                'first_name'   => 'Bob',
                'last_name'    => 'See',
                'event_name'   => 'Fun Event',
                'page_id'      => 2,
            ],
        ];

        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/helpers.php';
        $reqs = tta_get_refund_requests();
        $this->assertSame( 'tx1', $reqs[0]['transaction_id'] );
        $this->assertSame( 'tx2', $reqs[1]['transaction_id'] );
    }

    public function test_retry_pending_requests_skips_settlement() {
        global $wpdb;
        TTA_Cache::delete( 'tta_refund_requests' );
        $wpdb->results_data = [
            [
                'id'           => 1,
                'member_id'    => 7,
                'action_date'  => '2025-07-01 10:00:00',
                'action_data'  => json_encode([
                    'transaction_id' => 'tx1',
                    'ticket_id'      => 5,
                    'pending_reason' => 'settlement',
                    'attendee'       => []
                ]),
                'event_id'     => 20,
                'first_name'   => 'Ann',
                'last_name'    => 'Bee',
                'event_name'   => 'Fun Event',
                'page_id'      => 2,
            ],
            [
                'id'           => 2,
                'member_id'    => 8,
                'action_date'  => '2025-07-02 10:00:00',
                'action_data'  => json_encode([
                    'transaction_id' => 'tx2',
                    'ticket_id'      => 5,
                    'attendee'       => []
                ]),
                'event_id'     => 20,
                'first_name'   => 'Bob',
                'last_name'    => 'See',
                'event_name'   => 'Fun Event',
                'page_id'      => 2,
            ],
        ];
        update_option( 'tta_refund_pool_released', [ 5 => 1 ] );
        $wpdb->var_value = 0;
        TTA_Refund_Processor_Test::$processed = [];
        TTA_Refund_Processor_Test::retry_pending_requests();
        $this->assertSame( [ 'tx2' ], TTA_Refund_Processor_Test::$processed );
    }

    public function test_convert_links_transforms_markdown() {
        require_once __DIR__ . '/../includes/helpers.php';
        $in  = 'Check [your profile](/member-dashboard/?tab=profile) today.';
        $out = 'Check <a href="/member-dashboard/?tab=profile">your profile</a> today.';
        $this->assertSame( $out, tta_convert_links( $in ) );
    }

    public function test_convert_links_handles_tokens() {
        require_once __DIR__ . '/../includes/helpers.php';
        $tokens = [
            '{event_name}' => 'Roller Skating #2',
            '{event_link}' => 'https://example.com/roller-skating-2/',
        ];
        $in  = '[{event_name}]({event_link})';
        $out = '<a href="https://example.com/roller-skating-2/">Roller Skating #2</a>';
        $this->assertSame( $out, tta_convert_links( strtr( $in, $tokens ) ) );
    }

    public function test_convert_links_handles_address_tokens() {
        require_once __DIR__ . '/../includes/helpers.php';
        $tokens = [
            '{event_address}' => '500 Sample St',
            '{event_address_link}' => 'https://maps.google.com/?q=500+Sample+St',
        ];
        $in  = '[{event_address}]({event_address_link})';
        $out = '<a href="https://maps.google.com/?q=500+Sample+St">500 Sample St</a>';
        $this->assertSame( $out, tta_convert_links( strtr( $in, $tokens ) ) );
    }

    public function test_expand_anchor_tokens_handles_anchor() {
        require_once __DIR__ . '/../includes/helpers.php';
        $tokens = [ '{dashboard_upcoming_url}' => 'http://example.com/member-dashboard/?tab=upcoming' ];
        $in  = 'Visit {dashboard_upcoming_url anchor="here"} to see events.';
        $exp = 'Visit [here](http://example.com/member-dashboard/?tab=upcoming) to see events.';
        $this->assertSame( $exp, tta_expand_anchor_tokens( $in, $tokens ) );
    }

    public function test_expand_anchor_tokens_returns_url_when_empty() {
        require_once __DIR__ . '/../includes/helpers.php';
        $tokens = [ '{dashboard_upcoming_url}' => 'http://example.com/member-dashboard/?tab=upcoming' ];
        $in  = 'Go {dashboard_upcoming_url anchor=""} now.';
        $exp = 'Go http://example.com/member-dashboard/?tab=upcoming now.';
        $this->assertSame( $exp, tta_expand_anchor_tokens( $in, $tokens ) );
    }

    public function test_convert_bold_and_strip_bold() {
        require_once __DIR__ . '/../includes/helpers.php';
        $in = 'Hello **World** *there* ***friend***';
        $exp = 'Hello <strong>World</strong> <em>there</em> <strong><em>friend</em></strong>';
        $this->assertSame( $exp, tta_convert_bold( $in ) );
        $this->assertSame( 'Hello World there friend', tta_strip_bold( $in ) );
    }
}
