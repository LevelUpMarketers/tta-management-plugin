<?php
/**
 * Scroll To Top Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_scroll_top'] = array(
	'title' => esc_html__( 'Scroll To Top', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'scroll_top',
			'default' => true,
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Enable Scroll Up Button?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'scroll_top_style',
			'default' => '',
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'black' => esc_html__( 'Black', 'total' ),
					'accent' => esc_html__( 'Accent', 'total' ),
				),
			),
		),
		array(
			'id' => 'scroll_top_breakpoint',
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Breakpoint', 'total' ),
				'type' => 'select',
				'choices' => wpex_utl_breakpoints(),
				'description' => esc_html__( 'Select the breakpoint at which point the scroll to button becomes visible. By default it is visible on all devices.', 'total' ),
			),
		),
		array(
			'id' => 'scroll_top_arrow',
			'default' => 'chevron-up',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Arrow', 'total' ),
				'type' => 'select',
				'choices' => array(
					'chevron-up' => 'chevron-up',
					'caret-up' => 'caret-up',
					'angle-up' => 'angle-up',
					'angle-double-up' => 'angle-double-up',
					'long-arrow-up' => 'long-arrow-up',
					'arrow-circle-o-up' => 'arrow-circle-o-up',
					'arrow-up' => 'arrow-up',
					'caret-square-o-up' => 'caret-square-o-up',
					'level-up' => 'level-up',
					'sort-up' => 'sort-up',
					'toggle-up' => 'toggle-up',
				),
			),
		),
		array(
			'id' => 'scroll_top_speed',
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Local Scroll Speed in Milliseconds', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '1000',
				),
			),
		),
		array(
			'id' => 'scroll_top_size',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Button Size', 'total' ),
				'input_attrs' => array(
					'placeholder' => '35px',
				),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top',
				'sanitize' => 'px',
				'alter' => array(
					'width',
					'height',
					'line-height',
				),
			),
		),
		array(
			'id' => 'scroll_top_icon_size',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Icon Size', 'total' ),
				'input_attrs' => array(
					'placeholder' => '16px',
				),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top',
				'alter' => 'font-size',
			),
		),
		array(
			'id' => 'scroll_top_border_radius',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Radius', 'total' ),
				'input_attrs' => array(
					'placeholder' => '9999px',
				),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top',
				'alter' => 'border-radius',
			),
		),
		array(
			'id' => 'scroll_top_right_position',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Side Position', 'total' ),
				'input_attrs' => array(
					'placeholder' => '25px',
				),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top',
				'alter' => is_rtl() ? 'margin-left' : 'margin-right',
			),
		),
		array(
			'id' => 'scroll_top_bottom_position',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Bottom Position', 'total' ),
				'input_attrs' => array(
					'placeholder' => '25px',
				),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top',
				'alter' => 'margin-bottom',
			),
		),
		array(
			'id' => 'scroll_top_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'scroll_top_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top:hover',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'scroll_top_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'scroll_top_bg_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#site-scroll-top:hover',
				'alter' => 'background-color',
			),
		),
	),
);