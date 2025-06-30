<?php
/**
 * WPBakery Builder Customizer Settings
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_visual_composer'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'settings' => array(
		array(
			'id' => 'vc_row_bottom_margin',
			'default' => '40px',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Column Bottom Margin', 'total' ),
				'description' => esc_html__( 'Having a default bottom margin makes it easier for your website to be responsive so on mobile devices when columns stack they will automatically have space between them.', 'total' ),
			),
			'inline_css' => array(
				'target' => '.vc_column-inner',
				'alter' => 'margin-bottom',
			),
		),
		array(
			'id' => 'vcex_heading_default_tag',
			'default' => 'div',
			'control' => array(
				'type' => 'select',
				'label' => esc_html__( 'Heading Module Default HTML Tag', 'total' ),
				'choices' => array(
					'div'  => 'div',
					'span' => 'span',
					'h1'   => 'h1',
					'h2'   => 'h2',
					'h3'   => 'h3',
					'h4'   => 'h4',
					'h5'   => 'h5',
				),
			),
		),
		array(
			'id' => 'vcex_heading_typography_tag_styles',
			'default' => 0,
			'control' => array(
				'type' => 'checkbox',
				'label' => esc_html__( 'Apply the settings under Typography > h1, h2, h3, h4 to the heading module.', 'total' ),
			),
		),
	),
);
