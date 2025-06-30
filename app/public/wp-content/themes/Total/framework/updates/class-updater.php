<?php
/**
 * Provides updates for the Total theme.
 *
 * @package Total WordPress theme
 * @subpackage Framework
 * @version 5.0.6
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class Updater {

	/**
	 * Our single Updater instance.
	 *
	 * @var Updater
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
	 * Create or retrieve the instance of Updater.
	 *
	 * @return Updater
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Updater;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Total theme updater API url.
	 *
	 * @return string
	 */
	public $api_url = 'https://wpexplorer-updates.com/api/v1/';

	/**
	 * Active theme license.
	 *
	 * @return string
	 */
	public $theme_license = '';

	/**
	 * Run action hooks.
	 */
	public function init_hooks() {

		// This is for testing only !!!!
		//set_site_transient( 'update_themes', null );

		$this->theme_license = wpex_get_theme_license();

		if ( $this->theme_license ) {
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_update' ) );
		}

	}

	/**
	 * Makes a call to the API.
	 *
	 * @param $params array   The parameters for the API call
	 * @return        array   The API response
	 */
	public function call_api( $action, $params ) {

		$api = add_query_arg( $params, $this->api_url . $action );

		$request = wp_safe_remote_get( $api );

		if ( is_wp_error( $request ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );

		return json_decode( $body );

	}

	/**
	 * Checks the API response to see if there was an error.
	 *
	 * @param $response The API response to verify
	 * @return bool     True if there was an error. Otherwise false.
	 */
	public function is_api_error( $response ) {

		if ( $response === false || ! is_object( $response ) || isset( $response->error ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Calls the License Manager API to get the license information for the
	 * current product.
	 *
	 * @return object|bool   The product data, or false if API call fails.
	 */
	public function get_license_info() {

		return $this->call_api( 'info', array(
			'theme'   => 'Total',
			'license' => urlencode( trim( wp_strip_all_tags( $this->theme_license ) ) ),
		) );

	}

	/**
	 * Check for updates.
	 *
	 * @return object|bool	If there is an update, returns the license information.
	 *                      Otherwise returns false.
	 */
	public function update_request() {

		$license_info = $this->get_license_info();

		if ( $this->is_api_error( $license_info ) ) {
			return false;
		}

		return $license_info;

	}

	/**
	 * The filter that checks if there are updates to the theme.
	 *
	 * @param $transient    mixed   The transient used for WordPress updates
	 * @return mixed        The transient with our (possible) additions.
	 */
	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Query API for updates.
		$update = $this->update_request();

		if ( $this->is_api_error( $update ) ) {
			return $transient;
		}

		$theme = wp_get_theme( 'Total' );

		// Update is available.
		if ( isset( $update->version )
			&& isset( $update->package )
			&& version_compare( $theme->get( 'Version' ), $update->version, '<' )
		) {

			$transient->response[ 'Total' ] = array(
				'theme'        => 'Total',
				'new_version'  => $update->version,
				'package'      => $update->package,
				'url'          => WPEX_THEME_CHANGELOG_URL,
				'requires'     => '', // @todo update API to return this.
				'requires_php' => '', // @todo update API to return this.
			);

		}

		return $transient;
	}

}
Updater::instance();