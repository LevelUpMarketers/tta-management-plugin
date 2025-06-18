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
 * Recursively unslash incoming data.
 *
 * @param mixed $value
 * @return mixed
 */
function tta_unslash( $value ) {
    if ( is_array( $value ) ) {
        return array_map( 'tta_unslash', $value );
    }
    return is_string( $value ) ? wp_unslash( $value ) : $value;
}

/**
 * Sanitize a text field allowing apostrophes from user input.
 *
 * @param mixed $value
 * @return string
 */
function tta_sanitize_text_field( $value ) {
    return sanitize_text_field( tta_unslash( $value ) );
}

/**
 * Sanitize textarea input preserving apostrophes.
 *
 * @param mixed $value
 * @return string
 */
function tta_sanitize_textarea_field( $value ) {
    return sanitize_textarea_field( tta_unslash( $value ) );
}

/**
 * Sanitize email input preserving apostrophes.
 *
 * @param mixed $value
 * @return string
 */
function tta_sanitize_email( $value ) {
    return sanitize_email( tta_unslash( $value ) );
}

/**
 * Sanitize a URL input preserving apostrophes.
 *
 * @param mixed $value
 * @return string
 */
function tta_esc_url_raw( $value ) {
    return esc_url_raw( tta_unslash( $value ) );
}

/**
 * Decode a discount string stored in the events table.
 *
 * @param string $raw
 * @return array{code:string,type:string,amount:float}
 */
function tta_parse_discount_data( $raw ) {
    $default = [ 'code' => '', 'type' => 'percent', 'amount' => 0 ];
    if ( ! $raw ) {
        return $default;
    }

    $data = json_decode( $raw, true );
    if ( is_array( $data ) && isset( $data['code'] ) ) {
        $code   = sanitize_text_field( $data['code'] );
        $type   = in_array( $data['type'], [ 'flat', 'percent' ], true ) ? $data['type'] : 'percent';
        $amount = floatval( $data['amount'] ?? 0 );
        return [
            'code'   => $code,
            'type'   => $type,
            'amount' => $amount,
        ];
    }

    // Legacy plain code string defaults to 10% discount
    return [
        'code'   => sanitize_text_field( $raw ),
        'type'   => 'percent',
        'amount' => 10,
    ];
}

/**
 * Encode discount data for storage.
 *
 * @param string $code
 * @param string $type
 * @param float  $amount
 * @return string
 */
function tta_build_discount_data( $code, $type = 'percent', $amount = 0 ) {
    $code   = sanitize_text_field( $code );
    if ( '' === $code ) {
        return '';
    }
    $type   = in_array( $type, [ 'flat', 'percent' ], true ) ? $type : 'percent';
    $amount = floatval( $amount );

    $data = [
        'code'   => $code,
        'type'   => $type,
        'amount' => $amount,
    ];

    return wp_json_encode( $data );
}

/**
 * Store a notice to display on the cart page.
 *
 * @param string $message
 */
function tta_set_cart_notice( $message ) {
    if ( ! session_id() ) {
        session_start();
    }
    $_SESSION['tta_cart_notice'] = sanitize_text_field( $message );
}

/**
 * Fetch and clear any cart notice stored in the session.
 *
 * @return string
 */
function tta_get_cart_notice() {
    if ( ! session_id() ) {
        session_start();
    }
    if ( empty( $_SESSION['tta_cart_notice'] ) ) {
        return '';
    }

    $msg = sanitize_text_field( $_SESSION['tta_cart_notice'] );
    unset( $_SESSION['tta_cart_notice'] );
    return $msg;
}

/**
 * Get count of tickets a user has already purchased for an event.
 *
 * @param int    $user_id
 * @param string $event_ute_id
 * @return int
 */
function tta_get_purchased_ticket_count( $user_id, $event_ute_id ) {
    global $wpdb;
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT action_data FROM {$wpdb->prefix}tta_memberhistory WHERE wpuserid = %d AND action_type = 'purchase'",
            $user_id
        ),
        ARRAY_A
    );
    $total = 0;
    foreach ( $rows as $row ) {
        $data = json_decode( $row['action_data'], true );
        foreach ( (array) ( $data['items'] ?? [] ) as $it ) {
            if ( ( $it['event_ute_id'] ?? '' ) === $event_ute_id ) {
                $total += intval( $it['quantity'] );
            }
        }
    }
    return $total;
}

/**
 * Render the cart table HTML for the given cart.
 *
 * @param TTA_Cart $cart
 * @param array    $discount_codes
 * @param array    $notices        Optional per-ticket messages keyed by ticket ID.
 *
 * @return string HTML markup
*/
function tta_render_cart_contents( TTA_Cart $cart, $discount_codes = [], array $notices = [] ) {
    ob_start();
    $items = $cart->get_items_with_discounts( $discount_codes );
    $total = $cart->get_total( $discount_codes );
    $code_events = [];
    foreach ( $items as $row ) {
        $info = tta_parse_discount_data( $row['discountcode'] );
        if ( $info['code'] && ! isset( $code_events[ $info['code'] ] ) ) {
            $code_events[ $info['code'] ] = $row['event_name'];
        }
    }
    if ( $items ) {
        ?>
        <table class="tta-cart-table">
            <thead>
                <tr>
                    <th>
                        <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( 'Hover over each event name for a description.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Event', 'tta' ); ?>
                    </th>
                    <th>
                            <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'We reserve your ticket for 5 minutes so events don\'t oversell. After 5 minutes it becomes available to others.' ); ?>">
                                <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Ticket Reserved for…', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Limit of two tickets per event in total.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Quantity', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Base cost before member discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Price', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Price for this row after all discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Subtotal', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon tta-tooltip-left" data-tooltip="<?php echo esc_attr( 'Remove this item from your cart.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Remove Item', 'tta' ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $it ) : ?>
                    <?php $sub = $it['quantity'] * $it['final_price']; ?>
                    <?php $expire_at = strtotime( $it['expires_at'] ); ?>
                    <tr data-expire-at="<?php echo esc_attr( $expire_at ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>">
                        <td data-label="<?php echo esc_attr( 'Event' ); ?>">
                            <?php
                            $desc = '';
                            if ( $it['page_id'] && function_exists( 'get_post_field' ) ) {
                                $desc = get_post_field( 'post_excerpt', intval( $it['page_id'] ) );
                                if ( '' === $desc ) {
                                    $desc = wp_strip_all_tags( wp_trim_words( get_post_field( 'post_content', intval( $it['page_id'] ) ), 30 ) );
                                }
                            }
                            ?>
                            <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( $desc ); ?>">
                                <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                            </span>
                            <a href="<?php echo esc_url( get_permalink( $it['page_id'] ) ); ?>">
                                <?php echo esc_html( $it['event_name'] ); ?>
                            </a><br>
                            <?php echo esc_html( $it['ticket_name'] ); ?>
                        </td>
                        <td data-label="<?php echo esc_attr( 'Ticket Reserved for…' ); ?>" class="tta-countdown-cell"><span class="tta-countdown"></span></td>
                        <td data-label="<?php echo esc_attr( 'Quantity' ); ?>">
                            <input type="number" name="cart_qty[<?php echo esc_attr( $it['ticket_id'] ); ?>]" value="<?php echo esc_attr( $it['quantity'] ); ?>" min="0" class="tta-cart-qty">
                            <?php $ntext = $notices[ $it['ticket_id'] ] ?? ''; ?>
                            <div class="tta-ticket-notice <?php echo $ntext ? 'tt-show' : ''; ?>" aria-live="polite"><?php echo esc_html( $ntext ); ?></div>
                        </td>
                        <td data-label="<?php echo esc_attr( 'Price' ); ?>">
                            <?php
                            $base = '$' . number_format( $it['baseeventcost'], 2 );
                            echo esc_html( $base );
                            if ( intval( $it['quantity'] ) > 1 ) {
                                echo ' x ' . intval( $it['quantity'] );
                            }
                            ?>
                        </td>
                        <td data-label="<?php echo esc_attr( 'Subtotal' ); ?>">
                            <?php
                            $orig = '$' . number_format( $it['baseeventcost'] * $it['quantity'], 2 );
                            $final = '$' . number_format( $sub, 2 );
                            if ( floatval( $it['final_price'] ) !== floatval( $it['baseeventcost'] ) ) {
                                echo '<span class="tta-price-strike">' . esc_html( $orig ) . '</span> ' . esc_html( $final );
                            } else {
                                echo esc_html( $final );
                            }
                            ?>
                        </td>
                        <td><button type="button" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>" class="tta-remove-item" aria-label="Remove"></button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4"><?php esc_html_e( 'Total', 'tta' ); ?></th>
                    <td colspan="2" class="tta-cart-total">$<?php echo esc_html( number_format( $total, 2 ) ); ?></td>
                </tr>
                <?php if ( $discount_codes ) : ?>
                <tr class="tta-active-discounts">
                    <td colspan="6">
                        <?php esc_html_e( 'Active Discount Codes:', 'tta' ); ?>
                        <?php foreach ( $discount_codes as $code ) : ?>
                            <?php $ev = $code_events[ $code ] ?? ''; ?>
                            <span class="tta-discount-tag"><?php echo esc_html( $code . ( $ev ? " ($ev)" : '' ) ); ?> <button type="button" class="tta-remove-discount tta-remove-item" data-code="<?php echo esc_attr( $code ); ?>" aria-label="Remove"></button></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        <p class="tta-cart-discount">
            <label><?php esc_html_e( 'Discount Code', 'tta' ); ?>
                <input type="text" id="tta-discount-code" name="discount_code">
            </label>
            <button type="button" id="tta-apply-discount" disabled><?php esc_html_e( 'Apply Discount', 'tta' ); ?></button>
            <span class="tta-discount-feedback"></span>
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
 * @param array    $discount_codes Optional active discount codes.
 *
 * @return string
 */
function tta_render_checkout_summary( TTA_Cart $cart, $discount_codes = [] ) {
    ob_start();
    $items = $cart->get_items_with_discounts( $discount_codes );
    $total = $cart->get_total( $discount_codes );
    $code_events = [];
    foreach ( $items as $row ) {
        $info = tta_parse_discount_data( $row['discountcode'] );
        if ( $info['code'] && ! isset( $code_events[ $info['code'] ] ) ) {
            $code_events[ $info['code'] ] = $row['event_name'];
        }
    }
    if ( $items ) {
        ?>
        <div id="tta-checkout-container">
        <table class="tta-checkout-summary">
            <thead>
                <tr>
                    <th>
                        <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( 'Hover over each event name for a description.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Event', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( "We reserve your ticket for 5 minutes so events don't oversell. After 5 minutes it becomes available to others." ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Ticket Reserved for…', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Limit of two tickets per event in total.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Qty', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Base cost before member discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Price', 'tta' ); ?>
                    </th>
                    <th>
                        <span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( 'Price for this row after all discounts.' ); ?>">
                            <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                        </span>
                        <?php esc_html_e( 'Subtotal', 'tta' ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $it ) : ?>
                    <?php $sub = $it['quantity'] * $it['final_price']; ?>
                    <?php $expire_at = strtotime( $it['expires_at'] ); ?>
                    <tr data-expire-at="<?php echo esc_attr( $expire_at ); ?>" data-ticket="<?php echo esc_attr( $it['ticket_id'] ); ?>">
                        <td data-label="<?php echo esc_attr( 'Event' ); ?>">
                            <?php
                            $desc = '';
                            if ( $it['page_id'] && function_exists( 'get_post_field' ) ) {
                                $desc = get_post_field( 'post_excerpt', intval( $it['page_id'] ) );
                                if ( '' === $desc ) {
                                    $desc = wp_strip_all_tags( wp_trim_words( get_post_field( 'post_content', intval( $it['page_id'] ) ), 30 ) );
                                }
                            }
                            ?>
                            <span class="tta-tooltip-icon tta-tooltip-right" data-tooltip="<?php echo esc_attr( $desc ); ?>">
                                <img src="<?php echo esc_url( ( defined( 'TTA_PLUGIN_URL' ) ? TTA_PLUGIN_URL : '' ) . 'assets/images/admin/question.svg' ); ?>" alt="?">
                            </span>
                            <a href="<?php echo esc_url( get_permalink( $it['page_id'] ) ); ?>">
                                <?php echo esc_html( $it['event_name'] ); ?>
                            </a><br>
                            <?php echo esc_html( $it['ticket_name'] ); ?>
                        </td>
                        <td class="tta-countdown-cell" data-label="<?php echo esc_attr( 'Ticket Reserved for…' ); ?>"><span class="tta-countdown"></span></td>
                        <td data-label="<?php echo esc_attr( 'Qty' ); ?>"><?php echo intval( $it['quantity'] ); ?></td>
                        <td data-label="<?php echo esc_attr( 'Price' ); ?>">
                            <?php
                            $base = '$' . number_format( $it['baseeventcost'], 2 );
                            echo esc_html( $base );
                            if ( intval( $it['quantity'] ) > 1 ) {
                                echo ' x ' . intval( $it['quantity'] );
                            }
                            ?>
                        </td>
                        <td data-label="<?php echo esc_attr( 'Subtotal' ); ?>">
                            <?php
                            $orig = '$' . number_format( $it['baseeventcost'] * $it['quantity'], 2 );
                            $final_sub = '$' . number_format( $sub, 2 );
                            if ( floatval( $it['final_price'] ) !== floatval( $it['baseeventcost'] ) ) {
                                echo '<span class="tta-price-strike">' . esc_html( $orig ) . '</span> ' . esc_html( $final_sub );
                            } else {
                                echo esc_html( $final_sub );
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4"><?php esc_html_e( 'Total', 'tta' ); ?></th>
                    <td>$<?php echo esc_html( number_format( $total, 2 ) ); ?></td>
                </tr>
                <?php if ( $discount_codes ) : ?>
                <tr class="tta-active-discounts">
                    <td colspan="5">
                        <?php esc_html_e( 'Active Discount Codes:', 'tta' ); ?>
                        <?php foreach ( $discount_codes as $code ) : ?>
                            <?php $ev = $code_events[ $code ] ?? ''; ?>
                            <span class="tta-discount-tag"><?php echo esc_html( $code . ( $ev ? " ($ev)" : '' ) ); ?></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        </div>
        <?php
    } else {
        echo '<p>' . esc_html__( 'Your cart is empty.', 'tta' ) . '</p>';
    }
    return trim( ob_get_clean() );
}

/**
 * Render an image preview for admin screens that works even when
 * WordPress cannot generate intermediate sizes (e.g. unsupported
 * MIME types).
 *
 * @param int   $attachment_id Attachment ID.
 * @param array $size          [width, height] to display.
 * @param array $attrs         Optional attributes for the img tag.
 * @return string              HTML <img> markup or empty string.
 */
function tta_admin_preview_image( $attachment_id, array $size, array $attrs = [] ) {
    $attachment_id = intval( $attachment_id );
    if ( ! $attachment_id ) {
        return '';
    }

    $url = wp_get_attachment_image_url( $attachment_id, $size );
    if ( ! $url ) {
        $url = wp_get_attachment_url( $attachment_id );
    }

    if ( ! $url ) {
        return '';
    }

    $attr_str = '';
    foreach ( $attrs as $key => $val ) {
        $attr_str .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $val ) );
    }

    $width  = intval( $size[0] );
    $height = intval( $size[1] );

    return sprintf(
        '<img src="%s" width="%d" height="%d"%s />',
        esc_url( $url ),
        $width,
        $height,
        $attr_str
    );
}
