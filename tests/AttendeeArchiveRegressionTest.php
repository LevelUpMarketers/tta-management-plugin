<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

class DummyWpdbAttendeeArchive {
    public $prefix = 'wp_';
    public $options = 'wp_options';
    public $history_rows = [];
    public $transaction_rows = [];
    public $transactions_by_id = [];
    public $attendees = [];
    public $attendees_archive = [];
    public $last_prepare_args = [];
    public $last_query = '';
    public $attendee_counts_ran = false;

    public function esc_like($text) {
        return $text;
    }

    public function prepare($query, ...$args) {
        $flat = [];
        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $value) {
                    $flat[] = $value;
                }
            } else {
                $flat[] = $arg;
            }
        }
        $this->last_prepare_args = $flat;
        foreach ($flat as $value) {
            if (is_int($value) || (string)intval($value) === (string)$value) {
                $query = preg_replace('/%d/', (string)intval($value), $query, 1);
            } else {
                $safe  = str_replace("'", "''", (string)$value);
                $query = preg_replace('/%s/', "'{$safe}'", $query, 1);
            }
        }
        return $query;
    }

    public function get_results($query, $output = ARRAY_A) {
        $this->last_query = $query;
        if (strpos($query, 'FROM wp_tta_memberhistory') !== false) {
            return $this->history_rows;
        }
        if (strpos($query, 'FROM wp_tta_transactions WHERE transaction_id IN') !== false) {
            return $this->transaction_rows;
        }
        if (strpos($query, 'SUM(cnt)') !== false && strpos($query, 'tta_attendees_archive') !== false) {
            $this->attendee_counts_ran = true;
            $args = $this->last_prepare_args;
            $half = (int) (count($args) / 2);
            $ids  = array_slice($args, 0, $half);
            $counts = [];
            foreach ($ids as $id) {
                $id = intval($id);
                $counts[$id] = 0;
            }
            foreach ($this->attendees as $row) {
                $tx = intval($row['transaction_id']);
                if (isset($counts[$tx])) {
                    $counts[$tx]++;
                }
            }
            foreach ($this->attendees_archive as $row) {
                $tx = intval($row['transaction_id']);
                if (isset($counts[$tx])) {
                    $counts[$tx]++;
                }
            }
            $results = [];
            foreach ($counts as $tx => $total) {
                if ($total > 0) {
                    $results[] = [ 'transaction_id' => $tx, 'cnt' => $total ];
                }
            }
            return $results;
        }
        if (strpos($query, 'SELECT * FROM wp_tta_attendees') !== false) {
            $ticket_id = intval($this->last_prepare_args[0] ?? 0);
            $rows = [];
            foreach ($this->attendees as $row) {
                if (intval($row['ticket_id']) === $ticket_id) {
                    $rows[] = $row;
                }
            }
            foreach ($this->attendees_archive as $row) {
                if (intval($row['ticket_id']) === $ticket_id) {
                    $rows[] = $row;
                }
            }
            return $rows;
        }
        if (strpos($query, 'FROM wp_tta_transactions WHERE id IN') !== false) {
            $ids = $this->last_prepare_args;
            $rows = [];
            foreach ($ids as $id) {
                $id = intval($id);
                if (isset($this->transactions_by_id[$id])) {
                    $rows[] = $this->transactions_by_id[$id];
                }
            }
            return $rows;
        }
        return [];
    }

    public function get_var($query) {
        if (strpos($query, 'FROM wp_tta_members') !== false) {
            return 0;
        }
        return null;
    }
}

class AttendeeArchiveRegressionTest extends TestCase {
    private $wpdb;

    protected function setUp(): void {
        if (!defined('ABSPATH')) {
            define('ABSPATH', sys_get_temp_dir() . '/wp/');
        }
        if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return is_string($v) ? trim($v) : $v; } }
        if (!function_exists('sanitize_textarea_field')) { function sanitize_textarea_field($v){ return is_string($v) ? trim($v) : $v; } }
        if (!function_exists('sanitize_email')) { function sanitize_email($v){ return trim($v); } }
        if (!function_exists('sanitize_user')) { function sanitize_user($v){ return preg_replace('/[^A-Za-z0-9]/', '', $v); } }
        if (!function_exists('wp_unslash')) { function wp_unslash($v){ return is_array($v) ? array_map('wp_unslash', $v) : str_replace('\\', '', $v); } }
        if (!function_exists('esc_url')) { function esc_url($v){ return $v; } }
        if (!function_exists('esc_url_raw')) { function esc_url_raw($v){ return $v; } }
        if (!function_exists('esc_attr')) { function esc_attr($v){ return $v; } }
        if (!function_exists('esc_html')) { function esc_html($v){ return $v; } }
        if (!function_exists('esc_html__')) { function esc_html__($v, $d = null){ return $v; } }
        if (!function_exists('esc_html_e')) { function esc_html_e($v, $d = null){ echo $v; } }
        if (!function_exists('is_user_logged_in')) { function is_user_logged_in(){ return true; } }
        if (!function_exists('wp_get_current_user')) { function wp_get_current_user(){ return (object)['ID'=>1,'user_email'=>'member@example.com']; } }
        if (!function_exists('get_userdata')) { function get_userdata($id){ return (object)['ID'=>$id,'user_email'=>'buyer@example.com']; } }
        if (!function_exists('get_permalink')) { function get_permalink($id){ return 'post/' . $id; } }
        if (!function_exists('current_time')) { function current_time($type = 'mysql', $gmt = false){ return $type === 'timestamp' ? time() : date($type === 'mysql' ? 'Y-m-d H:i:s' : $type); } }
        if (!function_exists('get_transient')) { function get_transient($k){ return $GLOBALS['transients'][$k] ?? false; } }
        if (!function_exists('set_transient')) { function set_transient($k,$v,$t=0){ $GLOBALS['transients'][$k] = $v; return true; } }
        if (!function_exists('delete_transient')) { function delete_transient($k){ unset($GLOBALS['transients'][$k]); return true; } }
        if (!function_exists('is_admin')) { function is_admin(){ return false; } }
        if (!function_exists('current_user_can')) { function current_user_can($c){ return false; } }

        global $wpdb;
        $this->wpdb = $wpdb = new DummyWpdbAttendeeArchive();

        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/helpers.php';

        $GLOBALS['transients'] = [];
    }

    protected function tearDown(): void {
        $GLOBALS['transients'] = [];
    }

    public function test_member_past_events_include_archive_only_attendees() {
        global $wpdb;
        $wpdb->history_rows = [
            [
                'action_data' => json_encode([
                    'transaction_id' => 'GATEWAY-ARCH',
                    'amount' => 25,
                    'items' => [
                        [
                            'ticket_id' => 7,
                            'ticket_name' => 'VIP',
                            'quantity' => 1,
                        ],
                    ],
                ]),
                'event_id'    => 42,
                'name'        => 'Archived Event',
                'page_id'     => 11,
                'mainimageid' => 0,
                'date'        => '2020-01-01',
                'time'        => '18:00|20:00',
                'address'     => '1 St -  - City - ST - 00000',
                'type'        => 'paid',
                'refunds'     => '0',
            ],
        ];
        $wpdb->transaction_rows = [
            [ 'id' => 555, 'transaction_id' => 'GATEWAY-ARCH' ],
        ];
        $wpdb->transactions_by_id = [
            555 => [
                'id' => 555,
                'transaction_id' => 'GATEWAY-ARCH',
                'created_at' => '2020-01-01 10:00:00',
                'wpuserid' => 9,
                'details' => json_encode([
                    [ 'ticket_id' => 7, 'final_price' => 25 ],
                ]),
            ],
        ];
        $wpdb->attendees = [];
        $wpdb->attendees_archive = [
            [
                'id' => 901,
                'ticket_id' => 7,
                'transaction_id' => 555,
                'first_name' => 'Archive',
                'last_name' => 'Only',
                'email' => 'archive@example.com',
                'phone' => '',
                'status' => 'checked_in',
            ],
        ];

        $events = tta_get_member_past_events(5);
        $this->assertCount(1, $events);
        $this->assertTrue($wpdb->attendee_counts_ran, 'Expected attendee counts query to include archive union.');
        $items = $events[0]['items'];
        $this->assertCount(1, $items);
        $this->assertSame('Archive', $items[0]['attendees'][0]['first_name']);
        $this->assertSame('archive@example.com', $items[0]['attendees'][0]['email']);
    }
}
