<?php
/**
 * Total Template Actions.
 *
 * Action/filter hooks used for theme functions/templates.
 *
 * @package Total WordPress Theme
 * @subpackage Hooks
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*

	# Head
	# Outer Wrap
	# Wrap
	# Topbar
	# Header
	# Header Logo
	# Main
	# Primary
	# Content
	# Page Headerf
	# Footer
	# Footer Bottom

/*-------------------------------------------------------------------------------*/
/* [ Head ]
/*-------------------------------------------------------------------------------*/
add_action( 'wp_head', 'wpex_google_analytics_tag', 0 );

/*-------------------------------------------------------------------------------*/
/* [ Outer Wrap ]
/*-------------------------------------------------------------------------------*/

// Outer Wrap > Before
add_action( 'wpex_outer_wrap_before', 'wpex_site_overlay', 10 );
add_action( 'wpex_outer_wrap_before', 'wpex_site_frame_border', 0 );
add_action( 'wpex_outer_wrap_before', 'wpex_skip_to_content_link', 10 );
add_action( 'wpex_outer_wrap_before', 'wpex_ls_top', 10 );
add_action( 'wpex_outer_wrap_before', 'wpex_toggle_bar_button', 10 );
add_action( 'wpex_outer_wrap_before', 'wpex_toggle_bar', 10 );
add_action( 'wpex_outer_wrap_before', 'wpex_mobile_menu_navbar', 10 );
add_action( 'wpex_outer_wrap_before', 'wpex_mobile_menu_fixed_top', 10 );

// Outer Wrap > After
add_action( 'wpex_outer_wrap_after', 'wpex_mobile_menu_alt', 10 );
add_action( 'wpex_outer_wrap_after', 'wpex_mobile_searchform', 10 );
add_action( 'wpex_outer_wrap_after', 'wpex_mobile_menu_extras', 10 );
add_action( 'wpex_outer_wrap_after', 'wpex_scroll_top', 10 );
add_action( 'wpex_outer_wrap_after', 'wpex_search_overlay', 10 );

/*-------------------------------------------------------------------------------*/
/* [ Wrap ]
/*-------------------------------------------------------------------------------*/

// Wrap > Top
add_action( 'wpex_hook_wrap_top', 'wpex_top_bar', 5 );
add_action( 'wpex_hook_wrap_top', 'wpex_header', 10 );

// Wrap > Bottom
add_action( 'wpex_hook_wrap_bottom', 'wpex_footer', 10 );


/*-------------------------------------------------------------------------------*/
/* [ Topbar ]
/*-------------------------------------------------------------------------------*/

// Topbar > Before
add_action( 'wpex_hook_topbar_before', 'wpex_post_slider', 10 );
add_action( 'wpex_hook_topbar_inner', 'wpex_topbar_inner', 10 );

/*-------------------------------------------------------------------------------*/
/* [ Header ]
/*-------------------------------------------------------------------------------*/

// Header > before
add_action( 'wpex_hook_header_before', 'wpex_post_slider', 10 );
add_action( 'wpex_hook_header_before', 'wpex_overlay_header_wrap_open', 9999 );

// Header > Top
add_action( 'wpex_hook_header_top', 'wpex_header_menu', 10 );

// Header > Inner
add_action( 'wpex_hook_header_inner', 'wpex_header_logo', 10 );
add_action( 'wpex_hook_header_inner', 'wpex_header_aside', 10 );
add_action( 'wpex_hook_header_inner', 'wpex_header_menu', 10 );
add_action( 'wpex_hook_header_inner', 'wpex_mobile_menu_icons', 10 );
add_action( 'wpex_hook_header_inner', 'wpex_header_inner_search_dropdown', 10 );
add_action( 'wpex_hook_header_inner', 'wpex_search_header_replace', 99 );

// Header > Bottom
add_action( 'wpex_hook_header_bottom', 'wpex_post_slider', 10 );
add_action( 'wpex_hook_header_bottom', 'wpex_header_menu', 10 );
add_action( 'wpex_hook_header_bottom', 'wpex_mobile_menu_navbar', 10 );

// Header > After
add_action( 'wpex_hook_header_after', 'wpex_overlay_header_template', 0 );
add_action( 'wpex_hook_header_after', 'wpex_display_breadcrumbs', 10 );


/*-------------------------------------------------------------------------------*/
/* [ Header Logo ]
/*-------------------------------------------------------------------------------*/
add_action( 'wpex_hook_site_logo_inner', 'wpex_header_logo_inner', 10 );


/*-------------------------------------------------------------------------------*/
/* [ Main ]
/*-------------------------------------------------------------------------------*/

// Main > Top
add_action( 'wpex_hook_main_top', 'wpex_page_header', 10 );
add_action( 'wpex_hook_main_top', 'wpex_post_slider', 10 );

// Main > Bottom
add_action( 'wpex_hook_main_bottom', 'wpex_next_prev', 10 );

// Main > After
add_action( 'wpex_hook_main_after', 'wpex_overlay_header_wrap_close', 9999 );


/*-------------------------------------------------------------------------------*/
/* [ Primary ]
/*-------------------------------------------------------------------------------*/

// Primary > Before
add_action( 'wpex_hook_primary_before', 'wpex_blog_single_media_above', 10 );

// Primary > After
add_action( 'wpex_hook_primary_after', 'wpex_get_sidebar_template', 10 );


/*-------------------------------------------------------------------------------*/
/* [ Content ]
/*-------------------------------------------------------------------------------*/

// Content > Top
add_action( 'wpex_hook_content_top', 'wpex_term_description', 10 );

// Content > Bottom
add_action( 'wpex_hook_content_bottom', 'wpex_post_edit', 10 );


/*-------------------------------------------------------------------------------*/
/* [ Page Header ]
/*-------------------------------------------------------------------------------*/

// Page Header > Before
add_action( 'wpex_hook_page_header_before', 'wpex_post_slider', 10 );

// Page Header > Top
add_action( 'wpex_hook_page_header_top', 'wpex_page_header_overlay', 0 );

// Page Header > Inner
add_action( 'wpex_hook_page_header_inner', 'wpex_page_header_content', 10 );
add_action( 'wpex_hook_page_header_inner', 'wpex_page_header_aside', 10 );

// Page Header > Content
add_action( 'wpex_hook_page_header_content', 'wpex_page_header_title', 10 );
add_action( 'wpex_hook_page_header_content', 'wpex_page_header_subheading', 10 );
add_action( 'wpex_hook_page_header_content', 'wpex_display_breadcrumbs', 20 );

// Page Header > Aside
add_action( 'wpex_hook_page_header_aside', 'wpex_page_header_subheading', 10 );
add_action( 'wpex_hook_page_header_aside', 'wpex_display_breadcrumbs', 20 );

// Page Header > After
add_action( 'wpex_hook_page_header_after', 'wpex_display_breadcrumbs', 0 );


/*-------------------------------------------------------------------------------*/
/* [ Sidebar ]
/*-------------------------------------------------------------------------------*/
add_action( 'wpex_hook_sidebar_inner', 'wpex_display_sidebar', 10 );


/*-------------------------------------------------------------------------------*/
/* [ Footer ]
/*-------------------------------------------------------------------------------*/

// Footer > Before
add_action( 'wpex_hook_footer_before', 'wpex_footer_reveal_open', 0 );
add_action( 'wpex_hook_footer_before', 'wpex_footer_callout', 10 );

// Footer > Inner
add_action( 'wpex_hook_footer_inner', 'wpex_footer_widgets', 10 );

// Footer > After
add_action( 'wpex_hook_footer_after', 'wpex_footer_bottom', 10 );
add_action( 'wpex_hook_footer_after', 'wpex_footer_reveal_close', 99 );


/*-------------------------------------------------------------------------------*/
/* [ Footer Bottom ]
/*-------------------------------------------------------------------------------*/
add_action( 'wpex_hook_footer_bottom_inner', 'wpex_footer_bottom_flex_open', 5 );
add_action( 'wpex_hook_footer_bottom_inner', 'wpex_footer_bottom_copyright', 10 );
add_action( 'wpex_hook_footer_bottom_inner', 'wpex_footer_bottom_menu', 10 );
add_action( 'wpex_hook_footer_bottom_inner', 'wpex_footer_bottom_flex_close', 15 );


