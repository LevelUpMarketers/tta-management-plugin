<?php
/**
 * Custom 404 Page Design
 *
 * @package Total WordPress theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class Custom404 {

	/**
	 * Start things up
	 */
	public function __construct() {

		// Admin Panel
		if ( wpex_is_request( 'admin' ) ) {
			add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
			add_action( 'admin_init', array( $this, 'register_page_options' ) );
		}

		// Frontend redirects
		if ( wpex_is_request( 'frontend' ) ) {

			// Redirect all pages home
			if ( get_theme_mod( 'error_page_redirect', false ) ) {
				add_filter( 'template_redirect', array( $this, 'redirect' ) );
			}

			// Display standard 404 page and register 404 page ID for custom page settings and content
			elseif ( get_theme_mod( 'error_page_content_id' ) ) {
				add_filter( 'wpex_post_id', array( $this, 'post_id' ) );
				add_filter( 'wpex_vc_css_ids', array( $this, 'vc_css_ids' ) );
			}

		}

	}

	/**
	 * Add sub menu page for the custom CSS input
	 */
	public function add_submenu_page() {
		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_html__( 'Custom 404', 'total' ),
			esc_html__( 'Custom 404', 'total' ),
			'administrator',
			WPEX_THEME_PANEL_SLUG . '-404',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Function that will register admin page options.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_setting
	 * @link http://codex.wordpress.org/Function_Reference/add_settings_section
	 * @link http://codex.wordpress.org/Function_Reference/add_settings_field
	 */
	public function register_page_options() {

		// Register settings
		register_setting( 'wpex_error_page', 'error_page', array( $this, 'sanitize' ) );

		// Add main section to our options page
		add_settings_section( 'wpex_error_page_main', false, array( $this, 'section_main_callback' ), 'wpex-custom-error-page-admin' );

		// Redirect field
		add_settings_field(
			'redirect',
			esc_html__( 'Redirect 404\'s', 'total' ),
			array( $this, 'redirect_field_callback' ),
			'wpex-custom-error-page-admin',
			'wpex_error_page_main'
		);

		// Custom Page ID
		add_settings_field(
			'error_page_id',
			esc_html__( 'Custom 404 Page', 'total' ),
			array( $this, 'content_id_field_callback' ),
			'wpex-custom-error-page-admin',
			'wpex_error_page_main'
		);

		// Title field
		add_settings_field(
			'error_page_title',
			esc_html__( '404 Page Title', 'total' ),
			array( $this, 'title_field_callback' ),
			'wpex-custom-error-page-admin',
			'wpex_error_page_main'
		);

		// Content field
		add_settings_field(
			'error_page_text',
			esc_html__( '404 Page Content', 'total' ),
			array( $this, 'content_field_callback' ),
			'wpex-custom-error-page-admin',
			'wpex_error_page_main'
		);

	}

	/**
	 * Sanitization callback
	 */
	public function sanitize( $options ) {

		// Set theme mods
		if ( isset( $options['redirect'] ) ) {
			set_theme_mod( 'error_page_redirect', 1 );
		} else {
			remove_theme_mod( 'error_page_redirect' );
		}

		if ( ! empty( $options['title'] ) ) {
			set_theme_mod( 'error_page_title', $options['title'] );
		} else {
			remove_theme_mod( 'error_page_title' );
		}

		if ( ! empty( $options['text'] ) ) {
			set_theme_mod( 'error_page_text', $options['text'] );
		} else {
			remove_theme_mod( 'error_page_text' );
		}

		if ( ! empty( $options['content_id'] ) ) {
			set_theme_mod( 'error_page_content_id', $options['content_id'] );
		} else {
			remove_theme_mod( 'error_page_content_id' );
		}

		// Set options to nothing since we are storing in the theme mods
		$options = '';
		return $options;
	}

	/**
	 * Main Settings section callback
	 */
	public function section_main_callback( $options ) {
		// Leave blank
	}

	/**
	 * Fields callback functions
	 */

	// Redirect field
	public function redirect_field_callback() {
		$val = get_theme_mod( 'error_page_redirect' );
		echo '<input type="checkbox" name="error_page[redirect]" id="error-page-redirect" value="'. esc_attr( $val ) .'" '. checked( $val, true, false ) .'> ';
		echo '<span class="description">'. esc_html__( 'Automatically 301 redirect all 404 errors to your homepage.', 'total' ) .'</span>';
	}

	// Custom Error Page ID
	public function content_id_field_callback() {

		$page_id = get_theme_mod( 'error_page_content_id' );

		wp_dropdown_pages( array(
			'echo'             => true,
			'selected'         => $page_id,
			'name'             => 'error_page[content_id]',
			'id'               => 'error-page-content-id',
			'class'            => 'wpex-chosen',
			'show_option_none' => esc_html__( 'None', 'total' ),
		) ); ?>

		<br />

		<p class="description"><?php esc_html_e( 'Select a custom page to replace your default 404 page content.', 'total' ) ?></p>

		<?php if ( $page_id ) { ?>

			<br />

			<div id="wpex-footer-builder-edit-links">

				<a href="<?php echo admin_url( 'post.php?post='. $page_id .'&action=edit' ); ?>" class="button" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Backend Edit', 'total' ); ?>
				</a>

				<?php if ( WPEX_VC_ACTIVE ) { ?>

					<a href="<?php echo admin_url( 'post.php?vc_action=vc_inline&post_id='. $page_id .'&post_type=page' ); ?>" class="button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Frontend Edit', 'total' ); ?></a>

				<?php } ?>

				<a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" class="button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Preview', 'total' ); ?></a>

			</div>

		<?php } ?>

	<?php }

	// Title field
	public function title_field_callback() { ?>
		<input type="text" name="error_page[title]" id="error-page-title" value="<?php echo get_theme_mod( 'error_page_title' ); ?>">
		<p class="description"><?php esc_html_e( 'Enter a custom title for your 404 page.', 'total' ) ?></p>
	<?php }

	// Content field
	public function content_field_callback() {
		wp_editor( get_theme_mod( 'error_page_text' ), 'error_page_text', array(
			'textarea_name' => 'error_page[text]'
		) );
	}

	/**
	 * Settings page output
	 */
	public function create_admin_page() {

		wp_enqueue_style( 'wpex-chosen' );
		wp_enqueue_script( 'wpex-chosen' );

		wp_enqueue_script(
			'wpex-custom-404',
			wpex_asset_url( 'js/dynamic/admin/wpex-custom-404.min.js' ),
			array( 'jquery' ),
			WPEX_THEME_VERSION
		);

		?>

		<div class="wrap">

			<h1><?php esc_html_e( 'Custom 404', 'total' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'wpex_error_page' ); ?>
				<?php do_settings_sections( 'wpex-custom-error-page-admin' ); ?>
				<?php submit_button(); ?>
			</form>

		</div>

	<?php }

	/**
	 * Redirect all pages to the under cronstruction page if user is not logged in
	 *
	 * @link  http://codex.wordpress.org/Plugin_API/Action_Reference/template_redirect
	 * @since 1.6.0
	 */
	public function redirect() {
		if ( is_404() ) {
			wp_redirect( esc_url( home_url( '/' ) ), 301 );
			exit();
		}
	}

	/**
	 * Custom VC CSS for 404 custom page design
	 *
	 * @since 3.6.0
	 */
	public function post_id( $post_id ) {
		if ( is_404() && $error_page = wpex_parse_obj_id( get_theme_mod( 'error_page_content_id' ), 'page' ) ) {
			$post_id = $error_page;
		}
		return $post_id;
	}

	/**
	 * Custom VC CSS for 404 custom page design
	 */
	public function vc_css_ids( $ids ) {
		if ( is_404() ) {
			$ids[] = wpex_get_current_post_id();
		}
		return $ids;
	}

}
new Custom404();