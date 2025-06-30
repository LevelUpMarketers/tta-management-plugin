<?php
/**
 * Card: Simple 2
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Inner
$output .= '<div class="wpex-card-inner wpex-p-25 wpex-border wpex-border-main wpex-border-solid wpex-last-mb-0">';

	// Date
	$output .= $this->get_date( array(
		'class' => 'wpex-mb-5 wpex-text-gray-600',
	) );

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-lg wpex-mb-15',
	) );

	// Excerpt
	$output .= $this->get_excerpt();

$output .= '</div>';

return $output;