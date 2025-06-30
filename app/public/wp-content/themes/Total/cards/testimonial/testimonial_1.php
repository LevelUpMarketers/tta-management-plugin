<?php
/**
 * Card: Testimonial 1
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-text-center">';

	// Thumbnail
	$output .= $this->get_thumbnail( array(
		'link'        => false,
		'class'       => 'wpex-rounded-full wpex-mb-15',
		'image_class' => 'wpex-rounded-full wpex-p-3 wpex-shadow-xs',
	) );

	// Rating
	$output .= $this->get_star_rating( array(
		'class' => 'wpex-mb-5 wpex-text-sm',
	) );

	// Author
	$output .= $this->get_element( array(
		'content' => wpex_get_testimonial_author(),
		'class'   => 'wpex-card-testimonial-author wpex-heading wpex-text-lg wpex-mb-15',
	) );

	// Excerpt
	$output .= $this->get_excerpt( array(
		'length' => '-1',
		'class'  => 'wpex-mt-15',
	) );

$output .= '</div>';

return $output;