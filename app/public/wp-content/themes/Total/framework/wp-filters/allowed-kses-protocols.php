<?php
/**
 * Filters the kses_allowed_protocols for sanitization like esc_url to allow
 * specific protocols such as skype calls
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 5.0
 */

defined( 'ABSPATH' ) || exit;

function wpex_kses_allowed_protocols( $protocols ) {
	$protocols[] = 'skype';
	$protocols[] = 'whatsapp';
	$protocols[] = 'callto';
	return $protocols;
}
add_filter( 'kses_allowed_protocols' , 'wpex_kses_allowed_protocols' );