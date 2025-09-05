<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A', 'ARRAY_A'); }
class MassEmailTest extends TestCase {
    public static $sent = [];
    protected function setUp(): void {
        if (!function_exists('sanitize_email')) { function sanitize_email($v){ return trim($v); } }
        if (!function_exists('wp_mail')) { function wp_mail($to,$sub,$body,$headers=[]){ MassEmailTest::$sent[]=$to; return true; } }
        if (!function_exists('tta_get_event_attendees')) { function tta_get_event_attendees($u){ return [ ['first_name'=>'Ann','last_name'=>'A','email'=>'a@example.com'] ]; } }
        if (!function_exists('tta_get_event_for_email')) { function tta_get_event_for_email($u){ return ['id'=>1,'name'=>'Event']; } }
        if (!function_exists('tta_get_member_row_by_email')) { function tta_get_member_row_by_email($e){ return null; } }
        if (!function_exists('tta_get_membership_level_by_email')) { function tta_get_membership_level_by_email($e){ return 'free'; } }
        if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return trim($v); } }
        if (!function_exists('home_url')) { function home_url($p=''){ return '/'; } }
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/email/class-email-handler.php';
    }
    public function test_send_mass_email_returns_count(){
        self::$sent = [];
        $handler = TTA_Email_Handler::get_instance();
        $count = $handler->send_mass_email('ute', ['a@example.com','bad'], 'Subject', 'Body');
        $this->assertSame(1, $count);
        $this->assertSame(['a@example.com'], self::$sent);
    }
}
