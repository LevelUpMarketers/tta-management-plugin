<?php
/**
 * Card: Simple 5
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Title
$output .= $this->get_title( array(
	'class' => 'wpex-heading wpex-text-lg',
) );

$output .= '<div class="wpex-card-meta wpex-mt-5 wpex-text-gray-500 wpex-child-inherit-color wpex-last-mr-0">';

	// Author
	$output .= $this->get_author( array(
		'html_tag'   => 'span',
		'prefix'     => esc_html( 'by', 'total' ) . ' ',
		'class'      => 'wpex-inline-block wpex-mr-5',
		'link_class' => 'wpex-underline',
	) );

	// Date
	$output .= $this->get_date( array(
		'html_tag' => 'span',
		'prefix'   => esc_html( 'on', 'total' ) . ' ',
		'class'    => 'wpex-inline-block wpex-mr-5',
	) );

$output .= '</div>';

return $output;