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
        $cart->method('get_items')->willReturn([
            [
                'ticket_id'=>1,
                'ticket_name'=>'VIP',
                'quantity'=>1,
                'price'=>10,
                'event_name'=>'Party',
                'page_id'=>55,
                'expires_at'=> date('Y-m-d H:i:s', time()+60)
            ]
        ]);
        $cart->method('get_total')->willReturn(10);
        function get_permalink($id){return 'post/'.$id;}
        $html = tta_render_cart_contents($cart,'');
        $this->assertStringContainsString('post/55',$html);
        $this->assertStringContainsString('data-expire', $html);
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
}
