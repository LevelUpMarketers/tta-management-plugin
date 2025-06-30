<?php
/**
 * Image Overlay: Title Center
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

// Get post data
$title = isset( $args['post_title'] ) ? $args['post_title'] : get_the_title();

// Title is required
if ( ! $title ) {
	return;
}

?>

<div class="overlay-title-center theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'title-center' ); ?> wpex-flex wpex-justify-center wpex-items-center wpex-text-center">
	<span class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'title-center' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'title-center', '50' ); ?>"></span>
	<div class="overlay-content wpex-relative wpex-text-white wpex-uppercase wpex-font-semibold wpex-p-15 wpex-tracking-widest">
		<?php echo apply_filters( 'wpex_overlay_content_title-center', '<span class="title">' . esc_html( $title ) . '</span>' ); ?>
	</div>
</div>