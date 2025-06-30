<?php
/**
 * Staff entry thumbnail
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$has_links = get_theme_mod( 'staff_links_enable', true );

?>

<?php
// Open link around staff members if enabled
if ( $has_links ) { ?>

	<a href="<?php wpex_permalink(); ?>" title="<?php wpex_esc_title(); ?>" class="staff-entry-media-link">

<?php } ?>

	<?php wpex_staff_entry_thumbnail(); ?>

	<?php wpex_entry_media_after( 'staff' ); ?>

	<?php wpex_overlay( 'inside_link' ); ?>

<?php if ( $has_links ) { ?>

	</a>

<?php } ?>

<?php wpex_overlay( 'outside_link' ); ?>
