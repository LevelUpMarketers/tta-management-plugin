<?php
/**
 * Customizer => Footer Bottom
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0.4
 */

defined( 'ABSPATH' ) || exit;

// General
$this->sections['wpex_footer_bottom'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'settings' => array(
		array(
			'id' => 'footer_bottom',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Footer Bottom (copyright/menu)?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'If you disable this option we recommend you go to the Customizer Manager and disable the section as well so the next time you work with the Customizer it will load faster.', 'total' ),
			),
		),
		array(
			'id' => 'footer_copyright_text',
			'transport' => 'partialRefresh',
			'default' => 'Copyright <a href="#">Your Business LLC.</a> [current_year] - All Rights Reserved',
			'control' => array(
				'label' => esc_html__( 'Copyright', 'total' ),
				'type' => 'textarea',
			),
		),
		array(
			'id' => 'bottom_footer_text_align',
			'transport' => 'partialRefresh',
			'control' =>  array(
				'type' => 'select',
				'label' => esc_html__( 'Text Align', 'total' ),
				'choices' => array(
					'' => esc_html__( 'Default','total' ),
					'left' => esc_html__( 'Left','total' ),
					'right' => esc_html__( 'Right','total' ),
					'center' => esc_html__( 'Center','total' ),
				),
			),
		),
		array(
			'id' => 'bottom_footer_padding',
			'transport' => 'postMessage',
			'control' =>  array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => '#footer-bottom',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'bottom_footer_background',
			'transport' => 'postMessage',
			'control' =>  array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-bottom',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'bottom_footer_color',
			'transport' => 'postMessage',
			'control' =>  array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#footer-bottom',
					'#footer-bottom p',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'bottom_footer_link_color',
			'transport' => 'postMessage',
			'control' =>  array(
				'type' => 'color',
				'label' => esc_html__( 'Links', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-bottom a',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'bottom_footer_link_color_hover',
			'transport' => 'postMessage',
			'control' =>  array(
				'type' => 'color',
				'label' => esc_html__( 'Links: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-bottom a:hover',
				'alter' => 'color',
			),
		),
	),
);