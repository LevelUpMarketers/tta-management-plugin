<?php
/**
 * Image Overlay: Title Push Up Overlay
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0.6
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

<div class="overlay-title-push-up theme-overlay wpex-bg-<?php echo wpex_overlay_bg( 'title-push-up' ); ?> wpex-text-white wpex-text-center wpex-absolute wpex-inset-x-0 wpex-py-15 wpex-px-20 wpex-w-100 wpex-text-md wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'title-push-up' ); ?>"><?php
	echo apply_filters( 'wpex_overlay_content_title-push-up', '<span class="title">' . esc_html( $title ) . '</span>' );
?></div>