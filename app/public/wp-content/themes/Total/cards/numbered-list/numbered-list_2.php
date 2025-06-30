<?php
/**
 * Card: Numbered List 2
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Inner
$output .= '<div class="wpex-card-inner wpex-flex">';

	// Number
	$output .= $this->get_number( array(
		'class'  => 'wpex-flex-shrink-0 wpex-mr-20 wpex-text-accent wpex-text-3xl wpex-font-bold',
		'suffix' => '.',
	) );

	// Details
	$output .= '<div class="wpex-card-details wpex-flex-grow">';

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-lg',
		) );

		// Excerpt
		$output .= $this->get_excerpt( array(
			'class'  => 'wpex-mt-5 wpex-text-gray-600',
			'length' => 10,
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;