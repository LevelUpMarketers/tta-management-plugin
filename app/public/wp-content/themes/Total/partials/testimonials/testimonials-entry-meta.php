<?php
/**
 * Outputs the testimonial entry meta data
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_testimonials_entry_meta_class(); ?>><?php

	// Display rating author
	get_template_part( 'partials/testimonials/testimonials-entry-author' );

	// Display testimonial company
	get_template_part( 'partials/testimonials/testimonials-entry-company' );

	// Display testimonial star rating
	get_template_part( 'partials/testimonials/testimonials-entry-rating' );

?></div>