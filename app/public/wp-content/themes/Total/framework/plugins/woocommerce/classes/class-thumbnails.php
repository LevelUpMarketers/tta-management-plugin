<?php
/**
 * Theme tweaks for WooCommerce images
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 *
 */

namespace TotalTheme\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class Thumbnails {

	/**
	 * Our single Thumbnails instance.
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disabled instantiation.
	}

	/**
	 * Disable the cloning of this class.
	 */
	final public function __clone() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Disable the wakeup of this class.
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of Thumbnails.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Thumbnails;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 5.0
	 */
	public function init_hooks() {

		// Disable thumb regeneration as much as possible
		add_filter( 'woocommerce_resize_images', '__return_false' );
		add_filter( 'woocommerce_image_sizes_to_resize', '__return_empty_array' );
		add_filter( 'woocommerce_regenerate_images_intermediate_image_sizes', '__return_empty_array' );
		add_action( 'customize_register', array( $this, 'remove_customizer_sections' ), 99 );

		// Admin only functions
		if ( wpex_is_request( 'admin' ) ) {

			// Set admin post thumbnail to correct size
			add_filter( 'admin_post_thumbnail_size', array( $this, 'admin_post_thumbnail_size' ), 10, 3 );

			// Remove image size settings in Woo Product Display tab
			// @todo remove since things have changed in 3.3.0 (give time for customers to update)
			add_filter( 'woocommerce_product_settings', array( $this, 'remove_product_settings' ) );

			// Add WooCommerce tab to Total image sizes panel
			add_filter( 'wpex_image_sizes_tabs', array( $this, 'image_sizes_tabs' ), 40 );

		}

		// Add image sizes to Total panel
		add_filter( 'wpex_image_sizes', array( $this, 'add_image_sizes' ), 99 );

		// Define single shop thumbnail size
		add_filter( 'woocommerce_gallery_thumbnail_size', array( $this, 'gallery_thumbnail_size' ) );

		// Filter image sizes and return cropped versions
		if ( get_theme_mod( 'image_resizing', true ) ) {

			// @todo Could be optimized a bit to prevent duplicate checks
			// @todo Figure out how to add retina support to the WooCommerce images since you can't easily add data attributes via Woo filters
			add_filter( 'wp_get_attachment_image_src', array( $this, 'attachment_image_src' ), 9999, 4 );

			// Alter cart thumbnail so it can be custom cropped seperately from the product entries
			add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'cart_item_thumbnail' ), 10, 3 );

		}

	}

	/**
	 * Remove image size settings in Woo Product Display tab.
	 *
	 * @since 2.0.0
	 */
	public function remove_product_settings( $settings ) {
		$remove = array(
			'image_options',
			'shop_catalog_image_size',
			'shop_single_image_size',
			'shop_thumbnail_image_size',
			'woocommerce_enable_lightbox'
		);
		foreach( $settings as $key => $val ) {
			if ( isset( $val['id'] ) && in_array( $val['id'], $remove ) ) {
				unset( $settings[$key] );
			}
		}
		return $settings;
	}

	/**
	 * Add WooCommerce tab to Total image sizes panel.
	 *
	 * @since 3.3.2
	 */
	public function image_sizes_tabs( $array ) {
		$array['woocommerce'] = 'WooCommerce';
		return $array;
	}

	/**
	 * Add image sizes to Total panel.
	 *
	 * @since 2.0.0
	 */
	public function add_image_sizes( $sizes ) {
		return array_merge( $sizes, array(
			'shop_catalog' => array(
				'label'   => esc_html__( 'Product Entry', 'total' ),
				'width'   => 'woo_entry_width',
				'height'  => 'woo_entry_height',
				'crop'    => 'woo_entry_image_crop',
				'section' => 'woocommerce',
			),
			'shop_single' => array(
				'label'   => esc_html__( 'Product Post', 'total' ),
				'width'   => 'woo_post_width',
				'height'  => 'woo_post_height',
				'crop'    => 'woo_post_image_crop',
				'section' => 'woocommerce',
			),
			'shop_single_thumbnail' => array(
				'label'   => esc_html__( 'Product Post Gallery Thumbnail', 'total' ),
				'width'   => 'woo_post_thumb_width',
				'height'  => 'woo_post_thumb_height',
				'crop'    => 'woo_post_thumb_crop',
				'section' => 'woocommerce',
			),
			'shop_category' => array(
				'label'     => esc_html__( 'Category Thumbnail', 'total' ),
				'width'     => 'woo_cat_entry_width',
				'height'    => 'woo_cat_entry_height',
				'crop'      => 'woo_cat_entry_image_crop',
				'section'   => 'woocommerce',
			),
			'shop_thumbnail_cart' => array(
				'label'     => esc_html__( 'Widgets & Cart Thumbnail', 'total' ),
				'width'     => 'woo_shop_thumbnail_width',
				'height'    => 'woo_shop_thumbnail_height',
				'crop'      => 'woo_shop_thumbnail_crop',
				'section'   => 'woocommerce',
			),
		) );
	}

	/**
	 * Define single shop thumbnail size
	 *
	 * @since 4.6
	 */
	public function gallery_thumbnail_size() {
		return 'shop_single_thumbnail';
	}

	/**
	 * Filter image sizes and return cropped versions where we aren't altering the HTML
	 *
	 * @since 4.0
	 */
	public function attachment_image_src( $image, $attachment_id, $size, $icon ) {

		if ( $image ) {

			switch ( $size ) {

				// Single product
				case 'single':
				case 'shop_single':
				case 'woocommerce_single':
					$custom_dims = wpex_get_thumbnail_sizes( 'shop_single' );
					$custom_size = 'shop_single';
					break;

				// Single product gallery thumbnail
				case 'shop_single_thumbnail':
					$custom_dims = wpex_get_thumbnail_sizes( 'shop_single_thumbnail' );
					$custom_size = 'shop_single_thumbnail';
					break;

				// Thumbnails.
				case 'woocommerce_thumbnail':
					$custom_dims = wpex_get_thumbnail_sizes( 'shop_thumbnail_cart' );
					$custom_size = 'shop_thumbnail_cart';
					break;

				// Catalog
				case 'shop_catalog':
					$custom_dims = wpex_get_thumbnail_sizes( 'shop_catalog' );
					$custom_size = 'shop_catalog';
					break;

			}

			// Generate custom image size via theme resizer.
			if ( $attachment_id && ! empty( $custom_size ) ) {

				$generate_image = wpex_image_resize( array(
					'attachment' => $attachment_id,
					'size'       => $custom_size,
					'height'     => isset( $custom_dims['height'] ) ? $custom_dims['height'] : '',
					'width'      => isset( $custom_dims['width'] ) ? $custom_dims['width'] : '',
					'crop'       => isset( $custom_dims['crop'] ) ? $custom_dims['crop'] : '',
					'image_src'  => $image, // IMPORTANT !!
				) );

				if ( ! empty( $generate_image ) ) {
					$image = $generate_image;
				}

			}

		}

		return $image;

	}

	/**
	 * Alter the cart item thumbnail size
	 *
	 * Needed to add retina support and properly crop images
	 *
	 * @since 4.0
	 */
	public function cart_item_thumbnail( $thumb, $cart_item, $cart_item_key ) {
		if ( ! empty( $cart_item['variation_id'] )
			&& $thumbnail = get_post_thumbnail_id( $cart_item['variation_id'] )
		) {
			return wpex_get_post_thumbnail( array(
				'size'       => 'shop_thumbnail_cart',
				'attachment' => $thumbnail,
			) );
		} elseif ( isset( $cart_item['product_id'] )
			&& $thumbnail = get_post_thumbnail_id( $cart_item['product_id'] )
		) {
			return wpex_get_post_thumbnail( array(
				'size'       => 'shop_thumbnail_cart',
				'attachment' => $thumbnail,
			) );
		} else {
			return wc_placeholder_img();
		}
		return $thumb;
	}

	/**
	 * Remove customizer sections
	 *
	 * @since 4.6
	 */
	public function remove_customizer_sections( $wp_customize ) {
		$wp_customize->remove_section( 'woocommerce_product_images' );
	}

	/**
	 * Set admin post thumbnail to correct size
	 *
	 * @see wp-admin/includes/post.php
	 * @since 4.6
	 */
	public function admin_post_thumbnail_size( $size, $thumbnail_id, $post ) {
		if ( 'product' == get_post_type( $post ) ) {
			return 'shop_single';
		}
		return $size;
	}

}
Thumbnails::instance();