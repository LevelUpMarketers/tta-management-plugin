<?php
/**
 * Blog entry media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_blog_entry_media_type();

if ( $media_type ) { ?>

	<div <?php wpex_blog_entry_media_class(); ?>><?php

		if ( 'thumbnail' == $media_type ) {

			get_template_part( 'partials/blog/media/blog-entry' ); // fallback support for pre v5

		} else {

			get_template_part( 'partials/blog/media/blog-entry-' . $media_type );

		}

	?></div>

<?php }