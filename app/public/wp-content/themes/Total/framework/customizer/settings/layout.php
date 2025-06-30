<?php
/**
 * Layout Panel
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// General
$this->sections['wpex_layout_general'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'panel' => 'wpex_layout',
	'settings' => array(
		array(
			'id' => 'container_max_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Max Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '90%',
				),
				'description' => esc_html__( 'Used for the fluid design. By default the main container will never be larger than 90% of the browser screen size.', 'total' ),
			),
			'inline_css' => array(
				'target' => 'body.wpex-responsive .container,.boxed-main-layout.wpex-responsive #wrap',
				'alter' => 'max-width',
			),
			'control_display' => array(
				'check' => 'responsive',
				'value' => 'true',
			),
		),
		array(
			'id' => 'content_layout',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Content Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
				'desc' => esc_html__( 'Select your default content layout for your site. You can always browse to different tabs in the Customizer such as the blog tab to alter your layout specifically for your blog archives and posts.', 'total' ),
			),
		),
		array(
			'id' => 'main_layout_style',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Site Layout Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'full-width' => esc_html__( 'Full Width','total' ),
					'boxed' => esc_html__( 'Boxed','total' )
				),
			),
		),
		array(
			'id' => 'boxed_dropdshadow',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Boxed Layout Drop Shadow?', 'total' ),
				'type' => 'checkbox',
			),
			'control_display' => array(
				'check' => 'main_layout_style',
				'value' => array( 'boxed' ),
			),
		),
		array(
			'id' => 'boxed_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Outer Margin', 'total' ),
				'input_attrs' => array(
					'placeholder' => '40px 30px',
				),
			),
			'control_display' => array(
				'check' => 'main_layout_style',
				'value' => array( 'boxed' ),
			),
			'inline_css' => array(
				'target' => '.boxed-main-layout #outer-wrap',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'boxed_wrap_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Inner Background', 'total' ),
			),
			'control_display' => array(
				'check' => 'main_layout_style',
				'value' => array( 'boxed' ),
			),
			'inline_css' => array(
				'target' => '.boxed-main-layout #wrap,.is-sticky #site-header',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'site_frame_border',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Site Frame Border?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'If enabled it will add a 10px fixed color frame around your site content.', 'total' ),
			),
		),
		array(
			'id' => 'site_frame_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Site Frame Border Color', 'total' ),
				'type' => 'color',
			),
			'inline_css' => array(
				'target' => '#wpex-sfb-l,#wpex-sfb-r,#wpex-sfb-t,#wpex-sfb-b',
				'alter' => 'background-color',
			),
			'control_display' => array(
				'check' => 'site_frame_border',
				'value' => 'true',
			),
		),
	),
);

// Desktop Widths
$this->sections['wpex_layout_desktop_widths'] = array(
	'title' => esc_html__( 'Desktop Widths', 'total' ),
	'panel' => 'wpex_layout',
	'desc' => esc_html__( 'For screens greater than or equal to 960px.', 'total' ),
	'settings' => array(
		array(
			'id' => 'main_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Main Container Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '980px',
				),
			),
			'inline_css' => array(
				'target' => '.container,.boxed-main-layout #wrap',
				'alter' => 'width',
			),
		),
		array(
			'id' => 'left_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Content with Sidebar Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '69%',
				),
			),
			'inline_css' => array(
				'media_query' => '(min-width: 960px)',
				'target' => 'body.has-sidebar .content-area, .wpex-content-w',
				'alter' => 'width',
			),
		),
		array(
			'id' => 'sidebar_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Sidebar Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '26%',
				),
			),
			'inline_css' => array(
				'media_query' => '(min-width: 960px)',
				'target' => '#sidebar',
				'alter' => 'width',
			),
		),
	),
);

// Medium Screen Widths
$this->sections['wpex_layout_medium_widths'] = array(
	'title' => esc_html__( 'Medium Screens Widths', 'total' ),
	'panel' => 'wpex_layout',
	'desc' => esc_html__( 'For screens between 960px - 1280px. Such as landscape tablets and small monitors/laptops.', 'total' ),
	'settings' => array(
		array(
			'id' => 'tablet_landscape_main_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Main Container Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => esc_html__( 'inherit from desktop widths', 'total' ),
				),
			),
			'inline_css' => array(
				'target' => '.container,.boxed-main-layout #wrap',
				'alter' => 'width',
				'media_query' => '(min-width: 960px) and (max-width: 1280px)',
			),
		),
		array(
			'id' => 'tablet_landscape_left_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Content with Sidebar Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => esc_html__( 'inherit from desktop widths', 'total' ),
				),
			),
			'inline_css' => array(
				'target' => 'body.has-sidebar .content-area, .wpex-content-w',
				'alter' => 'width',
				'media_query' => '(min-width: 960px) and (max-width: 1280px)',
			),
		),
		array(
			'id' => 'tablet_landscape_sidebar_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Sidebar Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => esc_html__( 'inherit from desktop widths', 'total' ),
				),
			),
			'inline_css' => array(
				'target' => '#sidebar',
				'alter' => 'width',
				'media_query' => '(min-width: 960px) and (max-width: 1280px)',
			),
		),
	),
);

// Tablet Portrait Widths
$this->sections['wpex_layout_tablet_widths'] = array(
	'title' => esc_html__( 'Tablet Widths', 'total' ),
	'panel' => 'wpex_layout',
	'desc' => esc_html__( 'For screens between 768px - 959px. Such as portrait tablet.', 'total' ),
	'settings' => array(
		array(
			'id' => 'tablet_main_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Main Container Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => esc_html__( 'inherit from desktop widths', 'total' ),
				),
			),
			'inline_css' => array(
				'target' => '.container,.boxed-main-layout #wrap',
				'alter' => 'width',
				'media_query' => '(min-width: 768px) and (max-width: 959px)',
			),
		),
		array(
			'id' => 'tablet_left_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Content with Sidebar Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '100%',
				),
			),
			'inline_css' => array(
				'target' => 'body.has-sidebar .content-area, .wpex-content-w',
				'alter' => 'width',
				'media_query' => '(min-width: 768px) and (max-width: 959px)',
			),
		),
		array(
			'id' => 'tablet_sidebar_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Sidebar Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '100%',
				),
			),
			'inline_css' => array(
				'target' => '#sidebar',
				'alter' => 'width',
				'media_query' => '(min-width: 768px) and (max-width: 959px)',
			),
		),
	),
);

// Mobile Phone Widths
$this->sections['wpex_layout_phone_widths'] = array(
	'title' => esc_html__( 'Mobile Phone Widths', 'total' ),
	'panel' => 'wpex_layout',
	'desc' => esc_html__( 'For screens between 0 - 767px.', 'total' ),
	'settings' => array(
		array(
			'id' => 'mobile_landscape_main_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Landscape: Main Container Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => esc_html__( 'inherit from max-width', 'total' ),
				),
			),
			'inline_css' => array(
				'target' => '.container,.boxed-main-layout #wrap',
				'alter' => 'width',
				'media_query' => '(min-width: 480px) and (max-width: 767px)',
			),
		),
		array(
			'id' => 'mobile_portrait_main_container_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Portrait: Main Container Width', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => esc_html__( 'inherit from max-width', 'total' ),
				),
			),
			'inline_css' => array(
				'target' => '.container,.boxed-main-layout #wrap',
				'alter' => 'width',
				'media_query' => '(max-width: 767px)',
			),
		),
	),
);