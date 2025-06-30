<?php
/**
 * Card: Blog 1
 *
 * @package Total WordPress Theme
 * @subpackage Cards
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

$output = '';

// Media
$output .= $this->get_media( array(
	'class' => 'wpex-mb-20',
) );

// Title
$output .= $this->get_title( array(
	'class' => 'wpex-heading wpex-text-lg wpex-mb-10',
) );

$output .= '<div class="wpex-card-meta wpex-flex wpex-flex-wrap wpex-mb-15 wpex-child-inherit-color">';

	// Date
	$output .= $this->get_date( array(
		'class' => 'wpex-mr-20',
		'icon'  => 'ticon ticon-clock-o wpex-mr-5',
	) );

	// Author
	$output .= $this->get_author( array(
		'class' => 'wpex-mr-20',
		'icon' => 'ticon ticon-user-o wpex-mr-5',
	) );

	// Primary Term
	$output .= $this->get_primary_term( array(
		'class' => 'wpex-mr-20',
		'term_class' => 'wpex-mr-5',
		'icon' => 'ticon ticon-folder-o wpex-mr-5',
	) );

	// Comment Count
	$this->get_comment_count( array(
		'class' => 'wpex-child-inherit-color',
		'icon' => 'ticon ticon-comment-o wpex-mr-5',
	) );

$output .= '</div>';

// Excerpt
$output .= $this->get_excerpt( array(
	'class' => 'wpex-mb-20',
) );

// More Button
$output .= $this->get_more_link( array(
	'link_class' => 'theme-button',
	'text'       => esc_html__( 'Read more', 'total' ),
) );

return $output;