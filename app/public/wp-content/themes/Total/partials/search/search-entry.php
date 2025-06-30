<?php
/**
 * Search entry layout
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="post-<?php the_ID(); ?>" <?php wpex_search_entry_class(); ?>>
	<?php if ( ! wpex_search_entry_card() ) { ?>
		<div <?php wpex_search_entry_inner_class(); ?>>
			<?php if ( apply_filters( 'wpex_search_has_post_thumbnail', has_post_thumbnail() ) ) : ?>
				<?php get_template_part( 'partials/search/search-entry-thumbnail' ); ?>
			<?php endif; ?>
			<div <?php wpex_search_entry_content_class(); ?>>
				<?php get_template_part( 'partials/search/search-entry-header' ); ?>
				<?php get_template_part( 'partials/search/search-entry-excerpt' ); ?>
			</div>
		</div>
		<?php wpex_search_entry_divider(); ?>
	<?php } ?>
</article>