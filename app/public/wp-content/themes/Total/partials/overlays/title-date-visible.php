<?php
/**
 * Title Date Visibile Overlay
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

?>

<div class="overlay-title-date-visible theme-overlay wpex-absolute wpex-inset-0 wpex-flex wpex-items-center wpex-justify-center wpex-text-center">
	<div class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'title-date-visible' ); ?> wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'title-date-visible' ); ?>"></div>
	<div class="overlay-content wpex-relative wpex-text-white wpex-p-15 wpex-clr">
		<div class="overlay-title wpex-text-lg"><?php echo esc_html( $title ); ?></div>
		<div class="overlay-date wpex-opacity-80 wpex-italic"><?php echo esc_html( $date ); ?></div>
	</div>
</div>