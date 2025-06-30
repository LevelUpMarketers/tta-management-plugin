<?php
/**
 * Magnifying Hover Overlay
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

<div class="magnifying-hover theme-overlay overlay-hide wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'magnifying-hover' ); ?> wpex-text-white wpex-text-2xl wpex-flex wpex-items-center wpex-justify-center" aria-hidden="true">
	<span class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'magnifying-hover' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'magnifying-hover' ); ?>"></span>
	<span class="wpex-relative ticon ticon-search"></span>
</div>