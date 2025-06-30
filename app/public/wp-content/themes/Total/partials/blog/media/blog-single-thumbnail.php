<?php
/**
 * Blog single thumbnail
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! has_post_thumbnail() ) {
	return;
}

?>

<?php
// Image with lightbox link
if ( wpex_has_blog_single_thumbnail_lightbox() ) :

	wpex_enqueue_lightbox_scripts();

	?>

	<a href="<?php echo wpex_get_lightbox_image(); ?>" title="<?php esc_attr_e( 'Enlarge Image', 'total' ); ?>" class="wpex-lightbox<?php wpex_entry_image_animation_classes(); ?>"><?php wpex_blog_post_thumbnail(); ?></a>

<?php
// No lightbox
else : ?>

	<?php wpex_blog_post_thumbnail(); ?>

<?php endif; ?>

<?php wpex_blog_single_thumbnail_caption(); ?>