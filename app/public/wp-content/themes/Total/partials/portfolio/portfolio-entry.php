<?php
/**
 * Portfolio entry
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="post-<?php the_ID(); ?>" <?php wpex_portfolio_entry_class(); ?>>
	<?php if ( ! wpex_portfolio_entry_card() ) { ?>
		<div <?php wpex_portfolio_entry_inner_class(); ?>>
			<?php get_template_part( 'partials/portfolio/portfolio-entry-media' ); ?>
			<?php get_template_part( 'partials/portfolio/portfolio-entry-content' ); ?>
		</div>
	<?php } ?>
</article>