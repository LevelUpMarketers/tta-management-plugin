<?php
/**
 * Testimonials entry content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div <?php wpex_testimonials_entry_content_class(); ?>>
	<span class="testimonial-caret"></span>
	<?php if ( get_theme_mod( 'testimonial_entry_title', false ) ) : ?>
		<h2 <?php wpex_testimonials_entry_title_class(); ?>><?php the_title(); ?></h2>
	<?php endif; ?>
	<div class="testimonial-entry-text wpex-last-mb-0 wpex-clr"<?php wpex_schema_markup( 'entry_content' ); ?>><?php the_content(); ?></div>
</div>