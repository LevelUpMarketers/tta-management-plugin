<?php
/**
 * Alter oEmbed output
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if we should add a responsive wrapper to a given oembed.
 *
 * @since 5.0
 */
function wpex_maybe_add_oembed_responsive_wrap( $url ) {

	if ( ! $url ) {
		return;
	}

	$add_responsive = false;

	$hosts = apply_filters( 'wpex_oembed_responsive_hosts', array(
		'youtube.com',
		'youtu.be',
		'youtube-nocookie.com',
		'vimeo.com',
		'blip.tv',
		'money.cnn.com',
		'dailymotion.com',
		'flickr.com',
		'hulu.com',
		'kickstarter.com',
		'soundcloud.com',
	) );

	if ( $hosts ) {
		foreach( $hosts as $host ) {
			if ( strpos( $url, $host ) !== false ) {
				$add_responsive = true;
				break; // no need to loop further
			}
		}
	}

	return (bool) apply_filters( 'wpex_responsive_video_wrap', $add_responsive ); // @todo deprecate and rename this filter.

}

/**
 * Add responsive classname around oembed.
 */
function wpex_oembed_html( $cache, $url, $attr, $post_ID ) {
	if ( true === wpex_maybe_add_oembed_responsive_wrap( $url ) ) {
		return '<div class="wpex-responsive-media">' . $cache . '</div>';
	} else {
		return $cache;
	}
}
add_filter( 'embed_oembed_html', 'wpex_oembed_html', 99, 4 );

/**
 * Remove frameborder from oembeds.
 *
 * @todo is this still needed?
 */
function wpex_remove_oembed_frameborder( $return, $data, $url ) {
	return str_ireplace( 'frameborder="0"', '', $return );
}
add_filter( 'oembed_dataparse', 'wpex_remove_oembed_frameborder', 10, 3 );
