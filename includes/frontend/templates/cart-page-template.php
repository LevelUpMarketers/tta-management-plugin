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

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
    if ( isset( $_POST['tta_checkout'] ) ) {
        $cart->finalize_purchase();
        wp_safe_redirect( add_query_arg( 'checkout', 'done', get_permalink() ) );
        exit;
    }
}

$discount_code = $_SESSION['tta_discount_code'] ?? '';
$items         = $cart->get_items();
$checkout_done = isset( $_GET['checkout'] ) && 'done' === $_GET['checkout'];
?>

<div class="wrap tta-cart-page">
    <?php if ( $checkout_done ) : ?>
        <p class="tta-checkout-complete">
            <?php esc_html_e( 'Thank you for your purchase!', 'tta' ); ?>
        </p>
    <?php endif; ?>

    <form id="tta-cart-form" method="post">
        <div id="tta-cart-container">
            <?php echo tta_render_cart_contents( $cart, $discount_code ); ?>
        </div>
        <p>
            <button class="tta-cart-checkout-button" name="tta_checkout" type="submit">
                <?php esc_html_e( 'Checkout', 'tta' ); ?>
            </button>
        </p>
        <span class="tta-progress-spinner">
            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
        </span>
    </form>
</div>

<?php
get_footer();
