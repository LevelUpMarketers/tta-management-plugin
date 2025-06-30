<?php
/**
 * Card: Simple 6
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Title
$output .= $this->get_title( array(
	'class' => 'wpex-heading wpex-text-lg wpex-mb-10',
) );

// Excerpt
$output .= $this->get_excerpt( array(
	'class' => 'wpex-mb-20',
) );

// More Link
$output .= $this->get_more_link( array(
	'text'   => esc_html__( 'Learn more', 'total' ),
	'suffix' => ' &rarr;',
) );

return $output;