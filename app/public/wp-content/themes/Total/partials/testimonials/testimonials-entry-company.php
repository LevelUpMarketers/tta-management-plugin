<?php
/**
 * Testimonials entry company
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wpex_has_testimonial_company() ) {
	return;
}

?>

<?php if ( wpex_has_testimonial_company_link() ) : ?>

	<a href="<?php echo wpex_get_testimonial_company_url(); ?>" <?php wpex_testimonials_entry_company_class(); ?><?php wpex_testimonials_entry_company_link_target(); ?>><?php echo wp_kses_post( wpex_get_testimonial_company() ); ?></a>

<?php else : ?>

	<span <?php wpex_testimonials_entry_company_class(); ?>><?php echo wp_kses_post( wpex_get_testimonial_company() ); ?></span>

<?php endif; ?>