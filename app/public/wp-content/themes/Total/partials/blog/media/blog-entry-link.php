<?php
/**
 * Blog entry link format
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$overlay = wpex_blog_entry_overlay_style();

?>

<a href="<?php wpex_permalink(); ?>" title="<?php wpex_esc_title(); ?>" class="blog-entry-media-link">
	<?php echo wpex_get_blog_entry_thumbnail(); ?>
	<?php wpex_entry_media_after( 'blog' ); ?>
	<?php wpex_overlay( 'inside_link', $overlay ); ?>
</a>

<?php wpex_overlay( 'outside_link', $overlay ); ?>