<?php
/**
 * Card: Staff 6
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Inner
$output .= '<div class="wpex-card-inner wpex-bg-white wpex-text-center">';

	// Thumbnail
	$output .= $this->get_thumbnail();

	// Details
	$output .= '<div class="wpex-card-details wpex-p-25">';

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-lg wpex-text-gray-800 wpex-font-normal wpex-leading-snug wpex-child-inherit-color',
		) );

		// Generate divider html
		$divider = $this->get_empty_element( array(
			'html_tag' => 'span',
			'class'    => 'wpex-card-divider wpex-inline-block wpex-bg-accent wpex-my-5',
			'css'      => 'width:30px;height:2px;',
		) );

		// Position
		$output .= $this->get_element( array(
			'content' => wpex_get_staff_member_position(),
			'class'   => 'wpex-card-staff-member-position wpex-text-xs wpex-uppercase wpex-leading-snug wpex-tracking-wide wpex-text-gray-500',
			'before'  => $divider, // divider should only display if a position exists.
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;