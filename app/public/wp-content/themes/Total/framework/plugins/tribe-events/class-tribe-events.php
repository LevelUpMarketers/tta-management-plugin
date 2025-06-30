<?php
/**
 * Configure the Tribe Events Plugin
 *
 * @package Total WordPress Theme
 * @subpackage Vendor
 * @version 5.0
 */

namespace TotalTheme;

use Tribe__Events__Community__Main;
use Tribe__Events__Main;

defined( 'ABSPATH' ) || exit;

final class Tribe_Events {

	/**
	 * Current plugin version.
	 */
	public $plugin_version;

	/**
	 * Our single Tribe_Events instance.
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
	 */
	final public function __wakeup() {
		throw new Exception( 'You\'re doing things wrong.' );
	}

	/**
	 * Create or retrieve the instance of Tribe_Events.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Tribe_Events;
			static::$instance->define_constants();
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Define constants.
	 */
	public function define_constants() {
		define( 'WPEX_TRIBE_EVENTS_DIR', WPEX_FRAMEWORK_DIR . 'plugins/tribe-events/' );
		define( 'WPEX_TRIBE_EVENTS_COMMUNITY_ACTIVE', class_exists( 'Tribe__Events__Community__Main' ) );
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Add Customizer settings
		add_filter( 'wpex_customizer_panels', array( $this, 'customizer_settings' ) );

		// Add custom sidebar
		add_filter( 'wpex_register_sidebars_array', array( $this, 'register_events_sidebar' ), 10 );

		// Custom styles
		if ( $this->has_theme_styles() ) {

			// Custom CSS
			if ( wpex_is_request( 'frontend' ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'load_custom_stylesheet' ), 10 );
			}

			// Add new accent colors
			add_filter( 'wpex_accent_texts', array( $this, 'accent_texts' ) );
			add_filter( 'wpex_accent_backgrounds', array( $this, 'accent_backgrounds' ) );
			add_filter( 'wpex_accent_hover_backgrounds', array( $this, 'accent_hover_backgrounds' ) );
			add_filter( 'wpex_accent_borders', array( $this, 'accent_borders' ) );

		}

		// Back-end functions
		if ( wpex_is_request( 'admin' ) ) {

			add_filter( 'wpex_main_metaboxes_post_types', array( $this, 'metaboxes' ), 10 );

		}

		// Front-end functions
		if ( wpex_is_request( 'frontend' ) ) {

			// Filter body classes
			add_filter( 'body_class', array( $this, 'body_class' ), 10 );

			// Set correct page ID for post type archive
			add_filter( 'wpex_post_id', array( $this, 'set_events_page_id' ), 10 );

			// Configure layouts
			add_filter( 'wpex_post_layout_class', array( $this, 'layouts' ), 10 );

			// Alter main title
			add_filter( 'wpex_page_header_title_args', array( $this, 'page_header_title_args' ), 10 );

			// Add event meta after title
			add_filter( 'wpex_post_subheading', array( $this, 'post_subheading' ), 10 );

			// Display custom sidebar
			add_filter( 'wpex_get_sidebar', array( $this, 'display_events_sidebar' ), 10 );

			// Disable next/previous links
			add_filter( 'wpex_has_next_prev', array( $this, 'next_prev' ), 10, 2 );

			// Redirect page used for page settings to the homepage
			if ( get_theme_mod( 'tribe_events_main_page' ) && false === wpex_is_request( 'admin' ) ) {
				add_filter( 'template_redirect', array( $this, 'redirect_events_page_to_events_archive' ) );
			}

			// Edit post link for community events
			if ( WPEX_TRIBE_EVENTS_COMMUNITY_ACTIVE ) {
				add_filter( 'get_edit_post_link', array( $this, 'get_edit_post_link' ), 40 );
			}

		}

	}

	/**
	 * Check if the theme should add custom theme styles
	 */
	public function has_theme_styles() {
		return get_theme_mod( 'tribe_events_total_styles', true );
	}

	/**
	 * Get plugin version.
	 */
	public function get_plugin_version() {
		if ( ! $this->plugin_version ) {
			if ( class_exists( 'Tribe__Events__Main' ) && defined( 'Tribe__Events__Main::VERSION' ) ) {
				$this->plugin_version = Tribe__Events__Main::VERSION;
			}
		}
		return $this->plugin_version;
	}

	/**
	 * Filter body classes.
	 */
	public function body_class( $classes ) {
		if ( get_theme_mod( 'tribe_events_page_header_details', true )
			&& is_singular( 'tribe_events' )
			&& wpex_has_page_header()
		) {
			$classes[] = 'tribe-page-header-details';
		}
		return $classes;
	}

	/**
	 * Load custom CSS file for tweaks.
	 */
	public function load_custom_stylesheet() {

		if ( ! $this->has_theme_styles() ) {
			return;
		}

		// Newer plugin styles
		if ( version_compare( $this->get_plugin_version(), '5.0.0', '>=' ) ) {

			wp_enqueue_style(
				'wpex-the-events-calendar',
				wpex_asset_url( 'css/wpex-the-events-calendar.css' )
			);

		}

		// Old Pre 5.0.0 styles
		else {

			wp_enqueue_style(
				'wpex-tribe-events-old',
				wpex_asset_url( 'css/backwards-compat/wpex-tribe-events-old.css' )
			);

			if ( WPEX_TRIBE_EVENTS_COMMUNITY_ACTIVE ) {
				wp_enqueue_style(
					'wpex-tribe-events-community',
					wpex_asset_url( 'css/backwards-compat/wpex-tribe-events-community-old.css' ),
					array( 'tribe_events-community-styles' )
				);
			}
		}

	}

	/**
	 * Set correct page id for main events page.
	 */
	public function set_events_page_id( $id ) {
		if ( is_post_type_archive( 'tribe_events' ) && $page_id = wpex_get_tribe_events_main_page_id() ) {
			return $page_id;
		}
		return $id;
	}

	/**
	 * Alter the post layouts for all events.
	 */
	public function layouts( $class ) {

		// Return full-width for event posts and archives
		if ( wpex_is_tribe_events() ) {
			if ( is_singular( 'tribe_events' ) ) {
				$class = get_theme_mod( 'tribe_events_single_layout', 'full-width' );
			} else {
				$class = get_theme_mod( 'tribe_events_archive_layout', 'full-width' );
			}
		}

		// Full width for community edit
		if ( WPEX_TRIBE_EVENTS_COMMUNITY_ACTIVE ) {

			// My events
			if ( tribe_is_community_edit_event_page() || tribe_is_community_my_events_page() ) {
				$class = get_theme_mod( 'tribe_events_community_my_events_layout', 'full-width' );
			}

		}

		// Return class
		return $class;

	}

	/**
	 * Add the Page Settings metabox to the events calendar.
	 */
	public function metaboxes( $types ) {
		$types['tribe_events'] = 'tribe_events';
		return $types;
	}

	/**
	 * Alter the main page header title text for tribe events.
	 */
	public function page_header_title_args( $args ) {

		if ( is_search() ) {
			return $args; // Fixes issue with search results and the events calendar.
		}

		if ( tribe_is_event_category() ) {
			$main_page = wpex_get_tribe_events_main_page_id();
			$args['string'] = $main_page ? get_the_title( $main_page ) : esc_html__( 'Events Calendar', 'total' );
		} elseif ( tribe_is_month() ) {
			$post_id = wpex_get_current_post_id();
			$args['string'] = $post_id ? get_the_title( $post_id ) : esc_html__( 'Events Calendar', 'total' );
		} elseif ( tribe_is_event() && ! tribe_is_day() && ! is_single() ) {
			$args['string'] = esc_html__( 'Events List', 'total' );
		} elseif ( tribe_is_day() ) {
			$args['string'] = esc_html__( 'Single Day Events', 'total' );
		} elseif ( is_singular( 'tribe_events' ) ) {
			if ( get_theme_mod( 'tribe_events_page_header_details', true ) ) {
				$args['html_tag'] = 'h1';
				$args['string']   = single_post_title( '', false );
			} else {
				$obj = get_post_type_object( 'tribe_events' );
				$args['string'] = $obj->labels->name;
			}
		}

		return $args;

	}

	/**
	 * Alter the post subheading for events.
	 */
	public function post_subheading( $subheading ) {
		if ( is_singular( 'tribe_events' ) && get_theme_mod( 'tribe_events_page_header_details', true ) ) {
			$subheading = '<div class="page-subheading-extra wpex-mt-5">';
				$subheading .= tribe_events_event_schedule_details( wpex_get_current_post_id(), '<div class="schedule"><span class="ticon ticon-calendar-o wpex-mr-10"></span>', '</div>' );
			if ( $cost = tribe_get_cost( null, true ) ) {
				$subheading .= '<div class="cost"><span class="ticon ticon-money wpex-mr-10"></span>' . wp_kses_post( $cost ) . '</div>';
			}
			$subheading .= '</div>';
		}
		return $subheading;
	}

	/**
	 * Register a new events sidebar area.
	 */
	public function register_events_sidebar( $sidebars ) {
		$sidebars['tribe_events_sidebar'] = esc_html__( 'Events Sidebar', 'total' );
		return $sidebars;
	}

	/**
	 * Alter main sidebar to display events sidebar.
	 */
	public function display_events_sidebar( $sidebar ) {
		if ( wpex_is_tribe_events() && is_active_sidebar( 'tribe_events_sidebar' ) ) {
			$sidebar = 'tribe_events_sidebar';
		}
		return $sidebar;
	}

	/**
	 * Disables the next/previous links for tribe events because they already have some.
	 */
	public function next_prev( $return, $post_type ) {
		if ( 'tribe_events' == $post_type ) {
			return false;
		}
		return $return;
	}

	/**
	 * Adds custom accent border colors.
	 */
	public function accent_texts( $targets ) {
		if ( version_compare( $this->get_plugin_version(), '5.0.0', '>=' ) ) {
			$targets = array_merge( $targets, array(
				'.tribe-events .tribe-events-c-ical__link',
				'.single-tribe_events a.tribe-events-gcal',
				'.single-tribe_events a.tribe-events-gcal:hover',
				'.single-tribe_events a.tribe-events-ical',
				'.single-tribe_events a.tribe-events-ical:hover',
			) );
		}
		return $targets;
	}

	/**
	 * Adds background accents for tribe events.
	 */
	public function accent_backgrounds( $targets ) {

		if ( version_compare( $this->get_plugin_version(), '5.0.0', '>=' ) ) {

			$tribe_targets = array(
				'.tribe-common .tribe-common-c-btn',
				'.tribe-common a.tribe-common-c-btn',
				'#tribe-events .tribe-events-button',
				'#tribe_events_filters_wrapper input[type=submit]',
				'.tribe-events-button',
				'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]',
				'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]>a',
			);

		} else {

			$tribe_targets = array(
				'#tribe-events .tribe-events-button',
				'#tribe-events .tribe-events-button:hover',
				'#tribe_events_filters_wrapper input[type=submit]',
				'.tribe-events-button',
				'.tribe-events-button.tribe-active:hover',
				'.tribe-events-button.tribe-inactive',
				'.tribe-events-button:hover',
				'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]',
				'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]>a',
				'#my-events .button, #my-events .button:hover',
				'#add-new .button, #add-new .button:hover',
				'.table-menu-btn, .table-menu-btn:hover',
			);

		}

		return array_merge( $targets, $tribe_targets );

	}

	/**
	 * Adds custom accent hover backgrounds.
	 */
	public function accent_hover_backgrounds( $targets ) {

		if ( version_compare( $this->get_plugin_version(), '5.0.0', '>=' ) ) {

			$targets = array_merge( $targets, array(
				'.tribe-common .tribe-common-c-btn:hover',
				'.tribe-common a.tribe-common-c-btn:hover',
				'#tribe-events .tribe-events-button:hover',
				'#tribe_events_filters_wrapper input[type=submit]:hover',
				'.tribe-events-button:hover',
				'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]:hover',
				'.tribe-events-calendar td.tribe-events-present div[id*=tribe-events-daynum-]>a:hover,
				.tribe-events .tribe-events-c-ical__link:hover',
			) );

		}

		return $targets;
	}

	/**
	 * Adds custom accent border colors.
	 */
	public function accent_borders( $targets ) {
		if ( version_compare( $this->get_plugin_version(), '5.0.0', '>=' ) ) {
			$targets = array_merge( $targets, array(
				'.tribe-events .tribe-events-c-ical__link',
			) );
		}
		return $targets;
	}

	/**
	 * Adds Customizer settings for Tribe Events.
	 *
	 * @since 3.3.3
	 */
	public function customizer_settings( $panels ) {
		$branding = ( $branding = wpex_get_theme_branding() ) ? ' (' . $branding . ')' : '';
		$panels['tribe_events'] = array(
			'title'      => esc_html__( 'Events Calendar', 'total' ) . $branding,
			'is_section' => true,
			'settings'   => WPEX_TRIBE_EVENTS_DIR . 'customizer.php'
		);
		return $panels;
	}

	/**
	 * Redirects.
	 *
	 * @todo Can we remove this now?
	 */
	public function redirect_events_page_to_events_archive() {

		// Check for main page
		if ( $page_id = get_theme_mod( 'tribe_events_main_page' ) ) {

			// Redirect on page as long as it's not posts page to prevent endless loop
			if ( is_page( $page_id ) && $page_id != get_option( 'page_for_posts' ) ) {

				// Get archive link
				$archive_link = get_post_type_archive_link( 'tribe_events' );

				// Set redirect
				$redirect = $archive_link ? $archive_link : home_url( '/' );

				// Redirect and exit for security
				wp_redirect( esc_url( $redirect ), 301 );
				exit();
			}

		}

	}

	/**
	 * Edit post link.
	 */
	public function get_edit_post_link( $url ) {
		if ( is_singular( 'tribe_events' ) && class_exists( 'Tribe__Events__Community__Main' ) ) {
			$url = esc_url( Tribe__Events__Community__Main::instance()->getUrl( 'edit', get_the_ID(), null, Tribe__Events__Main::POSTTYPE ) );
		}
		return $url;
	}

}
Tribe_Events::instance();