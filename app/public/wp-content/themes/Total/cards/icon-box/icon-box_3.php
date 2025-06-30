<?php
/**
 * Card: Icon Box 3
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

$output = '';

$output .= '<div class="wpex-card-inner wpex-bg-gray-100 wpex-text-gray-900 wpex-p-30 wpex-text-center">';

	// Icon
	$output .= $this->get_icon( array(
		'size'  => 'sm',
		'class' => 'wpex-text-accent wpex-mb-20',
	) );

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-lg wpex-mb-15',
	) );

	// Excerpt
	$output .= $this->get_excerpt( array(
		'class' => 'wpex-mb-15',
	) );

	// More Link
	$output .= $this->get_more_link( array(
		'class'  => 'wpex-font-semibold',
		'text'   => esc_html__( 'Learn more', 'total' ),
		'suffix' => ' &rarr;',
	) );

$output .= '</div>';

return $output;