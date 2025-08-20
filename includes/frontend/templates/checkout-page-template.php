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
if ( isset( $_GET['auto'] ) && 'reentry' === $_GET['auto'] && is_user_logged_in() ) {
    $cart->empty_cart();
    $_SESSION['tta_membership_purchase'] = 'reentry';
} elseif ( isset( $_SESSION['tta_membership_purchase'] ) && 'reentry' === $_SESSION['tta_membership_purchase'] && 'POST' !== $_SERVER['REQUEST_METHOD'] && ( ! isset( $_GET['auto'] ) || 'reentry' !== $_GET['auto'] ) ) {
    // Clear any lingering re-entry membership flag when accessing checkout normally.
    unset( $_SESSION['tta_membership_purchase'] );
}

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['tta_do_checkout'] ) ) {
    check_admin_referer( 'tta_checkout_action', 'tta_checkout_nonce' );

    $discount_codes   = $_SESSION['tta_discount_codes'] ?? [];
    $ticket_total     = $cart->get_total( $discount_codes, false );
    $membership_level = $_SESSION['tta_membership_purchase'] ?? '';
    $membership_total = $membership_level ? tta_get_membership_price( $membership_level ) : 0;
    $context          = tta_get_current_user_context();
    if ( 'premium' === strtolower( $context['membership_level'] ) ) {
        if ( in_array( $membership_level, [ 'basic', 'premium' ], true ) ) {
            unset( $_SESSION['tta_membership_purchase'] );
            tta_set_cart_notice( __( 'Premium members cannot purchase another membership.', 'tta' ) );
            wp_safe_redirect( home_url( '/cart' ) );
            exit;
        }
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
        'address2'   => tta_sanitize_text_field( $_POST['billing_street_2'] ?? '' ),
        'city'       => tta_sanitize_text_field( $_POST['billing_city'] ),
        'state'      => tta_sanitize_text_field( $_POST['billing_state'] ),
        'zip'        => tta_sanitize_text_field( $_POST['billing_zip'] ),
    ];

    if ( empty( $billing['address'] ) || empty( $billing['city'] ) || empty( $billing['state'] ) || empty( $billing['zip'] ) ) {
        $checkout_error = __( 'Please complete all required billing address fields.', 'tta' );
    }

    if ( empty( $checkout_error ) ) {
        $api = new TTA_AuthorizeNet_API();
        $attendees = $_POST['attendees'] ?? [];
        $transaction_id = '';

        if ( $membership_total > 0 ) {
            $charge = $api->charge(
                $membership_total,
                preg_replace( '/\D/', '', $_POST['card_number'] ),
                $exp_date,
                tta_sanitize_text_field( $_POST['card_cvc'] ),
                $billing
            );
            if ( ! $charge['success'] ) {
                $checkout_error = $charge['error'];
            } else {
                TTA_Transaction_Logger::log(
                    $charge['transaction_id'],
                    $membership_total,
                    [
                        [
                            'membership'  => tta_get_membership_label( $membership_level ),
                            'quantity'    => 1,
                            'price'       => $membership_total,
                            'final_price' => $membership_total,
                        ],
                    ],
                    '',
                    0,
                    get_current_user_id(),
                    substr( preg_replace( '/\D/', '', $_POST['card_number'] ), -4 )
                );

                if ( 'reentry' === $membership_level ) {
                    tta_unban_user( get_current_user_id() );
                } else {
                    $existing_sub = tta_get_user_subscription_id( get_current_user_id() );
                    if ( $existing_sub ) {
                        $api->cancel_subscription( $existing_sub );
                    }
                    $sub_name = ( 'premium' === $membership_level ) ? TTA_PREMIUM_SUBSCRIPTION_NAME : TTA_BASIC_SUBSCRIPTION_NAME;
                    $sub_desc = ( 'premium' === $membership_level ) ? TTA_PREMIUM_SUBSCRIPTION_DESCRIPTION : TTA_BASIC_SUBSCRIPTION_DESCRIPTION;
                    $sub = $api->create_subscription(
                        $membership_total,
                        preg_replace( '/\D/', '', $_POST['card_number'] ),
                        $exp_date,
                        tta_sanitize_text_field( $_POST['card_cvc'] ),
                        $billing,
                        $sub_name,
                        $sub_desc,
                        date( 'Y-m-d', strtotime( '+1 month' ) )
                    );
                    if ( $sub['success'] ) {
                        tta_update_user_membership_level( get_current_user_id(), $membership_level, $sub['subscription_id'], 'active' );
                        $_SESSION['tta_checkout_sub'] = [
                            'subscription_id' => $sub['subscription_id'],
                            'result_code'     => $sub['result_code'] ?? '',
                            'message_code'    => $sub['message_code'] ?? '',
                            'message_text'    => $sub['message_text'] ?? '',
                        ];
                    } else {
                        $checkout_error = $sub['error'];
                    }
                }
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
            $last4  = substr( preg_replace( '/\D/', '', $_POST['card_number'] ), -4 );
            $cart->finalize_purchase( $transaction_id, $ticket_total, $attendees, $last4 );

            $user   = wp_get_current_user();
            $emails = array_merge( [ $user->user_email ], tta_collect_attendee_emails( $attendees ) );
            $emails = array_filter( array_map( 'sanitize_email', $emails ) );
            $unique = [];
            foreach ( $emails as $email ) {
                $key = strtolower( $email );
                if ( ! isset( $unique[ $key ] ) ) {
                    $unique[ $key ] = $email;
                }
            }
            $emails = array_values( $unique );
            $_SESSION['tta_checkout_emails']      = $emails;
            $_SESSION['tta_checkout_membership']  = $membership_total > 0 ? $membership_level : '';
            $_SESSION['tta_checkout_has_tickets'] = ! empty( $attendees );

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
$has_membership   = in_array( $membership_level, [ 'basic', 'premium', 'reentry' ], true );
get_header();

$header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">CHECKOUT</p>[/vc_column_text][/vc_column][/vc_row]';
echo do_shortcode( $header_shortcode );

 $items         = $cart->get_items();
 $checkout_done = isset( $_GET['checkout'] ) && 'done' === $_GET['checkout'];
$sub_details   = $_SESSION['tta_checkout_sub'] ?? null;
$user          = wp_get_current_user();
$is_logged_in  = is_user_logged_in();
$has_tickets   = false;
if ( $checkout_done ) {
    $sent_emails  = $_SESSION['tta_checkout_emails']     ?? [];
    $member_level = $_SESSION['tta_checkout_membership'] ?? '';
    $has_tickets  = ! empty( $_SESSION['tta_checkout_has_tickets'] );
    unset( $_SESSION['tta_checkout_sub'], $_SESSION['tta_checkout_emails'], $_SESSION['tta_checkout_membership'], $_SESSION['tta_checkout_has_tickets'] );
}
?>
<div class="wrap tta-checkout-page">
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
                    $amount = 'premium' === $member_level ? TTA_PREMIUM_MEMBERSHIP_PRICE : TTA_BASIC_MEMBERSHIP_PRICE;
                    printf(
                        '<p>%s</p>',
                        wp_kses_post(
                            sprintf(
                                __( "Thanks for becoming a %s Member! There's nothing else for you to do - you'll be automatically billed $%s once monthly, and can cancel anytime on your %s. An email will be sent to %s with your Membership Details. Thanks again, and enjoy your Membership perks!", 'tta' ),
                                ucfirst( $member_level ),
                                number_format_i18n( $amount, 0 ),
                                '<a href="https://trying-to-adult-rva-2025.local/member-dashboard/?tab=billing">' . esc_html__( 'Member Dashboard', 'tta' ) . '</a>',
                                esc_html( $user->user_email )
                            )
                        )
                    );

                    if ( 'basic' === $member_level ) {
                        printf(
                            '<p>%s</p>',
                            wp_kses_post(
                                sprintf(
                                    __( "Did you know that there's even MORE perks and discounts to be had with a Premium Membership? %s", 'tta' ),
                                    '<a href="https://trying-to-adult-rva-2025.local/become-a-member/">' . esc_html__( 'Learn more here.', 'tta' ) . '</a>'
                                )
                            )
                        );
                    } elseif ( 'premium' === $member_level ) {
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
            <?php echo esc_html( $checkout_error ); ?>
        </p>
    <?php elseif ( ! $items && ! $has_membership ) : ?>
        <p><?php esc_html_e( 'Your cart is empty.', 'tta' ); ?></p>
<?php else : ?>
        <?php if ( ! $is_logged_in ) : ?>
            <?php echo tta_render_login_register_section( home_url( '/checkout' ) ); ?>
        <?php endif; ?>
        <form id="tta-checkout-form" method="post">
            <?php wp_nonce_field( 'tta_checkout_action', 'tta_checkout_nonce' ); ?>
            <?php echo tta_render_checkout_summary( $cart, $discount_codes ); ?>
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
                        <p style="display:block;" class="tta-attendee-note">Please enter your credit or debit card details below.</p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Card Number', 'tta' ); ?><br />
                                <input type="text" name="card_number" placeholder="&#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226; &#8226;&#8226;&#8226;&#8226;" required <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'Expiration', 'tta' ); ?><br />
                                <input type="text" id="tta-card-exp" class="tta-card-exp" name="card_exp" placeholder="MM/YY" required maxlength="5" pattern="\d{2}/\d{2}" inputmode="numeric" <?php disabled( ! $is_logged_in ); ?> />
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php esc_html_e( 'CVC', 'tta' ); ?><br />
                                <input type="text" name="card_cvc" placeholder="123" required <?php disabled( ! $is_logged_in ); ?> />
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
