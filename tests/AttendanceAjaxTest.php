<?php
use PHPUnit\Framework\TestCase;

if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return is_string($v)?trim($v):$v; } }
if (!function_exists('sanitize_email')) { function sanitize_email($v){ return is_string($v)?trim($v):$v; } }

class AttendanceAjaxTest extends TestCase {
    public function test_mark_pending_no_show_updates_only_pending() {
        if (!function_exists('check_ajax_referer')) {
            function check_ajax_referer($a,$b) {}
        }
        if (!function_exists('wp_send_json_success')) {
            function wp_send_json_success($data = null) { return ['success'=>true,'data'=>$data]; }
        }
        if (!function_exists('wp_send_json_error')) {
            function wp_send_json_error($data = null) { return ['success'=>false,'data'=>$data]; }
        }
        global $set_calls;
        $set_calls = [];
        if (!function_exists('tta_set_attendance_status')) {
            function tta_set_attendance_status($id,$status){
                global $set_calls; $set_calls[] = [$id,$status];
            }
        }
        if (!function_exists('tta_get_event_attendees_with_status')) {
            function tta_get_event_attendees_with_status($ute){
                return [
                    ['id'=>1,'status'=>'pending'],
                    ['id'=>2,'status'=>'checked_in'],
                    ['id'=>3,'status'=>'pending'],
                ];
            }
        }
        require_once __DIR__.'/../includes/ajax/handlers/class-ajax-attendance.php';
        $_POST = ['event_ute_id'=>'ev1','nonce'=>'n'];
        TTA_Ajax_Attendance::mark_pending_no_show();
        $this->assertSame([[1,'no_show'],[3,'no_show']], $set_calls);
    }

    public function test_set_attendance_reload_when_not_pending() {
        if (!function_exists('check_ajax_referer')) { function check_ajax_referer($a,$b) {} }
        if (!function_exists('wp_send_json_success')) { function wp_send_json_success($data = null) { return ['success'=>true,'data'=>$data]; } }
        if (!function_exists('wp_send_json_error')) { function wp_send_json_error($data = null) { return ['success'=>false,'data'=>$data]; } }
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public function get_row($q,$fmt){ return ['email'=>'a@test.com','status'=>'checked_in']; }
            public function prepare($q,...$a){ return $q; }
        };
        require_once __DIR__.'/../includes/ajax/handlers/class-ajax-attendance.php';
        $_POST = ['attendee_id'=>1,'status'=>'no_show','nonce'=>'n'];
        $res = TTA_Ajax_Attendance::set_attendance();
        $this->assertTrue($res['data']['reload']);
    }
}
