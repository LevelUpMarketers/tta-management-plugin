<?php
use PHPUnit\Framework\TestCase;
if (!defined('ABSPATH')) { define('ABSPATH', sys_get_temp_dir().'/wp/'); }

class CronTest extends TestCase {
    protected function setUp(): void {
        if (!function_exists('wp_next_scheduled')) { function wp_next_scheduled($h){ return false; } }
        if (!function_exists('wp_schedule_event')) { function wp_schedule_event($t,$rec,$hook){ $GLOBALS['scheduled'][] = [$t,$rec,$hook]; } }
        if (!function_exists('wp_clear_scheduled_hook')) { function wp_clear_scheduled_hook($hook){ $GLOBALS['cleared'][] = $hook; } }
        if (!function_exists('add_filter')) { function add_filter($h,$cb){ $GLOBALS['filters'][] = [$h,$cb]; return true; } }
        require_once __DIR__ . '/../includes/cart/class-cart-cleanup.php';
    }

    protected function tearDown(): void {
        $GLOBALS['scheduled'] = [];
        $GLOBALS['cleared'] = [];
        $GLOBALS['filters'] = [];
    }

    public function test_add_schedule_registers_interval() {
        $schedules = TTA_Cart_Cleanup::add_schedule([]);
        $this->assertArrayHasKey('tta_ten_minutes', $schedules);
        $this->assertSame(600, $schedules['tta_ten_minutes']['interval']);
    }

    public function test_schedule_and_clear_events() {
        TTA_Cart_Cleanup::schedule_event();
        $this->assertNotEmpty($GLOBALS['scheduled']);
        TTA_Cart_Cleanup::clear_event();
        $this->assertContains('tta_cart_cleanup_event', $GLOBALS['cleared']);
        $this->assertContains('tta_cart_item_cleanup_event', $GLOBALS['cleared']);
    }
}
