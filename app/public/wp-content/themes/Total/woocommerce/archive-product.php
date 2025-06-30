<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

?>

	<div id="content-wrap" class="container wpex-clr">

			<?php wpex_hook_primary_before(); ?>

			<div id="primary" class="content-area wpex-clr">

				<?php wpex_hook_content_before(); ?>

				<div id="content" class="site-content wpex-clr">

					<?php wpex_hook_content_top(); ?>

					<article class="entry-content entry wpex-clr">

						<?php
						/**
						 * woocommerce_before_main_content hook.
						 *
						 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
						 * @hooked woocommerce_breadcrumb - 20
						 */
						do_action( 'woocommerce_before_main_content' ); ?>

						<?php
						/**
						 * woocommerce_archive_description hook.
						 *
						 * @hooked woocommerce_taxonomy_archive_description - 10
						 * @hooked woocommerce_product_archive_description - 10
						 */
						do_action( 'woocommerce_archive_description' ); ?>

						<?php
						if ( have_posts() ) {
							/**
							 * Hook: woocommerce_before_shop_loop.
							 *
							 * @hooked wc_print_notices - 10
							 * @hooked woocommerce_result_count - 20
							 * @hooked woocommerce_catalog_ordering - 30
							 */
							do_action( 'woocommerce_before_shop_loop' );
							woocommerce_product_loop_start();
							if ( wc_get_loop_prop( 'total' ) ) {
								while ( have_posts() ) {
									the_post();
									/**
									 * Hook: woocommerce_shop_loop.
									 *
									 * @hooked WC_Structured_Data::generate_product_data() - 10
									 */
									do_action( 'woocommerce_shop_loop' );
									wc_get_template_part( 'content', 'product' );
								}
							}
							woocommerce_product_loop_end();
							/**
							 * Hook: woocommerce_after_shop_loop.
							 *
							 * @hooked woocommerce_pagination - 10
							 */
							do_action( 'woocommerce_after_shop_loop' );
						} else {
							/**
							 * Hook: woocommerce_no_products_found.
							 *
							 * @hooked wc_no_products_found - 10
							 */
							do_action( 'woocommerce_no_products_found' );
						}
						/**
						 * Hook: woocommerce_after_main_content.
						 *
						 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
						 */
						do_action( 'woocommerce_after_main_content' ); ?>

					</article>

					<?php wpex_hook_content_bottom(); ?>

				</div>

				<?php wpex_hook_content_after(); ?>

			</div>

			<?php wpex_hook_primary_after(); ?>

		</div>

<?php get_footer( 'shop' ); ?>