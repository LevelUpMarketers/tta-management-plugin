<?php
/**
 * Portfolio single header/title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<header id="portfolio-single-header" <?php wpex_portfolio_single_header_class(); ?>>
	<h1 id="portfolio-single-title" <?php wpex_portfolio_single_title_class(); ?>><?php the_title(); ?></h1>
</header>