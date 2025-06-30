<?php
/**
 * Card: Staff 3
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Thumbnail
$output .= $this->get_thumbnail( array(
	'class' => 'wpex-rounded-t',
	'image_class' => 'wpex-rounded-t',
) );

// Details
$output .= '<div class="wpex-card-details wpex-bg-white wpex-p-25 wpex-last-mb-0">';

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-lg wpex-mb-5',
	) );

	// Position
	$output .= $this->get_element( array(
		'content' => wpex_get_staff_member_position(),
		'class'   => 'wpex-card-staff-member-position wpex-text-gray-600 wpex-font-bold',
	) );

	// Excerpt
	$output .= $this->get_excerpt( array(
		'length' => 15,
		'class'  => 'wpex-my-10 wpex-text-gray-600',
	) );

	// Social Links
	$output .= $this->get_element( array(
		'content' => wpex_get_staff_social( array(
			'show_icons' => false,
			'spacing'    => '10',
		) ),
		'class'   => 'wpex-mt-15',
	) );

$output .= '</div>';

return $output;