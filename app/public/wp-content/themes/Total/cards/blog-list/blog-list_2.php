<?php
/**
 * Card: Blog List 2
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$bk = $this->get_breakpoint();

$output .= '<div class="wpex-card-inner wpex-' . $bk . '-flex">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-mb-20 wpex-' . $bk . '-w-33 wpex-' . $bk . '-flex-shrink-0 wpex-' . $bk . '-mb-0 wpex-' . $bk . '-mr-30 wpex-rounded-sm',
		'image_class' => 'wpex-rounded-sm',
	) );

	// Details
	$output .= '<div class="wpex-card-details wpex-' . $bk . '-flex-grow wpex-last-mb-0">';

		// Terms
		$output .= $this->get_terms_list( array(
			'class' => 'wpex-mb-10 wpex-text-sm wpex-font-semibold',
			'separator' => ' &middot; '
		) );

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-xl wpex-mb-15',
			'link_class' => 'wpex-inherit-color-important',
		) );

		// Excerpt
		$output .= $this->get_excerpt( array(
			'class' => 'wpex-mb-15',
		) );

		// Footer
		$output .= '<div class="wpex-card-footer wpex-flex wpex-items-center">';

			// Avatar
			$output .= $this->get_avatar( array(
				'size'        => 40,
				'class'       => 'wpex-flex-shrink-0 wpex-mr-15',
				'image_class' => 'wpex-rounded-full wpex-align-middle',
			) );

			// Footer aside
			$output .= '<div class="wpex-card-meta wpex-flex-grow wpex-leading-snug wpex-text-sm">';

				// Author
				$output .= $this->get_author( array(
					'class' => 'wpex-text-gray-900 wpex-font-bold wpex-capitalize',
					'link'  => false,
				) );

				// Date
				$output .= $this->get_date( array(
					'type' => 'published',
				) );

			$output .= '</div>';

		$output .= '</div>';

	$output .= '</div>';

$output .= '</div>';

return $output;