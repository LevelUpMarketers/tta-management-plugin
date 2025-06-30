<?php
/**
 * bbPress Configuration Class
 *
 * @package Total WordPress Theme
 * @subpackage Vendor
 * @version 5.0
 */

namespace TotalTheme;

use WPEX_Breadcrumbs;

defined( 'ABSPATH' ) || exit;

final class bbPress {

	/**
	 * Our single bbPress instance.
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
	 * Create or retrieve the instance of bbPress.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new bbPress;
			static::$instance->definitions();
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Define new constants.
	 */
	public function definitions() {
		define( 'WPEX_BBPRESS_DIR', WPEX_FRAMEWORK_DIR . 'plugins/bbpress/' );
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {

		// Load custom CSS for bbPress
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		// Add a bbPress sidebar
		if ( get_theme_mod( 'bbpress_custom_sidebar', true ) ) {
			add_filter( 'wpex_register_sidebars_array', array( $this, 'register_sidebar' ) );
		}

		// Alter main sidebar to display bbPress sidebar
		add_filter( 'wpex_get_sidebar', array( $this, 'display_sidebar' ) );

		// Remove page title
		add_filter( 'wpex_single_blocks', array( $this, 'page_blocks' ), 99, 2 );

		// Disable next/prev
		add_filter( 'wpex_has_next_prev', array( $this, 'next_prev' ), 99 );

		// Set correct bbPress layouts
		add_filter( 'wpex_post_layout_class', array( $this, 'layouts' ) );

		// Title fixes
		add_filter( 'wpex_title', array( $this, 'title' ) );

		// Add custom post classes
		add_filter( 'post_class', array( $this, 'post_class' ) );

		// Breadcrumbs Fixes
		add_filter( 'wpex_breadcrumbs_trail', array( $this, 'breadcrumbs' ) );

		// Accents
		add_filter( 'wpex_accent_texts', array( $this, 'accent_texts' ) );
		add_filter( 'wpex_accent_backgrounds', array( $this, 'accent_backgrounds' ) );

		// Add Customizer panel
		add_filter( 'wpex_customizer_panels', array( $this, 'add_customizer_panel' ) );

	}

	/**
	 * Load custom CSS for bbPress.
	 *
	 * Must load globally because of Widgets.
	 */
	public function scripts() {
		wp_enqueue_style( 'wpex-bbpress', wpex_asset_url( 'css/wpex-bbpress.css' ), array( 'bbp-default' ), WPEX_THEME_VERSION );
		wp_style_add_data( 'wpex-bbpress', 'rtl', 'replace' );
	}

	/**
	 * Registers a bbpress_sidebar widget area.
	 */
	public function register_sidebar( $sidebars ) {
		$sidebars['bbpress_sidebar'] = esc_html__( 'bbPress Sidebar', 'total' );
		return $sidebars;
	}

	/**
	 * Alter main sidebar to display bbpress_sidebar sidebar.
	 */
	public function display_sidebar( $sidebar ) {
		if ( function_exists( 'is_bbpress' )
			&& is_bbpress()
			&& get_theme_mod( 'bbpress_custom_sidebar', true )
		) {
			$sidebar = 'bbpress_sidebar';
		}
		return $sidebar;
	}

	/**
	 * Alter page blocks to remove elements that aren't needed for bbPress.
	 */
	public function page_blocks( $blocks, $type ) {
		if ( function_exists( 'is_bbpress' ) && is_bbpress() ) {
			return array( 'content' ); // Only content needed
		}
		return $blocks;
	}

	/**
	 * Disable next/prev for bbPress.
	 */
	public function next_prev( $bool ) {
		if ( function_exists( 'is_bbpress' ) && is_bbpress() ) {
			return false;
		}
		return $bool;
	}

	/**
	 * Set layouts.
	 */
	public function layouts( $layout ) {

		// Forum
		if ( bbp_is_forum_archive() ) {
			$layout = get_theme_mod( 'bbpress_forums_layout' );
		}

		// Single forum
		if ( bbp_is_single_forum() ) {
			$layout = get_theme_mod( 'bbpress_single_forum_layout' );
		}

		// Topics
		if ( bbp_is_topic_archive() ) {
			$layout = get_theme_mod( 'bbpress_topics_layout' );
		}

		// Topics
		if ( bbp_is_single_topic()
			|| bbp_is_topic_edit()
			|| bbp_is_topic_merge()
			|| bbp_is_topic_split()
		) {
			$layout = get_theme_mod( 'bbpress_single_topic_layout' );
		}

		// User profile
		if ( bbp_is_single_user() ) {
			$layout = get_theme_mod( 'bbpress_user_layout', 'full-width' );
		}

		// Return layout
		return $layout;

	}

	/**
	 * Fix page header title.
	 */
	public function title( $title ) {
		if ( bbp_is_single_forum()
			|| bbp_is_single_topic()
			|| bbp_is_topic_edit()
			|| bbp_is_topic_merge()
			|| bbp_is_topic_split()
		) {
			$title = get_the_title();
		}
		return $title;
	}

	/**
	 * Add custom post classes.
	 */
	public function post_class( $classes ) {
		if ( 'forum' == get_post_type() ) {
			$count = bbp_show_lead_topic() ? bbp_get_forum_reply_count() : bbp_get_forum_post_count();
			$count = ( 0 == $count ) ? 'no' : $count;
			$classes[] = $count .'-replies';
		}
		return $classes;
	}

	/**
	 * Fix Breadcrumbs trail.
	 */
	public function breadcrumbs( $trail ) {

		if ( ! class_exists( 'WPEX_Breadcrumbs' ) ) {
			return $trail;
		}

		// Set correct archive for single topic
		if ( bbp_is_single_topic() || bbp_is_single_user() ) {
			$obj = get_post_type_object( 'forum' );
			if ( $obj && $forums_link = get_post_type_archive_link( 'forum' ) ) {
				$trail['post_type_archive'] = WPEX_Breadcrumbs::get_crumb_html( $obj->labels->name, $forums_link, 'trail-forums' );
			}
			/*
			@deprecated in 4.6 - already included in main crumbs
			if ( bbp_is_single_topic() ) {
				$forum = wp_get_post_parent_id( get_the_ID() );
				if ( $forum ) {
					$text = get_the_title( $forum );
					$link = get_permalink( $forum );
					$trail['pre_trail_end'] = WPEX_Breadcrumbs::get_crumb_html( $text, $link, 'trail-forums' );
				}
			}*/
		}

		// Set correct end_trail for user
		if ( bbp_is_single_user() ) {
			$trail['trail_end'] = get_the_title();
		}

		// Search results
		if ( bbp_is_search_results() ) {
			$obj = get_post_type_object( 'forum' );
			if ( $obj && $forums_link = get_post_type_archive_link( 'forum' ) ) {
				$trail['post_type_archive'] = WPEX_Breadcrumbs::get_crumb_html( $obj->labels->name, $forums_link, 'trail-forums' );
			}
			$trail['trail_end'] = esc_html__( 'Search Results', 'total' );
		}

		// Return breadcrumbs trail
		return $trail;

	}

	/**
	 * Adds new elements for text accents.
	 */
	public function accent_texts( $elements ) {
		return array_merge( array(
			'.bbp-body a.bbp-forum-title',
			'.bbp-reply-title a.bbp-topic-permalink',
		), $elements );
		return $elements;
	}

	/**
	 * Adds new elements for background accents.
	 */
	public function accent_backgrounds( $elements ) {
		return array_merge( array(
			'#bbpress-forums #bbp-single-user-details #bbp-user-navigation li.current a',
		), $elements );
		return $elements;
	}

	/**
	 * Adds new Customizer Panel for bbPress.
	 */
	public function add_customizer_panel( $panels ) {
		$panels['bbpress'] = array(
			'title'      => esc_html__( 'bbPress', 'total' ),
			'is_section' => true,
			'settings'   => WPEX_BBPRESS_DIR . 'customizer.php'
		);
		return $panels;
	}

}
bbPress::instance();