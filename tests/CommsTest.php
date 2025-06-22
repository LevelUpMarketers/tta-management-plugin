<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A','ARRAY_A'); }

class CommsTest extends TestCase {
    protected function setUp(): void {
        if (!defined('ABSPATH')) { define('ABSPATH', sys_get_temp_dir().'/wp/'); }
        if (!function_exists('get_option')) { function get_option($k,$d=null){ return $GLOBALS['options'][$k] ?? $d; } }
        if (!function_exists('update_option')) { function update_option($k,$v,$autoload=true){ $GLOBALS['options'][$k]=$v; } }
        if (!function_exists('__')) { function __($s,$d=null){ return $s; } }
        require_once __DIR__.'/../includes/admin/class-comms-admin.php';
        require_once __DIR__.'/../includes/helpers.php';
        $GLOBALS['options'] = [];
    }

    public function test_defaults_returned_when_option_missing() {
        $templates = tta_get_comm_templates();
        $this->assertArrayHasKey('purchase', $templates);
        $this->assertSame('Thanks for Registering!', $templates['purchase']['email_subject']);
    }

    public function test_saved_values_override_defaults() {
        $custom = [
            'purchase' => [ 'email_subject' => 'Custom', 'email_body' => 'B', 'sms_text' => 'C' ]
        ];
        update_option('tta_comms_templates', $custom);
        $templates = tta_get_comm_templates();
        $this->assertSame('Custom', $templates['purchase']['email_subject']);
        $this->assertSame('B', $templates['purchase']['email_body']);
    }
}
