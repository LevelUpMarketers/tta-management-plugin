<?php
/**
 * Pagination Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_pagination'] = array(
	'title' => esc_html__( 'Pagination', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'pagination_align',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'select',
				'default' => 'left',
				'label' => esc_html__( 'Alignment', 'total' ),
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'left' => esc_html__( 'Left', 'total' ),
					'center' => esc_html__( 'Center', 'total' ),
					'right' => esc_html__( 'Right', 'total' ),
				),
			),
		),
		array(
			'id' => 'pagination_arrow',
			'transport' => 'postMessage',
			'default' => 'angle',
			'control' => array(
				'type' => 'select',
				'label' => esc_html__( 'Arrow Style', 'total' ),
				'choices' => array(
					'angle' => esc_html__( 'Angle', 'total' ),
					'arrow' => esc_html__( 'Arrow', 'total' ),
					'caret' => esc_html__( 'Caret', 'total' ),
					'chevron' => esc_html__( 'Chevron', 'total' ),
				),
			),
		),
		array(
			'id' => 'pagination_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => 'ul.page-numbers a,span.page-numbers,.page-links > span,.page-links a > span,.bbp-pagination-links span.page-numbers,.bbp-pagination-links .page-numbers',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'pagination_font_size',
			'description' => esc_html__( 'Value in px or em.', 'total' ),
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Font Size', 'total' ),
			),
			'inline_css' => array(
				'target' => 'ul.page-numbers, .page-links',
				'alter' => 'font-size',
			),
		),
		array(
			'id' => 'pagination_border_width',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Width', 'total' ),
			),
			'inline_css' => array(
				'target' => 'ul.page-numbers, ul.page-numbers li, .page-links, .page-links > span, .page-links > a, .page-links li',
				'alter' => 'border-width',
			),
		),
		array(
			'id' => 'pagination_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'ul.page-numbers, ul.page-numbers li, .page-links, .page-links > a, .page-links > span, .page-links li',
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'pagination_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => 'ul.page-numbers a, a.page-numbers, span.page-numbers, .page-links span',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'pagination_hover_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-numbers a:hover,.page-numbers.current,.page-numbers.current:hover,.page-links > span.current,.page-links a > span:hover,.bbp-pagination-links .page-numbers.current,.elementor-pagination .page-numbers.current',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'pagination_hover_active',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color: Active', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-numbers.current,.page-numbers.current:hover,.page-links > span.current,.bbp-pagination-links .page-numbers.current,.elementor-pagination .page-numbers.current',
				'alter' => 'color',
				'important' => true,
			),
		),
		array(
			'id' => 'pagination_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => 'ul.page-numbers a,span.page-numbers,.page-links > span,.page-links a > span,.bbp-pagination-links span.page-numbers,.bbp-pagination-links .page-numbers',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'pagination_hover_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-numbers a:hover,.page-numbers.current,.page-numbers.current:hover,.page-links > span.current,.page-links a > span:hover,.bbp-pagination-links .page-numbers.current,.elementor-pagination .page-numbers.current',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'pagination_active_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background: Active', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-numbers.current,.page-numbers.current:hover,.page-links > span.current,.bbp-pagination-links .page-numbers.current,.elementor-pagination .page-numbers.current',
				'alter' => 'background',
				'important' => true,
			),
		),
		array(
			'id' => 'loadmore_text',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Load More Text', 'total' ),
			),
		),
	),
);