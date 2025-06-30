<?php
/**
 * Staff single position
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

$position = get_post_meta( get_the_ID(), 'wpex_staff_position', true );

if ( ! $position ) {
	return;
}

?>

<div id="staff-single-position" <?php wpex_staff_single_position_class(); ?>><?php echo wp_kses_post( $position ); ?></div>