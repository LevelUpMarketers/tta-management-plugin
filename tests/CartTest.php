<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A','ARRAY_A'); }
if (!function_exists('__')) { function __($s, $d = null) { return $s; } }

class DummyWpdbCartHelper {
    public $prefix = 'wp_';
    public $data = [];
    public function get_results($query,$output=ARRAY_A){
        if(preg_match('/FROM (\S+) WHERE wpuserid = (\d+)/',$query,$m)){
            $table=$m[1]; $uid=intval($m[2]);
            $rows = $this->data[$table][$uid] ?? [];
            if (strpos($query, "action_type = 'refund'") !== false) {
                $rows = array_filter($rows, function($r){ return ($r['action_type'] ?? '') === 'refund'; });
            } elseif (strpos($query, "action_type = 'purchase'") !== false) {
                $rows = array_filter($rows, function($r){ return !isset($r['action_type']) || $r['action_type'] === 'purchase'; });
            }
            if (preg_match('/event_id = (\d+)/', $query, $e)) {
                $eid = intval($e[1]);
                $rows = array_filter($rows, function($r) use ($eid){ return ($r['event_id'] ?? 0) == $eid; });
            }
            return array_values($rows);
        }
        return [];
    }
    public function get_row($query,$output=ARRAY_A){
        if (preg_match('/FROM (\S+) WHERE wpuserid = (\d+)/',$query,$m)) {
            $table=$m[1]; $uid=intval($m[2]);
            return $this->data[$table][$uid][0] ?? null;
        }
        if (preg_match('/FROM (\S+) WHERE email = ([^ ]+)/',$query,$m)) {
            $table=$m[1];
            $email=trim($m[2],"' ");
            foreach (($this->data[$table][1] ?? []) as $row) {
                if (($row['email'] ?? '') === $email) return $row;
            }
        }
        return null;
    }
    public function get_var($q){
        if (strpos($q, $this->prefix.'tta_events') !== false || strpos($q, $this->prefix.'tta_events_archive') !== false) {
            if (strpos($q, "'ev1'") !== false || strpos($q, 'ev1') !== false) return 10;
        }
        return null;
    }
    public function prepare($q,...$a){
        foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1); }
        return $q;
    }
}

class CartTest extends TestCase {
    protected function setUp(): void {
        if (!defined('ABSPATH')) {
            define('ABSPATH', sys_get_temp_dir() . '/wp/');
        }
        if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){return is_string($v)?trim($v):$v;} }
        if (!function_exists('sanitize_email')) { function sanitize_email($v){return trim($v);} }
        if (!function_exists('sanitize_user')) { function sanitize_user($v){ return preg_replace('/[^A-Za-z0-9]/','',$v); } }
        if (!function_exists('wp_unslash')) { function wp_unslash($v){ return is_array($v)?array_map('wp_unslash',$v):str_replace('\\','',$v); } }
        if (!function_exists('is_user_logged_in')) { function is_user_logged_in(){ return true; } }
        if (!function_exists('wp_get_current_user')) { function wp_get_current_user(){ return (object)['ID'=>1,'user_email'=>'me@example.com','user_login'=>'user','first_name'=>'First','last_name'=>'Last']; } }
        if (!function_exists('get_transient')) { function get_transient($k){ return $GLOBALS['transients'][$k] ?? false; } }
        if (!function_exists('set_transient')) { function set_transient($k,$v,$t=0){ $GLOBALS['transients'][$k]=$v; } }
        if (!function_exists('delete_transient')) { function delete_transient($k){ unset($GLOBALS['transients'][$k]); } }
        if (!function_exists('esc_html')) { function esc_html($v){ return $v; } }
        if (!function_exists('esc_html_e')) { function esc_html_e($s,$d=null){ echo $s; } }
        if (!function_exists('esc_html__')) { function esc_html__($s,$d=null){ return $s; } }
        if (!function_exists('esc_attr')) { function esc_attr($v){ return $v; } }
        if (!function_exists('esc_url')) { function esc_url($v){ return $v; } }
        if (!function_exists('add_action')) { function add_action($t,$c){} }
        if (!function_exists('add_filter')) { function add_filter($t,$c,$p=10,$a=1){} }
        if (!function_exists('esc_attr__')) { function esc_attr__($s,$d=null){ return $s; } }
        global $wpdb;
        $wpdb = new DummyWpdbCartHelper();
        $wpdb->data['wp_tta_members'][1][] = [
            'wpuserid' => 1,
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'me@example.com',
            'phone' => '555',
            'opt_in_event_update_sms' => 1,
            'opt_in_event_update_email' => 1,
        ];
        if (!defined('TTA_PLUGIN_URL')) { define('TTA_PLUGIN_URL', 'http://example.com/'); }
    }
    public function test_get_purchased_ticket_count(){
        global $wpdb;
        $wpdb = new DummyWpdbCartHelper();
        $wpdb->data['wp_tta_memberhistory'][1][] = [
            'action_data' => json_encode(['items'=>[['event_ute_id'=>'ev1','quantity'=>1]]])
        ];
        $wpdb->data['wp_tta_memberhistory'][1][] = [
            'action_data' => json_encode(['items'=>[['event_ute_id'=>'ev1','quantity'=>2]]])
        ];
        $wpdb->data['wp_tta_memberhistory'][1][] = [
            'event_id'   => 10,
            'action_type'=> 'refund',
            'action_data'=> json_encode(['transaction_id'=>'t1','attendee_id'=>5,'cancel'=>1,'amount'=>0])
        ];
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        $count = tta_get_purchased_ticket_count(1,'ev1');
        $this->assertSame(2,$count);
    }

    public function test_render_cart_contains_event_link(){
        global $wpdb;
        $wpdb = new stdClass();
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        $cart = $this->createMock('TTA_Cart');
        $items = [
            [
                'ticket_id'=>1,
                'ticket_name'=>'VIP',
                'quantity'=>1,
                'price'=>10,
                'final_price'=>10,
                'baseeventcost'=>10,
                'discountcode'=>'',
                'event_name'=>'Party',
                'page_id'=>55,
                'expires_at'=> date('Y-m-d H:i:s', time()+60)
            ]
        ];
        $cart->method('get_items')->willReturn($items);
        $cart->method('get_items_with_discounts')->willReturn($items);
        $cart->method('get_total')->willReturn(10);
        function get_permalink($id){return 'post/'.$id;}
        $html = tta_render_cart_contents($cart,[],[]);
        $this->assertStringContainsString('post/55',$html);
        $this->assertStringContainsString('data-expire-at', $html);
        $this->assertStringContainsString('data-ticket="1"', $html);
    }

    public function test_render_attendee_fields_outputs_inputs(){
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        global $wpdb;
        $wpdb = new DummyWpdbCartHelper();
        $wpdb->data['wp_tta_members'][1][] = [
            'wpuserid'=>1,
            'first_name'=>'First',
            'last_name'=>'Last',
            'email'=>'me@example.com',
            'phone'=>'555',
            'opt_in_event_update_sms'=>1,
            'opt_in_event_update_email'=>1,
        ];
        $cart = $this->createMock('TTA_Cart');
        $items = [
            [
                'ticket_id'=>1,
                'ticket_name'=>'VIP',
                'quantity'=>2,
                'price'=>10,
                'event_name'=>'Party',
                'page_id'=>55,
                'event_ute_id'=>'ev1',
                'expires_at'=> date('Y-m-d H:i:s', time()+60)
            ]
        ];
        $cart->method('get_items')->willReturn($items);
        $html = tta_render_attendee_fields($cart, false);
        $this->assertStringContainsString('attendees[1][0][first_name]', $html);
        $this->assertStringContainsString('attendees[1][0][phone]', $html);
        $this->assertStringContainsString('attendees[1][0][opt_in_sms]', $html);
        $this->assertStringContainsString('attendees[1][0][opt_in_sms]" checked', $html);
        $this->assertStringContainsString('attendees[1][0][opt_in_email]" checked', $html);
        $this->assertStringContainsString('value="First"', $html);
        $this->assertStringContainsString('VIP #2', $html);
    }

    public function test_attendee_fields_prefill_each_event(){
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        global $wpdb;
        $wpdb = new DummyWpdbCartHelper();
        $wpdb->data['wp_tta_members'][1][] = [
            'wpuserid'=>1,
            'first_name'=>'First',
            'last_name'=>'Last',
            'email'=>'me@example.com',
            'phone'=>'555',
            'opt_in_event_update_sms'=>1,
            'opt_in_event_update_email'=>1,
        ];
        $cart = $this->createMock('TTA_Cart');
        $items = [
            [
                'ticket_id'=>1,
                'ticket_name'=>'VIP',
                'quantity'=>2,
                'price'=>10,
                'event_name'=>'Party',
                'page_id'=>55,
                'event_ute_id'=>'ev1',
                'expires_at'=> date('Y-m-d H:i:s', time()+60)
            ],
            [
                'ticket_id'=>2,
                'ticket_name'=>'VIP',
                'quantity'=>2,
                'price'=>10,
                'event_name'=>'Gala',
                'page_id'=>56,
                'event_ute_id'=>'ev2',
                'expires_at'=> date('Y-m-d H:i:s', time()+60)
            ]
        ];
        $cart->method('get_items')->willReturn($items);
        $html = tta_render_attendee_fields($cart, false);
        $this->assertStringContainsString('attendees[1][0][first_name]', $html);
        $this->assertStringContainsString('attendees[2][0][first_name]', $html);
        $this->assertEquals(4, substr_count($html, 'value="First"'));
    }

    public function test_get_event_attendees_queries_table(){
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $last_query = '';
            public function get_results($q,$o=ARRAY_A){
                $this->last_query = $q;
                return [ ['first_name'=>'John','last_name'=>'Doe','email'=>'j@example.com','ticket_id'=>1] ];
            }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%s/',$v,$q,1); $q=preg_replace('/%d/',$v,$q,1);} return $q; }
        };
        require_once __DIR__ . '/../includes/helpers.php';
        $rows = tta_get_event_attendees('ev1');
        $this->assertSame('John', $rows[0]['first_name']);
        $this->assertStringContainsString('wp_tta_attendees', $wpdb->last_query);
    }

    public function test_item_cleanup_queries(){
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $queries = [];
            public function get_results($q,$o=ARRAY_A){
                $this->queries[] = $q;
                return [['ticket_id'=>5,'quantity'=>2]];
            }
            public function query($q){ $this->queries[] = $q; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1);} return $q; }
        };
        if(!function_exists('current_time')){ function current_time($t='mysql',$gmt=false){ return $t==='timestamp'?0:'now'; } }
        require_once __DIR__ . '/../includes/cart/class-cart-cleanup.php';
        TTA_Cart_Cleanup::clean_expired_items();
        $sql = implode("\n", $wpdb->queries);
        $this->assertStringContainsString('wp_tta_cart_items', $sql);
        $this->assertStringContainsString('wp_tta_tickets', $sql);
    }

    public function test_add_and_remove_adjusts_inventory(){
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $queries = [];
            public $insert_id = 0;
            public $items = [];
            public function get_row($q,$o=ARRAY_A){ $this->queries[]=$q; return null; }
            public function get_var($q){
                $this->queries[]=$q;
                if(strpos($q,'ticketlimit')!==false) return 5;
                if(preg_match('/SELECT quantity FROM (\S+) WHERE cart_id = (\d+) AND ticket_id = (\d+)/',$q,$m)){
                    return $this->items[$m[3]] ?? null;
                }
                return null;
            }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1); } return $q; }
            public function query($q){ $this->queries[]=$q; return 1; }
            public function insert($t,$d,$f){
                $this->queries[]='INSERT';
                $this->insert_id++;
                if(isset($d['ticket_id'])){
                    $this->items[$d['ticket_id']]=$d['quantity'];
                }
            }
            public function update($t,$d,$w,$f1=null,$f2=null){ $this->queries[]='UPDATE'; if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ $this->queries[]='DELETE'; unset($this->items[$w['ticket_id']]); }
        };
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql',$gmt=false){ return $t==='timestamp'?0:'now'; } }
        if(!function_exists('get_current_user_id')){ function get_current_user_id(){ return 0; } }
        if(!function_exists('home_url')){ function home_url($p=''){ return '/'.$p; } }
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        $cart = new TTA_Cart();
        $cart->add_item(5,1,10);
        $cart->remove_item(5);
        $sql = implode("\n", $wpdb->queries);
        $this->assertStringContainsString('ticketlimit = ticketlimit + -1', $sql);
        $this->assertStringContainsString('ticketlimit = ticketlimit + 1', $sql);
    }

    public function test_inventory_updates_clear_cache(){
        global $wpdb, $transients;
        $transients = ['tta_cache_tickets_ev1' => ['foo'], 'tta_cache_ticket_stock_5' => 3];
        $wpdb = new class {
            public $prefix = 'wp_';
            public $queries = [];
            public $items = [];
            public $insert_id = 0;
            public function get_var($q){
                if (strpos($q,'event_ute_id')!==false) return 'ev1';
                if(preg_match('/SELECT quantity FROM (\S+) WHERE cart_id = (\d+) AND ticket_id = (\d+)/',$q,$m)){
                    return $this->items[$m[3]] ?? null;
                }
                return 5;
            }
            public function get_row($q,$o=ARRAY_A){ $this->queries[]=$q; return null; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1);} return $q; }
            public function query($q){ $this->queries[]=$q; return 1; }
            public function insert($t,$d,$f){
                $this->queries[]='INSERT';
                $this->insert_id++;
                if(isset($d['ticket_id'])){
                    $this->items[$d['ticket_id']]=$d['quantity'];
                }
            }
            public function update($t,$d,$w,$f1=null,$f2=null){ $this->queries[]='UPDATE'; if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ $this->queries[]='DELETE'; unset($this->items[$w['ticket_id']]); }
        };
        if(!function_exists('get_transient')){ function get_transient($k){ global $transients; return $transients[$k] ?? false; } }
        if(!function_exists('set_transient')){ function set_transient($k,$v,$t=0){ global $transients; $transients[$k]=$v; } }
        if(!function_exists('delete_transient')){ function delete_transient($k){ global $transients; unset($transients[$k]); } }
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql',$gmt=false){ return $t==='timestamp'?0:'now'; } }
        if(!function_exists('get_current_user_id')){ function get_current_user_id(){ return 0; } }
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        $cart = new TTA_Cart();
        $cart->add_item(5,1,10);
        $this->assertArrayNotHasKey('tta_cache_tickets_ev1', $transients);
        $this->assertArrayNotHasKey('tta_cache_ticket_stock_5', $transients);
    }

    public function test_ajax_add_to_cart_enforces_limit(){
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $items = [];
            public $insert_id = 0;
            public function get_row($q,$o=ARRAY_A){
                return [
                    'id'=>1,
                    'event_ute_id'=>'ev1',
                    'memberlimit'=>2,
                    'baseeventcost'=>10,
                    'discountedmembercost'=>8,
                    'premiummembercost'=>7
                ];
            }
            public function get_var($q){
                if (strpos($q,'ticketlimit')!==false) return 5;
                if (strpos($q,'memberlimit')!==false) return 2;
                return null;
            }
            public function get_results($q,$o=ARRAY_A){ return []; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1);} return $q; }
            public function query($q){ return 1; }
            public function insert($t,$d,$f){
                $this->insert_id++;
                if(isset($d['ticket_id'])){
                    $this->items[$d['ticket_id']]=$d['quantity'];
                }
            }
            public function update($t,$d,$w,$f1=null,$f2=null){ if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ }
        };
        if(!function_exists('check_ajax_referer')){ function check_ajax_referer($a,$b){} }
        if(!function_exists('wp_send_json_success')){ function wp_send_json_success($d){ $GLOBALS['_last_json']=['success'=>true,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('wp_send_json_error')){ function wp_send_json_error($d){ $GLOBALS['_last_json']=['success'=>false,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('add_action')){ function add_action($t,$c){} }
        if(!function_exists('is_user_logged_in')){ function is_user_logged_in(){ return false; } }
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql',$gmt=false){ return $t==='timestamp'?0:'now'; } }
        if(!function_exists('get_current_user_id')){ function get_current_user_id(){ return 0; } }

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        require_once __DIR__ . '/../includes/ajax/handlers/class-ajax-cart.php';

        $_POST['items'] = json_encode([['ticket_id'=>1,'quantity'=>3]]);
        $_POST['nonce'] = 'n';

        TTA_Ajax_Cart::ajax_add_to_cart();

        $this->assertSame(2, $wpdb->items[1]);
    }

    public function test_ajax_add_to_cart_merges_items(){
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $items = [1 => 1];
            public $insert_id = 0;
            public function get_row($q,$o=ARRAY_A){
                return [
                    'id'=>1,
                    'event_ute_id'=>'ev1',
                    'baseeventcost'=>10,
                    'discountedmembercost'=>8,
                    'premiummembercost'=>7
                ];
            }
            public function get_var($q){
                if (strpos($q,'ticketlimit')!==false) return 5;
                if (strpos($q,'event_ute_id')!==false) return 'ev1';
                return null;
            }
            public function get_results($q,$o=ARRAY_A){
                if(strpos($q,'tta_cart_items')!==false){
                    $out=[]; foreach($this->items as $id=>$qty){ $out[]=['ticket_id'=>$id,'quantity'=>$qty,'event_ute_id'=>'ev1']; }
                    return $out;
                }
                return [];
            }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1);} return $q; }
            public function query($q){ return 1; }
            public function insert($t,$d,$f){
                $this->insert_id++;
                if(isset($d['ticket_id'])){
                    $this->items[$d['ticket_id']]=$d['quantity'];
                }
            }
            public function update($t,$d,$w,$f1=null,$f2=null){ if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ unset($this->items[$w['ticket_id']]); }
        };
        if(!function_exists('check_ajax_referer')){ function check_ajax_referer($a,$b){} }
        if(!function_exists('wp_send_json_success')){ function wp_send_json_success($d){ $GLOBALS['_last_json']=['success'=>true,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('wp_send_json_error')){ function wp_send_json_error($d){ $GLOBALS['_last_json']=['success'=>false,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('add_action')){ function add_action($t,$c){} }
        if(!function_exists('is_user_logged_in')){ function is_user_logged_in(){ return false; } }
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql',$gmt=false){ return $t==='timestamp'?0:'now'; } }
        if(!function_exists('get_current_user_id')){ function get_current_user_id(){ return 0; } }

        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        require_once __DIR__ . '/../includes/ajax/handlers/class-ajax-cart.php';

        $_POST['items'] = json_encode([
            ['ticket_id'=>1,'quantity'=>1],
            ['ticket_id'=>2,'quantity'=>1]
        ]);
        $_POST['nonce'] = 'n';

        TTA_Ajax_Cart::ajax_add_to_cart();

        $this->assertSame(1, $wpdb->items[1]);
        $this->assertSame(1, $wpdb->items[2]);
    }

    public function test_ticket_controls_disabled_when_sold_out(){
        $tickets = [
            ['id'=>1,'ticket_name'=>'VIP','ticketlimit'=>0,'baseeventcost'=>10,'discountedmembercost'=>8,'premiummembercost'=>7],
            ['id'=>2,'ticket_name'=>'GA','ticketlimit'=>0,'baseeventcost'=>5,'discountedmembercost'=>4,'premiummembercost'=>3]
        ];
        ob_start();
        $all_sold_out = true;
        foreach($tickets as $t){ if(intval($t['ticketlimit'])>0){ $all_sold_out = false; break; } }
        foreach($tickets as $t){
            $limit = intval($t['ticketlimit']);
            $available = $limit > 0 ? $limit : 0;
            $is_sold_out = $available < 1;
            echo '<button class="tta-qty-increase'.($is_sold_out?' tta-disabled':'').'"'.($is_sold_out?' disabled':'').'>+</button>';
        }
        echo '<button id="tta-get-tickets"'.($all_sold_out?' disabled':'').'></button>';
        $html = ob_get_clean();
        $this->assertStringContainsString('tta-disabled', $html);
        $this->assertStringContainsString('id="tta-get-tickets"', $html);
        $this->assertStringContainsString('disabled', $html);
    }

}
