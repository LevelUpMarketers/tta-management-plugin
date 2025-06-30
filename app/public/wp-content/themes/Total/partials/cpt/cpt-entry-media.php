<?php
/**
 * CTP entry media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_cpt_entry_media_type();

if ( $media_type ) { ?>

	<div <?php wpex_cpt_entry_media_class(); ?>><?php

		get_template_part( 'partials/cpt/cpt-entry-' . $media_type );

	?></div>

<?php }