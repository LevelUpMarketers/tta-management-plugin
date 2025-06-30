<?php
/**
 * Tracking functions
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns Google Analytics tracking code.
 *
 * @since 5.0
 */
function wpex_google_analytics_tag() {

	$google_property_id = apply_filters( 'wpex_google_property_id', get_theme_mod( 'google_property_id' ) );

	if ( empty( $google_property_id ) ) {
		return;
	}

	$validate_id = (bool) preg_match( '/^ua-\d{4,9}-\d{1,4}$/i', strval( $google_property_id ) );

	if ( ! $validate_id ) {
		return;
	}

	echo "<!-- Google Analytics -->";
	echo "<script>";
		echo "window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;";
		echo "ga('create', '" . wp_strip_all_tags( $google_property_id ) . "', 'auto');";
		echo "ga('send', 'pageview');";
		echo "ga('set', 'anonymizeIp', true);";
	echo "</script>";
	echo "<script async src='https://www.google-analytics.com/analytics.js'></script>";
	echo "<!-- End Google Analytics -->";

}