<?php
/**
 * Staff entry media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_staff_entry_media_type();

if ( $media_type ) { ?>

	<div <?php wpex_staff_entry_media_class(); ?>><?php

		get_template_part( 'partials/staff/staff-entry-' . $media_type );

	?></div>

<?php }

