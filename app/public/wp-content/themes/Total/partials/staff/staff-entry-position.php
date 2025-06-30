<?php
/**
 * Staff entry position
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( get_theme_mod( 'staff_entry_position', true ) && wpex_has_staff_member_position() ) : ?>

	<div <?php wpex_staff_entry_position_class(); ?>><?php

		echo wp_kses_post( wpex_get_staff_member_position() );

	?></div>

<?php endif; ?>