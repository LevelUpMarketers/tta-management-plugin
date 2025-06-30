<?php
/**
 * Card: Blog List 11
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$bk = $this->get_breakpoint();

// Inner
$output .= '<div class="wpex-card-inner wpex-' . $bk . '-flex">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-mb-25 wpex-' . $bk . '-w-50 wpex-' . $bk . '-mr-25 wpex-' . $bk . '-mb-0 wpex-' . $bk . '-flex-shrink-0',
	) );

	// Details
	$output .= '<div class="wpex-card-details wpex-' . $bk . '-flex-grow wpex-last-mb-0">';

		// Primary term
		$output .= $this->get_primary_term( array(
			'class' => 'wpex-uppercase wpex-text-gray-600 wpex-text-xs wpex-font-semibold wpex-tracking-wide wpex-mb-5',
			'term_class' => 'wpex-inherit-color',
		) );

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-xl wpex-mb-15',
		) );

		// Excerpt
		$output .= $this->get_excerpt();

	$output .= '</div>';

$output .= '</div>';

return $output;