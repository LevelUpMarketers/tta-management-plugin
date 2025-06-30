<?php
/**
 * Card: Product 5
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-relative">';

	// Sale flash
	$sale_flash = $this->get_sale_flash( array(
		'class' => 'wpex-absolute wpex-z-5 wpex-left-0 wpex-top-0 wpex-mt-10 -wpex-ml-5 wpex-inline-block wpex-py-5 wpex-px-10 wpex-mb-20 wpex-bg-accent wpex-text-white wpex-leading-normal wpex-text-xs wpex-uppercase wpex-font-semibold',
	) );

	// Media
	$output .= $this->get_media( array(
		'before' => $sale_flash,
		'class'  => 'wpex-mb-20'
	) );

	// Meta
	$output .= '<div class="wpex-card-meta wpex-flex wpex-flex-wrap wpex-justify-between">';

		// Primary Term
		$output .= $this->get_primary_term( array(
			'class' => 'wpex-text-xs wpex-uppercase wpex-text-gray-500 wpex-child-inherit-color',
		) );

		// Rating
		$output .= $this->get_star_rating( array(
			'class' => '',
		) );

	$output .= '</div>';

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-md wpex-font-normal',
	) );

	// Price
	$output .= $this->get_price( array(
		'class'    => 'wpex-text-black wpex-text-2xl wpex-font-bold',
		'show_sale' => true,
		'min_max'   => true,
	) );

$output .= '</div>';

return $output;