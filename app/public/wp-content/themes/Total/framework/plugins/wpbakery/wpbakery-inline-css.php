<?php
/**
 * Outputs the CSS saved in the VC for a spefic post ID
 *
 * @package Total WordPress Theme
 * @subpackage Visual Composer
 * @since 5.0
 *
 * @todo convert to singleton class
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loops through settings to add line CSS for VC content added outside it's standard scope
 *
 * @since 4.0
 */
function wpex_output_post_vc_css( $css ) {

	// Get current post ID
	$post_id = wpex_get_current_post_id();

	// Define ID's as array
	$ids = array();

	// Toggle Bar
	if ( wpex_has_togglebar() && $togglebar_id = wpex_togglebar_content_id() ) {
		$ids[] = $togglebar_id;
	}

	// Topbar
	if ( wpex_has_topbar() ) {

		// Top Bar Content
		if ( $topbar_content = wpex_topbar_content( $post_id ) ) {
			if ( $topbar_content && strpos( $topbar_content, 'vc_row' ) ) {
				$ids[] = wpex_get_translated_theme_mod( 'top_bar_content' );
			}
		}

		// Top Bar Social Alt
		if ( $topbar_social_alt = wpex_topbar_social_alt_content( $post_id ) ) {
			if ( $topbar_social_alt && strpos( $topbar_social_alt, 'vc_row' ) ) {
				$ids[] = wpex_get_translated_theme_mod( 'top_bar_social_alt' );
			}
		}
	}

	// Header Aside
	if ( wpex_header_supports_aside() ) {
		$header_aside_content = wpex_header_aside_content( $post_id );
		if ( $header_aside_content && strpos( $header_aside_content, 'vc_row' ) ) {
			$ids[] = wpex_get_translated_theme_mod( 'header_aside' );
		}
	}

	// Callout
	if ( wpex_has_callout() && $callout_content = wpex_callout_content( $post_id ) ) {
		if ( $callout_content && strpos( $callout_content, 'vc_row' ) ) {
			$ids[] = wpex_get_translated_theme_mod( 'callout_text' );
		}
	}

	// Singular template
	if ( is_singular() ) {
		if ( $template = wpex_get_singular_template_id( get_post_type() ) ) {
			$ids[] = $template;
		}
	}

	// Overlay Header template
	$overlay_header_template = apply_filters( 'wpex_overlay_header_template', get_theme_mod( 'overlay_header_template' ) );
	if ( $overlay_header_template && wpex_has_overlay_header() ) {
		$ids[] = $overlay_header_template;
	}

	// WooCommerce
	if ( wpex_is_woo_shop() ) {
		$ids[] = wpex_parse_obj_id( wc_get_page_id( 'shop' ) );
	}

	// Apply filters to ID's
	$ids = (array) apply_filters( 'wpex_vc_css_ids', $ids );

	// Sanitize ID's
	$ids = array_unique( array_filter( $ids ) );

	// Generate CSS
	if ( $ids ) {

		// Loop through id's
		foreach ( $ids as $id ) {

			$id = intval( $id );

			// If not valid ID continue
			if ( ! $id ) {
				continue;
			}

			// Conditional checks, some CSS isn't necessarily needed globally
			if ( function_exists( 'is_shop' ) && is_shop() ) {
				$condition = true; // Always return true for the shop
			} elseif ( is_404() && $id == wpex_get_current_post_id() ) {
				$condition = true;
			} else {
				$condition = ( $id == wpex_get_current_post_id() ) ? false : true;
			}

			// Add CSS
			if ( $condition && $vc_css = get_post_meta( $id, '_wpb_shortcodes_custom_css', true ) ) {
				$css .= '/*VC META CSS*/' . $vc_css;
			}

		}

	}

	// Return $css
	return $css;

}
add_filter( 'wpex_head_css', 'wpex_output_post_vc_css' );

/**
 * Output CSS for wpbakery template.
 *
 * @since 5.0
 */
function wpex_vc_meta_inline_style( $id = '' ) {
	echo wpex_get_vc_meta_inline_style( $id );
}

/**
 * Return inline style element for the CSS saved in the VC meta for any post ID.
 *
 * @since 4.0
 */
function wpex_get_vc_meta_inline_style( $id = '' ) {
	if ( ! $id ) {
		return;
	}
	$css = get_post_meta( $id, '_wpb_shortcodes_custom_css', true );
	if ( $css ) {
		return '<style>' . wp_strip_all_tags( $css ) . '</style>';
	}
}