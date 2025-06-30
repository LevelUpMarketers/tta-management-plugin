<?php
/**
 * Used for custom site backgrounds
 *
 * @package Total WordPress Theme
 * @subpackage Classes
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class SiteBackgrounds {

	/**
	 * Our single SiteBackgrounds instance.
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
	 * Create or retrieve the instance of SiteBackgrounds.
	 *
	 * @return SiteBackgrounds
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new SiteBackgrounds;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_filter( 'wpex_head_css', array( $this, 'site_background_css' ), 999 );
	}

	/**
	 * Generates the CSS output.
	 */
	public function site_background_css( $output ) {

		// Vars
		$css = $add_css = '';

		// Global vars
		$css     = '';
		$image   = get_theme_mod( 't_background_image' ); // converted to background_img in 4.3 to prevent conflict with WP
		$style   = get_theme_mod( 't_background_style' );
		$pattern = get_theme_mod( 't_background_pattern' );
		$post_id = wpex_get_current_post_id();

		// Single post vars
		if ( $post_id ) {

			// Color
			$single_color = get_post_meta( $post_id, 'wpex_page_background_color', true );

			// Image
			$single_image = get_post_meta( $post_id, 'wpex_page_background_image_redux', true );
			if ( $single_image ) {
				if ( is_array( $single_image ) ) {
					$single_image = ( ! empty( $single_image['url'] ) ) ? $single_image['url'] : '';
				} else {
					$single_image = $single_image;
				}
			} else {
				$single_image = get_post_meta( $post_id, 'wpex_page_background_image', true );
			}

			// Background style
			$single_style = get_post_meta( $post_id, 'wpex_page_background_image_style', true );

		}

		/*-----------------------------------------------------------------------------------*/
		/*  - Sanitize Data
		/*-----------------------------------------------------------------------------------*/

		$color = ! empty( $single_color ) ? $single_color : '';
		$style = ( ! empty( $single_image ) && ! empty( $single_style ) ) ? $single_style : $style;
		$image = ! empty( $single_image ) ? $single_image : $image;

		$settings = apply_filters( 'wpex_body_background_settings', array(
			'color'   => $color,
			'image'   => $image,
			'style'   => $style,
			'pattern' => $pattern,
		) );

		if ( ! $settings ) {
			return;
		}

		extract( $settings );

		if ( $image && is_numeric( $image ) ) {
			$image = wp_get_attachment_image_src( $image, 'full' );
			$image = isset( $image[0] ) ? $image[0] : '';
		}

		$style = $style ? $style : 'stretched';

		/*-----------------------------------------------------------------------------------*/
		/*  - Generate CSS
		/*-----------------------------------------------------------------------------------*/

		// Color
		if ( $color && '#' !== $color ) {

			$color_escaped = wp_strip_all_tags( $color );

			$css .= 'background-color:' . $color_escaped . '!important;';

			if ( wpex_has_footer_reveal() ) {
				$output .= '.footer-has-reveal #main{ background-color:' . $color_escaped . '!important;}';
			}

		}

		// Image
		if ( $image && ! $pattern ) {
			$css .= 'background-image:url(' . esc_url( $image ) . ') !important;';
			$css .= wpex_sanitize_data( $style, 'background_style_css' );
		}

		// Pattern
		if ( $pattern ) {
			$patterns = wpex_get_background_patterns();
			if ( isset( $patterns[$pattern] ) ) {
				$css .= 'background-image:url(' . esc_url( $patterns[$pattern]['url'] ) . '); background-repeat:repeat;';
			}
		}

		/*-----------------------------------------------------------------------------------*/
		/*  - Return $css
		/*-----------------------------------------------------------------------------------*/
		if ( ! empty( $css ) ) {
			$css = '/*SITE BACKGROUND*/body{' . $css . '}';
			$output .= $css;
		}

		// Return output css
		return $output;

	}

}
SiteBackgrounds::instance();