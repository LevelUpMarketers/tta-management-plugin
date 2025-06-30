<?php
/**
 * Adds custom options to the Post Types Unlimited Plugin meta options.
 *
 * @package Total WordPress Theme
 * @subpackage PostTypesUnlimited
 * @version 5.0.4
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class PostTypesUnlimited {

	/**
	 * Post Types Variable.
	 */
	public static $types = array();

	/**
	 * Taxonomies Variable.
	 */
	public static $taxonomies = array();

	/**
	 * Our single PostTypesUnlimited instance.
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disabled instantiation.
	}

	/**
	 * Disable the cloning of this class.
	 *
	 * @return void
	 */
	final public function __clone() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Disable the wakeup of this class.
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of PostTypesUnlimited.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new PostTypesUnlimited;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		$this->global_hooks();

		if ( wpex_is_request( 'admin' ) ) {
			$this->admin_hooks();
		}

	}

	/**
	 * Hook into global actions and filters.
	 */
	public function global_hooks() {
		add_filter( 'wpex_image_sizes', array( $this, 'wpex_image_sizes' ), 100 );
		add_filter( 'wpex_register_sidebars_array', array( $this, 'wpex_register_sidebars_array' ) );
		add_action( 'init', array( $this, 'register_post_series' ) );
	}

	/**
	 * Hook into admin actions and filters.
	 */
	public function admin_hooks() {

		// Add custom metaboxes to the ptu plugin admin screen
		add_filter( 'ptu_metaboxes', array( $this, 'add_meta' ), 50 );

		// hook into theme filters
		add_filter( 'wpex_main_metaboxes_post_types', array( $this, 'metabox_main' ) );
		add_filter( 'wpex_card_metabox_post_types', array( $this, 'metabox_card' ) );
		add_filter( 'wpex_metabox_array', array( $this, 'metabox_media' ) );
		add_filter( 'wpex_image_sizes_tabs', array( $this, 'wpex_image_sizes_tabs' ), 50 );
		add_filter( 'wpex_gallery_metabox_post_types', array( $this, 'wpex_gallery_metabox_post_types' ) );

	}

	/**
	 * Add new meta options.
	 *
	 * @since 4.8.4
	 */
	public function add_meta( $metaboxes ) {

		/*** Post Type | General ***/
		$metaboxes[] = $this->type_general_metabox();

		/*** Post Type | Archives ***/
		$metaboxes[] = $this->type_archive_metabox();

		/*** Post Type | Single ***/
		$metaboxes[] = $this->type_single_metabox();

		/*** Post Type | Related ***/
		$metaboxes[] = $this->type_related_metabox();

		/*** Taxonomy ***/
		$metaboxes[] = $this->tax_metabox();

		return $metaboxes;

	}

	/**
	 * Post Type general options.
	 *
	 * @since 5.0
	 */
	public function type_general_metabox() {

		return array(
			'id'       => 'total_ptu',
			'title'    => esc_html__( 'Theme Settings', 'total' ) . ' - ' . esc_html__( 'General', 'total' ),
			'screen'   => array( 'ptu' ),
			'context'  => 'normal',
			'priority' => 'low',
			'fields'   => array(
				array(
					'name' => esc_html__( 'Theme Settings Metabox', 'total' ),
					'id'   => 'total_ps_meta',
					'type' => 'checkbox',
				),
				array(
					'name' => esc_html__( 'Metabox Media Tab', 'total' ),
					'id'   => 'total_ps_meta_media',
					'type' => 'checkbox',
				),
				array(
					'name' => esc_html__( 'Card Settings Metabox', 'total' ),
					'id'   => 'total_ps_meta_card',
					'type' => 'checkbox',
				),
				array(
					'name' => esc_html__( 'Post Series', 'total' ),
					'id'   => 'total_post_series',
					'type' => 'checkbox',
				),
				array(
					'name' => esc_html__( 'Image Gallery', 'total' ),
					'id'   => 'total_post_gallery',
					'type' => 'checkbox',
				),
				array(
					'name' => esc_html__( 'Image Sizes', 'total' ),
					'id'   => 'total_image_sizes',
					'type' => 'checkbox',
					'desc' => esc_html__( 'Enable image size settings for this post type under Theme Panel > Image Sizes.', 'total' ),
				),
				array(
					'name' => esc_html__( 'Custom Sidebar', 'total' ),
					'id'   => 'total_custom_sidebar',
					'type' => 'text',
					'desc' => esc_html__( 'Enter a name to create a custom sidebar for the post type archive, single posts and attached taxonomies.', 'total' ),
				),
				array(
					'name'    => esc_html__( 'Main Page', 'total' ),
					'id'      => 'total_main_page',
					'type'    => 'select',
					'desc'    => esc_html__( 'Used for breadcrumbs.', 'total' ),
					'choices' => $this->choices_pages(),
				),
				array(
					'name'    => esc_html__( 'Main Taxonomy', 'total' ),
					'id'      => 'total_main_taxonomy',
					'type'    => 'select',
					'desc'    => esc_html__( 'Used for breadcrumbs, post meta categories and related items.', 'total' ),
					'choices' => $this->choices_taxonomies(),
				),
			)
		);

	}

	/**
	 * Post Type archive options.
	 *
	 * @since 5.0
	 */
	public function type_archive_metabox() {

		$grid_columns = array( '' => esc_html__( 'Default', 'total' ) ) + wpex_grid_columns();

		return array(
			'id'       => 'total_ptu_type_archive',
			'title'    => esc_html__( 'Theme Settings', 'total' ) . ' - ' . esc_html__( 'Archives', 'total' ),
			'screen'   => array( 'ptu' ),
			'context'  => 'normal',
			'priority' => 'low',
			'fields'   => array(
				array(
					'name' => esc_html__( 'Custom Title', 'total' ),
					'id'   => 'total_archive_page_header_title',
					'type' => 'text',
				),
				array(
					'name'    => esc_html__( 'Title Style', 'total' ),
					'id'      => 'total_archive_page_header_title_style',
					'type'    => 'select',
					'choices' => wpex_get_page_header_styles(),
				),
				array(
					'name'    => esc_html__( 'Layout', 'total' ),
					'id'      => 'total_archive_layout',
					'type'    => 'select',
					'choices' => wpex_get_post_layouts(),
				),
				array(
					'name' => esc_html__( 'Post Count', 'total' ),
					'id'   => 'total_archive_posts_per_page',
					'type' => 'text',
					'desc' => esc_html__( 'How many posts do you want to display before showing the post pagination? Enter -1 to display all of them without pagination.', 'total' ),
				),
				array(
					'name'    => esc_html__( 'Columns', 'total' ),
					'id'      => 'total_archive_grid_columns',
					'type'    => 'select',
					'choices' => $grid_columns,
				),
				array(
					'name'    => esc_html__( 'Grid Style', 'total' ),
					'id'      => 'total_archive_grid_style',
					'type'    => 'select',
					'choices' => array(
						''        => esc_html__( 'Default', 'total' ),
						'masonry' => esc_html__( 'Masonry', 'total' ),
					),
				),
				array(
					'name'    => esc_html__( 'Gap', 'total' ),
					'id'      => 'total_archive_grid_gap',
					'type'    => 'select',
					'choices' => wpex_column_gaps(),
				),
				array(
					'name'    => esc_html__( 'Card Style', 'total' ),
					'id'      => 'total_entry_card_style',
					'type'    => 'select',
					'choices' => wpex_choices_card_styles(),
				),
				array(
					'name'    => esc_html__( 'Image Overlay', 'total' ),
					'id'      => 'total_entry_overlay_style',
					'type'    => 'select',
					'choices' => wpex_overlay_styles_array(),
				),
				array(
					'name'    => esc_html__( 'Blocks (non-card style)', 'total' ),
					'id'      => 'total_entry_blocks',
					'type'    => 'multi_select',
					'default' => array( 'media', 'title', 'meta', 'content', 'readmore' ),
					'choices' => array(
						'media'    => esc_html__( 'Media (Thumbnail, Slider, Video)', 'total' ),
						'title'    => esc_html__( 'Title', 'total' ),
						'meta'     => esc_html__( 'Meta', 'total' ),
						'content'  => esc_html__( 'Content', 'total' ),
						'readmore' => esc_html__( 'Readmore', 'total' ),
					),
				),
				array(
					'name'    => esc_html__( 'Meta (non-card style)', 'total' ),
					'id'      => 'total_entry_meta_blocks',
					'type'    => 'multi_select',
					'default' => array( 'date', 'author', 'categories', 'comments' ),
					'choices' => array(
						'date'       => esc_html__( 'Date', 'total' ),
						'author'     => esc_html__( 'Author', 'total' ),
						'categories' => esc_html__( 'Categories', 'total' ),
						'comments'   => esc_html__( 'Comments', 'total' ),
					),
				),
				array(
					'name'        => esc_html__( 'Excerpt Length', 'total' ),
					'id'          => 'total_entry_excerpt_length',
					'type'        => 'number', // important to allow 0 to save and -1
					'min'         => '-1',
					'step'        => '1',
					'max'         => '9999',
					'placeholder' => '40',
					'desc'        => esc_html__( 'Number of words to display for your excerpt. Enter -1 to display the full post content. Note: custom excerpts are not trimmed.', 'total' ),
				),
			),

		);

	}

	/**
	 * Post Type single options.
	 *
	 * @since 5.0
	 */
	public function type_single_metabox() {

		return array(
			'id'       => 'total_ptu_type_single',
			'title'    => esc_html__( 'Theme Settings', 'total' ) . ' - ' . esc_html__( 'Single Post', 'total' ),
			'screen'   => array( 'ptu' ),
			'context'  => 'normal',
			'priority' => 'low',
			'fields'   => array(
				array(
					'name'    => esc_html__( 'Dynamic Template', 'total' ),
					'id'      => 'total_singular_template_id',
					'type'    => 'select',
					'desc'    => esc_html__( 'Select a template to be used for your singular post design.', 'total' ),
					'choices' => wpex_choices_dynamic_templates(),
				),
				array(
					'name' => esc_html__( 'Title', 'total' ),
					'id'   => 'total_page_header_title',
					'type' => 'text',
					'desc' => esc_html__( 'Use {{title}} to display the current post title.', 'total' ),
				),
				array(
					'name'    => esc_html__( 'Title Style', 'total' ),
					'id'      => 'total_page_header_title_style',
					'type'    => 'select',
					'choices' => wpex_get_page_header_styles(),
				),
				array(
					'name'    => esc_html__( 'Title tag', 'total' ),
					'id'      => 'total_page_header_title_tag',
					'type'    => 'select',
					'choices' => array(
						''     => esc_html__( 'Default', 'total' ),
						'h1'   => 'h1',
						'h2'   => 'h2',
						'h3'   => 'h3',
						'h4'   => 'h4',
						'h5'   => 'h5',
						'h6'   => 'h6',
						'div'  => 'div',
						'span' => 'span',
					),
				),
				array(
					'name'    => esc_html__( 'Layout', 'total' ),
					'id'      => 'total_post_layout',
					'type'    => 'select',
					'choices' => wpex_get_post_layouts(),
				),
				array(
					'name'    => esc_html__( 'Blocks', 'total' ),
					'id'      => 'total_single_blocks',
					'type'    => 'multi_select',
					'default' => array( 'media', 'title', 'meta', 'post-series', 'content', 'page-links', 'share', 'author-bio', 'related', 'comments' ),
					'choices' => array(
						'media'       => esc_html__( 'Media (Thumbnail, Slider, Video)', 'total' ),
						'title'       => esc_html__( 'Title', 'total' ),
						'meta'        => esc_html__( 'Meta', 'total' ),
						'post-series' => esc_html__( 'Post Series', 'total' ),
						'content'     => esc_html__( 'Content', 'total' ),
						'page-links'  => esc_html__( 'Page Links', 'total' ),
						'share'       => esc_html__( 'Social Share', 'total' ),
						'author-bio'  => esc_html__( 'Author Bio', 'total' ),
						'related'     => esc_html__( 'Related', 'total' ),
						'comments'    => esc_html__( 'Comments', 'total' ),
					),
				),
				array(
					'name'    => esc_html__( 'Meta', 'total' ),
					'id'      => 'total_single_meta_blocks',
					'type'    => 'multi_select',
					'default' => array( 'date', 'author', 'categories', 'comments' ),
					'choices' => array(
						'date'       => esc_html__( 'Date', 'total' ),
						'author'     => esc_html__( 'Author', 'total' ),
						'categories' => esc_html__( 'Categories (Main Taxonomy)', 'total' ),
						'comments'   => esc_html__( 'Comments', 'total' ),
					),
				),
				array(
					'name'    => esc_html__( 'Display Next/Previous Links?', 'total' ),
					'id'      => 'total_next_prev',
					'type'    => 'checkbox',
					'default' => true,
				),
			)

		);

	}

	/**
	 * Post Type related options.
	 *
	 * @since 5.0
	 */
	public function type_related_metabox() {

		$grid_columns = array( '' => esc_html__( 'Default', 'total' ) ) + wpex_grid_columns();

		return array(
			'id'       => 'total_ptu_related',
			'title'    => esc_html__( 'Theme Settings', 'total' ) . ' - ' . esc_html__( 'Related Posts', 'total' ),
			'screen'   => array( 'ptu' ),
			'context'  => 'normal',
			'priority' => 'low',
			'fields'   => array(
				array(
					'name'    => esc_html__( 'Related By', 'total' ),
					'id'      => 'total_related_taxonomy',
					'type'    => 'select',
					'choices' => array_merge( $this->choices_taxonomies(), array( 'null' => esc_html__( 'Anything', 'total' ) ) ),
				),
				array(
					'name'    => esc_html__( 'Order', 'total' ),
					'id'      => 'total_related_order',
					'type'    => 'select',
					'choices' => array(
						''     => esc_html__( 'Default', 'total' ),
						'desc' => esc_html__( 'DESC', 'total' ),
						'asc'  => esc_html__( 'ASC', 'total' ),
					),
				),
				array(
					'name'    => esc_html__( 'Order By', 'total' ),
					'id'      => 'total_related_orderby',
					'type'    => 'select',
					'choices' => array(
						''     => esc_html__( 'Default', 'total' ),
						'date'          => esc_html__( 'Date', 'total' ),
						'title'         => esc_html__( 'Title', 'total' ),
						'modified'      => esc_html__( 'Modified', 'total' ),
						'author'        => esc_html__( 'Author', 'total' ),
						'rand'          => esc_html__( 'Random', 'total' ),
						'comment_count' => esc_html__( 'Comment Count', 'total' ),
					),
				),
				array(
					'name' => esc_html__( 'Post Count', 'total' ),
					'id'   => 'total_related_count',
					'type' => 'text',
				),
				array(
					'name'    => esc_html__( 'Columns', 'total' ),
					'id'      => 'total_related_columns',
					'type'    => 'select',
					'choices' => $grid_columns,
				),
				array(
					'name'    => esc_html__( 'Gap', 'total' ),
					'id'      => 'total_related_gap',
					'type'    => 'select',
					'choices' => wpex_column_gaps(),
				),
				array(
					'name'    => esc_html__( 'Card Style', 'total' ),
					'id'      => 'total_related_entry_card_style',
					'type'    => 'select',
					'choices' => wpex_choices_card_styles(),
				),
				array(
					'name'    => esc_html__( 'Image Overlay', 'total' ),
					'id'      => 'total_related_entry_overlay_style',
					'type'    => 'select',
					'choices' => wpex_overlay_styles_array(),
				),
				array(
					'name'        => esc_html__( 'Excerpt Length', 'total' ),
					'id'          => 'total_related_entry_excerpt_length',
					'type'        => 'number', // important to allow 0 to save and -1
					'min'         => '-1',
					'step'        => '1',
					'max'         => '9999',
					'placeholder' => '15',
					'desc'        => esc_html__( 'Number of words to display for your excerpt. Enter -1 to display the full post content. Note: custom excerpts are not trimmed.', 'total' ),
				),
			)
		);

	}

	/**
	 * Taxonomy options.
	 *
	 * @since 5.0
	 */
	public function tax_metabox() {

		return array(
			'id'       => 'total_ptu_tax',
			'title'    => esc_html__( 'Theme Settings', 'total' ),
			'screen'   => array( 'ptu_tax' ),
			'context'  => 'normal',
			'priority' => 'low',
			'fields'   => array(
				array(
					'name'    => esc_html__( 'Title Style', 'total' ),
					'id'      => 'total_tax_page_header_title_style',
					'type'    => 'select',
					'choices' => wpex_get_page_header_styles(),
				),
				array(
					'name'    => esc_html__( 'Template', 'total' ),
					'id'      => 'total_tax_template_id',
					'type'    => 'select',
					'choices' => wpex_choices_dynamic_templates(),
				),
				array(
					'name'    => esc_html__( 'Layout', 'total' ),
					'id'      => 'total_tax_layout',
					'type'    => 'select',
					'choices' => wpex_get_post_layouts(),
				),
				array(
					'name' => esc_html__( 'Post Count', 'total' ),
					'id'   => 'total_tax_posts_per_page',
					'type' => 'text',
					'desc' => esc_html__( 'How many posts do you want to display before showing the post pagination? Enter -1 to display all of them without pagination.', 'total' ),
				),
				array(
					'name'    => esc_html__( 'Sidebar', 'total' ),
					'id'      => 'total_tax_sidebar',
					'type'    => 'select',
					'choices' => wpex_choices_widget_areas(),
				),
				array(
					'name'    => esc_html__( 'Columns', 'total' ),
					'id'      => 'total_tax_grid_columns',
					'type'    => 'select',
					'choices' => wpex_grid_columns(),
				),
				array(
					'name'    => esc_html__( 'Grid Style', 'total' ),
					'id'      => 'total_tax_grid_style',
					'type'    => 'select',
					'choices' => array(
						''        => esc_html__( 'Default', 'total' ),
						'masonry' => esc_html__( 'Masonry', 'total' ),
					),
				),
				array(
					'name'    => esc_html__( 'Gap', 'total' ),
					'id'      => 'total_tax_grid_gap',
					'type'    => 'select',
					'choices' => wpex_column_gaps(),
				),
				array(
					'name'    => esc_html__( 'Description Position', 'total' ),
					'id'      => 'total_tax_term_description_position',
					'type'    => 'select',
					'choices' => array(
						'subheading' => esc_html__( 'As Subheading', 'total' ),
						'above_loop' => esc_html__( 'Before Your Posts', 'total' ),
					),
				),
				array(
					'name'    => esc_html__( 'Page Header Thumbnail', 'total' ),
					'id'      => 'total_tax_term_page_header_image_enabled',
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'name'    => esc_html__( 'Card Style', 'total' ),
					'id'      => 'total_tax_entry_card_style',
					'type'    => 'select',
					'choices' => wpex_choices_card_styles(),
				),
				array(
					'name'    => esc_html__( 'Image Overlay', 'total' ),
					'id'      => 'total_tax_entry_overlay_style',
					'type'    => 'select',
					'choices' => wpex_overlay_styles_array(),
				),
				array(
					'name'    => esc_html__( 'Image Size', 'total' ),
					'id'      => 'total_tax_entry_image_size',
					'type'    => 'select',
					'choices' => $this->choices_image_size(),
				),
				array(
					'name' => esc_html__( 'Excerpt Length', 'total' ),
					'id'   => 'total_tax_entry_excerpt_length',
					'type' => 'number', // important to allow 0 to save and -1
					'min'  => '-1',
					'step' => '1',
					'max'  => '9999',
					'desc' => esc_html__( 'Number of words to display for your excerpt. Enter -1 to display the full post content. Note: custom excerpts are not trimmed.', 'total' ),
				),
			)
		);

	}

	/**
	 * Image size selector.
	 *
	 * @since 5.0
	 */
	public function choices_image_size() {

		$choices = array(
			'' => esc_html__( 'Default', 'total' ),
		);
		$get_sizes = wpex_get_thumbnail_sizes();

		if ( $get_sizes ) {

			foreach ( $get_sizes as $size => $dims ) {
				$choices[$size] = $size;
			}

		}

		return $choices;

	}

	/**
	 * Page choices for select field.
	 *
	 * @since 5.0
	 */
	public function choices_pages() {

		$choices = array(
			'' => esc_html__( 'None', 'total' ),
		);

		$get_pages = get_pages();

		if ( $get_pages && ! is_wp_error( $get_pages ) ) {
			foreach ( $get_pages as $page ) {
				$choices[$page->ID] = $page->post_title;
			}
		}

		return $choices;

	}

	/**
	 * Taxonomy select
	 *
	 * @since 5.0
	 */
	public function choices_taxonomies() {

		$choices = array(
			'' => esc_html__( 'Select', 'total' ),
		);

		$taxonomies = get_taxonomies( array(
			'public' => true,
		), 'objects' );

		if ( $taxonomies && ! is_wp_error( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$choices[$taxonomy->name] = esc_html( $taxonomy->label ) . ' (' . $taxonomy->name . ')';
			}
		}

		return $choices;

	}

	/**
	 * Get post types and store in class variable.
	 *
	 * @since 4.8.4
	 */
	public static function get_post_types() {
		if ( self::$types ) {
			return self::$types;
		}
		$get_types = get_posts( array(
			'numberposts' 	   => -1,
			'post_type' 	   => 'ptu',
			'post_status'      => 'publish',
			'suppress_filters' => false,
			'fields'           => 'ids',
		) );
		if ( $get_types ) {
			foreach( $get_types as $id ) {
				$name = get_post_meta( $id, '_ptu_name', true );
				if ( $name ) {
					self::$types[ $name ] = $id;
				}
			}
		}
		return self::$types;
	}

	/**
	 * Get taxonomies and store in class variable.
	 *
	 * @since 4.8.4
	 */
	public static function get_taxonomies() {
		if ( self::$taxonomies ) {
			return self::$taxonomies;
		}
		$get_taxes = get_posts( array(
			'numberposts' 	   => -1,
			'post_type' 	   => 'ptu_tax',
			'post_status'      => 'publish',
			'suppress_filters' => false,
			'fields'           => 'ids',
		) );
		if ( $get_taxes ) {
			foreach( $get_taxes as $id ) {
				$name = get_post_meta( $id, '_ptu_name', true );
				if ( $name ) {
					self::$taxonomies[ $name ] = $id;
				}
			}
		}
		return self::$taxonomies;
	}

	/**
	 * Return post typemeta value.
	 *
	 * @since 4.8.4
	 */
	public static function get_setting_value( $post_type, $setting_id ) {
		$types = self::get_post_types();
		if ( $types && ! empty( $types[$post_type] ) ) {
			return get_post_meta( $types[$post_type], $setting_id, true );
		}
	}

	/**
	 * Return meta value.
	 *
	 * @since 4.8.4
	 */
	public static function get_tax_setting_value( $tax, $setting_id ) {
		$taxes = self::get_taxonomies();
		if ( $taxes && ! empty( $taxes[$tax] ) ) {
			return get_post_meta( $taxes[$tax], $setting_id, true );
		}
	}

	/**
	 * Enable metabox for types.
	 *
	 * @since 4.8.4
	 */
	public function metabox_main( $types ) {
		$get_types = self::get_post_types();
		if ( $get_types ) {
			foreach ( $get_types as $type => $id ) {
				if ( get_post_meta( $id, '_ptu_total_ps_meta', true ) ) {
					$types[$type] = $type;
				}
			}
		}
		return $types;
	}

	/**
	 * Enable card metabox for types.
	 *
	 * @since 4.8.4
	 */
	public function metabox_card( $types ) {
		$get_types = self::get_post_types();
		if ( $get_types ) {
			foreach ( $get_types as $type => $id ) {
				if ( get_post_meta( $id, '_ptu_total_ps_meta_card', true ) ) {
					$types[$type] = $type;
				}
			}
		}
		return $types;
	}

	/**
	 * Enable the metabox media tab for post types.
	 *
	 * @since 5.0
	 */
	public function metabox_media( $settings ) {
		if ( isset( $settings['media'] ) ) {
			$get_types = self::get_post_types();
			if ( $get_types ) {
				foreach ( $get_types as $type => $id ) {
					if ( get_post_meta( $id, '_ptu_total_ps_meta_media', true ) ) {
						$settings['media']['post_type'][] = $type;
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * Enable image sizes.
	 *
	 * @since 4.8.4
	 */
	public function wpex_image_sizes_tabs( $tabs ) {
		$types = self::get_post_types();
		if ( $types ) {
			foreach ( $types as $type => $id ) {
				if ( get_post_meta( $id, '_ptu_total_image_sizes', true ) ) {
					$postType = get_post_type_object( $type );
					if ( $postType ) {
						$tabs[$type] = $postType->labels->singular_name;
					}
				}
			}
		}
		return $tabs;
	}

	/**
	 * Add image size options.
	 *
	 * @since 4.8.4
	 */
	public function wpex_image_sizes( $sizes ) {
		$types = self::get_post_types();
		if ( $types ) {
			foreach ( $types as $type => $id ) {
				if ( get_post_meta( $id, '_ptu_total_image_sizes', true ) ) {
					$sizes[ $type . '_archive' ] = array(
						'label'   => esc_html__( 'Archive', 'total' ),
						'width'   => $type . '_archive_image_width',
						'height'  => $type . '_archive_image_height',
						'crop'    => $type . '_archive_image_crop',
						'section' => $type,
					);
					$sizes[ $type . '_single' ] = array(
						'label'   => esc_html__( 'Post', 'total' ),
						'width'   => $type . '_post_image_width',
						'height'  => $type . '_post_image_height',
						'crop'    => $type . '_post_image_crop',
						'section' => $type,
					);
					$sizes[ $type . '_single_related' ] = array(
						'label'   => esc_html__( 'Post Related Items', 'total' ),
						'width'   => $type . '_single_related_image_width',
						'height'  => $type . '_single_related_image_height',
						'crop'    => $type . '_single_related_image_crop',
						'section' => $type,
					);
				}
			}
		}
		return $sizes;
	}

	/**
	 * Register sidebars.
	 *
	 * @since 4.8.4
	 */
	public function wpex_register_sidebars_array( $sidebars ) {
		$types = self::get_post_types();
		if ( $types ) {
			foreach ( $types as $type => $id ) {
				$sidebar = get_post_meta( $id, '_ptu_total_custom_sidebar', true );
				if ( $sidebar ) {
					$id = wp_strip_all_tags( $sidebar );
					$id = str_replace( ' ', '_', $sidebar );
					$id = strtolower( $sidebar );
					$sidebars[$id] = $sidebar;
				}
			}
		}
		return $sidebars;
	}

	/**
	 * Register post series for selected post types.
	 *
	 * @since 5.0.4
	 */
	public function register_post_series() {
		$types = self::get_post_types();
		if ( $types ) {
			foreach ( $types as $type => $id ) {
				$check = get_post_meta( $id, '_ptu_total_post_series', true );
				if ( wp_validate_boolean( $check ) ) {
					register_taxonomy_for_object_type( 'post_series', $type );
				}
			}
		}
	}

	/**
	 * Enable gallery metabox.
	 *
	 * @since 4.8.4
	 */
	public function wpex_gallery_metabox_post_types( $types ) {
		$get_types = self::get_post_types();
		if ( $get_types ) {
			foreach ( $get_types as $type => $id ) {
				if ( get_post_meta( $id, '_ptu_total_post_gallery', true ) ) {
					$types[$id] = $type;
				}
			}
		}
		return $types;
	}

}
PostTypesUnlimited::instance();