<?php
/**
 * Under Construction Addon
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class UnderConstruction {

	/**
	 * Our single UnderConstruction instance.
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
	 *
	 * @return void
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of UnderConstruction.
	 *
	 * @return UnderConstruction
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new UnderConstruction;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		if ( wpex_is_request( 'admin' ) ) {
			add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 5 );
			add_action( 'admin_init', array( $this, 'register_page_options' ) );
		}

		if ( wpex_is_request( 'frontend' ) && get_theme_mod( 'under_construction', false ) ) {
			add_action( 'template_redirect', array( $this, 'redirect' ) );
		}

	}

	/**
	 * Add sub menu page for the custom CSS input.
	 *
	 * @since 2.0.0
	 */
	public function add_submenu_page() {

		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_html__( 'Under Construction', 'total' ),
			esc_html__( 'Under Construction', 'total' ),
			'administrator',
			WPEX_THEME_PANEL_SLUG . '-under-construction',
			array( $this, 'create_admin_page' )
		);

	}

	/**
	 * Function that will register admin page options.
	 *
	 * @since 2.0.0
	 */
	public function register_page_options() {

		// Register settings
		register_setting( 'wpex_under_construction', 'under_construction', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
		) );

		// Add main section to our options page
		add_settings_section( 'wpex_under_construction_main', false, array( $this, 'section_main_callback' ), 'wpex-under-construction-admin' );

		// Redirect field
		add_settings_field(
			'under_construction',
			esc_html__( 'Enable Under Constuction', 'total' ),
			array( $this, 'redirect_field_callback' ),
			'wpex-under-construction-admin',
			'wpex_under_construction_main'
		);

		// Custom Page ID
		add_settings_field(
			'under_construction_page_id',
			esc_html__( 'Under Construction Page', 'total' ),
			array( $this, 'content_id_field_callback' ),
			'wpex-under-construction-admin',
			'wpex_under_construction_main'
		);

		// Exclude pages
		add_settings_field(
			'under_construction_exclude_pages',
			esc_html__( 'Exclude Pages From Redirection', 'total' ),
			array( $this, 'under_construction_exclude_pages_callback' ),
			'wpex-under-construction-admin',
			'wpex_under_construction_main'
		);

	}

	/**
	 * Sanitization callback.
	 *
	 * @since 2.0.0
	 */
	public function sanitize_settings( $settings ) {

		if ( isset ( $settings['enable'] ) ) {
			set_theme_mod( 'under_construction', 1 );
		} else {
			remove_theme_mod( 'under_construction' );
		}

		if ( isset( $settings['content_id'] ) ) {
			set_theme_mod( 'under_construction_page_id', $settings['content_id'] );
		}

		if ( isset( $settings['exclude_pages'] ) && is_array( $settings['exclude_pages'] ) ) {
			set_theme_mod( 'under_construction_exclude_pages', $settings['exclude_pages'] );
		} else {
			remove_theme_mod( 'under_construction_exclude_pages' );
		}

		return '';
	}

	/**
	 * Main Settings section callback.
	 *
	 * @since 2.0.0
	 */
	public function section_main_callback( $options ) {
		// Leave blank
	}

	/**
	 * Fields callback functions.
	 *
	 * @since 2.0.0
	 */

	// Enable admin field
	public function redirect_field_callback() {
		$val    = get_theme_mod( 'under_construction', false );
		echo '<input type="checkbox" name="under_construction[enable]" value="' . esc_attr( $val ) . '" ' . checked( $val, true, false ) . ' id="wpex-under-construction-enable"> ';
	}

	// Page ID admin field
	public function content_id_field_callback() {

		wp_enqueue_script(
			'wpex-chosen',
			wpex_asset_url( 'lib/chosen/chosen.jquery.min.js' ),
			array( 'jquery' ),
			'1.4.1'
		);

		wp_enqueue_style(
			'wpex-chosen',
			wpex_asset_url( 'lib/chosen/chosen.min.css' ),
			false,
			'1.4.1'
		);

		// Get construction page id
		$page_id = get_theme_mod( 'under_construction_page_id' ); ?>

		<select name="under_construction[content_id]" id="wpex-under-construction-page-select" class="wpex-chosen">

			<option value=""><?php esc_html_e( 'None', 'total' ); ?></option>

			<?php
			$pages = get_pages( array(
				'exclude' => get_option( 'page_on_front' ),
			) );
			if ( $pages ) {
				foreach ( $pages as $page ) {
					echo '<option value="' . absint( $page->ID ) . '"' . selected( $page_id, $page->ID, false ) . '>' . esc_attr( $page->post_title ) . '</option>';
				}
			} ?>

		</select>

		<p class="description"><?php esc_html_e( 'Select your custom page for your under construction display. Every page and post will redirect to your selected page for non-logged in users.', 'total' ) ?></p>

		<?php
		// Display edit and preview buttons
		if ( $page_id ) { ?>

			<p style="margin:20px 0 0;">

			<a href="<?php echo admin_url( 'post.php?post='. $page_id .'&action=edit' ); ?>" class="button" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Backend Edit', 'total' ); ?>
            </a>

            <?php if ( WPEX_VC_ACTIVE ) { ?>
                <a href="<?php echo admin_url( 'post.php?vc_action=vc_inline&post_id='. $page_id .'&post_type=page' ); ?>" class="button" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Frontend Edit', 'total' ); ?>
                </a>
            <?php } ?>

            <a href="<?php echo get_permalink( $page_id ); ?>" class="button" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Preview', 'total' ); ?>
            </a>

		<?php } ?>

	<?php }

	// Exclude pages callback
	public function under_construction_exclude_pages_callback() {
		$exclude_pages = (array) get_theme_mod( 'under_construction_exclude_pages', false );
		$pages = get_pages( array(
			'exclude' => get_option( 'page_on_front' ),
		) );
		if ( ! $pages ) {
			return;
		} ?>
		<select data-placeholder="<?php esc_html_e( 'Click to select&hellip;', 'total' ); ?>" multiple name="under_construction[exclude_pages][]" id="wpex-under-construction-exclude-pages-select" class="wpex-chosen-multiselect">
			<option value=""><?php esc_html_e( 'None', 'total' ); ?></option>
			<?php
			foreach ( $pages as $page ) {
				echo '<option value="' . absint( $page->ID ) . '"' . selected( in_array( $page->ID, $exclude_pages ), true, false ) . '>' . esc_attr( $page->post_title ) . '</option>';
			} ?>
		</select>
	<?php }

	/**
	 * Settings page output.
	 *
	 * @since 2.0.0
	 */
	public function create_admin_page() {

		wp_enqueue_style( 'wpex-chosen' );
		wp_enqueue_script( 'wpex-chosen' );

		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

		?>

		<div class="wrap">

			<h1><?php esc_html_e( 'Under Construction', 'total' ); ?></h1>

			<p><?php esc_html_e( 'Redirect all non-logged in traffic to a specific page. Useful when building the site or making changes and do not want to make it public yet.', 'total' ); ?></p>

			<hr />

			<form method="post" action="options.php">
				<?php settings_fields( 'wpex_under_construction' ); ?>
				<?php do_settings_sections( 'wpex-under-construction-admin' ); ?>
				<?php submit_button(); ?>
			</form>

		</div>

	<?php }

	/**
	 * Redirect all pages to the under cronstruction page if user is not logged in.
	 *
	 * @since 1.6.0
	 */
	public function redirect() {
		$redirect  = false;
		$permalink = null;

		// Get under construction page ID
		$page_id = intval( wpex_parse_obj_id( get_theme_mod( 'under_construction_page_id' ), 'page' ) );

		// Return if ID not defined
		if ( ! $page_id ) {
			return;
		}

		// Return if under construction is the same as posts page because it creates an endless loop
		if ( $page_id == get_option( 'page_for_posts' ) ) {
			return;
		}

		// Check excluded pages
		if ( $exclude_pages = get_theme_mod( 'under_construction_exclude_pages', null ) ) {
			if ( is_array( $exclude_pages ) && in_array( wpex_get_current_post_id(), $exclude_pages ) ) {
				return;
			}
		}

		// If user is not logged in redirect them
		if ( ! is_user_logged_in() ) {

			// Get permalink
			$permalink = get_permalink( $page_id );

			// Redirect to under construction page
			if ( $permalink && ! is_page( $page_id ) ) {
				$redirect = true;
			}

		}

		// Apply filters
		$redirect = apply_filters( 'wpex_has_under_construction_redirect', $redirect );

		// Redirect
		if ( $redirect && $permalink ) {
			wp_redirect( esc_url( $permalink ), 307 );
			exit();
		}

	}

}
UnderConstruction::instance();