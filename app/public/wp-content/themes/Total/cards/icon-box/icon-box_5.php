<?php
/**
 * Card: Icon Box 5
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

$output = '';

if ( $this->has_link() ) {
	$output .= $this->get_link_open( array(
		'class' => 'wpex-card-inner wpex-block wpex-bg-white wpex-p-40 wpex-border-2 wpex-border-gray-200 wpex-border-solid wpex-text-center wpex-text-gray-600 wpex-no-underline wpex-transition-all wpex-duration-300 wpex-hover-border-accent wpex-hover-text-accent',
	) );
} else {
	$output .= '<div class="wpex-card-inner wpex-block wpex-bg-white wpex-p-40 wpex-border-2 wpex-border-gray-200 wpex-border-solid wpex-text-center wpex-no-underline wpex-text-gray-600">';
}

	// Icon
	$output .= $this->get_icon( array(
		'link'  => false,
		'size'  => 'sm',
		'class' => 'wpex-text-accent wpex-mb-10',
	) );

	// Title
	$output .= $this->get_title( array(
		'link'  => false,
		'class' => 'wpex-heading wpex-font-medium wpex-text-lg wpex-inherit-color',
	) );

if ( $this->has_link() ) {
	$output .= $this->get_link_close();
} else {
	$output .= '</div>';
}

return $output;