<?php
/**
 * Card: Video 1
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Video
$output .= $this->get_video( array(
	'class' => 'wpex-mb-15',
) );

// Title
$output .= $this->get_title( array(
	'class'      => 'wpex-heading wpex-text-md',
	'link_class' => 'wpex-inherit-color-important wpex-hover-underline',
) );

// Terms
$output .= $this->get_terms_list( array(
	'class'      => 'wpex-text-gray-800',
	'separator'  => ' &middot; ',
	'term_class' => 'wpex-inherit-color',
) );

return $output;