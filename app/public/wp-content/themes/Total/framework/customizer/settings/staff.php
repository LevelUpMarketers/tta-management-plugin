<?php
/**
 * Staff Customizer Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get staff taxonomies
$staff_taxonomies = array(
	'null' => esc_html__( 'Anything', 'total' ),
);
$get_staff_taxonomies = get_object_taxonomies( 'staff' );
if ( $get_staff_taxonomies ) {
	foreach( $get_staff_taxonomies as $tax ) {
		$staff_taxonomies[$tax] = get_taxonomy( $tax )->labels->name;
	}
}

// Single blocks
$blocks = apply_filters( 'wpex_staff_single_blocks', array(
	'title'    => esc_html__( 'Post Title', 'total' ),
	'meta'     => esc_html__( 'Meta', 'total' ),
	'media'    => esc_html__( 'Media', 'total' ),
	'content'  => esc_html__( 'Content', 'total' ),
	'social'   => esc_html__( 'Social Links', 'total' ),
	'share'    => esc_html__( 'Social Share Buttons', 'total' ),
	'comments' => esc_html__( 'Comments', 'total' ),
	'related'  => esc_html__( 'Related Posts', 'total' ),
), 'customizer' );

// Archives
$this->sections['wpex_staff_archives'] = array(
	'title' => esc_html__( 'Archives', 'total' ),
	'panel' => 'wpex_staff',
	'desc' => esc_html__( 'The following options are for the post type category and tag archives.', 'total' ),
	'settings' => array(
		array(
			'id' => 'staff_archive_layout',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Page Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'staff_archive_template_id',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => esc_html__( 'Select a template to override the default output for the post type archive, category and tag entries.', 'total' ),
			),
		),
		array(
			'id' => 'staff_entry_card_style',
			'control' => array(
				'label' => esc_html__( 'Card Style', 'total' ),
				'type' => 'wpex-card-select',
				'description' => esc_html__( 'Select a card style to override the default entry design using a preset theme card.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_staff_archive_tempate_id',
			),
		),
		array(
			'id' => 'staff_archive_grid_style',
			'default' => 'fit-rows',
			'control' => array(
				'label' => esc_html__( 'Grid Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'fit-rows' => esc_html__( 'Fit Rows','total' ),
					'masonry' => esc_html__( 'Masonry','total' ),
					'no-margins' => esc_html__( 'No Margins','total' ),
				),
				'active_callback' => 'wpex_cac_hasnt_staff_archive_tempate_id',
			),
		),
		array(
			'id' => 'staff_archive_grid_equal_heights',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Enable Equal Heights?', 'total' ),
				'desc' => esc_html__( 'If enabled it will set the content of each entry so they are the same height.', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_staff_supports_equal_heights',
			),
		),
		array(
			'id' => 'staff_entry_columns',
			'default' => '3',
			'control' => array(
				'label' => esc_html__( 'Columns', 'total' ),
				'type' => 'wpex-columns',
				'active_callback' => 'wpex_cac_hasnt_staff_archive_tempate_id',
			),
		),
		array(
			'id' => 'staff_archive_grid_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
				'active_callback' => 'wpex_cac_hasnt_staff_archive_tempate_id',
			),
		),
		array(
			'id' => 'staff_archive_posts_per_page',
			'default' => '12',
			'control' => array(
				'label' => esc_html__( 'Posts Per Page', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'staff_entry_overlay_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Image Overlay', 'total' ),
				'type' => 'select',
				'choices' => $overlay_styles,
				'active_callback' => 'wpex_cac_hasnt_staff_archive_tempate_id',
			),
		),
		array(
			'id' => 'staff_entry_image_hover_animation',
			'control' => array(
				'label' => esc_html__( 'Image Hover Animation', 'total' ),
				'type' => 'select',
				'choices' => wpex_image_hovers(),
				'active_callback' => 'wpex_cac_hasnt_staff_card',
			),
		),
		array(
			'id' => 'staff_entry_details',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Show Details?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_staff_card',
			),
		),
		array(
			'id' => 'staff_entry_position',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Position?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_staff_card',
			),
		),
		array(
			'id' => 'staff_entry_excerpt_length',
			'default' => '20',
			'control' => array(
				'label' => esc_html__( 'Excerpt length', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Enter 0 or leave blank to disable, enter -1 to display the full post content.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_staff_archive_tempate_id',
			),
		),
		array(
			'id' => 'staff_entry_social',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Social Links?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_staff_card',
			),
		),
	),
);

// Single
$this->sections['wpex_staff_single'] = array(
	'title' => esc_html__( 'Single Post', 'total' ),
	'panel' => 'wpex_staff',
	'settings' => array(
		array(
			'id' => 'staff_singular_page_title',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Page Header Title?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_has_page_header',
			),
		),
		array(
			'id' => 'staff_next_prev',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Next/Previous Links?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'staff_single_header_position',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Show Position as Subheading?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'staff_single_layout',
			'control' => array(
				'label' => esc_html__( 'Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'staff_singular_template',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => $template_desc,
			),
		),
		array(
			'id' => 'staff_post_composer',
			'default' => 'content,related',
			'control' => array(
				'label' => esc_html__( 'Post Layout Elements', 'total' ),
				'type' => 'wpex-sortable',
				'choices' => $blocks,
				'desc' => esc_html__( 'Click and drag and drop elements to re-order them.', 'total' ),
				'active_callback' => 'wpex_cac_staff_single_hasnt_custom_template',
			),
		),
	),
);

// Related
$this->sections['wpex_staff_single_related'] = array(
	'title' => esc_html__( 'Related Posts', 'total' ),
	'panel' => 'wpex_staff',
	'desc' => esc_html__( 'The related posts section displays at the bottom of the post content and can be enabled/disabled via the Post Layout Elements setting under the "Single Post" tab.', 'total' ),
	'settings' => array(
		array(
			'id' => 'staff_related_title',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Related Posts Title', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'staff_related_entry_card_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Card Style', 'total' ),
				'type' => 'wpex-card-select',
				'description' => esc_html__( 'Select a card style to override the default entry design using a preset theme card.', 'total' ),
			),
		),
		array(
			'id' => 'staff_related_count',
			'default' => '3',
			'control' => array(
				'label' => esc_html__( 'Post Count', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'staff_related_taxonomy',
			'default' => taxonomy_exists( 'staff_category' ) ? 'staff_category' : 'none',
			'control' => array(
				'label' => esc_html__( 'Related By', 'total' ),
				'type' => 'select',
				'choices' => $staff_taxonomies,
			),
		),
		array(
			'id' => 'staff_related_order',
			'default' => 'desc',
			'control' => array(
				'label' => esc_html__( 'Order', 'total' ),
				'type' => 'select',
				'choices' => array(
					'desc' => esc_html__( 'DESC', 'total' ),
					'asc' => esc_html__( 'ASC', 'total' ),
				),
			),
		),
		array(
			'id' => 'staff_related_orderby',
			'default' => 'date',
			'control' => array(
				'label' => esc_html__( 'Order By', 'total' ),
				'type' => 'select',
				'choices' => array(
					'date'          => esc_html__( 'Date', 'total' ),
					'title'         => esc_html__( 'Title', 'total' ),
					'modified'      => esc_html__( 'Modified', 'total' ),
					'author'        => esc_html__( 'Author', 'total' ),
					'rand'          => esc_html__( 'Random', 'total' ),
					'comment_count' => esc_html__( 'Comment Count', 'total' ),
				),
			),
		),
		array(
			'id' => 'staff_related_columns',
			'default' => '3',
			'control' => array(
				'label' => esc_html__( 'Columns', 'total' ),
				'type' => 'wpex-columns',
			),
		),
		array(
			'id' => 'staff_related_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
			),
		),
		array(
			'id' => 'staff_related_entry_overlay_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Image Overlay', 'total' ),
				'type' => 'select',
				'choices' => $overlay_styles
			),
		),
		array(
			'id' => 'staff_related_entry_excerpt_length',
			'default' => '20',
			'control' => array(
				'label' => esc_html__( 'Excerpt length', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Enter 0 or leave blank to disable, enter -1 to display the full post content.', 'total' ),
			),
		),
		array(
			'id' => 'staff_related_excerpts',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Details for Related Posts?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_staff_related_card',
			),
		),
	)
);

// Social Links
$this->sections['wpex_staff_social_links'] = array(
	'title' => esc_html__( 'Social Links', 'total' ),
	'panel' => 'wpex_staff',
	'settings' => array(
		array(
			'id' => 'staff_social_show_icons',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Use Icons?', 'total' ),
				'desc' => esc_html__( 'If disabled it will display text links.', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'staff_social_default_style',
			'default' => 'minimal-round',
			'control' => array(
				'label' => esc_html__( 'Default Social Style', 'total' ),
				'type' => 'select',
				'choices' => $social_styles,
			),
			'control_display' => array(
				'check' => 'staff_social_show_icons',
				'value' => 'true',
			),
		),
		array(
			'id' => 'staff_social_link_target',
			'default' => 'blank',
			'control' => array(
				'label' => esc_html__( 'Link Target', 'total' ),
				'type' => 'select',
				'choices' => array(
					'blank' => esc_html__( 'Blank', 'total' ),
					'self' => esc_html__( 'Self', 'total' ),
				),
			),
		),
		array(
			'id' => 'staff_social_font_size',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Font/Icon Size', 'total' ),
			),
			'inline_css' => array(
				'target' => '.staff-social',
				'alter' => 'font-size',
			),
		),
	)
);