<?php
/**
 * The template for displaying Search Results pages
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

				<?php
				// Display default theme layout if elementor template not defined
				if ( ! wpex_theme_do_location( 'archive' ) ) :

					// Display posts if there are in fact posts to display
					if ( have_posts() ) :

						// Get index loop type
						$loop_type = wpex_get_index_loop_type();

						// Get loop top
						get_template_part( 'partials/loop/loop-top', $loop_type );

							// Set the loop counter which is used for clearing floats
							wpex_set_loop_counter();

							// Loop through posts
							while ( have_posts() ) : the_post();

								// Add to running count
								wpex_increment_loop_running_count();

								// Before entry hook
								wpex_hook_archive_loop_before_entry();

								// Get content template part (entry content)
								get_template_part( 'partials/loop/loop', $loop_type );

								// After entry hook
								wpex_hook_archive_loop_after_entry();

							// End loop
							endwhile;

						// Get loop bottom
						get_template_part( 'partials/loop/loop-bottom', $loop_type );

						// Display pagination
						wpex_loop_pagination( $loop_type );

						// Reset query vars
						wpex_reset_loop_query_vars();

						?>

					<?php
					// Show message because there aren't any posts
					else :

						get_template_part( 'partials/no-posts-found' );

					endif;

				endif; ?>

				<?php wpex_hook_content_bottom(); ?>

			</div>

		<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>