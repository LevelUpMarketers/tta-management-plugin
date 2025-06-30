<?php
/**
 * The template for editing templatera templates via the front-end editor.
 *
 * @package Total WordPress Theme
 * @subpackage Templates
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

	<div id="content-wrap" class="container wpex-clr">

		<?php wpex_hook_primary_before(); ?>

		<div id="primary" class="content-area wpex-clr">

			<?php wpex_hook_content_before(); ?>

			<div id="content" class="site-content wpex-clr">

				<?php wpex_hook_content_top(); ?>

				<div class="single-page-content entry wpex-clr">

					<?php if ( wpex_is_footer_builder_page() || wpex_is_header_builder_page() ) : ?>

						<div class="wpex-theme-builder-content-area wpex-mb-40"><?php esc_html_e( 'Content Area', 'total' ); ?></div>

					<?php else : ?>

						<?php while ( have_posts() ) : the_post(); ?>

							<?php the_content(); ?>

						<?php endwhile; ?>

					<?php endif; ?>

				</div>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>