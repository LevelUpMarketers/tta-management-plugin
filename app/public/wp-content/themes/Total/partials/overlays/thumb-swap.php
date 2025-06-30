<?php
/**
 * Secondary Image Swap
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

// Thumbnail required
if ( ! has_post_thumbnail() ) {
	return;
}

// Get secondary image
$secondary_image = wpex_get_secondary_thumbnail();

// Secondary image required
if ( ! $secondary_image ) {
	return;
}

?>

<div class="overlay-thumb-swap-secondary wpex-invisible wpex-opacity-0 wpex-absolute wpex-inset-0 wpex-index-1 wpex-overflow-hidden wpex-transition-all wpex-duration-300 wpex-flex wpex-items-center wpex-justify-center">
	<?php if ( is_numeric( $secondary_image ) ) {
		echo wpex_get_post_thumbnail( array(
			'attachment' => $secondary_image,
			'width'      => isset( $args['img_width'] ) ? $args['img_width'] :'',
			'height'     => isset( $args['img_height'] ) ? $args['img_height'] :'',
			'crop'       => isset( $args['img_crop'] ) ? $args['img_crop'] :'',
			'size'       => isset( $args['img_size'] ) ?$args['img_size'] :'',
		) );
	} else { ?>
		<img src="<?php echo esc_url( $secondary_image ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
	<?php } ?>
</div>