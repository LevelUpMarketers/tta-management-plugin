<?php
/**
 * Image Swap style thumbnail
 *
 * @package Total Wordpress Theme
 * @subpackage Templates/WooCommerce
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Return woocommerce placeholder image if no featured image is defined
if ( ! has_post_thumbnail() ) {
	wpex_woo_placeholder_img();
	return;
}

// Globals
global $product;

// Get first image
$attachment = get_post_thumbnail_id();

// Get Second Image in Gallery
$secondary_img_id = '';
if ( apply_filters( 'wpex_woo_use_secondary_thumbnail_for_image_swap', true ) ) {
	$secondary_img_id = get_post_meta( $product->get_id(), 'wpex_secondary_thumbnail', true );
}
if ( empty( $secondary_img_id ) ) {
	$attachment_ids = $product->get_gallery_image_ids();
	if ( ! empty( $attachment_ids ) ) {
		$attachment_ids = array_unique( $attachment_ids ); // remove duplicate images
		if ( $attachment_ids['0'] != $attachment ) {
			$secondary_img_id = $attachment_ids['0'];
		} elseif ( isset( $attachment_ids['1'] ) && $attachment_ids['1'] != $attachment ) {
			$secondary_img_id = $attachment_ids['1'];
		}
	}
}

$secondary_img_id = ( $secondary_img_id != $attachment ) ? $secondary_img_id : '';

// Return thumbnail
if ( $secondary_img_id ) : ?>

	<div class="woo-entry-image-swap wpex-clr"><?php

		// Main Image
		wpex_post_thumbnail( array(
			'attachment' => $attachment,
			'size'       => 'shop_catalog',
			'alt'        => wpex_get_esc_title(),
			'class'      => 'woo-entry-image-main wp-post-image',
		) );

		// Secondary Image
		wpex_post_thumbnail( array(
			'attachment' => $secondary_img_id,
			'size'       => 'shop_catalog',
			'class'      => 'woo-entry-image-secondary',
		) );

	?></div>

<?php else : ?>

	<?php
	// Single Image
	wpex_post_thumbnail( array(
		'attachment' => $attachment,
		'size'       => 'shop_catalog',
		'alt'        => wpex_get_esc_title(),
		'class'      => 'woo-entry-image-main wp-post-image',
	) ); ?>

<?php endif; ?>