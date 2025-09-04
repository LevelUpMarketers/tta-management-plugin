<?php
use PHPUnit\Framework\TestCase;

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
}
