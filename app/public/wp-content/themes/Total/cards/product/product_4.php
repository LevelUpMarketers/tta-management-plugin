<?php
/**
 * Card: Product 4
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-relative wpex-bg-white wpex-shadow-lg">';

	// Sale flash
	$sale_flash = $this->get_sale_flash( array(
		'class' => 'wpex-absolute wpex-z-5 wpex-left-0 wpex-top-0 wpex-inline-block wpex-py-5 wpex-px-10 wpex-mb-20 wpex-bg-accent wpex-text-white wpex-leading-normal wpex-text-xs wpex-uppercase wpex-font-semibold',
	) );

	// Media
	$output .= $this->get_media( array(
		'before' => $sale_flash,
	) );

	$output .= '<div class="wpex-card-details wpex-p-15 wpex-last-mb-0">';

		// Categories
		$output .= $this->get_terms_list( array(
			'class'     => 'wpex-last-mr-0 wpex-text-gray-500 wpex-child-inherit-color',
			'separator' => ' &middot; ',
		) );

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-mb-10 wpex-font-semibold',
		) );

		// Price
		$output .= $this->get_price( array(
			'class'    => 'wpex-text-black wpex-text-lg wpex-font-semibold',
			'show_sale' => true,
			'min_max'   => true,
		) );

	$output .= '</div>';

$output .= '</div>';

return $output;