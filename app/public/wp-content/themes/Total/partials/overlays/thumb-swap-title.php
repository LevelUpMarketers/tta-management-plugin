<?php
/**
 * Secondary Image Swap & Title
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

<div class="overlay-thumb-swap-secondary wpex-invisible wpex-opacity-0 wpex-absolute wpex-inset-0 wpex-z-1 wpex-overflow-hidden wpex-transition-all wpex-duration-500 wpex-flex wpex-items-center wpex-justify-center">
	<?php
	if ( is_numeric( $secondary_image ) ) {
		echo wpex_get_post_thumbnail( array(
			'attachment' => $secondary_image,
			'width'      => isset( $args['img_width'] ) ? $args['img_width'] :'',
			'height'     => isset( $args['img_height'] ) ? $args['img_height'] :'',
			'crop'       => isset( $args['img_crop'] ) ? $args['img_crop'] :'',
			'alt'        => isset( $args['post_esc_title'] ) ?$args['post_esc_title'] :'',
			'size'       => isset( $args['img_size'] ) ?$args['img_size'] :'',
		) );
	} else { ?>
		<img src="<?php echo esc_url( $secondary_image ); ?>" />
	<?php } ?>
	<div class="overlay-thumb-swap-title wpex-absolute wpex-bottom-0 wpex-inset-x-0 wpex-text-white wpex-font-semibold wpex-text-center wpex-leading-snug wpex-py-30 wpex-px-20 wpex-text-lg"><span class="wpex-relative wpex-z-5"><?php echo esc_html( get_the_title() ); ?></span></div>
</div>