<?php
/**
 * Categories + Title Bottom Visible
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Only used for inside position
if ( 'outside_link' != $position ) {
	return;
}

// Get category taxonomy for current post type
$taxonomy = wpex_get_post_type_cat_tax();

// Get post title
$title = isset( $args['post_title'] ) ? $args['post_title'] : get_the_title(); ?>

<div class="overlay-cats-title-btm-v theme-overlay wpex-absolute wpex-bottom-0 wpex-inset-x-0 wpex-py-10 wpex-px-20 wpex-text-center wpex-text-white">
	<span class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'categories-title-bottom-visible' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'categories-title-bottom-visible', '60' ); ?>"></span>
	<div class="overlay-content wpex-relative">
		<?php if ( $taxonomy ) {
			wpex_list_post_terms( array(
				'taxonomy' => $taxonomy,
				'before'   => '<div class="overlay-cats-title-btm-v-cats wpex-italic wpex-text-sm wpex-opacity-80 wpex-child-inherit-color wpex-clr">',
				'after'    => '</div>',
				'instance' => 'overlay_categories_title-bottom_visible',
			) );
		} ?>
		<a href="<?php the_permalink(); ?>" class="overlay-cats-title-btm-v-title wpex-inherit-color wpex-font-semibold wpex-text-md wpex-no-underline"><?php echo esc_html( $title ); ?></a>
	</div>
</div>