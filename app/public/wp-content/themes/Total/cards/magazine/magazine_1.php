<?php
/**
 * Card: Magazine 1
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Media
$output .= $this->get_media( array(
	'class' => 'wpex-mb-10',
) );

// Details
$output .= '<div class="wpex-card-details wpex-last-mb-0">';

	// Header
	$output .= '<div class="wpex-card-header wpex-flex wpex-flex-wrap wpex-mb-10 wpex-text-gray wpex-text-xs wpex-uppercase wpex-font-medium">';

		// Primary Term
		$output .= $this->get_primary_term( array(
			'link'       => true,
			'class'      => 'wpex-inline-block',
			'term_class' => 'wpex-inherit-color wpex-no-underline wpex-inline-block wpex-border-0 wpex-border-b-2 wpex-border-solid wpex-border-gray-400 wpex-hover-border-accent wpex-hover-text-accent',
			'after'      => '<span class="wpex-mx-5">&middot;</span>'
		) );

		// Date
		$output .= $this->get_date( array(
			'class' => 'wpex-inline-block',
		) );

	$output .= '</div>';

	// Title
	$output .= $this->get_title( array(
		'link'  => true,
		'class' => 'wpex-heading wpex-text-xl wpex-font-bold wpex-mb-5',
	) );

	// Excerpt
	$output .= $this->get_excerpt();

$output .= '</div>';

return $output;