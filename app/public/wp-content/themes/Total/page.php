<?php
/**
 * The template for displaying pages | The theme will actually use singular.php for pages - this file is to prevent issues
 * with improperly coded plugins.
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

				<?php
				// Display singular content unless there is a custom elementor template defined
				if ( ! wpex_theme_do_location( 'single' ) ) :

					while ( have_posts() ) : the_post();

						wpex_get_template_part( 'page_single_blocks' );

					endwhile;

				endif; ?>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>