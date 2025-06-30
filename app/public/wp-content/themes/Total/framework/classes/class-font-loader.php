<?php
/**
 * FontLoader Class
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

class Font_Loader {

	/**
	 * Font to load.
	 *
	 * @var string
	 */
	public $font = '';

	/**
	 * Font type.
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * Font args.
	 *
	 * @var array.
	 */
	public $font_args = '';

	/**
	 * Array of already loaded fonts.
	 *
	 * @var array.
	 */
	public $loaded_fonts = array();

	/**
	 * Font URL.
	 *
	 * @var string
	 */
	public $font_url = '';

	/**
	 * Main constructor.
	 */
	public function __construct( $font, $type = '', $args = array() ) {

		$this->font      = $font;
		$this->type      = $type;
		$this->font_args = $args;

		if ( $this->font && ! in_array( $this->font, $this->loaded_fonts ) ) {

			$this->load_font();

		}

	}

	/**
	 * Load the font.
	 */
	public function load_font() {

		// Get font arguments if unknown
		if ( empty( $this->font_args ) ) {

			$registered_fonts = wpex_get_registered_fonts();

			if ( ! empty( $registered_fonts ) && isset( $registered_fonts[ $this->font ] ) ) {
				$this->type = 'registered';
				$this->font_args = $registered_fonts[ $this->font ];
			}

		}

		// Sanitize type (fallback to google)
		$this->type = $this->type ? $this->type : 'google';

		if ( 'registered' == $this->type ) {
			$this->load_custom_font();
		} else {
			$this->load_theme_font();
		}

	}

	/**
	 * Load a theme font.
	 */
	public function load_theme_font() {

		$font = $this->font;

		if ( 'google' == $this->type ) {

			$gfonts = wpex_google_fonts_array();

			if ( empty( $gfonts ) || ! is_array( $gfonts ) ) {
				return;
			}

			if ( 'Sansita One' === $font ) {
				$font = 'Sansita'; // renamed font
			}

			if ( ! in_array( $font, $gfonts ) ) {
				return;
			}

			$this->enqueue_google_font( $font );

		}

	}

	/**
	 * Load a custom font.
	 */
	public function load_custom_font() {

		$font = $this->font;
		$args = $this->font_args;

		$type = ! empty( $args['type'] ) ? $args['type'] : '';

		if ( $type ) {

			$method = 'enqueue_' . $type . '_font';

			if ( method_exists( $this, $method ) ) {
				return $this->$method( $font, $args );
			}

		}

	}

	/**
	 * Enqueue google font.
	 */
	public function enqueue_google_font( $font, $args = array() ) {

		if ( ! wpex_has_google_services_support() ) {
			return;
		}

		// Define default Google font args
		$default_args = array(
			'weights' => array(
				'100',
				'200',
				'300',
				'400',
				'500',
				'600',
				'700',
				'800',
				'900',
			),
			'italic'  => true,
			'subset'  => get_theme_mod( 'google_font_subsets', array( 'latin' ) ),
			'display' => get_theme_mod( 'google_font_display', 'swap' ),
		);

		// Parse args and extract
		extract( wp_parse_args( $args, $default_args ) );

		// Check allowed font weights
		$weights = apply_filters( 'wpex_google_font_enqueue_weights', $weights, $font );
		$weights = is_array( $weights ) ? $weights : explode( ',', $weights );

		// Check if we should get italic fonts
		$italic = apply_filters( 'wpex_google_font_enqueue_italics', $italic, $font );

		//Check the subsets to load
		$subset = apply_filters( 'wpex_google_font_enqueue_subsets', $subset, $font );
		$subset = is_array( $subset ) ? $subset : explode( ',', $subset );

		// Check font display type
		$display = apply_filters( 'wpex_google_font_enqueue_display', $display, $font );

		// Define Google Font URL
		$url = wpex_get_google_fonts_url() . '/css2?family=' . str_replace( ' ', '%20', $this->sanitize_google_font_name( $font ) );

		// Define axes vars for generating Google Font URL
		$axis_tag_list = array();
		$axes          = array();

		// Add italics to axis tag list
		if ( $italic ) {
			$axis_tag_list[] = 'ital';
		}

		// Font with variables
		if ( ! empty( $weights ) ) {
			if ( ( count( $weights ) > 1 ) || ( 1 == count( $weights ) && '400' !== $weights[0] ) ) {

				$axis_tag_list[] = 'wght';
				$url .= ':' . implode( ',' , $axis_tag_list ) . '@';

				if ( $italic ) {

					foreach( $weights as $weight ) {
						$axes[] = '0,' . $weight;
					}

					foreach( $weights as $weight ) {
						$axes[] = '1,' . $weight;
					}

				} else {

					foreach( $weights as $weight ) {
						$axes[] = $weight;
					}

				}

				$url .= implode( ';' , $axes );
			}

			// Singular 400 font
			else {
				if ( $axis_tag_list ) {
					$url .= ':' . implode( ',' , $axis_tag_list ) . '@0;1';
				}
			}
		}

		// Add font display
		if ( $display ) {
			$url .= '&display=' . wp_strip_all_tags( $display );
		}

		// Add subsets
		if ( $subset ) {
			$url .= '&subset=' . wp_strip_all_tags( implode( ', ', $subset ) );
		}

		// Update $font_url var
		$this->font_url = esc_url( $url );

		// Enqueue the font
		wp_enqueue_style(
			'wpex-google-font-' . $this->get_font_handle( $font ),
			$this->font_url,
			array(),
			null // important
		);

	}

	/**
	 * Enqueue adobe font.
	 */
	public function enqueue_adobe_font( $font, $args = array() ) {

		if ( empty( $args['project_id'] ) ) {
			return;
		}

		// Update $font_url var
		$this->font_url = esc_url( 'https://use.typekit.net/' . $args['project_id'] . '.css' );

		wp_enqueue_style(
			'typekit-' . wp_strip_all_tags( $args['project_id'] ),
			$this->font_url,
			array(),
			null
		);

	}

	/**
	 * Return correct font handle for enqueue.
	 */
	public function get_font_handle( $font ) {
		return str_replace( ' ', '-', strtolower( trim( $font ) ) );
	}

	/**
	 * Sanitize Google Font Name
	 */
	public function sanitize_google_font_name( $font ) {
		return str_replace( ' ', '+', trim( $font ) );
	}

}