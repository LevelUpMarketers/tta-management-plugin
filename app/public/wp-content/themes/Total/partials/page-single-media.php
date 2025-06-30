<?php
/**
 * Page Media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_page_single_media_type();

if ( $media_type ) { ?>

	<div id="post-media" <?php wpex_page_single_media_class(); ?>><?php

		get_template_part( 'partials/page/page-single-' . $media_type );

	?></div>

<?php }