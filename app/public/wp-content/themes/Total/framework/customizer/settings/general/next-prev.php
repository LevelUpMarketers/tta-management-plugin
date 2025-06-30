<?php
/**
 * Next/Prev Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_next_prev'] = array(
	'title' => esc_html__( 'Next/Previous Links', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'next_prev_in_same_term',
			'default' => true,
			'control' => array(
				'type' => 'checkbox',
				'label' => esc_html__( 'From Same Category?', 'total' ),
			),
		),
		array(
			'id' => 'next_prev_reverse_order',
			'default' => false,
			'control' => array(
				'type' => 'checkbox',
				'label' => esc_html__( 'Reverse Order?', 'total' ),
			),
		),
		array(
			'id' => 'next_prev_link_bg_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '.post-pagination-wrap',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'next_prev_link_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.post-pagination-wrap',
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'next_prev_link_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => '.post-pagination-wrap',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'next_prev_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.post-pagination a',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'next_prev_link_font_size',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Font Size', 'total' ),
				'description' => esc_html__( 'Value in px or em.', 'total' ),
			),
			'inline_css' => array(
				'target' => '.post-pagination',
				'alter' => 'font-size',
				'sanitize' => 'font-size',
			),
		),
		array(
			'id' => 'next_prev_next_text',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Custom Next Text', 'total' ),
			),
		),
		array(
			'id' => 'next_prev_prev_text',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Custom Prev Text', 'total' ),
			),
		),
	),
);