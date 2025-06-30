<?php
/**
 * Template Name: Blog
 *
 * @package Total WordPress Theme
 * @subpackage Templates
 * @version 5.0
 *
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

				<?php while ( have_posts() ) : the_post(); ?>

					<?php
					// Display certain page blocks
					$blocks = wpex_single_blocks();

					if ( is_array( $blocks ) ) :

						foreach( $blocks as $block ) {

							switch ( $block ) {
								case 'media':
									get_template_part( 'partials/page-single-media' );
								break;
								case 'title':
									get_template_part( 'partials/page-single-title' );
								break;
							}

						}

					endif; ?>

					<?php
					// Always display content if there is some
					get_template_part( 'partials/page-single-content' ); ?>

				<?php endwhile; ?>

				<?php
				global $post, $paged, $more;
				$more = 0;
				if ( get_query_var( 'paged' ) ) {
					$paged = get_query_var( 'paged' );
				} else if ( get_query_var( 'page' ) ) {
					$paged = get_query_var( 'page' );
				} else {
					$paged = 1;
				}

				// Query posts
				$wp_query = new WP_Query( array(
					'post_type'        => 'post',
					'paged'            => $paged,
					'category__not_in' => ( $exclude = wpex_blog_exclude_categories() ) ? $exclude : null,
				) );

				if ( $wp_query->posts ) :

					// Get index loop type
					$loop_type = 'blog';

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

					// Return pagination
					wpex_loop_pagination( $loop_type );

				endif;

				?>

				<?php wp_reset_postdata(); wp_reset_query(); ?>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>