<?php
/**
 * Tables
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_general_tables'] = array(
	'title' => esc_html__( 'Tables', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'thead_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Table Header Background', 'total' ),
			),
			'inline_css' => array(
				'target' => 'thead',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'thead_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Table Header Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'table thead, table thead th',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'tables_th_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Th Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'table th',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'tables_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Cells Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'table th, table td',
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'tables_cell_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Cell Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => 'table th, table td',
				'alter' => 'padding',
			),
		),
	),
);
