<?php
/**
 * Overlays Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0.6
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_overlays'] = array(
	'title'    => esc_html__( 'Image Overlays', 'total' ),
	'panel'    => 'wpex_general',
	'settings' => array(
		array(
			'id'          => 'overlay_speed',
			'default'     => '300',
			'control'     => array(
				'label'   => esc_html__( 'Hover Speed', 'total' ),
				'type'    => 'select',
				'choices' => array(
					'75'   => '75ms',
					'100'  => '100ms',
					'150'  => '150ms',
					'200'  => '200ms',
					'300'  => '300ms',
					'500'  => '500ms',
					'700'  => '700ms',
					'1000' => '1000ms',
				),
			),
		),
		array(
			'id'          => 'overlay_opacity',
			'default'     => '60',
			'control'     => array(
				'label'   => esc_html__( 'Overlay Opacity', 'total' ),
				'type'    => 'select',
				'choices' => wpex_utl_opacities(),
			),
		),
		array(
			'id'          => 'overlay_bg',
			'default'     => 'black',
			'control'     => array(
				'label'   => esc_html__( 'Overlay Background', 'total' ),
				'type'    => 'select',
				'choices' => array(
					'black' => esc_html__( 'Black', 'total' ),
					'accent' => esc_html__( 'Accent', 'total' ),
					'accent_alt' => esc_html__( 'Accent Hover Color', 'total' ),
				),
			),
		),
	),
);