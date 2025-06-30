<?php
/**
 * Staff entry title
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<h2 <?php wpex_staff_entry_title_class(); ?>><?php

	// Display staff title with links
	if ( get_theme_mod( 'staff_links_enable', true ) ) : ?>

		<a href="<?php wpex_permalink(); ?>"><?php the_title(); ?></a>

	<?php
	// Display simple title without links
	else :

		the_title();

	endif;

?></h2>