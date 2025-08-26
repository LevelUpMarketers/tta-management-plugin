<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A','ARRAY_A'); }

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($v){ return is_string($v) ? trim($v) : $v; }
}
if (!function_exists('current_time')) {
    function current_time($format){ return '2024-02-01'; }
}
if (!function_exists('get_transient')) {
    function get_transient($k){ return $GLOBALS['transients'][$k] ?? false; }
}
if (!function_exists('set_transient')) {
    function set_transient($k,$v,$t=0){ $GLOBALS['transients'][$k]=$v; }
}
if (!function_exists('delete_transient')) {
    function delete_transient($k){ unset($GLOBALS['transients'][$k]); }
}
if (!function_exists('add_shortcode')) {
    function add_shortcode($tag,$func){}
}

require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
require_once __DIR__ . '/../includes/shortcodes/class-homepage-shortcode.php';

class HomepageShortcodeTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['transients'] = [];
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $last_query = '';
            public function prepare($q,...$a){
                foreach ($a as $v) {
                    $q = preg_replace('/%s/', $v, $q, 1);
                    $q = preg_replace('/%d/', $v, $q, 1);
                }
                return $q;
            }
            public function get_results($q,$output=ARRAY_A){
                $this->last_query = $q;
                $rows = [
                    ['id'=>1,'name'=>'Past 1','date'=>'2024-01-01','page_id'=>10,'mainimageid'=>20],
                    ['id'=>2,'name'=>'Past 2','date'=>'2023-12-01','page_id'=>11,'mainimageid'=>21],
                    ['id'=>3,'name'=>'Past 3','date'=>'2023-11-01','page_id'=>12,'mainimageid'=>22],
                    ['id'=>4,'name'=>'Past 4','date'=>'2023-10-01','page_id'=>13,'mainimageid'=>23],
                    ['id'=>5,'name'=>'Past 5','date'=>'2023-09-01','page_id'=>14,'mainimageid'=>24],
                ];
                if (preg_match('/LIMIT (\d+)/', $q, $m)) {
                    $rows = array_slice($rows, 0, (int)$m[1]);
                }
                return $rows;
            }
        };
    }

    public function test_recent_past_events_queries_archive_and_limits(){
        $sc = TTA_Homepage_Shortcode::get_instance();
        $ref = new \ReflectionClass($sc);
        $method = $ref->getMethod('get_recent_past_events');
        $method->setAccessible(true);
        $events = $method->invoke($sc, 4);
        $this->assertCount(4, $events);
        $this->assertSame('Past 1', $events[0]['name']);
        global $wpdb;
        $this->assertStringContainsString('tta_events_archive', $wpdb->last_query);
    }
}
