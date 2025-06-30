<?php
/**
 * LearnDash Customizer Settings
 *
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $post_layouts ) ) {
	$post_layouts = wpex_get_post_layouts();
}

// General
$this->sections['wpex_learndash_general'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'panel' => 'wpex_learndash',
	'settings' => array(
		array(
			'id' => 'learndash_wpex_metabox',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Editor Settings Metabox?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'learndash_breadcrumbs',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Show Breadcrumbs?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'learndash_layout',
			'control' => array(
				'label' => esc_html__( 'Default LearnDash Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
	),
);

$sections = array(
	'courses'      => esc_html__( 'Courses', 'total' ),
	'lessons'      => esc_html__( 'Lessons', 'total' ),
	'topic'        => esc_html__( 'Topics', 'total' ),
	'quiz'         => esc_html__( 'Quizzes', 'total' ),
	'question'     => esc_html__( 'Questions', 'total' ),
	'certificates' => esc_html__( 'Certificates', 'total' ),
	'assignment'   => esc_html__( 'Assignments', 'total' ),
);

$entry_blocks = array(
	'media'    => esc_attr__( 'Media (Thumbnail, Slider, Video)', 'total' ),
	'title'    => esc_attr__( 'Title', 'total' ),
	'meta'     => esc_attr__( 'Meta', 'total' ),
	'content'  => esc_attr__( 'Content', 'total' ),
	'readmore' => esc_attr__( 'Readmore', 'total' ),
);
$entry_defaults = array_keys( $entry_blocks );

$single_blocks = array(
	'media'       => esc_attr__( 'Media (Thumbnail, Slider, Video)', 'total' ),
	'title'       => esc_attr__( 'Title', 'total' ),
	'meta'        => esc_attr__( 'Meta', 'total' ),
	'content'     => esc_attr__( 'Content', 'total' ),
	'page-links'  => esc_attr__( 'Page Links', 'total' ),
	'share'       => esc_attr__( 'Social Share', 'total' ),
	'comments'    => esc_attr__( 'Comments', 'total' ),
);

$single_defaults = array_keys( $single_blocks );

foreach ( $sections as $section => $title ) {

	$settings = array();

	$post_type = 'sfwd-' . $section;

	// Bail if post type not registered
	if ( ! post_type_exists( $post_type ) ) {
		continue;
	}

	// Archive Settings (some types don't have archives )
	if ( in_array( $section, array( 'courses', 'lessons', 'question' ) ) ) {

		$settings[] = array(
			'id' => $post_type . '_archives_layout',
			'control' => array(
				'label' => esc_html__( 'Archives Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		);

		$settings[] = array(
			'id' => $post_type . '_grid_entry_columns',
			'default' => '1',
			'control' => array(
				'label' => esc_html__( 'Archive Grid Columns', 'total' ),
				'type' => 'select',
				'choices' => wpex_grid_columns(),
			),
		);

		$settings[] = array(
			'id' => $post_type . '_readmore_text',
			'control' => array(
				'label' => esc_html__( 'Custom Read More Text', 'total' ),
				'type' => 'text',
			),
		);

		$settings[] = array(
			'id' => $post_type . '_entry_blocks',
			'default' => $entry_defaults,
			'control' => array(
				'label' => esc_html__( 'Entry Blocks', 'total' ),
				'type' => 'multiple-select',
				'choices' => $entry_blocks,
			),
		);

	}

	// Single settings
	$settings[] = array(
		'id' => $post_type . '_single_layout',
		'control' => array(
			'label' => esc_html__( 'Single Layout', 'total' ),
			'type' => 'select',
			'choices' => $post_layouts,
		),
	);

	$settings[] = array(
		'id' => $post_type . '_single_header',
		'control' => array(
			'label' => esc_html__( 'Single Header Display', 'total' ),
			'type' => 'select',
			'choices' => array(
				'' => esc_html__( 'Default','total' ),
				'post_title' => esc_html__( 'Post Title','total' ),
			),
		),
	);

	$settings[] = array(
		'id' => $post_type . '_singular_template',
		'control' => array(
			'label' => esc_html__( 'Single Dynamic Template', 'total' ),
			'type' => 'select',
			'type' => 'wpex-dropdown-templates',
			'desc' => $template_desc,
		),
	);

	$settings[] = array(
		'id' => $post_type . '_single_blocks',
		'default' => $single_defaults,
		'control' => array(
			'label' => esc_html__( 'Single Blocks', 'total' ),
			'type' => 'multiple-select',
			'choices' => $single_blocks,
		),
		'control_display' => array(
			'check' => $post_type . '_singular_template',
			'value' => '',
		),
	);

	$this->sections['wpex_learndash_' . $section ] = array(
		'title' => $title,
		'panel' => 'wpex_learndash',
		'settings' => $settings,
	);

}
