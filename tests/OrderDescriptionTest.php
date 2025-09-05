<?php
use PHPUnit\Framework\TestCase;
if(!defined('ARRAY_A')){ define('ARRAY_A','ARRAY_A'); }

if(!function_exists('sanitize_text_field')){
    function sanitize_text_field($v){ return is_string($v)?trim($v):$v; }
}
if(!function_exists('sanitize_email')){
    function sanitize_email($v){ return trim($v); }
}
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(!class_exists('TTA_Cart')){
    class TTA_Cart {
        public function get_items(){
            return [
                ['event_name' => 'Sample Event One'],
                ['event_name' => 'Another Event Two'],
            ];
        }
    }
}
require_once __DIR__ . '/../includes/helpers.php';

class OrderDescriptionTest extends TestCase {
    protected function setUp(): void {
        $_SESSION = [];
    }

    public function test_description_includes_events_and_membership(){
        $_SESSION['tta_membership_purchase'] = 'premium';
        $desc = tta_build_order_description();
        $this->assertStringContainsString('Sample Event One', $desc);
        $this->assertStringContainsString('Another Event Two', $desc);
        $this->assertStringContainsString('Premium', $desc);
    }

    public function test_normalize_limits_length(){
        $long = str_repeat('A', 300);
        $norm = tta_normalize_authnet_description($long);
        $this->assertLessThanOrEqual(255, strlen($norm));
    }
}
