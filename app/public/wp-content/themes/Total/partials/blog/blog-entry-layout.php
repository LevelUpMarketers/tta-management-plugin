<?php
/**
 * Blog entry layout
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Quote format has it's own output
if ( 'quote' === get_post_format() ) :

	get_template_part( 'partials/blog/blog-entry-quote' );

// All other entry formats share the same output
else : ?>

	<article id="post-<?php the_ID(); ?>" <?php wpex_blog_entry_class(); ?>>

		<?php if ( ! wpex_blog_entry_card() ) { ?>

			<div <?php wpex_blog_entry_inner_class(); ?>>

				<?php
				$entry_style = wpex_blog_entry_style();
				$blocks = wpex_blog_entry_layout_blocks();

				// Thumbnail entry style uses different layout
				if ( 'thumbnail-entry-style' === $entry_style ) : ?>

					<?php get_template_part( 'partials/blog/blog-entry-media' ); ?>

					<div <?php wpex_blog_entry_content_class(); ?>>

						<?php
						// Loop through entry blocks
						foreach ( $blocks as $block ) :

							// Custom block output
							if ( is_callable( $block ) ) {
								call_user_func( $block );
							}

							// Display the entry title
							elseif ( 'title' === $block ) {

								get_template_part( 'partials/blog/blog-entry-title' );

							}

							// Display the entry meta
							elseif ( 'meta' === $block ) {

								get_template_part( 'partials/blog/blog-entry-meta' );

							}

							// Display the entry excerpt or content
							elseif ( 'excerpt_content' === $block ) {

								get_template_part( 'partials/blog/blog-entry-content' );

							}

							// Display the readmore button
							elseif ( 'readmore' === $block ) {

								if ( wpex_has_readmore() ) {

									get_template_part( 'partials/blog/blog-entry-readmore' );

								}

							}

							/* Display the social share  // Deprecated in v4.5.5
							elseif ( 'social_share' == $block ) {

								wpex_social_share();

							}*/

							// Custom Blocks
							else {

								get_template_part( 'partials/blog/blog-entry-' . $block );

							}

						// End block loop
						endforeach; ?>

					</div>

				<?php

				// Other entry styles
				else :

					// Loop through composer blocks and output layout
					foreach ( $blocks as $block ) :

						// Callable output
						if ( is_callable( $block ) ) {

							call_user_func( $block );

						}

						// Featured media
						elseif ( 'featured_media' === $block ) {

							get_template_part( 'partials/blog/blog-entry-media' );

						}

						// Display the entry header
						elseif ( 'title' === $block ) {

							get_template_part( 'partials/blog/blog-entry-title' );

						}

						// Display the entry meta
						elseif ( 'meta' === $block ) {

							get_template_part( 'partials/blog/blog-entry-meta' );

						}

						// Display the entry excerpt or content
						elseif ( 'excerpt_content' === $block ) {

							get_template_part( 'partials/blog/blog-entry-content' );

						}

						// Display the readmore button
						elseif ( 'readmore' === $block ) {

							if ( wpex_has_readmore() ) {

								get_template_part( 'partials/blog/blog-entry-readmore' );

							}

						}

						/* Display social links in entries // Deprecated in v4.5.5
						elseif ( 'social_share' == $block ) {
							wpex_social_share();
						} */

						// Custom Blocks
						else {

							get_template_part( 'partials/blog/blog-entry-' . $block );

						}

					// End block loop
					endforeach;

				// End block check
				endif; ?>

			</div>

			<?php wpex_blog_entry_divider(); ?>

		<?php } ?>

	</article>

<?php endif; ?>