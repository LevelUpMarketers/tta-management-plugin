<?php
/**
 * Product Loop Start
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

defined( 'ABSPATH' ) || exit;

/*----------------------------------------------------------------------*/
/* [ Custom Theme output ]
/*----------------------------------------------------------------------*/
if ( true === wpex_has_woo_mods() ) :

	// Define classes and apply filter for easy modification
	$classes = 'products wpex-row wpex-clr';

	if ( get_theme_mod( 'woo_entry_equal_height', false ) ) {
		$classes .= ' match-height-grid';
	}

	if ( $gap = get_theme_mod( 'woo_shop_columns_gap' ) ) {
		$classes .= ' gap-' . sanitize_html_class( $gap );
	}

	$classes = apply_filters( 'wpex_woo_loop_wrap_classes', $classes );

	?>

	<ul class="<?php echo esc_attr( $classes );?>">

<?php
/*----------------------------------------------------------------------*/
/* [ Default output ]
/*----------------------------------------------------------------------*/
else : ?>

	<ul class="products columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>">

<?php endif; ?>