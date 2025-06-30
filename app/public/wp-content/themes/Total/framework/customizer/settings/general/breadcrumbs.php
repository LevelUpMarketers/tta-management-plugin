<?php
/**
 * Breadcrumbs Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_breadcrumbs'] = array(
	'title' => esc_html__( 'Breadcrumbs', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'breadcrumbs',
			'transport' => 'partialRefresh',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Breadcrumbs?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'breadcrumbs_visibility',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Visibility', 'total' ),
				'type' => 'wpex-visibility-select',
			),
		),
		array(
			'id' => 'breadcrumbs_position',
			'transport' => 'refresh', // IMPORTANT !!!!
			'default' => 'page_header_aside',
			'control' => array(
				'label' => esc_html__( 'Location', 'total' ),
				'type'  => 'select',
				'choices' => array(
					'page_header_aside'   => esc_html__( 'Page Header Aside', 'total' ),
					'page_header_content' => esc_html__( 'Page Header Content', 'total' ),
					'page_header_after'   => esc_html__( 'After Page Header', 'total' ),
					'header_after'        => esc_html__( 'After Site Header', 'total' ),
					'custom'              => esc_html__( 'Custom Child Theme Location', 'total' ),
				),
				'active_callback' => 'wpex_cac_has_breadcrumbs',
			),
		),
		array(
			'id' => 'breadcrumbs_home_title',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Custom Home Title', 'total' ),
				'type'  => 'text',
			),
		),
		array(
			'id' => 'breadcrumbs_disable_taxonomies',
			'transport' => 'partialRefresh',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Remove categories and other taxonomies from breadcrumbs?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'breadcrumbs_first_cat_only',
			'transport' => 'partialRefresh',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display First Category Only?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'breadcrumbs_disable_taxonomies',
				'value' => 'false',
			),
		),
		array(
			'id' => 'breadcrumbs_title_trim',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Title Trim Length', 'total' ),
				'type'  => 'text',
				'desc'  => esc_html__( 'Enter the max number of words to display for your breadcrumbs post title.', 'total' ),
			),
		),
		array(
			'id' => 'breadcrumbs_separator',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Separator', 'total' ),
				'type' => 'text',
				'desc'  => esc_html__( 'Enter an HTML entity, keyboard character or shortcode.', 'total' ),
			),
		),
		array(
			'id' => 'breadcrumbs_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background Color', 'total' ),
				'active_callback' => 'wpex_cac_has_breadcrumbs',
			),
			'inline_css' => array(
				'target' => '.site-breadcrumbs',
				'alter' => 'Background-color',
			),
		),
		array(
			'id' => 'breadcrumbs_text_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Text Color', 'total' ),
				'active_callback' => 'wpex_cac_has_breadcrumbs',
			),
			'inline_css' => array(
				'target' => '.site-breadcrumbs',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'breadcrumbs_seperator_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Separator Color', 'total' ),
				'active_callback' => 'wpex_cac_has_breadcrumbs',
			),
			'inline_css' => array(
				'target' => '.site-breadcrumbs .sep',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'breadcrumbs_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color', 'total' ),
				'active_callback' => 'wpex_cac_has_breadcrumbs',
			),
			'inline_css' => array(
				'target' => '.site-breadcrumbs a',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'breadcrumbs_link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color: Hover', 'total' ),
				'active_callback' => 'wpex_cac_has_breadcrumbs',
			),
			'inline_css' => array(
				'target' => '.site-breadcrumbs a:hover',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'breadcrumbs_py',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Vertical Padding', 'total' ),
				'type'  => 'select',
				'choices' => wpex_utl_paddings(),
			),
		),
		array(
			'id' => 'breadcrumbs_mt',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Top Margin', 'total' ),
				'type'  => 'select',
				'choices' => wpex_utl_margins(),
			),
		),
		array(
			'id' => 'breadcrumbs_mb',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Bottom Margin', 'total' ),
				'type'  => 'select',
				'choices' => wpex_utl_margins(),
			),
		),
	),
);
