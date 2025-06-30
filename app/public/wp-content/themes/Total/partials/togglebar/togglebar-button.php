<?php
/**
 * Togglebar button output
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 5.0.6
 */

defined( 'ABSPATH' ) || exit;

// Get options
$toggle_bar_style = wpex_togglebar_style();
$default_state    = wpex_togglebar_state();
$visibility       = wpex_togglebar_visibility();

// Link attributes
$attrs = array(
	'href'        => '#',
	'class'       => array(
		'toggle-bar-btn',
		'fade-toggle',
		'open-togglebar',
		'wpex-block',
		'wpex-top-0',
		'wpex-right-0',
		'wpex-text-white'
	),
	'aria-hidden' => 'true',
);

// Set correct position
if ( 'inline' == $toggle_bar_style ) {
	$attrs['class'][] = 'wpex-absolute';
} else {
	$attrs['class'][] = 'wpex-fixed';
}

// Visibility
if ( $visibility && 'always-visible' !== $visibility ) {
	$attrs['class'][] = $visibility;
}

// Add active class if set to display by default
if ( 'visible' == $default_state ) {
	$attrs['class'][] = 'active-bar';
}

// Closed icon classes
$closed_icon = get_theme_mod( 'toggle_bar_button_icon', 'plus' );
$closed_icon = apply_filters( 'wpex_togglebar_icon_class', 'ticon ticon-' . $closed_icon );

// Active icon classes
$active_icon = get_theme_mod( 'toggle_bar_button_icon_active', 'minus' );
$active_icon = apply_filters( 'wpex_togglebar_icon_active_class', 'ticon ticon-' . $active_icon );

// Default icon
$default_icon = ( 'visible' == $default_state ) ? $active_icon : $closed_icon;

// Closed icon
$attrs['data-icon'] = esc_attr( $closed_icon );

// Active icon @todo rename to data-icon-active?
$attrs['data-icon-hover'] = esc_attr( $active_icon );

// Icon
$icon = '<span class="' . esc_attr( $default_icon ) . '"></span>';

// Display button
echo wpex_parse_html( 'a', $attrs, $icon ); ?>