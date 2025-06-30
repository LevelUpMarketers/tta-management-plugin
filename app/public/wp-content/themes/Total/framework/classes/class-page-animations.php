<?php
/**
 * Page Animation
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

namespace TotalTheme;

defined( 'ABSPATH' ) || exit;

final class PageAnimations {

	/**
	 * Load in animation.
	 *
	 * @var animate_in
	 */
	public $animate_in;

	/**
	 * Leave page animation.
	 *
	 * @var animate_out
	 */
	public $animate_out;

	/**
	 * Our single PageAnimations instance.
	 *
	 * @var PageAnimations
	 */
	private static $instance;

	/**
	 * Disable instantiation.
	 */
	private function __construct() {
		// Private to disable instantiation.
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
	 * Create or retrieve the instance of PageAnimations.
	 *
	 * @return PageAnimations
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new PageAnimations;
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		add_filter( 'wpex_customizer_sections', array( $this, 'customizer_settings' ) );

		// Get animations
		$this->animate_in  = apply_filters( 'wpex_page_animation_in', get_theme_mod( 'page_animation_in' ) );
		$this->animate_out = apply_filters( 'wpex_page_animation_out', get_theme_mod( 'page_animation_out' ) );

		// If page animations is enabled lets do things
		if ( $this->animate_in && $this->animate_out && wpex_is_request( 'frontend' ) ) {
			add_action( 'template_redirect', array( $this, 'frontend_hooks' ) );
		}

	}

	/**
	 * Front end hooks.
	 */
	public function frontend_hooks() {

		if ( is_customize_preview() || wpex_vc_is_inline() || wpex_elementor_is_preview_mode() ) {
			return; // not allowed in builders or customizer - !important!!
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5 ); // load before theme scripts
		add_action( 'wpex_outer_wrap_before', array( $this, 'open_wrapper' ) );
		add_action( 'wpex_outer_wrap_after', array( $this, 'close_wrapper' ) );
		add_filter( 'wpex_head_css', array( $this, 'styling' ) );

	}

	/**
	 * Retrieves cached CSS or generates the responsive CSS.
	 */
	public function enqueue_scripts() {

		$localize = $this->localize();

		if ( ! $localize ) {
			return;
		}

		wp_enqueue_style(
			'animsition',
			wpex_asset_url( 'lib/animsition/animsition.css' )
		);

		wp_enqueue_script(
			'animsition',
			wpex_asset_url( 'lib/animsition/animsition.js' ),
			array( 'jquery' ),
			'4.0.2',
			false
		);

		wp_enqueue_script(
			'wpex-animsition-init',
			wpex_asset_url( 'js/dynamic/animsition-init.js' ),
			array( 'jquery', 'animsition' ),
			'1.0.0',
			false
		);

		wp_localize_script( 'wpex-animsition-init', 'wpexAnimsition', $localize );

	}

	/**
	 * Localize script.
	 */
	public function localize() {

		// Set defaults
		$array = array(
			'loading'      => true,
			'loadingClass' => 'animsition-loading',
			'loadingInner' => false, // For custom image
			'inDuration'   => '600',
			'outDuration'  => '400',
		);

		// Animate In
		if ( $this->animate_in && array_key_exists( $this->animate_in, $this->in_transitions() ) ) {
			$array['inClass'] = $this->animate_in;
		}

		// Animate out
		if ( $this->animate_out && array_key_exists( $this->animate_out, $this->out_transitions() ) ) {
			$array['outClass'] = $this->animate_out;
		}

		// Custom Speed
		if ( $speed = intval( get_theme_mod( 'page_animation_speed' ) ) ) {
			$array['inDuration']  = $speed;
			$array['outDuration'] = $speed;
		}

		// New out speed setting
		if ( $speed = intval( get_theme_mod( 'page_animation_speed_out' ) ) ) {
			$array['outDuration'] = $speed;
		}

		// Loading inner
		if ( $text = esc_html( get_theme_mod( 'page_animation_loading' ) ) ) {
			$array['loadingClass'] = 'wpex-animsition-loading';
			$array['loadingInner'] = $text;
		}

		// Link Elements / The links that trigger the animation
		$array['linkElement'] = 'a[href]:not([target="_blank"]):not([href^="#"]):not([href*="javascript"]):not([href*=".jpg"]):not([href*=".jpeg"]):not([href*=".gif"]):not([href*=".png"]):not([href*=".mov"]):not([href*=".swf"]):not([href*=".mp4"]):not([href*=".flv"]):not([href*=".avi"]):not([href*=".mp3"]):not([href^="mailto:"]):not([href*="?"]):not([href*="#localscroll"]):not(".wpex-lightbox,.local-scroll-link,.exclude-from-page-animation,.wcmenucart,.local-scroll,.about_paypal,.wpex-lightbox-gallery,.wpb_single_image.wpex-lightbox a.vc_single_image-wrapper")';

		// Return localize array
		return apply_filters( 'wpex_animsition_settings', $array );

	}

	/**
	 * Open wrapper.
	 *
	 */
	public function open_wrapper() {
		echo '<div class="wpex-page-animation-wrap animsition wpex-clr">';
	}

	/**
	 * Close Wrapper.
	 *
	 */
	public function close_wrapper() {
		echo '</div>';
	}

	/**
	 * In Transitions.
	 *
	 */
	public function in_transitions() {
		return array(
			''              => esc_html__( 'None', 'total' ),
			'fade-in'       => esc_html__( 'Fade In', 'total' ),
			'fade-in-up'    => esc_html__( 'Fade In Up', 'total' ),
			'fade-in-down'  => esc_html__( 'Fade In Down', 'total' ),
			'fade-in-left'  => esc_html__( 'Fade In Left', 'total' ),
			'fade-in-right' => esc_html__( 'Fade In Right', 'total' ),
			'rotate-in'     => esc_html__( 'Rotate In', 'total' ),
			'flip-in-x'     => esc_html__( 'Flip In X', 'total' ),
			'flip-in-y'     => esc_html__( 'Flip In Y', 'total' ),
			'zoom-in'       => esc_html__( 'Zoom In', 'total' ),
		);
	}

	/**
	 * Out Transitions.
	 */
	public function out_transitions() {
		return array(
			''               => esc_html__( 'None', 'total' ),
			'fade-out'       => esc_html__( 'Fade Out', 'total' ),
			'fade-out-up'    => esc_html__( 'Fade Out Up', 'total' ),
			'fade-out-down'  => esc_html__( 'Fade Out Down', 'total' ),
			'fade-out-left'  => esc_html__( 'Fade Out Left', 'total' ),
			'fade-out-right' => esc_html__( 'Fade Out Right', 'total' ),
			'rotate-out'     => esc_html__( 'Rotate Out', 'total' ),
			'flip-out-x'     => esc_html__( 'Flip Out X', 'total' ),
			'flip-out-y'     => esc_html__( 'Flip Out Y', 'total' ),
			'zoom-out'       => esc_html__( 'Zoom Out', 'total' ),
		);
	}

	/**
	 * Adds customizer settings for the animations.
	 */
	public function customizer_settings( $sections ) {

		$sections['wpex_page_animations'] = array(
			'title' => esc_html__( 'Page Animations', 'total' ),
			'panel' => 'wpex_general',
			'desc'  => esc_html__( 'You must save your options and refresh your live site to preview changes to this setting.', 'total' ),
			'settings' => array(
				array(
					'id' => 'page_animation_in',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'In Animation', 'total' ),
						'type' => 'select',
						'choices' => $this->in_transitions(),
					),
				),
				array(
					'id' => 'page_animation_out',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Out Animation', 'total' ),
						'type' => 'select',
						'choices' => $this->out_transitions(),
					),
				),
				array(
					'id' => 'page_animation_loading',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Loading Text', 'total' ),
						'type' => 'text',
						'desc' =>  esc_html__( 'Replaces the loading icon.', 'total' ),
					),
				),
				array(
					'id' => 'page_animation_speed',
					'transport' => 'postMessage',
					'default' => 600,
					'control' => array(
						'label' => esc_html__( 'In Speed', 'total' ),
						'type' => 'number',
					),
				),
				array(
					'id' => 'page_animation_speed_out',
					'transport' => 'postMessage',
					'default' => 400,
					'control' => array(
						'label' => esc_html__( 'Out Speed', 'total' ),
						'type' => 'number',
					),
				),
				array(
					'id' => 'page_animation_color',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Color', 'total' ),
						'type' => 'color',
					),
				),
				array(
					'id' => 'page_animation_loader_inner_color',
					'transport' => 'postMessage',
					'control' => array(
						'label' => esc_html__( 'Loader Inner Color', 'total' ),
						'type' => 'color',
					),
				),
			)
		);

		return $sections;

	}

	/**
	 * Custom styling.
	 */
	public function styling( $css ) {
		$custom_loader = esc_html( get_theme_mod( 'page_animation_loading' ) );
		if ( $color = wp_strip_all_tags( get_theme_mod( 'page_animation_color' ) ) ) {
			if ( $custom_loader ) {
				$css .= '.wpex-animsition-loading{color:'. $color .';}';
			} else {
				$css .= '.animsition-loading{border-top-color:'. $color .';border-right-color:'. $color .';border-bottom-color:'. $color .';}';
			}
		}
		if ( ! $custom_loader && $color = wp_strip_all_tags( get_theme_mod( 'page_animation_loader_inner_color' ) ) ) {
			$css .= '.animsition-loading{border-left-color:'. $color .';}';
		}
		return $css;
	}

}
PageAnimations::instance();