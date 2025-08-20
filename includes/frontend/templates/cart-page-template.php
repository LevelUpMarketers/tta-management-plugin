<?php
/**
 * Template Name: Cart Page
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Instantiate the cart before any output so sessions start correctly
$cart = new TTA_Cart();

get_header();

$header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">CART</p>[/vc_column_text][/vc_column][/vc_row]';
echo do_shortcode( $header_shortcode );

$discount_codes = $_SESSION['tta_discount_codes'] ?? [];
$notice        = tta_get_cart_notice();
$waitlist_ctx  = tta_get_waitlist_context();

if ( $waitlist_ctx ) {
    wp_enqueue_style(
        'tta-eventpage-css',
        TTA_PLUGIN_URL . 'assets/css/frontend/event-page.css',
        [ 'tta-frontend-css' ],
        TTA_PLUGIN_VERSION
    );
    wp_enqueue_script(
        'tta-waitlist-js',
        TTA_PLUGIN_URL . 'assets/js/frontend/waitlist.js',
        [ 'jquery' ],
        TTA_PLUGIN_VERSION,
        true
    );
    wp_localize_script(
        'tta-waitlist-js',
        'tta_waitlist',
        [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'tta_frontend_nonce' ),
            'eventUte'  => $waitlist_ctx['event_ute_id'],
            'eventName' => $waitlist_ctx['event_name'],
            'firstName' => $waitlist_ctx['first_name'],
            'lastName'  => $waitlist_ctx['last_name'],
            'email'     => $waitlist_ctx['email'],
            'phone'     => $waitlist_ctx['phone'],
        ]
    );
}

$items = $cart->get_items();
?>
<div class="wrap tta-cart-page">
    <form id="tta-cart-form">
        <div id="tta-cart-container">
            <?php if ( $notice ) : ?>
                <p class="tta-cart-notice">
                    <?php echo wp_kses_post( $notice ); ?>
                    <?php if ( $waitlist_ctx ) : ?>
                        <br>
                        <button type="button" class="tta-button tta-button-primary tta-join-waitlist" data-ticket-id="<?php echo esc_attr( $waitlist_ctx['ticket_id'] ); ?>" data-ticket-name="<?php echo esc_attr( $waitlist_ctx['ticket_name'] ); ?>">
                            <?php esc_html_e( 'Join The Waitlist', 'tta' ); ?>
                        </button>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php echo tta_render_cart_contents( $cart, $discount_codes, [] ); ?>
        </div>
    </form>
    <p>
        <a class="tta-cart-checkout-button<?php echo empty( $items ) ? ' tta-disabled' : ''; ?>" href="<?php echo esc_url( home_url( '/checkout' ) ); ?>"<?php echo empty( $items ) ? ' disabled aria-disabled="true"' : ''; ?>>
            <?php esc_html_e( 'Checkout', 'tta' ); ?>
        </a>
    </p>
    <span class="tta-progress-spinner">
        <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
    </span>
</div>
<?php if ( $waitlist_ctx ) : ?>
<div id="tta-waitlist-overlay" class="tta-waitlist-overlay" style="display:none;">
  <div class="tta-waitlist-modal">
    <button type="button" class="tta-waitlist-close" aria-label="Close">×</button>
    <h2><?php esc_html_e( 'Join The Waitlist', 'tta' ); ?></h2>
    <p class="tta-waitlist-description"><?php esc_html_e( "We'll notify you if a spot opens up.", 'tta' ); ?></p>
    <form id="tta-waitlist-form">
      <input type="hidden" name="ticket_id" value="">
      <input type="hidden" name="ticket_name" value="">
      <label><?php esc_html_e( 'First Name', 'tta' ); ?>
        <input type="text" name="first_name" required>
      </label>
      <label><?php esc_html_e( 'Last Name', 'tta' ); ?>
        <input type="text" name="last_name" required>
      </label>
      <label><?php esc_html_e( 'Email', 'tta' ); ?>
        <input type="email" name="email" required>
      </label>
      <label><?php esc_html_e( 'Phone', 'tta' ); ?>
        <input type="tel" name="phone">
      </label>
      <label><input type="checkbox" name="opt_email" checked> <?php esc_html_e( 'email me when a spot becomes available', 'tta' ); ?></label>
      <label><input type="checkbox" name="opt_sms" checked> <?php esc_html_e( 'text me when a spot becomes available', 'tta' ); ?></label>
      <button type="submit" class="tta-button tta-button-primary"><?php esc_html_e( 'Join Waitlist', 'tta' ); ?></button>
      <span class="tta-progress-spinner">
        <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
      </span>
      <span class="tta-admin-progress-response"><p class="tta-admin-progress-response-p"></p></span>
    </form>
  </div>
</div>
<?php endif; ?>

<?php
get_footer();
