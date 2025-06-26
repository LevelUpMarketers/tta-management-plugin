<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class TTA_Ajax_Venues {
    public static function init(){
        add_action('wp_ajax_tta_get_venue',[__CLASS__,'get_venue']);
        add_action('wp_ajax_tta_get_venue_form',[__CLASS__,'get_venue_form']);
        add_action('wp_ajax_tta_update_venue',[__CLASS__,'update_venue']);
    }
    public static function get_venue(){
        global $wpdb; $table = $wpdb->prefix.'tta_venues';
        $name = sanitize_text_field($_POST['name'] ?? '');
        $v = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE name=%s", $name), ARRAY_A);
        if($v){ wp_send_json_success($v); } else { wp_send_json_error(['message'=>'Venue not found']); }
    }

    public static function get_venue_form(){
        check_ajax_referer('tta_venue_get_action','get_venue_nonce');
        if(empty($_POST['venue_id'])) wp_send_json_error(['message'=>'Missing ID']);
        $_GET['venue_id']=intval($_POST['venue_id']);
        ob_start();
        include TTA_PLUGIN_DIR.'includes/admin/views/venues-edit.php';
        $html=ob_get_clean();
        wp_send_json_success(['html'=>$html]);
    }

    public static function update_venue(){
        check_ajax_referer('tta_venue_save_action','tta_venue_save_nonce');
        if(empty($_POST['venue_id'])) wp_send_json_error(['message'=>'Missing ID']);
        global $wpdb; $table=$wpdb->prefix.'tta_venues';
        $address=implode(' - ', array_filter([
            tta_sanitize_text_field($_POST['street_address']??''),
            tta_sanitize_text_field($_POST['address_2']??''),
            tta_sanitize_text_field($_POST['city']??''),
            tta_sanitize_text_field($_POST['state']??''),
            tta_sanitize_text_field($_POST['zip']??'')
        ]));
        $data=[
            'name'=>tta_sanitize_text_field($_POST['name']),
            'venueurl'=>tta_esc_url_raw($_POST['venueurl']),
            'url2'=>tta_esc_url_raw($_POST['url2']),
            'url3'=>tta_esc_url_raw($_POST['url3']),
            'url4'=>tta_esc_url_raw($_POST['url4']),
            'address'=>$address
        ];
        $wpdb->update($table,$data,['id'=>intval($_POST['venue_id'])]);
        wp_send_json_success(['message'=>'Venue updated!']);
    }
}
