<?php
/**
 * LayerSlider Config
 *
 * @package Total WordPress Theme
 * @subpackage LayerSlider
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class LayerSlider {

	/**
	 * Start things up.
	 */
	public function __construct() {

		if ( wpex_is_request( 'admin' ) ) {
			add_action( 'admin_init', array( $this, 'remove_notices' ), PHP_INT_MAX );
		}

	}

	/**
	 * Remove notices.
	 */
	public function remove_notices() {

		if ( defined( 'LS_PLUGIN_BASE' ) && ! get_option( 'layerslider-authorized-site', null ) ) {
			remove_action( 'after_plugin_row_' . LS_PLUGIN_BASE, 'layerslider_plugins_purchase_notice', 10, 3 );
		}

	}

}
new LayerSlider;