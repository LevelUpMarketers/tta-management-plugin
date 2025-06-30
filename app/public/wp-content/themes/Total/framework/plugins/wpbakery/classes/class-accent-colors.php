<?php
/**
 * WPBakery Accent Colors
 *
 * @package Total WordPress Theme
 * @subpackage WPBakery
 * @version 5.0
 */

namespace TotalTheme\WPBakery;

defined( 'ABSPATH' ) || exit;

class Accent_Colors {

	/**
	 * Our single Accent_Colors instance.
	 *
	 * @var Accent_Colors
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
	 * Create or retrieve the instance of Accent_Colors.
	 *
	 * @return Accent_Colors
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Accent_Colors;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_filter( 'wpex_accent_texts', array( $this, 'accent_texts' ) );
		add_filter( 'wpex_accent_borders', array( $this, 'accent_borders' ) );
		add_filter( 'wpex_accent_backgrounds', array( $this, 'accent_backgrounds' ) );
	}

	/**
	 * Adds text accents.
	 */
	public function accent_texts( $texts ) {
		return array_merge( array(
			'.vc_toggle_total .vc_toggle_title',
			'.vcex-module a:hover .wpex-heading',
			'.vcex-icon-box-link-wrap:hover .wpex-heading',
			//'.wpb-js-composer .vc_tta.vc_general.vc_tta-style-total .vc_tta-panel-title>a',
			//'.wpb-js-composer .vc_tta.vc_general.vc_tta-style-total .vc_tta-tab>a',
		), $texts );
	}

	/**
	 * Adds border accents.
	 */
	public function accent_borders( $borders ) {
		return array_merge( array(
			'.wpb_tabs.tab-style-alternative-two .wpb_tabs_nav li.ui-tabs-active a' => array( 'bottom' ),
		), $borders );
	}

	/**
	 * Adds background accents.
	 */
	public function accent_backgrounds( $backgrounds ) {
		return array_merge( array(
			'.vcex-testimonials-fullslider .sp-button:hover',
			'.vcex-testimonials-fullslider .sp-selected-button',
			'.vcex-testimonials-fullslider.light-skin .sp-button:hover',
			'.vcex-testimonials-fullslider.light-skin .sp-selected-button',
			'.vcex-testimonials-fullslider .sp-button.sp-selected-button',
			'.vcex-testimonials-fullslider .sp-button:hover',
		), $backgrounds );
	}

}
Accent_Colors::instance();