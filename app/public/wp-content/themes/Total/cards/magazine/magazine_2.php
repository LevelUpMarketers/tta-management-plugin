<?php
/**
 * Card: Magazine 1
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Media
$output .= $this->get_media( array(
	'class'       => 'wpex-mb-20 wpex-rounded-lg',
	'image_class' => 'wpex-rounded-lg',
) );

// Details
$output .= '<div class="wpex-card-details">';

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-xl wpex-font-medium wpex-mb-10',
	) );

	// Excerpt
	$output .= $this->get_excerpt( array(
		'class' => 'wpex-mb-15',
	) );

	// Author
	$output .= $this->get_author( array(
		'class' => 'wpex-font-bold wpex-child-inherit-color',
		'link_class' => 'wpex-inherit-color',
		'prefix' => esc_html__( 'By', 'total' ) . ' ',
	) );

	// Date
	$output .= '<div class="wpex-card-meta">';

		$output .= $this->get_date( array(
			'class'    => 'wpex-tex-xs wpex-text-gray-500',
			'html_tag' => 'span',
			'suffix'   => '. '
		) );

		$output .= $this->get_time( array(
			'class' => 'wpex-tex-xs wpex-text-gray-500',
			'html_tag' => 'span',
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;