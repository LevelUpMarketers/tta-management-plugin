<?php
/**
 * WooCommerce Entry Mods
 *
 * @package Total WordPress Theme
 * @subpackage WooCommerce
 * @version 5.0
 *
 */

namespace TotalTheme\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class ProductEntry {

	/**
	 * Our single ProductEntry instance.
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
	 * Create or retrieve the instance of ProductEntry.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new ProductEntry;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Add HTML to product entries
		// Note link opens on 10 and closes on 5
		if ( apply_filters( 'wpex_woocommerce_has_shop_loop_item_inner_div', true ) ) {
			add_action( 'woocommerce_before_shop_loop_item', array( $this, 'add_shop_loop_item_inner_div' ), 0 );
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'close_shop_loop_item_inner_div' ), 99 );
		}

		// Add wrapper around product entry details to align buttons
		if ( apply_filters( 'wpex_woocommerce_has_product_entry_details_wrap', true ) ) {
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'loop_details_open' ), 99 );
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'loop_details_close' ), 4 );
		}

		// Add out of stock badge
		if ( apply_filters( 'wpex_woocommerce_out_of_stock_badge', true ) ) {
			add_action( 'woocommerce_before_shop_loop_item', array( $this, 'add_shop_loop_item_out_of_stock_badge' ) );
		}

		// Alter post class classes
		// Must run on priority 40 or else $woocommerce_loop['columns'] may be empty
		if ( apply_filters( 'wpex_woocommerce_custom_post_class', true ) ) {
			add_filter( 'post_class', array( $this, 'add_product_entry_classes' ), 40, 3 );
		}

		// Remove loop product thumbnail function and add our own that pulls from template parts
		// @todo add setting to disable this (make sure associated customizater settings are removed as well)
		if ( apply_filters( 'wpex_woocommerce_template_loop_product_thumbnail', true ) ) {

			// Tweak link open/close
			remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

			// Add link around media
			add_action( 'wpex_woocommerce_loop_thumbnail_before', 'woocommerce_template_loop_product_link_open', 0 );
			add_action( 'wpex_woocommerce_loop_thumbnail_after', 'woocommerce_template_loop_product_link_close', 11 );

			// Display custom thumbnail media
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'loop_product_thumbnail' ), 10 );

			// Add custom cart icons into thumbnail wrap
			if ( ! get_theme_mod( 'woo_default_entry_buttons', false ) ) {
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				add_action( 'wpex_woocommerce_loop_thumbnail_after', array( $this, 'loop_add_to_cart' ), 40 );
			}

		}

	}

	/**
	 * Adds an opening div "product-inner" around product entries.
	 *
	 * @since 4.4
	 */
	public function add_shop_loop_item_inner_div() {
		echo '<div class="product-inner wpex-clr">';
	}

	/**
	 * Closes the "product-inner" div around product entries.
	 *
	 * @since 4.4
	 */
	public function close_shop_loop_item_inner_div() {
		echo '</div>';
	}

	/**
	 * Adds an out of stock tag to the products.
	 *
	 * @since 4.4
	 */
	public function add_shop_loop_item_out_of_stock_badge() {
		if ( wpex_woo_product_instock() ) {
			return;
		} ?>
		<div class="outofstock-badge"><?php echo apply_filters( 'wpex_woo_outofstock_text', esc_html__( 'Out of Stock', 'total' ) ); ?></div>
		<?php
	}

	/**
	 * Open details wrapper
	 *
	 * @since 4.4
	 */
	public function loop_details_open() {
		echo '<div class="product-details match-height-content">';
	}

	/**
	 * Close details wrapper
	 *
	 * @since 4.4
	 */
	public function loop_details_close() {
		echo '</div>';
	}

	/**
	 * Add classes to WooCommerce product entries.
	 *
	 * @since 4.8
	 */
	public function add_product_entry_classes( $classes, $class = '', $post_id = '' ) {

		if ( ! $post_id || ! in_array( get_post_type( $post_id ), array( 'product', 'product_variation' ) ) ) {
			return $classes;
		}

		if ( is_search() && empty( $_GET[ 'post_type' ] ) ) {
			return $classes;
		}

		global $woocommerce_loop;

		if ( ! empty( $woocommerce_loop ) ) {

			$context  = ! empty( $woocommerce_loop['name'] ) ? $woocommerce_loop['name'] : '';
			$woo_cols = ! empty( $woocommerce_loop['columns'] ) ? $woocommerce_loop['columns'] : '';
			$columns  = '';

			// Add default theme classes
			$classes[] = 'col';
			$classes[] = 'wpex-woo-entry'; // Fallback class

			// Shop archives Columns
			if ( empty( $context ) ) {
				$columns = get_theme_mod( 'woocommerce_shop_columns', '4' );
			}

			// Cross-Sells
			elseif ( 'cross-sells' == $context ) {
				$columns = get_theme_mod( 'woocommerce_cross_sells_columns', '2' );
			}

			// Up-sells
			elseif ( 'up-sells' == $context ) {
				$columns = get_theme_mod( 'woocommerce_upsells_columns', '4' );
			}

			// Related
			elseif ( 'related' == $context ) {
				$columns = get_theme_mod( 'woocommerce_related_columns', '4' );
			}

			// Core WooCommerce shortcodes
			elseif ( in_array( $context, array( 'featured_products', 'products', 'recent_products' ) )
				&& ! empty( $woocommerce_loop['is_shortcode'] )
				&& isset( $woocommerce_loop['columns'] ) ) {
				$columns = get_theme_mod( 'woocommerce_shop_columns', '4' );
			}

			// $woocommerce_loop['columns'] must equal our defined cols otherwise we are in some weird loop
			// and we don't want to give cols the incorrect classes
			if ( intval( wpex_get_array_first_value( $columns ) ) == $woo_cols ) {
				$classes[] = wpex_grid_class( $columns );
			} else {
				$classes[] = wpex_grid_class( $woo_cols );
			}

		}

		return $classes;

	}

	/**
	 * Returns our product thumbnail from our template parts based on selected style in theme mods.
	 *
	 * @since 4.8
	 */
	public function loop_product_thumbnail() {

		// Get entry product media style
		$style = ( $style = get_theme_mod( 'woo_product_entry_style' ) ) ? $style : 'image-swap';

		// Get entry product media template part
		echo '<div class="wpex-loop-product-images">';
			do_action( 'wpex_woocommerce_loop_thumbnail_before' );
				get_template_part( 'woocommerce/loop/thumbnail/' . $style );
			do_action( 'wpex_woocommerce_loop_thumbnail_after' );
		echo '</div>';

	}

	/**
	 * Output loop add to cart buttons with customw wrapper
	 *
	 * @since 4.8
	 */
	public function loop_add_to_cart() { ?>
		<div class="wpex-loop-product-add-to-cart"><?php woocommerce_template_loop_add_to_cart(); ?></div>
	<?php }

}
ProductEntry::instance();