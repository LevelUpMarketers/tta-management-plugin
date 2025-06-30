<?php
/**
 * Title Date Hover Overlay
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
$date  = isset( $args['post_date'] ) ? $args['post_date'] : get_the_date();

// Get animation speed
$speed = wpex_overlay_speed( 'title-date-hover' );

?>

<div class="overlay-title-date-hover overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo intval( $speed ); ?> wpex-overflow-hidden wpex-flex wpex-items-center wpex-justify-center wpex-text-center">
	<div class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'title-date-hover' ); ?> wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'title-date-hover' ); ?>"></div>
	<div class="overlay-content overlay-scale wpex-relative wpex-text-white wpex-p-15 wpex-duration-<?php echo intval( $speed ); ?> wpex-transition-transform wpex-clr">
		<div class="overlay-title wpex-text-lg"><?php echo esc_html( $title ); ?></div>
		<div class="overlay-date wpex-opacity-80 wpex-italic"><?php echo esc_html( $date ); ?></div>
	</div>
</div>