<?php
/**
 * Helper functions for getting and displaying videos
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if a given post has a video.
 *
 * @since 5.0
 */
function wpex_has_post_video( $post_id = null ) {
	return (bool) wpex_get_post_video( $post_id );
}

/**
 * Return correct video embed url.
 *
 * @since 4.3
 * @todo we don't need this anymore for new lightbox functions
 *		 but we need to support wpex_get_video_embed_url_params function as customers have used this.
 */
function wpex_get_video_embed_url( $url = '' ) {

	// Return if no url
	if ( ! $url ) {
		return;
	}

	// Sanitize youtube links
	if ( strpos( $url, 'youtu' ) !== false ) {

		// Covert only if not already in correct format
		if ( strpos( $url, 'embed' ) === false ) {

			// Check for youtu.be
			$url = str_replace( 'youtu.be/', 'youtube.com/watch?v=', $url );

			// Convert url
			$url_string = parse_url( $url, PHP_URL_QUERY );
			parse_str( $url_string, $args );
			if ( ! empty ( $args['v'] ) ) {
				$url = 'youtube.com/embed/' . $args['v'];
			}

		}

	}

	// Sanitize vimeo links
	elseif ( strpos( $url, 'vimeo' ) !== false ) {

		// Covert only if not already in correct format
		if ( strpos( $url, 'player.vimeo' ) === false ) {

			// Get the ID
			$video_id = str_replace( 'http://vimeo.com/', '', $url );
			if ( ! is_numeric( $video_id ) ) {
				$video_id = str_replace( 'https://vimeo.com/', '', $url );
			} elseif ( ! is_numeric( $video_id ) ) {
				$video_id = str_replace( 'http://www.vimeo.com/', '', $url );
			} elseif ( ! is_numeric( $video_id ) ) {
				$video_id = str_replace( 'https://www.vimeo.com/', '', $url );
			}

			// Return embed URL
			if ( is_numeric( $video_id ) ) {
				$url = 'player.vimeo.com/video/' . $video_id;
			}

		}

	}

	// Escape URL and set to correct URL scheme
	$url = $url ? set_url_scheme( esc_url( $url ) ) : '';

	// Add parameters
	$params = apply_filters( 'wpex_get_video_embed_url_params', array(), $url );
	$params_string = '';

	// Add params
	if ( $params ) {

		if ( false === strpos( $url, '?' ) ) {
			$url = $url . '?cparams=1'; // @todo remove this and optimize code below instead.
		}

		// Loop through and check vendors
		foreach ( $params as $vendor => $params ) {

			// Youtube fixes
			$vendor = ( 'youtube' == $vendor ) ? 'yout' : $vendor;

			// Check initial video url for vendor (youtube/vimeo/etc)
			if ( false !== strpos( $url, $vendor ) ) {

				// Loop through and add params to variable
				foreach ( $params as $key => $val ) {

					$params_string .= '&' . esc_attr( $key ) . '=' . esc_attr( $val );

				}

			}

		}

	}

	// Return url
	return $url . $params_string;

}

/**
 * Adds the sp-video class to iFrames.
 *
 * @since 1.0.0
 */
function wpex_add_sp_video_to_oembed( $oembed ) {
	return str_replace( '<iframe', '<iframe class="sp-video"', $oembed );
}

/**
 * Returns post video oEmbed url.
 *
 * @since 4.0
 */
function wpex_get_post_video_oembed_url( $post_id = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$video = '';
	if ( $meta = get_post_meta( $post_id, 'wpex_post_video', true ) ) {
		$video = $meta;
	} elseif ( $meta = get_post_meta( $post_id, 'wpex_post_oembed', true ) ) {
		$video = $meta;
	}
	return apply_filters( 'wpex_get_post_video_oembed_url', $video );
}

/**
 * Echo post video.
 *
 * @since 2.0.0
 */
function wpex_post_video( $post_id = '' ) {
	echo wpex_get_post_video( $post_id );
}

/**
 * Returns post video.
 *
 * @since 2.0.0
 * @todo update to return an array with the video and type return array( $video, 'embed' )
 */
function wpex_get_post_video( $post_id = '' ) {

	$post_id = $post_id ? $post_id : get_the_ID();

	$video = '';

	// Embed
	if ( $embed = get_post_meta( $post_id, 'wpex_post_video_embed', true ) ) {
		$video = $embed;
	}

	// Check for self-hosted first
	if ( ! $video ) {
		$self_hosted = get_post_meta( $post_id, 'wpex_post_self_hosted_media', true );
		if ( is_numeric( $self_hosted ) ) {
			if ( wp_attachment_is( 'video', $self_hosted ) ) {
				$video = $self_hosted;
			}
		} else {
			$video = $self_hosted;
		}
	}

	// Check for wpex_post_video custom field
	if ( ! $video ) {
		$video = get_post_meta( $post_id, 'wpex_post_video', true );
	}

	// Check for post oembed
	if ( ! $video ) {
		$video = get_post_meta( $post_id, 'wpex_post_oembed', true );
	}

	// Check old redux custom field last
	if ( ! $video ) {
		$self_hosted = get_post_meta( $post_id, 'wpex_post_self_hosted_shortcode_redux', true );
		if ( is_numeric( $self_hosted ) ) {
			if ( wp_attachment_is( 'video', $self_hosted ) ) {
				$video = $self_hosted;
			}
		} else {
			$video = $self_hosted;
		}
	}

	// Apply filters & return
	return apply_filters( 'wpex_get_post_video', $video );

}

/**
 * Returns post video type.
 *
 * @since 5.0
 */
function wpex_get_post_video_type( $video = '' ) {
	if ( false !== strpos( $video, '<iframe' )  ) {
		return 'iframe';
	} elseif ( $video === get_post_meta( get_the_ID(), 'wpex_post_self_hosted_media', true ) ) {
		return 'self_hosted';
	} elseif ( $video === get_post_meta( get_the_ID(), 'wpex_post_self_hosted_shortcode_redux', true ) ) {
		return 'self_hosted';
	} else {
		return 'oembed'; // hopefully, in reality it could be anything...
	}
}

/**
 * Echo post video HTML.
 *
 * @since 2.0.0
 */
function wpex_post_video_html( $video = '' ) {
	echo wpex_get_post_video_html( $video );
}

/**
 * Returns post video HTML.
 *
 * @since 2.0.0
 */
function wpex_get_post_video_html( $video = '' ) {

	// Get video
	$video = $video ? $video : wpex_get_post_video();

	// Return if video is empty
	if ( empty( $video ) ) {
		return false;
	}

	$html = '';

	// Get video type
	$video_type = wpex_get_post_video_type( $video );

	// Return video based on type
	switch ( $video_type ) {

		case 'iframe':

			$iframe_video = wpex_sanitize_data( $video, 'iframe' );

			if ( $iframe_video ) {

				$add_responsive_wrap = ( false !== strpos( $video, 'youtu' ) || false !== strpos( $video, 'vimeo' ) ) ? true : false;

				if ( apply_filters( 'wpex_responsive_video_wrap', $add_responsive_wrap ) ) {
					$html = '<div class="wpex-responsive-media">' . $iframe_video . '</div>';
				} else {
					$html = $iframe_video;
				}

			}

			break;

		case 'self_hosted':

			$video = is_numeric( $video ) ? wp_get_attachment_url( $video ) : $video;

			if ( filter_var( esc_url( $video ), FILTER_VALIDATE_URL ) && function_exists( 'wp_video_shortcode' ) ) {
				$html = wp_video_shortcode( array( 'src' => $video, 'width' => '9999' ) );
			} else {
				$html = do_shortcode( wp_strip_all_tags( $video ) );
			}

			break;

		default:

			$html = wpex_video_oembed( $video );

			break;

	}

	return apply_filters( 'wpex_post_video_html', $html, $video );

}

/**
 * Generate custom oEmbed output.
 *
 * @since 3.6.0
 */
function wpex_video_oembed( $video = '', $classes = '', $params = array() ) {

	// Video required
	if ( ! $video ) {
		return;
	}

	// Define output
	$output = '';

	// Sanitize URL
	$video_escaped = esc_url( $video );

	// If escaped video is empty then perhaps the $video is not an oembed URL, maybe
	// it's a shortcode, lets try and parse it.
	if ( empty( $video_escaped ) && ! empty( $video ) && is_string( $video ) ) {
		return do_shortcode( wp_strip_all_tags( $video ) );
	}

	// Fetch oEmbed output
	if ( apply_filters( 'wpex_has_oembed_cache', true ) ) { // filter added for testing purposes only.
		global $wp_embed;
		if ( $wp_embed && is_object( $wp_embed ) ) {
			$html = $wp_embed->shortcode( array(), $video_escaped );
		}
	} else {
		$html = wp_oembed_get( $video_escaped );
	}

	// Return if there is an error fetching the oembed code
	if ( empty( $html ) || is_wp_error( $html ) ) {
		return;
	}

	// Add classes
	if ( $classes ) {

		// Class attribute already added already via filter
		if ( strpos( 'class="', $html ) ) {
			$html = str_replace( 'class="', 'class="' . esc_attr( $classes ) . ' ', $html );
		}

		// No class attribute found so lets add new one with our custom classes
		else {
			$html = str_replace( '<iframe', '<iframe class="' . esc_attr( $classes ) . '"', $html );
		}

	}

	// Apply filters for params
	$params = apply_filters( 'wpex_video_oembed_params', $params );

	// Add params
	if ( $params ) {

		// Define empty params string
		$params_string = '';

		// Loop through and check vendors
		foreach ( $params as $vendor => $params ) {

			// Youtube fixes
			$vendor = ( 'youtube' == $vendor ) ? 'yout' : $vendor;

			// Check initial video url for vendor (youtube/vimeo/etc)
			if ( strpos( $video_escaped, $vendor ) ) {

				// Loop through and add params to variable
				foreach ( $params as $key => $val ) {
					$params_string .= '&' . esc_attr( $key ) . '=' . esc_attr( $val );
				}

			}

		}

		// Add params
		if ( $params_string ) {
			$html = str_replace( '?feature=oembed', '?feature=oembed' . $params_string, $html );
		}

	}

	// Return output
	return $html;

}