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

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['tta_checkout'] ) ) {
    $cart->finalize_purchase();
    wp_safe_redirect( add_query_arg( 'checkout', 'done', get_permalink() ) );
    exit;
}

$items         = $cart->get_items();
$checkout_done = isset( $_GET['checkout'] ) && 'done' === $_GET['checkout'];
?>

<div class="wrap tta-cart-page">
    <?php if ( $checkout_done ) : ?>
        <p class="tta-checkout-complete">
            <?php esc_html_e( 'Thank you for your purchase!', 'tta' ); ?>
        </p>
    <?php endif; ?>

    <?php if ( $items ) : ?>
        <form method="post">
            <table class="tta-cart-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Ticket', 'tta' ); ?></th>
                        <th><?php esc_html_e( 'Quantity', 'tta' ); ?></th>
                        <th><?php esc_html_e( 'Price', 'tta' ); ?></th>
                        <th><?php esc_html_e( 'Subtotal', 'tta' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ( $items as $it ) : ?>
                        <?php $sub = $it['quantity'] * $it['price']; $total += $sub; ?>
                        <tr>
                            <td><?php echo esc_html( $it['ticket_name'] ); ?></td>
                            <td><?php echo intval( $it['quantity'] ); ?></td>
                            <td><?php echo esc_html( number_format( $it['price'], 2 ) ); ?></td>
                            <td><?php echo esc_html( number_format( $sub, 2 ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3"><?php esc_html_e( 'Total', 'tta' ); ?></th>
                        <td><?php echo esc_html( number_format( $total, 2 ) ); ?></td>
                    </tr>
                </tfoot>
            </table>
            <p>
                <button class="tta-cart-checkout-button" name="tta_checkout" type="submit">
                    <?php esc_html_e( 'Checkout', 'tta' ); ?>
                </button>
            </p>
        </form>
    <?php else : ?>
        <p><?php esc_html_e( 'Your cart is empty.', 'tta' ); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
