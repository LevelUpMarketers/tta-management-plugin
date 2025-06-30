<?php
class TTA_SMS_Handler {
    public static function get_instance() { static $inst; return $inst ?: $inst = new self(); }
    private function __construct() {
        // TODO: SMS notification hooks
    }
}
?>