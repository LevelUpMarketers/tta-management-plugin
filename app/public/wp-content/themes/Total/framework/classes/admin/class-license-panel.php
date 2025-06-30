<?php
/**
 * Theme License Activation and De-activation
 *
 * @package Total WordPress theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class LicensePanel {

	/**
	 * Our single LicensePanel instance.
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
	 * Create or retrieve the instance of LicensePanel.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new LicensePanel;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		if ( false === get_transient( 'wpex_verify_active_license' ) ) {
			add_action( 'admin_init', array( $this, 'verify_license' ) );
		}

		add_action( 'admin_menu', array( $this, 'add_admin_submenu_page' ), 1 );
		add_action( 'wp_ajax_wpex_theme_license_form', array( $this, 'license_form_ajax' ) );

		if ( ! wpex_get_theme_license() && ! get_option( 'total_dismiss_license_notice', false ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}

	}

	/**
	 * Verify license every
	 *
	 * @since 4.5.4
	 */
	public function verify_license() {
		wpex_verify_active_license();
		set_transient( 'wpex_verify_active_license', 1, WEEK_IN_SECONDS );
	}

	/**
	 * Return sanitized current site URL
	 *
	 * @since 4.5
	 */
	public function get_site_url() {
		return rawurlencode( trim( site_url() ) );
	}

	/**
	 * Add sub menu page
	 *
	 * @since 4.5
	 */
	public function add_admin_submenu_page() {
		add_submenu_page(
			WPEX_THEME_PANEL_SLUG,
			esc_html__( 'Theme License', 'total' ),
			esc_html__( 'Theme License', 'total' ),
			'administrator',
			WPEX_THEME_PANEL_SLUG . '-theme-license',
			array( $this, 'theme_license_page' )
		);
	}

	/**
	 * Settings page output
	 *
	 * @since 4.5
	 */
	public function theme_license_page() {

		wp_enqueue_style( 'wpex-admin-pages' );
		wp_enqueue_script( 'wpex-admin-pages' );

		if ( isset( $_GET['troubleshoot'] ) ) {
			$this->troubleshoot();
			return;
		}

		$license = wpex_get_theme_license();

		$license = wpex_verify_active_license( $license ) ? $license : null;

		$is_dev = get_option( 'active_theme_license_dev' );

		$license_cleared = ! empty( $_GET[ 'license-cleared' ] ) ? true : false; ?>

		<div id="wpex-admin-page" class="wrap wpex-theme-license-page">

			<h1><?php esc_html_e( 'Theme License', 'total' ); ?></h1>

			<?php if ( $license || $license_cleared ) {

				$notice_type = 'updated';
				if ( $is_dev || $license_cleared ) {
					$notice_type = 'notice-warning';
				} ?>

				<div class="wpex-admin-ajax-notice notice <?php echo esc_attr( $notice_type ); ?>">
					<?php if ( $license_cleared ) { ?>
						<p><?php echo wp_kses_post( __( 'The current URL did not match the URL of the registered license. Your license has been removed from this site but remains active on the original URL. You can now enter a new license for this site.', 'total' ) ); ?>
					<?php } elseif ( $is_dev ) { ?>
						<p><?php esc_html_e( 'Your site is currently active as a development environment.', 'total' ); ?></p>
					<?php } else { ?>
						<p><?php esc_html_e( 'Congratulations. Your theme license is active.', 'total' ); ?></p>
					<?php } ?>
				</div>

			<?php } else { ?>

				<div class="wpex-admin-ajax-notice notice"></div>

			<?php } ?>

			<div class="wpex-theme-license-box wpex-boxed-shadow">

				<h2><?php esc_html_e( 'Verify your License', 'total' ); ?></h2>

				<p class="wpex-top-note"><?php echo wp_kses_post( __( 'Enter your purchase code below and click the activate button or hit enter. You can learn how to find your purchase code <a target="_blank" rel="noopener noreferrer" href="https://wpexplorer-themes.com/total/docs/how-to-find-your-total-theme-license/">here</a>.', 'total' ) ); ?></p>

				<form method="post" id="wpex-theme-license-form">

					<?php if ( $license ) { ?>

						<input type="text" id="wpex_license" name="license" placeholder="<?php echo esc_attr( $license ); ?>" value="<?php echo esc_attr( $license ); ?>" readonly="readonly" autocomplete="off" onclick="select()" />

					<?php } else { ?>

						<input type="text" id="wpex_license" name="license" placeholder="<?php esc_html_e( 'Enter your purchase code here.', 'total' ); ?>" autocomplete="off" />

					<?php } ?>

					<?php if ( ! $license ) { ?>
						<p class="wpex-license-checkfield"><input type="checkbox" id="wpex_dev_license" name="devlicense" /> <label for="wpex_dev_license" class="description"><?php echo wp_kses_post( __( 'Check this box if this is your development environment (not the final or live website)', 'total' ) ); ?></label></p>
					<?php } ?>

					<?php wp_nonce_field( 'wpex_theme_license_form_nonce', 'wpex_theme_license_form_nonce' ); ?>

					<p class="submit">

						<?php
						$submit_classes = 'primary button-hero ';
						$submit_classes .= $license ? 'deactivate' : 'activate';
						$activate_txt   = esc_html__( 'Activate your license', 'total' );
						$deactivate_txt = esc_html__( 'Deactivate your license', 'total' );
						submit_button(
							$license ? $deactivate_txt : $activate_txt,
							$submit_classes,
							'submit',
							false,
							array(
								'data-activate'   => $activate_txt,
								'data-deactivate' => $deactivate_txt,
							)
						); ?>

						<img src="<?php echo esc_url( includes_url( 'images/spinner.gif' ) ); ?>" class="wpex-spinner" width="20" height="20" alt="<?php esc_html_e( 'Loading&hellip;', 'total' ); ?>" />

					</p>

				</form>

				<p class="description"><?php echo wp_kses_post( __( 'A purchase code (license) is only valid for <strong>One WordPress Installation</strong> (single or multisite). Are you already using this theme on another installation? Purchase <a target="_blank" rel="noopener noreferrer" href="https://themeforest.net/item/total-responsive-multipurpose-wordpress-theme/6339019?ref=WPExplorer&license=regular&open_purchase_for_item_id=6339019">new license here</a> to get your new purchase code. If you are running a multisite network you only need to activate your license on the main site.', 'total' ) ); ?></p>

			</div><!-- .wpex-theme-license-box -->

			<div class="wpex-license-troubleshoot">
				<a href="https://wpexplorer-themes.com/support/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Manage Licenses', 'total' ); ?></a> | <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpex-panel-theme-license&troubleshoot=1' ) ); ?>"><?php esc_html_e( 'Troubleshoot', 'total' ); ?></a>
			</div>

		</div><!-- .wrap -->

	<?php }

	/**
	 * Troubleshoot
	 *
	 * @since 5.0
	 */
	public function troubleshoot() {

		echo '<h1>License API Troubleshooting</h1>';

		if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
			echo 'Looks like the wp_remote_retrieve_response_code function doesnt exist, make sure you update WordPress';
			return;
		}

		$remote_response = wp_remote_get( 'https://wpexplorer-themes.com/activate-license/?troubleshoot=1' );

		$response_code = intval( wp_remote_retrieve_response_code( $remote_response ) );

		echo '<ul>';

			switch ( $response_code ) {
				case 200:
					echo '<li><strong class="wpex-green-span">' . json_decode( wp_remote_retrieve_body( $remote_response ) ) . '</strong></li>';
					break;
				case 301:
					echo '<li><strong>301 Error</strong>: Cloudflare Firewall blocking access.</li>';
					break;
				case 403:
					echo '<li><strong>Forbidden</strong>: Your server has been blocked by our firewall for security reasons.</li>';
					break;
				case 404:
					echo '<li><strong>404 Error</strong>: Please contact the theme developer for assistance.</li>';
					break;
				default:
					if ( isset( $remote_response->errors ) && is_array( $remote_response->errors ) ) {
						foreach ( $remote_response->errors as $k => $v ) {
							if ( empty( $v[0] ) ) {
								continue;
							}
							echo '<li><strong>' . $k . '</strong>: ' . $v[0] . '</li>';
						}

					}
					break;
			}

		echo '</ul>';

	}

	/**
	 * Activate License
	 *
	 * @since 4.5
	 */
	public function activate_license( $license, $dev, $response ) {
		$args = array(
			'market'     => 'envato',
			'product_id' => '6339019',
			'license'    => $license,
			'url'        => $this->get_site_url(),
		);
		if ( $dev ) {
			$args['dev'] = '1';
		}
		$remote_url = add_query_arg( $args, 'https://wpexplorer-themes.com/activate-license/' );
		$remote_response = wp_remote_get( $remote_url );
		if ( is_wp_error( $remote_response ) ) {
			$response['message'] = $response->get_error_message();
		} else {
			$remote_response_code = wp_remote_retrieve_response_code( $remote_response );
			$response['response_code'] = $remote_response_code;
			if ( 200 == $remote_response_code ) {
				$result = json_decode( wp_remote_retrieve_body( $remote_response ) );
				$status = $result->status;
				if ( 'active' == $status ) {
					$response['success'] = true;
					$response['message'] = esc_html__( 'Congratulations. Your theme license is active.', 'total' );
					$response['messageClass'] = 'updated';
					update_option( 'active_theme_license', $license );
					if ( $dev ) {
						update_option( 'active_theme_license_dev', true );
					}
				} else {
					switch ( $status ) {
						case 'api_error':
							$response['message'] = esc_html__( 'The license code is not properly formated or couldn\'t be validated by the Envato API.', 'total' );
							break;
						case 'invalid':
							$response['message'] = esc_html__( 'This license code is not valid.', 'total' );
							break;
						case 'duplicate':
							$response['message'] = esc_html__( 'This license is already in use. Click the "manage licenses" link below to log in with your Envato ID and manage your licenses.', 'total' );
							break;
						default:
							if ( ! empty( $result->error ) ) {
								$response['message'] = $result['error'];
							}
							break;
					}
					$response['messageClass'] = 'notice-error';
				}
			} else {
				$response['message'] = esc_html( 'Can not connect to the verification server at this time. Please make sure outgoing connections are enabled on your server and try again. If it still does not work please wait a few minutes and try again.', 'total' );
			}
		}
		return $response;
	}

	/**
	 * Deactivate License
	 *
	 * @since 4.5
	 */
	public function deactivate_license( $license, $dev, $response ) {
		$args = array(
			'market'  => 'envato',
			'license' => $license,
			'url'     => $this->get_site_url(),
		);
		if ( $dev ) {
			$args['dev'] = '1';
		}
		$remote_url = add_query_arg( $args, 'https://wpexplorer-themes.com/deactivate-license/' );
		$remote_response = wp_remote_get( $remote_url );
		if ( is_wp_error( $remote_response ) ) {
			$response['message'] = $response->get_error_message();
		} else {
			$remote_response_code = wp_remote_retrieve_response_code( $remote_response );
			$response['response_code'] = $remote_response_code;
			if ( 200 == $remote_response_code ) {
				$result = json_decode( wp_remote_retrieve_body( $remote_response ) );
				if ( 'success' == $result->status ) {
					delete_option( 'active_theme_license' );
					delete_option( 'active_theme_license_dev' );
					$response['message'] = esc_html__( 'The license has been deactivated successfully.', 'total' );
					$response['messageClass'] = 'notice-warning';
					$response['success'] = true;
				} elseif ( $result->message ) {
					$response['message'] = $result->message;
				} else {
					$response['message'] = $result;
				}
				if ( isset( $result->clearLicense ) ) {
					delete_option( 'active_theme_license' );
					delete_option( 'active_theme_license_dev' );
					$response['success']      = true;
					$response['clearLicense'] = true;
					$response['message']      =  '';
				}
			} else {
				$response['message'] = esc_html( 'Can not connect to the verification server at this time, please try again in a few minutes.', 'total' );
			}
		}
		return $response;
	}

	/**
	 * License form ajax
	 *
	 * @since 4.5
	 */
	public function license_form_ajax() {

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpex_theme_license_form_nonce' ) ) {
			wp_die();
		}

		$response = array(
			'message'       => '',
			'messageClass'  => 'notice-error',
			'success'       => false,
			'response_code' => '',
		);
		$license = isset( $_POST['license'] ) ? wp_strip_all_tags( trim( $_POST['license'] ) ) : '';
		$process = isset( $_POST['process'] ) ? $_POST['process'] : '';

		if ( 'deactivate' == $process ) {
			$response = $this->deactivate_license( $license, get_option( 'active_theme_license_dev', false ), $response );
			wp_send_json( $response );
		}

		elseif ( 'activate' == $process ) {

			$dev = ( isset( $_POST['devlicense'] ) && 'checked' == $_POST['devlicense'] ) ? true : false;

			if ( empty( $license ) ) {
				$response['message']      = esc_html__( 'Please enter a license.', 'total' );
				$response['messageClass'] = 'notice-warning';
			} else {
				$response = $this->activate_license( $license, $dev, $response );
			}

			wp_send_json( $response );

		}

		wp_die();

	}

	/**
	 * Admin Notice
	 *
	 * @since 4.9.6
	 */
	public function admin_notice() {

		if ( isset( $_GET['total-dismiss'] )
			&& 'license-nag' === $_GET['total-dismiss']
			&& isset( $_GET[ 'total_dismiss_license_nag_nonce' ] )
			&& wp_verify_nonce( $_GET['total_dismiss_license_nag_nonce'], 'total_dismiss_license_nag' )
		) {
			update_option( 'total_dismiss_license_notice', true );
			return;
		}

		$screen = get_current_screen();

	    if ( in_array( $screen->id, array( 'dashboard', 'themes', 'plugins' ) ) ) { ?>

			<div class="notice notice-warning is-dismissible">
				<p><strong><?php esc_html_e( 'Activate Theme License', 'total' ); ?></strong>: <?php echo esc_html_e( 'Don\'t forget to activate your theme license to receive updates and support.', 'total' ); ?></p>
				<p><strong><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpex-panel-theme-license' ) ); ?>"><?php esc_html_e( 'Activate your license', 'total' ); ?></a> | <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'total-dismiss', 'license-nag' ), 'total_dismiss_license_nag', 'total_dismiss_license_nag_nonce'  ) ); ?>"><?php esc_html_e( 'Dismiss notice', 'total' ); ?></a></strong></p>
			</div>

		<?php }
	}

}
LicensePanel::instance();