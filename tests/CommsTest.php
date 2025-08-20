<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A','ARRAY_A'); }

class CommsTest extends TestCase {
    protected function setUp(): void {
        if (!defined('ABSPATH')) { define('ABSPATH', sys_get_temp_dir().'/wp/'); }
        if (!function_exists('get_option')) { function get_option($k,$d=null){ return $GLOBALS['options'][$k] ?? $d; } }
        if (!function_exists('update_option')) { function update_option($k,$v,$autoload=true){ $GLOBALS['options'][$k]=$v; } }
        if (!function_exists('__')) { function __($s,$d=null){ return $s; } }
        if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_textarea_field')) { function sanitize_textarea_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_key')) { function sanitize_key($v){ return preg_replace('/[^a-zA-Z0-9_]/','',strtolower($v)); } }
        if (!function_exists('add_action')) { function add_action($h,$c,$p=10,$a=1){} }
        if (!function_exists('add_filter')) { function add_filter($h,$c,$p=10,$a=1){} }
        if (!function_exists('wp_unslash')) { function wp_unslash($v){ return is_array($v)?array_map('wp_unslash',$v):str_replace('\\','',$v); } }
        require_once __DIR__.'/../includes/admin/class-comms-admin.php';
        require_once __DIR__.'/../includes/helpers.php';
        require_once __DIR__.'/../includes/ajax/handlers/class-ajax-comms.php';
        $GLOBALS['options'] = [];
        if (!function_exists('wp_create_nonce')) { function wp_create_nonce($a=''){ return 'nonce'; } }
    }

    public function test_defaults_returned_when_option_missing() {
        $templates = tta_get_comm_templates();
        $this->assertArrayHasKey('purchase', $templates);
        $this->assertSame('Thanks for Registering!', $templates['purchase']['email_subject']);
        $this->assertSame('External', $templates['purchase']['type']);
        $this->assertSame('Event Confirmation', $templates['purchase']['category']);
        $this->assertArrayHasKey('waitlist_available', $templates);
    }

    public function test_saved_values_override_defaults() {
        $custom = [
            'purchase' => [ 'email_subject' => 'Custom', 'email_body' => 'B', 'sms_text' => 'C' ]
        ];
        update_option('tta_comms_templates', $custom);
        $templates = tta_get_comm_templates();
        $this->assertSame('Custom', $templates['purchase']['email_subject']);
        $this->assertSame('B', $templates['purchase']['email_body']);
        $this->assertSame('External', $templates['purchase']['type']);
    }

    public function test_ajax_save_template() {
        $_POST = [
            'template_key'      => 'purchase',
            'email_subject'     => 'It\'s New',
            'email_body'        => 'You\'re invited',
            'sms_text'          => 'Don\'t forget',
            'tta_comms_save_nonce' => wp_create_nonce('tta_comms_save_action'),
        ];
        // fake WordPress functions
        if (!function_exists('check_ajax_referer')) {
            function check_ajax_referer($a,$b){ return true; }
        }
        if (!function_exists('current_user_can')) {
            function current_user_can(){ return true; }
        }
        if (!function_exists('wp_send_json_success')) {
            function wp_send_json_success($arr){ $GLOBALS['_last_json']=['success'=>true,'data'=>$arr]; return $GLOBALS['_last_json']; }
        }
        if (!function_exists('wp_send_json_error')) {
            function wp_send_json_error($arr){ $GLOBALS['_last_json']=['success'=>false,'data'=>$arr]; return $GLOBALS['_last_json']; }
        }
        TTA_Ajax_Comms::save_template();
        $this->assertTrue($GLOBALS['_last_json']['success']);
        $templates = get_option('tta_comms_templates');
        $this->assertSame("It's New", $templates['purchase']['email_subject']);
        $this->assertSame("You're invited", $templates['purchase']['email_body']);
    }

    public function test_get_comm_templates_unslashes_saved_values() {
        $stored = [
            'purchase' => [
                'email_subject' => 'You\\\'re registered',
                'email_body'    => 'It\\\'s confirmed',
                'sms_text'      => ''
            ]
        ];
        update_option('tta_comms_templates', $stored);

        $templates = tta_get_comm_templates();
        $this->assertSame("You're registered", $templates['purchase']['email_subject']);
        $this->assertSame("It's confirmed", $templates['purchase']['email_body']);
    }
}
