<?php
/**
 * The template for displaying all pages, single posts and attachments
 *
 * @package Total WordPress Theme
 * @subpackage Templates
 * @version 5.0.6
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
				// Display singular content unless there is a custom template defined
				if ( ! wpex_theme_do_location( 'single' ) ) :

					// Start loop
					while ( have_posts() ) : the_post();

						// Single Page
						if ( is_singular( 'page' ) ) {

							wpex_get_template_part( 'page_single_blocks' );

						}

						// Single posts
						elseif ( is_singular( 'post' ) ) {

							wpex_get_template_part( 'blog_single_blocks' );

						}

						// Portfolio Posts
						elseif ( is_singular( 'portfolio' ) && wpex_is_total_portfolio_enabled() ) {

							wpex_get_template_part( 'portfolio_single_blocks' );

						}

						// Staff Posts
						elseif ( is_singular( 'staff' ) && wpex_is_total_staff_enabled() ) {

							wpex_get_template_part( 'staff_single_blocks' );

						}

						// Testimonials Posts
						elseif ( is_singular( 'testimonials' ) && wpex_is_total_testimonials_enabled() ) {

							wpex_get_template_part( 'testimonials_single_blocks' );

						}

						/**
						 * All other post types.
						 *
						 * When customizing your custom post types it's best to create
						 * a new singular-{post_type}.php file to prevent any possible conflicts in the future
						 * rather then altering the template part or create a dynamic template.
						 *
						 * @link https://wpexplorer-themes.com/total/docs/custom-post-type-singular-template/
						 */
						else {

							// Prevent issues with custom types named the same as core partial files.
							// @todo remove the $post_type paramater from wpex_get_template_part.
							$post_type = get_post_type();

							if ( in_array( $post_type, array( 'audio', 'video', 'gallery', 'content', 'comments', 'media', 'meta', 'related', 'share', 'title' ) ) ) {
								$post_type = null;
							}

							wpex_get_template_part( 'cpt_single_blocks', $post_type );

						}

					endwhile; ?>

				<?php endif; ?>

				<?php wpex_hook_content_bottom(); ?>

			</div>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>