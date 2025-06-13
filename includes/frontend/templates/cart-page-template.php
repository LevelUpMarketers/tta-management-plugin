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
    if ( isset( $_POST['tta_update_cart'] ) ) {
        foreach ( (array) ( $_POST['cart_qty'] ?? [] ) as $ticket_id => $qty ) {
            $cart->update_quantity( intval( $ticket_id ), intval( $qty ) );
        }
        $_SESSION['tta_discount_code'] = sanitize_text_field( $_POST['discount_code'] ?? '' );
        wp_safe_redirect( get_permalink() );
        exit;
    }

    if ( isset( $_POST['tta_checkout'] ) ) {
        $cart->finalize_purchase();
        wp_safe_redirect( add_query_arg( 'checkout', 'done', get_permalink() ) );
        exit;
    }
}

$discount_code = $_SESSION['tta_discount_code'] ?? '';
$items         = $cart->get_items();
$total         = $cart->get_total( $discount_code );
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
                    <?php foreach ( $items as $it ) : ?>
                        <?php $sub = $it['quantity'] * $it['price']; ?>
                        <tr>
                            <td><?php echo esc_html( $it['ticket_name'] ); ?></td>
                            <td>
                                <input type="number" name="cart_qty[<?php echo esc_attr( $it['ticket_id'] ); ?>]" value="<?php echo esc_attr( $it['quantity'] ); ?>" min="0">
                            </td>
                            <td><?php echo esc_html( number_format( $it['price'], 2 ) ); ?></td>
                            <td><?php echo esc_html( number_format( $sub, 2 ) ); ?></td>
                            <td><button type="submit" name="cart_qty[<?php echo esc_attr( $it['ticket_id'] ); ?>]" value="0" class="tta-remove-item">&times;</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3"><?php esc_html_e( 'Total', 'tta' ); ?></th>
                        <td colspan="2"><?php echo esc_html( number_format( $total, 2 ) ); ?></td>
                    </tr>
                </tfoot>
            </table>
            <p class="tta-cart-discount">
                <label><?php esc_html_e( 'Discount Code', 'tta' ); ?>
                    <input type="text" name="discount_code" value="<?php echo esc_attr( $discount_code ); ?>">
                </label>
                <button name="tta_update_cart" type="submit" class="tta-cart-update-button"><?php esc_html_e( 'Update Cart', 'tta' ); ?></button>
            </p>
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
