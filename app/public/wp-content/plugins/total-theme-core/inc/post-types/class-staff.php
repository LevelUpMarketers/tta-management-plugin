<?php
/**
 * Portfolio Post Type
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class Staff {

	/**
	 * Our single Staff instance.
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
	 * Create or retrieve the instance of Staff.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Staff;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Adds the staff post type
		add_action( 'init', array( $this, 'register_post_type' ), 0 );

		// Adds the staff taxonomies
		if ( is_mod_enabled( get_theme_mod( 'staff_tags', 'on' ) ) ) {
			add_action( 'init', array( $this, 'register_tags' ), 0 );
		}
		if ( is_mod_enabled( get_theme_mod( 'staff_categories', 'on' ) ) ) {
			add_action( 'init', array( $this, 'register_categories' ), 0 );
		}

		// Register staff sidebar
		if ( get_theme_mod( 'staff_custom_sidebar', true ) ) {
			add_filter( 'wpex_register_sidebars_array', array( $this, 'register_sidebar' ), 10 );
		}

		// Add image sizes
		add_filter( 'wpex_image_sizes', array( $this, 'add_image_sizes' ), 10 );

		// Create relations between users and staff members
		if ( apply_filters( 'wpex_staff_users_relations', true ) ) {
			add_action( 'personal_options_update', array( $this, 'save_custom_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_custom_profile_fields' ) );
			add_filter( 'personal_options', array( $this, 'personal_options' ) );
			add_filter( 'wpex_post_author_bio_data', array( $this, 'post_author_bio_data' ) );
		}

		// Add staff VC modules
		add_filter( 'vcex_builder_modules', array( $this, 'vc_modules' ) );

		/*-------------------------------------------------------------------------------*/
		/* -  Admin only actions/filters
		/*-------------------------------------------------------------------------------*/
		if ( is_admin() ) {

			// Adds columns in the admin view for taxonomies
			add_filter( 'manage_edit-staff_columns', array( $this, 'edit_columns' ) );
			add_action( 'manage_staff_posts_custom_column', array( $this, 'column_display' ), 10, 2 );

			// Allows filtering of posts by taxonomy in the admin view
			add_action( 'restrict_manage_posts', array( $this, 'tax_filters' ) );

			// Add new image sizes tab
			add_filter( 'wpex_image_sizes_tabs', array( $this, 'image_sizes_tabs' ), 10 );

			// Add gallery metabox to staff
			add_filter( 'wpex_gallery_metabox_post_types', array( $this, 'add_gallery_metabox' ), 20 );

		}

		/*-------------------------------------------------------------------------------*/
		/* -  Front-End only actions/filters
		/*-------------------------------------------------------------------------------*/
		else {

			// Displays correct sidebar for staff posts
			if ( get_theme_mod( 'staff_custom_sidebar', true ) ) {
				add_filter( 'wpex_get_sidebar', array( $this, 'display_sidebar' ), 10 );
			}

			// Alter the post layouts for staff posts and archives
			add_filter( 'wpex_post_layout_class', array( $this, 'layouts' ), 10 );

			// Add subheading for staff member if enabled
			add_action( 'wpex_post_subheading', array( $this, 'add_position_to_subheading' ), 10 );

			// Archive posts per page
			add_action( 'pre_get_posts', array( $this, 'posts_per_page' ), 10 );

			// Tweak page header title
			add_filter( 'wpex_page_header_title_args', array( $this, 'alter_title' ), 10 );

		}

	} // End __construct

	/*-------------------------------------------------------------------------------*/
	/* -  Start Class Functions
	/*-------------------------------------------------------------------------------*/

	/**
	 * Run on activation.
	 */
	public function on_activation() {
		$this->register_post_type();
		if ( is_mod_enabled( get_theme_mod( 'staff_tags', true ) ) ) {
			$this->register_tags();
		}
		if ( is_mod_enabled( get_theme_mod( 'staff_categories', true ) ) ) {
			$this->register_categories();
		}
	}

	/**
	 * Return correct staff name.
	 */
	public function staff_name() {
		if ( function_exists( 'wpex_get_staff_name' ) ) {
			return wpex_get_staff_name();
		} else {
			return get_theme_mod( 'staff_labels', esc_html__( 'Staff', 'total-theme-core' ) );
		}
	}

	/**
	 * Return correct staff singular name.
	 */
	public function staff_singular_name() {
		if ( function_exists( 'wpex_get_staff_singular_name' ) ) {
			return wpex_get_staff_singular_name();
		} else {
			return get_theme_mod( 'staff_singular_name', esc_html__( 'Staff Member', 'total-theme-core' ) );
		}
	}

	/**
	 * Return correct staff icon.
	 */
	public function staff_menu_icon() {
		if ( function_exists( 'wpex_get_staff_menu_icon' ) ) {
			return wpex_get_staff_menu_icon();
		} else {
			return get_theme_mod( 'staff_admin_icon', 'businessman' );
		}
	}

	/**
	 * Register post type.
	 */
	public function register_post_type() {

		$name          = $this->staff_name();
		$singular_name = $this->staff_singular_name();
		$has_archive   = wp_validate_boolean( get_theme_mod( 'staff_has_archive', false ) );
		$default_slug  = $has_archive ? 'staff' : 'staff-member';
		$slug          = ( $slug = get_theme_mod( 'staff_slug' ) ) ? $slug : $default_slug;
		$menu_icon     = $this->staff_menu_icon();

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
			'public'          => true,
			'capability_type' => 'post',
			'labels'          => $labels,
			'supports'        => $supports,
			'has_archive'     => $has_archive,
			'show_in_rest'    => wp_validate_boolean( get_theme_mod( 'staff_show_in_rest', false ) ),
			'menu_icon'       => 'dashicons-' . sanitize_html_class( $menu_icon ),
			'menu_position'   => 20,
			'rewrite'         => array(
				'slug'        => $slug,
				'with_front'  => false
			),
		);

		$args = (array) apply_filters( 'wpex_staff_args', $args );

		register_post_type( 'staff', $args );

	}

	/**
	 * Register Staff tags.
	 */
	public function register_tags() {

		$name = ( $name = get_theme_mod( 'staff_tag_labels' ) ) ? esc_html( $name ) : esc_html__( 'Staff Tags', 'total-theme-core' );
		$slug = ( $slug = get_theme_mod( 'staff_tag_slug' ) ) ? esc_html( $slug ) : 'staff-tag';

		$labels = array(
			'name' => $name,
			'singular_name'              => $name,
			'menu_name'                  => $name,
			'search_items'               => esc_html__( 'Search Staff Tags', 'total-theme-core' ),
			'popular_items'              => esc_html__( 'Popular Staff Tags', 'total-theme-core' ),
			'all_items'                  => esc_html__( 'All Staff Tags', 'total-theme-core' ),
			'parent_item'                => esc_html__( 'Parent Staff Tag', 'total-theme-core' ),
			'parent_item_colon'          => esc_html__( 'Parent Staff Tag:', 'total-theme-core' ),
			'edit_item'                  => esc_html__( 'Edit Staff Tag', 'total-theme-core' ),
			'update_item'                => esc_html__( 'Update Staff Tag', 'total-theme-core' ),
			'add_new_item'               => esc_html__( 'Add New Staff Tag', 'total-theme-core' ),
			'new_item_name'              => esc_html__( 'New Staff Tag Name', 'total-theme-core' ),
			'separate_items_with_commas' => esc_html__( 'Separate staff tags with commas', 'total-theme-core' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove staff tags', 'total-theme-core' ),
			'choose_from_most_used'      => esc_html__( 'Choose from the most used staff tags', 'total-theme-core' ),
		);

		$args = apply_filters( 'wpex_taxonomy_staff_tag_args', array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'hierarchical'      => false,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'          => $slug,
				'with_front'    => false
			),
		) );

		register_taxonomy( 'staff_tag', array( 'staff' ), $args );

	}

	/**
	 * Register Staff category.
	 */
	public function register_categories() {

		$name = ( $name = get_theme_mod( 'staff_cat_labels' ) ) ? esc_html( $name ) : esc_html__( 'Staff Categories', 'total-theme-core' );
		$slug = ( $slug = get_theme_mod( 'staff_cat_slug' ) ) ? esc_html( $slug ) : 'staff-category';

		$labels = array(
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
		);

		$args = apply_filters( 'wpex_taxonomy_staff_category_args', array(
			'labels'            => $labels,
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
		) );

		register_taxonomy( 'staff_category', array( 'staff' ), $args );

	}


	/**
	 * Adds columns to the WP dashboard edit screen.
	 */
	public function edit_columns( $columns ) {
		if ( taxonomy_exists( 'staff_category' ) ) {
			$columns['staff_category'] = esc_html__( 'Category', 'total-theme-core' );
		}
		if ( taxonomy_exists( 'staff_tag' ) ) {
			$columns['staff_tag'] = esc_html__( 'Tags', 'total-theme-core' );
		}
		return $columns;
	}


	/**
	 * Adds columns to the WP dashboard edit screen.
	 */
	public function column_display( $column, $post_id ) {

		switch ( $column ) :

			// Display the staff categories in the column view
			case 'staff_category':

				$category_list = get_the_term_list( $post_id, 'staff_category', '', ', ', '' );

				if ( ! empty( $category_list ) && ! is_wp_error( $category_list ) ) {
					echo $category_list;
				} else {
					echo '&#8212;';
				}

			break;

			// Display the staff tags in the column view
			case 'staff_tag':

				$tag_list = get_the_term_list( $post_id, 'staff_tag', '', ', ', '' );

				if ( ! empty( $tag_list ) && ! is_wp_error( $tag_list ) ) {
					echo $tag_list;
				} else {
					echo '&#8212;';
				}

			break;

		endswitch;

	}

	/**
	 * Adds taxonomy filters to the staff admin page.
	 */
	public function tax_filters( $post_type ) {

		if ( 'staff' == $post_type ) {

			$taxonomies = array( 'staff_category', 'staff_tag' );

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
	 * Registers a new custom staff sidebar.
	 */
	public function register_sidebar( $sidebars ) {
		$obj            = get_post_type_object( 'staff' );
		$post_type_name = $obj->labels->name;
		$sidebars['staff_sidebar'] = $post_type_name . ' ' . esc_html__( 'Sidebar', 'total-theme-core' );
		return $sidebars;
	}

	/**
	 * Alter main sidebar to display staff sidebar.
	 */
	public function display_sidebar( $sidebar ) {
		if ( is_singular( 'staff' ) || wpex_is_staff_tax() || is_post_type_archive( 'staff' ) ) {
			$sidebar = 'staff_sidebar';
		}
		return $sidebar;
	}

	/**
	 * Alter the post layouts for staff posts and archives.
	 */
	public function layouts( $layout_class ) {
		if ( is_singular( 'staff' ) ) {
			$layout_class = get_theme_mod( 'staff_single_layout' );
		} elseif ( wpex_is_staff_tax() || is_post_type_archive( 'staff' ) ) {
			$layout_class = get_theme_mod( 'staff_archive_layout', 'full-width' );
		}
		return $layout_class;
	}

	/**
	 * Display position for page header subheading.
	 */
	public function add_position_to_subheading( $subheading ) {
		if ( is_singular( 'staff' )
			&& get_theme_mod( 'staff_single_header_position', true )
			&& ! in_array( 'title', wpex_staff_single_blocks() )
			&& $meta = get_post_meta( get_the_ID(), 'wpex_staff_position', true )
		) {
			$subheading = $meta;
		}
		return $subheading;
	}

	/**
	 * Alters posts per page for staff archives and exclude staff from search results
	 */
	public function posts_per_page( $query ) {
		if ( ! defined( 'TOTAL_THEME_ACTIVE' ) || is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( wpex_is_staff_tax() || $query->is_post_type_archive( 'staff' ) ) {
			$query->set( 'posts_per_page', get_theme_mod( 'staff_archive_posts_per_page', '12' ) );
		}
	}

	/**
	 * Adds a "staff" tab to the image sizes admin panel
	 */
	public function image_sizes_tabs( $array ) {
		$array['staff'] = wpex_get_staff_name();
		return $array;
	}

	/**
	 * Adds image sizes for the staff to the image sizes panel.
	 */
	public function add_image_sizes( $sizes ) {
		$obj            = get_post_type_object( 'staff' );
		$post_type_name = $obj->labels->singular_name;
		$sizes['staff_entry'] = array(
			'label'   => sprintf( esc_html__( '%s Entry', 'total-theme-core' ), $post_type_name ),
			'width'   => 'staff_entry_image_width',
			'height'  => 'staff_entry_image_height',
			'crop'    => 'staff_entry_image_crop',
			'section' => 'staff',
		);
		$sizes['staff_post'] = array(
			'label'   => sprintf( esc_html__( '%s Post', 'total-theme-core' ), $post_type_name ),
			'width'   => 'staff_post_image_width',
			'height'  => 'staff_post_image_height',
			'crop'    => 'staff_post_image_crop',
			'section' => 'staff',
		);
		$sizes['staff_related'] = array(
			'label'   => sprintf( esc_html__( '%s Post Related', 'total-theme-core' ), $post_type_name ),
			'width'   => 'staff_related_image_width',
			'height'  => 'staff_related_image_height',
			'crop'    => 'staff_related_image_crop',
			'section' => 'staff',
		);
		return $sizes;
	}

	/**
	 * Tweak the page header
	 */
	public function alter_title( $args ) {
		if ( is_singular( 'staff' ) ) {
			$blocks = wpex_staff_single_blocks();
			if ( $blocks && is_array( $blocks ) && ! in_array( 'title', $blocks ) ) {
				$args['string']   = single_post_title( '', false );
				$args['html_tag'] = 'h1';
			}
		}
		return $args;
	}

	/**
	 * Adds the staff post type to the gallery metabox post types array.
	 */
	public function add_gallery_metabox( $types ) {
		$types[] = 'staff';
		return $types;
	}

	/**
	 * Adds field to user dashboard to connect to staff member.
	 */
	public function personal_options( $user ) {

		// Get staff members
		$staff_posts = get_posts( array(
			'post_type'      => 'staff',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		// Return if no staff
		if ( ! $staff_posts ) {
			return;
		}

		// Get staff meta
		$meta_value = get_user_meta( $user->ID, 'wpex_staff_member_id', true ); ?>

	    	<tr>
	    		<th scope="row"><?php esc_html_e( 'Connect to Staff Member', 'total-theme-core' ); ?></th>
				<td>
					<fieldset>
						<select type="text" id="wpex_staff_member_id" name="wpex_staff_member_id">
							<option value="" <?php selected( $meta_value, '', true ); ?>>&#8212;</option>
							<?php foreach ( $staff_posts as $id ) { ?>
								<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $meta_value, $id, true ); ?>><?php echo esc_html( get_the_title( $id ) ); ?></option>
							<?php } ?>
						</select>
					</fieldset>
				</td>
			</tr>

	    <?php

	}

	/**
	 * Saves user profile fields
	 */
	public function save_custom_profile_fields( $user_id ) {

		// Get meta
		$meta = isset( $_POST['wpex_staff_member_id'] ) ? $_POST['wpex_staff_member_id'] : '';

		// Get options
		$relations = get_option( 'wpex_staff_users_relations' );
		$relations = is_array( $relations ) ? $relations : array(); // sanitize

		// Add item
		if ( $meta ) {

			// Prevent staff ID's from being used more then 1x
			if ( array_key_exists( $meta, $relations ) ) {
				return;
			}

			// Update list of relations
			else {
				$relations[$user_id] = $meta;
				update_option( 'wpex_staff_users_relations', $relations );
			}

			// Update user meta
			update_user_meta( $user_id, 'wpex_staff_member_id', $meta );

		}

		// Remove item
		else {

			unset( $relations[ $user_id ] );
			update_option( 'wpex_staff_users_relations', $relations );
			delete_user_meta( $user_id, 'wpex_staff_member_id' );

		}

	}

	/**
	 * Alters post author bio data based on staff item relations
	 */
	public function post_author_bio_data( $data ) {
		$relations       = get_option( 'wpex_staff_users_relations' );
		$staff_member_id = isset( $relations[$data['post_author']] ) ? $relations[$data['post_author']] : '';
		if ( $staff_member_id ) {
			$data['author_name'] = get_the_title( $staff_member_id );
			$data['posts_url'] = get_the_permalink( $staff_member_id );
			$featured_image = wpex_get_post_thumbnail( array(
				'attachment' => get_post_thumbnail_id( $staff_member_id ),
				'size'       => 'wpex_custom',
				'width'      => $data['avatar_size'],
				'height'     => $data['avatar_size'],
				'alt'        => $data['author_name'],
			) );
			if ( $featured_image ) {
				$data['avatar'] = $featured_image;
			}
		}
		return $data;
	}

	/**
	 * Add custom VC modules
	 */
	public function vc_modules( $modules ) {
		$modules[] = 'staff_grid';
		$modules[] = 'staff_carousel';
		$modules[] = 'staff_social';
		return $modules;
	}

}
Staff::instance();