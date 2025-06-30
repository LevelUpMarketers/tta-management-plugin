<?php
/**
 * Portfolio Post Type
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class Portfolio {

	/**
	 * Our single Portfolio instance.
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
	 * Create or retrieve the instance of Portfolio.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Portfolio;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Adds the portfolio post type
		add_action( 'init', array( $this, 'register_post_type' ), 0 );

		// Register portfolio tags if enabled
		if ( is_mod_enabled( get_theme_mod( 'portfolio_tags', true ) ) ) {
			add_action( 'init', array( $this, 'register_tags' ), 0 );
		}

		// Register portfolio categories if enabled
		if ( is_mod_enabled( get_theme_mod( 'portfolio_categories', true ) ) ) {
			add_action( 'init', array( $this, 'register_categories' ), 0 );
		}

		// Adds the portfolio custom sidebar
		if ( get_theme_mod( 'portfolio_custom_sidebar', true ) ) {
			add_filter( 'wpex_register_sidebars_array', array( $this, 'register_sidebar' ), 10 );
		}

		// Add image sizes
		add_filter( 'wpex_image_sizes', array( $this, 'add_image_sizes' ), 10 );

		// Register translation strings
		add_filter( 'wpex_register_theme_mod_strings', array( $this, 'register_theme_mod_strings' ) );

		// Add portfolio VC modules
		add_filter( 'vcex_builder_modules', array( $this, 'vc_modules' ) );

		/*-------------------------------------------------------------------------------*/
		/* -  Admin only actions/filters
		/*-------------------------------------------------------------------------------*/
		if ( is_admin() ) {

			// Adds columns in the admin view for taxonomies
			add_filter( 'manage_edit-portfolio_columns', array( $this, 'edit_columns' ) );
			add_action( 'manage_portfolio_posts_custom_column', array( $this, 'column_display' ), 10, 2 );

			// Allows filtering of posts by taxonomy in the admin view
			add_action( 'restrict_manage_posts', array( $this, 'tax_filters' ) );

			// Add new image sizes tab
			add_filter( 'wpex_image_sizes_tabs', array( $this, 'image_sizes_tabs' ), 10 );

			// Add gallery metabox to portfolio
			add_filter( 'wpex_gallery_metabox_post_types', array( $this, 'add_gallery_metabox' ), 20 );

		}

		/*-------------------------------------------------------------------------------*/
		/* -  Front-End only actions/filters
		/*-------------------------------------------------------------------------------*/
		else {

			// Display correct sidebar for portfolio items
			if ( get_theme_mod( 'portfolio_custom_sidebar', true ) ) {
				add_filter( 'wpex_get_sidebar', array( $this, 'display_sidebar' ), 10 );
			}

			// Alter the post layouts for portfolio posts and archives
			add_filter( 'wpex_post_layout_class', array( $this, 'layouts' ), 10 );

			// Archive posts per page
			add_action( 'pre_get_posts', array( $this, 'posts_per_page' ) );

			// Tweak page header title
			add_filter( 'wpex_page_header_title_args', array( $this, 'alter_title' ), 10 );

		}

	}

	/*-------------------------------------------------------------------------------*/
	/* -  Start Class Functions
	/*-------------------------------------------------------------------------------*/

	/**
	 * Run on activation
	 */
	public function on_activation() {
		$this->register_post_type();
		if ( is_mod_enabled( get_theme_mod( 'portfolio_tags', true ) ) ) {
			$this->register_tags();
		}
		if ( is_mod_enabled( get_theme_mod( 'portfolio_categories', true ) ) ) {
			$this->register_categories();
		}
	}

	/**
	 * Return correct portfolio name.
	 */
	public function portfolio_name() {
		if ( function_exists( 'wpex_get_portfolio_name' ) ) {
			return wpex_get_portfolio_name();
		} else {
			return get_theme_mod( 'portfolio_labels', esc_html__( 'Portfolio', 'total-theme-core' ) );
		}
	}

	/**
	 * Return correct portfolio singular name.
	 */
	public function portfolio_singular_name() {
		if ( function_exists( 'wpex_get_portfolio_singular_name' ) ) {
			return wpex_get_portfolio_singular_name();
		} else {
			return get_theme_mod( 'portfolio_singular_name', esc_html__( 'Portfolio Item', 'total-theme-core' ) );
		}
	}

	/**
	 * Return correct portfolio icon.
	 */
	public function portfolio_menu_icon() {
		if ( function_exists( 'wpex_get_portfolio_menu_icon' ) ) {
			return wpex_get_portfolio_menu_icon();
		} else {
			return get_theme_mod( 'portfolio_admin_icon', 'portfolio' );
		}
	}

	/**
	 * Register post type.
	 */
	public function register_post_type() {

		$name          = $this->portfolio_name();
		$singular_name = $this->portfolio_singular_name();
		$has_archive   = wp_validate_boolean( get_theme_mod( 'portfolio_has_archive', false ) );
		$default_slug  = $has_archive ? 'portfolio' : 'portfolio-item';
		$slug          = ( $slug = get_theme_mod( 'portfolio_slug' ) ) ? $slug : $default_slug;

		$labels = array(
			'name'               => $name,
			'singular_name'      => $singular_name,
			'add_new'            => esc_html__( 'Add New', 'total-theme-core' ),
			'add_new_item'       => sprintf( esc_html__( 'Add New %s', 'total-theme-core' ), $singular_name ),
			'edit_item'          => sprintf( esc_html__( 'Edit %s', 'total-theme-core' ), $singular_name ),
			'new_item'           => sprintf( esc_html__( 'Add New %s', 'total-theme-core' ), $singular_name ),
			'view_item'          => sprintf( esc_html__( 'View %s', 'total-theme-core' ), $singular_name ),
			'search_items'       => esc_html__( 'Search Items', 'total-theme-core' ),
			'not_found'          => esc_html__( 'No Items Found', 'total-theme-core' ),
			'not_found_in_trash' => esc_html__( 'No Items Found In Trash', 'total-theme-core' )
		);

		$supports = array(
			'title',
			'editor',
			'excerpt',
			'thumbnail',
			'comments',
			'custom-fields',
			'revisions',
			'author',
			'page-attributes',
		);

		$args = array(
			'labels'          => $labels,
			'public'          => true,
			'capability_type' => 'post',
			'has_archive'     => $has_archive,
			'menu_icon'       => 'dashicons-' . $this->portfolio_menu_icon(),
			'menu_position'   => 20,
			'supports'        => $supports,
			'show_in_rest'    => wp_validate_boolean( get_theme_mod( 'portfolio_show_in_rest', false ) ),
			'rewrite'         => array(
				'slug'        => $slug,
				'with_front'  => false
			),
		);

		$args = (array) apply_filters( 'wpex_portfolio_args', $args );

		register_post_type( 'portfolio', $args );

	}

	/**
	 * Register Portfolio tags.
	 */
	public function register_tags() {

		// Define and sanitize options
		$name = ( $name = get_theme_mod( 'portfolio_tag_labels' ) ) ? $name : esc_html__( 'Portfolio Tags', 'total-theme-core' );
		$slug = ( $slug = get_theme_mod( 'portfolio_tag_slug' ) ) ? $slug : 'portfolio-tag';

		// Define labels
		$labels = array(
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
		);

		// Define portfolio tag arguments
		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => false,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'          => $slug,
				'with_front'    => false,
			),
		);

		// Apply filters
		$args = apply_filters( 'wpex_taxonomy_portfolio_tag_args', $args );

		// Register the portfolio tag taxonomy
		register_taxonomy( 'portfolio_tag', array( 'portfolio' ), $args );

	}

	/**
	 * Register Portfolio category.
	 */
	public function register_categories() {

		// Define and sanitize options
		$name = ( $name = get_theme_mod( 'portfolio_cat_labels' ) ) ? esc_html( $name ) : esc_html__( 'Portfolio Categories', 'total-theme-core' );
		$slug = ( $slug = get_theme_mod( 'portfolio_cat_slug' ) ) ? esc_html( $slug ) : 'portfolio-category';

		// Define labels
		$labels = array(
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
		);

		// Define args and apply filters
		$args = apply_filters( 'wpex_taxonomy_portfolio_category_args', array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				'slug'          => $slug,
				'with_front'    => false
			),
			'query_var'         => true
		) );

		// Register the portfolio category taxonomy
		register_taxonomy( 'portfolio_category', array( 'portfolio' ), $args );

	}

	/**
	 * Adds columns to the WP dashboard edit screen.
	 */
	public function edit_columns( $columns ) {
		if ( taxonomy_exists( 'portfolio_category' ) ) {
			$columns['portfolio_category'] = esc_html__( 'Category', 'total-theme-core' );
		}
		if ( taxonomy_exists( 'portfolio_tag' ) ) {
			$columns['portfolio_tag']      = esc_html__( 'Tags', 'total-theme-core' );
		}
		return $columns;
	}


	/**
	 * Adds columns to the WP dashboard edit screen.
	 */
	public function column_display( $column, $post_id ) {

		switch ( $column ) :

			// Display the portfolio categories in the column view
			case 'portfolio_category':

				$category_list = get_the_term_list( $post_id, 'portfolio_category', '', ', ', '' );

				if ( ! empty( $category_list ) && ! is_wp_error( $category_list ) ) {
					echo $category_list;
				} else {
					echo '&#8212;';
				}

			break;

			// Display the portfolio tags in the column view
			case 'portfolio_tag':

				$tag_list = get_the_term_list( $post_id, 'portfolio_tag', '', ', ', '' );

				if ( ! empty( $tag_list ) && ! is_wp_error( $tag_list ) ) {
					echo $tag_list;
				} else {
					echo '&#8212;';
				}

			break;

		endswitch;

	}

	/**
	 * Adds taxonomy filters to the portfolio admin page.
	 */
	public function tax_filters( $post_type ) {

		if ( 'portfolio' == $post_type ) {

			$taxonomies = array( 'portfolio_category', 'portfolio_tag' );

			foreach ( $taxonomies as $tax_slug ) {

				if ( ! taxonomy_exists( $tax_slug ) ) {
					continue;
				}

				$current_tax_slug = isset( $_GET[$tax_slug] ) ? $_GET[$tax_slug] : false;
				$tax_obj          = get_taxonomy( $tax_slug );
				$tax_name         = $tax_obj->labels->name;
				$terms            = get_terms( $tax_slug );

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) { ?>

					<select name="<?php echo esc_attr( $tax_slug ); ?>" id="<?php echo esc_attr( $tax_slug ); ?>" class="postform">

					<option value=""><?php echo esc_html( $tax_name ); ?></option>

					<?php foreach ( $terms as $term ) { ?>

						<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current_tax_slug, $term->slug, true ); ?>><?php echo esc_html( $term->name ); ?> (<?php echo absint( $term->count ); ?> )</option>

					<?php } ?>

					</select>

				<?php }

			}

		}

	}

	/**
	 * Registers a new custom portfolio sidebar.
	 */
	public function register_sidebar( $sidebars ) {
		$obj            = get_post_type_object( 'portfolio' );
		$post_type_name = $obj->labels->name;
		$sidebars['portfolio_sidebar'] = $post_type_name . ' ' . esc_html__( 'Sidebar', 'total-theme-core' );
		return $sidebars;
	}

	/**
	 * Alter main sidebar to display portfolio sidebar.
	 */
	public function display_sidebar( $sidebar ) {
		if ( is_singular( 'portfolio' ) || wpex_is_portfolio_tax() || is_post_type_archive( 'portfolio' ) ) {
			$sidebar = 'portfolio_sidebar';
		}
		return $sidebar;
	}

	/**
	 * Alter the post layouts for portfolio posts and archives.
	 */
	public function layouts( $layout_class ) {
		if ( is_singular( 'portfolio' ) ) {
			$layout_class = get_theme_mod( 'portfolio_single_layout', 'full-width' );
		} elseif ( wpex_is_portfolio_tax() || is_post_type_archive( 'portfolio' ) ) {
			$layout_class = get_theme_mod( 'portfolio_archive_layout', 'full-width' );
		}
		return $layout_class;
	}

	/**
	 * Archive posts per page
	 */
	public function posts_per_page( $query ) {
		if ( ! defined( 'TOTAL_THEME_ACTIVE' ) || is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( wpex_is_portfolio_tax() || $query->is_post_type_archive( 'portfolio' ) ) {
			$query->set( 'posts_per_page', absint( get_theme_mod( 'portfolio_archive_posts_per_page', 12 ) ) );
		}
	}

	/**
	 * Adds a "portfolio" tab to the image sizes admin panel
	 */
	public function image_sizes_tabs( $array ) {
		$array['portfolio'] = wpex_get_portfolio_name();
		return $array;
	}

	/**
	 * Adds image sizes for the portfolio to the image sizes panel.
	 */
	public function add_image_sizes( $sizes ) {
		$obj            = get_post_type_object( 'portfolio' );
		$post_type_name = $obj->labels->singular_name;
		$sizes['portfolio_entry'] = array(
			'label'   => sprintf( esc_html__( '%s Entry', 'total-theme-core' ), $post_type_name ),
			'width'   => 'portfolio_entry_image_width',
			'height'  => 'portfolio_entry_image_height',
			'crop'    => 'portfolio_entry_image_crop',
			'section' => 'portfolio',
		);
		$sizes['portfolio_post'] = array(
			'label'   => sprintf( esc_html__( '%s Post', 'total-theme-core' ), $post_type_name ),
			'width'   => 'portfolio_post_image_width',
			'height'  => 'portfolio_post_image_height',
			'crop'    => 'portfolio_post_image_crop',
			'section' => 'portfolio',
		);
		$sizes['portfolio_related'] = array(
			'label'   => sprintf( esc_html__( '%s Post Related', 'total-theme-core' ), $post_type_name ),
			'width'   => 'portfolio_related_image_width',
			'height'  => 'portfolio_related_image_height',
			'crop'    => 'portfolio_related_image_crop',
			'section' => 'portfolio',
		);
		return $sizes;
	}

	/**
	 * Adds the portfolio post type to the gallery metabox post types array.
	 */
	public function add_gallery_metabox( $types ) {
		$types[] = 'portfolio';
		return $types;
	}

	/**
	 * Tweak the page header title args
	 */
	public function alter_title( $args ) {
		if ( is_singular( 'portfolio' ) ) {
			$blocks = wpex_portfolio_single_blocks();
			if ( is_array( $blocks ) && ! in_array( 'title', $blocks ) ) {
				$args['string']   = single_post_title( '', false );
				$args['html_tag'] = 'h1';
			}
		}
		return $args;
	}

	/**
	 * Register portfolio theme mod strings
	 */
	public function register_theme_mod_strings( $strings ) {
		if ( is_array( $strings ) ) {
			$strings['portfolio_labels']        = 'Portfolio';
			$strings['portfolio_singular_name'] = 'Portfolio Item';
		}
		return $strings;
	}

	/**
	 * Add custom VC modules
	 */
	public function vc_modules( $modules ) {
		$modules[] = 'portfolio_grid';
		$modules[] = 'portfolio_carousel';
		return $modules;
	}

}
Portfolio::instance();