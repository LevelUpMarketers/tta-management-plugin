<?php
/**
 * Portfolio entry thumbnail
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<a href="<?php the_permalink(); ?>" title="<?php wpex_esc_title(); ?>" class="portfolio-entry-media-link">
	<?php wpex_portfolio_entry_thumbnail(); ?>
	<?php wpex_entry_media_after( 'portfolio' ); ?>
	<?php wpex_overlay( 'inside_link' ); ?>
</a>

<?php wpex_overlay( 'outside_link' ); ?>