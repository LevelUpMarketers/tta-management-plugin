<?php
/**
 * Adds all Typography options to the Customizer and outputs the custom CSS for them
 *
 * @package Total WordPress Theme
 * @subpackage Customizer
 * @version 5.0.2
 *
 * @todo convert to singleton.
 */

namespace TotalTheme;

use WP_Customize_Control;
use WP_Customize_Color_Control;

use WPEX_Fonts_Dropdown_Custom_Control;
use WPEX_Customize_Multicheck_Control;
use WPEX_Responsive_Field_Custom_Control;

defined( 'ABSPATH' ) || exit;

class Typography {

	/**
	 * Main constructor.
	 */
	public function __construct() {

		$this->init_hooks();

	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Register customizer settings
		if ( wpex_has_customizer_panel( 'typography' ) ) {
			add_action( 'customize_register', array( $this, 'register' ), 40 );
		}

		// Front-end actions
		if ( wpex_is_request( 'frontend' ) ) {

			if ( get_theme_mod( 'google_fonts_in_footer' ) ) {
				add_action( 'wp_footer', array( $this, 'load_fonts' ) );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'load_fonts' ) );
			}

		}

		// CSS output for typography settings
		if ( is_customize_preview() && wpex_has_customizer_panel( 'typography' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_registered_fonts' ) );
			add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
			add_action( 'wp_head', array( $this, 'live_preview_styles' ), 999 );
		} else {
			add_filter( 'wpex_head_css', array( $this, 'head_css' ), 99 );
		}

	}

	/**
	 * Array of Typography settings to add to the customizer.
	 */
	public function get_settings() {
		return apply_filters( 'wpex_typography_settings', array(
			'body' => array(
				'label' => esc_html__( 'Body', 'total' ),
				'target' => 'body',
			),
			'logo' => array(
				'label' => esc_html__( 'Logo', 'total' ),
				'target' => '#site-logo a.site-logo-text',
				'exclude' => array( 'color' ),
				'active_callback' => 'wpex_cac_hasnt_custom_logo',
				'condition' => 'wpex_header_has_text_logo',
			),
			'button' => array(
				'label' => esc_html__( 'Buttons', 'total' ),
				'target' => '.theme-button,input[type="submit"],button,#site-navigation .menu-button>a>span.link-inner,.woocommerce .button',
				'exclude' => array( 'color', 'margin', 'font-size' ),
			),
			'top_menu' => array(
				'label' => esc_html__( 'Top Bar', 'total' ),
				'target' => '#top-bar-content',
				'exclude' => array( 'color' ),
				'active_callback' => 'wpex_cac_has_topbar',
				'condition' => 'wpex_has_topbar',
			),
			'header_aside' => array(
				'label' => esc_html__( 'Header Aside', 'total' ),
				'target' => '.header-aside-content', // updated in 5.0 used to be "#header-aside"
			),
			'menu' => array(
				'label' => esc_html__( 'Main Menu', 'total' ),
				'target' => '#site-navigation .dropdown-menu .link-inner', // @todo Should we add : #current-shop-items-dropdown, #searchform-dropdown input[type="search"] ??
				'exclude' => array( 'color', 'line-height' ), // Can't include color causes issues with menu styling settings
				'active_callback' => 'wpex_cac_supports_menu_typo',
				'condition' => 'wpex_hasnt_dev_style_header',
			),
			'menu_dropdown' => array(
				'label' => esc_html__( 'Main Menu: Dropdowns', 'total' ),
				'target' => '#site-navigation .dropdown-menu ul .link-inner',
				'exclude' => array( 'color' ),
				'active_callback' => 'wpex_cac_supports_menu_typo',
				'condition' => 'wpex_hasnt_dev_style_header',
			),
			'mobile_menu' => array(
				'label' => esc_html__( 'Mobile Menu', 'total' ),
				'target' => '.wpex-mobile-menu, #sidr-main',
				'exclude' => array( 'color' ),
			),
			'page_title' => array(
				'label' => esc_html__( 'Page Header Title', 'total' ),
				'target' => '.page-header .page-header-title',
				'exclude' => array( 'color' ),
				'active_callback' => 'wpex_cac_has_page_header',
				'condition' => 'wpex_has_page_header',
			),
			'page_subheading' => array(
				'label' => esc_html__( 'Page Title Subheading', 'total' ),
				'target' => '.page-header .page-subheading',
				'active_callback' => 'wpex_cac_has_page_header',
				'condition' => 'wpex_has_page_header',
			),
			'blog_entry_title' => array(
				'label' => esc_html__( 'Blog Entry Title', 'total' ),
				'target' => '.blog-entry-title.entry-title, .blog-entry-title.entry-title a, .blog-entry-title.entry-title a:hover',
			),
			'blog_entry_meta' => array(
				'label' => esc_html__( 'Blog Entry Meta', 'total' ),
				'target' => '.blog-entry .meta',
			),
			'blog_entry_content' => array(
				'label' => esc_html__( 'Blog Entry Excerpt', 'total' ),
				'target' => '.blog-entry-excerpt',
			),
			'blog_post_title' => array(
				'label' => esc_html__( 'Blog Post Title', 'total' ),
				'target' => 'body.single-post .single-post-title',
			),
			'blog_post_meta' => array(
				'label' => esc_html__( 'Blog Post Meta', 'total' ),
				'target' => '.single-post .meta',
			),
			'breadcrumbs' => array(
				'label' => esc_html__( 'Breadcrumbs', 'total' ),
				'target' => '.site-breadcrumbs',
				'exclude' => array( 'color', 'line-height' ),
				'active_callback' => 'wpex_cac_has_breadcrumbs',
				'condition' => 'wpex_has_breadcrumbs',
			),
			'headings' => array(
				'label' => esc_html__( 'Headings', 'total' ),
				'target' => 'h1,h2,h3,h4,h5,h6,.theme-heading,.page-header-title,.wpex-heading,.vcex-heading,.entry-title',
				'exclude' => array( 'font-size' ),
			),
			'theme_heading' => array(
				'label' => esc_html__( 'Theme Heading', 'total' ),
				'target' => '.theme-heading',
				'margin' => true,
			),
			'sidebar' => array(
				'label' => esc_html__( 'Sidebar', 'total' ),
				'target' => '#sidebar',
				'exclude' => array( 'color' ),
				'condition' => 'wpex_has_sidebar',
			),
			'sidebar_widget_title' => array(
				'label' => esc_html__( 'Sidebar Widget Heading', 'total' ),
				'target' => '.sidebar-box .widget-title',
				'margin' => true,
				'exclude' => array( 'color' ),
				'condition' => 'wpex_has_sidebar',
			),
			'entry_h1' => array(
				'label' => esc_html__( 'H1', 'total' ),
				'target' => 'h1,.wpex-h1',
				'margin' => true,
				'description' => esc_html__( 'Will target headings in your post content.', 'total' ),
			),
			'entry_h2' => array(
				'label' => 'H2',
				'target' => 'h2,.wpex-h2',
				'margin' => true,
				'description' => esc_html__( 'Will target headings in your post content.', 'total' ),
			),
			'entry_h3' => array(
				'label' => 'H3',
				'target' => 'h3,.wpex-h3',
				'margin' => true,
				'description' => esc_html__( 'Will target headings in your post content.', 'total' ),
			),
			'entry_h4' => array(
				'label' => 'H4',
				'target' => 'h4,.wpex-h4',
				'margin' => true,
				'description' => esc_html__( 'Will target headings in your post content.', 'total' ),
			),
			'post_content' => array(
				'label' => esc_html__( 'Post Content', 'total' ),
				'target' => '.single-blog-content, .vcex-post-content-c, .wpb_text_column, body.no-composer .single-content, .woocommerce-Tabs-panel--description',
			),
			'footer_widgets' => array(
				'label' => esc_html__( 'Footer Widgets', 'total' ),
				'target' => '#footer-widgets',
				'exclude' => array( 'color' ),
				'active_callback' => 'wpex_cac_has_footer_widgets',
				'condition' => 'wpex_footer_has_widgets',
			),
			'footer_widget_title' => array(
				'label' => esc_html__( 'Footer Widget Heading', 'total' ),
				'target' => '.footer-widget .widget-title',
				'exclude' => array( 'color' ),
				'margin' => true,
				'active_callback' => 'wpex_cac_has_footer_widgets',
				'condition' => 'wpex_footer_has_widgets',
			),
			'callout' => array(
				'label' => esc_html__( 'Footer Callout', 'total' ),
				'target' => '.footer-callout-content',
				'exclude' => array( 'color' ),
				'condition' => 'wpex_has_callout',
			),
			'copyright' => array(
				'label' => esc_html__( 'Footer Bottom Text', 'total' ),
				'target' => '#copyright',
				'exclude' => array( 'color' ),
				'condition' => 'wpex_has_footer_bottom',
			),
			'footer_menu' => array(
				'label' => esc_html__( 'Footer Bottom Menu', 'total' ),
				'target' => '#footer-bottom-menu',
				'exclude' => array( 'color' ),
				'condition' => 'wpex_has_footer_bottom',
			),
		) );
	}

	/**
	 * Loads js file for customizer preview.
	 */
	public function customize_preview_init() {

		wp_enqueue_script( 'wpex-typography-customize-preview',
			wpex_asset_url( 'js/dynamic/customizer/wpex-typography.min.js' ),
			array( 'customize-preview' ),
			WPEX_THEME_VERSION,
			true
		);

		wp_localize_script( 'wpex-typography-customize-preview', 'wpexTypo', array(
			'stdFonts'          => wpex_standard_fonts(),
			'customFonts'       => wpex_add_custom_fonts(),
			'googleFontsUrl'    => wpex_get_google_fonts_url(),
			'googleFontsSuffix' => '100i,200i,300i,400i,500i,600i,700i,800i,100,200,300,400,500,600,700,800',
			'sytemUIFontStack'  => wpex_get_system_ui_font_stack(),
			'settings'          => $this->get_settings(),
			'attributes'        => array(
				'font-family',
				'font-weight',
				'font-style',
				'font-size',
				'color',
				'line-height',
				'letter-spacing',
				'text-transform',
				'margin',
			),
		) );

	}

	/**
	 * Register typography options to the Customizer.
	 */
	public function register( $wp_customize ) {

		if ( ! class_exists( 'WPEX_Customizer' ) ) {
			return;
		}

		// Get Settings
		$settings = $this->get_settings();

		// Return if settings are empty. This check is needed due to the filter added above
		if ( empty( $settings ) ) {
			return;
		}

		// Add General Panel
		$wp_customize->add_panel( 'wpex_typography', array(
			'priority'   => 142,
			'capability' => 'edit_theme_options',
			'title'      => esc_html__( 'Typography', 'total' ),
		) );

		// Add General Tab with font smoothing
		$wp_customize->add_section( 'wpex_typography_general' , array(
			'title'    => esc_html__( 'General', 'total' ),
			'priority' => 1,
			'panel'    => 'wpex_typography',
		) );

		// Font Smoothing
		$wp_customize->add_setting( 'enable_font_smoothing', array(
			'type'              => 'theme_mod',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'wpex_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'enable_font_smoothing', array(
			'label'       => esc_html__( 'Font Smoothing', 'total' ),
			'section'     => 'wpex_typography_general',
			'settings'    => 'enable_font_smoothing',
			'type'        => 'checkbox',
			'description' => esc_html__( 'Enable font-smoothing site wide. This makes fonts look a little "skinner". ', 'total' ),
		) ) );

		// Google Font settings
		if ( wpex_has_google_services_support() ) {

			// Load custom font 1
			$wp_customize->add_setting( 'load_custom_google_font_1', array(
				'type'              => 'theme_mod',
				'sanitize_callback' => 'esc_html',
			) );
			$wp_customize->add_control( new WPEX_Fonts_Dropdown_Custom_Control( $wp_customize, 'load_custom_google_font_1', array(
					'label'       => esc_html__( 'Load Custom Font', 'total' ),
					'section'     => 'wpex_typography_general',
					'settings'    => 'load_custom_google_font_1',
					'type'        => 'wpex-font-family',
					'description' => esc_html__( 'Allows you to load a custom font site wide for use with custom CSS. ', 'total' ),
				)
			) );

			// Google font display option.
			$wp_customize->add_setting( 'google_font_display', array(
				'type'              => 'theme_mod',
				'transport'         => 'postMessage',
				'default'           => 'swap',
				'sanitize_callback' => 'wpex_sanitize_customizer_select',
			) );

			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'google_font_display', array(
				'label'    => esc_html__( 'Google Font Display Type', 'total' ),
				'section'  => 'wpex_typography_general',
				'settings' => 'google_font_display',
				'type'     => 'select',
				'choices'  => array(
					''     => esc_html__( 'None', 'total' ),
					'auto'     => 'auto',
					'block'    => 'block',
					'swap'     => 'swap',
					'fallback' => 'fallback',
					'optional' => 'optional',
				),
				'description' => '<a href="https://developers.google.com/web/updates/2016/02/font-display#swap" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn More', 'total' ) . '</a>'
			) ) );

			// Load fonts in footer
			$wp_customize->add_setting( 'google_fonts_in_footer', array(
				'type'              => 'theme_mod',
				'transport'         => 'postMessage',
				'sanitize_callback' => 'wpex_sanitize_checkbox',
			) );

			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'google_fonts_in_footer', array(
				'label'    => esc_html__( 'Load Fonts After The Body Tag', 'total' ),
				'section'  => 'wpex_typography_general',
				'settings' => 'google_fonts_in_footer',
				'type'     => 'checkbox',
			) ) );

			// Select subsets
			$wp_customize->add_setting( 'google_font_subsets', array(
				'type'              => 'theme_mod',
				'default'           => 'latin',
				'sanitize_callback' => 'esc_html',
			) );
			$wp_customize->add_control( new WPEX_Customize_Multicheck_Control( $wp_customize, 'google_font_subsets', array(
					'label'    => esc_html__( 'Font Subsets', 'total' ),
					'section'  => 'wpex_typography_general',
					'settings' => 'google_font_subsets',
					'choices'  => array(
						'latin'        => 'latin',
						'latin-ext'    => 'latin-ext',
						'cyrillic'     => 'cyrillic',
						'cyrillic-ext' => 'cyrillic-ext',
						'greek'        => 'greek',
						'greek-ext'    => 'greek-ext',
						'vietnamese'   => 'vietnamese',
					),
				)
			) );
		}

		// Loop through settings
		foreach( $settings as $element => $array ) {

			$label = ! empty( $array['label'] ) ? $array['label'] : null;

			if ( ! $label ) {
				continue; // label is required
			}

			$exclude_attributes = ! empty( $array['exclude'] ) ? $array['exclude'] : false;
			$active_callback    = ! empty( $array['active_callback'] ) ? $array['active_callback'] : null;
			$description        = ! empty( $array['description'] ) ? $array['description'] : '';
			$transport          = ! empty( $array['transport'] ) ? $array['transport'] : 'postMessage';

			/* Create description based on targets
			if ( empty( $description ) ) {
				$description = '<strong>Target(s)</strong>: ' . wp_strip_all_tags( $array['target'] );
			}*/

			// Get attributes
			if ( ! empty ( $array['attributes'] ) ) {
				$attributes = $array['attributes'];
			} else {
				$attributes = array(
					'font-family',
					'font-weight',
					'font-style',
					'text-transform',
					'font-size',
					'line-height',
					'letter-spacing',
					'color',
				);
			}

			// Allow for margin on this attribute
			if ( isset( $array['margin'] ) ) {
				$attributes[] = 'margin';
			}

			// Set keys equal to vals
			$attributes = array_combine( $attributes, $attributes );

			// Exclude attributes for specific options
			if ( $exclude_attributes ) {
				foreach ( $exclude_attributes as $key => $val ) {
					unset( $attributes[ $val ] );
				}
			}

			// Define Section
			$wp_customize->add_section( 'wpex_typography_' . $element , array(
				'title'       => $label,
				'panel'       => 'wpex_typography',
				'description' => $description,
			) );

			// Font Family
			if ( in_array( 'font-family', $attributes ) ) {

				// Get default
				$default = ! empty( $array['defaults']['font-family'] ) ? $array['defaults']['font-family'] : NULL;

				// Add setting
				$wp_customize->add_setting( $element . '_typography[font-family]', array(
					'type'              => 'theme_mod',
					'default'           => $default,
					'transport'         => $transport,
					'sanitize_callback' => 'esc_html',
				) );

				// Add Control
				$wp_customize->add_control( new WPEX_Fonts_Dropdown_Custom_Control( $wp_customize, $element . '_typography[font-family]', array(
						'type'            => 'wpex-font-family',
						'label'           => esc_html__( 'Font Family', 'total' ),
						'section'         => 'wpex_typography_' . $element,
						'settings'        => $element . '_typography[font-family]',
						'active_callback' => $active_callback,
				) ) );

			}

			// Font Weight
			if ( in_array( 'font-weight', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[font-weight]', array(
					'type'              => 'theme_mod',
					'sanitize_callback' => 'wpex_sanitize_customizer_select',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( $element . '_typography[font-weight]', array(
					'label'           => esc_html__( 'Font Weight', 'total' ),
					'section'         => 'wpex_typography_' . $element,
					'settings'        => $element . '_typography[font-weight]',
					'type'            => 'select',
					'active_callback' => $active_callback,
					'choices'         => $this->choices_font_weights(),
					'description'     => esc_html__( 'Note: Not all Fonts support every font weight style. ', 'total' ),
				) );

			}

			// Font Style
			if ( in_array( 'font-style', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[font-style]', array(
					'type'              => 'theme_mod',
					'sanitize_callback' => 'wpex_sanitize_customizer_select',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( $element . '_typography[font-style]', array(
					'label'           => esc_html__( 'Font Style', 'total' ),
					'section'         => 'wpex_typography_' . $element,
					'settings'        => $element . '_typography[font-style]',
					'type'            => 'select',
					'active_callback' => $active_callback,
					'choices'         => $this->choices_font_style(),
				) );

			}

			// Text-Transform
			if ( in_array( 'text-transform', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[text-transform]', array(
					'type'              => 'theme_mod',
					'sanitize_callback' => 'wpex_sanitize_customizer_select',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( $element . '_typography[text-transform]', array(
					'label'           => esc_html__( 'Text Transform', 'total' ),
					'section'         => 'wpex_typography_' . $element,
					'settings'        => $element . '_typography[text-transform]',
					'type'            => 'select',
					'active_callback' => $active_callback,
					'choices'         => $this->choices_text_transform(),
				) );

			}

			// Font Size
			if ( in_array( 'font-size', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[font-size]', array(
					'type'              => 'theme_mod',
					'sanitize_callback' => 'wpex_sanitize_font_size_mod',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( new WPEX_Responsive_Field_Custom_Control( $wp_customize, $element . '_typography[font-size]', array(
					'label'           => esc_html__( 'Font Size', 'total' ),
					'section'         => 'wpex_typography_' . $element,
					'settings'        => $element . '_typography[font-size]',
					'type'            => 'wpex-responsive-field',
					'description'     => esc_html__( 'Please specify a unit (px, em, vm, vmax, vmin). If no unit is specified px will be used by default.', 'total' ),
					'active_callback' => $active_callback,
				) ) );

			}

			// Font Color
			if ( in_array( 'color', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[color]', array(
					'type'              => 'theme_mod',
					'default'           => '',
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $element . '_typography_color', array(
					'label'           => esc_html__( 'Font Color', 'total' ),
					'section'         => 'wpex_typography_' . $element,
					'settings'        => $element . '_typography[color]',
					'active_callback' => $active_callback,
				) ) );

			}

			// Line Height
			if ( in_array( 'line-height', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[line-height]', array(
					'type'              => 'theme_mod',
					'sanitize_callback' => 'esc_html',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( $element . '_typography[line-height]',
					array(
						'label'           => esc_html__( 'Line Height', 'total' ),
						'section'         => 'wpex_typography_' . $element,
						'settings'        => $element . '_typography[line-height]',
						'type'            => 'text',
						'active_callback' => $active_callback,
				) );

			}

			// Letter Spacing
			if ( in_array( 'letter-spacing', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[letter-spacing]', array(
					'type'              => 'theme_mod',
					'sanitize_callback' => 'wpex_sanitize_letter_spacing',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $element . '_typography_letter_spacing', array(
					'label'           => esc_html__( 'Letter Spacing', 'total' ),
					'section'         => 'wpex_typography_' . $element,
					'settings'        => $element . '_typography[letter-spacing]',
					'type'            => 'text',
					'active_callback' => $active_callback,
					'description'     => esc_html__( 'Please specify a unit (px, em, vm, vmax, vmin). If no unit is specified px will be used by default.', 'total' ),
				) ) );

			}

			// Margin
			if ( in_array( 'margin', $attributes ) ) {

				$wp_customize->add_setting( $element . '_typography[margin]', array(
					'type'              => 'theme_mod',
					'sanitize_callback' => 'esc_html',
					'transport'         => $transport,
				) );

				$wp_customize->add_control( $element . '_typography[margin]',
					array(
						'label'           => esc_html__( 'Margin', 'total' ),
						'section'         => 'wpex_typography_' . $element,
						'settings'        => $element . '_typography[margin]',
						'type'            => 'text',
						'active_callback' => $active_callback,
						'description'     => esc_html__( 'Please use the following format: top right bottom left. ', 'total' ),
				) );

			}

		}

	}

	/**
	 * Loop through settings.
	 *
	 * @since 1.6.0
	 */
	public function loop( $return = 'css' ) {
		$css                  = '';
		$tablet_landscape_css = '';
		$tablet_portrait_css  = '';
		$phone_landscape_css  = '';
		$phone_portrait_css   = '';
		$fonts                = array();
		$preview_styles       = array();
		$settings             = $this->get_settings();

		if ( ! $settings ) {
			return;
		}

		// Supported fonts when google services is disabled and there aren't any registered fonts.
		if ( ! wpex_has_google_services_support() && ! wpex_has_registered_fonts() ) {
			$supported_fonts = array_merge( wpex_standard_fonts(), wpex_add_custom_fonts() );
		}

		// Loop through settings that need typography styling applied to them
		foreach( $settings as $element => $array ) {

			// Check conditional first
			if ( 'css' === $return ) {
				if ( isset( $array['condition'] ) && ! call_user_func( $array['condition'] ) ) {
					continue;
				}
			}

			// Add empty css var
			$desktop_props          = '';
			$tablet_landscape_props = '';
			$tablet_portrait_props  = '';
			$phone_landscape_props  = '';
			$phone_portrait_props   = '';

			// Get target and current mod
			$target  = isset( $array['target'] ) ? $array['target'] : '';
			$get_mod = get_theme_mod( $element . '_typography' );

			// Attributes to loop through
			if ( ! empty( $array['attributes'] ) ) {
				$attributes = $array['attributes'];
			} else {
				$attributes = array(
					'font-family',
					'font-weight',
					'font-style',
					'font-size',
					'color',
					'line-height',
					'letter-spacing',
					'text-transform',
				);

				// Allow for margin on this attribute
				if ( isset( $array['margin'] ) ) {
					$attributes[] = 'margin';
				}

			}

			// Set attributes keys equal to vals
			$attributes = array_combine( $attributes, $attributes );

			// Exclude attributes
			if ( ! empty( $array['exclude'] ) ) {
				foreach ( $array['exclude'] as $k => $v ) {
					unset( $attributes[ $v ] );
				}
			}

			// Loop through attributes
			foreach ( $attributes as $attribute ) {

				// Define val
				$default = isset( $array['defaults'][$attribute] ) ? $array['defaults'][$attribute] : NULL;
				$val     = isset ( $get_mod[$attribute] ) ? $get_mod[$attribute] : $default;

				if ( 'font-family' == $attribute
					&& ! empty( $supported_fonts )
					&& ! in_array( $val, $supported_fonts )
				) {
					$val = null;
				}

				// If there is a value lets do something
				if ( $val ) {

					// Font Sizes have responsive settings so we need to treat them differently.
					if ( 'font-size' == $attribute && is_array( $val ) ) {

						$fontsize_pstyle = '';

						$responsive_bkpoints = array(
							'd'  => '',
							'tl' => '',
							'tp' => '',
							'pl' => '',
							'pp' => '',
						);

						$val = array_map( 'wpex_sanitize_font_size', $val );

						foreach ( $responsive_bkpoints as $bk_id => $bk_val ) {

							if ( ! empty( $val[$bk_id] ) ) {

								$bk_val = $attribute . ':' . $val[$bk_id] . ';';

								switch ( $bk_id ) {
									case 'd':
										if ( 'css' == $return ) {
											$desktop_props .= $bk_val;
										}
										$fontsize_pstyle .= $target . '{' . $bk_val . ';}';
										break;
									case 'tl':
										if ( 'css' == $return ) {
											$tablet_landscape_props .= $bk_val;
										}
										$fontsize_pstyle .= '@media(max-width:1024px){' . $target . '{' . $bk_val . ';}}';
										break;

									case 'tp':
										if ( 'css' == $return ) {
											$tablet_portrait_props .= $bk_val;
										}
										$fontsize_pstyle .= '@media(max-width:959px){' . $target . '{' . $bk_val . ';}}';
										break;
									case 'pl':
										if ( 'css' == $return ) {
												$phone_landscape_props .= $bk_val;
											}
											$fontsize_pstyle .= '@media(max-width:767px){' . $target . '{' . $bk_val . ';}}';
										break;
									case 'pp':
										if ( 'css' == $return ) {
											$phone_portrait_props .= $bk_val;
										}
										$fontsize_pstyle .= '@media(max-width:479px){' . $target . '{' . $bk_val . ';}}';
										break;
								} // end switch

								if ( 'preview_styles' == $return ) {
									$preview_styles['wpex-customizer-' . $element . '-font-size'] = $fontsize_pstyle;
								}

							}

						}

					} else {

						// Sanitize to remove extra quotes.
						$val = str_replace( '"', '', $val );

						// Sanitize data
						if ( 'letter-spacing' == $attribute ) {
							$val = wpex_sanitize_letter_spacing( $val );
						} elseif ( 'font-size' == $attribute ) {
							$val = wpex_sanitize_font_size( $val );
						}

						// Add quotes around font-family && font family to scripts array
						if ( 'font-family' == $attribute ) {
							$fonts[] = $val;
							$val = wpex_sanitize_font_family( $val ); // convert html characters
							if ( strpos( $val, '"' ) || strpos( $val, ',' ) ) {
								$val = $val;
							} else {
								$val = '"' . esc_html( $val ) . '"';
							}
						}

						// No value for this setting
						if ( ! $val ) {
							continue;
						}

						// Add to inline CSS
						if ( 'css' == $return ) {
							$desktop_props .= $attribute . ':' . $val . ';';
						}

						// Customizer styles need to be added for each attribute
						elseif ( 'preview_styles' == $return ) {
							$preview_styles['wpex-customizer-' . $element . '-' . $attribute] = $target . '{' . $attribute . ':' . $val . ';}';
						}

					}

				}

			} // End foreach attributes

			// Front-end inline CSS
			if ( $desktop_props ) {
				$css .= $target . '{' . $desktop_props . '}';
			}

			if ( $tablet_landscape_props ) {
				$tablet_landscape_css .= $target . '{' . $tablet_landscape_props . '}';
			}

			if ( $tablet_portrait_props ) {
				$tablet_portrait_css .=  $target . '{' . $tablet_portrait_props . '}';
			}

			if ( $phone_landscape_props ) {
				$phone_landscape_css .= $target . '{' . $phone_landscape_props . '}';
			}

			if ( $phone_portrait_props ) {
				$phone_portrait_css .= $target . '{' . $phone_portrait_props . '}';
			}

		} // End foreach settings

		// Check for custom font
		if ( $custom_font = get_theme_mod( 'load_custom_google_font_1' ) ) {
			$fonts[] = $custom_font;
		}

		// Update selected fonts class var
		if ( $fonts ) {
			$fonts = array_unique( $fonts ); // Return only 1 of each font
		}

		// Combine all CSS for output
		if ( $css || $tablet_landscape_css || $tablet_portrait_css || $phone_landscape_css || $phone_portrait_css ) {

			if ( $tablet_landscape_css ) {
				$css .= '@media(max-width:1024px){' . esc_attr( $tablet_landscape_css ) . '}';
			}

			if ( $tablet_portrait_css ) {
				$css .= '@media(max-width:959px){' . esc_attr( $tablet_portrait_css ) . '}';
			}

			if ( $phone_landscape_css ) {
				$css .= '@media(max-width:767px){' . esc_attr( $phone_landscape_css ) . '}';
			}

			if ( $phone_portrait_css ) {
				$css .= '@media(max-width:479px){' . esc_attr( $phone_portrait_css ) . '}';
			}

			$css = '/*TYPOGRAPHY*/' . $css;

		}

		// Return data
		switch ( $return ) {
			case 'css':
				return $css;
				break;
			case 'preview_styles':
				return $preview_styles;
				break;
			case 'fonts':
				return $fonts;
				break;
		}

	}

	/**
	 * Outputs the typography custom CSS.
	 */
	public function head_css( $output ) {
		$typography_css = $this->loop( 'css' );
		if ( $typography_css ) {
			$output .= $typography_css;
		}
		return $output;
	}

	/**
	 * Returns correct CSS to output to wp_head.
	 */
	public function live_preview_styles() {
		$live_preview_styles = $this->loop( 'preview_styles' );
		if ( is_array( $live_preview_styles ) ) {
			foreach ( $live_preview_styles as $key => $val ) {
				if ( ! empty( $val ) ) {
					echo '<style id="' . esc_attr( $key ) . '"> ' . $val . '</style>';
				}
			}
		}
	}

	/**
	 * Loads Google fonts via wp_enqueue_style.
	 */
	public function load_fonts() {
		if ( wpex_disable_google_services() ) {
			return;
		}
		$fonts = $this->loop( 'fonts' );
		if ( ! empty( $fonts ) && is_array( $fonts ) ) {
			foreach( $fonts as $font ) {
				wpex_enqueue_google_font( $font );
			}
		}
	}

	/**
	 * Return text transform choices.
	 */
	public function choices_text_transform() {
		return array(
			''           => esc_html__( 'Default', 'total' ),
			'capitalize' => esc_html__( 'Capitalize', 'total' ),
			'lowercase'  => esc_html__( 'Lowercase', 'total' ),
			'uppercase'  => esc_html__( 'Uppercase', 'total' ),
			'none'       => esc_html__( 'None', 'total' ),
		);
	}

	/**
	 * Return font style choices.
	 */
	public function choices_font_style() {
		return array(
			''       => esc_html__( 'Default', 'total' ),
			'normal' => esc_html__( 'Normal', 'total' ),
			'italic' => esc_html__( 'Italic', 'total' ),
		);
	}

	/**
	 * Return font weight choices.
	 */
	public function choices_font_weights() {
		return array(
			''    => esc_html__( 'Default', 'total' ),
			'100' => esc_html__( 'Extra Light: 100', 'total' ),
			'200' => esc_html__( 'Light: 200', 'total' ),
			'300' => esc_html__( 'Book: 300', 'total' ),
			'400' => esc_html__( 'Normal: 400', 'total' ),
			'500' => esc_html__( 'Medium: 500', 'total' ),
			'600' => esc_html__( 'Semibold: 600', 'total' ),
			'700' => esc_html__( 'Bold: 700', 'total' ),
			'800' => esc_html__( 'Extra Bold: 800', 'total' ),
			'900' => esc_html__( 'Black: 900', 'total' ),
		);
	}

	/**
	 * Loads all registered fonts in the Customizer for use with Typography options
	 */
	public function enqueue_registered_fonts() {

		$fonts = wpex_get_registered_fonts();

		if ( ! empty( $fonts ) ) {

			foreach( $fonts as $font => $args ) {

				if ( 'other' == $args['type'] ) {
					continue;
				}
				wpex_enqueue_font( $font, 'registered', $args );

			}

		}

	}

}
new Typography();