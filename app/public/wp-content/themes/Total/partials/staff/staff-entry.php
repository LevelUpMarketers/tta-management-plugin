<?php
/**
 * Staff entry
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<article id="post-<?php the_ID(); ?>" <?php wpex_staff_entry_class(); ?>>
	<?php if ( ! wpex_staff_entry_card() ) { ?>
		<div <?php wpex_staff_entry_inner_class(); ?>>
			<?php get_template_part( 'partials/staff/staff-entry-media' ); ?>
			<?php get_template_part( 'partials/staff/staff-entry-content' ); ?>
		</div>
	<?php } ?>
</article>