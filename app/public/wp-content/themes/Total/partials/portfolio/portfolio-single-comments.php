<?php
/**
 * Portfolio single comments
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! comments_open() ) {
	return;
} ?>

<div id="portfolio-single-comments" <?php wpex_portfolio_single_comments_class(); ?>><?php

	comments_template();

?></div>