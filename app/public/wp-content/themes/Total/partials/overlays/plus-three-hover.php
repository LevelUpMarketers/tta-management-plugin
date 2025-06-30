<?php
/**
 * Plus Three Hover Overlay
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

?>

<div class="overlay-plus-three-hover overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'plus-three-hover' ); ?> wpex-text-accent wpex-text-6xl wpex-flex wpex-items-center wpex-justify-center" aria-hidden="true">
	<span class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'plus-three-hover', 'black' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'plus-three-hover', '70' ); ?>"></span>
	<span class="overlay-content overlay-transform wpex-relative wpex-leading-none wpex-translate-y-50 wpex-transition-all wpex-duration-300 ticon ticon-plus-circle"></span>
</div>