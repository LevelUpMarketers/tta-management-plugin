<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class TTA_Ajax_Venues {
    public static function init(){
        add_action('wp_ajax_tta_get_venue',[__CLASS__,'get_venue']);
    }
    public static function get_venue(){
        global $wpdb; $table = $wpdb->prefix.'tta_venues';
        $name = sanitize_text_field($_POST['name'] ?? '');
        $v = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE name=%s", $name), ARRAY_A);
        if($v){ wp_send_json_success($v); } else { wp_send_json_error(['message'=>'Venue not found']); }
    }
}
