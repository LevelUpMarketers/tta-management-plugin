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

$bk = $this->get_breakpoint();

// Inner
$output .= '<div class="wpex-card-inner wpex-' . $bk . '-flex">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-mb-10 wpex-' . $bk . '-w-50 wpex-' . $bk . '-flex-shrink-0 wpex-' . $bk . '-mr-25 wpex-' . $bk . '-mb-0',
	) );

	// Details
	$output .= '<div class="wpex-card-details wpex-' . $bk . '-flex-grow">';

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

$output .= '</div>';

return $output;