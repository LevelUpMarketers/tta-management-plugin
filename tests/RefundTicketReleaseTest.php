<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A','ARRAY_A'); }

class DummyWpdbRefund {
    public $prefix = 'wp_';
    public $data = [];
    public function prepare($q,...$a){
        foreach($a as $v){ $q=preg_replace('/%d/',$v,$q,1); $q=preg_replace('/%s/',$v,$q,1); }
        return $q;
    }
    public function get_var($query){
        if (strpos($query, 'FROM wp_tta_events') !== false) {
            return 11; // event id
        }
        if (strpos($query, 'FROM wp_tta_cart_items') !== false) {
            return count($this->data['wp_tta_cart_items']);
        }
        return null;
    }
    public function get_results($query,$output=ARRAY_A){
        if (strpos($query, 'FROM wp_tta_tickets') !== false) {
            return $this->data['wp_tta_tickets'];
        }
        return [];
    }
    public function query($query){
        if (preg_match('/UPDATE wp_tta_tickets SET ticketlimit = ticketlimit \+ (\d+) WHERE id = (\d+)/',$query,$m)){
            $diff=intval($m[1]); $id=intval($m[2]);
            foreach($this->data['wp_tta_tickets'] as &$row){ if($row['id']==$id){ $row['ticketlimit'] += $diff; }}
        }
    }
}

class RefundTicketReleaseTest extends TestCase {
    protected function setUp(): void {
        if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
        require_once __DIR__ . '/../includes/helpers.php';
        global $wpdb;
        $wpdb = new DummyWpdbRefund();
        $wpdb->data['wp_tta_tickets'] = [ ['id'=>1,'event_ute_id'=>'ev1','ticketlimit'=>0] ];
        $wpdb->data['wp_tta_cart_items'] = [ ['ticket_id'=>1,'expires_at'=>date('Y-m-d H:i:s', time()+600)] ];
        if (!function_exists('current_time')) {
            function current_time( $type = 'mysql', $gmt = false ) {
                return 'timestamp' === $type ? time() : gmdate( 'Y-m-d H:i:s' );
            }
        }
    }

    public function test_event_has_active_cart_reservations(){
        $this->assertTrue( tta_event_has_active_cart_reservations('ev1') );
        global $wpdb;
        $wpdb->data['wp_tta_cart_items'] = [];
        $this->assertFalse( tta_event_has_active_cart_reservations('ev1') );
    }
}
