<?php
/**
 * Testimonials entry
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="post-<?php the_ID(); ?>" <?php wpex_testimonials_entry_class(); ?>>
	<?php if ( ! wpex_testimonials_entry_card() ) { ?>
		<?php get_template_part( 'partials/testimonials/testimonials-entry-content' ); ?>
		<div <?php wpex_testimonials_entry_bottom_class(); ?>>
			<?php get_template_part( 'partials/testimonials/testimonials-entry-avatar' ); ?>
			<?php get_template_part( 'partials/testimonials/testimonials-entry-meta' ); ?>
		</div>
	<?php } ?>
</article>