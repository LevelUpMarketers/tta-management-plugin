<?php
/**
 * Sidebar Customizer Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_sidebar'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'settings' => array(
		array(
			'id' => 'has_widget_icons',
			'transport' => 'postMessage',
			'default' => '1',
			'control' => array(
				'label' => esc_html__( 'Widget Icons', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Certain widgets include little icons such as the recent posts widget. Here you can toggle the icons on or off.', 'total' ),
			),
		),

		// General Design
		array(
			'id' => 'sidebar_heading_styling',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Design', 'total' ),
			),
		),
		array(
			'id' => 'sidebar_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#sidebar',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'sidebar_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => '#sidebar',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'sidebar_text_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Text Color', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#sidebar',
					'#sidebar p',
					'.widget-recent-posts-icons li .fa',
				),
				'alter' => 'color',
			),
		),
		array(
			'id' => 'sidebar_borders_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Li & Calendar Borders', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#sidebar li',
					'#sidebar .modern-menu-widget',
					'#sidebar .modern-menu-widget li ul',
					'#sidebar #wp-calendar thead th',
					'#sidebar #wp-calendar tbody td',
				),
				'alter' => 'border-color',
			),
		),

		// Links
		array(
			'id' => 'sidebar_heading_links',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Links', 'total' ),
			),
		),
		array(
			'id' => 'sidebar_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#sidebar a',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'sidebar_link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#sidebar a:hover',
				'alter' => 'color',
			),
		),

		// Widget Titles
		array(
			'id' => 'sidebar_heading_widget_title',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Widget Title', 'total' ),
			),
		),
		array(
			'id' => 'sidebar_headings',
			'default' => 'div',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Tag', 'total' ),
				'type' => 'select',
				'choices' => array(
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6',
					'span' => 'span',
					'div' => 'div',
				),
			),
		),
		array(
			'id' => 'sidebar_headings_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#sidebar .widget-title',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'sidebar_headings_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#sidebar .widget-title',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'sidebar_headings_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => '#sidebar .widget-title',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'sidebar_headings_align',
			'transport' => 'postMessage',
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
			'inline_css' => array(
				'target' => '#sidebar .widget-title',
				'alter' => 'text-align',
			),
		),
	),
);