<?php
/**
 * Page Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$blocks = apply_filters( 'wpex_page_single_blocks', array(
	'title'    => esc_html__( 'Title', 'total' ),
	'media'    => esc_html__( 'Media', 'total' ),
	'content'  => esc_html__( 'Content', 'total' ),
	'share'    => esc_html__( 'Social Share Buttons', 'total' ),
	'comments' => esc_html__( 'Comments', 'total' ),
), 'customizer' );

$this->sections['wpex_pages'] = array(
	'title'  => esc_html__( 'Pages', 'total' ),
	'panel'  => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'page_single_layout',
			'control' => array(
				'label' => esc_html__( 'Page Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'page_singular_page_title',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Page Header Title?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_has_page_header',
			),
		),
		array(
			'id' => 'pages_custom_sidebar',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Custom Sidebar?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'page_singular_template',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => $template_desc,
			),
		),
		array(
			'id' => 'page_composer',
			'default' => 'content',
			'control' => array(
				'label' => esc_html__( 'Post Layout Elements', 'total' ),
				'type' => 'wpex-sortable',
				'choices' => $blocks,
				'desc' => esc_html__( 'Click and drag and drop elements to re-order them.', 'total' ),
				'active_callback' => 'wpex_cac_page_single_hasnt_custom_template',
			),
		),
	),
);