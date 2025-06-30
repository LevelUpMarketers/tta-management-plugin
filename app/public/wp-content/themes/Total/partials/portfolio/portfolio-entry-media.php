<?php
/**
 * Portfolio entry media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_portfolio_entry_media_type();

if ( $media_type ) { ?>

	<div <?php wpex_portfolio_entry_media_class(); ?>><?php

		get_template_part( 'partials/portfolio/portfolio-entry-' . $media_type );

	?></div>

<?php }
