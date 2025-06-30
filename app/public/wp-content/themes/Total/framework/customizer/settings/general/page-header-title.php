<?php
/**
 * Page Header Title Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_page_header'] = array(
	'title' => esc_html__( 'Page Header Title', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'page_header_style',
			//'transport' => 'postMessage', // needs refresh because of body class and active_callbacks
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type' => 'select',
				'choices' => $page_header_styles,
			),
		),
		array(
			'id' => 'page_header_breakpoint',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Responsive Breakpoint', 'total' ),
				'type' => 'select',
				'choices' => wpex_utl_breakpoints(),
				'description' => esc_html__( 'Option used for header styles that have content on the side such as breadcrumbs.', 'total' ),
			),
		),
		array(
			'id' => 'page_header_min_height',
			'transport' => 'postMessage',
			'control_display' => array(
				'check' => 'page_header_style',
				'value' => 'background-image',
			),
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Min-Height', 'total' ),
				'input_attrs' => array(
					'placeholder' => '400px',
				),
			),
			'inline_css' => array(
				'target' => '.page-header.background-image-page-header',
				'alter' => 'min-height',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'page_header_align_items',
			'transport' => 'postMessage',
			'default' => 'center',
			'control_display' => array(
				'check' => 'page_header_style',
				'value' => 'background-image',
			),
			'control' => array(
				'type' => 'select',
				'label' => esc_html__( 'Vertical Alignment', 'total' ),
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'start' => esc_html__( 'Top', 'total' ),
					'center' => esc_html__( 'Center', 'total' ),
					'end' => esc_html__( 'Bottom', 'total' ),
				),
			),
		),
		array(
			'id' => 'page_header_text_align',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'select',
				'label' => esc_html__( 'Text Align', 'total' ),
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'left' => esc_html__( 'Left', 'total' ),
					'center' => esc_html__( 'Center', 'total' ),
					'right' => esc_html__( 'Right', 'total' ),
				),
			),
		),
		array(
			'id' => 'page_header_hidden_main_top_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Hidden Page Header Title Spacing', 'total' ),
				'desc' => esc_html__( 'When the page header title is set to hidden there will not be any space between the header and the main content. If you want to add a default space between the header and your main content you can enter the px value here.', 'total' ),
				'input_attrs' => array(
					'placeholder' => '0px',
				),
			),
			'inline_css' => array(
				'target' => 'body.page-header-disabled #content-wrap',
				'alter' => 'padding-top',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'page_header_top_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Top Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '20px',
				),
			),
			'inline_css' => array(
				'target' => '.page-header.wpex-supports-mods',
				'alter' => 'padding-top',
			),
		),
		array(
			'id' => 'page_header_bottom_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Bottom Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '20px',
				),
			),
			'inline_css' => array(
				'target' => '.page-header.wpex-supports-mods',
				'alter' => 'padding-bottom',
			),
		),
		array(
			'id' => 'page_header_bottom_margin',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Bottom Margin', 'total' ),
				'input_attrs' => array(
					'placeholder' => '20px',
				),
			),
			'inline_css' => array(
				'target' => '.page-header',
				'alter' => 'margin-bottom',
			),
		),
		array(
			'id' => 'page_header_background',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-header.wpex-supports-mods',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'page_header_title_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Text Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-header.wpex-supports-mods .page-header-title',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'page_header_top_border',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Top Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-header.wpex-supports-mods',
				'alter' => 'border-top-color',
			),
		),
		array(
			'id' => 'page_header_bottom_border',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Bottom Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.page-header.wpex-supports-mods',
				'alter' => 'border-bottom-color',
			),
		),
		array(
			'id' => 'page_header_border_width',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Width', 'total' ),
				'input_attrs' => array(
					'placeholder' => '1px',
				),
			),
			'inline_css' => array(
				'target' => '.page-header.wpex-supports-mods',
				'alter' => array(
					'border-top-width',
					'border-bottom-width',
				),
			),
		),
		array(
			'id' => 'page_header_background_img',
			'transport' => 'refresh',
			'control' => array(
				'type' => 'media',
				'mime_type' => 'image',
				'label' => esc_html__( 'Background Image', 'total' ),
			),
		),
		array(
			'id' => 'page_header_background_img_style',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Background Image Style', 'total' ),
				'type'  => 'select',
				'active_callback' => 'wpex_cac_has_page_header_title_background',
				'choices' => $bg_styles,
			),
		),
		array(
			'id' => 'page_header_background_fetch_thumbnail',
			'control' => array(
				'type' => 'multiple-select',
				'label' => esc_html__( 'Fetch Background From Featured Image', 'total' ),
				'description' => esc_html__( 'Check the box next to any post type where you want to display the featured image as the page header title background.', 'total' ),
				'active_callback' => 'wpex_cac_has_page_header_title_background',
				'choices' => $post_types,
			),
		),
		array(
			'id' => 'page_header_subheading_location',
			'transport' => 'refresh',
			'default' => 'page_header_content',
			'control' => array(
				'label' => esc_html__( 'Subheading Location', 'total' ),
				'type'  => 'select',
				'choices' => array(
					''                    => esc_html__( 'Default', 'total' ),
					'page_header_content' => esc_html__( 'Page Header Content', 'total' ),
					'page_header_aside'   => esc_html__( 'Page Header Aside', 'total' ),
				),
			),
		),
	),
);
