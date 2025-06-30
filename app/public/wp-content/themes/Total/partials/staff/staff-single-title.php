<?php
/**
 * Staff single title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

?>

<header id="staff-single-header" <?php wpex_staff_single_header_class(); ?>>
	<h1 id="staff-single-title" <?php wpex_staff_single_title_class(); ?>><?php the_title(); ?></h1>
	<?php if ( wpex_has_staff_single_title_position() ) {
		get_template_part( 'partials/staff/staff-single-position' );
	} ?>
</header>