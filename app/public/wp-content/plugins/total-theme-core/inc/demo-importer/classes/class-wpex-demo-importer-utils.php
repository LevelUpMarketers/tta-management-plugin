<?php
/**
 * Contains utility methods for the plugin
 *
 * @version 4.5
 */
class WPEX_Demo_Importer_Utils {

	/**
	 * Returns true if on demo importer
	 *
	 * @version 4.5
	 */
	public static function is_admin_page( $hook ) {
		if ( defined( 'WPEX_ADMIN_PANEL_HOOK_PREFIX' ) && WPEX_ADMIN_PANEL_HOOK_PREFIX . '-demo-importer' === $hook ) {
			return true;
		}
		return false;
	}

	/**
	 * Gets and returns url body using wp_remote_get
	 *
	 * @since 1.1.0
	 */
	public static function remote_get( $url ) {

		// Get data
		$response = wp_remote_get( $url );

		// Check response type
		if ( is_wp_error( $response ) || ( wp_remote_retrieve_response_code( $response ) != 200 ) ) {
			return false;
		}

		// Get remote body val
		$body = wp_remote_retrieve_body( $response );

		// Return data
		if ( ! empty( $body ) ) {
			return $body;
		} else {
			return false;
		}

	}

}