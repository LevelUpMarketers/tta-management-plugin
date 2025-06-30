<?php
/**
 * Post gallery functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 *
 */

/**
 * Check if the post has a gallery.
 *
 * @since 5.0
 */
function wpex_has_post_gallery( $post_id = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (bool) wpex_get_gallery_ids( $post_id );
}

/**
 * Retrieve attachment IDs.
 *
 * @since 1.0.0
 */
function wpex_get_gallery_ids( $post_id = '' ) {
	$attachment_ids = array();
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();
	if ( class_exists( 'WC_product' ) && 'product' == get_post_type( $post_id ) ) {
		$product = new WC_product( $post_id );
		if ( $product && method_exists( $product, 'get_gallery_image_ids' ) ) {
			$attachment_ids = $product->get_gallery_image_ids();
		}
	}
	if ( empty( $attachment_ids ) ) {
		$attachment_ids = get_post_meta( $post_id, '_easy_image_gallery', true );
	}
	if ( ! empty( $attachment_ids ) ) {
		if ( ! is_array( $attachment_ids ) ) {
			$attachment_ids = explode( ',', $attachment_ids );
		}
		$attachment_ids = array_values( array_filter( $attachment_ids, 'wpex_sanitize_gallery_id' ) );
		return apply_filters( 'wpex_get_post_gallery_ids', $attachment_ids );
	}
}

/**
 * Make sure an ID exists and is an attachement.
 *
 * @since 1.0.0
 */
function wpex_sanitize_gallery_id( $id = '' ) {
	if ( 'attachment' == get_post_type( $id ) ) {
		return $id;
	}
}

/**
 * Get array of gallery image urls.
 *
 * @since 3.5.0
 */
function wpex_get_gallery_images( $post_id = '', $size = 'full' ) {
	$post_id = $post_id ? $post_id : wpex_get_current_post_id();
	$ids     = wpex_get_gallery_ids( $post_id );
	if ( ! $ids ) {
		return;
	}
	$images = array();
	foreach ( $ids as $id ) {
		$img_url = wpex_image_resize( array(
			'attachment' => $id,
			'size'       => $size,
			'return'     => 'url',
		) );
		if ( $img_url ) {
			$images[] = $img_url;
		}
	}
	return $images;
}

/**
 * Return gallery count.
 *
 * @since 1.0.0
 */
function wpex_gallery_count( $post_id = '' ) {
	$ids = wpex_get_gallery_ids( $post_id );
	return count( $ids );
}