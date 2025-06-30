<?php
/**
 * Card: Icon Box 6
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-bg-white wpex-border wpex-border-solid wpex-border-gray-300 wpex-border-t-4 wpex-border-t-gray-800">';

	$output .= '<div class="wpex-card-details wpex-p-20">';

		// Icon
		$output .= $this->get_icon( array(
			'size'  => 'sm',
			'class' => 'wpex-text-accent wpex-mb-15',
		) );

		// Title
		$output .= $this->get_title( array(
			'class' => 'wpex-heading wpex-text-lg wpex-mb-10',
		) );

		// Excerpt
		$output .= $this->get_excerpt();

	$output .= '</div>';

	// More Link
	$output .= $this->get_more_link( array(
		'class'  => 'wpex-border-t wpex-border-solid wpex-border-gray-300 wpex-py-15 wpex-px-20 wpex-text-sm wpex-font-medium wpex-uppercase',
		'text'   => esc_html__( 'Learn more', 'total' ),
		'suffix' => ' &raquo;',
	) );

$output .= '</div>';

return $output;