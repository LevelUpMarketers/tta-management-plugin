<?php
/**
 * Class used to insert links in the head for preloading assets.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.4
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class Preload_Assets {

	/**
	 * Our single Preload_Assets instance.
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
	 * Create or retrieve the instance of Preload_Assets.
	 *
	 * @return Preload_Assets
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Preload_Assets;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Initialization hooks.
	 *
	 * @since 5.0
	 */
	public function init_hooks() {
		add_action( 'wp_head', array( $this, 'add_links' ) );
	}

	/**
	 * Add links to wp_head
	 *
	 * @since 5.0
	 */
	public function add_links() {

		$output = '';

		$links = $this->get_links();

		if ( $links ) {

			foreach ( $links as $link => $atts ) {

				if ( isset( $atts['condition'] ) && false === $atts['condition'] ) {
					continue;
				}

				$output .= '<link rel="preload" href="' . esc_attr( esc_url( $atts['href'] ) ) . '"';

					if ( isset( $atts['type'] ) ) {
						$output .= ' type="' . esc_attr( $atts['type'] ) . '"';
					}

					if ( isset( $atts['as'] ) ) {
						$output .= ' as="' . esc_attr( $atts['as'] ) . '"';
					}

					if ( isset( $atts['media'] ) ) {
						$output .= ' media="' . esc_attr( $atts['media'] ) . '"';
					}

					if ( isset( $atts['crossorigin'] ) ) {
						$output .= ' crossorigin';
					}

				$output .= '>';

			}

		}

		$output_escaped = $output;

		echo $output_escaped; // @codingStandardsIgnoreLine

	}

	/**
	 * Return array of links.
	 *
	 * @since 5.0
	 */
	public function get_links() {

		$links = array();

		// Sticky Logo
		$sticky_logo = wpex_sticky_header_logo_img();

		if ( $sticky_logo ) {

			$links[] = array(
				'href' => $sticky_logo,
				'as'   => 'image'
			);

		}

		// Sticky Logo retina
		$sticky_logo_retina = wpex_sticky_header_logo_img_retina();

		if ( $sticky_logo_retina ) {

			$links[] = array(
				'href' => $sticky_logo_retina,
				'as'   => 'image'
			);

		}

		// Theme Icon woff2 file
		$links[] = array(
			'href'        => wpex_asset_url( 'lib/ticons/fonts/ticons-webfont.woff2' ),
			'type'        => 'font/woff2',
			'as'          => 'font',
			'crossorigin' => true,
			'condition'   => wp_style_is( 'ticons' ),
		);

		// Return links
		return (array) apply_filters( 'wpex_preload_links', $links );

	}

}
Preload_Assets::instance();