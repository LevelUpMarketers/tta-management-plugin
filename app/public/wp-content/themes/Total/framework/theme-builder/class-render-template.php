<?php
/**
 * Theme Builder | Render Template
 *
 * @package Total WordPress theme
 * @subpackage Theme Builder
 * @version 5.0
 */

namespace TotalTheme\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

final class Render_Template {

	/**
	 * Template to render.
	 */
	private $template;

	/**
	 * Location where template is being displayed.
	 */
	private $location;

	/**
	 * Render the template.
	 */
	public function __construct( $template, $location ) {

		$this->template = $template;
		$this->location = $location;

	}

	/**
	 * Render the template
	 */
	public function render() {

		if ( empty( $this->template ) ) {
			return false;
		}

		$template_content = '';

		if ( 'singular' == $this->location && post_password_required() ) {
			ob_start();
				the_content();
			$template_content = ob_get_clean();
		}

		$post = get_post( $this->template );

		if ( $post && 'publish' == get_post_status( $post ) ) {

			$template_content = wpex_sanitize_template_content( $post->post_content );

		}

		if ( $template_content ) {
			echo $this->before_template() . $template_content . $this->after_template(); // XSS ok.
			return true;
		}

	}

	/**
	 * Before template content.
	 */
	private function before_template() {
		return $this->wpbakery_template_css();
	}

	/**
	 * After template content.
	 */
	private function after_template() {
		return '';
	}

	/**
	 * Return template CSS.
	 */
	private function wpbakery_template_css() {

		if ( 'templatera' == get_post_type( $this->template ) ) {

			$css = '';

			$meta = get_post_meta( $this->template, '_wpb_shortcodes_custom_css', true );

			if ( $meta ) {

				$css .= '<style data-type="vc_shortcodes-custom-css">';

					$css .= wp_strip_all_tags( $meta );

				$css .= '</style>';

			}

			return $css;

		}

	}

}