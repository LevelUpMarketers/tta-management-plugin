<?php
/**
 * Testimonials entry media/avatar
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! has_post_thumbnail() ) {
	return;
}

?>

<div <?php wpex_testimonials_entry_media_class(); ?>><?php

	wpex_testimonials_entry_thumbnail();

?></div>