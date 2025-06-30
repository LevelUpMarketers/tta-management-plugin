<?php
/**
 * Customizer => Top Bar
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// General
$this->sections['wpex_topbar_general'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'panel' => 'wpex_topbar',
	'settings' => array(
		array(
			'id' => 'top_bar',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Top Bar?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'top_bar_visibility',
			'control' => array(
				'label' => esc_html__( 'Visibility', 'total' ),
				'type' => 'wpex-visibility-select',
			),
		),
		array(
			'id' => 'top_bar_style',
			'default' => 'one',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Layout', 'total' ),
				'type' => 'select',
				'choices' => array(
					'one' => esc_html__( 'Default', 'total' ),
					'two' => esc_html__( 'Reverse', 'total' ),
					'three' => esc_html__( 'Centered', 'total' ),
				),
			),
		),
		array(
			'id' => 'topbar_split_breakpoint',
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Responsive Breakpoint', 'total' ),
				'type' => 'select',
				'choices' => wpex_utl_breakpoints(),
				'description' => esc_html__( 'Select the breakpoint at which point the top bar is split into a left/right layout.' ),
			),
		),
		array(
			'id' => 'topbar_alignment',
			'transport' => 'refresh',
			'control' => array(
				'label' => esc_html__( 'Collapsed Alignment', 'total' ),
				'type' => 'select',
				'choices' => array(
					''       => esc_html__( 'Default', 'total' ),
					'left'   => esc_html__( 'Left', 'total' ),
					'center' => esc_html__( 'Center', 'total' ),
					'right'  => esc_html__( 'Right', 'total' ),
				),
			),
		),
		// Sticky
		array(
			'id' => 'top_bar_sticky_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Sticky', 'total' ),
			),
		),
		array(
			'id' => 'top_bar_sticky',
			'default' => false,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Sticky Top Bar?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Disabled in Customizer for optimization reasons. Please save and test live site.', 'total' ),
			),
		),
		array(
			'id' => 'top_bar_sticky_mobile',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Sticky Mobile Support?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Disabled in Customizer for optimization reasons. Please save and test live site.', 'total' ),
			),
		),

		// Design
		array(
			'id' => 'top_bar_design_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Design', 'total' ),
			),
		),
		array(
			'id' => 'top_bar_fullwidth',
			'default' => false,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Full Width Top Bar?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Not used in Boxed style layout.', 'total' ),
			),
		),
		array(
			'id' => 'top_bar_bottom_border',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Enable Bottom Border?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'top_bar_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#top-bar-wrap',
					'.wpex-top-bar-sticky',
				),
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'top_bar_border',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Borders', 'total' ),
			),
			'inline_css' => array(
				'target' => '#top-bar-wrap',
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'top_bar_text',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Color', 'total' ),
			),
			'inline_css' => array(
				'target' => array(
					'#top-bar-wrap',
					'#top-bar-content strong',
				),
				'alter' => 'color',
			),
		),
		// link colors
		array(
			'id' => 'top_bar_link_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '#top-bar a:not(.theme-button):not(.wpex-social-btn)',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'top_bar_link_color_hover',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Link Color: Hover', 'total' ),
			),
			'inline_css' => array(
				'target' => '#top-bar a:not(.theme-button):not(.wpex-social-btn):hover',
				'alter' => 'color',
			),
		),
		// Padding
		array(
			'id' => 'top_bar_top_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Top Padding', 'total' ),
				'description' => $pixel_desc,
			),
			'inline_css' => array(
				'target' => '#top-bar',
				'alter' => 'padding-top',
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'top_bar_bottom_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Bottom Padding', 'total' ),
				'description' => $pixel_desc,
			),
			'inline_css' => array(
				'target' => '#top-bar',
				'alter' => 'padding-bottom',
				'sanitize' => 'px',
			),
		),
	),
);

/*-----------------------------------------------------------------------------------*/
/* - TopBar => Content
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_topbar_content'] = array(
	'title' => esc_html__( 'Content', 'total' ),
	'panel' => 'wpex_topbar',
	'settings' => array(
		array(
			'id' => 'top_bar_content',
			'transport' => 'partialRefresh',
			'default' => '<span class="wpex-inline">[font_awesome icon="phone"] 1-800-987-654</span>

<span class="wpex-inline">[font_awesome icon="envelope"] admin@totalwptheme.com</span>

<span class="wpex-inline">[font_awesome icon="user"] [wp_login_url text="User Login" logout_text="Logout"]</span>',
			'control' => array(
				'label' => esc_html__( 'Content', 'total' ),
				'type' => 'wpex-textarea',
				'rows' => 25,
				'description' => $post_id_content_desc,
			),
			'control_display' => array(
				'check' => 'top_bar',
				'value' => 'true'
			),
		),
	),
);

/*-----------------------------------------------------------------------------------*/
/* - TopBar => Social
/*-----------------------------------------------------------------------------------*/
$this->sections['wpex_topbar_social'] = array(
	'title' => esc_html__( 'Social', 'total' ),
	'panel' => 'wpex_topbar',
	'settings' => array(
		array(
			'id' => 'top_bar_social_alt',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Social Alternative', 'total' ),
				'type' => 'textarea',
				'description' => esc_html__( 'Replaces the social links with your custom output.', 'total' ) . $post_id_content_desc,
			),
		),
		array(
			'id' => 'top_bar_social',
			'default' => true,
			'transport' => 'refresh', // Other items relly on this conditionally to show/hide
			'control' => array(
				'label' => esc_html__( 'Enable Social Links?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'top_bar_social_target',
			'default' => 'blank',
			'transport' => 'postMessage', // Doesn't need any js because you can't click links in the customizer anyway
			'control' => array(
				'label' => esc_html__( 'Social Link Target', 'total' ),
				'type' => 'select',
				'choices' => array(
					'blank' => esc_html__( 'New Window', 'total' ),
					'self' => esc_html__( 'Same Window', 'total' ),
				),
			),
		),

		// Design
		array(
			'id' => 'top_bar_social_design_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Icons Design', 'total' ),
			),
		),
		array(
			'id' => 'top_bar_social_style',
			'default' => 'none',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Social Style', 'total' ),
				'type' => 'select',
				'choices' => $social_styles,
			),
		),
		array(
			'id' => 'top_bar_social_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Social Links Color', 'total' ),
				'description' => esc_html__( 'Applied only when the social style is set to "none".', 'total' ),
			),
			'inline_css' => array(
				'target' => '#top-bar-social a.wpex-social-btn-no-style',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'top_bar_social_hover_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Social Links Hover Color', 'total' ),
				'description' => esc_html__( 'Applied only when the social style is set to "none".', 'total' ),
			),
			'inline_css' => array(
				'target' => '#top-bar-social a.wpex-social-btn-no-style:hover',
				'alter' => 'color',
			),
		),
	),
);

// Social settings
$social_options = wpex_topbar_social_options();

if ( $social_options ) {

	$this->sections['wpex_topbar_social']['settings'][] = array(
		'id' => 'top_bar_social_profiles_heading',
		'control' => array(
			'type' => 'wpex-heading',
			'label' => esc_html__( 'Social Profiles', 'total' ),
		),
	);

	foreach ( $social_options as $key => $val ) {
		$this->sections['wpex_topbar_social']['settings'][] = array(
			'id' => 'top_bar_social_profiles[' . $key .']',
			'transport' => 'partialRefresh',
			'sanitize_callback' => 'esc_html',
			'control' => array(
				'label' => esc_html( $val['label'], 'total' ),
				'type' => 'text',
			),
		);
	}

}