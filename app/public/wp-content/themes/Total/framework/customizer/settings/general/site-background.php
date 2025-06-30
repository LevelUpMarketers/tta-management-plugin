<?php
/**
 * Site Background Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_background'] = array(
	'title'  => esc_html__( 'Site Background', 'total' ),
	'panel'  => 'wpex_general',
	'desc' => esc_html__( 'Here you can alter the global site background. It is recommended that you first set the site layout to "Boxed" under Layout > General > Site Layout Style.', 'total' ),
	'settings' => array(
		array(
			'id' => 't_background_color',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Background Color', 'total' ),
				'type' => 'color',
			),
			'inline_css' => array(
				'target' => 'body,.footer-has-reveal #main,body.boxed-main-layout',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 't_background_image',
			'sanitize_callback' => 'absint',
			'control' => array(
				'label' => esc_html__( 'Custom Background Image', 'total' ),
				'type' => 'media',
				'mime_type' => 'image',
			),
		),
		array(
			'id' => 't_background_style',
			'default' => 'stretched',
			'control' => array(
				'label' => esc_html__( 'Background Image Style', 'total' ),
				'type'  => 'select',
				'choices' => $bg_styles,
			),
		),
		array(
			'id' => 't_background_pattern',
			'sanitize_callback' => 'esc_html',
			'control' => array(
				'label' => esc_html__( 'Background Pattern', 'total' ),
				'type'  => 'wpex-bg-patterns',
			),
		),
	),
);