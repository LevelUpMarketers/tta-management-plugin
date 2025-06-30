<?php
/**
 * WooCommerce function overrides
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Override sub-category thumbnail
 *
 * @since 4.8.2
 */
if ( ! function_exists( 'woocommerce_subcategory_thumbnail' ) && get_theme_mod( 'woo_dynamic_image_resizing', true ) ) {
	/**
	 * Show subcategory thumbnails.
	 *
	 * @param mixed $category Category.
	 */
	function woocommerce_subcategory_thumbnail( $category ) {

		// Get attachment id
		$attachment = get_term_meta( $category->term_id, 'thumbnail_id', true  );

		// Return thumbnail if attachment is defined
		if ( $attachment ) {

			$attachment_alt = wpex_get_attachment_data( $attachment, 'alt' );

			wpex_post_thumbnail( array(
				'attachment' => $attachment,
				'size'       => 'shop_category',
				'alt'        => $attachment_alt ? $attachment_alt : $category->name,
			) );

		}

		// Display placeholder
		else {

			echo '<img src="'. wc_placeholder_img_src() . '" alt="'. esc_html__( 'Placeholder Image', 'total' ) . '" />';

		}

	}
}

/**
 * Override product entry title to include link
 *
 * @since 4.8
 */
if ( ! function_exists( 'woocommerce_template_loop_product_title' )
	&& apply_filters( 'wpex_woocommerce_template_loop_product_thumbnail', true ) ) {
	function woocommerce_template_loop_product_title() {
		echo '<a href="' . esc_url( get_permalink() ) . '"><h2 class="woocommerce-loop-product__title">' . get_the_title() . '</h2></a>';
	}
}