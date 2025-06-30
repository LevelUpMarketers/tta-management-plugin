<?php
/**
 * vcex_post_series shortcode output
 *
 * @package Total WordPress Theme
 * @subpackage Total Theme Core
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$shortcode_tag = 'vcex_post_series';

if ( ! vcex_maybe_display_shortcode( $shortcode_tag, $atts ) ) {
	return;
}

if ( function_exists( 'wpex_get_template_part' ) ) {
	wpex_get_template_part( 'post_series' );
}