<?php
/**
 * Custom CSS Admin Panel.
 *
 * @package TotalThemeCore
 * @version 1.2
 */

namespace TotalThemeCore;

defined( 'ABSPATH' ) || exit;

final class CustomCSSPanel {

	/**
	 * Our single CustomCSSPanel instance.
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
	 * Create or retrieve the instance of CustomCSSPanel.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new CustomCSSPanel;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_submenu_page' ), 20 );
		add_action( 'admin_bar_menu', array( $this, 'adminbar_menu' ), 999 );
		add_action( 'admin_init', array( $this,'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Add sub menu page for the custom CSS input.
	 */
	public function add_admin_submenu_page() {
		$slug = defined( 'WPEX_THEME_PANEL_SLUG' ) ? WPEX_THEME_PANEL_SLUG : 'themes.php';
		add_submenu_page(
			$slug,
			esc_html__( 'Custom CSS', 'total' ),
			esc_html__( 'Custom CSS', 'total' ),
			'administrator',
			$slug . '-custom-css',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Add custom CSS to the adminbar since it will be used frequently.
	 */
	public function adminbar_menu( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$slug = defined( 'WPEX_THEME_PANEL_SLUG' ) ? WPEX_THEME_PANEL_SLUG : 'themes.php';
		$wp_admin_bar->add_node( array(
			'id'    => 'wpex_custom_css',
			'title' => esc_html__( 'Custom CSS', 'total' ),
			'href'  => esc_url( admin_url( 'admin.php?page=' . $slug . '-custom-css' ) ),
			'meta'  => array(
			'class' => 'wpex-custom-css',
			)
		) );
	}

	/**
	 * Register a setting and its sanitization callback.
	 */
	public function register_settings() {
		register_setting( 'wpex_custom_css', 'wpex_custom_css', array( $this, 'sanitize' ) );
	}

	/**
	 * Displays all messages registered to 'wpex-custom_css-notices'.
	 */
	public function notices() {
		settings_errors( 'wpex_custom_css_notices' );
	}

	/**
	 * Sanitization callback.
	 */
	public function sanitize( $option ) {
		$deprecated_custom_css = get_theme_mod( 'custom_css', null );

		// Sanitize and save theme mod
		if ( ! empty( $option ) ) {

			if ( function_exists( 'wp_get_custom_css' ) && ! $deprecated_custom_css ) {

				wp_update_custom_css_post( $option );

			} else {

				set_theme_mod( 'custom_css', wp_strip_all_tags( $option ) );

			}

		} else {

			if ( function_exists( 'wp_get_custom_css' ) && ! $deprecated_custom_css ) {

				wp_update_custom_css_post( '' );

			} else {

				remove_theme_mod( 'custom_css' );

			}

		}

		// Return notice
		add_settings_error(
			'wpex_custom_css_notices',
			'settings_updated',
			esc_html__( 'Settings saved.', 'total' ),
			'updated'
		);

		// Lets save the custom CSS into a standard option as well for backup
		return wp_strip_all_tags( $option );
	}

	/**
	 * Load scripts.
	 */
	public function enqueue_scripts() {

		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

		// Enqueue code editor and settings for manipulating HTML.
		$settings = wp_enqueue_code_editor( array(
			'type' => 'text/css'
		) );

		// Bail if user disabled CodeMirror.
		if ( false === $settings ) {
			return;
		}

		wp_add_inline_script(
			'code-editor',
			sprintf(
				'jQuery( function() { wp.codeEditor.initialize( "wpex_custom_css", %s ); } );',
				wp_json_encode( $settings )
			)
		);

	}

	/**
	 * Settings page output.
	 */
	public function create_admin_page() {

		if ( function_exists( 'wp_get_custom_css' ) && ! get_theme_mod( 'custom_css', null ) ) {
			$custom_css = wp_get_custom_css();
		} else {
			$custom_css = get_theme_mod( 'custom_css', null );
		}

		$this->enqueue_scripts();

		?>

		<div class="wrap wpex-custom-css-panel-wrap">

			<h1><?php esc_html_e( 'Custom CSS', 'total' ); ?></h1>

			<p><?php esc_html_e( 'This panel hooks into the native WordPress custom CSS functionality which you can also locate under Appearance > Customize > Additional CSS.', 'total' ); ?></p>

			<hr />

			<div class="wpex-remember-to-save">
				<p><?php echo wp_kses_post( __( 'Don\'t forget to <a href="#">save your changes</a>', 'total' ) ); ?></p>
			</div>

			<div style="margin-top:20px;">
				<form method="post" action="options.php">
					<?php settings_fields( 'wpex_custom_css' ); ?>
					<table class="form-table">
						<tr valign="top">
							<td style="padding:0;">
								<textarea cols="70" rows="30" id="wpex_custom_css" name="wpex_custom_css"><?php echo wp_strip_all_tags( $custom_css ); ?></textarea>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>

		</div>

	<?php }

}
CustomCSSPanel::instance();