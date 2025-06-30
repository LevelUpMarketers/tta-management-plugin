<?php
/**
 * Portfolio entry content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wpex_has_portfolio_entry_content() ) {
	return;
}

?>

<div <?php wpex_portfolio_entry_content_class(); ?>>
	<?php get_template_part( 'partials/portfolio/portfolio-entry-title' ); ?>
	<?php get_template_part( 'partials/portfolio/portfolio-entry-excerpt' ); ?>
</div>