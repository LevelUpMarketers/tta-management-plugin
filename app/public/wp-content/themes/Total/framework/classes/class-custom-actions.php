<?php
/**
 * Custom user actions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class CustomActions {

	/**
	 * Our single CustomActions instance.
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
	 * Create or retrieve the instance of CustomActions.
	 *
	 * @return CustomActions
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new CustomActions;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 3.0.0
	 */
	public function init_hooks() {

		if ( wpex_is_request( 'admin' ) ) {
			add_action( 'admin_menu', array( $this, 'add_page' ), 40 );
			add_action( 'admin_init', array( $this,'register_settings' ) );
		}

		if ( wpex_is_request( 'frontend' ) ) {
			add_action( 'init', array( $this,'output' ) );
		}

	}

	/**
	 * Add sub menu page.
	 *
	 * @since 3.0.0
	 */
	public function add_page() {
		$slug = WPEX_THEME_PANEL_SLUG;
		add_submenu_page(
			$slug,
			esc_html__( 'Custom Actions', 'total' ),
			esc_html__( 'Custom Actions', 'total' ),
			'administrator',
			$slug . '-user-actions',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Register a setting and its sanitization callback.
	 *
	 * @since 3.0.0
	 */
	public function register_settings() {
		register_setting(
			'wpex_custom_actions',
			'wpex_custom_actions',
			array( $this, 'admin_sanitize' )
		);
	}

	/**
	 * Main Sanitization callback.
	 *
	 * @since 3.0.0
	 */
	public function admin_sanitize( $options ) {

		if ( ! empty( $options ) ) {

			// Loop through options and save them
			foreach ( $options as $key => $val ) {

				// Delete action if empty
				if ( empty( $val['action'] ) ) {
					unset( $options[$key] );
				}

				// Validate settings
				else {

					// Sanitize action @todo don't allow javascript anymore?
					//$options[$key]['action'] = wp_kses_post( $val['action'] );

					// Priority must be a number
					if ( ! empty( $val['priority'] ) ) {
						$options[$key]['priority'] = intval( $val['priority'] );
					}


				}
			}

			return $options;

		}

	}

	/**
	 * Settings page.
	 *
	 * @since 3.0.0
	 */
	public function create_admin_page() {

		wp_enqueue_style(
			'wpex-custom-actions-admin',
			get_theme_file_uri( '/assets/css/wpex-custom-actions-admin.css' ),
			array(),
			WPEX_THEME_VERSION,
			'all'
		);

		wp_enqueue_script(
			'wpex-custom-actions-admin',
			get_theme_file_uri( '/assets/js/dynamic/admin/wpex-custom-actions-admin.min.js' ),
			array( 'jquery' ),
			WPEX_THEME_VERSION,
			false
		);

		?>

		<div class="wrap wpex-ca-admin-wrap">

			<h1><?php esc_html_e( 'Custom Actions', 'total' ); ?></h1>

			<p><?php esc_html_e( 'Here you can insert HTML code into any section of the theme. PHP code is not allowed for security reasons. If you wish to insert PHP code into a theme action you will want to use a child theme or shortcodes in the fields below.', 'total' ); ?></p>

			<hr />

			<form method="post" action="options.php">

				<?php settings_fields( 'wpex_custom_actions' ); ?>

				<?php $options = get_option( 'wpex_custom_actions' ); ?>

				<div class="wpex-ca-admin-inner">

					<div class="wpex-ca-admin-list">

						<?php
						// Get hooks
						$wp_hooks = array(
							'wp_hooks' => array(
								'label' => 'WordPress',
								'hooks' => array(
									'wp_head',
									'wp_body_open',
									'wp_footer',
								),
							),
							'html' => array(
								'label' => 'HTML',
								'hooks' => array( 'wpex_hook_after_body_tag' )
							)
						);

						// Theme hooks
						$theme_hooks = wpex_theme_hooks();

						// Remove header hooks if builder is enabled
						if ( wpex_header_builder_id() ) {
							unset( $theme_hooks['header'] );
							unset( $theme_hooks['header_logo'] );
							unset( $theme_hooks['main_menu'] );
						}

						// Combine hooks
						$hooks = ( $wp_hooks + $theme_hooks );

						// Loop through sections
						foreach( $hooks as $section ) : ?>

							<div class="wpex-ca-admin-list-group">

								<h2><?php echo esc_html( $section['label'] ); ?></h2>

								<?php
								// Loop through hooks
								$hooks = $section['hooks'];

								foreach ( $hooks as $hook ) :

									// Get data
									$action   = ! empty( $options[$hook]['action'] ) ? $options[$hook]['action'] : '';
									$priority = isset( $options[$hook]['priority'] ) ? intval( $options[$hook]['priority'] ) : 10;

									?>

										<div class="wpex-ca-admin-list-item wpex-ca-closed<?php if ( $action ) echo ' wpex-ca-admin-not-empty'; ?>">

											<div class="wpex-ca-admin-list-item-header">
												<h3><?php echo wp_strip_all_tags( $hook ); ?></span></h3>
												<div class="hide-if-no-js">
													<button class="wpex-ca-admin-toggle" aria-expanded="false">
														<span class="screen-reader-text"><?php esc_html_e( 'Toggle fields for action hook:', 'total' ); ?> <?php echo wp_strip_all_tags( $hook ); ?></span>
														<span class="dashicons dashicons-arrow-down" aria-hidden="true"></span>
													</button>
												</div>
											</div>

											<div class="wpex-ca-admin-list-item-fields">

												<p>
													<label for="wpex_custom_actions[<?php echo esc_attr( $hook ); ?>][action]"><?php esc_html_e( 'Code', 'total' ); ?></label>
													<textarea id="wpex_custom_actions[<?php echo esc_attr( $hook ); ?>][action]" placeholder="<?php esc_attr_e( 'Enter your custom action here&hellip;', 'total' ); ?>" name="wpex_custom_actions[<?php echo esc_attr( $hook ); ?>][action]" rows="10" cols="50" style="width:100%;"><?php echo esc_textarea( $action ); ?></textarea>
												</p>

												<p class="wpex-clr">
													<label for="wpex_custom_actions[<?php echo esc_attr( $hook ); ?>][priority]"><?php esc_html_e( 'Priority', 'total' ); ?></label>
													<input id="wpex_custom_actions[<?php echo esc_attr( $hook ); ?>][priority]" name="wpex_custom_actions[<?php echo esc_attr( $hook ); ?>][priority]" type="number" value="<?php echo esc_attr( $priority ); ?>">
												</p>

											</div><!-- .wpex-ca-admin-list-item-fields -->

										</div><!-- .wpex-ca-admin-list-item -->

								<?php endforeach; ?>

							</div><!-- .wpex-ca-admin-list-group -->

						<?php endforeach; ?>

					</div><!-- .wpex-ca-admin-list -->

					<div class="wpex-ca-admin-save">

						<h3><?php esc_html_e( 'Save Your Actions', 'total' ); ?></h3>

						<p><?php esc_html_e( 'Click the button below to save your custom actions.', 'total' ); ?></p>

						<?php submit_button(); ?>

					</div><!-- .wpex-ca-admin-save -->

				</div><!-- .wpex-ca-admin -->

			</form>

		</div><!-- .wrap -->

	<?php }

	/**
	 * Outputs code on the front-end
	 *
	 * @since 3.0.0
	 */
	public function output() {

		// Get actions
		$actions = get_option( 'wpex_custom_actions' );

		// Return if actions are empty
		if ( empty( $actions ) ) {
			return;
		}

		// Loop through options
		foreach ( $actions as $key => $val ) {
			if ( ! empty( $val['action'] ) ) {
				$priority = isset( $val['priority'] ) ? intval( $val['priority'] ) : 10;
				add_action( $key, array( $this, 'execute_action' ), $priority );
			}
		}

	}

	/**
	 * Used to execute an action
	 *
	 * @since 3.0.0
	 */
	public function execute_action() {

		$hook    = current_filter();
		$actions = get_option( 'wpex_custom_actions' );
		$output  = $actions[$hook]['action'];

		if ( $output && empty( $actions[$hook]['php'] ) ) {
			//$output = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $output ); // remove script tags
			//$output = wp_kses_post( $output ); // @todo
			echo do_shortcode( $output );
		}

	}

}
CustomActions::instance();