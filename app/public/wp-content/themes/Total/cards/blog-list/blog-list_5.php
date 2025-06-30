<?php
/**
 * Card: Blog List 5
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Card Inner
$output .= '<div class="wpex-card-inner wpex-flex wpex-items-center">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-w-33 wpex-flex-shrink-0 wpex-mr-20',
	) );

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-lg wpex-flex-grow',
	) );

$output .= '</div>';

return $output;