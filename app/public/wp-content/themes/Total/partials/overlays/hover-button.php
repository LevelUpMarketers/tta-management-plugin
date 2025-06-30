<?php
/**
 * Hover Button Overlay
 *
 * @package Total WordPress Theme
 * @subpackage Partials
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Only used for outside_link position
if ( 'outside_link' != $position ) {
	return;
}

// Define vars
$link = $text = '';

// Lightbox
$lightbox_link  = ! empty( $args['lightbox_link'] ) ? $args['lightbox_link'] : '';
$lightbox_data  = ! empty( $args['lightbox_data'] ) ? $args['lightbox_data'] : '';
$lightbox_data  = ( is_array( $lightbox_data ) ) ? ' ' . implode( ' ', $lightbox_data ) : $lightbox_data;
$lightbox_class = ! empty( $args['lightbox_class'] ) ? $args['lightbox_class'] : 'wpex-lightbox';

if ( 'wpex-lightbox-group-item' == $lightbox_class ) {
	$lightbox_class = 'wpex-lightbox';
}

// Link
if ( ! $lightbox_link ) {
	$link = isset( $args['post_permalink'] ) ? $args['post_permalink'] : wpex_get_permalink();
} else {
	$link = $lightbox_link;
}

// Custom link
$link   = ! empty( $args['overlay_link'] ) ? $args['overlay_link'] : $link;
$target = ! empty( $args['link_target'] ) ? $args['link_target'] : $link;

// Text
$text = ! empty( $args['overlay_button_text'] ) ? $args['overlay_button_text'] : esc_html__( 'View Post', 'total' );
$text = ( 'post_title' == $text ) ? get_the_title() : $text;

// Link classes
$link_classes = 'overlay-hover-button-link wpex-text-md theme-button minimal-border white';
if ( $lightbox_link ) {
	$link_classes .= ' ' . $lightbox_class;
}

// Apply filters for child theming
$link   = apply_filters( 'wpex_hover_button_overlay_link', $link );
$target = apply_filters( 'wpex_button_overlay_target', $target );
$text   = apply_filters( 'wpex_hover_button_overlay_text', $text );

// Get animation speed
$speed = wpex_overlay_speed( 'hover-button' );

?>

<div class="overlay-hover-button overlay-hide theme-overlay wpex-absolute wpex-inset-0 wpex-transition-all wpex-duration-<?php echo intval( $speed ); ?> wpex-flex wpex-items-center wpex-justify-center">
	<span class="overlay-bg wpex-bg-<?php echo wpex_overlay_bg( 'hover-button' ); ?> wpex-block wpex-absolute wpex-inset-0 wpex-opacity-<?php echo wpex_overlay_opacity( 'hover-button' ); ?>"></span>
	<div class="overlay-content overlay-scale wpex-relative wpex-font-semibold wpex-transition-transform wpex-duration-<?php echo intval( $speed ); ?> wpex-p-20 wpex-clr"><?php echo wpex_parse_html( 'a', array(
		'href'   => esc_url( $link ),
		'class'  => esc_attr( $link_classes ),
		'data'   => $lightbox_data,
		'target' => $target,
	), do_shortcode( wp_kses_post( $text ) ) ); ?></div>
</div>