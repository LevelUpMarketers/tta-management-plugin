<?php
/**
 * Post Slider Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_post_slider'] = array(
	'title'  => esc_html__( 'Post Gallery Slider', 'total' ),
	'panel'  => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'post_slider_animation',
			'default' => 'slide',
			'control' => array(
				'label' => esc_html__( 'Animation', 'total' ),
				'type' => 'select',
				'choices' => array(
					'slide' => esc_html__( 'Slide', 'total' ),
					'fade' => esc_html__( 'Fade','total' ),
				),
			),
		),
		array(
			'id' => 'post_slider_animation_speed',
			'default' => '600',
			'control' => array(
				'label' => esc_html__( 'Custom Animation Speed', 'total' ),
				'type' => 'textfield',
				'description' => esc_html__( 'Enter a value in milliseconds.', 'total' ),
			),
		),
		array(
			'id' => 'post_slider_autoplay',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Auto Play?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'post_slider_loop',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Loop?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'post_slider_thumbnails',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Thumbnails?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'post_slider_arrows',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Arrows?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'post_slider_arrows_on_hover',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Arrows on Hover?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'post_slider_dots',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Dots?', 'total' ),
				'type' => 'checkbox',
			),
		),
	),
);