<?php
/**
 * Footer Customizer Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 *
 * @todo rename mods from _callout_ to _footer_callout_
 */

defined( 'ABSPATH' ) || exit;

// General
$this->sections['wpex_callout_general'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'panel' => 'wpex_callout',
	'settings' => array(
		array(
			'id' => 'callout',
			'transport' => 'partialRefresh',
			'default' => 1,
			'control' => array(
				'label' => esc_html__( 'Enable Callout?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'callout_visibility',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Visibility', 'total' ),
				'type' => 'wpex-visibility-select',
			),
		),
		array(
			'id' => 'footer_callout_breakpoint',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Responsive Breakpoint', 'total' ),
				'type' => 'select',
				'choices' => wpex_utl_breakpoints(),
			),
		),
		array(
			'id' => 'callout_top_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Top Padding', 'total' ),
				'description' => $pixel_desc,
			),
			'inline_css' => array(
				'target' => '#footer-callout-wrap',
				'alter' => 'padding-top',
			),
		),
		array(
			'id' => 'callout_bottom_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Bottom Padding', 'total' ),
				'description' => $pixel_desc,
			),
			'inline_css' => array(
				'target' => '#footer-callout-wrap',
				'alter' => 'padding-bottom',
			),
		),
		array(
			'id' => 'footer_callout_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-callout-wrap',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'footer_callout_bg_img',
			'control' => array(
				'type' => 'media',
				'mime_type' => 'image',
				'label' => esc_html__( 'Background Image', 'total' ),
			),
		),
		array(
			'id' => 'footer_callout_bg_img_style',
			'transport' => 'postMessage',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Background Image Style', 'total' ),
				'type'  => 'select',
				'choices' => $bg_styles,
			),
			'control_display' => array(
				'check' => 'footer_callout_bg_img',
				'value' => 'true',
			),
		),
		array(
			'id' => 'footer_callout_border_width',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Width', 'total' ),
				'description' => $border_desc,
			),
			'inline_css' => array(
				'target' => '#footer-callout-wrap',
				'alter' => array( 'border-top-width', 'border-bottom-width' ),
			),
		),
		array(
			'id' => 'footer_callout_border',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-callout-wrap',
				'alter' =>  array( 'border-top-color', 'border-bottom-color' ),
			),
		),
		array(
			'id' => 'footer_callout_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Text Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-callout-wrap',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'footer_callout_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Links', 'total' ),
			),
			'inline_css' => array(
				'target' => '.footer-callout-content a',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'footer_callout_link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Links: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '.footer-callout-content a:hover',
				'alter' => 'color',
			),
		),
	)
);

// Aside
$this->sections['wpex_callout_aside_content'] = array(
	'title' => esc_html__( 'Content', 'total' ),
	'panel' => 'wpex_callout',
	'settings' => array(
		array(
			'id' => 'callout_text',
			'transport' => 'partialRefresh',
			'default' => 'I am the footer call-to-action block, here you can add some relevant/important information about your company or product. I can be disabled in the Customizer.',
			'control' => array(
				'label' => esc_html__( 'Text', 'total' ),
				'type' => 'textarea',
				'description' => $post_id_content_desc,
			),
		),
	)
);

// Button
$this->sections['wpex_callout_button'] = array(
	'title' => esc_html__( 'Button', 'total' ),
	'panel' => 'wpex_callout',
	'settings' => array(
		array(
			'id' => 'callout_link',
			'transport' => 'partialRefresh',
			'default' => '#',
			'sanitize_callback' => 'esc_url_raw',
			'control' => array(
				'label' => esc_html__( 'Link URL', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Leave empty to disable button.', 'total' ),
			),
		),
		array(
			'id' => 'callout_link_txt',
			'transport' => 'partialRefresh',
			'default' => 'Get In Touch',
			'control' => array(
				'label' => esc_html__( 'Link Text', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'callout_button_target',
			'transport' => 'postMessage',
			'default' => 'blank',
			'control' => array(
				'label' => esc_html__( 'Link Target', 'total' ),
				'type' => 'select',
				'choices' => array(
					'blank' => esc_html__( 'Blank', 'total' ),
					'self' => esc_html__( 'Self', 'total' ),
				),
			),
		),
		array(
			'id' => 'callout_button_rel',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Link Rel', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'None', 'total' ),
					'nofollow' => esc_html__( 'Nofollow', 'total' ),
				),
			),
		),
		array(
			'id' => 'callout_button_icon',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Icon Select', 'total' ),
				'type' => 'wpex-fa-icon-select',
			),
		),
		array(
			'id' => 'callout_button_icon_position',
			'transport' => 'partialRefresh',
			'default' => 'after_text',
			'control' => array(
				'label' => esc_html__( 'Icon Position', 'total' ),
				'type' => 'select',
				'choices' => array(
					'after_text' => esc_html__( 'After Text', 'total' ),
					'before_text' => esc_html__( 'Before Text', 'total' ),
				),
			),
		),
		array(
			'id' => 'callout_button_style',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Button Style', 'total' ),
				'type' => 'select',
				'choices' => wpex_button_styles(),
			),
		),
		array(
			'id' => 'callout_button_color',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Button Color', 'total' ),
				'type' => 'select',
				'choices' => wpex_button_colors(),
			),
		),
		array(
			'id' => 'callout_button_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'description' => $padding_desc,
			),
			'inline_css' => array(
				'target' => '#footer-callout .theme-button',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'callout_button_border_radius',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Radius', 'total' ),
				'description' => $pixel_desc,
			),
			'inline_css' => array(
				'target' => '#footer-callout .theme-button',
				'alter' => 'border-radius',
				'important' => 'true',
			),
		),
		array(
			'id' => 'footer_callout_button_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-callout .theme-button',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'footer_callout_button_hover_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-callout .theme-button:hover',
				'alter' => 'background',
			),
		),
		array(
			'id' => 'footer_callout_button_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-callout .theme-button',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'footer_callout_button_hover_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#footer-callout .theme-button:hover',
				'alter' => 'color',
			),
		),
	),
);