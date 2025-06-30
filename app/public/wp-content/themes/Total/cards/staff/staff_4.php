<?php
/**
 * Card: Staff 4
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Inner
$output .= '<div class="wpex-card-inner wpex-text-center">';

	// Thumbnail
	$output .= $this->get_thumbnail( array(
		'class' => 'wpex-mb-15 wpex-rounded-full-tl wpex-rounded-full-bl wpex-rounded-full-br',
		'image_class' => 'wpex-rounded-full-tl wpex-rounded-full-bl wpex-rounded-full-br'
	) );

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-md wpex-font-bold',
	) );

	// Position
	$output .= $this->get_element( array(
		'content' => wpex_get_staff_member_position(),
		'class'   => 'wpex-card-staff-member-position wpex-text-gray-500',
	) );

$output .= '</div>';

return $output;