<?php
/**
 * Portfolio single media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_portfolio_single_media_type();

if ( $media_type ) { ?>

	<div id="portfolio-single-media" <?php wpex_portfolio_single_media_class(); ?>><?php

		get_template_part( 'partials/portfolio/portfolio-single-' . $media_type );

	?></div>

<?php }
