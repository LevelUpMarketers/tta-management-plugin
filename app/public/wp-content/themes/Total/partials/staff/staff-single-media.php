<?php
/**
 * Staff single media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_staff_single_media_type();

if ( $media_type ) { ?>

	<div id="staff-single-media" <?php wpex_staff_single_media_class(); ?>><?php

		get_template_part( 'partials/staff/staff-single-' . $media_type );

	?></div>

<?php }