<?php
/**
 * Card: Blog List 6
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$bk = $this->get_breakpoint();

// Inner
$output .= '<div class="wpex-card-inner wpex-' . $bk . '-flex wpex-bg-white wpex-border wpex-border-solid wpex-border-main">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-' . $bk . '-w-50 wpex-' . $bk . '-flex-shrink-0',
	) );

	// Details
	$output .= '<div class="wpex-card-details wpex-' . $bk . '-flex-grow wpex-p-25 wpex-last-mb-0">';

		// Terms
		$output .= $this->get_terms_list( array(
			'class'      => 'wpex-mb-15 wpex-font-bold wpex-text-xs wpex-uppercase wpex-tracking-wide',
			'term_class' => 'wpex-inline-block',
			'separator'  => '<span class="wpex-card-terms-list-sep wpex-inline-block wpex-mx-5">&#8725;</span>',
		) );

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-xl wpex-font-bold wpex-my-15',
			'link_class' => 'wpex-inherit-color-important',
		) );

		// Excerpt
		$output .= $this->get_excerpt( array(
			'class' => 'wpex-text-gray-600 wpex-my-15',
		) );

		// Date
		$output .= $this->get_date( array(
			'type'   => 'time_ago',
			'prefix' =>  esc_html__( 'Published', 'total' ) . ' ',
			'class'  => 'wpex-mt-20',
			'icon'   => 'ticon ticon-clock-o wpex-mr-5',
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;