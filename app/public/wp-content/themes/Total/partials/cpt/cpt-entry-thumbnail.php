<?php
/**
 * CTP entry thumbnail
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 *
 */

defined( 'ABSPATH' ) || exit;

$overlay = wpex_cpt_entry_overlay_style();

?>

<a href="<?php wpex_permalink(); ?>" title="<?php wpex_esc_title(); ?>" class="cpt-entry-media-link<?php wpex_entry_image_animation_classes(); ?>">
	<?php wpex_cpt_entry_thumbnail(); ?>
	<?php wpex_entry_media_after( get_post_type() . '_entry' ); ?>
	<?php wpex_overlay( 'inside_link', $overlay ); ?>
</a>

<?php wpex_overlay( 'outside_link', $overlay ); ?>