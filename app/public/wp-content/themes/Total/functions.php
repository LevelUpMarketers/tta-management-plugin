<?php
/**
 * This is where the magic begins.
 *
 * @package Total WordPress Theme
 * @subpackage Template
 * @version 5.0.6
 *
 * Theme URI     : https://total.wpexplorer.com/
 * Documentation : https://wpexplorer-themes.com/total/docs/
 * License URI   : http://themeforest.net/licenses/terms/regular
 * Subscribe     : https://www.wpexplorer.com/blog/
 */

defined( 'ABSPATH' ) || exit;

final class WPEX_Theme_Setup {

	/**
	 * Our single WPEX_Theme_Setup instance.
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
	 * Create or retrieve the instance of WPEX_Theme_Setup.
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new WPEX_Theme_Setup;
			static::$instance->define_constants();
			static::$instance->includes();
			static::$instance->init_hooks();
		}

		return static::$instance;
	}

	/**
	 * Define constants.
	 */
	public function define_constants() {

		// TotalTheme version
		define( 'TOTAL_THEME_ACTIVE', true );
		define( 'WPEX_THEME_VERSION', '5.0.6' );

		// Supported Bundled plugin versions
		define( 'WPEX_VC_SUPPORTED_VERSION', '6.4.1' );
		define( 'WPEX_THEME_CORE_PLUGIN_SUPPORTED_VERSION', '1.2.5' );

		// Theme Branding
		define( 'WPEX_THEME_BRANDING', get_theme_mod( 'theme_branding', 'Total' ) );

		// Theme changelog URL
		define( 'WPEX_THEME_CHANGELOG_URL', 'https://wpexplorer-themes.com/total/changelog/' );

		// Theme directory location and URL
		define( 'WPEX_THEME_DIR', get_template_directory() );
		define( 'WPEX_THEME_URI', get_template_directory_uri() );

		// Theme Panel slug and hook prefix
		define( 'WPEX_THEME_PANEL_SLUG', 'wpex-panel' );
		define( 'WPEX_ADMIN_PANEL_HOOK_PREFIX', 'theme-panel_page_' . WPEX_THEME_PANEL_SLUG );

		// Theme framework location
		define( 'WPEX_FRAMEWORK_DIR', WPEX_THEME_DIR . '/framework/' );
		define( 'WPEX_FRAMEWORK_DIR_URI', WPEX_THEME_URI . '/framework/' );

		// Theme stylesheet and main javascript handles
		define( 'WPEX_THEME_STYLE_HANDLE', 'wpex-style' );
		define( 'WPEX_THEME_JS_HANDLE', 'wpex-core' ); //@todo rename to wpex-js?

		// Check if certain plugins are enabled
		define( 'WPEX_PTU_ACTIVE', class_exists( 'Post_Types_Unlimited' ) );
		define( 'WPEX_VC_ACTIVE', class_exists( 'Vc_Manager' ) );
		define( 'WPEX_TEMPLATERA_ACTIVE', class_exists( 'VcTemplateManager' ) );
		define( 'WPEX_WOOCOMMERCE_ACTIVE', class_exists( 'WooCommerce' ) );
		define( 'WPEX_WPML_ACTIVE', class_exists( 'SitePress' ) );
		define( 'WPEX_ELEMENTOR_ACTIVE', did_action( 'elementor/loaded' ) );
		define( 'WPEX_BBPRESS_ACTIVE', class_exists( 'bbPress' ) );

		// Theme Core post type checks
		define( 'WPEX_PORTFOLIO_IS_ACTIVE', get_theme_mod( 'portfolio_enable', true ) );
		define( 'WPEX_STAFF_IS_ACTIVE', get_theme_mod( 'staff_enable', true ) );
		define( 'WPEX_TESTIMONIALS_IS_ACTIVE', get_theme_mod( 'testimonials_enable', true ) );

	}

	/**
	 * Include theme files.
	 */
	public function includes() {

		$this->global_includes();

		$this->admin_includes();

		$this->frontend_includes();

	}

	/**
	 * Include global required files.
	 */
	public function global_includes() {

		/*** Always include ***/

		// Runs after updating your theme to migrate settings
		require_once WPEX_FRAMEWORK_DIR . 'updates/after-update.php';

		// Deprecated functions/classes
		require_once WPEX_FRAMEWORK_DIR . 'functions/deprecated.php';

		// Theme mod helper functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/theme-mods.php';

		// Core theme functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/core-functions.php';

		// Conditional functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/conditionals.php';

		// CSS Utility helper functions that return arrays of helper class names for Customizer/Module settings
		require_once WPEX_FRAMEWORK_DIR . 'functions/css-utility.php';

		// HTML/CSS parser functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/parsers.php';

		// Sanitization functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/sanitization-functions.php';

		// Functions that return arrays for use in Customizer/Module settings
		require_once WPEX_FRAMEWORK_DIR . 'functions/arrays.php';

		// Helper functions for translation plugins such as WPML/Polylang
		require_once WPEX_FRAMEWORK_DIR . 'functions/translations.php';

		// Wrappers for get_template_part and array of template parts
		require_once WPEX_FRAMEWORK_DIR . 'functions/template-parts.php';

		// Fallback functions for newer WP functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/wp-fallbacks.php';

		// Helper functions return correct post type names/slugs for Portfolio/Staff/Testimonials
		require_once WPEX_FRAMEWORK_DIR . 'functions/post-types-branding.php';

		// Recommended/Bundled plugin functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/recommended-plugins.php';

		// Custom Font helper functions and arrays of available fonts
		require_once WPEX_FRAMEWORK_DIR . 'functions/fonts.php';

		// Theme Icon helper functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/theme-icons.php';

		// Post Thumbnail helper functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/post-thumbnails.php';

		// Image Overlay functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/overlays.php';

		// Helper functions for generating shape dividers
		require_once WPEX_FRAMEWORK_DIR . 'functions/shape-dividers.php';

		// CPT Post blocks
		require_once WPEX_FRAMEWORK_DIR . 'functions/blocks-single.php';

		// CPT Entry blocks
		require_once WPEX_FRAMEWORK_DIR . 'functions/blocks-entry.php';

		// CPT Meta blocks
		require_once WPEX_FRAMEWORK_DIR . 'functions/blocks-meta.php';

		// Helper function returns correct aria-label based on location
		require_once WPEX_FRAMEWORK_DIR . 'functions/aria-labels.php';

		// WPEX cards
		require_once WPEX_FRAMEWORK_DIR . 'cards/card-functions.php';
		require_once WPEX_FRAMEWORK_DIR . 'cards/class-wpex-card.php';

		// Theme builder functions
		require_once WPEX_FRAMEWORK_DIR . 'theme-builder/theme-builder-functions.php';

		// After switch theme actions
		require_once WPEX_FRAMEWORK_DIR . 'wp-actions/after-switch-theme.php';

		// Register widget areas
		require_once WPEX_FRAMEWORK_DIR . 'wp-actions/register-widget-areas.php';

		// Disable WP uptdate checks to prevent issues with other themes using same Total theme name
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/disable-wp-update-check.php';

		// SSL fix for wp_get_attachment_url to prevent mixed content errors
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/honor-ssl-for-attachements.php';

		// Allow custom protocols such as callto
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/allowed-kses-protocols.php';

		// Helper class for sanitizing data
		require_once WPEX_FRAMEWORK_DIR . 'classes/class-sanitize-data.php';

		// Image Resizer class
		require_once WPEX_FRAMEWORK_DIR . 'classes/class-resize-image.php';

		// Font Loader class used for enqueueing Google, Adobe or custom fonts
		require_once WPEX_FRAMEWORK_DIR . 'classes/class-font-loader.php';

		// Class that generates CSS for custom fonts
		require_once WPEX_FRAMEWORK_DIR . 'classes/class-render-custom-font-css.php';

		// Theme Builder
		require_once WPEX_FRAMEWORK_DIR . 'theme-builder/class-theme-builder.php';

		/** Maybe include */

		// Header builder
		if ( get_theme_mod( 'header_builder_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'theme-builder/class-header-builder.php';
		}

		// Footer builder
		if ( get_theme_mod( 'footer_builder_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'theme-builder/class-footer-builder.php';
		}

		// Custom 404 Page
		if ( get_theme_mod( 'custom_404_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'theme-builder/class-custom-404.php';
		}

		// Page animations
		if ( get_theme_mod( 'page_animations_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-page-animations.php';
		}

		// WP Header Image support
		if ( get_theme_mod( 'header_image_enable', false ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-wp-custom-header.php';
		}

		// Custom accent colors
		if ( get_theme_mod( 'accent_colors_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-accent-colors.php';
		}

		// Custom border colors
		if ( get_theme_mod( 'border_colors_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-border-colors.php';
		}

		// Custom output for WP gallery
		if ( wpex_custom_wp_gallery_supported() ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-post-gallery.php';
		}

		// Under Construction
		if ( get_theme_mod( 'under_construction_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-under-construction.php';
		}

		// Helper class for disabling Google services
		if ( wpex_disable_google_services() ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-disable-google-services.php';
		}

		// Custom Favicons panel and output
		if ( get_theme_mod( 'favicons_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-favicons.php';
		}

		// Custom login page
		if ( get_theme_mod( 'custom_admin_login_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-custom-login.php';
		}

		// Custom actions panel
		if ( get_theme_mod( 'custom_actions_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-custom-actions.php';
		}

		// Helper class for removing cpt slugs
		if ( get_theme_mod( 'remove_posttype_slugs', false ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-remove-post-type-slugs.php';
		}

		/** Plugin/Vendor Support*/

		// WPBakery support and mods
		if ( WPEX_VC_ACTIVE && wpex_has_vc_mods() ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/wpbakery/class-wpbakery.php';
		}

		// WooCommerce support
		if ( WPEX_WOOCOMMERCE_ACTIVE ) {
			if ( wpex_has_woo_mods() && wpex_woo_version_supported() ) {
				require_once WPEX_FRAMEWORK_DIR . 'plugins/woocommerce/class-woocommerce.php';
			} else {
				require_once WPEX_FRAMEWORK_DIR . 'plugins/woocommerce/class-woocommerce-vanilla.php';
			}
		}

		// Elementor support
		if ( WPEX_ELEMENTOR_ACTIVE && apply_filters( 'wpex_elementor_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-elementor.php';
		}

		// Post Types Unlimited Support
		if ( WPEX_PTU_ACTIVE ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-post-types-unlimited.php';
		}

		// Yoast SEO support
		if ( defined( 'WPSEO_VERSION' ) && apply_filters( 'wpex_yoastseo_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-yoast-seo.php';
		}

		// The Events Calendar support
		if ( class_exists( 'Tribe__Events__Main' ) && apply_filters( 'wpex_tribe_events_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/tribe-events/class-tribe-events.php';
		}

		// One-Click Demo importer support
		if ( class_exists( 'OCDI\OneClickDemoImport' ) && apply_filters( 'wpex_ocdi_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-ocdi.php';
		}

		// W3 Total cache support.
		if ( defined( 'W3TC' ) && apply_filters( 'wpex_w3_total_cache_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-w3-total-cache.php';
		}

		// Real Media library support
		if ( defined( 'RML_VERSION' ) && apply_filters( 'wpex_realmedialibrary_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/real-media-library.php';
		}

		// WPML support
		if ( WPEX_WPML_ACTIVE && apply_filters( 'wpex_wpml_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-wpml.php';
		}

		// Polylang support
		if ( class_exists( 'Polylang' ) && apply_filters( 'wpex_polylang_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-polylang.php';
		}

		// bbPress support
		if ( WPEX_BBPRESS_ACTIVE && apply_filters( 'wpex_bbpress_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/bbpress/class-bbpress.php';
		}

		// BuddyPress support
		if ( function_exists( 'buddypress' ) && apply_filters( 'wpex_buddypress_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-buddy-press.php';
		}

		// Contact form 7 support
		if ( defined( 'WPCF7_VERSION' ) && apply_filters( 'wpex_contactform7_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-contact-form-7.php';
		}

		// Gravity form support
		if ( class_exists( 'RGForms' ) && apply_filters( 'wpex_gravityforms_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-gravity-forms.php';
		}

		// JetPack support
		if ( class_exists( 'Jetpack' ) && apply_filters( 'wpex_jetpack_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-jetpack.php';
		}

		// Learndash support
		if ( defined( 'LEARNDASH_VERSION' ) && apply_filters( 'wpex_learndash_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/learn-dash/class-learn-dash.php';
		}

		// Sensei plugin support
		if ( function_exists( 'Sensei' ) && apply_filters( 'wpex_sensei_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-sensei.php';
		}

		// Custom Post Type UI support
		if ( function_exists( 'cptui_init' ) && apply_filters( 'wpex_cptui_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-custom-post-type-ui.php';
		}

		// Massive Addons for WPBakery support
		if ( defined( 'MPC_MASSIVE_VERSION' ) && apply_filters( 'wpex_massive_addons_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-massive-addons.php';
		}

		// TablePress support
		if ( class_exists( 'TablePress' ) && apply_filters( 'wpex_tablepress_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-table-press.php';
		}

		/* LayerSlider support @todo remove
		if ( class_exists( 'LS_Sliders' ) && apply_filters( 'wpex_layerslider_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-layer-slider.php';
		}*/

		// Slider Revolution support
		if ( class_exists( 'RevSlider' ) && apply_filters( 'wpex_revslider_support', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'plugins/class-revslider.php';
		}


		/* These Classes must Load last */

		// Image sizes panel and registration
		if ( get_theme_mod( 'image_sizes_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-images-sizes.php';
		}

		// Customizer utility class and theme settings array
		require_once WPEX_FRAMEWORK_DIR . 'customizer/class-wpex-customizer.php';

		// Typography Customizer panel, settings and front-end output
		if ( get_theme_mod( 'typography_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/class-typography.php'; // requires customizer
		}

	}

	/**
	 * Include required admin files.
	 */
	public function admin_includes() {

		// Main Theme Panel
		require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-admin-panel.php';

		// Provide auto updates for the theme
		if ( get_theme_mod( 'auto_updates', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'updates/class-updater.php';
		}

		// Plugin update notifications
		if ( apply_filters( 'wpex_has_bundled_plugin_update_notices', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'updates/class-plugin-updates.php';
		}

		// Auto update filters
		require_once WPEX_FRAMEWORK_DIR . 'updates/class-auto-updates.php';

		// Post type editor class
		if ( get_theme_mod( 'post_type_admin_settings', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-post-type-editor-panel.php';
		}

		// Add custom fields for media attachments
		if ( get_theme_mod( 'custom_attachment_fields', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-media-meta-fields.php';
		}

		// Import/Export panel
		if ( get_theme_mod( 'import_export_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-import-export-panel.php';
		}

		// Theme license panel
		if ( apply_filters( 'wpex_show_license_panel', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-license-panel.php';
		}

		// Custom mce editor edits
		require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-mce-editor.php';

		// Accessibility Panel
		if ( apply_filters( 'wpex_accessibility_panel', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-accessibility-panel.php';
		}

		// Custom styles for the WP editor (classic & gutenberg)
		if ( get_theme_mod( 'editor_styles_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/admin/class-editor-styles.php';
		}

		// Register & Enqueue scripts for use in the admin
		require_once WPEX_FRAMEWORK_DIR . 'wp-actions/admin/admin-enqueue-scripts.php';

		// Display Thumbnails in the WP admin posts list view
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/admin/dashboard-thumbnails.php';

	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {

		// Adds Google analytics tracking to the site based on panel setting
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/google-analytics.php';

		// Returns correct aria landmark based on location
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/aria-landmark.php';

		// Control site layout
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/layouts.php';

		// Theme breadcrumb helper functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/breadcrumbs.php';

		// wpex_the_content function for displaying the_content using custom filters
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/wpex-the-content.php';

		// L10n for theme javascript
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/js-localize-data.php';

		// Adds wp_head theme meta tags
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/head-meta-tags.php';

		// Returns correct schema markup based on location
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/schema-markup.php';

		// Theme social share function
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/social-share.php';

		// Post video functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/videos.php';

		// Post audio functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/audio.php';

		// Author bio functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/author.php';

		// Post media functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/post-media.php';

		// Custom excerpt functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/excerpts.php';

		// Togglebar functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/togglebar.php';

		// Topbar functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/topbar.php';

		// Header functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/header.php';

		// Header menu functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/header-menu.php';

		// Returns current page title
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/title.php';

		// Post slider functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/post-slider.php';

		// Post gallery functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/post-gallery.php';

		// Page header functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/page-header.php';

		// Sidebar functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/sidebar.php';

		// Footer callout functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/footer-callout.php';

		// Footer functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/footer.php';

		// Footer bottom functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/footer-bottom.php';

		// Theme pagination helper functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/pagination.php';

		// Grid helper functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/grids.php';

		// Page functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/page.php';

		// Archive functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/archives.php';

		// Loop functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/loop.php';

		// Blog functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/blog.php';

		// Portfolio functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/portfolio.php';

		// Staff functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/staff.php';

		// Testimonials functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/testimonials.php';

		// Custom post type functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/cpt.php';

		// Search functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/search.php';

		// Star rating functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/star-rating.php';

		// User social link functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/user-social-links.php';

		// Define post format icons
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/post-format-icons.php';

		// Local scroll functions
		require_once WPEX_FRAMEWORK_DIR . 'functions/frontend/local-scroll.php';

		// Handle redirections
		require_once WPEX_FRAMEWORK_DIR . 'wp-actions/frontend/redirections.php';

		// Filter posts via pre_get_posts
		require_once WPEX_FRAMEWORK_DIR . 'wp-actions/frontend/pre-get-posts.php';

		// Enqueue site scripts
		require_once WPEX_FRAMEWORK_DIR . 'wp-actions/frontend/wp-enqueue-scripts.php';

		// Filter body classes
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/body-class.php';

		// Filter post classes
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/post-class.php';

		// Custom oembed output (adds responsive wrappers)
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/oembed.php';

		// Fix singular pagination bug in WP
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/singular-pagination-fix.php';

		// Move comment form fields back to how they were originally in WP
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/move-comment-form-fields.php';

		// Add schema markup for post author links
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/schema-author-posts-link.php';

		// Add local scroll to the comment scroll to link
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/comments-link-scrollto-fix.php';

		// Exclude items from next/previous posts such as link formats
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/next-previous-posts-exclude.php';

		// Customize the core WP password protection form
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/custom-password-protection-form.php';

		// Filter default WP tag cloud args
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/widget-tag-cloud-args.php';

		// Add custom classnames to various core WP widgets
		require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/widget-custom-classes.php';

		// Remove menu ID's for accessibility if enabled
		if ( get_theme_mod( 'remove_menu_ids', false ) && apply_filters( 'wpex_accessibility_panel', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/accessibility-remove-menu-ids.php';
		}

		// Remove site emoji scripts
		if ( get_theme_mod( 'remove_emoji_scripts_enable', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'wp-actions/frontend/remove-emoji-scripts.php';
		}

		// Add a span around the WordPress category widgets for easier styling
		if ( apply_filters( 'wpex_widget_counter_span', true ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'wp-filters/frontend/widget-add-span-to-count.php';
		}

		// Enable post thumbnail format icons
		if ( get_theme_mod( 'thumbnail_format_icons', false ) ) {
			require_once WPEX_FRAMEWORK_DIR . 'classes/frontend/class-thumbnail-format-icons.php';
		}

		// Load global fonts
		require_once WPEX_FRAMEWORK_DIR . 'classes/frontend/class-global-fonts.php';

		// Custom site backgrounds
		require_once WPEX_FRAMEWORK_DIR . 'classes/frontend/class-site-backgrouds.php';

		// Theme breadcrumbs
		require_once WPEX_FRAMEWORK_DIR . 'classes/frontend/class-wpex-breadcrumbs.php';

		// Outputs inline CSS for theme settings
		require_once WPEX_FRAMEWORK_DIR . 'classes/frontend/class-inline-css.php';

		// Preload assets
		require_once WPEX_FRAMEWORK_DIR . 'classes/frontend/class-preload-assets.php';

		/*** Maybe include ***/

			// Advanced styles for various Customizer options
			if ( apply_filters( 'wpex_generate_advanced_styles', true ) ) {
				require_once WPEX_FRAMEWORK_DIR . 'classes/frontend/class-advanced-styles.php';
			}

	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		add_action( 'after_setup_theme', array( $this, 'theme_setup' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'hooks_actions' ), 10 );
		add_filter( 'woocommerce_create_pages', '__return_empty_array' ); // prevent issues with importer.
	}

	/**
	 * Adds basic theme support functions and registers the nav menus.
	 */
	public function theme_setup() {

		// Load text domain
		load_theme_textdomain( 'total', WPEX_THEME_DIR . '/languages' );

		// Get globals
		global $content_width;

		// Set content width based on theme's default design
		if ( ! isset( $content_width ) ) {
			$content_width = 980;
		}

		// Register theme navigation menus
		register_nav_menus( array(
			'topbar_menu'     => esc_html__( 'Top Bar', 'total' ),
			'main_menu'       => esc_html__( 'Main/Header', 'total' ),
			'mobile_menu_alt' => esc_html__( 'Mobile Menu Alternative', 'total' ),
			'mobile_menu'     => esc_html__( 'Mobile Icons', 'total' ),
			'footer_menu'     => esc_html__( 'Footer', 'total' ),
		) );

		// Add support for head feed links
		add_theme_support( 'automatic-feed-links' );

		// Add support for post thumbnails (featured images)
		add_theme_support( 'post-thumbnails' );

		// Add support for the title tag
		add_theme_support( 'title-tag' );

		// Add support for Customizer widgets postMessage refresh
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for Gutenberg align-wide styles
		add_theme_support( 'align-wide' );

		// Add support for Gutenberg responsive media/embeds
		add_theme_support( 'responsive-embeds' );

		// Add support for various post formats (used on blog only)
		add_theme_support( 'post-formats', array(
			'video',
			'gallery',
			'audio',
			'quote',
			'link'
		) );

		// Add HTML5 support to various core WP elements
		add_theme_support( 'html5', array(
			'comment-list',
			'comment-form',
			'search-form',
			'gallery',
			'caption',
			'style',
			'script'
		) );

		// Add custom theme support for gutenberg editor.
		if ( ! class_exists( 'Classic_Editor' )
			&& ! ( WPEX_VC_ACTIVE && get_option( 'wpb_js_gutenberg_disable' ) )
		) {
			add_theme_support( 'gutenberg-editor' );
		}

		// Enable Custom Logo if the header customizer section isn't enabled
		if ( ! wpex_has_customizer_panel( 'header' ) ) {
			add_theme_support( 'custom-logo' );
		}

		// Enable excerpts for pages.
		add_post_type_support( 'page', 'excerpt' );

	}

	/**
	 * Defines all theme hooks and runs all needed actions for theme hooks.
	 */
	public function hooks_actions() {

		// Register theme hooks (needed in backend for actions panel)
		require_once WPEX_FRAMEWORK_DIR . 'hooks/hooks.php';

		// Add default theme actions
		require_once WPEX_FRAMEWORK_DIR . 'hooks/add-actions.php';

		// Remove actions and helper functions to remove all theme actions
		require_once WPEX_FRAMEWORK_DIR . 'hooks/remove-actions.php';

		// Functions used to return correct partial/template-parts
		require_once WPEX_FRAMEWORK_DIR . 'hooks/partials.php';

	}

}
WPEX_Theme_Setup::instance();