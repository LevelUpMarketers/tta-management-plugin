<?php
/**
 * Card: Testimonial 4
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Inner
$output .= '<div class="wpex-card-inner wpex-bg-gray-100 wpex-p-30 wpex-text-center wpex-last-mb-0">';

	// Excerpt
	$output .= $this->get_excerpt( array(
		'length' => '-1',
		'class'  => 'wpex-text-2xl wpex-font-500 wpex-text-gray-900',
	) );

	// Footer
	$output .= '<div class="wpex-card-footer wpex-mt-20 wpex-flex wpex-items-center wpex-justify-center wpex-text-left">';

		// Thumbnail
		$output .= $this->get_thumbnail( array(
			'link'        => false,
			'class'       => 'wpex-rounded-full wpex-mr-20',
			'image_class' => 'wpex-rounded-full',
		) );

		// Meta
		$output .= '<div class="wpex-card-meta">';

			// Author
			$output .= $this->get_element( array(
				'content'  => wpex_get_testimonial_author(),
				'class'    => 'wpex-card-testimonial-author wpex-heading wpex-text-md wpex-text-gray-900 wpex-font-bold',
			) );

			// Company
			$output .= $this->get_element( array(
				'content'     => wpex_get_testimonial_company(),
				'link'        => wpex_get_testimonial_company_url(),
				'link_target' => wpex_get_testimonial_company_link_target(),
				'class'       => 'wpex-card-testimonial-company wpex-text-gray-600 wpex-child-inherit-color',
			) );

		$output .= '</div>';

	$output .= '</div>';

$output .= '</div>';

return $output;