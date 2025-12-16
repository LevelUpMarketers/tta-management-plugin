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

        $user_id = get_current_user_id();
        $ban_info = tta_get_ban_message( $user_id );

        $payment_problem       = false;
        $payment_problem_msg   = '';
        $payment_dismiss_label = __( 'Dismiss This Message', 'tta' );

        if ( $user_id ) {
            $context = tta_get_user_context_by_id( $user_id );
            $status  = strtolower( $context['subscription_status'] ?? '' );

            if ( 'paymentproblem' === $status ) {
                $payment_problem     = true;
                $payment_problem_msg = sprintf(
                    __(
                        "Looks like there's an issue with your last Membership payment! <a href=\"%s\">Visit your Member Dashboard</a> to update your payment info, or purchase a <a href=\"%s\">new membership here!</a>",
                        'tta'
                    ),
                    esc_url( home_url( '/member-dashboard/?tab=billing' ) ),
                    esc_url( home_url( '/become-a-member/' ) )
                );
            }
        }
        $data = [
            'is_banned'                      => tta_user_is_banned( $user_id ),
            'reentry_url'                    => add_query_arg( 'auto', 'reentry', home_url( '/checkout' ) ),
            'checkout_url'                   => home_url( '/checkout' ),
            'banned_message'                 => $ban_info['message'] ?? '',
            'show_button'                    => ! empty( $ban_info['button'] ),
            'reentry_label'                  => __( 'Purchase Re-entry Ticket', 'tta' ),
            'cart_message'                   => __( 'Tickets reserved for', 'tta' ),
            'checkout_label'                 => __( 'Go to Checkout', 'tta' ),
            'cart_expires'                   => 0,
            'has_payment_problem'            => $payment_problem,
            'payment_problem_message'        => wp_kses_post( $payment_problem_msg ),
            'payment_problem_dismiss_label'  => $payment_dismiss_label,
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
