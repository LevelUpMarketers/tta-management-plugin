<?php
/**
 * Template Name: Landing Page
 *
 * @package Total WordPress Theme
 * @subpackage Templates
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

				<?php if ( ! wpex_theme_do_location( 'single' ) ) : ?>

					<?php while ( have_posts() ) : the_post(); ?>

						<article class="entry-content entry wpex-clr">

							<?php the_content(); ?>

						</article>

					 <?php endwhile; ?>

				<?php endif; ?>

				<?php get_template_part( 'partials/post-edit' ); ?>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>