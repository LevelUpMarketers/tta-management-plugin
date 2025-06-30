<?php
/**
 * Portfolio single content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="portfolio-single-content" <?php wpex_portfolio_single_content_class(); ?><?php wpex_schema_markup( 'entry_content' ); ?>><?php

	the_content();

?></article>