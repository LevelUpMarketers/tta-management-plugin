<?php
/**
 * Card: Portfolio 1
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Media
$output .= $this->get_media( array(
	'class' => 'wpex-mb-15',
) );

// Details
$output .= '<div class="wpex-card-details wpex-flex wpex-flex-wrap wpex-justify-between">';

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-uppercase wpex-font-bold',
	) );

	// Date
	$output .= $this->get_date( array(
		'type' => 'time_ago',
		'class' => 'wpex-text-gray-500 wpex-text-right',
	) );

$output .= '</div>';

return $output;