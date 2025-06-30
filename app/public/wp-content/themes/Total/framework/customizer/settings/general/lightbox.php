<?php
/**
 * Lightbox Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_lightbox'] = array(
	'title' => esc_html__( 'Lightbox', 'total' ),
	'panel' => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'lightbox_load_style_globally',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Load Scripts Globally?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'By default the lightbox scripts will only load as needed by the theme. You can enable this option to load the scripts globally on the whole site if needed or you can use the [wpex_lightbox_scripts] shortcode anywhere to load the scripts as needed.', 'total' ),
			),
		),
		array(
			'id' => 'lightbox_auto',
			'control' => array(
				'label' => esc_html__( 'Enable Auto Lightbox?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'Automatically add Lightbox to images inserted into the post editor.', 'total' ),
			),
		),
		array(
			'id' => 'lightbox_slideshow_autostart',
			'control' => array(
				'label' => esc_html__( 'Lightbox Gallery Slideshow Auto Start?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'lightbox_slideshow_speed',
			'default' => 3000,
			'control' => array(
				'label' => esc_html__( 'Gallery Slideshow Speed', 'total' ),
				'type' => 'text',
				'description' => esc_html__( 'Enter a value in milliseconds.', 'total' ),
			),
		),
		array(
			'id' => 'lightbox_animation_duration',
			'default' => 366,
			'control' => array(
				'label' => esc_html__( 'Duration in ms for the open/close animation.', 'total' ),
				'type' => 'text',
				'sanitize_callback' => 'absint',
			),
		),
		array(
			'id' => 'lightbox_transition_effect',
			'default' => 'fade',
			'control' => array(
				'label' => esc_html__( 'Transition Effect', 'total' ),
				'type' => 'select',
				'choices' => array(
					'fade' => esc_html__( 'Fade', 'total' ),
					'slide' => esc_html__( 'Slide', 'total' ),
					'circular' => esc_html__( 'Circular', 'total' ),
					'tube' => esc_html__( 'Tube', 'total' ),
					'zoom-in-out' => esc_html__( 'Zoom-In-Out', 'total' ),
				),
			),
		),
		array(
			'id' => 'lightbox_transition_duration',
			'default' => 366,
			'control' => array(
				'label' => esc_html__( 'Duration in ms for transition animation.', 'total' ),
				'type' => 'text',
				'sanitize_callback' => 'absint',
			),
		),
		array(
			'id' => 'lightbox_thumbnails',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Gallery Thumbnails Panel?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'lightbox_thumbnails_auto_start',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Auto Open Gallery Thumbnails Panel?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'lightbox_loop',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Enable Gallery Loop?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'lightbox_arrows',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Gallery Arrows?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'lightbox_fullscreen',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Display Fullscreen Button?', 'total' ),
				'type' => 'checkbox',
			),
		),
	),
);