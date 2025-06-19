<?php
/**
 * Template Name: Checkout Page
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Initialize cart early for sessions
$cart          = new TTA_Cart();
$checkout_error = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['tta_do_checkout'] ) ) {
    check_admin_referer( 'tta_checkout_action', 'tta_checkout_nonce' );

    $discount_codes = $_SESSION['tta_discount_codes'] ?? [];
    $cart_changed   = $cart->sync_with_inventory();
    $amount         = $cart->get_total( $discount_codes );
    if ( $cart_changed ) {
        tta_set_cart_notice( __( 'Some tickets in your cart were no longer available and have been removed. Please review the updated cart and try again.', 'tta' ) );
        wp_safe_redirect( home_url( '/cart' ) );
        exit;
    }

    $exp_input  = tta_sanitize_text_field( $_POST['card_exp'] );
    $exp_digits = preg_replace( '/\D/', '', $exp_input );
    $exp_date   = '';
    if ( strlen( $exp_digits ) === 4 ) {
        $month = substr( $exp_digits, 0, 2 );
        $year  = substr( $exp_digits, 2, 2 );
        if ( (int) $month >= 1 && (int) $month <= 12 ) {
            $exp_date = '20' . $year . '-' . $month;
        } else {
            $checkout_error = __( 'Invalid expiration month.', 'tta' );
        }
    } else {
        $checkout_error = __( 'Invalid expiration date format.', 'tta' );
    }

    $billing = [
        'first_name' => tta_sanitize_text_field( $_POST['billing_first_name'] ),
        'last_name'  => tta_sanitize_text_field( $_POST['billing_last_name'] ),
        'address'    => tta_sanitize_text_field( $_POST['billing_street'] ),
        'city'       => tta_sanitize_text_field( $_POST['billing_city'] ),
        'state'      => tta_sanitize_text_field( $_POST['billing_state'] ),
        'zip'        => tta_sanitize_text_field( $_POST['billing_zip'] ),
    ];

    if ( empty( $checkout_error ) ) {
        $api    = new TTA_AuthorizeNet_API();
        $result = $api->charge(
            $amount,
            preg_replace( '/\D/', '', $_POST['card_number'] ),
            $exp_date,
            tta_sanitize_text_field( $_POST['card_cvc'] ),
            $billing
        );

        $attendees = $_POST['attendees'] ?? [];

        if ( $result['success'] ) {
            $res = $cart->finalize_purchase( $result['transaction_id'], $amount, $attendees );
            if ( is_wp_error( $res ) ) {
                $checkout_error = $res->get_error_message();
            } else {
                wp_safe_redirect( add_query_arg( 'checkout', 'done', get_permalink() ) );
                exit;
            }
        } else {
            $checkout_error = $result['error'];
        }
    }
    // Display any payment error below
    if ( $checkout_error ) {
        // countdowns continue normally; no special handling needed
    }
}

$discount_codes = $_SESSION['tta_discount_codes'] ?? [];
get_header();

$items         = $cart->get_items();
$checkout_done = isset( $_GET['checkout'] ) && 'done' === $_GET['checkout'];
?>
<div class="wrap tta-checkout-page">
    <?php if ( $checkout_done ) : ?>
        <p class="tta-checkout-complete">
            <?php esc_html_e( 'Thank you for your purchase!', 'tta' ); ?>
        </p>
    <?php elseif ( $checkout_error ) : ?>
        <p class="tta-checkout-error">
            <?php echo esc_html( $checkout_error ); ?>
        </p>
    <?php elseif ( ! $items ) : ?>
        <p><?php esc_html_e( 'Your cart is empty.', 'tta' ); ?></p>
    <?php else : ?>
        <form id="tta-checkout-form" method="post">
            <?php wp_nonce_field( 'tta_checkout_action', 'tta_checkout_nonce' ); ?>
            <?php echo tta_render_checkout_summary( $cart, $discount_codes ); ?>
            <div class="tta-checkout-grid">
                <div class="tta-checkout-left">
                    <h3><?php esc_html_e( 'Ticket Details', 'tta' ); ?></h3>
                    <?php echo tta_render_attendee_fields( $cart ); ?>
                </div>
                <div class="tta-checkout-right">
                    <h3><?php esc_html_e( 'Billing Details', 'tta' ); ?></h3>
                    <?php $user = wp_get_current_user(); ?>
                    <p>
                        <label>
                            <?php esc_html_e( 'First Name', 'tta' ); ?><br />
                            <input type="text" name="billing_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" required />
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'Last Name', 'tta' ); ?><br />
                            <input type="text" name="billing_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" required />
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'Email', 'tta' ); ?><br />
                            <input type="email" name="billing_email" value="<?php echo esc_attr( $user->user_email ); ?>" required />
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'Street Address', 'tta' ); ?><br />
                            <input type="text" name="billing_street" />
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'City', 'tta' ); ?><br />
                            <input type="text" name="billing_city" />
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'State', 'tta' ); ?><br />
                            <select name="billing_state">
                                <?php foreach ( tta_get_us_states() as $abbr => $name ) : ?>
                                    <option value="<?php echo esc_attr( $abbr ); ?>"><?php echo esc_html( $name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'ZIP', 'tta' ); ?><br />
                            <input type="text" name="billing_zip" />
                        </label>
                    </p>
                    <h3><?php esc_html_e( 'Payment Info', 'tta' ); ?></h3>
                    <p>
                        <label>
                            <?php esc_html_e( 'Card Number', 'tta' ); ?><br />
                            <input type="text" name="card_number" placeholder="&#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226;" required />
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'Expiration', 'tta' ); ?><br />
                            <input type="text" id="tta-card-exp" name="card_exp" placeholder="MM/YY" required maxlength="5" pattern="\d{2}/\d{2}" inputmode="numeric" />
                        </label>
                    </p>
                    <p>
                        <label>
                            <?php esc_html_e( 'CVC', 'tta' ); ?><br />
                            <input type="text" name="card_cvc" placeholder="123" required />
                        </label>
                    </p>
                    <p>
                        <button class="tta-button tta-button-primary" type="submit" name="tta_do_checkout">
                            <?php esc_html_e( 'Place Order', 'tta' ); ?>
                        </button>
                    </p>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php
get_footer();
