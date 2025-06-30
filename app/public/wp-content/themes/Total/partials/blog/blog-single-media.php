<?php
/**
 * Blog single media
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$media_type = wpex_blog_single_media_type();

if ( $media_type ) { ?>

	<div id="post-media" <?php wpex_blog_single_media_class(); ?>><?php

		if ( 'thumbnail' == $media_type ) {

			get_template_part( 'partials/blog/media/blog-single' ); // fallback support for pre v5

		} else {

			get_template_part( 'partials/blog/media/blog-single-' . $media_type );

		}

	?></div>

<?php }
