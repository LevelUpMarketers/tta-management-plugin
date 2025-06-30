<?php
/**
 * The template for displaying 404 pages (Not Found).
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

			<main id="content" class="site-content wpex-clr">

				<?php wpex_hook_content_top(); ?>

				<?php if ( ! wpex_theme_do_location( 'single' ) ) : ?>

					<article class="entry wpex-clr">

						<?php
						// Check custom page content
						if ( get_theme_mod( 'error_page_content_id' ) && $id = wpex_get_current_post_id() ) :

							$post    = get_post( $id ); // get post
							$content = wpex_parse_vc_content( $post->post_content ); // remove weird p tags and extra code
							$content = wp_kses_post( $content ); // security
							echo do_shortcode( $content ); // parse shortcodes and echo

						else :

							// Get error text
							$error_text = trim( wpex_get_translated_theme_mod( 'error_page_text' ) );

							// Display custom text
							if ( $error_text )  : ?>

								<div class="custom-error404-content wpex-clr"><?php echo wpex_the_content( $error_text, 'error404' ); ?></div>

							<?php
							// Display default text
							else : ?>

								<div class="error404-content wpex-text-center wpex-py-30 wpex-clr">

									<h1 class="error404-content-heading wpex-m-0 wpex-mb-10 wpex-text-6xl"><?php esc_html_e( 'Sorry, this page could not be found.', 'total' ); ?></h1>
									<div class="error404-content-text wpex-text-md wpex-last-mb-0"><?php esc_html_e( 'The page you are looking for doesn\'t exist, no longer exists or has been moved.', 'total' ); ?></div>

								</div>

							<?php endif; ?>

						<?php endif; ?>

					</article>

				<?php endif; ?>

				<?php wpex_hook_content_bottom(); ?>

			</main>

			<?php wpex_hook_content_after(); ?>

		</div>

		<?php wpex_hook_primary_after(); ?>

	</div>

<?php get_footer(); ?>