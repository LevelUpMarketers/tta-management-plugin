<?php
/**
 * Video Icon Overlay
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

<div class="overlay-icon overlay-icon-video"><span>&#9658;</span></div>