<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class TTA_Discount_Codes_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'TTA Discount Codes',
            'TTA Discount Codes',
            'manage_options',
            'tta-discount-codes',
            [ $this, 'render_page' ],
            // Use the price tag icon so the menu item matches other dashboard entries.
            // This icon visually communicates that the screen is for managing codes
            // that apply discounts during checkout.
            'dashicons-tag',
            9.9
        );
    }

    public function render_page() {
        $codes = tta_get_global_discount_codes();
        if ( isset( $_POST['tta_discount_codes_nonce'] ) && wp_verify_nonce( $_POST['tta_discount_codes_nonce'], 'tta_discount_codes_save' ) ) {
            $new = [];
            if ( isset( $_POST['codes'] ) && is_array( $_POST['codes'] ) ) {
                foreach ( $_POST['codes'] as $row ) {
                    $code = sanitize_text_field( $row['code'] ?? '' );
                    if ( '' === $code ) {
                        continue;
                    }
                    $type   = in_array( $row['type'] ?? 'percent', [ 'flat', 'percent' ], true ) ? $row['type'] : 'percent';
                    $amount = floatval( $row['amount'] ?? 0 );
                    $new[]  = [ 'code' => $code, 'type' => $type, 'amount' => $amount ];
                }
            }
            tta_save_global_discount_codes( $new );
            $codes = $new;
            echo '<div class="updated"><p>' . esc_html__( 'Discount codes saved.', 'tta' ) . '</p></div>';
        }
        include TTA_PLUGIN_DIR . 'includes/admin/views/discount-codes.php';
    }
}

TTA_Discount_Codes_Admin::get_instance();
