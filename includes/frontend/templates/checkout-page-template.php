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
$cart           = new TTA_Cart();
$checkout_error = '';
if ( isset( $_GET['auto'] ) && 'reentry' === $_GET['auto'] && is_user_logged_in() ) {
    $cart->empty_cart();
    $_SESSION['tta_membership_purchase'] = 'reentry';
} elseif ( isset( $_SESSION['tta_membership_purchase'] ) && 'reentry' === $_SESSION['tta_membership_purchase'] && 'POST' !== $_SERVER['REQUEST_METHOD'] && ( ! isset( $_GET['auto'] ) || 'reentry' !== $_GET['auto'] ) ) {
    // Clear any lingering re-entry membership flag when accessing checkout normally.
    unset( $_SESSION['tta_membership_purchase'] );
}

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['tta_do_checkout'] ) ) {
    $nonce = isset( $_POST['tta_checkout_nonce'] ) ? wp_unslash( $_POST['tta_checkout_nonce'] ) : '';
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'tta_checkout_action' ) ) {
        $checkout_error = __( 'Your checkout session has expired. Please reload the page and try again.', 'tta' );
    } else {
        $checkout_error = __( "Encryption of your payment information failed! Please try again later. If you're still having trouble, please contact us using the form on our <a href=\"/contact\">Contact Page</a>.", 'tta' );
    }
}


$discount_codes   = $_SESSION['tta_discount_codes'] ?? [];
$membership_level = $_SESSION['tta_membership_purchase'] ?? '';
$membership_notice = '';
if ( is_user_logged_in() && $membership_level ) {
    $context       = tta_get_current_user_context();
    $current_level = strtolower( $context['membership_level'] ?? 'free' );
    if ( $current_level === $membership_level ) {
        unset( $_SESSION['tta_membership_purchase'] );
        $membership_notice = sprintf(
            __( 'You already have a %1$s! Please <a href="%2$s">visit your Member Dashboard</a> to manage your membership.', 'tta' ),
            tta_get_membership_label( $current_level ),
            esc_url( home_url( '/member-dashboard/?tab=billing' ) )
        );
        $membership_level = '';
    } elseif ( 'premium' === $current_level && 'basic' === $membership_level ) {
        unset( $_SESSION['tta_membership_purchase'] );
        $membership_notice = sprintf(
            __( 'You have a Premium Membership already - are you sure you want to downgrade to a Standard Membership? If so, please <a href="%s">do so on your Member Dashboard</a>.', 'tta' ),
            esc_url( home_url( '/member-dashboard/?tab=billing' ) )
        );
        $membership_level = '';
    }
}
$has_membership   = in_array( $membership_level, [ 'basic', 'premium', 'reentry' ], true );
$total_amount     = $cart->get_total( $discount_codes );
$is_free_checkout = ( 0 >= $total_amount );
get_header();

$header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">CHECKOUT</p>[/vc_column_text][/vc_column][/vc_row]';
echo do_shortcode( $header_shortcode );

 $items         = $cart->get_items();
 $checkout_done = isset( $_GET['checkout'] ) && 'done' === $_GET['checkout'];
$sub_details        = $_SESSION['tta_checkout_sub'] ?? null;
$user               = wp_get_current_user();
$is_logged_in       = is_user_logged_in();
$payment_disabled   = ! $is_logged_in || $is_free_checkout;
$has_tickets        = false;
$browse_events_url  = tta_get_last_events_url();
if ( $checkout_done ) {
    $sent_emails  = $_SESSION['tta_checkout_emails']     ?? [];
    $member_level = $_SESSION['tta_checkout_membership'] ?? '';
    $has_tickets  = ! empty( $_SESSION['tta_checkout_has_tickets'] );
    unset( $_SESSION['tta_checkout_sub'], $_SESSION['tta_checkout_emails'], $_SESSION['tta_checkout_membership'], $_SESSION['tta_checkout_has_tickets'] );
}
?>
<div class="wrap tta-checkout-page">
    <?php if ( $membership_notice ) : ?>
        <p class="tta-cart-notice"><?php echo wp_kses_post( $membership_notice ); ?></p>
    <?php endif; ?>
    <?php if ( $checkout_done ) : ?>
        <div class="tta-checkout-complete">
            <?php
            if ( $member_level ) {
                if ( 'reentry' === $member_level ) {
                    printf(
                        '<p>%s</p>',
                        wp_kses_post(
                            sprintf(
                                __( 'Thanks for purchasing your Re-Entry Ticket! You can once again register for events. An email will be sent to %s for your records. Thanks again, and welcome back!', 'tta' ),
                                esc_html( $user->user_email )
                            )
                        )
                    );
                } else {
                    $amount          = 'premium' === $member_level ? TTA_PREMIUM_MEMBERSHIP_PRICE : TTA_BASIC_MEMBERSHIP_PRICE;
                    $membership_name = 'premium' === $member_level ? __( 'Premium', 'tta' ) : __( 'Standard', 'tta' );

                    printf(
                        '<p>%s</p>',
                        wp_kses_post(
                            sprintf(
                                __( "Thanks for becoming a %s Member! There's nothing else for you to do - you'll be automatically billed $%s once monthly, and can cancel anytime on your %s. An email will be sent to %s with your Membership Details. Thanks again, and enjoy your Membership perks!", 'tta' ),
                                $membership_name,
                                number_format_i18n( $amount, 0 ),
                                '<a href="' . esc_url( home_url( '/member-dashboard/?tab=billing' ) ) . '">' . esc_html__( 'Member Dashboard', 'tta' ) . '</a>',
                                esc_html( $user->user_email )
                            )
                        )
                    );

                    if ( 'premium' === $member_level ) {
                        printf(
                            '<p>%s <a href="mailto:sam@tryingtoadultrva.com">sam@tryingtoadultrva.com</a> %s</p>',
                            esc_html__( 'Did you know? You can earn a free event and other perks by referring friends and family! Let us know who you\'ve referred at', 'tta' ),
                            esc_html__( "and we'll reach out.", 'tta' )
                        );
                    }
                }
            }

            if ( $has_tickets && ! empty( $sent_emails ) ) {
                $intro = $member_level ? __( 'Also, thanks for signing up for our upcoming event!', 'tta' ) : __( 'Thanks for signing up!', 'tta' );
                echo '<p>' . esc_html( $intro ) . ' ' . esc_html__( 'A receipt has been emailed to each of the email addresses below. Please keep these emails to present to the Event Host or Volunteer upon arrival.', 'tta' ) . '</p>';
                echo '<ul>';
                foreach ( $sent_emails as $e ) {
                    echo '<li>' . esc_html( $e ) . '</li>';
                }
                echo '</ul>';
            }

            if ( $sub_details && ! empty( $sub_details['subscription_id'] ) ) {
                echo '<p>' . esc_html( sprintf( 'Subscription ID: %s', $sub_details['subscription_id'] ) ) . '</p>';
                if ( ! empty( $sub_details['result_code'] ) ) {
                    echo '<p>' . esc_html( sprintf( 'Result Code: %s', $sub_details['result_code'] ) ) . '</p>';
                }
                if ( ! empty( $sub_details['message_code'] ) || ! empty( $sub_details['message_text'] ) ) {
                    $code = $sub_details['message_code'] ? $sub_details['message_code'] . ': ' : '';
                    echo '<p>' . esc_html( $code . $sub_details['message_text'] ) . '</p>';
                }
            }
            ?>
        </div>
    <?php elseif ( $checkout_error ) : ?>
        <p class="tta-checkout-error">
            <?php echo wp_kses_post( $checkout_error ); ?>
        </p>
    <?php elseif ( ! $items && ! $has_membership ) : ?>
        <p><?php esc_html_e( 'Your cart is empty.', 'tta' ); ?></p>
<?php else : ?>
        <?php if ( ! $is_logged_in ) : ?>
            <?php
            if ( $has_membership ) {
                echo tta_render_membership_checkout_section( home_url( '/checkout' ) );
            } else {
                echo tta_render_login_register_section( home_url( '/checkout' ) );
            }
            ?>
        <?php endif; ?>
        <form id="tta-checkout-form" method="post">
            <?php wp_nonce_field( 'tta_checkout_action', 'tta_checkout_nonce' ); ?>
            <?php echo tta_render_checkout_summary( $cart, $discount_codes ); ?>
            <?php if ( $browse_events_url ) : ?>
                <div class="tta-checkout-browse">
                    <a class="tta-cart-browse-button tta-checkout-browse-button" href="<?php echo esc_url( $browse_events_url ); ?>">
                        <?php esc_html_e( 'Browse More Events', 'tta' ); ?>
                    </a>
                </div>
            <?php endif; ?>
            <div class="tta-checkout-grid">
                <?php if ( $items ) : ?>
                <div class="tta-checkout-left<?php echo $is_logged_in ? '' : ' tta-disabled'; ?>">
                    <h3><?php esc_html_e( 'Ticket Details', 'tta' ); ?></h3>
                    <?php echo tta_render_attendee_fields( $cart, ! $is_logged_in ); ?>
                </div>
                <?php endif; ?>
                <div class="tta-checkout-right">
                    <h3><?php esc_html_e( 'Billing Details', 'tta' ); ?></h3>
                    <?php
                    // Variables defined above
                    ?>
                    <div class="tta-billing-details-div-container<?php echo $is_logged_in ? '' : ' tta-disabled'; ?>">
                        <h4>Billing Address</h4>
                    <p style="display:block;" class="tta-attendee-note">Please enter the Billing address of the payment method you'll be using.</p>
                        <p>
                            <label>
                                <?php esc_html_e( 'First Name', 'tta' ); ?><br />
                                <input type="text" name="billing_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" required <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Last Name', 'tta' ); ?><br />
                                <input type="text" name="billing_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" required <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Email', 'tta' ); ?><br />
                                <input type="email" name="billing_email" value="<?php echo esc_attr( $user->user_email ); ?>" required <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Street Address', 'tta' ); ?><br />
                                <input type="text" name="billing_street" required <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Address Line 2', 'tta' ); ?><br />
                                <input type="text" name="billing_street_2" <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'City', 'tta' ); ?><br />
                                <input type="text" name="billing_city" required <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'State', 'tta' ); ?><br />
                                <select name="billing_state" required <?php disabled( ! $is_logged_in ); ?> >
                                    <?php foreach ( tta_get_us_states() as $abbr => $name ) : ?>
                                        <option value="<?php echo esc_attr( $abbr ); ?>"><?php echo esc_html( $name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'ZIP', 'tta' ); ?><br />
                                <input type="text" name="billing_zip" required <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                    </div>
                    <div class="tta-billing-details-div-container<?php echo $is_logged_in ? '' : ' tta-disabled'; ?>">
                        <h4><?php esc_html_e( 'Payment Info', 'tta' ); ?></h4>
                        <p style="display:block;" class="tta-attendee-note">
                            <?php
                            echo $is_free_checkout ? esc_html__( 'No payment is required for this order - simply click the button below to secure your spot!', 'tta' ) : esc_html__( 'Please enter your credit or debit card details below.', 'tta' );
                            ?>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Card Number', 'tta' ); ?><br />
                                <input type="text" name="card_number" placeholder="&#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226;" <?php echo $payment_disabled ? '' : 'required'; ?> <?php disabled( $payment_disabled ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Expiration', 'tta' ); ?><br />
                                <input type="text" id="tta-card-exp" class="tta-card-exp" name="card_exp" placeholder="MM/YY" maxlength="5" pattern="\d{2}/\d{2}" inputmode="numeric" <?php echo $payment_disabled ? '' : 'required'; ?> <?php disabled( $payment_disabled ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'CVC', 'tta' ); ?><br />
                                <input type="text" name="card_cvc" placeholder="123" <?php echo $payment_disabled ? '' : 'required'; ?> <?php disabled( $payment_disabled ); ?> />
                            </label>
                        </p>
                        <p class="tta-place-order-button-p">
                            <button class="tta-button tta-button-primary" type="submit" name="tta_do_checkout" <?php disabled( ! $is_logged_in ); ?> >
                                <?php esc_html_e( 'Place Order', 'tta' ); ?>
                            </button>
                            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                            <span id="tta-checkout-response" class="tta-admin-progress-response-p"></span>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php
get_footer();
