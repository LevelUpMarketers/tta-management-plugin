<?php
/**
 * Plus Two Hover Overlay
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

<div class="overlay-plus-two-hover overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'plus-two-hover' ); ?> wpex-text-white wpex-text-2xl wpex-flex wpex-items-center wpex-justify-center" aria-hidden="true">
	<span class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'plus-two-hover' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'plus-two-hover' ); ?>"></span>
	<span class="wpex-relative ticon ticon-plus"></span>
</div>