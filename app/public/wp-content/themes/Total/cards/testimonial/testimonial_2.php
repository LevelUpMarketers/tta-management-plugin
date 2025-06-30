<?php
/**
 * Card: Testimonial 2
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0.6
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-header wpex-flex wpex-items-center">';

	// Thumbnail
	$output .= $this->get_thumbnail( array(
		'link'        => false,
		'class'       => 'wpex-shrink-0 wpex-rounded-full wpex-mr-15',
		'image_class' => 'wpex-card-thumbnail-sm wpex-rounded-full',
	) );

	// Header aside
	$output .= '<div class="wpex-card-header-aside">';

		// Title
		$output .= $this->get_title( array(
			'link'  => false,
			'class' => 'wpex-card-title wpex-heading wpex-text-md wpex-mb-5',
		) );

		// Rating
		$output .= $this->get_star_rating( array(
			'class' => 'wpex-text-sm',
		) );

	$output .= '</div>';

$output .= '</div>';

// Excerpt
$output .= $this->get_excerpt( array(
	'class' => 'wpex-mt-15',
) );

return $output;