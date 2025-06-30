<?php
/**
 * Forms Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$inputs_target = '.site-content input[type="date"],.site-content input[type="time"],.site-content input[type="datetime-local"],.site-content input[type="week"],.site-content input[type="month"],.site-content input[type="text"],.site-content input[type="email"],.site-content input[type="url"],.site-content input[type="password"],.site-content input[type="search"],.site-content input[type="tel"],.site-content input[type="number"],.site-content textarea';

$inputs_target_focus = '.site-content input[type="date"]:focus,.site-content input[type="time"]:focus,.site-content input[type="datetime-local"],.site-content input[type="week"],.site-content input[type="month"]:focus,.site-content input[type="text"]:focus,.site-content input[type="email"]:focus,.site-content input[type="url"]:focus,.site-content input[type="password"]:focus,.site-content input[type="search"]:focus,.site-content input[type="tel"]:focus,.site-content input[type="number"]:focus,.site-content textarea:focus';

$this->sections['wpex_general_forms'] = array(
	'title' => esc_html__( 'Forms', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'label_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Label Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'label,#comments #commentform label',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'forms_inputs_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Inputs', 'total' ),
			),
		),
		array(
			'id' => 'input_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '6px 10px',
				),
			),
			'inline_css' => array(
				'target' => $inputs_target,
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'input_border_radius',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Radius', 'total' ),
				'input_attrs' => array(
					'placeholder' => '3px',
				),
			),
			'inline_css' => array(
				'target' => $inputs_target,
				'alter' => array( 'border-radius' ),
			),
		),
		array(
			'id' => 'input_font_size',
			'description' => esc_html__( 'Value in px or em.', 'total' ),
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Font-Size', 'total' ),
				'input_attrs' => array(
					'placeholder' => '1em',
				),
			),
			'inline_css' => array(
				'target' => $inputs_target,
				'alter' => 'font-size',
			),
		),
		array(
			'id' => 'input_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => $inputs_target,
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'input_background_focus',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Focus: Background', 'total' ),
			),
			'inline_css' => array(
				'target' => $inputs_target_focus,
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'input_border',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => $inputs_target,
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'input_border_focus',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Focus: Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => $inputs_target_focus,
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'input_border_width',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Width', 'total' ),
				'input_attrs' => array(
					'placeholder' => '1px',
				),
			),
			'inline_css' => array(
				'target' => $inputs_target,
				'alter' => 'border-width',
			),
		),
		array(
			'id' => 'input_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => $inputs_target,
				'alter' => 'color',
			),
		),
		array(
			'id' => 'input_color_focus',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Focus: Color', 'total' ),
			),
			'inline_css' => array(
				'target' => $inputs_target_focus,
				'alter' => 'color',
			),
		),
	),
);
