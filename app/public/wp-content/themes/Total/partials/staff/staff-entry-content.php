<?php
/**
 * Staff entry content
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wpex_has_staff_entry_content() ) {
	return;
}

?>

<div <?php wpex_staff_entry_content_class(); ?>>
	<?php get_template_part( 'partials/staff/staff-entry-title' ); ?>
	<?php get_template_part( 'partials/staff/staff-entry-position' ); ?>
	<?php get_template_part( 'partials/staff/staff-entry-excerpt' ); ?>
	<?php get_template_part( 'partials/staff/staff-entry-social' ); ?>
</div>