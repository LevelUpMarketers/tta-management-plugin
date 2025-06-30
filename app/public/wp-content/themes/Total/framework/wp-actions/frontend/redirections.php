<?php
/**
 * Redirect single posts if redirect custom field is being used.
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_post_redirect() {

	if ( wpex_vc_is_inline() ) {
		return; // never redirect while editing a page
	}

	$redirect = '';

	// Redirect singular posts
	if ( is_singular() ) {
		if ( 'link' == get_post_format() && ! apply_filters( 'wpex_redirect_link_format_posts', false ) ) {
			$redirect = '';
		} else {
			$redirect = wpex_get_custom_permalink();
		}
	}

	// Terms
	elseif ( is_tax() || is_category() || is_tag() ) {
		$redirect = get_term_meta( get_queried_object_id(), 'wpex_redirect', true );
	}

	// No redirection
	if ( ! $redirect ) {
		return;
	}

	// If redirect url is a number try and grab the permalink
	if ( is_numeric( $redirect ) ) {
		$redirect = get_permalink( $redirect );
	}

	// Redirect
	if ( $redirect ) {
		wp_redirect( esc_url( $redirect ), 301 );
		exit;
	}

}

add_action( 'template_redirect', 'wpex_post_redirect' );
