<?php
/**
 * Card: Testimonial 5
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-flex wpex-items-center wpex-bg-white wpex-p-25 wpex-rounded-md wpex-shadow">';

	$output .= '<div class="wpex-card-details wpex-flex-grow">';

		// Icon
		$quote_dir = is_rtl() ? 'right' : 'left';
		$output .= $this->get_icon( array(
			'icon' => 'ticon ticon-quote-' . $quote_dir,
			'class' => 'wpex-text-accent wpex-mb-10',
		) );

		// Excerpt
		$output .= $this->get_excerpt( array(
			'length' => '-1',
			'class'  => 'wpex-font-500',
		) );

		// Author
		$output .= $this->get_element( array(
			'content'  => wpex_get_testimonial_author(),
			'class'    => 'wpex-card-testimonial-author wpex-mt-10 wpex-heading wpex-text-md wpex-text-gray-900 wpex-font-bold wpex-uppercase',
		) );

	$output .= '</div>';

	// Thumbnail
	$output .= $this->get_thumbnail( array(
		'link'        => false,
		'class'       => 'wpex-flex-shrink-0 wpex-ml-20 wpex-rounded-full',
		'image_class' => 'wpex-rounded-full',
	) );

$output .= '</div>';

return $output;