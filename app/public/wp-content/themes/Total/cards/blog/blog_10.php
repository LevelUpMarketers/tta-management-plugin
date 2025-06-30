<?php
/**
 * Card: Blog 10
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Media
$output .= $this->get_media( array(
	'class' => 'wpex-mb-25',
) );

// Primary term
$output .= $this->get_primary_term( array(
	'class' => 'wpex-uppercase wpex-text-gray-600 wpex-text-xs wpex-font-semibold wpex-tracking-wide wpex-mb-5',
	'term_class' => 'wpex-inherit-color',
) );

// Title
$output .= $this->get_title( array(
	'class' => 'wpex-heading wpex-text-xl wpex-mb-15',
) );

// Excerpt
$output .= $this->get_excerpt();

return $output;