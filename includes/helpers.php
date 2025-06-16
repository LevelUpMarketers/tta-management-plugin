<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return an array of US states (abbreviation => full name).
 */
function tta_get_us_states() {
    return array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
    );
}

/**
 * Render the cart table HTML for the given cart.
 *
 * @param TTA_Cart $cart
 * @param string   $discount_code
 * @return string HTML markup
 */
function tta_render_cart_contents( TTA_Cart $cart, $discount_code = '' ) {
    ob_start();
    $items = $cart->get_items();
    $total = $cart->get_total( $discount_code );
    if ( $items ) {
        ?>
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
                            <input type="number" name="cart_qty[<?php echo esc_attr( $it['ticket_id'] ); ?>]" value="<?php echo esc_attr( $it['quantity'] ); ?>" min="0" class="tta-cart-qty">
                        </td>
                        <td><?php echo esc_html( number_format( $it['price'], 2 ) ); ?></td>
                        <td><?php echo esc_html( number_format( $sub, 2 ) ); ?></td>
                        <td><button type="button" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>" class="tta-remove-item">&times;</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3"><?php esc_html_e( 'Total', 'tta' ); ?></th>
                    <td colspan="2" class="tta-cart-total"><?php echo esc_html( number_format( $total, 2 ) ); ?></td>
                </tr>
            </tfoot>
        </table>
        <p class="tta-cart-discount">
            <label><?php esc_html_e( 'Discount Code', 'tta' ); ?>
                <input type="text" id="tta-discount-code" name="discount_code" value="<?php echo esc_attr( $discount_code ); ?>">
            </label>
        </p>
        <?php
    } else {
        echo '<p>' . esc_html__( 'Your cart is empty.', 'tta' ) . '</p>';
    }
    return trim( ob_get_clean() );
}

/**
 * Render a read-only summary of the cart for the checkout page.
 *
 * @param TTA_Cart $cart
 * @param string   $discount_code
 * @return string
 */
function tta_render_checkout_summary( TTA_Cart $cart, $discount_code = '' ) {
    ob_start();
    $items = $cart->get_items();
    $total = $cart->get_total( $discount_code );
    if ( $items ) {
        ?>
        <table class="tta-checkout-summary">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Ticket', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Qty', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Price', 'tta' ); ?></th>
                    <th><?php esc_html_e( 'Subtotal', 'tta' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $it ) : ?>
                    <?php $sub = $it['quantity'] * $it['price']; ?>
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
        <?php
    } else {
        echo '<p>' . esc_html__( 'Your cart is empty.', 'tta' ) . '</p>';
    }
    return trim( ob_get_clean() );
}
