<?php
/**
 * Search Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

$this->sections['wpex_search'] = array(
	'title'  => esc_html__( 'Search Results Page', 'total' ),
	'panel'  => 'wpex_general',
	'settings' => array(
		array(
			'id' => 'search_custom_sidebar',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Custom Sidebar?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'search_standard_posts_only',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Show Standard Posts Only?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'search_layout',
			'control' => array(
				'label' => esc_html__( 'Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'search_archive_template_id',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => esc_html__( 'Select a template to override the default output for your search results.', 'total' ),
			),
		),
		array(
			'id' => 'search_style',
			'default' => 'default',
			'control' => array(
				'label' => esc_html__( 'Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'default' => esc_html__( 'Left Thumbnail', 'total' ),
					'blog' => esc_html__( 'Inherit From Blog','total' ),
				),
				'active_callback' => 'wpex_cac_hasnt_search_card',
			),
		),
		array(
			'id' => 'search_entry_card_style',
			'control' => array(
				'label' => esc_html__( 'Card Style', 'total' ),
				'type' => 'wpex-card-select',
				'description' => esc_html__( 'Select a card style to override the default entry design using a preset theme card.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_search_archive_tempate_id',
			),
		),
		array(
			'id' => 'search_archive_grid_style',
			'default' => 'fit-rows',
			'control' => array(
				'label' => esc_html__( 'Grid Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'fit-rows' => esc_html__( 'Fit Rows','total' ),
					'masonry' => esc_html__( 'Masonry','total' ),
				),
				'active_callback' => 'wpex_cac_has_search_card',
			),
		),
		array(
			'id' => 'search_entry_columns',
			'default' => '2',
			'control' => array(
				'label' => esc_html__( 'Columns', 'total' ),
				'type' => 'wpex-columns',
				'active_callback' => 'wpex_cac_has_search_card',
			),
		),
		array(
			'id' => 'search_archive_grid_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
				'active_callback' => 'wpex_cac_has_search_card',
			),
		),
		array(
			'id' => 'search_posts_per_page',
			'default' => '10',
			'control' => array(
				'label' => esc_html__( 'Posts Per Page', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_hasnt_search_archive_tempate_id',
			),
		),
		array(
			'id' => 'search_entry_excerpt_length',
			'default' => '30',
			'control' => array(
				'label' => esc_html__( 'Excerpt length', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Enter 0 or leave blank to disable, enter -1 to display the full post content.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_search_archive_tempate_id',
			),
		),
		array(
			'id' => 'search_results_cpt_loops',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Enable Post Type Parameter Support?', 'total' ),
				'type' => 'checkbox',
				'description' => esc_html__( 'When displaying custom post type results (using the post_type parameter in the search URL) it will display the results using the default post type archive loop design.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_search_archive_tempate_id',
			),
		),
	),
);