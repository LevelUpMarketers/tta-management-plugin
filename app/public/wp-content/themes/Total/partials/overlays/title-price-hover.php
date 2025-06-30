<?php
/**
 * Title Price Hover Overlay
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Only used for inside position
if ( 'inside_link' != $position ) {
	return;
}

// Get post data
$title = isset( $args['post_title'] ) ? $args['post_title'] : get_the_title();

// Animation speed
$speed = wpex_overlay_speed( 'overlay-title-price-hover' );

?>

<div class="overlay-title-price-hover overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo intval( $speed ); ?> wpex-overflow-hidden wpex-flex wpex-items-center wpex-justify-center wpex-text-center">
	<div class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'overlay-title-price-hover' ); ?> wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'overlay-title-price-hover', '70' ); ?>"></div>
	<div class="overlay-content overlay-scale wpex-relative wpex-text-white wpex-p-15 wpex-duration-<?php echo intval( $speed ); ?> wpex-transition-transform wpex-clr">
		<div class="overlay-title wpex-text-lg"><?php echo esc_html( $title ); ?></div>
		<?php if ( function_exists( 'wpex_get_woo_product_price' ) ) { ?>
			<?php echo wpex_get_woo_product_price( get_the_ID(), '<div class="overlay-price wpex-opacity-80 wpex-italic">', '</div>' ); ?>
		<?php } ?>
	</div>
</div>