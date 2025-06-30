<?php
/**
 * CPT single media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_cpt_single_media_type();

if ( $media_type ) { ?>

	<div id="post-media" <?php wpex_cpt_single_media_class(); ?>><?php

		get_template_part( 'partials/cpt/cpt-single-' . $media_type );

	?></div>

<?php }