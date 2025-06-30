<?php
/**
 * WooCommerce Default template
 *
 * @package Total WordPress Theme
 * @subpackage Templates
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

?>

	<div id="content-wrap" class="container wpex-clr">

		<?php wpex_hook_primary_before(); ?>

		<div id="primary" class="content-area wpex-clr">

			<?php wpex_hook_content_before(); ?>

			<div id="content" class="site-content wpex-clr">

				<?php wpex_hook_content_top(); ?>

				<article class="entry-content entry wpex-clr"><?php

					// Default shop output
					if ( wpex_woo_archive_has_loop() || false === wpex_has_woo_mods() ) {
						woocommerce_content();
					}

					// Custom shop output
					else {
						$shop_page = get_post( wc_get_page_id( 'shop' ) );
						if ( $shop_page && $shop_page->post_content ) {
							echo wpex_the_content( $shop_page->post_content );
						}
					}

				?></article>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>