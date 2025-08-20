<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class TTA_Alert_Bar {
    public static function init() {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function enqueue_assets() {
        wp_enqueue_style(
            'tta-alert-bar-css',
            TTA_PLUGIN_URL . 'assets/css/frontend/alert-bar.css',
            [ 'tta-frontend-css' ],
            TTA_PLUGIN_VERSION
        );
        wp_enqueue_script(
            'tta-alert-bar-js',
            TTA_PLUGIN_URL . 'assets/js/frontend/alert-bar.js',
            [ 'jquery' ],
            TTA_PLUGIN_VERSION,
            true
        );

        $ban_info = tta_get_ban_message( get_current_user_id() );
        $data = [
            'is_banned'     => tta_user_is_banned( get_current_user_id() ),
            'reentry_url'   => add_query_arg( 'auto', 'reentry', home_url( '/checkout' ) ),
            'checkout_url'  => home_url( '/checkout' ),
            'banned_message'=> $ban_info['message'] ?? '',
            'show_button'   => ! empty( $ban_info['button'] ),
            'reentry_label' => __( 'Purchase Re-entry Ticket', 'tta' ),
            'cart_message'  => __( 'Tickets reserved for', 'tta' ),
            'checkout_label'=> __( 'Go to Checkout', 'tta' ),
            'cart_expires'  => 0,
        ];

        if ( empty( $data['is_banned'] ) ) {
            $cart   = new TTA_Cart();
            $items  = $cart->get_items();
            $expire = 0;
            foreach ( $items as $it ) {
                $ts = strtotime( $it['expires_at'] );
                if ( ! $expire || $ts < $expire ) {
                    $expire = $ts;
                }
            }
            if ( $expire ) {
                $data['cart_expires'] = $expire;
            }
        }

        wp_localize_script( 'tta-alert-bar-js', 'ttaAlertBarData', $data );
    }
}
