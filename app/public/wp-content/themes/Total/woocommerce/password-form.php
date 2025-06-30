<?php
/**
 * Template used for the WooCommerce Category Locker plugin
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

				<article class="entry-content entry wpex-clr">

					<?php
					do_action( 'wcl_before_passform' );

					if ( function_exists( 'wcl_get_the_password_form' ) ) {
						echo wcl_get_the_password_form();
					}

					do_action( 'wcl_after_passform' ); ?>

				</article>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>