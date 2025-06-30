<?php
class TTA_Members_Shortcode {
    public static function get_instance() { static $inst; return $inst ?: $inst = new self(); }
    private function __construct() {
        add_shortcode('tta_members', array($this, 'render'));
    }
    public function render($atts) {
        // TODO: Query & display member info
    }
}
?>