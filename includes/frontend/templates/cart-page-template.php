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

$discount_codes = $_SESSION['tta_discount_codes'] ?? [];
$notice        = tta_get_cart_notice();

$items = $cart->get_items();
?>
<div class="wrap tta-cart-page">
    <?php if ( $notice ) : ?>
        <p class="tta-cart-notice"><?php echo esc_html( $notice ); ?></p>
    <?php endif; ?>
    <form id="tta-cart-form">
        <div id="tta-cart-container">
            <?php echo tta_render_cart_contents( $cart, $discount_codes, [] ); ?>
        </div>
    </form>
    <p>
        <a class="tta-cart-checkout-button" href="<?php echo esc_url( home_url( '/checkout' ) ); ?>">
            <?php esc_html_e( 'Checkout', 'tta' ); ?>
        </a>
    </p>
    <span class="tta-progress-spinner">
        <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
    </span>
</div>

<?php
get_footer();
