<?php
/**
 * Plugin Name: Templatera
 * Plugin URI: http://wpbakery.com/addons/templatera
 * Description: Template Manager for WPBakery Page Builder on Steroids
 * Version: 2.0.4
 * Author: WPBakery
 * Author URI: http://wpbakery.com
 * License: http://codecanyon.net/licenses
 * @package WPBakery
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
define( 'WPB_VC_REQUIRED_VERSION', '5.1' );

function templatera_notice() {
	echo sprintf( '<div class="updated"><p><strong>Templatera</strong> %s <strong><a href="http://bit.ly/vcomposer" target="_blank">WPBakery Page Builder</a></strong> %s</p></div>', esc_html__( 'requires', 'templatera' ), esc_html__( 'plugin to be installed and activated on your site.', 'templatera' ) );
}

function templatera_notice_version() {
	echo sprintf( '<div class="updated">
    <p><strong>Templatera</strong> %s <strong>%s</strong> %s <strong><a href="http://bit.ly/vcomposer" target="_blank">WPBakery Page Builder</a></strong> %s %s %s</p>
  </div>', esc_html__( 'requires', 'templatera' ), esc_html( WPB_VC_REQUIRED_VERSION ), esc_html__( 'version of', 'templatera' ), esc_html__( 'plugin to be installed and activated on your site.', 'templatera' ), esc_html__( 'Current version is', 'templatera' ), esc_html( WPB_VC_VERSION ) );
}

// Get directory path of this plugin.
$dir = dirname( __FILE__ );

// Template manager main class is required.
require_once $dir . '/lib/vc_template_manager.php';

/**
 * Registry hooks
 */

register_activation_hook( __FILE__, array(
	'VcTemplateManager',
	'install',
) );

add_action( 'init', 'templatera_init', 8 );

/**
 * Initialize Templatera with init action.
 */
function templatera_init() {
	// Display notice if WPBakery Page Builder is not installed or activated.
	if ( ! defined( 'WPB_VC_VERSION' ) ) {
		add_action( 'admin_notices', 'templatera_notice' );

		return;
	} elseif ( version_compare( WPB_VC_VERSION, WPB_VC_REQUIRED_VERSION ) < 0 ) {
		add_action( 'admin_notices', 'templatera_notice_version' );

		return;
	}

	if ( ! defined( 'WPB_VC_NEW_MENU_VERSION' ) ) {
		define( 'WPB_VC_NEW_MENU_VERSION', (bool) ( version_compare( '4.5', WPB_VC_VERSION ) <= 0 ) );
	}

	if ( ! defined( 'WPB_VC_NEW_USER_ACCESS_VERSION' ) ) {
		define( 'WPB_VC_NEW_USER_ACCESS_VERSION', (bool) ( version_compare( '4.8', WPB_VC_VERSION ) <= 0 ) );
	}

	if ( WPB_VC_NEW_MENU_VERSION ) {
		add_action( 'admin_head', 'wpb_templatera_menu_highlight' );
		add_action( 'vc_menu_page_build', 'wpb_templatera_add_submenu_page' );
	}

	add_filter( 'page_row_actions', 'wpb_templatera_render_row_action' );
	add_filter( 'post_row_actions', 'wpb_templatera_render_row_action' );

	$dir = dirname( __FILE__ );

	// Init or use instance of the manager.
	global $vc_template_manager;
	$vc_template_manager = new VcTemplateManager( $dir );
	$vc_template_manager->init();
}

/**
 * Add sub page to WPBakery Page Builder pages
 *
 * @since 1.2
 */
function wpb_templatera_add_submenu_page() {
	if ( ! WPB_VC_NEW_USER_ACCESS_VERSION || ( WPB_VC_NEW_USER_ACCESS_VERSION && vc_user_access()->part( 'templates' )->checkStateAny( true, null )->get() ) ) {
		$labels = VcTemplateManager::getPostTypesLabels();
		add_submenu_page( VC_PAGE_MAIN_SLUG, $labels['name'], $labels['name'], 'edit_posts', 'edit.php?post_type=' . rawurlencode( VcTemplateManager::postType() ), '' );
	}
}

/**
 * Highlight Vc submenu
 *
 * @since 1.2
 */
function wpb_templatera_menu_highlight() {
	global $parent_file, $submenu_file, $post_type;

	if ( VcTemplateManager::postType() === $post_type && ( ! defined( 'VC_IS_TEMPLATE_PREVIEW' ) || ! VC_IS_TEMPLATE_PREVIEW ) ) {
		$parent_file = VC_PAGE_MAIN_SLUG;
		$submenu_file = 'edit.php?post_type=' . rawurlencode( VcTemplateManager::postType() );
	}
}

/**
 * Add 'Export' link to each template
 *
 * @param $actions
 *
 * @return mixed
 */
function wpb_templatera_render_row_action( $actions ) {
	$post = get_post();

	if ( 'templatera' === get_post_type( $post->ID ) ) {
		$url = 'export.php?page=wpb_vc_settings&action=export_templatera&id=' . $post->ID;
		$actions['vc_export_template'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Export', 'templatera' ) . '</a>';
	}

	return $actions;
}
