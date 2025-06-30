<?php
/**
 * Portfolio Post Type
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class Testimonials {

	/**
	 * Our single Testimonials instance.
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
	 * Create or retrieve the instance of Testimonials.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Testimonials;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Adds the testimonials post type
		add_action( 'init', array( $this, 'register_post_type' ), 0 );

		// Adds the testimonials taxonomies
		if ( is_mod_enabled( get_theme_mod( 'testimonials_categories', true ) ) ) {
			add_action( 'init', array( $this, 'register_categories' ), 0 );
		}

		// Register testimonials sidebar
		if ( get_theme_mod( 'testimonials_custom_sidebar', true ) ) {
			add_filter( 'wpex_register_sidebars_array', array( $this, 'register_sidebar' ), 10 );
		}

		// Add image sizes
		add_filter( 'wpex_image_sizes', array( $this, 'add_image_sizes' ) );

		// Add testimonial VC modules
		add_filter( 'vcex_builder_modules', array( $this, 'vc_modules' ) );

		/*-------------------------------------------------------------------------------*/
		/* -  Admin only actions/filters
		/*-------------------------------------------------------------------------------*/
		if ( is_admin() ) {

			// Adds columns in the admin view for taxonomies
			add_filter( 'manage_edit-testimonials_columns', array( $this, 'edit_columns' ) );
			add_action( 'manage_testimonials_posts_custom_column', array( $this, 'column_display' ), 10, 2 );

			// Allows filtering of posts by taxonomy in the admin view
			add_action( 'restrict_manage_posts', array( $this, 'tax_filters' ) );

			// Add new image sizes tab
			add_filter( 'wpex_image_sizes_tabs', array( $this, 'image_sizes_tabs' ), 10 );

			// Add meta settings
			add_filter( 'wpex_metabox_array', array( $this, 'add_meta' ), 5, 2 );

		}

		/*-------------------------------------------------------------------------------*/
		/* -  Front-End only actions/filters
		/*-------------------------------------------------------------------------------*/
		else {

			// Display testimonials sidebar for testimonials
			if ( get_theme_mod( 'testimonials_custom_sidebar', true ) ) {
				add_filter( 'wpex_get_sidebar', array( $this, 'display_sidebar' ), 10 );
			}

			// Alter the default page title
			add_action( 'wpex_page_header_title_args', array( $this, 'alter_title' ), 10 );

			// Alter the post layouts for testimonials posts and archives
			add_filter( 'wpex_post_layout_class', array( $this, 'layouts' ), 10 );

			// Posts per page
			add_action( 'pre_get_posts', array( $this, 'posts_per_page' ) );

			// Alter previous post link title
			add_filter( 'wpex_prev_post_link_title', array( $this, 'prev_post_link_title' ), 10, 2 );

			// Alter next post link title
			add_filter( 'wpex_next_post_link_title', array( $this, 'next_post_link_title' ), 10, 2 );

		}

	} // End construct

	/*-------------------------------------------------------------------------------*/
	/* -  Start Class Functions
	/*-------------------------------------------------------------------------------*/

	/**
	 * Run on activation
	 */
	public function on_activation() {
		$this->register_post_type();
		if ( is_mod_enabled( get_theme_mod( 'testimonials_categories', true ) ) ) {
			$this->register_categories();
		}
	}

	/**
	 * Return correct testimonials name.
	 */
	public function testimonials_name() {
		if ( function_exists( 'wpex_get_testimonials_name' ) ) {
			return wpex_get_testimonials_name();
		} else {
			return get_theme_mod( 'testimonials_labels', esc_html__( 'Testimonials', 'total-theme-core' ) );
		}
	}

	/**
	 * Return correct testimonials singular name.
	 */
	public function testimonials_singular_name() {
		if ( function_exists( 'wpex_get_testimonials_singular_name' ) ) {
			return wpex_get_testimonials_singular_name();
		} else {
			return get_theme_mod( 'testimonials_singular_name', esc_html__( 'Testimonials', 'total-theme-core' ) );
		}
	}

	/**
	 * Return correct testimonials icon.
	 */
	public function testimonials_menu_icon() {
		if ( function_exists( 'wpex_get_testimonials_menu_icon' ) ) {
			return wpex_get_testimonials_menu_icon();
		} else {
			return get_theme_mod( 'testimonials_admin_icon', 'groups' );
		}
	}

	/**
	 * Register post type
	 */
	public function register_post_type() {

		$name          = $this->testimonials_name();
		$singular_name = $this->testimonials_singular_name();
		$menu_position = get_theme_mod( 'testimonials_admin_menu_position', 20 );
		$has_archive   = wp_validate_boolean( get_theme_mod( 'testimonials_has_archive', false ) );
		$default_slug  = $has_archive ? 'testimonials' : 'testimonial';
		$slug          = ( $slug = get_theme_mod( 'testimonials_slug' ) ) ? $slug : $default_slug;
		$menu_icon     = $this->testimonials_menu_icon();

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
			'menu_icon'       => 'dashicons-' . sanitize_html_class( $menu_icon ),
			'menu_position'   => absint( $menu_position ),
			'rewrite'         => array(
				'slug'        => esc_html( $slug ),
				'with_front'  => false
			),
			'supports'        => $supports,
			'show_in_rest'    => wp_validate_boolean( get_theme_mod( 'testimonials_has_single', false ) ),
		);

		if ( wp_validate_boolean( get_theme_mod( 'testimonials_show_in_rest', false ) ) ) {
			$args['show_in_rest'] = 1;
		}

		$args = (array) apply_filters( 'wpex_testimonials_args', $args );

		register_post_type( 'testimonials', $args );

	}

	/**
	 * Register Testimonials category
	 */
	public function register_categories() {


		$name = ( $name = get_theme_mod( 'testimonials_cat_labels' ) ) ? esc_html( $name ) : esc_html__( 'Testimonials Categories', 'total-theme-core' );
		$slug = ( $slug = get_theme_mod( 'testimonials_cat_slug' ) ) ? esc_html( $slug ) : 'testimonials-category';

		$args = array(
			'labels' => array(
				'name'                       => $name,
				'singular_name'              => $name,
				'menu_name'                  => $name,
				'search_items'               => esc_html__( 'Search', 'total-theme-core' ),
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
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'          => $slug,
				'with_front'    => false
			),
		);

		$args = apply_filters( 'wpex_taxonomy_testimonials_category_args', $args );

		register_taxonomy( 'testimonials_category', array( 'testimonials' ), $args );

	}

	/**
	 * Adds columns to the WP dashboard edit screen
	 */
	public function edit_columns( $columns ) {
		$columns['testimonial_author'] = esc_html__( 'By', 'total-theme-core' );
		$columns['testimonial_rating'] = esc_html__( 'Rating', 'total-theme-core' );
		if ( taxonomy_exists( 'testimonials_category' ) ) {
			$columns['testimonial_category'] = esc_html__( 'Category', 'total-theme-core' );
		}
		return $columns;
	}

	/**
	 * Adds columns to the WP dashboard edit screen
	 */
	public function column_display( $column, $post_id ) {

		switch ( $column ) :

			case 'testimonial_author':

				if ( $by = get_post_meta( $post_id, 'wpex_testimonial_author', true ) ) {
					echo esc_html( $by );
				} else {
					echo '&#8212;';
				}

				break;

			case 'testimonial_rating':

				if ( $rating = get_post_meta( $post_id, 'wpex_post_rating', true ) ) {
					echo esc_html( $rating );
				} else {
					echo '&#8212;';
				}

				break;

			case 'testimonial_category':

				if ( taxonomy_exists( 'testimonials_category' ) ) {
					$category_list = get_the_term_list( $post_id, 'testimonials_category', '', ', ', '' );
					if ( ! empty( $category_list ) && ! is_wp_error( $category_list ) ) {
						echo $category_list;
					}
				} else {
					echo '&#8212;';
				}

			break;

		endswitch;

	}

	/**
	 * Adds taxonomy filters to the testimonials admin page
	 */
	public function tax_filters( $post_type ) {

		if ( 'testimonials' == $post_type && taxonomy_exists( 'testimonials_category' ) ) {

			$tax_slug         = 'testimonials_category';
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

	/**
	 * Registers a new custom testimonials sidebar
	 */
	public function register_sidebar( $sidebars ) {
		$obj = get_post_type_object( 'testimonials' );
		$post_type_name = $obj->labels->name;
		$sidebars['testimonials_sidebar'] = esc_html( $post_type_name ) . ' ' . esc_html__( 'Sidebar', 'total-theme-core' );
		return $sidebars;
	}

	/**
	 * Alter main sidebar to display testimonials sidebar
	 */
	public function display_sidebar( $sidebar ) {
		if ( is_singular( 'testimonials') || wpex_is_testimonials_tax() || is_post_type_archive( 'testimonials' ) ) {
			$sidebar = 'testimonials_sidebar';
		}
		return $sidebar;
	}

	/**
	 * Alters the default page title
	 */
	public function alter_title( $args ) {
		if ( is_singular( 'testimonials' ) ) {
			if ( ! get_theme_mod( 'testimonials_labels' )
				&& $author = get_post_meta( get_the_ID(), 'wpex_testimonial_author', true )
			) {
				$title = sprintf( esc_html__( 'Testimonial by: %s', 'total-theme-core' ), $author );
			} else {
				$title = single_post_title( '', false );
			}
			$args['string']   = $title;
			$args['html_tag'] = 'h1';
		}
		return $args;
	}

	/**
	 * Alter the post layouts for testimonials posts and archives
	 */
	public function layouts( $class ) {
		if ( is_singular( 'testimonials' ) ) {
			$class = get_theme_mod( 'testimonials_single_layout' );
		} elseif ( wpex_is_testimonials_tax() || is_post_type_archive( 'testimonials' ) ) {
			$class = get_theme_mod( 'testimonials_archive_layout', 'full-width' );
		}
		return $class;
	}

	/**
	 * Archive posts per page
	 */
	public function posts_per_page( $query ) {
		if ( ! defined( 'TOTAL_THEME_ACTIVE' ) || is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( wpex_is_testimonials_tax() || is_post_type_archive( 'testimonials' ) ) {
			$query->set( 'posts_per_page', get_theme_mod( 'testimonials_archive_posts_per_page', '12' ) );
		}
	}

	/**
	 * Adds a "testimonials" tab to the image sizes admin panel
	 */
	public function image_sizes_tabs( $array ) {
		$array['testimonials'] = wpex_get_testimonials_name();
		return $array;
	}

	/**
	 * Adds image sizes for the testimonials to the image sizes panel
	 */
	public function add_image_sizes( $sizes ) {
		$obj            = get_post_type_object( 'testimonials' );
		$post_type_name = $obj->labels->singular_name;
		$sizes['testimonials_entry'] = array(
			'label'   => sprintf( esc_html__( '%s Entry', 'total-theme-core' ), $post_type_name ),
			'width'   => 'testimonials_entry_image_width',
			'height'  => 'testimonials_entry_image_height',
			'crop'    => 'testimonials_entry_image_crop',
			'section' => 'testimonials',
		);
		return $sizes;
	}

	/**
	 * Alter previous post link title
	 */
	public function prev_post_link_title( $title, $post_type ) {
		if ( 'testimonials' == $post_type ) {
			$title = '<span class="ticon ticon-angle-double-left"></span>' . esc_html__( 'Previous', 'total-theme-core' );
		}
		return $title;
	}

	/**
	 * Alter next post link title
	 */
	public function next_post_link_title( $title, $post_type ) {
		if ( 'testimonials' == $post_type ) {
			$title = esc_html__( 'Next', 'total-theme-core' ) . '<span class="ticon ticon-angle-double-right"></span>';
		}
		return $title;
	}

	/**
	 * Adds testimonials meta options
	 */
	public function add_meta( $array, $post ) {
		$obj = get_post_type_object( 'testimonials' );
		$array['testimonials'] = array(
			'title'                   => $obj->labels->singular_name,
			'post_type'               => array( 'testimonials' ),
			'settings'                => array(
				'testimonial_author'  => array(
					'title'           => esc_html__( 'Author', 'total-theme-core' ),
					'description'     => esc_html__( 'Enter the name of the author for this testimonial.', 'total-theme-core' ),
					'id'              => 'wpex_testimonial_author',
					'type'            => 'text',
				),
				'testimonial_company' => array(
					'title'           => esc_html__( 'Company', 'total-theme-core' ),
					'description'     => esc_html__( 'Enter the name of the company for this testimonial.', 'total-theme-core' ),
					'id'              => 'wpex_testimonial_company',
					'type'            => 'text',
				),
				'testimonial_url'     => array(
					'title'           => esc_html__( 'Company URL', 'total-theme-core' ),
					'description'     => esc_html__( 'Enter the URL for the company for this testimonial.', 'total-theme-core' ),
					'id'              => 'wpex_testimonial_url',
					'type'            => 'text',
				),
				'post_rating'         => array(
					'title'           => esc_html__( 'Rating', 'total-theme-core' ),
					'description'     => esc_html__( 'Enter a rating for this testimonial.', 'total-theme-core' ),
					'id'              => 'wpex_post_rating',
					'type'            => 'number',
					'max'             => '10',
					'min'             => '1',
					'step'            => '0.1',
				),
			),
		);
		return $array;
	}

	/**
	 * Add custom VC modules
	 */
	public function vc_modules( $modules ) {
		$modules[] = 'testimonials_grid';
		$modules[] = 'testimonials_carousel';
		$modules[] = 'testimonials_slider';
		return $modules;
	}

}
Testimonials::instance();