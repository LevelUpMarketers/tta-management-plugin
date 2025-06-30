<?php
/**
 * Title Category Visible Overlay
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Only used for inside position
if ( 'inside_link' != $position ) {
	return;
}

?>

<div class="overlay-title-category-visible theme-overlay wpex-absolute wpex-inset-0 wpex-flex wpex-items-center wpex-justify-center wpex-text-center">

	<div class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'title-category-visible' ); ?> wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'title-category-visible' ); ?>"></div>

	<div class="overlay-content wpex-relative wpex-text-white wpex-p-15 wpex-clr">

		<div class="overlay-title wpex-text-lg"><?php
			$title = isset( $args['post_title'] ) ? $args['post_title'] : get_the_title();
			echo esc_html( $title );
		?></div>

		<?php if ( $taxonomy = wpex_get_post_type_cat_tax() ) {
			wpex_list_post_terms( array(
				'taxonomy'   => $taxonomy,
				'before'     => '<div class="overlay-terms wpex-opacity-80 wpex-italic">',
				'after'      => '</div>',
				'show_links' => false,
				'instance'   => 'overlay_title-category-visible',
			) );
		} ?>

	</div>

</div>