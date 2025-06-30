<?php
/**
 * Card: Blog 12
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-bg-white wpex-shadow wpex-rounded-sm">';

	// Media
	$output .= $this->get_media();

	// Details
	$output .= '<div class="wpex-card-details wpex-p-25 wpex-last-mb-0">';

		// Terms
		$output .= $this->get_terms_list( array(
			'class' => 'wpex-font-semibold wpex-leading-normal wpex-mb-15 wpex-last-mr-0',
			'term_class' => 'wpex-inline-block wpex-bg-accent wpex-text-white wpex-hover-opacity-80 wpex-no-underline wpex-mr-5 wpex-mb-5 wpex-px-10 wpex-py-5 wpex-rounded-full wpex-text-xs',
		) );

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-lg wpex-font-bold wpex-mb-15',
			'link_class' => 'wpex-inherit-color-important',
		) );

		// Excerpt
		$output .= $this->get_excerpt( array(
			'class' => 'wpex-mb-30',
		) );

		// Date
		$output .= $this->get_date( array(
			'type'  => 'published',
			'class' => 'wpex-text-xs wpex-uppercase wpex-font-semibold',
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;