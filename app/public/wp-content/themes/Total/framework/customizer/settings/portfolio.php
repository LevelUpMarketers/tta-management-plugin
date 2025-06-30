<?php
/**
 * Portfolio Customizer Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

// Get portfolio taxonomies
$portfolio_taxonomies = array(
	'null' => esc_html__( 'Anything', 'total' ),
);
$get_portfolio_taxonomies = get_object_taxonomies( 'portfolio' );
if ( $get_portfolio_taxonomies ) {
	foreach( $get_portfolio_taxonomies as $tax ) {
		$portfolio_taxonomies[$tax] = get_taxonomy( $tax )->labels->name;
	}
}

// Single Blocks
$blocks = apply_filters( 'wpex_portfolio_single_blocks', array(
	'title'    => esc_html__( 'Post Title', 'total' ),
	'meta'     => esc_html__( 'Post Meta', 'total' ),
	'media'    => esc_html__( 'Media', 'total' ),
	'content'  => esc_html__( 'Content', 'total' ),
	'share'    => esc_html__( 'Social Share Buttons', 'total' ),
	'comments' => esc_html__( 'Comments', 'total' ),
	'related'  => esc_html__( 'Related Posts', 'total' ),
), 'customizer' );

// Archives
$this->sections['wpex_portfolio_archives'] = array(
	'title' => esc_html__( 'Archives & Entries', 'total' ),
	'panel' => 'wpex_portfolio',
	'desc' => esc_html__( 'The following options are for the post type category and tag archives.', 'total' ),
	'settings' => array(
		array(
			'id' => 'portfolio_archive_layout',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Page Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'portfolio_archive_template_id',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => esc_html__( 'Select a template to override the default output for the post type archive, category and tag entries.', 'total' ),
			),
		),
		array(
			'id' => 'portfolio_entry_card_style',
			'control' => array(
				'label' => esc_html__( 'Card Style', 'total' ),
				'type' => 'wpex-card-select',
				'description' => esc_html__( 'Select a card style to override the default entry design using a preset theme card.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_portfolio_archive_tempate_id',
			),
		),
		array(
			'id' => 'portfolio_archive_grid_style',
			'default' => 'fit-rows',
			'control' => array(
				'label' => esc_html__( 'Grid Style', 'total' ),
				'type' => 'select',
				'choices'   => array(
					'fit-rows' => esc_html__( 'Fit Rows','total' ),
					'masonry' => esc_html__( 'Masonry','total' ),
					'no-margins' => esc_html__( 'No Margins','total' ),
				),
				'active_callback' => 'wpex_cac_hasnt_portfolio_archive_tempate_id',
			),
		),
		array(
			'id' => 'portfolio_entry_columns',
			'default' => '4',
			'control' => array(
				'label' => esc_html__( 'Columns', 'total' ),
				'type' => 'wpex-columns',
				'active_callback' => 'wpex_cac_hasnt_portfolio_archive_tempate_id',
			),
		),
		array(
			'id' => 'portfolio_archive_grid_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
				'active_callback' => 'wpex_cac_hasnt_portfolio_archive_tempate_id',
			),
		),
		array(
			'id' => 'portfolio_archive_grid_equal_heights',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Enable Equal Heights?', 'total' ),
				'desc' => esc_html__( 'If enabled it will set the content of each entry so they are the same height.', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_portfolio_supports_equal_heights',
			),
		),
		array(
			'id' => 'portfolio_archive_posts_per_page',
			'default' => '12',
			'control' => array(
				'label' => esc_html__( 'Posts Per Page', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'portfolio_entry_overlay_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Image Overlay', 'total' ),
				'type' => 'select',
				'choices' => $overlay_styles,
				'active_callback' => 'wpex_cac_hasnt_portfolio_archive_tempate_id',
			),
		),
		array(
			'id' => 'portfolio_entry_image_hover_animation',
			'control' => array(
				'label' => esc_html__( 'Image Hover Animation', 'total' ),
				'type' => 'select',
				'choices' => wpex_image_hovers(),
				'active_callback' => 'wpex_cac_hasnt_portfolio_archive_tempate_id',
			),
		),
		array(
			'id' => 'portfolio_entry_details',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Show Details?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_portfolio_card',
			),
		),
		array(
			'id' => 'portfolio_entry_excerpt_length',
			'default' => '20',
			'control' => array(
				'label' => esc_html__( 'Excerpt length', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_hasnt_portfolio_archive_tempate_id',
			),
		),
	),
);

// Single
$this->sections['wpex_portfolio_single'] = array(
	'title' => esc_html__( 'Single Post', 'total' ),
	'panel' => 'wpex_portfolio',
	'settings' => array(
		array(
			'id' => 'portfolio_singular_page_title',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Page Header Title?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_has_page_header',
			),
		),
		array(
			'id' => 'portfolio_next_prev',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Next/Previous Links?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'portfolio_single_layout',
			'default' => 'full-width',
			'control' => array(
				'label' => esc_html__( 'Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'portfolio_singular_template',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => $template_desc,
			),
		),
		array(
			'id' => 'portfolio_post_composer',
			'default' => 'content,share,related',
			'control' => array(
				'label' => esc_html__( 'Post Layout Elements', 'total' ),
				'type' => 'wpex-sortable',
				'choices' => $blocks,
				'desc' => esc_html__( 'Click and drag and drop elements to re-order them.', 'total' ),
				'active_callback' => 'wpex_cac_portfolio_single_hasnt_custom_template',
			),
		),
	),
);

// Related
$this->sections['wpex_portfolio_related'] = array(
	'title' => esc_html__( 'Related Posts', 'total' ),
	'panel' => 'wpex_portfolio',
	'desc' => esc_html__( 'The related posts section displays at the bottom of the post content and can be enabled/disabled via the Post Layout Elements setting under the "Single Post" tab.', 'total' ),
	'settings' => array(
		array(
			'id' => 'portfolio_related_title',
			'transport' => 'postMessage',
			'default' => esc_html__( 'Related Projects', 'total' ),
			'control' => array(
				'label' => esc_html__( 'Related Posts Title', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'portfolio_related_entry_card_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Card Style', 'total' ),
				'type' => 'wpex-card-select',
				'description' => esc_html__( 'Select a card style to override the default entry design using a preset theme card.', 'total' ),
			),
		),
		array(
			'id' => 'portfolio_related_count',
			'default' => 4,
			'control' => array(
				'label' => esc_html__( 'Post Count', 'total' ),
				'type' => 'number',
			),
		),
		array(
			'id' => 'portfolio_related_taxonomy',
			'default' => taxonomy_exists( 'portfolio_category' ) ? 'portfolio_category' : 'none',
			'control' => array(
				'label' => esc_html__( 'Related By', 'total' ),
				'type' => 'select',
				'choices' => $portfolio_taxonomies,
			),
		),
		array(
			'id' => 'portfolio_related_order',
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
			'id' => 'portfolio_related_orderby',
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
			'id' => 'portfolio_related_columns',
			'default' => '4',
			'control' => array(
				'label' => esc_html__( 'Columns', 'total' ),
				'type' => 'wpex-columns',
			),
		),
		array(
			'id' => 'portfolio_related_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
			),
		),
		array(
			'id' => 'portfolio_related_entry_overlay_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Image Overlay', 'total' ),
				'type' => 'select',
				'choices' => $overlay_styles
			),
		),
		array(
			'id' => 'portfolio_related_entry_excerpt_length',
			'default' => '20',
			'control' => array(
				'label' => esc_html__( 'Excerpt length', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Enter 0 or leave blank to disable, enter -1 to display the full post content.', 'total' ),
			),
		),
		array(
			'id' => 'portfolio_related_excerpts',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Details for Related Posts?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_portfolio_related_card',
			),
		),
	),
);