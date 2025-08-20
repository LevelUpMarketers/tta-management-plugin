<?php
if (!defined('ARRAY_A')) { define('ARRAY_A', 'ARRAY_A'); }
use PHPUnit\Framework\TestCase;

class DummyWpdb {
    public $prefix = 'wp_';
    public $options = 'wp_options';
    public $insert_id = 0;
    public $data = [];
    public $queries = [];

    public function insert($table, $data) {
        $this->insert_id++;
        $this->data[$table][$this->insert_id] = $data;
        return true;
    }

    public function update($table, $data, $where) {
        $id = is_array($where) ? array_values($where)[0] : $where;
        if (isset($this->data[$table][$id])) {
            $this->data[$table][$id] = array_merge($this->data[$table][$id], $data);
            return 1;
        }
        return 0;
    }

    public function get_row($query, $output = ARRAY_A) {
        if (preg_match('/FROM (\S+) WHERE id = (\d+)/', $query, $m)) {
            $table = $m[1];
            $id    = intval($m[2]);
            return $this->data[$table][$id] ?? null;
        }
        if (preg_match('/FROM (\S+) WHERE ute_id =\s*\'?([^\' ]+)\'?/', $query, $m)) {
            $table = $m[1];
            $ute   = $m[2];
            if (isset($this->data[$table])) {
                foreach ($this->data[$table] as $id => $row) {
                    if (($row['ute_id'] ?? '') === $ute) {
                        $row['id'] = $id;
                        return $row;
                    }
                }
            }
            return null;
        }
        return null;
    }

    public function get_results($query, $output = ARRAY_A) {
        if (preg_match('/FROM (\S+) WHERE event_ute_id = ([^ ]+)/', $query, $m)) {
            $table = $m[1];
            $ute   = trim($m[2], "' ");
            $res   = [];
            if (isset($this->data[$table])) {
                foreach ($this->data[$table] as $id => $row) {
                    if (($row['event_ute_id'] ?? '') === $ute) {
                        $row['id'] = $id;
                        $res[] = $row;
                    }
                }
            }
            return $res;
        }
        return [];
    }

    public function get_var($query) {
        if (preg_match('/SELECT ute_id FROM (\S+) WHERE id = (\d+)/', $query, $m)) {
            $table = $m[1];
            $id    = intval($m[2]);
            return $this->data[$table][$id]['ute_id'] ?? null;
        }
        if (preg_match('/FROM (\S+) WHERE ticket_id = (\d+)/', $query, $m)) {
            $table = $m[1];
            $tid   = intval($m[2]);
            if (isset($this->data[$table])) {
                foreach ($this->data[$table] as $id => $row) {
                    if (($row['ticket_id'] ?? 0) == $tid) {
                        return $id;
                    }
                }
            }
        }
        return null;
    }

    public function prepare($query, ...$args) {
        foreach ($args as $a) {
            $query = preg_replace('/%d/', intval($a), $query, 1);
            $query = preg_replace('/%s/', $a, $query, 1);
        }
        return $query;
    }

    public function esc_like($str) { return $str; }
    public function query($sql) { $this->queries[] = $sql; }

    public function get_col($query) {
        return [];
    }
}

class EventTest extends TestCase {
    private $wpdb;

    protected function setUp(): void {
        if (!defined('ABSPATH')) {
            define('ABSPATH', sys_get_temp_dir() . '/wp/');
        }
        $GLOBALS['wp_posts'] = [];
        $GLOBALS['post_meta'] = [];

        if (!function_exists('check_ajax_referer')) {
            function check_ajax_referer($action, $name) {}
        }
        if (!function_exists('wp_send_json_success')) {
            function wp_send_json_success($data) { $GLOBALS['_last_json'] = ['success'=>true,'data'=>$data]; return $GLOBALS['_last_json']; }
        }
        if (!function_exists('wp_send_json_error')) {
            function wp_send_json_error($data) { $GLOBALS['_last_json'] = ['success'=>false,'data'=>$data]; return $GLOBALS['_last_json']; }
        }
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field($v) { return is_string($v) ? trim($v) : $v; }
        }
        if (!function_exists('esc_url_raw')) {
            function esc_url_raw($v) { return $v; }
        }
        if (!function_exists('esc_url')) {
            function esc_url($v){ return $v; }
        }
        if (!function_exists('wp_insert_post')) {
            function wp_insert_post($data){
                $id = count($GLOBALS['wp_posts']) + 1;
                $GLOBALS['wp_posts'][$id] = $data;
                return $id;
            }
        }
        if (!function_exists('update_post_meta')) {
            function update_post_meta($id,$key,$value){
                $GLOBALS['post_meta'][$id][$key] = $value;
            }
        }
        if (!function_exists('get_permalink')) {
            function get_permalink($id){ return 'post/'.$id; }
        }
        if (!function_exists('wp_update_post')) {
            function wp_update_post($data){
                $id = $data['ID'];
                $GLOBALS['wp_posts'][$id] = array_merge($GLOBALS['wp_posts'][$id], $data);
            }
        }
        if (!function_exists('wp_kses_post')) {
            function wp_kses_post($v){ return $v; }
        }
        if (!function_exists('wp_unslash')) { function wp_unslash($v){ return is_array($v)?array_map('wp_unslash',$v):str_replace('\\','',$v); } }
        if (!function_exists('is_wp_error')) {
            function is_wp_error($v){ return false; }
        }
        if (!function_exists('wp_json_encode')) {
            function wp_json_encode($data){ return json_encode($data); }
        }
        if (!function_exists('wp_enqueue_media')) { function wp_enqueue_media(){} }
        if (!function_exists('wp_enqueue_editor')) { function wp_enqueue_editor(){} }
        if (!function_exists('add_action')) { function add_action($t,$c,$p=10,$a=1){} }
        if (!function_exists('add_filter')) { function add_filter($t,$c,$p=10,$a=1){} }
        if (!function_exists('sanitize_textarea_field')) { function sanitize_textarea_field($v){ return is_string($v)?trim($v):$v; } }

        $GLOBALS['scheduled'] = [];
        if (!function_exists('wp_schedule_single_event')) {
            function wp_schedule_single_event($ts, $hook, $args = []) {
                $GLOBALS['scheduled'][] = [$ts, $hook, $args];
            }
        }
        if (!function_exists('wp_next_scheduled')) {
            function wp_next_scheduled($hook, $args = []) {
                return false;
            }
        }
        if (!function_exists('get_transient')) { function get_transient($k){ return false; } }
        if (!function_exists('set_transient')) { function set_transient($k,$v,$ttl=0){ return true; } }
        if (!function_exists('delete_transient')) { function delete_transient($k){ return true; } }
        if (!defined('DAY_IN_SECONDS')) { define('DAY_IN_SECONDS', 86400); }
        if (!defined('HOUR_IN_SECONDS')) { define('HOUR_IN_SECONDS', 3600); }

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/ajax/handlers/class-ajax-events.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/email/class-email-reminders.php';

        global $wpdb;
        $this->wpdb = $wpdb = new DummyWpdb();
    }

    protected function tearDown(): void {
        $_POST = [];
        $GLOBALS['scheduled'] = [];
    }

    private function basePost() {
        return [
            'name' => 'Test Event',
            'date' => '2025-01-01',
            'all_day_event' => '0',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'virtual_event' => '0',
            'street_address' => '123 St',
            'address_2' => '',
            'city' => 'Town',
            'state' => 'VA',
            'zip' => '12345',
            'venuename' => 'The Venue',
            'type' => 'free',
            'baseeventcost' => 50,
            'discountedmembercost' => 40,
            'premiummembercost' => 30,
            'waitlistavailable' => '1',
            'refundsavailable' => '0',
            'discountcode' => 'CODE',
            'discount_type' => 'percent',
            'discount_amount' => 10,
            'url2' => '',
            'url3' => '',
            'url4' => '',
            'mainimageid' => 0,
            'otherimageids' => '',
            'host_notes' => 'Test notes',
            'tta_event_save_nonce' => 'yes',
        ];
    }

    public function test_save_event_creates_records() {
        $_POST = $this->basePost();
        TTA_Ajax_Events::save_event();
        $result = $GLOBALS['_last_json'];
        $this->assertTrue($result['success']);
        $events_table  = $this->wpdb->prefix.'tta_events';
        $tickets_table = $this->wpdb->prefix.'tta_tickets';
        $event_id = $result['data']['id'];
        $ticket_id = $result['data']['ticket'];
        $event  = $this->wpdb->data[$events_table][$event_id];
        $ticket = $this->wpdb->data[$tickets_table][$ticket_id];
        $this->assertSame('Test Event', $event['name']);
        $this->assertSame('123 St -  - Town - VA - 12345', $event['address']);
        $this->assertSame('Test notes', $event['host_notes']);
        $this->assertSame($event['ute_id'], $ticket['event_ute_id']);
        $this->assertSame('General Admission', $ticket['ticket_name']);
    }

    public function test_update_event_changes_data() {
        $_POST = $this->basePost();
        TTA_Ajax_Events::save_event();
        $res = $GLOBALS['_last_json'];
        $id = $res['data']['id'];

        $_POST = $this->basePost();
        $_POST['tta_event_id'] = $id;
        $_POST['name'] = 'Updated';
        $_POST['date'] = '2025-02-02';
        $_POST['host_notes'] = 'Updated note';
        TTA_Ajax_Events::update_event();
        $result = $GLOBALS['_last_json'];
        $this->assertTrue($result['success']);
        $events_table  = $this->wpdb->prefix.'tta_events';
        $event  = $this->wpdb->data[$events_table][$id];
        $this->assertSame('Updated', $event['name']);
        $this->assertSame('2025-02-02', $event['date']);
        $this->assertSame('Updated note', $event['host_notes']);
    }

    public function test_save_event_requires_fields() {
        $_POST = $this->basePost();
        unset($_POST['name']);
        TTA_Ajax_Events::save_event();
        $result = $GLOBALS['_last_json'];
        $this->assertFalse($result['success']);
    }

    public function test_save_event_schedules_emails() {
        $_POST = $this->basePost();
        $_POST['date'] = '2030-01-01';
        TTA_Ajax_Events::save_event();
        $this->assertCount(6, $GLOBALS['scheduled']);
        $hooks = array_column($GLOBALS['scheduled'], 1);
        $this->assertContains('tta_attendee_reminder_email', $hooks);
        $this->assertContains('tta_host_reminder_email', $hooks);
        $this->assertContains('tta_volunteer_reminder_email', $hooks);
    }
}
