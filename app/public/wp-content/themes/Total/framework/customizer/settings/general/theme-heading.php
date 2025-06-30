<?php
/**
 * Theme Heading Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_theme_heading'] = array(
	'title' => esc_html__( 'Theme Heading', 'total' ),
	'panel' => 'wpex_general',
	'desc' => esc_html__( 'Heading used in various places such as the related and comments heading.', 'total' ),
	'settings' => array(
		array(
			'id' => 'theme_heading_style',
			'control' => array(
				'type' => 'select',
				'default' => '',
				'label' => esc_html__( 'Style', 'total' ),
				'choices' => wpex_get_theme_heading_styles(),
			),
		),
		array(
			'id' => 'theme_heading_tag',
			'default' => 'div',
			'control' => array(
				'label' => esc_html__( 'Tag', 'total' ),
				'type' => 'select',
				'choices' => array(
					'div' => 'div',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6',
				),
			),
		),
	),
);