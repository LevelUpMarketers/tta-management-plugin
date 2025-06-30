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

$output .= '<div class="wpex-card-inner wpex-flex wpex-items-center">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-w-33 wpex-flex-shrink-0 wpex-mr-20',
	) );

	$output .= '<div class="wpex-card-details wpex-flex-grow wpex-last-mb-0">';

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-lg wpex-mb-5',
		) );

		// Author
		$output .= $this->get_author( array(
			'class' => 'wpex-text-gray-500 wpex-font-medium wpex-child-inherit-color',
			'prefix' => esc_html( 'By', 'total' ) . ' ',
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;