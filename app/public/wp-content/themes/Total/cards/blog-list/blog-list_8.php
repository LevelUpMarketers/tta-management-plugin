<?php
/**
 * Card: Blog List 8
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$bk = $this->get_breakpoint();

$output .= '<div class="wpex-card-inner wpex-' . $bk . '-flex wpex-bg-white wpex-border wpex-border-solid wpex-border-gray-200 wpex-rounded-sm wpex-overflow-hidden">';

	// Media
	$output .= $this->get_media( array(
		'class' => 'wpex-' . $bk . '-w-50 wpex-' . $bk . '-flex-shrink-0',
	) );

	$output .= '<div class="wpex-card-details wpex-' . $bk . '-flex-grow wpex-p-25 wpex-last-mb-0">';

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-xl wpex-mb-15',
		) );

		// Excerpt
		$output .= $this->get_excerpt( array(
			'class' => 'wpex-mb-25',
			'length' => 30,
		) );

		$output .= '<div class="wpex-card-footer wpex-flex wpex-items-center wpex-mt-25">';

			// Avatar
			$output .= $this->get_avatar( array(
				'size'        => 35,
				'class'       => 'wpex-flex-shrink-0 wpex-mr-15',
				'image_class' => 'wpex-rounded-full wpex-align-middle',
			) );

			$output .= '<div class="wpex-card-meta wpex-flex-grow wpex-leading-snug wpex-text-sm">';

				// Author
				$output .= $this->get_author( array(
					'class' => 'wpex-font-medium wpex-text-gray-900 wpex-capitalize',
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