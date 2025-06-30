<?php
/**
 * Card: Product 2
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-relative wpex-text-center wpex-p-15 wpex-bg-white wpex-border wpex-border-solid wpex-border-gray-200">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-mb-15',
	) );

	// Details
	$output .= '<div class="wpex-card-details wpex-last-mb-0">';

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-md wpex-mb-5',
			'link_class' => 'wpex-inherit-color-important',
		) );

		// Price
		$output .= $this->get_price( array(
			'class' => 'wpex-text-accent wpex-font-semibold',
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;