<?php
// UNDER CONSTRUCTION - WILL BE AVAILABLE SOON!!! //

defined( 'ABSPATH' ) || exit;

global $product, $woocommerce_loop;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$style = get_theme_mod( 'woo_entry_card_style', 'product_5' );
$style = apply_filters( 'woo_entry_card_style', $style );

// Get card arguments
$card_args = array(
	'style'          => $style,
	'thumbnail_size' => 'shop_catalog',
	'post_id'        => $product->get_id(),
);

$overlay_style = get_theme_mod( 'woo_entry_card_overlay_style' );
if ( ! empty( $overlay_style ) && 'none' !== $overlay_style ) {
	$card_args['thumbnail_overlay_style'] = $overlay_style;
}

$class = array(
	'wpex-woo-entry',
	'col',
);

$context = ! empty( $woocommerce_loop['name'] ) ? $woocommerce_loop['name'] : '';

switch ( $context ) {
	case 'cross-sells':
		$columns = get_theme_mod( 'woocommerce_cross_sells_columns', '2' );
		break;
	case 'up-sells':
		$columns = get_theme_mod( 'woocommerce_upsells_columns', '4' );
		break;
	case 'related':
		$columns = get_theme_mod( 'woocommerce_related_columns', '4' );
		break;
	default:
		if ( ! empty( $woocommerce_loop['is_shortcode'] ) && ! empty( $woocommerce_loop['columns'] ) ) {
			$columns = $woocommerce_loop['columns'];
		} else {
			$columns = get_theme_mod( 'woocommerce_shop_columns', '4' );
		}
		break;
}

$class[] = wpex_grid_class( $columns );

$class[] = wc_get_loop_class();

?>
<li class="<?php echo implode( ' ', $class ); ?>">
	<?php wpex_card( $card_args ); ?>
</li>