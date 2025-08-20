<?php
use PHPUnit\Framework\TestCase;
if (!defined('ARRAY_A')) { define('ARRAY_A', 'ARRAY_A'); }

class DummyWpdbMembers {
    public $prefix = 'wp_';
    public $options = 'wp_options';
    public $insert_id = 0;
    public $data = [];
    public $queries = [];

    public function esc_like($str) {
        return $str;
    }

    public function query($sql) {
        $this->queries[] = $sql;
    }

    public function insert($table, $data) {
        $this->insert_id++;
        $this->data[$table][$this->insert_id] = $data;
        return true;
    }

    public function update($table, $data, $where, $formats = null, $where_formats = null) {
        $id = is_array($where) ? array_values($where)[0] : $where;
        if (isset($this->data[$table][$id])) {
            $this->data[$table][$id] = array_merge($this->data[$table][$id], $data);
            return 1;
        }
        return 0;
    }

    public function get_row($query, $output = ARRAY_A) {
        if (preg_match('/FROM (\S+) WHERE id = (\d+)/', $query, $m)) {
            $table = $m[1];
            $id    = intval($m[2]);
            return $this->data[$table][$id] ?? null;
        }
        if (preg_match('/FROM (\S+) WHERE wpuserid = (\d+)/', $query, $m)) {
            $table = $m[1];
            $uid   = intval($m[2]);
            if (isset($this->data[$table])) {
                foreach ($this->data[$table] as $row) {
                    if (($row['wpuserid'] ?? 0) == $uid) {
                        return $row;
                    }
                }
            }
        }
        return null;
    }

    public function prepare($query, ...$args) {
        foreach ($args as $a) {
            $query = preg_replace('/%d/', intval($a), $query, 1);
            $query = preg_replace('/%s/', $a, $query, 1);
        }
        return $query;
    }

    public function get_col($query) {
        return [];
    }
}

class MemberTest extends TestCase {
    private $wpdb;

    protected function setUp(): void {
        if (!defined('ABSPATH')) {
            define('ABSPATH', sys_get_temp_dir() . '/wp/');
        }
        @mkdir(ABSPATH . 'wp-admin/includes', 0777, true);
        if (!file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            file_put_contents(ABSPATH . 'wp-admin/includes/upgrade.php', "<?php\n");
        }
        $GLOBALS['wp_users'] = [];
        $GLOBALS['user_meta'] = [];
        $GLOBALS['usernames'] = [];
        $GLOBALS['deleted_users'] = [];

        if (!function_exists('check_ajax_referer')) { function check_ajax_referer($a,$b){} }
        if (!function_exists('add_action')) { function add_action($t,$c){} }
        if (!function_exists('wp_send_json_success')) { function wp_send_json_success($d){ $GLOBALS['_last_json']=['success'=>true,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if (!function_exists('wp_send_json_error')) { function wp_send_json_error($d){ $GLOBALS['_last_json']=['success'=>false,'data'=>$d]; return $GLOBALS['_last_json']; } }
        if (!function_exists('sanitize_text_field')) { function sanitize_text_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('tta_sanitize_text_field')) { function tta_sanitize_text_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_textarea_field')) { function sanitize_textarea_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('tta_sanitize_textarea_field')) { function tta_sanitize_textarea_field($v){ return is_string($v)?trim($v):$v; } }
        if (!function_exists('sanitize_email')) { function sanitize_email($v){ return trim($v); } }
        if (!function_exists('tta_sanitize_email')) { function tta_sanitize_email($v){ return trim($v); } }
        if (!function_exists('esc_url_raw')) { function esc_url_raw($v){ return $v; } }
        if (!function_exists('tta_esc_url_raw')) { function tta_esc_url_raw($v){ return $v; } }
        if (!function_exists('sanitize_user')) { function sanitize_user($v,$s=true){ return preg_replace('/[^A-Za-z0-9]/','',$v); } }
        if (!function_exists('wp_unslash')) { function wp_unslash($v){ return is_array($v)?array_map('wp_unslash',$v):str_replace('\\','',$v); } }
        if (!function_exists('email_exists')) { function email_exists($e){ return isset($GLOBALS['wp_users'][$e]); } }
        if (!function_exists('get_user_by')) { function get_user_by($f,$v){ return isset($GLOBALS['wp_users'][$v]) ? (object)$GLOBALS['wp_users'][$v] : null; } }
        if (!function_exists('admin_url')) { function admin_url($p=''){ return 'admin/'.$p; } }
        if (!function_exists('username_exists')) { function username_exists($u){ return isset($GLOBALS['usernames'][$u]); } }
        if (!function_exists('wp_generate_password')) { function wp_generate_password($l=12,$s=false,$e=false){ return 'pass'; } }
        if (!function_exists('wp_insert_user')) { function wp_insert_user($d){ $id=count($GLOBALS['wp_users'])+1; $GLOBALS['wp_users'][$d['user_email']] = ['ID'=>$id,'user_login'=>$d['user_login'],'user_email'=>$d['user_email']]; $GLOBALS['usernames'][$d['user_login']]=$id; return $id; } }
        if (!function_exists('is_wp_error')) { function is_wp_error($v){ return false; } }
        if (!function_exists('wp_delete_user')) { function wp_delete_user($id){ $GLOBALS['deleted_users'][]=$id; } }
        if (!function_exists('current_time')) { function current_time($t='mysql',$gmt=false){ return $t==='timestamp'?0:'now'; } }
        if (!function_exists('update_user_meta')) { function update_user_meta($u,$k,$v){ $GLOBALS['user_meta'][$u][$k]=$v; } }
        if (!function_exists('get_userdata')) { function get_userdata($uid){ foreach($GLOBALS['wp_users'] as $u){ if($u['ID']==$uid) return (object)$u; } return false; } }
        if (!function_exists('wp_update_user')) { function wp_update_user($d){
            foreach ($GLOBALS['wp_users'] as $email => $u) {
                if ($u['ID'] == $d['ID']) {
                    $new_email = $d['user_email'] ?? $email;
                    if ($new_email !== $email) {
                        unset($GLOBALS['wp_users'][$email]);
                        $u['user_email'] = $new_email;
                    } else {
                        $u = array_merge($u, $d);
                    }
                    $GLOBALS['wp_users'][$new_email] = $u;
                    break;
                }
            }
        } }
        if (!function_exists('media_handle_upload')) { function media_handle_upload($f,$p){ return 1; } }

        require_once __DIR__ . '/../includes/ajax/handlers/class-ajax-members.php';
        require_once __DIR__ . '/../includes/classes/class-tta-cache.php';

        global $wpdb;
        $this->wpdb = $wpdb = new DummyWpdbMembers();
    }

    protected function tearDown(): void {
        $_POST = [];
    }

    private function basePost() {
        return [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email' => 'john@example.com',
            'email_verify' => 'john@example.com',
            'tta_member_save_nonce' => 'yes',
            'membership_level' => 'free',
            'street_address' => '123 St',
            'address_2' => '',
            'city' => 'Town',
            'state' => 'VA',
            'zip' => '12345',
        ];
    }

    public function test_save_member_creates_record() {
        $_POST = $this->basePost();
        TTA_Ajax_Members::save_member();
        $res = $GLOBALS['_last_json'];
        $this->assertTrue($res['success']);
        $table = $this->wpdb->prefix.'tta_members';
        $id = $res['data']['member_id'];
        $member = $this->wpdb->data[$table][$id];
        $this->assertSame('John', $member['first_name']);
        $this->assertSame('Doe', $member['last_name']);
        $this->assertSame('john@example.com', $member['email']);
    }

    public function test_update_member_changes_data() {
        $_POST = $this->basePost();
        TTA_Ajax_Members::save_member();
        $res = $GLOBALS['_last_json'];
        $id = $res['data']['member_id'];

        $_POST = $this->basePost();
        unset($_POST['tta_member_save_nonce']);
        $_POST['member_id'] = $id;
        $_POST['tta_member_update_nonce'] = 'yes';
        $_POST['first_name'] = 'Jane';
        TTA_Ajax_Members::update_member();
        $res = $GLOBALS['_last_json'];
        $this->assertTrue($res['success']);
        $table = $this->wpdb->prefix.'tta_members';
        $member = $this->wpdb->data[$table][$id];
        $this->assertSame('Jane', $member['first_name']);
    }
}
