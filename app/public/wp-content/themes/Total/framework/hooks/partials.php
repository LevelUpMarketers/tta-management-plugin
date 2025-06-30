<?php
/**
 * These functions are used to load template parts (partials) when used within action hooks,
 * and they probably should never be updated or modified.
 *
 * @package Total WordPress Theme
 * @subpackage Hooks
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*

	# Accessibility
	# Toggle Bar
	# Top Bar
	# Header
	# Menu
	# Mobile Menu
	# Page Header
	# Sidebar
	# Blog
	# Footer
	# Footer Bottom
	# Archive Loop
	# Other

/*-------------------------------------------------------------------------------*/
/* -  Accessibility
/*-------------------------------------------------------------------------------*/

/**
 * Get skip to content link.
 *
 * @since 4.2
 */
function wpex_skip_to_content_link() {
	if ( get_theme_mod( 'skip_to_content', true ) ) {
		get_template_part( 'partials/accessibility/skip-to-content' );
	}
}

/*-------------------------------------------------------------------------------*/
/* -  Toggle Bar
/*-------------------------------------------------------------------------------*/

/**
 * Get togglebar layout template part if enabled.
 *
 * @since 1.0.0
 * @todo rename to wpex_togglebar for consistency?
 */
function wpex_toggle_bar() {
	if ( wpex_has_togglebar() ) {
		wpex_get_template_part( 'togglebar' );
	}
}

/**
 * Get togglebar button template part.
 *
 * @since 1.0.0
 * @todo rename to wpex_togglebar_button for consistency?
 */
function wpex_toggle_bar_button() {
	if ( wpex_has_togglebar() ) {
		wpex_get_template_part( 'togglebar_button' );
	}
}

/*-------------------------------------------------------------------------------*/
/* -  Top Bar
/*-------------------------------------------------------------------------------*/

/**
 * Get Top Bar layout template part if enabled.
 *
 * @since 1.0.0
 * @todo rename to wpex_topbar for consistency?
 */
function wpex_top_bar() {
	if ( wpex_has_topbar() ) {
		wpex_get_template_part( 'topbar' );
	}
}

/**
 * Get topbar innercontent.
 *
 * @since 5.0
 */
function wpex_topbar_inner() {

	if ( 'two' === wpex_topbar_style() ) {
		wpex_topbar_social();
		wpex_tobar_content();
	} else {
		wpex_tobar_content();
		wpex_topbar_social();
	}
}

/**
 * Get topbar content.
 *
 * @since 3.6.0
 */
function wpex_tobar_content() {
	wpex_get_template_part( 'topbar_content' );
}

/**
 * Get topbar social.
 *
 * @since 3.6.0
 */
function wpex_topbar_social() {
	wpex_get_template_part( 'topbar_social' );
}

/*-------------------------------------------------------------------------------*/
/* -  Header
/*-------------------------------------------------------------------------------*/

/**
 * Get the header template part if enabled.
 *
 * @since 1.5.3
 */
function wpex_header() {
	if ( wpex_has_header() ) {
		wpex_get_template_part( 'header' );
	}
}

/**
 * Get the header logo template part.
 *
 * @since 1.0.0
 */
function wpex_header_logo() {
	wpex_get_template_part( 'header_logo' );
}

/**
 * Get the header logo inner content.
 *
 * @since 4.5.5
 */
function wpex_header_logo_inner() {
	wpex_get_template_part( 'header_logo_inner' );
}

/**
 * Get the header aside content template part.
 *
 * @since 1.5.3
 */
function wpex_header_aside() {
	if ( wpex_header_supports_aside() ) {
		wpex_get_template_part( 'header_aside' );
	}
}

/**
 * Add search dropdown to header inner.
 *
 * @since 4.5.4
 */
function wpex_header_inner_search_dropdown() {

	// Make sure site is set to dropdown style
	if ( 'drop_down' != wpex_header_menu_search_style() ) {
		return;
	}

	// Only added in the header for certain styles
	if ( ! in_array( wpex_header_style(), array( 'one' ) ) ) {
		return;
	}

	// Get template part
	wpex_get_template_part( 'header_search_dropdown' );

}

/**
 * Get header search dropdown template part.
 *
 * @since 1.0.0
 * @deprecated 4.5.4
 */
function wpex_search_dropdown() {
	wpex_get_template_part( 'header_search_dropdown' );
}

/**
 * Get header search replace template part.
 *
 * @since 1.0.0
 */
function wpex_search_header_replace() {
	if ( 'header_replace' == wpex_header_menu_search_style() ) {
		wpex_get_template_part( 'header_search_replace' );
	}
}

/**
 * Gets header search overlay template part.
 *
 * @since 1.0.0
 */
function wpex_search_overlay() {
	if ( 'overlay' == wpex_header_menu_search_style() ) {
		wpex_get_template_part( 'header_search_overlay' );
	}
}

/**
 * Overlay Header Wrap Open.
 *
 * @since 3.2.0
 */
function wpex_overlay_header_wrap_open() {
	if ( wpex_has_overlay_header() ) {
		echo '<div id="overlay-header-wrap" class="wpex-clr">';
	}
}

/**
 * Overlay Header Wrap Close.
 *
 * @since 3.2.0
 */
function wpex_overlay_header_wrap_close() {
	if ( wpex_has_overlay_header() ) {
		echo '</div>';
	}
}

/**
 * Overlay Header Template
 *
 * @since 3.2.0
 */
function wpex_overlay_header_template() {

	if ( ! wpex_has_overlay_header() ) {
		return;
	}

	$template = apply_filters( 'wpex_overlay_header_template', get_theme_mod( 'overlay_header_template' ) );

	if ( ! $template ) {
		return;
	}

	$template_post = get_post( $template );

	if ( ! $template_post ) {
		return;
	}

	$template_content_escaped = wpex_sanitize_template_content( $template_post->post_content );

	if ( $template_content_escaped ) {

		echo '<div class="overlay-header-template"><div class="container wpex-clr">' . $template_content_escaped . '</div></div>';

	}

}

/*-------------------------------------------------------------------------------*/
/* -  Menu
/*-------------------------------------------------------------------------------*/

/**
 * Outputs the main header menu.
 *
 * @since 1.0.0
 */
function wpex_header_menu() {

	if ( false === wpex_has_header_menu() ) {
		return;
	}

	$get            = false;
	$header_style   = wpex_header_style();
	$current_filter = current_filter();

	switch ( $current_filter ) {

		case 'wpex_hook_header_inner':

			if ( 'one' == $header_style
				|| 'five' == $header_style
				|| 'six' == $header_style
				|| 'dev' == $header_style
				|| 'flex' == $header_style
			) {
				$get = true;
			}

			break;

		case 'wpex_hook_header_top':

			if ( 'four' == $header_style ) {
				$get = true;
			}

			break;

		case 'wpex_hook_header_bottom':

			if ( 'two' == $header_style || 'three' == $header_style ) {
				$get = true;
			}

			break;


	}

	if ( $get ) {
		wpex_get_template_part( 'header_menu' );
	}

}

/*-------------------------------------------------------------------------------*/
/* -  Menu > Mobile
/*-------------------------------------------------------------------------------*/

/**
 * Gets the template part for the fixed top mobile menu style.
 *
 * @since 3.0.0
 */
function wpex_mobile_menu_fixed_top() {
	if ( wpex_has_header_mobile_menu() && 'fixed_top' == wpex_header_menu_mobile_toggle_style() ) {
		wpex_get_template_part( 'header_mobile_menu_fixed_top' );
	}
}

/**
 * Gets the template part for the navbar mobile menu_style.
 *
 * @since 3.0.0
 */
function wpex_mobile_menu_navbar() {

	if ( ! wpex_has_header_mobile_menu() ) {
		return;
	}

	if ( 'navbar' !== wpex_header_menu_mobile_toggle_style() ) {
		return;
	}

	$get = false;
	$current_filter = current_filter();

	if ( 'outer_wrap_before' === get_theme_mod( 'mobile_menu_navbar_position' ) ) {
		$before_wrap = true;
	} else {
		$before_wrap = (bool) wpex_has_overlay_header(); // force before_wrap position for overlay header
	}

	switch ( $current_filter ) {

		case 'wpex_outer_wrap_before':

			if ( $before_wrap ) {
				$get = true;
			}

			break;

		case 'wpex_hook_header_bottom':

			if ( ! $before_wrap ) {
				$get = true;
			}

			break;

	}

	if ( $get ) {
		wpex_get_template_part( 'header_mobile_menu_navbar' );
	}

}

/**
 * Gets the template part for the "icons" style mobile menu.
 *
 * @since 1.0.0
 */
function wpex_mobile_menu_icons() {

	if ( ! in_array( wpex_header_menu_mobile_toggle_style(), array( 'icon_buttons', 'icon_buttons_under_logo' ) ) ) {
		return;
	}

	if ( ! wpex_has_header_mobile_menu() ) {
		return;
	}

	wpex_get_template_part( 'header_mobile_menu_icons' );

}

/**
 * Get mobile menu alternative if enabled.
 *
 * @since 1.3.0
 */
function wpex_mobile_menu_alt() {
	if ( wpex_has_mobile_menu_alt() ) {
		wpex_get_template_part( 'header_mobile_menu_alt' );
	}
}

/**
 * Mobile Menu Extras.
 *
 * @since 4.9.8
 */
function wpex_mobile_menu_extras() {
	wpex_get_template_part( 'header_mobile_menu_extras' );
}

/*-------------------------------------------------------------------------------*/
/* -  Page Header
/*-------------------------------------------------------------------------------*/

/**
 * Get page header template part if enabled.
 *
 * @since 1.5.2
 */
function wpex_page_header() {
	if ( wpex_has_page_header() && ! wpex_theme_do_location( 'page_header' ) ) {
		wpex_get_template_part( 'page_header' );
	}
}

/**
 * Get page header content template part.
 *
 * @since 5.0
 */
function wpex_page_header_content() {
	if ( has_action( 'wpex_hook_page_header_content' ) ) {
		wpex_get_template_part( 'page_header_content' );
	}
}

/**
 * Get page header aside template part.
 *
 * @since 5.0
 */
function wpex_page_header_aside() {
	if ( has_action( 'wpex_hook_page_header_aside' ) ) {
		wpex_get_template_part( 'page_header_aside' );
	}
}

/**
 * Get page header title template part if enabled.
 *
 * @since 1.0.0
 */
function wpex_page_header_title() {
	if ( wpex_has_page_header_title() ) {
		wpex_get_template_part( 'page_header_title' );
	}
}

/**
 * Get post heading template part.
 *
 * @since 1.0.0
 */
function wpex_page_header_subheading() {

	if ( ! wpex_page_header_has_subheading() ) {
		return;
	}

	$get = false;

	$current_filter = current_filter();
	$location       = wpex_get_mod( 'page_header_subheading_location', 'page_header_content', true );

	if ( $current_filter == 'wpex_hook_' . $location ) {
		$get = true;
	}

	if ( $get ) {
		wpex_get_template_part( 'page_header_subheading' );
	}

}

/**
 * Get breadcrumbs.
 *
 * @since 1.0.0
 */
function wpex_display_breadcrumbs() {
	$get = false;

	if ( ! wpex_has_breadcrumbs() ) {
		remove_action( 'wpex_hook_page_header_aside', 'wpex_display_breadcrumbs' );
		return;
	}

	$current_filter = current_filter();
	$position       = wpex_breadcrumbs_position();

	if ( $current_filter == 'wpex_hook_' . $position ) {
		$get = true;
	}

	if ( 'page_header_aside' !== $position ) {
		remove_action( 'wpex_hook_page_header_aside', 'wpex_display_breadcrumbs' ); // prevent empty aside container
	}

	if ( $get ) {
		wpex_get_template_part( 'breadcrumbs' );
	}

}

/*-------------------------------------------------------------------------------*/
/* -  Sidebar
/*-------------------------------------------------------------------------------*/

/**
 * Gets sidebar template.
 *
 * @since 2.1.0
 */
function wpex_get_sidebar_template() {
	if ( ! in_array( wpex_content_area_layout(), array( 'full-screen', 'full-width' ) ) ) {
		get_sidebar( apply_filters( 'wpex_get_sidebar_template', null ) );
	}
}

/**
 * Displays correct sidebar.
 *
 * @since 1.6.5
 */
function wpex_display_sidebar() {
	if ( wpex_has_sidebar() && $sidebar = wpex_get_sidebar() ) {
		dynamic_sidebar( $sidebar );
	}
}

/*-------------------------------------------------------------------------------*/
/* -  Blog
/*-------------------------------------------------------------------------------*/

/**
 * Blog single media above content.
 *
 * @since 1.0.0
 */
function wpex_blog_single_media_above() {

	// Only needed for blog posts
	if ( ! is_singular() ) {
		return;
	}

	// Blog media position
	$position = apply_filters( 'wpex_blog_single_media_position', wpex_get_custom_post_media_position() );

	// Display the post media above the post (this is a meta option)
	if ( 'above' == $position && ! post_password_required() ) {

		// Standard posts
		if ( 'post' == get_post_type() ) {

			// Get correct media template part
			wpex_get_template_part( 'blog_single_media', get_post_format() );

		}

		// Other post types
		else {

			wpex_get_template_part( 'cpt_single_media' );

		}

	}

}

/*-------------------------------------------------------------------------------*/
/* -  Footer
/*-------------------------------------------------------------------------------*/

/**
 * Gets the footer callout template part.
 *
 * @since 1.0.0
 */
function wpex_footer_callout() {
	if ( wpex_has_callout() && ! wpex_theme_do_location( 'footer_callout' ) ) {
		wpex_get_template_part( 'footer_callout' );
	}
}

/**
 * Gets the footer layout template part.
 *
 * @since 2.0.0
 */
function wpex_footer() {
	if ( wpex_has_footer() ) {
		wpex_get_template_part( 'footer' );
	}
}

/**
 * Get the footer widgets template part.
 *
 * @since 1.0.0
 */
function wpex_footer_widgets() {
	wpex_get_template_part( 'footer_widgets' );
}

/**
 * Gets the footer bottom template part.
 *
 * @since 1.0.0
 */
function wpex_footer_bottom() {
	if ( wpex_has_footer_bottom() ) {
		wpex_get_template_part( 'footer_bottom' );
	}
}

/**
 * Gets the scroll to top button template part.
 *
 * @since 1.0.0
 */
function wpex_scroll_top() {
	if ( get_theme_mod( 'scroll_top', true ) ) {
		wpex_get_template_part( 'scroll_top' );
	}
}

/**
 * Footer reaveal open code.
 *
 * @since 2.0.0
 */
function wpex_footer_reveal_open() {
	if ( wpex_has_footer_reveal() ) {
		wpex_get_template_part( 'footer_reveal_open' );
	}
}

/**
 * Footer reaveal close code.
 *
 * @since 2.0.0
 */
function wpex_footer_reveal_close() {
	if ( wpex_has_footer_reveal() ) {
		wpex_get_template_part( 'footer_reveal_close' );
	}
}

/**
 * Site Frame Border.
 *
 * @since 2.0.0
 */
function wpex_site_frame_border() {

	if ( wpex_has_site_frame_border() || is_customize_preview() ) { ?>

		<div id="wpex-sfb-l" class="wpex-bg-accent wpex-fixed wpex-z-1002 wpex-inset-y-0 wpex-left-0"></div>
		<div id="wpex-sfb-r" class="wpex-bg-accent wpex-fixed wpex-z-1002 wpex-inset-y-0 wpex-right-0"></div>
		<div id="wpex-sfb-t" class="wpex-bg-accent wpex-fixed wpex-z-1002 wpex-inset-x-0 wpex-top-0"></div>
		<div id="wpex-sfb-b" class="wpex-bg-accent wpex-fixed wpex-z-1002 wpex-inset-x-0 wpex-bottom-0"></div>

	<?php }
}

/*-------------------------------------------------------------------------------*/
/* -  Footer Bottom
/*-------------------------------------------------------------------------------*/

/**
 * Footer bottom flex box open.
 *
 * @since 4.9.3
 */
function wpex_footer_bottom_flex_open() {
	$align = get_theme_mod( 'bottom_footer_text_align' );
	if ( ! $align || ! in_array( $align, array( 'left', 'center', 'right' ) ) ) {
		$class = 'footer-bottom-flex wpex-md-flex wpex-md-justify-between wpex-md-items-center';
	} else {
		$class = 'footer-bottom-flex wpex-clr';
	}
	echo '<div class="' . esc_attr( $class ) . '">';
}

/**
 * Footer bottom flex box close.
 *
 * @since 4.9.3
 */
function wpex_footer_bottom_flex_close() {
	echo '</div>';
}

/**
 * Footer bottom copyright.
 *
 * @since 2.0.0
 */
function wpex_footer_bottom_copyright() {
	wpex_get_template_part( 'footer_bottom_copyright' );
}

/**
 * Footer bottom menu.
 *
 * @since 2.0.0
 */
function wpex_footer_bottom_menu() {
	wpex_get_template_part( 'footer_bottom_menu' );
}

/*-------------------------------------------------------------------------------*/
/* -  Other
/*-------------------------------------------------------------------------------*/

/**
 * Get term description.
 *
 * @since 1.0.0
 */
function wpex_term_description() {

	if ( ! is_tax() && ! is_category() && ! is_tag() ) {
		return;
	}

	$current_filter = current_filter();

	switch ( $current_filter ) {
		case 'wpex_hook_content_top':
			$get = wpex_has_term_description_above_loop();
			break;
		default:
			$get = true;
			break;
	}

	if ( $get ) {
		wpex_get_template_part( 'term_description' );
	}

}

/**
 * Get next/previous links.
 *
 * @since 1.0.0
 */
function wpex_next_prev() {
	if ( wpex_has_next_prev() ) {
		wpex_get_template_part( 'next_prev' );
	}
}

/**
 * Get next/previous links.
 *
 * @since 1.0.0
 */
function wpex_post_edit() {
	if ( wpex_has_post_edit() ) {
		wpex_get_template_part( 'post_edit' );
	}
}

/**
 * Site Overlay.
 *
 * @since 3.4.0
 */
function wpex_site_overlay() {
	echo '<div class="wpex-site-overlay"></div>';
}

/**
 * Site Top div.
 *
 * @since 3.4.0
 */
function wpex_ls_top() {
	echo '<span data-ls_id="#site_top"></span>';
}

/**
 * Returns social sharing template part.
 *
 * @since 2.0.0
 */
function wpex_social_share() {
	wpex_get_template_part( 'social_share' );
}

/**
 * Adds a hidden searchbox in the footer for use with the mobile menu.
 *
 * @since 1.5.1
 */
function wpex_mobile_searchform() {
	if ( get_theme_mod( 'mobile_menu_search', true ) ) {
		$mm_style = wpex_header_menu_mobile_style();
		if ( $mm_style && 'custom' != $mm_style ) {
			wpex_get_template_part( 'mobile_searchform' );
		}
	}
}

/**
 * Outputs page/post slider based on the wpex_post_slider_shortcode custom field.
 *
 * @since 1.0.0
 */
function wpex_post_slider( $post_id = '', $postion = '' ) {

	$post_id = $post_id ? $post_id : wpex_get_current_post_id();

	if ( ! wpex_has_post_slider( $post_id ) ) {
		return;
	}

	$get = false;
	$current_filter = current_filter();
	$position = wpex_post_slider_position( $post_id );

	switch ( $current_filter ) {

		case 'wpex_hook_topbar_before':

			if ( 'above_topbar' == $position ) {
				$get = true;
			}

			break;

		case 'wpex_hook_header_before':

			if ( 'above_header' == $position ) {
				$get = true;
			}

			break;

		case 'wpex_hook_header_bottom':

			if ( 'above_menu' == $position ) {
				$get = true;
			}

			break;

		case 'wpex_hook_page_header_before':

			if ( 'above_title' == $position ) {
				$get = true;
			}

			break;

		case 'wpex_hook_main_top':

			if ( 'below_title' == $position ) {
				$get = true;
			}

			break;


	}

	if ( $get ) {
		wpex_get_template_part( 'post_slider' );
	}

}