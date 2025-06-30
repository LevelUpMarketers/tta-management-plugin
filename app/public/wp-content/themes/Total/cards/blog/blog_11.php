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
	'class' => 'wpex-mb-10',
) );

// Title
$output .= $this->get_title( array(
	'class' => 'wpex-heading wpex-text-lg wpex-mb-5',
) );

// Author
$output .= $this->get_author( array(
	'class' => 'wpex-text-gray-500 wpex-font-medium wpex-child-inherit-color',
	'prefix' => esc_html( 'By', 'total' ) . ' ',
) );

return $output;