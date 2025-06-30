<?php
/**
 * Tribe Events Customizer Settings
 *
 * @package Total WordPress Theme
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// General
$this->sections['wpex_tribe_events'] = array(
	'settings' => array(
		array(
			'id' => 'tribe_events_total_styles',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Load Custom Theme Styles?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'tribe_events_main_page',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Events Page', 'total' ),
				'type' => 'dropdown-pages',
				'description' => esc_html__( 'Select the page being used as your main Events page.', 'total' ),
			),
		),
		array(
			'id' => 'tribe_events_archive_layout',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Archives Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'tribe_events_single_layout',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Single Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'tribe_events_page_header_details',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Event Details in Page Header?', 'total' ),
				'type' => 'checkbox',
				'description' => esc_html__( 'If the page header is disabled the event details will display in the default location.', 'total' ),
			),
		),
	),
);