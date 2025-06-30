<?php
/**
 * Card: Portfolio 3
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
$output .= '<div class="wpex-card-details wpex-flex wpex-items-center">';

	// Avatar
	$output .= $this->get_avatar( array(
		'size'        => 32,
		'class'       => 'wpex-flex-shrink-0 wpex-mr-15',
		'image_class' => 'wpex-rounded-full wpex-align-middle',
	) );

	$output .= '<div class="wpex-card-details wpex-flex-grow">';

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-md',
		) );

		// Terms
		$output .= $this->get_terms_list( array(
			'class'      => 'wpex-text-gray-600',
			'separator'  => ' &middot; ',
			'term_class' => 'wpex-inherit-color',
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;