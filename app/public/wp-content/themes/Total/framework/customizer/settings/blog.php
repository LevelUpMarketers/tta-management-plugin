<?php
/**
 * Blog Customizer Options
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

// Get blog taxonomies
$blog_taxonomies = array(
	'null' => esc_html__( 'Anything', 'total' ),
);
$get_blog_taxonomies = get_object_taxonomies( 'post' );
if ( $get_blog_taxonomies ) {
	foreach( $get_blog_taxonomies as $tax ) {
		$blog_taxonomies[$tax] = get_taxonomy( $tax )->labels->name;
	}
}

defined( 'ABSPATH' ) || exit;

// Entry meta
$entry_meta_defaults = array( 'date', 'author', 'categories', 'comments' );
$entry_meta_choices = array(
	'date'           => esc_html__( 'Date', 'total' ),
	'author'         => esc_html__( 'Author', 'total' ),
	'categories'     => esc_html__( 'All Categories', 'total' ),
	'first_category' => esc_html__( 'First Category', 'total' ),
	'comments'       => esc_html__( 'Comments', 'total' ),
);

// Entry Blocks
$entry_blocks = apply_filters( 'wpex_blog_entry_blocks', array(
	'featured_media'  => esc_html__( 'Media', 'total' ),
	'title'           => esc_html__( 'Title', 'total' ),
	'meta'            => esc_html__( 'Meta', 'total' ),
	'excerpt_content' => esc_html__( 'Excerpt', 'total' ),
	'readmore'        => esc_html__( 'Read More', 'total' ),
	//'social_share'    => esc_html__( 'Social Share Buttons', 'total' ),
), 'customizer' );

// Single Blocks
$single_blocks = apply_filters( 'wpex_blog_single_blocks', array(
	'featured_media' => esc_html__( 'Featured Media','total' ),
	'title'          => esc_html__( 'Title', 'total' ),
	'meta'           => esc_html__( 'Meta', 'total' ),
	'post_series'    => esc_html__( 'Post Series','total' ),
	'the_content'    => esc_html__( 'Content','total' ),
	'post_tags'      => esc_html__( 'Post Tags','total' ),
	'social_share'   => esc_html__( 'Social Share Buttons','total' ),
	'author_bio'     => esc_html__( 'Author Box','total' ),
	'related_posts'  => esc_html__( 'Related Posts','total' ),
	'comments'       => esc_html__( 'Comments','total' ),
), 'customizer' );

// General
$this->sections['wpex_blog_general'] = array(
	'title' => esc_html__( 'General', 'total' ),
	'panel' => 'wpex_blog',
	'settings' => array(
		array(
			'id' => 'blog_page',
			'transport' => 'partialRefresh',
			'control' => array(
				'label' => esc_html__( 'Main Page', 'total' ),
				'type' => 'wpex-dropdown-pages',
				'desc' => esc_html__( 'This setting is used for breadcrumbs when your main blog page is not the homepage.', 'total' ),
			),
		),
		array(
			'id' => 'blog_cats_exclude',
			'control' => array(
				'label' => esc_html__( 'Exclude Categories From Blog', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Enter the ID\'s of categories to exclude from the blog template or homepage blog seperated by a comma (no spaces).', 'total' ),
			),
		),
		array(
			'id' => 'blog_custom_sidebar',
			'control' => array(
				'label' => esc_html__( 'Enable Custom Blog Sidebar?', 'total' ),
				'type' => 'checkbox',
				'desc' => esc_html__( 'After enabling you can go to the main Widgets admin dashboard to add widgets to your blog sidebar or you can refresh the Customizer to access the new widget area here.', 'total' ),
			),
		),
	),
);

// Archives
$this->sections['wpex_blog_archives'] = array(
	'title' => esc_html__( 'Archives & Entries', 'total' ),
	'panel' => 'wpex_blog',
	'settings' => array(
		array(
			'id' => 'blog_archives_layout',
			'control' => array(
				'label' => esc_html__( 'Page Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'category_description_position',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Category & Tag Description Position', 'total' ),
				'type' => 'select',
				'choices' => array(
					''			 => esc_html__( 'Default', 'total' ),
					'under_title' => esc_html__( 'Under Title', 'total' ),
					'above_loop' => esc_html__( 'Before Entries', 'total' ),
					'hidden' => esc_html__( 'Hidden', 'total' ),
				),
			),
		),
		array(
			'id' => 'blog_archive_template_id',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => esc_html__( 'Select a template to override the default output for the main blog page, categories and tags.', 'total' ),
			),
		),
		array(
			'id' => 'blog_pagination_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Pagination Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'standard' => esc_html__( 'Standard', 'total' ),
					'load_more' => esc_html__( 'Load More', 'total' ),
					'infinite_scroll' => esc_html__( 'Infinite Scroll', 'total' ),
					'next_prev' => esc_html__( 'Next/Prev', 'total' )
				),
				'active_callback' => 'wpex_cac_hasnt_blog_archive_tempate_id',
			),
		),

		// Entry Blocks
		array(
			'id' => 'blog_archives_heading_blocks',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Entry Layout', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_blog_archive_tempate_id',
			),
		),
		array(
			'id' => 'blog_entry_card_style',
			'control' => array(
				'label' => esc_html__( 'Card Style', 'total' ),
				'type' => 'wpex-card-select',
				'description' => esc_html__( 'Select a card style to override the default entry design using a preset theme card.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_blog_archive_tempate_id',
			),
		),
		array(
			'id' => 'blog_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Entry Style', 'total' ),
				'type' => 'select',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'large-image-entry-style' => esc_html__( 'Large Image','total' ),
					'thumbnail-entry-style' => esc_html__( 'Left Thumbnail','total' ),
					'grid-entry-style' => esc_html__( 'Grid','total' ),
				),
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_left_thumbnail_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Left Thumbnail Width', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Default', 'total' ) .': 46%',
				'active_callback' => 'wpex_cac_blog_style_left_thumb',
			),
			'inline_css' => array(
				'target' => '.entries.left-thumbs .blog-entry .entry-media',
				'alter' => 'width',
			),
		),
		array(
			'id' => 'blog_right_content_width',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Right Content Width', 'total' ),
				'type' => 'text',
				'desc' => esc_html__( 'Default', 'total' ) .': 50%',
				'active_callback' => 'wpex_cac_blog_style_left_thumb',
			),
			'inline_css' => array(
				'target' => '.entries.left-thumbs .blog-entry .entry-details',
				'alter' => 'width',
			),
		),
		array(
			'id' => 'blog_grid_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Grid Style', 'total' ),
				'type' => 'select',
				'active_callback' => 'wpex_cac_grid_blog_style',
				'choices' => array(
					'' => esc_html__( 'Default', 'total' ),
					'fit-rows' => esc_html__( 'Fit Rows', 'total' ),
					'masonry' => esc_html__( 'Masonry', 'total' ),
				),
			),
		),
		array(
			'id' => 'blog_grid_columns',
			'control' => array(
				'label' => esc_html__( 'Grid Columns', 'total' ),
				'type' => 'select',
				'type' => 'wpex-columns',
				'choices' => array(
					''  => esc_html__( 'Default', 'total' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'active_callback' => 'wpex_cac_grid_blog_style',
			),
		),
		array(
			'id' => 'blog_grid_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
				'active_callback' => 'wpex_cac_grid_blog_style',
			),
		),
		array(
			'id' => 'blog_entry_composer',
			'default' => 'featured_media,title,meta,excerpt_content,readmore',
			'control' => array(
				'label' => esc_html__( 'Entry Layout Elements', 'total' ),
				'type' => 'wpex-sortable',
				'choices' => $entry_blocks,
				'desc' => esc_html__( 'Click and drag and drop elements to re-order them.', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),

		// Other
		array(
			'id' => 'blog_archives_heading_entry_settings',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Entry Settings', 'total' ),
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_entry_overlay',
			'control' => array(
				'label' => esc_html__( 'Image Overlay', 'total' ),
				'type' => 'select',
				'choices' => $overlay_styles,
				'active_callback' => 'wpex_cac_hasnt_blog_archive_tempate_id',
			),
		),
		array(
			'id' => 'blog_entry_image_hover_animation',
			'control' => array(
				'label' => esc_html__( 'Image Hover Animation', 'total' ),
				'type' => 'select',
				'choices' => wpex_image_hovers(),
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_entry_image_lightbox',
			'control' => array(
				'label' => esc_html__( 'Enable Lightbox on Thumbnail?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_archive_grid_equal_heights',
			'control' => array(
				'label' => esc_html__( 'Enable Equal Heights?', 'total' ),
				'desc' => esc_html__( 'If enabled it will set the content of each entry so they are the same height.', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_blog_supports_equal_heights',
			),
		),
		array(
			'id' => 'blog_exceprt',
			'default' => 'on',
			'control' => array(
				'label' => esc_html__( 'Enable Auto Excerpts?', 'total' ),
				'desc' => esc_html__( 'If enabled the theme will automatically generate an excerpt for your entries based on the post content.', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_excerpt_length',
			'default' => '40',
			'control' => array(
				'label' => esc_html__( 'Excerpt length', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_hasnt_blog_archive_tempate_id',
			),
		),
		array(
			'id' => 'blog_entry_readmore_text',
			'default' => esc_html__( 'Read More', 'total' ),
			'control' => array(
				'label' => esc_html__( 'Read More Button Text', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_entry_author_avatar',
			'control' => array(
				'label' => esc_html__( 'Display Author Avatar?', 'total' ),
				'desc' => esc_html__( 'If enabled it will display the post author avatar next to the entry title.', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_entry_video_output',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Featured Videos?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_entry_audio_output',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Display Featured Audio?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),
		array(
			'id' => 'blog_entry_gallery_output',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Gallery Slider?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_blog_card',
			),
		),

		// Meta
		array(
			'id' => 'blog_archives_meta_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Meta', 'total' ),
				'active_callback' => 'wpex_cac_has_blog_entry_meta',
			),
		),
		array(
			'id' => 'blog_entry_meta_sections',
			'default' => $entry_meta_defaults,
			'control' => array(
				'label' => esc_html__( 'Meta Sections', 'total' ),
				'type' => 'multiple-select',
				'choices' => $entry_meta_choices,
				'active_callback' => 'wpex_cac_has_blog_entry_meta',
			),
		),
	),
);

// Single
$this->sections['wpex_blog_single'] = array(
	'title' => esc_html__( 'Single Post', 'total' ),
	'panel' => 'wpex_blog',
	'settings' => array(
		array(
			'id' => 'blog_single_layout',
			'control' => array(
				'label' => esc_html__( 'Page Layout', 'total' ),
				'type' => 'select',
				'choices' => $post_layouts,
			),
		),
		array(
			'id' => 'post_singular_page_title',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Page Header Title?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_has_page_header',
			),
		),
		array(
			'id' => 'blog_single_header',
			'default' => 'custom_text',
			'control' => array(
				'label' => esc_html__( 'Header Displays', 'total' ),
				'type' => 'select',
				'choices' => array(
					'custom_text' => esc_html__( 'Custom Text','total' ),
					'post_title' => esc_html__( 'Post Title','total' ),
					'first_category' => esc_html__( 'First Category','total' ),
				),
				'active_callback' => 'wpex_cac_blog_single_has_page_header',
			),
		),
		array(
			'id' => 'blog_single_header_custom_text',
			'transport' => 'postMessage',
			'default' => esc_html__( 'Blog', 'total' ),
			'control' => array(
				'label' => esc_html__( 'Header Custom Text', 'total' ),
				'type' => 'text',
				'active_callback' => 'wpex_cac_blog_page_header_custom_text',
			),
		),

		// Post Layout
		array(
			'id' => 'blog_single_post_layout_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Post Layout', 'total' ),
			),
		),
		array(
			'id' => 'post_singular_template',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Dynamic Template', 'total' ),
				'type' => 'wpex-dropdown-templates',
				'desc' => $template_desc,
			),
		),
		array(
			'id' => 'blog_single_composer',
			//'transport' => 'postMessage',
			'default' => 'featured_media,title,meta,post_series,the_content,post_tags,social_share,author_bio,related_posts,comments',
			'control' => array(
				'label' => esc_html__( 'Single Layout Elements', 'total' ),
				'type' => 'wpex-sortable',
				'choices' => $single_blocks,
				'desc' => esc_html__( 'Click and drag and drop elements to re-order them.', 'total' ),
			),
			'control_display' => array(
				'check' => 'post_singular_template',
				'value' => '',
			),
		),

		// Post Settings
		array(
			'id' => 'blog_single_post_settings_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Post Settings', 'total' ),
			),
		),
		array(
			'id' => 'blog_post_media_position_above',
			'default' => false,
			'control' => array(
				'label' => esc_html__( 'Display Media Above Content & Sidebar?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'blog_post_image_lightbox',
			'control' => array(
				'label' => esc_html__( 'Enable Lightbox on Thumbnail?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'blog_thumbnail_caption',
			'control' => array(
				'label' => esc_html__( 'Display Thumbnail Caption?', 'total' ),
				'type' => 'checkbox',
			),
		),
		array(
			'id' => 'blog_next_prev',
			'default' => true,
			'control' => array(
				'label' => esc_html__( 'Display Next/Previous Links?', 'total' ),
				'type' => 'checkbox',
			),
		),

		// Meta
		array(
			'id' => 'blog_single_meta_heading',
			'control' => array(
				'type' => 'wpex-heading',
				'label' => esc_html__( 'Meta', 'total' ),
				'active_callback' => 'wpex_cac_has_blog_meta',
			),
		),
		array(
			'id' => 'blog_post_meta_sections',
			'default' => $entry_meta_defaults,
			'control' => array(
				'label' => esc_html__( 'Meta Sections', 'total' ),
				'type' => 'multiple-select',
				'choices' => $entry_meta_choices,
				'active_callback' => 'wpex_cac_has_blog_meta',
			),
		),
	),
);

// Author Box
$this->sections['wpex_author_box'] = array(
	'title' => esc_html__( 'Author Box', 'total' ),
	'panel' => 'wpex_blog',
	'settings' => array(
		array(
			'id' => 'author_box_avatar_size',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Avatar Size', 'total' ),
				'description' => esc_html__( 'Enter 0 to disable the avatar display.', 'total' ),
				'input_attrs' => array(
					'placeholder' => '70',
				),
			),
			'inline_css' => array(
				'target' => '.author-bio-avatar img',
				'alter' => array( 'width', 'height' ),
				'sanitize' => 'px',
			),
		),
		array(
			'id' => 'author_box_avatar_border_radius',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'select',
				'label' => esc_html__( 'Avatar Border Radius', 'total' ),
				'choices' => wpex_utl_border_radius(),
			),
		),
		array(
			'id' => 'author_box_bg',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Background', 'total' ),
			),
			'inline_css' => array(
				'target' => '.author-bio',
				'alter' => 'background-color',
			),
		),
		array(
			'id' => 'author_box_heading_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Heading Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.author-bio-title',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'author_box_description_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Description Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.author-bio',
				'alter' => 'color',
			),
		),
		array(
			'id' => 'author_box_margin',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Margin', 'total' ),
				'input_attrs' => array(
					'placeholder' => '0px',
				),
			),
			'inline_css' => array(
				'target' => '.author-bio',
				'alter' => 'margin',
			),
		),
		array(
			'id' => 'author_box_padding',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Padding', 'total' ),
				'input_attrs' => array(
					'placeholder' => '20px',
				),
			),
			'inline_css' => array(
				'target' => '.author-bio',
				'alter' => 'padding',
			),
		),
		array(
			'id' => 'author_box_border_color',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'color',
				'label' => esc_html__( 'Border Color', 'total' ),
			),
			'inline_css' => array(
				'target' => '.author-bio',
				'alter' => 'border-color',
			),
		),
		array(
			'id' => 'author_box_border_width',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Border Width', 'total' ),
				'input_attrs' => array(
					'placeholder' => '1px',
				),
			),
			'inline_css' => array(
				'target' => '.author-bio',
				'alter' => 'border-width',
			),
		),
		array(
			'id' => 'author_box_social_style',
			'transport' => 'partialRefresh',
			'default' => 'flat-color-round',
			'control' => array(
				'label' => esc_html__( 'Social Style', 'total' ),
				'type' => 'select',
				'choices' => $social_styles,
			),
		),
		array(
			'id' => 'author_box_social_font_size',
			'transport' => 'postMessage',
			'control' => array(
				'type' => 'text',
				'label' => esc_html__( 'Social Font Size', 'total' ),
				'input_attrs' => array(
					'placeholder' => '1em',
				),
			),
			'inline_css' => array(
				'target' => '.author-bio-social .wpex-social-btn',
				'alter' => 'font-size',
			),
		),
	)
);

// Related Posts
$this->sections['wpex_blog_single_related'] = array(
	'title' => esc_html__( 'Related Posts', 'total' ),
	'panel' => 'wpex_blog',
	'desc' => esc_html__( 'The related posts section displays at the bottom of the post content and can be enabled/disabled via the Post Layout Elements setting under the "Single Post" tab.', 'total' ),
	'settings' => array(
		array(
			'id' => 'blog_related_title',
			'transport' => 'postMessage',
			'control' => array(
				'label' => esc_html__( 'Related Posts Title', 'total' ),
				'type' => 'text',
				'input_attrs' => array(
					'placeholder' => esc_html__( 'Related Posts', 'total' ),
				),
			),
		),
		array(
			'id' => 'blog_related_entry_card_style',
			'default' => '',
			'control' => array(
				'label' => esc_html__( 'Card Style', 'total' ),
				'type' => 'wpex-card-select',
				'description' => esc_html__( 'Select a card style to override the default entry design using a preset theme card.', 'total' ),
			),
		),
		array(
			'id' => 'blog_related_count',
			'default' => 3,
			'control' => array(
				'label' => esc_html__( 'Post Count', 'total' ),
				'type' => 'text',
			),
		),
		array(
			'id' => 'blog_related_taxonomy',
			'default' => 'category',
			'control' => array(
				'label' => esc_html__( 'Related By', 'total' ),
				'type' => 'select',
				'choices' => $blog_taxonomies,
			),
		),
		array(
			'id' => 'blog_related_order',
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
			'id' => 'blog_related_orderby',
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
			'id' => 'blog_related_columns',
			'default' => '3',
			'control' => array(
				'label' => esc_html__( 'Columns', 'total' ),
				'type' => 'wpex-columns',
			),
		),
		array(
			'id' => 'blog_related_gap',
			'control' => array(
				'label' => esc_html__( 'Gap', 'total' ),
				'type' => 'select',
				'choices' => wpex_column_gaps(),
			),
		),
		array(
			'id' => 'blog_related_overlay',
			'control' => array(
				'label' => esc_html__( 'Related Posts Image Overlay', 'total' ),
				'type' => 'select',
				'choices' => $overlay_styles,
			),
		),
		array(
			'id' => 'blog_related_excerpt',
			'default' => 'on',
			'control' => array(
				'label' => esc_html__( 'Display Related Posts Excerpt?', 'total' ),
				'type' => 'checkbox',
				'active_callback' => 'wpex_cac_hasnt_blog_related_card',
			),
		),
		array(
			'id' => 'blog_related_excerpt_length',
			'default' => '15',
			'control' => array(
				'label' => esc_html__( 'Related Posts Excerpt Length', 'total' ),
				'type' => 'text',
			),
		),
	)
);