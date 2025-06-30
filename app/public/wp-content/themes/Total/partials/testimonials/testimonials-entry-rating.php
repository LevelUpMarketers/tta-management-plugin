<?php
/**
 * Outputs the testimonial entry rating
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wpex_has_testimonial_rating() ) {
	return;
}

?>

<div <?php wpex_testimonials_entry_rating_class(); ?>><?php wpex_testimonial_rating(); ?></div>