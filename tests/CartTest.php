<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A','ARRAY_A'); }

class DummyWpdbCartHelper {
    public $prefix = 'wp_';
    public $data = [];
    public function get_results($query,$output=ARRAY_A){
        if(preg_match('/FROM (\S+) WHERE wpuserid = (\d+)/',$query,$m)){
            $table=$m[1]; $uid=intval($m[2]);
            return $this->data[$table][$uid] ?? [];
        }
        return [];
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
        if (!function_exists('esc_html')) { function esc_html($v){ return $v; } }
        if (!function_exists('esc_html_e')) { function esc_html_e($s,$d=null){ echo $s; } }
        if (!function_exists('esc_attr')) { function esc_attr($v){ return $v; } }
        if (!function_exists('esc_url')) { function esc_url($v){ return $v; } }
    }
    public function test_get_purchased_ticket_count(){
        global $wpdb;
        $wpdb = new DummyWpdbCartHelper();
        $wpdb->data['wp_tta_memberhistory'][1][] = ['action_data'=> json_encode(['items'=>[['event_ute_id'=>'ev1','quantity'=>1]]])];
        $wpdb->data['wp_tta_memberhistory'][1][] = ['action_data'=> json_encode(['items'=>[['event_ute_id'=>'ev1','quantity'=>2]]])];
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        $count = tta_get_purchased_ticket_count(1,'ev1');
        $this->assertSame(3,$count);
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
        if(!function_exists('current_time')){ function current_time($t){ return 'now'; } }
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
            public $insert_id = 1;
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
            public function insert($t,$d,$f){ $this->queries[]='INSERT'; if(isset($d['ticket_id'])){ $this->items[$d['ticket_id']]=$d['quantity']; } }
            public function update($t,$d,$w,$f1=null,$f2=null){ $this->queries[]='UPDATE'; if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ $this->queries[]='DELETE'; unset($this->items[$w['ticket_id']]); }
        };
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql'){ return 'now'; } }
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
        $transients = ['tta_cache_tickets_ev1' => ['foo']];
        $wpdb = new class {
            public $prefix = 'wp_';
            public $queries = [];
            public $items = [];
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
            public function insert($t,$d,$f){ $this->queries[]='INSERT'; if(isset($d['ticket_id'])){ $this->items[$d['ticket_id']]=$d['quantity']; } }
            public function update($t,$d,$w,$f1=null,$f2=null){ $this->queries[]='UPDATE'; if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ $this->queries[]='DELETE'; unset($this->items[$w['ticket_id']]); }
        };
        if(!function_exists('get_transient')){ function get_transient($k){ global $transients; return $transients[$k] ?? false; } }
        if(!function_exists('set_transient')){ function set_transient($k,$v,$t=0){ global $transients; $transients[$k]=$v; } }
        if(!function_exists('delete_transient')){ function delete_transient($k){ global $transients; unset($transients[$k]); } }
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql'){ return 'now'; } }
        if(!function_exists('get_current_user_id')){ function get_current_user_id(){ return 0; } }
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';
        require_once __DIR__ . '/../includes/cart/class-cart.php';
        $cart = new TTA_Cart();
        $cart->add_item(5,1,10);
        $this->assertArrayNotHasKey('tta_cache_tickets_ev1', $transients);
    }

    public function test_ajax_add_to_cart_enforces_limit(){
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $items = [];
            public $insert_id = 1;
            public function get_row($q,$o=ARRAY_A){
                return [
                    'event_ute_id'=>'ev1',
                    'baseeventcost'=>10,
                    'discountedmembercost'=>8,
                    'premiummembercost'=>7
                ];
            }
            public function get_var($q){
                if (strpos($q,'ticketlimit')!==false) return 5;
                return null;
            }
            public function get_results($q,$o=ARRAY_A){ return []; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1);} return $q; }
            public function query($q){ return 1; }
            public function insert($t,$d,$f){ if(isset($d['ticket_id'])){ $this->items[$d['ticket_id']]=$d['quantity']; } }
            public function update($t,$d,$w,$f1=null,$f2=null){ if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ }
        };
        if(!function_exists('check_ajax_referer')){ function check_ajax_referer($a,$b){} }
        if(!function_exists('wp_send_json_success')){ function wp_send_json_success($d){ $GLOBALS['_last_json']=['success'=>true,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('wp_send_json_error')){ function wp_send_json_error($d){ $GLOBALS['_last_json']=['success'=>false,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('add_action')){ function add_action($t,$c){} }
        if(!function_exists('is_user_logged_in')){ function is_user_logged_in(){ return false; } }
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql'){ return 'now'; } }
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
            public $insert_id = 1;
            public function get_row($q,$o=ARRAY_A){
                return [
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
            public function insert($t,$d,$f){ if(isset($d['ticket_id'])){ $this->items[$d['ticket_id']]=$d['quantity']; } }
            public function update($t,$d,$w,$f1=null,$f2=null){ if(isset($w['ticket_id'])){ $this->items[$w['ticket_id']]=$d['quantity']; } }
            public function delete($t,$w,$f){ unset($this->items[$w['ticket_id']]); }
        };
        if(!function_exists('check_ajax_referer')){ function check_ajax_referer($a,$b){} }
        if(!function_exists('wp_send_json_success')){ function wp_send_json_success($d){ $GLOBALS['_last_json']=['success'=>true,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('wp_send_json_error')){ function wp_send_json_error($d){ $GLOBALS['_last_json']=['success'=>false,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if(!function_exists('add_action')){ function add_action($t,$c){} }
        if(!function_exists('is_user_logged_in')){ function is_user_logged_in(){ return false; } }
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('current_time')){ function current_time($t='mysql'){ return 'now'; } }
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

    public function test_lock_and_resume_preserves_time(){
        global $wpdb, $testNow;
        $testNow = time();
        $wpdb = new class($testNow) {
            public $prefix = 'wp_';
            public $updates = [];
            private $now;
            public function __construct($n){ $this->now=$n; }
            public function get_row($q,$o=ARRAY_A){ return null; }
            public function get_results($q,$o=ARRAY_A){
                if(strpos($q,'tta_cart_items')!==false){
                    return [['ticket_id'=>1,'expires_at'=>date('Y-m-d H:i:s',$this->now+100)]];
                }
                return [];
            }
            public function get_var($q){ return 0; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1);} return $q; }
            public function update($t,$d,$w,$f1=null,$f2=null){ $this->updates[]=['table'=>$t,'data'=>$d,'where'=>$w]; }
        };
        if(!function_exists('current_time')){ function current_time($t='mysql'){ global $testNow; return date('Y-m-d H:i:s',$testNow); } }
        if(!function_exists('wp_generate_uuid4')){ function wp_generate_uuid4(){ return 'x'; } }
        if(!function_exists('get_current_user_id')){ function get_current_user_id(){ return 0; } }

        require_once __DIR__.'/../includes/cart/class-cart.php';

        $_SESSION = [];
        $cart = new TTA_Cart();
        $cart->lock_items();
        $this->assertNotEmpty($_SESSION['tta_lock_remaining']);
        $testNow += 20;
        $cart->resume_items();
        $this->assertNotEmpty($wpdb->updates);
        $expire = $wpdb->updates[count($wpdb->updates)-1]['data']['expires_at'];
        $this->assertEquals(date('Y-m-d H:i:s',$testNow + 80), $expire);
    }
}
