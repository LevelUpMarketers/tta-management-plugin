<?php
/**
 * Helper functions for the theme builder features.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/*-------------------------------------------------------------------------------*/
/* [ Table of contents ]
/*-------------------------------------------------------------------------------*

	# Core
	# Header
	# Footer
	# Single (Dynamic Templates)

/*-------------------------------------------------------------------------------*/
/* [ Core ]
/*-------------------------------------------------------------------------------*/
function wpex_theme_do_location( $location = '' ) {
	$theme_builder = TotalTheme\Theme_Builder::instance();
	return $theme_builder->do_location( $location );
}

/*-------------------------------------------------------------------------------*/
/* [ Header ]
/*-------------------------------------------------------------------------------*/

/**
 * Get header builder ID
 *
 * @since 4.0
 */
function wpex_header_builder_id() {
	if ( class_exists( 'TotalTheme\HeaderBuilder' ) ) {
		return TotalTheme\HeaderBuilder::get_template_id();
	}
}

/**
 * Check if we are currently in header builder edit mode.
 *
 * @since 4.5
 */
function wpex_is_header_builder_page() {
	if ( ! empty( $_GET[ 'wpex_inline_header_template_editor' ] ) ) {
		return true;
	}
	$header_builder_id = wpex_header_builder_id();
	if ( $header_builder_id && $header_builder_id == wpex_get_current_post_id() ) {
		return true;
	}
}

/**
 * Check if the theme is using the header buidler
 *
 * @since 4.1
 */
function wpex_has_custom_header() {
	return ! empty( wpex_header_builder_id() ) ? true : false;
}

/*-------------------------------------------------------------------------------*/
/* [ Footer ]
/*-------------------------------------------------------------------------------*/

/**
 * Get footer builder ID
 *
 * @since 4.0
 */
function wpex_footer_builder_id() {
	if ( class_exists( 'TotalTheme\FooterBuilder' ) ) {
		return TotalTheme\FooterBuilder::get_template_id();
	}
}

/**
 * Check if we are currently in footer builder edit mode.
 *
 * @since 4.5
 */
function wpex_is_footer_builder_page() {
	if ( ! empty( $_GET[ 'wpex_inline_footer_template_editor' ] ) ) {
		return true;
	}
	$footer_builder_id = wpex_footer_builder_id();
	if ( $footer_builder_id && $footer_builder_id == wpex_get_current_post_id() ) {
		return true;
	}
}

/**
 * Check if footer builder is enabled
 *
 * @since 4.6.5
 */
function wpex_has_custom_footer() {
	return ! empty( wpex_footer_builder_id() ) ? true : false;
}

/*-------------------------------------------------------------------------------*/
/* [ Single | @todo move into ThemeBuilder Class ]
/*-------------------------------------------------------------------------------*/

/**
 * Check if a given post has a singular template.
 *
 * @since 5.0
 */
function wpex_has_singular_template() {
	return (bool) wpex_get_singular_template_id();
}

/**
 * Returns correct post content template.
 *
 * @since 4.3
 */
function wpex_get_singular_template_id( $type = '' ) {

	$post_type = $type ? $type : get_post_type();

	$post_id = is_admin() ? get_the_ID() : wpex_get_current_post_id();

	if ( $meta = get_post_meta( $post_id, 'wpex_singular_template', true ) ) {
		$template_id = $meta;
	} else {
		$template_id = get_theme_mod( $post_type . '_singular_template', null );
	}

	if ( WPEX_PTU_ACTIVE ) {
		$ptu_check = wpex_get_ptu_type_mod( $post_type, 'singular_template_id' );
		if ( $ptu_check ) {
			$template_id = $ptu_check;
		}
	}

	$template_id = (int) apply_filters( 'wpex_get_singular_template_id', $template_id, $post_type ); // legacy
	$template_id = (int) apply_filters( 'wpex_singular_template_id', $template_id, $post_type );
	$template_id = $template_id ? wpex_parse_obj_id( $template_id, 'page' ) : null;

	return $template_id;

}

/**
 * Returns correct post content template.
 *
 * @since 4.3
 */
function wpex_get_singular_template_content( $type = '' ) {

	$template_id = wpex_get_singular_template_id( $type );

	if ( empty( $template_id ) ) {
		return;
	}

	$temp_post = get_post( $template_id );

	if ( $temp_post && 'publish' == get_post_status( $temp_post ) ) {
		return $temp_post->post_content;
	}

}

/**
 * Returns correct post content template.
 *
 * @since 4.3
 */
function wpex_singular_template( $template_content = '' ) {

	if ( ! $template_content ) {
		return;
	}

	$template_content_escaped = wpex_sanitize_template_content( $template_content );

	if ( $template_content_escaped ) {

		$tag_escaped = tag_escape( apply_filters( 'wpex_singular_template_html_tag', 'div' ) );

		echo '<' . $tag_escaped . ' class="custom-singular-template entry wpex-clr">' . $template_content_escaped . '</' . $tag_escaped . '>';

	}

}

/**
 * Returns correct post ID when using a dynamic template.
 *
 * @since 4.8
 */
function wpex_get_dynamic_post_id() {
	return apply_filters( 'wpex_get_dynamic_post_id', wpex_get_current_post_id() );
}
