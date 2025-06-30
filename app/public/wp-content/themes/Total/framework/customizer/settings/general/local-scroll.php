<?php
/**
 * Local Scroll Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_local_scroll'] = array(
	'title'  => esc_html__( 'Local Scroll Links', 'total' ),
	'panel'  => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'scroll_to_hash',
			'default' => true,
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Scroll To Hash on Page Load?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'scroll_to_hash_timeout',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Scroll To Hash Timeout', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '500',
				),
				'description' => esc_html__( 'Time in milliseconds to wait before scrolling.', 'total' ),
			),
		),
		array(
			'id' => 'local_scroll_update_hash',
			'transport' => 'postMessage',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Update URL Hash When Clicking Local Scroll Links?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'local_scroll_highlight',
			'transport' => 'postMessage',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Highlight Menu Items on Local Scroll?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'local_scroll_speed',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Local Scroll Speed in Milliseconds', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => '1000',
				),
			),
		),
	),
);