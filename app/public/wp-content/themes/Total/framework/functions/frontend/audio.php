<?php
/**
 * Helper functions for getting and displaying audio
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if a given post has a video.
 *
 * @since 5.0
 */
function wpex_has_post_audio( $post_id = null ) {
	return (bool) wpex_get_post_audio( $post_id );
}

/**
 * Returns post audio
 *
 * @since 2.0.0
 */
function wpex_get_post_audio( $post_id = '' ) {

	$post_id = $post_id ? $post_id : get_the_ID();

	$audio = '';

	// Check for self-hosted first
	if ( $self_hosted = get_post_meta( $post_id, 'wpex_post_self_hosted_media', true ) ) {
		if ( is_numeric( $self_hosted ) ) {
			if ( wp_attachment_is( 'audio', $self_hosted ) ) {
				$audio = $self_hosted;
			}
		} else {
			$audio = $self_hosted;
		}
	}

	// Check for wpex_post_audio custom field
	if ( ! $audio  ) {
		$audio = get_post_meta( $post_id, 'wpex_post_audio', true );
	}

	// Check for post oembed
	if ( ! $audio ) {
		$audio = get_post_meta( $post_id, 'wpex_post_oembed', true );
	}

	// Check old redux custom field last
	if ( ! $audio ) {
		$self_hosted = get_post_meta( $post_id, 'wpex_post_self_hosted_shortcode_redux', true );
		if ( is_numeric( $self_hosted ) ) {
			if ( wp_attachment_is( 'audio', $self_hosted ) ) {
				$audio = $self_hosted;
			}
		} else {
			$audio = $self_hosted;
		}
	}

	// Apply filters & return
	return apply_filters( 'wpex_get_post_audio', $audio );

}

/**
 * Echo post audio HTML
 *
 * @since 2.0.0
 */
function wpex_post_audio_html( $audio = '' ) {
	echo wpex_get_post_audio_html( $audio );
}

/**
 * Returns post audio
 *
 * @since 2.0.0
 */
function wpex_get_post_audio_html( $audio = '' ) {

	$audio = $audio ? $audio : wpex_get_post_audio();

	if ( ! $audio ) {
		return;
	}

	// Check if self hosted
	$self_hosted = ( $audio === get_post_meta( get_the_ID(), 'wpex_post_self_hosted_media', true ) ) ? true : false;

	if ( $self_hosted ) {

		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );

		$audio = ( is_numeric( $audio ) ) ? wp_get_attachment_url( $audio ) : $audio;

		return wp_audio_shortcode( array(
			'src' => $audio,
		) );

	}

	// Return oEmbed
	else {

		if ( apply_filters( 'wpex_has_oembed_cache', true ) ) { // filter added for testing purposes only.
			global $wp_embed;
			if ( $wp_embed && is_object( $wp_embed ) ) {
				return $wp_embed->shortcode( array(), $audio );
			}
		} else {
			return wp_oembed_get( $audio );
		}

	}

}