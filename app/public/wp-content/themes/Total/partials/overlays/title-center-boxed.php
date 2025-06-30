<?php
/**
 * Image Overlay: Title Center Boxed
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

<div class="overlay-title-center-boxed theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'title-center-boxed' ); ?> wpex-flex wpex-justify-center wpex-items-center">
	<div class="title wpex-bg-white wpex-m-25 wpex-p-25 wpex-text-md wpex-font-semibold wpex-text-black wpex-text-center"><?php
			echo apply_filters( 'wpex_overlay_content_title-center-boxed', esc_html( $title ) );
		?></div>
</div>