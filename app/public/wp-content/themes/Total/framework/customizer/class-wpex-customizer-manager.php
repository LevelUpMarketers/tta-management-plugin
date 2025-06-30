<?php
/**
 * Customizer Manager
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Customizer_Manager' ) ) {

	class WPEX_Customizer_Manager extends WPEX_Customizer {

		/**
		 * Start things up.
		 */
		public function __construct() {
			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters.
		 */
		public function init_hooks() {
			add_action( 'admin_menu', array( $this, 'add_admin_submenu_page' ), 40 );
			add_action( 'admin_init', array( $this,'admin_options' ) );
		}

		/**
		 * Add sub menu page for the custom CSS input.
		 */
		public function add_admin_submenu_page() {
			add_submenu_page(
				WPEX_THEME_PANEL_SLUG,
				esc_html__( 'Customizer Manager', 'total' ),
				esc_html__( 'Customizer Manager', 'total' ),
				'administrator',
				WPEX_THEME_PANEL_SLUG . '-customizer',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Function that will register admin page options.
		 */
		public function admin_options() {
			register_setting( 'wpex_customizer_editor', 'wpex_disabled_customizer_panels', array(
				'type' => 'array',
				'sanitize_callback' => array( $this, 'sanitize_callback' )
			) );
		}

		/**
		 * Save options.
		 */
		public function sanitize_callback( $option ) {

			$sanitized_option = array();

			$panels = $this->panels();

			foreach ( $panels as $id => $val ) {

				if ( ! isset( $option[ $id ] ) ) {
					$sanitized_option[] = $id;
				}

			}

			$option = $sanitized_option;

			return $option;
		}

		/**
		 * Settings page output.
		 *
		 */
		public function create_admin_page() {

			wp_enqueue_style( 'wpex-admin-pages' );
			wp_enqueue_script( 'wpex-admin-pages' );

			?>

			<div id="wpex-customizer-manager-admin-page" class="wrap">

				<h1><?php esc_html_e( 'Customizer Manager', 'total' ); ?></h1>

				<p><?php esc_html_e( 'Disable sections in the Customizer that you no longer need. It will NOT alter any options already set in the Customizer or disable sections visible on the front-end of your site.', 'total' ); ?></p>

				<h2 class="nav-tab-wrapper">
					<a href="#" class="nav-tab nav-tab-active"><?php esc_html_e( 'Panels', 'total' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="nav-tab"><?php esc_html_e( 'Customizer', 'total' ); ?> <span class="dashicons dashicons-external"></span></a>
				</h2>

				<div class="wpex-check-uncheck">
					<a href="#" class="wpex-customizer-check-all"><?php esc_html_e( 'Check all', 'total' ); ?></a> | <a href="#" class="wpex-customizer-uncheck-all"><?php esc_html_e( 'Uncheck all', 'total' ); ?></a>
				</div>

				<form method="post" action="options.php">

					<?php settings_fields( 'wpex_customizer_editor' ); ?>

					<table class="form-table wpex-customizer-editor-table">
						<?php
						// Get panels
						$panels = $this->panels();

						// Get disabled panels
						$disabled_panels = get_option( 'wpex_disabled_customizer_panels', array() );

						// Loop through panels and add checkbox
						foreach ( $panels as $id => $val ) {

							// Parse panel data
							$title     = isset( $val['title'] ) ? $val['title'] : $val;
							$condition = isset( $val['condition'] ) ? $val['condition'] : true;

							// Check if option should be hidden
							$is_hidden = isset( $val['condition'] ) && ! call_user_func( $val['condition'] ) ? true : false;

							// Check if a given section is enabled
							$is_enabled = wpex_has_customizer_panel( $id ) ? 'on' : ''; ?>

							<tr valign="top"<?php if ( $is_hidden ) echo ' style="display:none;"'; ?>>
								<th scope="row"><?php echo esc_html( $title ); ?></th>
									<td>
									<?php
									// Condition isn't met so add setting as a hidden item
									if ( $is_hidden ) { ?>
										<input type="hidden" id="wpex_disabled_customizer_panels[<?php echo esc_attr( $id ); ?>]" name="wpex_disabled_customizer_panels[<?php echo esc_attr( $id ); ?>]"<?php checked( $is_enabled, 'on' ); ?>>
									<?php }
									// Display setting
									else { ?>
										<input class="wpex-customizer-editor-checkbox" type="checkbox" name="wpex_disabled_customizer_panels[<?php echo esc_attr( $id ); ?>]"<?php checked( $is_enabled, 'on' ); ?>>
									<?php } ?>
								</td>
							</tr>

						<?php } ?>

					</table>

					<?php submit_button(); ?>

				</form>

			</div><!-- .wrap -->

		<?php }

	}

}
new WPEX_Customizer_Manager;