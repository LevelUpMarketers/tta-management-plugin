<?php
/**
 * Social Share
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$social_share_items = wpex_social_share_items();

if ( $social_share_items ) {

	$social_share_choices = array();

	foreach ( $social_share_items as $k => $v ) {
		$social_share_choices[$k] = $v['site'];
	}

	$this->sections['wpex_social_sharing'] = array(
		'title'  => esc_html__( 'Social Share Buttons', 'total' ),
		'panel'  => 'wpex_general',
		'settings' => array(
			array(
				'id' => 'social_share_shortcode',
				'transport' => 'partialRefresh',
				'control' => array(
					'label' => esc_html__( 'Alternative Shortcode', 'total' ),
					'type' => 'text',
					'description' => esc_html__( 'Override the theme default social share with your custom social sharing shortcode.', 'total' ),
				),
			),
			array(
				'id'  => 'social_share_sites',
				'transport' => 'partialRefresh',
				'default' => array( 'twitter', 'facebook', 'linkedin', 'email' ),
				'control' => array(
					'label'  => esc_html__( 'Sites', 'total' ),
					'desc' => esc_html__( 'Click and drag and drop elements to re-order them.', 'total' ),
					'type' => 'wpex-sortable',
					'object' => 'WPEX_Customize_Control_Sorter',
					'choices' => $social_share_choices,
				),
			),
			array(
				'id' => 'social_share_position',
				'transport' => 'partialRefresh',
				'control' => array(
					'label' => esc_html__( 'Position', 'total' ),
					'type' => 'select',
					'choices' => array(
						'' => esc_html__( 'Default', 'total' ),
						'horizontal' => esc_html__( 'Horizontal', 'total' ),
						'vertical' => esc_html__( 'Vertical (Fixed)', 'total' ),
					),
				),
			),
			array(
				'id' => 'social_share_style',
				'transport' => 'partialRefresh',
				'default' => 'flat',
				'control' => array(
					'label' => esc_html__( 'Style', 'total' ),
					'type'  => 'select',
					'choices' => array(
						'flat' => esc_html__( 'Flat', 'total' ),
						'minimal' => esc_html__( 'Minimal', 'total' ),
						'three-d' => esc_html__( '3D', 'total' ),
						'rounded' => esc_html__( 'Rounded', 'total' ),
						'custom' => esc_html__( 'Custom', 'total' ),
					),
				),
			),
			array(
				'id' => 'social_share_label',
				'transport' => 'partialRefresh',
				'default' => true,
				'control' => array(
					'label' => esc_html__( 'Display Horizontal Style Label?', 'total' ),
					'type' => 'checkbox',
				),
			),
			array(
				'id' => 'social_share_font_size',
				'description' => esc_html__( 'Value in px or em.', 'total' ),
				'transport' => 'postMessage',
				'control' => array(
					'type' => 'text',
					'label' => esc_html__( 'Font Size', 'total' ),
					'input_attrs' => array(
						'placeholder' => '1em',
					),
				),
				'inline_css' => array(
					'target' => '.wpex-social-share li a',
					'alter' => 'font-size',
				),
			),
			array(
				'id' => 'social_share_heading',
				'transport' => 'partialRefresh',
				'default' => esc_html__( 'Share This', 'total' ),
				'control' => array(
					'label' => esc_html__( 'Horizontal Position Heading', 'total' ),
					'type'  => 'text',
					'description' => esc_html__( 'Leave blank to disable.', 'total' ),
				),
			),
			array(
				'id' => 'social_share_twitter_handle',
				'transport' => 'postMessage',
				'control' => array(
					'label' => esc_html__( 'Twitter Handle', 'total' ),
					'type' => 'text',
				),
			),
		)
	);

}