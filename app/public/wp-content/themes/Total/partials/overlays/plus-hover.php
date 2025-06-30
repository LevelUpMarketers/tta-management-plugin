<?php
/**
 * Plus Hover Overlay
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

<div class="overlay-plus-hover overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo wpex_overlay_speed( 'plus-hover' ); ?>" aria-hidden="true">
	<span class="overlay-bg wpex-bg-center wpex-bg-no-repeat wpex-bg-<?php echo wpex_overlay_bg( 'plus-hover' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'plus-hover' ); ?>"></span>
</div>