<?php
use PHPUnit\Framework\TestCase;

if (!defined('ARRAY_A')) { define('ARRAY_A', 'ARRAY_A'); }
if (!function_exists('sanitize_email')) { function sanitize_email($v){ return is_string($v)?trim($v):$v; } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return is_string($v)?trim($v):$v; } }
if (!function_exists('get_userdata')) { function get_userdata($id){ return (object)['user_email'=>'user@example.com']; } }
if (!function_exists('get_transient')) { function get_transient($k){ return false; } }
if (!function_exists('set_transient')) { function set_transient($k,$v,$t){ return true; } }
if (!function_exists('delete_transient')) { function delete_transient($k){ return true; } }
if (!class_exists('TTA_Cache')) {
    class TTA_Cache {
        public static function get($k){ return false; }
        public static function set($k,$v,$t=0){ return true; }
        public static function delete($k){ return true; }
    }
}
if (!defined('TTA_BAN_UNTIL_REENTRY')) { define('TTA_BAN_UNTIL_REENTRY', '9998-12-31 23:59:59'); }

class NoShowResetTest extends TestCase {
    public function test_count_respects_offset() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $attendees = 7;
            public $archive = 0;
            public $offset = 5;
            public function get_var($q){
                if (strpos($q,'tta_attendees_archive') !== false) return $this->archive;
                if (strpos($q,'tta_attendees') !== false && strpos($q,'no_show_offset') === false) return $this->attendees;
                if (strpos($q,'no_show_offset') !== false) return $this->offset;
                return 0;
            }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%s/',$v,$q,1); $q=preg_replace('/%d/',$v,$q,1);} return $q; }
        };
        require_once __DIR__.'/../includes/helpers.php';
        $this->assertSame(2, tta_get_no_show_event_count_by_email('user@example.com'));
    }

    public function test_member_rebanned_after_reset() {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $attendees = 10;
            public $archive = 0;
            public $offset = 5;
            public $updated = [];
            public function get_var($q){
                if (strpos($q,'SELECT status') !== false) return 'pending';
                if (strpos($q,'tta_attendees_archive') !== false) return $this->archive;
                if (strpos($q,'tta_attendees') !== false && strpos($q,'no_show_offset') === false) return $this->attendees;
                if (strpos($q,'no_show_offset') !== false) return $this->offset;
                return 0;
            }
            public function get_row($q,$o=ARRAY_A){ return ['email'=>'user@example.com']; }
            public function update($table,$data,$where,$f=null,$wf=null){ $this->updated[] = [$table,$data,$where]; }
            public function prepare($q,...$a){ foreach($a as $v){ $q=preg_replace('/%s/',$v,$q,1); $q=preg_replace('/%d/',$v,$q,1);} return $q; }
        };
        if (!function_exists('tta_get_attendee_user_id')) { function tta_get_attendee_user_id($id){ return 123; } }
        if (!function_exists('tta_clear_reinstatement_cron')) { function tta_clear_reinstatement_cron($id){} }
        if (!function_exists('tta_send_no_show_ban_email')) { function tta_send_no_show_ban_email($id){} }
        require_once __DIR__.'/../includes/helpers.php';
        tta_set_attendance_status(1,'no_show');
        $found=false;
        foreach($wpdb->updated as $u){
            if($u[0]=='wp_tta_members' && isset($u[1]['banned_until'])){ $found=true; }
        }
        $this->assertTrue($found);
    }
}
