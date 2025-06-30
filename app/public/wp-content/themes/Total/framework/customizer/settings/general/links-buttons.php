<?php
/**
 * Links & Buttons
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_general_links_buttons'] = array(
	'title' => esc_html__( 'Links & Buttons', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Links Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'a,h1 a:hover,h2 a:hover,h3 a:hover,h4 a:hover,h5 a:hover,h6 a:hover,.entry-title a:hover,.meta a:hover,.wpex-heading a:hover,.vcex-module a:hover .wpex-heading,.vcex-icon-box-link-wrap:hover .wpex-heading',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Links Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => 'a:hover',
				'alter' => 'color',
			),
		),
		// Buttons
		array(
			'id' => 'buttons_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Buttons', 'total' ),
			),
		),
		array(
			'id' => 'theme_button_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => '.theme-button,input[type="submit"],button,.button,.added_to_cart',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'theme_button_border_radius',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Radius', 'total' ),
			),
			'inline_css' => array(
				'target' => '.theme-button,input[type="submit"],button,#site-navigation .menu-button > a > span.link-inner,.button,.added_to_cart',
				'alter' => 'border-radius',
			),
		),
		array(
			'id' => 'theme_button_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '.theme-button,input[type="submit"],button,#site-navigation .menu-button > a > span.link-inner,.button,.added_to_cart',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'theme_button_hover_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '.theme-button:hover,input[type="submit"]:hover,button:hover,#site-navigation .menu-button > a:hover > span.link-inner,.button:hover,.added_to_cart:hover',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'theme_button_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.theme-button,input[type="submit"],button,#site-navigation .menu-button > a > span.link-inner,.button,.added_to_cart',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'theme_button_hover_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '.theme-button:hover,input[type="submit"]:hover,button:hover,#site-navigation .menu-button > a:hover > span.link-inner,.button:hover,.added_to_cart:hover',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'theme_button_border_style',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'select',
				'label' => esc_html__( 'Border Style', 'total' ),
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'solid' => esc_html__( 'Solid', 'total' ),
					'dashed' => esc_html__( 'Dashed', 'total' ),
					'none' => esc_html__( 'None', 'total' ),
				),
			),
			'inline_css' => array(
				'target' => '.theme-button,input[type="submit"],button,#site-navigation .menu-button > a > span.link-inner,.button,.added_to_cart',
				'alter' => 'border-style',
			),
		),
		array(
			'id' => 'theme_button_border_width',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Width', 'total' ),
			),
			'inline_css' => array(
				'target' => '.theme-button,input[type="submit"],button,#site-navigation .menu-button > a > span.link-inner,.button,.added_to_cart',
				'alter' => 'border-width',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'theme_button_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.theme-button,input[type="submit"],button,#site-navigation .menu-button > a > span.link-inner,.button,.added_to_cart',
				'alter' => 'border-color',
			),
		),
	),
);
