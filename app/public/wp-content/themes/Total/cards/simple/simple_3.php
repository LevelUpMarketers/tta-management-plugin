<?php
/**
 * Card: Simple 3
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Inner
$output .= '<div class="wpex-card-inner wpex-pt-15 wpex-pb-30 wpex-border-t-2 wpex-border-main wpex-border-solid wpex-last-mb-0">';

	// Primary Term
	$output .= $this->get_primary_term( array(
		'class'      => 'wpex-mb-15 wpex-text-xs wpex-font-bold',
		'term_class' => 'wpex-text-accent'
	) );

	// Title
	$output .= $this->get_title( array(
		'class' => 'wpex-heading wpex-text-2xl wpex-mb-20',
	) );

	// Excerpt
	$output .= $this->get_excerpt( array(
		'excerpt_length' => 30,
	) );

$output .= '</div>';

return $output;