<?php
/**
 * Card: Blog 4
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-border wpex-border-solid wpex-border-main wpex-bg-white">';

	// Media
	$output .= $this->get_media();

	// Details
	$output .= '<div class="wpex-card-details wpex-p-30 wpex-last-mb-0">';

		// Terms
		$output .= $this->get_terms_list( array(
			'class'      => 'wpex-text-accent wpex-child-inherit-color wpex-mb-10 wpex-font-bold wpex-text-xs wpex-uppercase wpex-tracking-wide',
			'term_class' => 'wpex-inline-block',
			'separator'  => '<span class="wpex-card-terms-list-sep wpex-inline-block wpex-mx-5">|</span>',
		) );

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-2xl wpex-font-light wpex-mb-10',
			'link_class' => 'wpex-inherit-color-important',
		) );

		// Author
		$output .= $this->get_author( array(
			'class' => 'wpex-mb-20 wpex-font-semibold wpex-text-gray-600 wpex-child-inherit-color',
			'prefix' => esc_html__( 'By', 'total' ) . ' ',
		) );

		// Excerpt
		$output .= $this->get_excerpt();

	$output .= '</div>';

$output .= '</div>';

return $output;