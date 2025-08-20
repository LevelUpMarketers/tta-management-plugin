<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class TTA_Refund_Requests_Admin {
    public static function get_instance() {
        static $inst;
        return $inst ?: $inst = new self();
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            'TTA Refund Requests',
            'TTA Refund Requests',
            'manage_options',
            'tta-refund-requests',
            [ $this, 'render_page' ],
            'dashicons-money-alt',
            9.5
        );
    }

    public function render_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Refund Requests', 'tta' ) . '</h1>';

        if ( isset( $_POST['tta_manual_refund'] ) && check_admin_referer( 'tta_manual_refund_action', 'tta_manual_refund_nonce' ) ) {
            $tx_id    = sanitize_text_field( $_POST['tx'] ?? '' );
            $ticket_id= intval( $_POST['ticket'] ?? 0 );
            $req      = tta_get_refund_request( $tx_id, $ticket_id );
            if ( $req ) {
                TTA_Refund_Processor::process_refund_request( $req );
                echo '<div class="updated"><p>' . esc_html__( 'Refund processed.', 'tta' ) . '</p></div>';
            } else {
                echo '<div class="error"><p>' . esc_html__( 'Refund request not found.', 'tta' ) . '</p></div>';
            }
            $requests = tta_get_refund_requests();
        } else {
            $requests = tta_get_refund_requests();
        }

        if ( $requests ) {
            echo '<table class="widefat fixed striped"><thead><tr>';
            echo '<th>' . esc_html__( 'Requested', 'tta' ) . '</th>';
            echo '<th>' . esc_html__( 'Name', 'tta' ) . '</th>';
            echo '<th>' . esc_html__( 'Event', 'tta' ) . '</th>';
            echo '<th>' . esc_html__( 'Paid', 'tta' ) . '</th>';
            echo '<th>' . esc_html__( 'Details', 'tta' ) . '</th>';
            echo '<th>' . esc_html__( 'Actions', 'tta' ) . '</th>';
            echo '</tr></thead><tbody>';

            foreach ( $requests as $req ) {
                $event_name = esc_html( $req['event_name'] );
                $event_link = $req['event_url'] ? '<a href="' . esc_url( $req['event_url'] ) . '">' . $event_name . '</a>' : $event_name;
                $date  = esc_html( date_i18n( 'F j, Y g:i a', strtotime( $req['date'] ) ) );
                $name  = esc_html( trim( $req['first_name'] . ' ' . $req['last_name'] ) );
                $paid  = $req['amount_paid'] ? sprintf( esc_html__( '$%s', 'tta' ), number_format_i18n( $req['amount_paid'], 2 ) ) : '&ndash;';
                $reason= esc_html( $req['reason'] );
                echo '<tr>';
                echo '<td>' . $date . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $event_link . '</td>';
                echo '<td>' . $paid . '</td>';
                echo '<td>' . $reason . '</td>';
                echo '<td>';
                echo '<form method="post" style="display:inline;">';
                wp_nonce_field( 'tta_manual_refund_action', 'tta_manual_refund_nonce' );
                echo '<input type="hidden" name="tx" value="' . esc_attr( $req['transaction_id'] ) . '">';
                echo '<input type="hidden" name="ticket" value="' . esc_attr( $req['ticket_id'] ) . '">';
                echo '<input type="submit" name="tta_manual_refund" class="button" value="' . esc_attr__( 'Refund Now', 'tta' ) . '">';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>' . esc_html__( 'No refund requests found.', 'tta' ) . '</p>';
        }
        echo '</div>';
    }
}

TTA_Refund_Requests_Admin::get_instance();
