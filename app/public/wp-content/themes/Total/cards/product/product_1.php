<?php
/**
 * Card: Product 1
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-text-center wpex-relative">';

	// Sale tag
	$sale_flash = $this->get_sale_flash( array(
		'class' => 'wpex-absolute wpex-z-5 wpex-left-0 wpex-top-0 wpex-ml-15 wpex-mt-15 wpex-inline-block wpex-py-5 wpex-px-10 wpex-mb-20 wpex-bg-accent wpex-text-white wpex-leading-normal wpex-rounded-sm',
	) );

	// Media
	$output .= $this->get_media( array(
		'class'  => 'wpex-mb-15',
		'before' => $sale_flash
	) );

	$output .= '<div class="wpex-card-details wpex-last-mb-0">';

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-md wpex-mb-5',
		) );

		// Price
		$output .= $this->get_price();

	$output .= '</div>';

$output .= '</div>';

return $output;