<?php
/**
 * WPBakery Toggle Configuration
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 *
 * @todo rename to WPBakery_Toggle_Config and add namespace
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VCEX_VC_Toggle_Config' ) ) {

	class VCEX_VC_Toggle_Config {

		/**
		 * Main constructor
		 *
		 * @since 4.1
		 */
		public function __construct() {

			if ( function_exists( 'vc_map_update' ) ) {
				$this->map_update();
			}

		}

		/**
		 * Update main settings
		 *
		 * @since 4.1
		 */
		public function map_update() {
			vc_map_update( 'vc_toggle', array(
				'name' => esc_html__( 'FAQ/Toggle', 'total' ),
			) );
		}

	}

}
new VCEX_VC_Toggle_Config();