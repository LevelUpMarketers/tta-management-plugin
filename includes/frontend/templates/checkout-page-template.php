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

    $discount_codes   = $_SESSION['tta_discount_codes'] ?? [];
    $cart_changed     = $cart->sync_with_inventory();
    $ticket_total     = $cart->get_total( $discount_codes, false );
    $membership_level = $_SESSION['tta_membership_purchase'] ?? '';
    $membership_total = $membership_level ? tta_get_membership_price( $membership_level ) : 0;
    $context          = tta_get_current_user_context();
    if ( 'premium' === strtolower( $context['membership_level'] ) && 'basic' === $membership_level ) {
        unset( $_SESSION['tta_membership_purchase'] );
        tta_set_cart_notice( __( 'Premium members cannot purchase a Basic Membership.', 'tta' ) );
        wp_safe_redirect( home_url( '/cart' ) );
        exit;
    }
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
        $api = new TTA_AuthorizeNet_API();
        $attendees = $_POST['attendees'] ?? [];
        $transaction_id = '';

        if ( $membership_total > 0 ) {
            $sub = $api->create_subscription(
                $membership_total,
                preg_replace( '/\D/', '', $_POST['card_number'] ),
                $exp_date,
                tta_sanitize_text_field( $_POST['card_cvc'] ),
                $billing
            );
            if ( $sub['success'] ) {
                tta_update_user_membership_level( get_current_user_id(), $membership_level );
            } else {
                $checkout_error = $sub['error'];
            }
        }

        if ( empty( $checkout_error ) && $ticket_total > 0 ) {
            $result = $api->charge(
                $ticket_total,
                preg_replace( '/\D/', '', $_POST['card_number'] ),
                $exp_date,
                tta_sanitize_text_field( $_POST['card_cvc'] ),
                $billing
            );
            if ( $result['success'] ) {
                $transaction_id = $result['transaction_id'];
            } else {
                $checkout_error = $result['error'];
            }
        }

        if ( empty( $checkout_error ) ) {
            $cart->finalize_purchase( $transaction_id, $ticket_total, $attendees );
            unset( $_SESSION['tta_membership_purchase'] );
            wp_safe_redirect( add_query_arg( 'checkout', 'done', get_permalink() ) );
            exit;
        }
    }
    // Display any payment error below
    if ( $checkout_error ) {
        // countdowns continue normally; no special handling needed
    }
}


$discount_codes   = $_SESSION['tta_discount_codes'] ?? [];
$membership_level = $_SESSION['tta_membership_purchase'] ?? '';
$has_membership   = in_array( $membership_level, [ 'basic', 'premium' ], true );
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
    <?php elseif ( ! $items && ! $has_membership ) : ?>
        <p><?php esc_html_e( 'Your cart is empty.', 'tta' ); ?></p>
    <?php else : ?>
        <form id="tta-checkout-form" method="post">
            <?php wp_nonce_field( 'tta_checkout_action', 'tta_checkout_nonce' ); ?>
            <?php echo tta_render_checkout_summary( $cart, $discount_codes ); ?>
            <div class="tta-checkout-grid">
                <?php if ( $items ) : ?>
                <div class="tta-checkout-left">
                    <h3><?php esc_html_e( 'Ticket Details', 'tta' ); ?></h3>
                    <?php echo tta_render_attendee_fields( $cart ); ?>
                </div>
                <?php endif; ?>
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
                        <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                        <span id="tta-checkout-response" class="tta-admin-progress-response-p"></span>
                    </p>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php
get_footer();
