<?php
/**
 * Accent Color Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_accent_colors'] = array(
	'title' => esc_html__( 'Accent Colors', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'accent_color',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Accent Color', 'total' ),
				'type' => 'color',
			),
		),
		array(
			'id' => 'accent_color_hover',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Accent Hover Color', 'total' ),
				'description' => esc_html__( 'Used for various hovers on accent colors such as buttons. If left empty it will inherit the custom accent color defined above.', 'total' ),
				'type' => 'color',
			),
		),
		array(
			'id' => 'main_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Border Accent Color', 'total' ),
				'type' => 'color',
			),
		),
		array(
			'id' => 'highlight_bg',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'User Selection Background', 'total' ),
				'type' => 'color',
			),
			'inline_css' => array(
				'target' => array( '::selection', '::-moz-selection' ),
				'alter' => 'background',
			),
		),
		array(
			'id' => 'highlight_color',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'User Selection Color', 'total' ),
				'type' => 'color',
			),
			'inline_css' => array(
				'target' => array( '::selection', '::-moz-selection' ),
				'alter' => 'color',
			),
		),
	)
);