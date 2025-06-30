<?php
/**
 * Post slider output
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get post id
$post_id = wpex_get_current_post_id();

// Get the Slider shortcode
$slider = wpex_get_post_slider_shortcode( $post_id );

// Disable on Mobile?
$disable_on_mobile = get_post_meta( $post_id, 'wpex_disable_post_slider_mobile', true );

// Get slider alternative
$slider_alt = get_post_meta( $post_id, 'wpex_post_slider_mobile_alt', true );

// Check if alider alternative for mobile custom field has a value
if ( 'on' == $disable_on_mobile && $slider_alt ) {

	// Sanitize slider mobile alt
	if ( is_numeric( $slider_alt ) ) {
		$slider_alt = wp_get_attachment_image_src( $slider_alt, 'full' );
		$slider_alt = $slider_alt[0];
	}

	// Cleanup validation for old Redux system
	if ( is_array( $slider_alt ) && ! empty( $slider_alt['url'] ) ) {
		$slider_alt = $slider_alt['url'];
	}

	// Mobile slider alternative link
	$slider_alt_link = get_post_meta( $post_id, 'wpex_post_slider_mobile_alt_url', true );

}

// Otherwise set all vars to empty
else {

	$slider_alt = $slider_alt_link = '';

}

// Slider classes
$classes = array(
	'page-slider',
	'wpex-clr'
);

if ( 'on' == get_post_meta( $post_id, 'wpex_contain_post_slider', true ) ) {
	$classes[] = 'container';
}

$classes = apply_filters( 'wpex_post_slider_classes', $classes ); ?>

<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<?php
	// Mobile slider
	if ( $slider_alt ) : ?>

		<div class="page-slider-mobile hidden-desktop wpex-text-center wpex-clr">

			<?php if ( $slider_alt_link ) :

				$target = get_post_meta( $post_id, 'wpex_post_slider_mobile_alt_url_target', true ); ?>

				<a href="<?php echo esc_url( $slider_alt_link ); ?>"<?php echo wpex_parse_link_target( $target ); ?>><img src="<?php echo esc_url( $slider_alt ); ?>" class="page-slider-mobile-alt wpex-inline-block wpex-align-middle" alt="<?php wpex_esc_title(); ?>" /></a>

			<?php else : ?>

				<img src="<?php echo esc_url( $slider_alt ); ?>" class="page-slider-mobile-alt wpex-inline-block wpex-align-middle" alt="<?php wpex_esc_title(); ?>" />

			<?php endif; ?>

		</div>

	<?php endif; ?>

	<?php
	// Open hidden on mobile wrap
	if ( 'on' == $disable_on_mobile ) {

		echo '<div class="visible-desktop wpex-clr">';

	}

	// Output slider
	echo do_shortcode( wp_kses_post( $slider ) );

	// Close hidden on mobile wrap
	if ( 'on' == $disable_on_mobile ) {

		echo '</div>';

	} ?>

</div>

<?php
// Add slider margin
if ( $margin = get_post_meta( $post_id, 'wpex_post_slider_bottom_margin', true ) ) {

	echo '<div style="height:' . intval( $margin ) . 'px;"></div>';

}