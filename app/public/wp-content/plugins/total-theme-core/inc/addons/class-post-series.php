<?php
/**
 * Post Series Class
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

class Post_Series {

	/**
	 * Our single Post_Series instance.
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
	 * Create or retrieve the instance of Post_Series.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Post_Series;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Filters
		add_filter( 'manage_edit-post_columns', array( $this, 'edit_columns' ) );
		add_filter( 'wpex_customizer_sections', array( $this, 'customizer_settings' ) );
		add_filter( 'vcex_builder_modules', array( $this, 'add_builder_module' ) );

		// Actions
		add_action( 'init', array( $this, 'register' ), 0 );
		add_action( 'manage_post_posts_custom_column', array( $this, 'column_display' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'tax_filters' ) );
		add_action( 'wpex_next_prev_same_cat_taxonomy', array( $this, 'next_prev_same_cat_taxonomy' ) );
		add_action( 'pre_get_posts', array( $this, 'fix_archives_order' ) );

	}

	/**
	 * Registers the custom taxonomy
	 */
	public function register() {

		$name = get_theme_mod( 'post_series_labels' );
		$name = $name ? $name : esc_html__( 'Post Series', 'total-theme-core' );
		$slug = get_theme_mod( 'post_series_slug' );
		$slug = $slug ? $slug : 'post-series';

		// Apply filters
		$args = apply_filters( 'wpex_taxonomy_post_series_args', array(
			'labels'             => array(
				'name'                       => $name,
				'singular_name'              => $name,
				'menu_name'                  => $name,
				'search_items'               => esc_html__( 'Search','total-theme-core' ),
				'popular_items'              => esc_html__( 'Popular', 'total-theme-core' ),
				'all_items'                  => esc_html__( 'All', 'total-theme-core' ),
				'parent_item'                => esc_html__( 'Parent', 'total-theme-core' ),
				'parent_item_colon'          => esc_html__( 'Parent', 'total-theme-core' ),
				'edit_item'                  => esc_html__( 'Edit', 'total-theme-core' ),
				'update_item'                => esc_html__( 'Update', 'total-theme-core' ),
				'add_new_item'               => esc_html__( 'Add New', 'total-theme-core' ),
				'new_item_name'              => esc_html__( 'New', 'total-theme-core' ),
				'separate_items_with_commas' => esc_html__( 'Separate with commas', 'total-theme-core' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove', 'total-theme-core' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'total-theme-core' ),
			),
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				'slug'  => $slug,
			),
			'query_var'         => true
		) );

		// Post types to register the post series for
		$obj_type = array( 'post' );
		$mod_obj_type = get_theme_mod( 'post_series_object_type' );
		if ( $mod_obj_type && is_string( $mod_obj_type ) ) {
			$mod_obj_type = explode( ',', $mod_obj_type );
			if ( is_array( $mod_obj_type ) ) {
				$obj_type = $mod_obj_type;
			}
		}

		// Register the taxonomy
		register_taxonomy( 'post_series', $obj_type, $args );

	}

	/**
	 * Adds columns to the WP dashboard edit screen
	 *
	 * @since 2.0.0
	 */
	public function edit_columns( $columns ) {
		$columns['wpex_post_series'] = esc_html__( 'Post Series', 'total-theme-core' );
		return $columns;
	}

	/**
	 * Adds columns to the WP dashboard edit screen
	 *
	 * @since 2.0.0
	 */
	public function column_display( $column, $post_id ) {
		switch ( $column ) {
			case "wpex_post_series":
			if ( $category_list = get_the_term_list( $post_id, 'post_series', '', ', ', '' ) ) {
				echo $category_list;
			} else {
				echo '&#8212;';
			}
			break;
		}
	}

	/**
	 * Adds taxonomy filters to the posts admin page
	 *
	 * @since 2.0.0
	 */
	public function tax_filters() {
		global $typenow;
		if ( 'post' === $typenow ) {
			$tax_slug         = 'post_series';
			$current_tax_slug = isset( $_GET[$tax_slug] ) ? esc_html( $_GET[$tax_slug] ) : false;
			$tax_obj          = get_taxonomy( $tax_slug );
			$tax_name         = $tax_obj->labels->name;
			$terms            = get_terms( $tax_slug );
			if ( count( $terms ) > 0) {
				echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
				echo "<option value=''>$tax_name</option>";
				foreach ( $terms as $term ) {
					echo '<option value=' . $term->slug, $current_tax_slug == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
				}
				echo "</select>";
			}
		}
	}

	/**
	 * Alter next/previous post links same_cat taxonomy
	 */
	public function next_prev_same_cat_taxonomy( $taxonomy ) {
		if ( wpex_is_post_in_series() ) {
			$taxonomy = 'post_series';
		}
		return $taxonomy;
	}

	/**
	 * Adds customizer settings for the animations
	 *
	 * @return array
	 */
	public function customizer_settings( $sections ) {
		$sections['wpex_post_series'] = array(
			'title' => esc_html__( 'Post Series', 'total-theme-core' ),
			'panel' => 'wpex_general',
			'desc' => esc_html__( 'Post Series is a custom taxonomy that allows you to "link" posts together so when viewing a post from a series you will see links to all related posts at the top. You can disable this function completely via the Theme Panel.', 'total-theme-core' ),
			'settings' => array(
				array(
					'id' => 'post_series_object_type',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Target Post Types', 'total-theme-core' ),
						'type' => 'text',
						'desc' => esc_html__( 'The Post Series is added only to posts by default. Enter a comma separated list of the post types you want it added to. If you want to keep it on posts make sure to include "post" in your list.', 'total-theme-core' ),
						'input_attrs' => array(
							'placeholder' => 'post',
						),
					),
				),
				array(
					'id' => 'post_series_labels',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Admin Label', 'total-theme-core' ),
						'type' => 'text',
						'input_attrs' => array(
							'placeholder' => esc_html__( 'Post Series', 'total-theme-core' ),
						),
					),
				),
				array(
					'id' => 'post_series_slug',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Slug', 'total-theme-core' ),
						'type' => 'text',
						'input_attrs' => array(
							'placeholder' => 'post-series',
						),
					),
				),
				array(
					'id' => 'post_series_bg',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Background', 'total-theme-core' ),
						'type' => 'color',
					),
					'inline_css' => array(
						'target' => '.wpex-post-series-toc',
						'alter' => 'background',
					),
				),
				array(
					'id' => 'post_series_borders',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Border', 'total-theme-core' ),
						'type' => 'color',
					),
					'inline_css' => array(
						'target' => '.wpex-post-series-toc',
						'alter' => 'border-color',
					),
				),
				array(
					'id' => 'post_series_header_color',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Header Color', 'total-theme-core' ),
						'type' => 'color',
					),
					'inline_css' => array(
						'target' => '.wpex-post-series-toc-header a',
						'alter' => 'color',
					),
				),
				array(
					'id' => 'post_series_color',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Text Color', 'total-theme-core' ),
						'type' => 'color',
					),
					'inline_css' => array(
						'target' => '.wpex-post-series-toc-list',
						'alter' => 'color',
					),
				),
				array(
					'id' => 'post_series_link_color',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Link Color', 'total-theme-core' ),
						'type' => 'color',
					),
					'inline_css' => array(
						'target' => '.wpex-post-series-toc-list a',
						'alter' => 'color',
					),
				),
			)
		);
		return $sections;
	}

	/**
	 * Add VC Module
	 */
	public function add_builder_module( $modules ) {
		$modules[] = 'post_series';
		return $modules;
	}

	/**
	 * Fix archives order
	 */
	public function fix_archives_order( $query ) {
		if ( ! is_admin() && $query->is_main_query() && is_tax( 'post_series' ) ) {
			$query->set( 'order', 'ASC' );
			return;
		}
	}

}
Post_Series::instance();