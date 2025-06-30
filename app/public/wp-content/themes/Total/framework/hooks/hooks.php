<?php
/**
 * Setup theme hooks
 *
 * @package Total WordPress Theme
 * @subpackage Hooks
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Hooks List ]
/*-------------------------------------------------------------------------------*/

/**
 * Array of theme hooks
 *
 * @since 2.0.0
 */
function wpex_theme_hooks() {
	return array(
		'outer_wrap' => array(
			'label' => esc_html__( 'Outer Wrap', 'total' ),
			'hooks' => array(
				'wpex_outer_wrap_before',
				'wpex_outer_wrap_after',
			),
		),
		'wrap' => array(
			'label' => esc_html__( 'Wrap', 'total' ),
			'hooks' => array(
				'wpex_hook_wrap_before',
				'wpex_hook_wrap_top',
				'wpex_hook_wrap_bottom',
				'wpex_hook_wrap_after'
			),
		),
		'topbar' => array(
			'label' => esc_html__( 'Top Bar', 'total' ),
			'hooks' => array(
				'wpex_hook_topbar_before',
				'wpex_hook_topbar_inner',
				'wpex_hook_topbar_after',
				'wpex_hook_topbar_social_top',
				'wpex_hook_topbar_social_bottom',
			),
		),
		'header' => array(
			'label' => esc_html__( 'Header', 'total' ),
			'hooks' => array(
				'wpex_hook_header_before',
				'wpex_hook_header_top',
				'wpex_hook_header_inner',
				'wpex_hook_header_bottom',
				'wpex_hook_header_after',
			),
		),
		'header_logo' => array(
			'label' => esc_html__( 'Logo', 'total' ),
			'hooks' => array(
				'wpex_hook_site_logo_inner',
			),
		),
		'main_menu' => array(
			'label' => esc_html__( 'Main Menu', 'total' ),
			'hooks' => array(
				'wpex_hook_main_menu_before',
				'wpex_hook_main_menu_top',
				'wpex_hook_main_menu_bottom',
				'wpex_hook_main_menu_after',
				'wpex_mobile_menu_top',
				'wpex_mobile_menu_bottom',
			),
		),
		'main' => array(
			'label' => esc_html__( 'Main', 'total' ),
			'hooks' => array(
				'wpex_hook_main_before',
				'wpex_hook_main_top',
				'wpex_hook_main_bottom',
				'wpex_hook_main_after',
			),
		),
		'primary' => array(
			'label' => esc_html__( 'Primary Wrap', 'total' ),
			'hooks' => array(
				'wpex_hook_primary_before',
				'wpex_hook_primary_after',
			),
		),
		'content' => array(
			'label' => esc_html__( 'Content Wrap', 'total' ),
			'hooks' => array(
				'wpex_hook_content_before',
				'wpex_hook_content_top',
				'wpex_hook_content_bottom',
				'wpex_hook_content_after',
			),
		),
		'sidebar' => array(
			'label' => esc_html__( 'Sidebar', 'total' ),
			'hooks' => array(
				'wpex_hook_sidebar_before',
				'wpex_hook_sidebar_top',
				'wpex_hook_sidebar_inner',
				'wpex_hook_sidebar_bottom',
				'wpex_hook_sidebar_after',
			),
		),
		'footer' => array(
			'label' => esc_html__( 'Footer', 'total' ),
			'hooks' => array(
				'wpex_hook_footer_before',
				'wpex_hook_footer_top',
				'wpex_hook_footer_inner',
				'wpex_hook_footer_widgets_top',
				'wpex_hook_footer_widgets_bottom',
				'wpex_hook_footer_bottom',
				'wpex_hook_footer_after',
			),
		),
		'footer_bottom' => array(
			'label' => esc_html__( 'Footer Bottom', 'total' ),
			'hooks' => array(
				'wpex_hook_footer_bottom_before',
				'wpex_hook_footer_bottom_top',
				'wpex_hook_footer_bottom_inner',
				'wpex_hook_footer_bottom_bottom',
				'wpex_hook_footer_bottom_after',
			),
		),
		'page_header' => array(
			'label' => esc_html__( 'Page Header', 'total' ),
			'hooks' => array(
				'wpex_hook_page_header_before',
				'wpex_hook_page_header_top',
				'wpex_hook_page_header_inner',
				'wpex_hook_page_header_content',
				'wpex_hook_page_header_aside',
				'wpex_hook_page_header_bottom',
				'wpex_hook_page_header_after',
			),
		),
		'page_header_title' => array(
			'label' => esc_html__( 'Page Header Title', 'total' ),
			'hooks' => array(
				'wpex_hook_page_header_title_before',
				'wpex_hook_page_header_title_after',
			),
		),
		'mobile_menu' => array(
			'label' => esc_html__( 'Mobile Menu', 'total' ),
			'hooks' => array(
				'wpex_hook_mobile_menu_top',
				'wpex_hook_mobile_menu_bottom',
			),
		),
		'mobile_menu_toggle' => array(
			'label' => esc_html__( 'Mobile Menu Toggle', 'total' ),
			'hooks' => array(
				'wpex_hook_mobile_menu_toggle_top',
				'wpex_hook_mobile_menu_toggle_bottom',
			),
		),
	);
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: Body ]
/*-------------------------------------------------------------------------------*/
function wpex_hook_after_body_tag() {
	do_action( 'wpex_hook_after_body_tag' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Outer Wrap ]
/*-------------------------------------------------------------------------------*/

function wpex_outer_wrap_before() {
	do_action( 'wpex_outer_wrap_before' );
}

function wpex_outer_wrap_after() {
	do_action( 'wpex_outer_wrap_after' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Topbar ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_topbar_before() {
	do_action( 'wpex_hook_topbar_before' );
}

function wpex_hook_topbar_inner() {
	do_action( 'wpex_hook_topbar_inner' );
}

function wpex_hook_topbar_after() {
	do_action( 'wpex_hook_topbar_after' );
}

function wpex_hook_topbar_social_top() {
	do_action( 'wpex_hook_topbar_social_top' );
}

function wpex_hook_topbar_social_bottom() {
	do_action( 'wpex_hook_topbar_social_bottom' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Header ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_header_before() {
	do_action( 'wpex_hook_header_before' );
}

function wpex_hook_header_top() {
	do_action( 'wpex_hook_header_top' );
}

function wpex_hook_header_inner() {
	do_action( 'wpex_hook_header_inner' );
}

function wpex_hook_header_bottom() {
	do_action( 'wpex_hook_header_bottom' );
}

function wpex_hook_header_after() {
	do_action( 'wpex_hook_header_after' );
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: Mobile Menu ]
/*-------------------------------------------------------------------------------*/
function wpex_hook_mobile_menu_top() {
	do_action( 'wpex_mobile_menu_top' ); // legacy pre v5.0
	do_action( 'wpex_hook_mobile_menu_top' );
}

function wpex_hook_mobile_menu_bottom() {
	do_action( 'wpex_mobile_menu_bottom' ); // legacy pre v5.0
	do_action( 'wpex_hook_mobile_menu_bottom' );
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: Mobile Menu Toggle ]
/*-------------------------------------------------------------------------------*/
function wpex_hook_mobile_menu_toggle_top() {
	do_action( 'wpex_hook_mobile_menu_toggle_top' );
}

function wpex_hook_mobile_menu_toggle_bottom() {
	do_action( 'wpex_hook_mobile_menu_toggle_bottom' );
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: Logo ]
/*-------------------------------------------------------------------------------*/
function wpex_hook_site_logo_inner() {
	do_action( 'wpex_hook_site_logo_inner' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Wrap ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_wrap_before() {
	do_action( 'wpex_hook_wrap_before' );
}

function wpex_hook_wrap_top() {
	do_action( 'wpex_hook_wrap_top' );
}

function wpex_hook_wrap_bottom() {
	do_action( 'wpex_hook_wrap_bottom' );
}

function wpex_hook_wrap_after() {
	do_action( 'wpex_hook_wrap_after' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Main ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_main_before() {
	do_action( 'wpex_hook_main_before' );
}

function wpex_hook_main_top() {
	do_action( 'wpex_hook_main_top' );
}

function wpex_hook_main_bottom() {
	do_action( 'wpex_hook_main_bottom' );
}

function wpex_hook_main_after() {
	do_action( 'wpex_hook_main_after' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Primary ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_primary_before() {
	do_action( 'wpex_hook_primary_before' );
}

function wpex_hook_primary_after() {
	do_action( 'wpex_hook_primary_after' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Content ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_content_before() {
	do_action( 'wpex_hook_content_before' );
}

function wpex_hook_content_top() {
	do_action( 'wpex_hook_content_top' );
}

function wpex_hook_content_bottom() {
	do_action( 'wpex_hook_content_bottom' );
}

function wpex_hook_content_after() {
	do_action( 'wpex_hook_content_after' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Sidebar ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_sidebar_before() {
	do_action( 'wpex_hook_sidebar_before' );
}

function wpex_hook_sidebar_after() {
	do_action( 'wpex_hook_sidebar_after' );
}

function wpex_hook_sidebar_top() {
	do_action( 'wpex_hook_sidebar_top' );
}

function wpex_hook_sidebar_bottom() {
	do_action( 'wpex_hook_sidebar_bottom' );
}

function wpex_hook_sidebar_inner() {
	do_action( 'wpex_hook_sidebar_inner' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Footer ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_footer_before() {
	do_action( 'wpex_hook_footer_before' );
}

function wpex_hook_footer_top() {
	do_action( 'wpex_hook_footer_top' );
}

function wpex_hook_footer_inner() {
	do_action( 'wpex_hook_footer_inner' );
}

function wpex_hook_footer_bottom() {
	do_action( 'wpex_hook_footer_bottom' );
}

function wpex_hook_footer_after() {
	do_action( 'wpex_hook_footer_after' );
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: Footer Bottom ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_footer_bottom_before() {
	do_action( 'wpex_hook_footer_bottom_before' );
}

function wpex_hook_footer_bottom_top() {
	do_action( 'wpex_hook_footer_bottom_top' );
}

function wpex_hook_footer_bottom_inner() {
	do_action( 'wpex_hook_footer_bottom_inner' );
}

function wpex_hook_footer_bottom_bottom() {
	do_action( 'wpex_hook_footer_bottom_bottom' );
}

function wpex_hook_footer_bottom_after() {
	do_action( 'wpex_hook_footer_bottom_after' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Main Menu ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_main_menu_before() {
	do_action( 'wpex_hook_main_menu_before' );
}

function wpex_hook_main_menu_top() {
	do_action( 'wpex_hook_main_menu_top' );
}

function wpex_hook_main_menu_bottom() {
	do_action( 'wpex_hook_main_menu_bottom' );
}

function wpex_hook_main_menu_after() {
	do_action( 'wpex_hook_main_menu_after' );
}


/*-------------------------------------------------------------------------------*/
/* [ Hooks: Page Header ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_page_header_before() {
	do_action( 'wpex_hook_page_header_before' );
}

function wpex_hook_page_header_top() {
	do_action( 'wpex_hook_page_header_top' );
}

function wpex_hook_page_header_inner() {
	do_action( 'wpex_hook_page_header_inner' );
}

function wpex_hook_page_header_content() {
	do_action( 'wpex_hook_page_header_content' );
}

function wpex_hook_page_header_aside() {
	do_action( 'wpex_hook_page_header_aside' );
}

function wpex_hook_page_header_bottom() {
	do_action( 'wpex_hook_page_header_bottom' );
}

function wpex_hook_page_header_after() {
	do_action( 'wpex_hook_page_header_after' );
}

function wpex_hook_page_header_title_before() {
	do_action( 'wpex_hook_page_header_title_before' );
}

function wpex_hook_page_header_title_after() {
	do_action( 'wpex_hook_page_header_title_after' );
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: Archive Loop ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_archive_loop_before_entry() {
	do_action( 'wpex_hook_archive_loop_before_entry' );
}

function wpex_hook_archive_loop_after_entry() {
	do_action( 'wpex_hook_archive_loop_after_entry' );
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: Header Search Overlay ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_header_search_overlay_top() {
	do_action( 'wpex_hook_header_search_overlay_top' );
}

function wpex_hook_header_search_overlay_bottom() {
	do_action( 'wpex_hook_header_search_overlay_bottom' );
}

/*-------------------------------------------------------------------------------*/
/* [ Hooks: WPBakery ]
/*-------------------------------------------------------------------------------*/

function wpex_hook_vc_row_top( $atts = '' ) {
	return apply_filters( 'wpex_hook_vc_row_top', '', $atts );
}

function wpex_hook_vc_row_bottom( $atts = '' ) {
	return apply_filters( 'wpex_hook_vc_row_bottom', '', $atts );
}

function wpex_hook_vc_section_bottom( $atts = '' ) {
	return apply_filters( 'wpex_hook_vc_section_bottom', '', $atts );
}

function wpex_hook_vc_column_inner_bottom( $atts = '' ) {
	return apply_filters( 'wpex_hook_vc_column_inner_bottom', '', $atts );
}
