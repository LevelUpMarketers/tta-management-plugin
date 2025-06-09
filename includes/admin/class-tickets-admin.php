<?php
class TTA_Tickets_Admin {
    public static function get_instance() { static $inst; return $inst ?: $inst = new self(); }
    private function __construct() {
        add_action('admin_menu', array($this, 'register_menu'));
    }
    public function register_menu() {
        add_menu_page('Tickets', 'Tickets', 'manage_options', 'tta-tickets', array($this, 'render_list'), 'dashicons-tickets', 8);
    }
    public function render_list() {
        // TODO: Implement WP_List_Table for Tickets
    }
}
?>